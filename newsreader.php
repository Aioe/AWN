<?php

include("./config.php");
include("./lib/style.php");
include("./lib/backend.php");
include("./lib/toolbar.php");

///////////////////////////////////////////////////////////////////////////

$start = $conf["start"];

if (isset($_GET["thread"])) $thread = $_GET["thread"];
else $thread = 0;
if (!filter_var($thread, FILTER_VALIDATE_INT)) fatal_error("thread", $thread);

if (isset($_GET["art"])) $article = $_GET["art"];
else $article = 0;
if (!filter_var($article, FILTER_VALIDATE_INT)) fatal_error("art", $article);

if (isset($_GET["group"])) $newsgroup = $_GET["group"];
else $newsgroup = 0;
if (!filter_var($newsgroup, FILTER_VALIDATE_INT)) fatal_error("group", $group);

$screen = $_GET["screen"];

if (
	($screen != "messages") and
	($screen != "threadlist") and
	($screen != "tree") and
	($screen != "grouplist")) fatal_error("screen", $screen);


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


/////////////////////////////////////////////////////////////////

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


?>

