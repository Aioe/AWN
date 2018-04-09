<?php

function plot_toolbar_groups($conf, $group, $thread, $article)
{
	plot_single_icon($conf, "menu", "", "Back to menu");			// 1
	plot_single_icon($conf, "listgroups", "", "List subscribed groups");		// 2
        plot_single_icon($conf, "listhreads", "", "List discussion threads");		// 3
        plot_single_icon($conf, "tree", "", "Show discussion tree");			// 4
        plot_single_icon($conf, "left", "", "Previous");				// 5
        plot_single_icon($conf, "right", "", "Next");					// 6
}


function plot_toolbar_threadlist($conf, $group, $thread, $article)
{ 
	plot_single_icon($conf, "quit", $conf["home"], "Back to list of threads");						// 1
        $next = $group + 1;
        if ($next > count($conf["active"]) -1) $next = 0;
        $prev = $group - 1;
        plot_single_icon($conf, "listgroups", $conf["base"], "List subscribed groups");				// 2
        plot_single_icon($conf, "newarticle", "post.php?type=2&amp;group=$group", "Compose a new message");	// 3
        plot_single_icon($conf, "tree", "", "Show discussion three");						// 4

        $urlp = set_url("threadlist", $prev, $thread, $article );
        $urln = set_url("threadlist", $next, $thread, $article );

        if ($prev > 0) plot_single_icon($conf, "left", "$urlp", "Previous group");				// 5
        else plot_single_icon($conf, "left", "", "Previous group");
        if ($next > 0) plot_single_icon($conf, "right", "$urln", "Next group");					// 6
        else plot_single_icon($conf, "right", "", "Next");
}

function plot_toolbar_tree($conf, $xover, $group, $thread, $article)
{
	plot_single_icon($conf, "quit", "?screen=threadlist&amp;group=$group&amp;thread=$thread", "Back to list of threads");											// 1
	$t3d = set_next_thread($xover, $group, $thread);
        $prev = $t3d[0];
        $next = $t3d[1];
        plot_single_icon($conf, "listgroups", $conf["base"], "List subscribed groups");									// 2
        $url = set_url("threadlist", $group, $thread, $article );

        plot_single_icon($conf, "listhreads", $url, "List discussion threads");										// 3
        plot_single_icon($conf, "articles", "?screen=messages&amp;group=$group&amp;thread=$thread&amp;art=$thread", "Show first article in thread");	// 4

        $urlp = set_url("tree", $group, $prev, $article );
        $urln = set_url("tree", $group, $next, $article );

        if ($prev > 0) plot_single_icon($conf, "left", $urlp, "Previous thread");									// 5
        else plot_single_icon($conf, "left", "", "Previous thread");
        if ($next > 0) plot_single_icon($conf, "right", $urln, "Next thread");										// 6
        else plot_single_icon($conf, "right", "", "Next thread");
}

function plot_toolbar_messages($conf, $xover, $group, $thread, $article, $format)
{
	plot_single_icon($conf, "quit", "index.php?screen=tree&amp;group=$group&amp;thread=$thread", "Back to list of thread"); 	// 1
	if ($format == 0) plot_single_icon($conf, "text", "?screen=messages&amp;group=$group&amp;thread=$thread&amp;art=$article&amp;format=1", "Show message with no quote");	// 2
	if ($format == 1) plot_single_icon($conf, "richtext", "?screen=messages&group=$group&amp;thread=$thread&amp;art=$article&amp;format=0", "Show full message");
	plot_single_icon($conf, "reply", "post.php?type=1&amp;group=$group&amp;thread=$thread&amp;art=$article", "Post a reply");	 		// 3
        $xover = set_next_article($xover, $group, $thread, $article);
        if (isset($xover[$article]["thread"]["prev"])) $prev = $xover[$article]["thread"]["prev"];
        else $prev = "";
        if (isset($xover[$article]["thread"]["next"])) $next = $xover[$article]["thread"]["next"];
        else $next = "";

        $url = set_url("tree", $group, $thread, $article );

        plot_single_icon($conf, "tree", $url, "Show discussion thread");										// 4

        $urlp = set_url("messages", $group, $thread, $prev );
        $urln = set_url("messages", $group, $thread, $next );

        if ($prev > 0 ) plot_single_icon($conf, "left", $urlp, "Previous article");									// 5
        else plot_single_icon($conf, "left", "", "Previous article");
        if ($next > 0) plot_single_icon($conf, "right", $urln, "Next article");										// 6
        else plot_single_icon($conf, "right", "", "Next article");
}


function plot_toolbar($xover, $conf, $screen, $group, $thread, $article, $format)
{
        echo "<div class=\"top\">\n";

        if ($screen == "groups") plot_toolbar_groups($conf, $group, $thread, $article); 		// lista dei gruppi
        elseif ($screen == "threadlist") plot_toolbar_threadlist($conf, $group, $thread, $article);   	//lista dei thread
        elseif ($screen == "tree") plot_toolbar_tree($conf, $xover, $group, $thread, $article); 		// albero dei messaggi
        elseif ($screen == "messages") plot_toolbar_messages($conf, $xover, $group, $thread, $article, $format);		// messaggi

        echo "</div>\n";
        echo "<div class=\"endtoolbar\">&nbsp;</div>";
}


function plot_single_icon($conf, $icon, $url, $alt)
{
	$iconpath = $conf["base"] . "png/$icon.png";

        if (strlen($url) > 0)
        {
                echo "<div class=\"toolbaricons\"><a href=\"" . $url . "\"><img alt=\"$alt\" src=\"$iconpath\"></a></div>\n";

        } else {
                echo "<div class=\"toolbaricons\"><img alt=\"$alt\" style=\"opacity: 0.4;\" src=\"$iconpath\"></div>\n";
        }
}

function set_next_thread($xover, $group, $thread)
{
	$prev = 0;
	$next = 0;

	$lista = array();

//////////////////////////////

	$max = 0;

	foreach($xover as $ordinal => $array)
	{
		if ((isset($xover[$ordinal]["References"])) and (empty($xover[$ordinal]["References"])))
		{
			$records = $xover[$ordinal]["followup"];
			$localmax = 0;
			if (count($records) == 0) $records[] = $ordinal;
			foreach($records as $second)
			{
				$oldtime = $xover[$second]["Time"];
				if ($oldtime > $localmax) $localmax = $oldtime;
			}
			$list[$ordinal] = $localmax;
		}
	}

	asort($list);
	
	$oldvalue = 0;
	$check = 0;
	foreach($list as $artm => $maxtime)
	{
		if ($check == 1)
		{
			$next = $artm;
			break;
		}
		if ($artm == $thread)
		{
			$prev = $oldvalue;
			$check = 1;
		}
		$oldvalue = $artm;
	}

	


///////////////////////////////


        return array($prev, $next);
}

function set_next_article($xover, $group, $thread, $article)
{
        if (isset($xover[$thread]["followup"])) $messages = $xover[$thread]["followup"];        
        if (isset($xover[$thread]["Mid"]))      $mid = $xover[$thread]["Mid"];

        $xover[$thread]["thread"]["prev"] = 0;
        $xover[$thread]["thread"]["next"] = 0;
        $oldid = $thread;

        while (count($messages) > 0)
        {
                $tt = 0;
                $tp = count($messages);
                foreach($messages as $post)
                {
                        if (strstr($xover[$post]["References"], $mid))
                        {
                                $tt++;
                                $xover[$oldid]["thread"]["next"] = $post;
                                $xover[$post]["thread"]["prev"] = $oldid;
                                $oldid = $post;                         
                                $mid = $xover[$post]["Mid"];
                                $key = array_search($post, $messages);
                                unset($messages[$key]);
                        } 
                }
                $mid = $xover[$thread]["Mid"];

                $tg = count($messages);
                if ($tg == $tp) 
                {
                        break;
                }
        }

        return $xover;
}


?>
