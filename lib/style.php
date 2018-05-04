<?php

function plot_threadlist($xover, $start, $conf, $screen, $newsgroup, $thread, $article, $format)
{
        plot_toolbar($xover, $conf, $screen, $newsgroup, $thread, $article, $format);
        echo "<div class=\"titolo\">" . $conf["active"][$newsgroup] . "</div>";

        $container = array();

        foreach ($xover as $start => $array)
        {
                if (
			(isset($xover[$start]["References"])) and
			(strlen($xover[$start]["References"]) == 0) and
			($start > 0)
		   )
                {
                        $subject = $xover[$start]["Subject"];
                        $from = $xover[$start]["From"];
                        $date = $xover[$start]["Date"];
                        $old_time = strtotime($date);
                        $date = date("j/m/y G:i", $old_time); 
                        $replies = $xover[$start]["nr_followup"];

                        $nick = preg_replace("/(\<.+\>)/", "", $from);
			$nick = clean_header($nick, $conf, $newsgroup, $start);
			$subject = clean_header($subject, $conf, $newsgroup, $start);			

                        $color = "";
                        $bgcolor = "";
                        $border = "";
                        $time = 0;

                        $articles = $xover[$start]["followup"];

                        foreach ($articles as $art) if ($time < $xover[$art]["Time"]) $time = $xover[$art]["Time"];
                        if ($time == 0) $time = $xover[$start]["Time"];

                        $diff = time() - $time;
                        $colors = set_background_color($diff, $conf);
                        $border  = $colors[1];

			$color = "background: linear-gradient(+90deg, #fff, $colors[0]);";
                        if ($replies > 0)
                        {
				$url = set_url("tree", $newsgroup, $start, "", $format);
                                $container[$diff] = "
<a href=\"$url\">
<div style=\"$color border-left: 5px solid $border;\" class=\"main3d\">
                <div class=\"description\">$subject ($replies)</div>
                <div class=\"addenda\">by <b>$nick</b> on $date</div>
</div></a>
";
                        } else {
              			$url = set_url("messages", $newsgroup, $start, $start, $format);
//				if ($bgcolor == 0) $bgcolor = "#fff";
		                $container[$diff] = "
<a href=\"$url\">
<div style=\"$color border-left: 5px solid $border;\" class=\"main3d\">
                <div class=\"description\">$subject</div>
                <div class=\"addenda\">by <b>$nick</b> on $date</div>
</div></a>
";
                        }
                }

        }
        
        ksort($container);

        foreach($container as $diff => $data) echo $data;

}

function build_thread($xover, $screen, $thread, $article, $group, $conf, $newsgroup, $format)
{
        plot_toolbar($xover, $conf, $screen, $group, $thread, $article, $format);
        $messages = $xover[$thread]["followup"];        
        $subject = $xover[$thread]["Subject"];

	$subjectb = clean_header($subject, $conf, $group, $thread);

        echo "<div class=\"titolo\">$subjectb</div>";

        plot_tree($xover, $screen, $group, $thread, $thread, $conf, $article, 1, $format);

        $mid = $xover[$thread]["Mid"];

        while (count($messages) > 0)
        {
                $tt = 0;
                $tp = count($messages);
                foreach($messages as $post)
                {
                        if (strstr($xover[$post]["References"], $mid))
                        {
                                $tt++;
                                plot_tree($xover, $screen, $group, $thread, $post, $conf, $article, 0, $format);
                                $mid = $xover[$post]["Mid"];
                                $key = array_search($post, $messages);
                                unset($messages[$key]);
                        } 
                }
                for ($tt; $tt >0; $tt--) echo "</li></ul>\n";
                $mid = $xover[$thread]["Mid"];

                $tg = count($messages);
                if ($tg == $tp) 
                {
                        break;
                }
        }


        echo "</li></ul>\n";
}

// plot_message($fp, $xover, $screen, $newsgroup, $thread, $article, $conf);

function plot_message($xover, $screen, $group, $thread, $article, $config, $format)
{
        plot_toolbar($xover, $config, $screen, $group, $thread, $article, $format);

	$body = get_nntp_body($config, $config["active"][$group], $article, 1, $format);

        $mid = $xover[$article]["Mid"];
        $from = $xover[$article]["From"];
        $date = $xover[$article]["Date"];
        $old_time = strtotime($date);
        $date = date(DATE_RFC822, $old_time);

	$from 		= clean_header($xover[$article]["From"], $config, $group, $article);
	$subject	= clean_header($xover[$article]["Subject"], $config, $group, $article);

	$from = preg_replace("/&lt;.+&gt;/", "", $from);

	if (preg_match("/^Re:\ /i", $subject ))
	{
		$subject = str_replace("Re: ", "", $subject);
		$subject = "Re: $subject";
	}

        $ng = $xover[$article]["Group"];
        echo "<div class=\"corpo\">\n";

	if ($format != 2) echo "<a href=\"?screen=messages&amp;group=$group&amp;thread=$thread&amp;art=$article&amp;format=2\">\n";
	else echo "<a href=\"?screen=messages&amp;group=$group&amp;thread=$thread&amp;art=$article&amp;format=0\">\n";
	echo "
<div class=\"postheaders\"><div class=\"header\">From</div><div class=\"value\">$from</div></div>
<div class=\"postheaders\"><div class=\"header\">Group</div><div class=\"value\">$ng</div></div>
<div class=\"postheaders\"><div class=\"header\">Subject</div><div class=\"value\">$subject</div></div>
<div class=\"postheaders\"><div class=\"header\">Date</div><div class=\"value\">$date</div></div>
</a><hr />";

	echo "<div class=\"testo\">$body</div></div>";
}

function clean_header($value, $conf, $newsgroup, $article)
{
	$output = mb_decode_mimeheader($value);
	$output = str_replace("_", " ", $output);
	$output = preg_replace("/\ $/", "" , $output);
	$output = htmlentities($output);
	return $output;
}

function set_url($screen, $group, $thread, $article, $format )
{
	$url = "";
	if (strlen($screen) > 0) $url = "?screen=$screen";
	if (strlen($group) > 0) $url .= "&amp;group=$group";
	if (strlen($thread) > 0) $url .= "&amp;thread=$thread";
	if (strlen($article) > 0) $url .= "&amp;art=$article";
	if (strlen($format) > 0) $url .= "&amp;format=$format";
	return $url;
}


function plot_grouplist($config, $screen, $newsgroup, $thread, $article, $format)
{

// plot_toolbar($xover, $conf, $screen, $group, $thread, $article, $format)


        plot_toolbar(0, $config, $screen, 0, 0, 0, $format);

	if (count($config["active"]) == 0)
	{
		echo "<h2>Before being read, groups needs to be subscribed</h2>\n";
		return TRUE;
	}

        $start = $config["start"];
        $id = 0;
        foreach($config["active"] as $group)
        {
                if (strlen($group) == 0) continue;
                $id++;
                $xover = nntp_xover($config, $group);       
                $time = 0;
                foreach($xover as $num => $array) if (($num >0) and ($xover[$num]["Group"] == $group) and ($xover[$num]["Time"] > $time)) $time = $xover[$num]["Time"];
                $diff = time() - $time;

                $colors = set_background_color($diff, $config);
                $bgcolor = $colors[0];
                $border  = $colors[1];

		$url = set_url("threadlist", $id, $thread, $article, $format);
echo "
<a href=\"$url\">
<div style=\"background: linear-gradient(+90deg, #fff, $colors[0]);  border-left: 5px solid $border;\" class=\"main3d\">
<div class=\"description\">$group</div>
</div></a>\n";
        }

}


function build_dep($xover)
{
////////////////////////////////

// va qui

//////////////////////////////////

        foreach ($xover as $num => $array)
        {
		if ($num > 0)
		{
			if (isset($xover[$num]["References"])) 
			{
				$references = explode(" ", $xover[$num]["References"]);
				$ref = $references[0];						// il primo mid nel reference

				$number = 0;

				foreach($xover as $item => $array)
				{
					if ($item > 0)
					{
						$oldmid = trim($xover[$item]["Mid"]);
						if (strcmp($oldmid, $ref) == 0)
						{
							$number = $item;
							break;
						}
					}
				}

				$oldref = "";
				if (isset($xover[$number]["References"])) $oldref = $xover[$number]["References"];
				if (!empty($oldref))
				{
					$gg = $xover[$num]["References"];
				 	$xover[$num]["References"] = $oldref . " " . $gg;	
				}

			}

                	$mid = $xover[$num]["Mid"];
                	$replies = array();
			$structure = array();
                	foreach($xover as $item => $array) 
			{
				if (($item > 0) and (isset($xover[$item]["References"])) and (strstr($xover[$item]["References"], $mid))) 
				{
					$replies[] = $item;
					$structure[$item] = $xover[$item]["Mid"];
				}

			}
                	$xover[$num]["nr_followup"] = count($replies);
                	krsort($replies);
                	$xover[$num]["followup"] = $replies;
		}
        }
        return $xover;
}



function plot_tree($xover, $screen, $group, $thread, $article, $conf, $post, $isfirst, $format)
{
        $mid = $xover[$article]["Mid"];
        $from = $xover[$article]["From"];
        $date = $xover[$article]["Date"];
        $old_time = strtotime($date);
        $date = date("j/m/y G:i", $old_time); 
        $nick = preg_replace("/(\<.+\>)/", "", $from);

	$fromb = clean_header($nick, $conf, $group, $article);

        $bgcolor = "";

        $time = $xover[$article]["Time"];
        $now = time();
        $diff = $now - $time;

        $colors = set_background_color($diff, $conf);
        $bgcolor = $colors[0];
        $border  = $colors[1];

	if (!empty($bgcolor)) $background = "background-color: $bgcolor;";
	else $background = "";

////////
	$url = set_url("messages", $group, $thread, $article, $format );

	if ($article == $post) $background = "background-color: #bbd";

	if ($isfirst) $style = "border-bottom: 1px solid #ccc; padding-left: 2%;";
	else $style = "";

	if ($article != $thread)
	{
		$old_subject = clean_header($xover[$thread]["Subject"], $conf, $group, $thread);
		$new_subject = clean_header($xover[$article]["Subject"], $conf, $group, $article);
		$new_subject = str_replace("Re: ", "", $new_subject);
		if (strlen($old_subject) != strlen($new_subject)) $subject = $new_subject;
		else $subject = "";
	} else $subject = "";

        echo "<ul style=\"$style\" class=\"lista\">";
        if ($subject == "") echo "
<li style=\"$background\"><a href=\"$url\">
<div class=\"tree\" style=\"border-left: 5px solid $border;\"><b>$fromb</b> $date</div></a>";
	else echo "
<li style=\"$background\"><a href=\"$url\">
<div class=\"tree\" style=\"border-left: 5px solid $border;\"><b>$fromb</b> $date<br /><b>$subject</b></div></a>";

}


function set_background_color($diff, $conf)
{

        $bgcolor = 0;
        $border  = 0; 
        foreach($conf["colors"]["background"] as $limit => $color) if ($diff <= $limit) $bgcolor = $color;
        foreach($conf["colors"]["border"] as $limit => $color) if ($diff <= $limit) $border = $color;

        $ret = array( $bgcolor, $border);
        return $ret;
}

function show_error_string($error, $html)
{ 
	if ($html == 1)	
	{
		include("config.php");
		print_html_head($conf);
	}
        echo "\n<br />  
                <div style=\"max-width: 80%; width: 80%; border: 1px solid #f99; margin-left: 10%; padding-left: 1%; padding-right: 1%; padding-top: 1%;\">
                <h3 style=\"text-align: center;\">Unrecoverable error</h3><hr />
                <p style=\"text-align: justify; font-size: larger;\">$error</p>
                </div>";

	if ($html == 1) print_html_tail($conf);
	exit(0);

}

function fatal_error($key, $value)
{
	show_error_string("Syntax error in URL options: key '<b>$key</b>' has a value of '<i>$value</i>' that is <b>not</b> allowed here.<br>Please report this failure to the system administrators.", 1);
	return 0;
}

function print_html_head($conf)
{
	$file = $conf["etcdir"] . "/" . "head.inc.php";
	include($file);
	echo "<div class=\"container\">";
	return TRUE;

}

function print_html_tail($conf)
{
	$file = $conf["etcdir"] . "/" . "tail.inc.php";
	echo "</div>";
        include($file);
	return TRUE;
}

function search_articles($conf, $newsgroup, $searchart, $format)
{
	if ($format == "") $format = 0;
	$xover = "";
	plot_toolbar($xover, $conf, "searchart", $newsgroup, "", "", $format);


	if (strlen($searchart) == 0)
	{
		echo "<div class=\"titolo\">Search inside " . $conf["active"][$newsgroup] . "</div>\n";
		echo "
<form>
<input type=\"hidden\" name=\"format\" value=\"$format\">
<input type=\"hidden\" name=\"screen\" value=\"searchart\">
<input type=\"hidden\" name=\"group\" value=\"$newsgroup\">
<fieldset style=\"padding-top: 1%;\"><input style=\"width: 98%; height: 50px; font-size: larger;\" type=\"text\" name=\"searchart\">
<br /><input type=\"submit\" style=\"padding: 3%;\" value=\"Search\">
</fieldset>
</form>";

	} else {
		$group = $conf["active"][$newsgroup];
		$xover = nntp_xover($conf, $group);

		echo "<div class=\"titolo\">Search results for '$searchart' inside $group</div>\n"; 
		$dirpath = $conf["spooldir"] . "/data/$group/";
		$filestoscan = scandir($dirpath);
		if (!$filestoscan) show_error_string("Unable to read direactory $dirpath!", 1);

		foreach($filestoscan as $filename)
		{
			$file = $dirpath . $filename;
			$testo = file_get_contents($file);
			if (preg_match("/$searchart/i", $testo))
			{
				$subj = clean_header($xover[$filename]["Subject"], $conf, $newsgroup, $filename);
				$from = clean_header($xover[$filename]["From"], $conf, $newsgroup, $filename);
				$date = $xover[$filename]["Date"];
				echo "
<a href=\"?screen=messages&amp;group=$newsgroup&amp;art=$filename&amp;format=$format\">
<div class=\"main3d\"><b>$subj</b><br />$from<br />$date</div></a>";



			}
			
		}

	}

}


?>
