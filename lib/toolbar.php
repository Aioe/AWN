<?php

function plot_toolbar($xover, $conf, $screen, $group, $thread, $article)
{
        echo "<div class=\"top\">\n";

	if ($screen == "messages")
	{
		plot_single_icon($conf, "reply", "post.php?type=1&amp;group=$group&amp;thread=$thread&amp;art=$article");
	} else plot_single_icon($conf, "menu", $conf["home"]);


        if ($screen == "groups") // lista dei gruppi
        {
                plot_single_icon($conf, "listgroups", "");
                plot_single_icon($conf, "listhreads", "");
                plot_single_icon($conf, "tree", "");
                plot_single_icon($conf, "left", "");
                plot_single_icon($conf, "right", "");
        } elseif ($screen == "threadlist") {   //lista dei thread
                $next = $group + 1;
                if ($next > count($conf["active"]) -1) $next = 0;
                $prev = $group - 1;
                plot_single_icon($conf, "listgroups", $conf["base"]);
                plot_single_icon($conf, "newarticle", "post.php?type=2&amp;group=$group");
                plot_single_icon($conf, "tree", "");

		$urlp = set_url("threadlist", $prev, $thread, $article );
		$urln = set_url("threadlist", $next, $thread, $article );

                if ($prev > 0) plot_single_icon($conf, "left", "$urlp");
                else plot_single_icon($conf, "left", "");
                if ($next > 0) plot_single_icon($conf, "right", "$urln");
                else plot_single_icon($conf, "right", "");
        } elseif ($screen == "tree") {  // messaggi nel thared
                $t3d = set_next_thread($xover, $group, $thread);
                $prev = $t3d[0];
                $next = $t3d[1];
                plot_single_icon($conf, "listgroups", $conf["base"]);
		$url = set_url("threadlist", $group, $thread, $article );

                plot_single_icon($conf, "listhreads", $url);
                plot_single_icon($conf, "tree", "");

		$urlp = set_url("tree", $group, $prev, $article );
                $urln = set_url("tree", $group, $next, $article );

                if ($prev > 0) plot_single_icon($conf, "left", $urlp);
                else plot_single_icon($conf, "left", "");
                if ($next > 0) plot_single_icon($conf, "right", $urln);
                else plot_single_icon($conf, "right", "");

        } elseif ($screen == "messages") {   // messaggio
                $xover = set_next_article($xover, $group, $thread, $article);
                $prev = $xover[$article]["thread"]["prev"];
                $next = $xover[$article]["thread"]["next"];

		$url = set_url("threadlist", $group, $thread, $article );

                plot_single_icon($conf, "listgroups", $conf["base"]);
                plot_single_icon($conf, "listhreads", $url);

		$url = set_url("tree", $group, $thread, $article );

                plot_single_icon($conf, "tree", $url);

		$urlp = set_url("messages", $group, $thread, $prev );
                $urln = set_url("messages", $group, $thread, $next );

                if ($prev > 0 ) plot_single_icon($conf, "left", $urlp);
                else plot_single_icon($conf, "left", "");
                if ($next > 0) plot_single_icon($conf, "right", $urln);
                else plot_single_icon($conf, "right", "");
        }

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
		if ((strlen($xover[$ordinal]["References"]) == 0) and ($ordinal > 0))
		{
			$records = $xover[$ordinal]["followup"];
			$localmax = 0;
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
        $messages = $xover[$thread]["followup"];        
        $mid = $xover[$thread]["Mid"];

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
