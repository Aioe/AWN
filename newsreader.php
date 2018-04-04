<?php

include("config.php");
include("style.php");
include("backend.php");
include("toolbar.php");

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


print_html_head();

//////////////////////////////////////////////////////////////////////////////


if (($screen == "groups") or (strlen($screen) == 0))
{
	$screen = "groups";
	plot_grouplist($conf, $screen, $newsgroup, $thread, $article );
	return 0;
} else $group = $conf["active"][$newsgroup];

$xover = nntp_xover($conf, $group);
if (!$xover)
{
        return 0;
}
krsort($xover);
$xover = build_dep($xover);

if ($screen == "threadlist")
{
        plot_threadlist($xover, $start, $conf, $screen, $newsgroup, $thread, $article);
} else if ($screen == "tree")
{
	build_thread($xover, $screen, $thread, $article, $newsgroup, $conf);
} else if ($screen = "messages")
{
	plot_message($xover, $screen, $newsgroup, $thread, $article, $conf);

}

///////////////////////////////////////////////////////////////////////////////////

print_html_tail();

/////////////////////////////////////////////////////////////////

?>

