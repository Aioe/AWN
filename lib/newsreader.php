<?php

include("config.php");
include("style.php");
include("backend.php");
include("toolbar.php");

date_default_timezone_set('Europe/Rome');

///////////////////////////////////////////////////////////////////////////

$start 		= $conf["start"];

$thread         = GET_header("thread");
$newsgroup	= GET_header("group");
$article 	= GET_header("art");

if (isset($_GET["screen"])) $screen = $_GET["screen"];
else $screen = "";

if (
	($screen != "") and
	($screen != "messages") and
	($screen != "threadlist") and
	($screen != "tree") and
	($screen != "grouplist")) fatal_error("screen", $screen);


if ($newsgroup)
{
	$groupcount = count($conf["active"]);
	if (($newsgroup >= $groupcount) or ($newsgroup == 0)) show_error_string("Parameter 'newsgroup' has an invalid value '<i>$newsgroup</i>', aborting", 1);
} 

print_html_head($conf);

//////////////////////////////////////////////////////////////////////////////


if (($screen == "groups") or (strlen($screen) == 0))
{
	$screen = "groups";
	plot_grouplist($conf, $screen, $newsgroup, $thread, $article );
	print_html_tail($conf);
	return(0);
} else $group = $conf["active"][$newsgroup];

$xover = nntp_xover($conf, $group);
if (!$xover)
{
        return 0;
}
krsort($xover);
$xover = build_dep($xover);

if (($thread  > 0) and (!check_article_exist($xover, $thread))) show_error_string("Parameter 'thread' has an invalid value of '<i>$thread</i>'", 1);
if (($article > 0) and (!check_article_exist($xover, $article))) show_error_string("Parameter 'art' has an invalid value of '<i>$article</i>'", 1);


if ($screen == "threadlist")
{
        plot_threadlist($xover, $start, $conf, $screen, $newsgroup, $thread, $article);
} else if ($screen == "tree")
{
	build_thread($xover, $screen, $thread, $article, $newsgroup, $conf);
} else if ($screen = "messages")
{
	$format = GET_header("format");
	plot_message($xover, $screen, $newsgroup, $thread, $article, $conf, $format);
}

///////////////////////////////////////////////////////////////////////////////////

print_html_tail($conf);

/////////////////////////////////////////////////////////////////

?>

