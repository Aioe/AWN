<?php

function plot_threadlist($xover, $start, $conf, $screen, $newsgroup, $thread, $article)
{
        plot_toolbar($xover, $conf, $screen, $newsgroup, $thread, $article);
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
                
                        if ($replies > 0)
                        {
				$url = set_url("tree", $newsgroup, $start, "");
                                $container[$diff] = "
<a href=\"$url\">
<div style=\"$color border-left: 5px solid $border;\" class=\"main3d\">
                <div class=\"description\">$subject ($replies)</div>
                <div class=\"addenda\">by <b>$nick</b> on $date</div>
</div></a>
";
                        } else {
              			$url = set_url("messages", $newsgroup, $start, $start);
		                $container[$diff] = "
<a href=\"$url\">
<div style=\"$color background-color: $bgcolor;  border-left: 5px solid $border;\" class=\"main3d\">
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

function build_thread($xover, $screen, $thread, $article, $group, $conf)
{
        plot_toolbar($xover, $conf, $screen, $group, $thread, $article);
        $messages = $xover[$thread]["followup"];        
        $subject = $xover[$thread]["Subject"];

        echo "<div class=\"titolo\">$subject</div>";

// plot_tree($xover, $screen, $group, $thread, $article, $conf, $post)

        plot_tree($xover, $screen, $group, $thread, $thread, $conf, $article, 1);

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
                                plot_tree($xover, $screen, $group, $thread, $post, $conf, $article, 0);
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

function plot_message($xover, $screen, $group, $thread, $article, $config)
{
        plot_toolbar($xover, $config, $screen, $group, $thread, $article);

	$body = get_nntp_body($config, $config["active"][$group], $article);

        $mid = $xover[$article]["Mid"];
        $from = $xover[$article]["From"];
        $date = $xover[$article]["Date"];
        $from = htmlentities($from);
        $subject = $xover[$article]["Subject"];
        $ng = $xover[$article]["Group"];
        $nick = preg_replace("/(\<.+\>)/", "", $from);
        echo "<div class=\"corpo\">
<div class=\"intestazioni\"><b>From:</b>       $nick</div>
<div class=\"intestazioni\"><b>Newsgroup:</b>  $ng</div>
<div class=\"intestazioni\"><b>Subject:</b>    $subject</div>
<div class=\"intestazioni\"><b>Date:</b>       $date</div>
<hr />
<div class=\"testo\">$body</div>
</div>
";

}

function set_url($screen, $group, $thread, $article )
{
	$url = "";
	if (strlen($screen) > 0) $url = "?screen=$screen";
	if (strlen($group) > 0) $url .= "&amp;group=$group";
	if (strlen($thread) > 0) $url .= "&amp;thread=$thread";
	if (strlen($article) > 0) $url .= "&amp;art=$article";
	return $url;
}


function plot_grouplist($config, $screen, $newsgroup, $thread, $article)
{
        plot_toolbar(0, $config, $screen, 0, 0, 0);
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

		$url = set_url("threadlist", $id, $thread, $article);

echo "
<a href=\"$url\">
<div style=\"background-color: $bgcolor;  border-left: 5px solid $border\" class=\"main3d\">
<div class=\"description\">$group</div>
</div></a>\n";
        }

}


function build_dep($xover)
{

        foreach ($xover as $num => $array)
        {
		if ($num > 0)
		{
                	$mid = $xover[$num]["Mid"];
                	$replies = array();
                	foreach($xover as $item => $array) if (($item > 0) and (isset($xover[$item]["References"])) and (strstr($xover[$item]["References"], $mid))) $replies[] = $item;
                	$xover[$num]["nr_followup"] = count($replies);
                	krsort($replies);
                	$xover[$num]["followup"] = $replies;
		}
        }
        return $xover;
}



function plot_tree($xover, $screen, $group, $thread, $article, $conf, $post, $isfirst)
{
        $mid = $xover[$article]["Mid"];
        $from = $xover[$article]["From"];
        $date = $xover[$article]["Date"];
        $old_time = strtotime($date);
        $date = date("j/m/y G:i", $old_time); 
        $nick = preg_replace("/(\<.+\>)/", "", $from);

        $bgcolor = "";

        $time = $xover[$article]["Time"];
        $now = time();
        $diff = $now - $time;

        $colors = set_background_color($diff, $conf);
        $bgcolor = $colors[0];
        $border  = $colors[1];

	$url = set_url("messages", $group, $thread, $article );

	if ($article == $post) $bgcolor = "#bbd";

	if ($isfirst) $style = "border-bottom: 1px solid #ccc; padding-left: 2%;";
	else $style = "";

        echo "<ul style=\"$style\" class=\"lista\">";
        echo "
<li style=\"background-color: $bgcolor;\">
<div class=\"tree\" style=\"border-left: 5px solid $border;\"><a href=\"$url\"><b>$nick</b><br />$date</a></div>";

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

function clean_body_line($line, $conf, $group, $article)
{
        $output = rtrim($line);
        $leng = strlen($output);
	if (
		(!isset($leng)) or ($leng == 0)
	   ) $leng = 0;
	else $leng--;
	$nobreak = 0;

	if (!isset($output[$leng])) return "";

       	if ($output[$leng] == "=") $nobreak = 1;
	$output = quoted_printable_decode($output); 

	$charset = "ISO8859-15"; // Default

	$ct = nntp_get_header($conf, $group, $article, "Content-Type", 0);

	if (preg_match("/charset=([a-z0-9\-]+)/i", $ct, $match))	$charset = trim($match[1]);
	$charset = strtoupper($charset);
	$output = htmlentities($output, ENT_SUBSTITUTE, $charset);
	if ($nobreak == 0) $output .= "<br />\n";
	return $output;
}

function show_error_string($error, $html)
{ 
	if ($html == 1)	print_html_head();
        echo 
                "<br />  
                <div style=\"max-width: 80%; width: 80%; border: 1px solid #f99; margin-left: 10%; padding-left: 1%; padding-right: 1%; padding-top: 1%;\">
                <h3 style=\"text-align: center;\">Unrecoverable error</h3><hr />
                <p style=\"text-align: justify; font-size: larger;\">$error</p>
                </div>

";
	print_html_tail();
	exit(0);

}

function fatal_error($key, $value)
{
	show_error_string("Syntax error in URL options: key '<b>$key</b>' has a value of '<i>$value</i>' that is <b>not</b> allowed here.<br>Please report this failure to the system administrators.", 1);
	return 0;
}

function print_html_head()
{
        echo "
<!DOCTYPE html>
<html lang=\"en\">
  <head>
  <meta charset=\"utf-8\">
    <meta name=\"description\" content=\"Aioe.org Newsreader\">
    <meta name=\"author\" content=\"Aioe\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=0.85\">
    <title>Aioe.org Newsreader</title>

    <link rel=\"stylesheet\" href=\"./nr.css\">
    <meta name=\"keywords\" content=\"Aioe.org NNTP USENET PUBLIC SERVER\" />
</head>
<body>

<div class=\"container\">
";


}

function print_html_tail()
{
        echo "
</div>

</body>
</html>";
}




?>
