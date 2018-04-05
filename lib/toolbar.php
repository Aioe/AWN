<?php

function plot_toolbar_groups($conf, $group, $thread, $article)
{
	plot_single_icon($conf, "menu", $conf["home"]);		// 1
	plot_single_icon($conf, "listgroups", "");		// 2
        plot_single_icon($conf, "listhreads", "");		// 3
        plot_single_icon($conf, "tree", "");			// 4
        plot_single_icon($conf, "left", "");			// 5
        plot_single_icon($conf, "right", "");			// 6
}


function plot_toolbar_threadlist($conf, $group, $thread, $article)
{ 
	plot_single_icon($conf, "left", $conf["home"]);					// 1
        $next = $group + 1;
        if ($next > count($conf["active"]) -1) $next = 0;
        $prev = $group - 1;
        plot_single_icon($conf, "listgroups", $conf["base"]);				// 2
        plot_single_icon($conf, "newarticle", "post.php?type=2&amp;group=$group");	// 3
        plot_single_icon($conf, "tree", "");						// 4

        $urlp = set_url("threadlist", $prev, $thread, $article );
        $urln = set_url("threadlist", $next, $thread, $article );

        if ($prev > 0) plot_single_icon($conf, "left", "$urlp");			// 5
        else plot_single_icon($conf, "left", "");
        if ($next > 0) plot_single_icon($conf, "right", "$urln");			// 6
        else plot_single_icon($conf, "right", "");
}

function plot_toolbar_tree($conf, $xover, $group, $thread, $article)
{
	plot_single_icon($conf, "menu", $conf["home"]);					// 1
	$t3d = set_next_thread($xover, $group, $thread);
        $prev = $t3d[0];
        $next = $t3d[1];
        plot_single_icon($conf, "listgroups", $conf["base"]);				// 2
        $url = set_url("threadlist", $group, $thread, $article );

        plot_single_icon($conf, "listhreads", $url);					// 3
        plot_single_icon($conf, "articles", "?screen=messages&amp;group=$group&amp;thread=$thread&amp;art=$thread");						// 4

        $urlp = set_url("tree", $group, $prev, $article );
        $urln = set_url("tree", $group, $next, $article );

        if ($prev > 0) plot_single_icon($conf, "left", $urlp);				// 5
        else plot_single_icon($conf, "left", "");
        if ($next > 0) plot_single_icon($conf, "right", $urln);				// 6
        else plot_single_icon($conf, "right", "");
}

function plot_toolbar_messages($conf, $xover, $group, $thread, $article)
{
	plot_single_icon($conf, "left", "index.php?screen=threadlist&amp;group=$group&amp;thread=$thread&amp;art=$article"); 	// 1									// 1
	plot_single_icon($conf, "listgroups", $conf["base"]);									// 2
	plot_single_icon($conf, "reply", "post.php?type=1&amp;group=$group&amp;thread=$thread&amp;art=$article"); 		// 3
        $xover = set_next_article($xover, $group, $thread, $article);
        if (isset($xover[$article]["thread"]["prev"])) $prev = $xover[$article]["thread"]["prev"];
        else $prev = "";
        if (isset($xover[$article]["thread"]["next"])) $next = $xover[$article]["thread"]["next"];
        else $next = "";

        $url = set_url("tree", $group, $thread, $article );

        plot_single_icon($conf, "tree", $url);										// 4

        $urlp = set_url("messages", $group, $thread, $prev );
        $urln = set_url("messages", $group, $thread, $next );

        if ($prev > 0 ) plot_single_icon($conf, "left", $urlp);								// 5
        else plot_single_icon($conf, "left", "");
        if ($next > 0) plot_single_icon($conf, "right", $urln);								// 6
        else plot_single_icon($conf, "right", "");
}


function plot_toolbar($xover, $conf, $screen, $group, $thread, $article)
{
        echo "<div class=\"top\">\n";

        if ($screen == "groups") plot_toolbar_groups($conf, $group, $thread, $article); 		// lista dei gruppi
        elseif ($screen == "threadlist") plot_toolbar_threadlist($conf, $group, $thread, $article);   	//lista dei thread
        elseif ($screen == "tree") plot_toolbar_tree($conf, $xover, $group, $thread, $article); 		// albero dei messaggi
        elseif ($screen == "messages") plot_toolbar_messages($conf, $xover, $group, $thread, $article);		// messaggi

        echo "</div>\n";
        echo "<div class=\"endtoolbar\">&nbsp;</div>";
}


function plot_single_icon($conf, $icon, $url)
{
	$iconpath = $conf["base"] . "png/$icon.png";

        if (strlen($url) > 0)
        {
                echo "<div class=\"toolbaricons\"><a href=\"" . $url . "\"><img src=\"$iconpath\"></a></div>\n";

        } else {
                echo "<div class=\"toolbaricons\"><img style=\"opacity: 0.4;\" src=\"$iconpath\"></div>\n";
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
