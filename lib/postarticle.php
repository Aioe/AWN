<?php

include("./config.php");
include("style.php");
include("backend.php");
include("toolbar.php");

///////////////////////////////////////////////////////////////////////////

$start          = $conf["start"];
if (isset($_POST["message"])) $message = $_POST["message"];


$thread         = GET_header("thread");
$newsgroup      = GET_header("group");
$article        = GET_header("art");
$type		= GET_header("type");
$noquote 	= GET_header("noquote");

$send 		= GET_header("x");




if ($type == 0) fatal_error("type", $type);
if ($newsgroup == 0) fatal_error("group", $group);

if ($type == 1) 
{
	if ($thread == 0)  fatal_error("thread", $thread);
	if ($article == 0) fatal_error("art", $article);
}

///////////////////////////////////////////////////////

if ($type == 1)
{

	if ($send > 0)
	{
		if (strlen($message) > 0) $output = post_message($conf, $newsgroup, $thread, $article, $message, $subject);
		echo "<h1>Message posted</h1>";
		return 0;
	}

	echo "
<form action=\"post.php\" method=\"post\">
<input type=\"hidden\" name=\"group\" value=\"$newgroup\">
<input type=\"hidden\" name=\"art\" value=\"$article\">
<input type=\"hidden\" name=\"thread\" value=\"$thread\">
<input type=\"hidden\" name=\"group\" value=\"$newsgroup\">
<input type=\"hidden\" name=\"type\" value=\"1\">
";
	post_toolbar($conf, $type, $newsgroup, $thread, $article, $noquote);

        $group = $conf["active"][$newsgroup];
        $xover = nntp_xover($conf, $group);
        $subject = $xover[$article]["Subject"];
	$from = $xover[$article]["From"];
	$date = $xover[$article]["Date"];
	if ($noquote == 0)
	{
		$from = $xover[$article]["From"];
        	$date = $xover[$article]["Date"];
		$text = get_nntp_body($conf, $group, $article);
		$quoted_text = str_replace("\n", "\n> ", $text);
		$quoted_text = str_replace("<br />\n", "\n", $quoted_text);
		$quote_head  = "On $date $from wrote:";
		$text_to_quote = "$quote_head\n$quoted_text\n";
	} else if ($noquote == 1) $text_to_quote = "";
	else if ($noquote == 2) {
                $from = $xover[$article]["From"];
                $date = $xover[$article]["Date"];
                $text = get_nntp_body($conf, $group, $article);
                $quoted_text = str_replace("\n", "\n> ", $text);
                $quoted_text = str_replace("<br />\n", "\n", $quoted_text);
                $quote_head  = "On $date $from wrote:";
                $text_to_quote = "$quote_head\n$quoted_text\n";
		$text_to_quote .= "\n$message\n";	
	} else {
		$text_to_quote = "";
		$noquote = 0;
	}
	
	$sender = get_sender($conf);
      	if ($sender)
	{
		echo "<div class=\"postheaders\">
	<div class=\"header\">Sender</div>
        <div class=\"value\">$sender</div>
</div>";
	}

	if (!preg_match("/^RE: /i", $subject)) $subject = "Re: $subject";

	echo "
<div class=\"postheaders\">
	<div class=\"header\">Group</div>
	<div class=\"value\">$group</div>
</div>
<div class=\"postheaders\">
        <div class=\"header\">Subject</div>
        <div class=\"value\">$subject</div>
</div>
";

	if (!$sender)
	{
		echo "
<fieldset>
    <legend>Posting identity</legend>
    <div class=\"postingident\"><input style=\"width: 25%;\" type=\"text\" name=\"nick\" value=\"Nomen Nescio\"> <input style=\"width: 70%;\" type=\"text\" name=\"email\" value=\"&lt;test@null.invalid&gt;\"></div>
 </fieldset>";


	}

	echo "
<textarea name=\"message\">
$text_to_quote
</textarea>
</form>\n";

}


function post_toolbar($conf, $type, $newsgroup, $thread, $article, $noquote)
{
	echo "<div class=\"top\">\n";

////////////////////////////////////////

        plot_single_icon($conf, "left", "index.php?screen=messages&amp;group=$newsgroup&amp;thread=$thread&amp;art=$article");

	echo "<div class=\"toolbaricons\"><input type=\"image\" alt=\"Send message\" src=\"./png/send.png\"></div>\n";

	plot_single_icon($conf, "quote", "post.php?screen=messages&amp;group=$newsgroup&amp;thread=$thread&amp;art=$article&amp;type=$type");

	if ($noquote == 0) plot_single_icon($conf, "cancel", "post.php?screen=messages&amp;group=$newsgroup&amp;thread=$thread&amp;art=$article&amp;type=$type&amp;noquote=1");
	else  if ($noquote == 1)
	{
		echo "<div class=\"toolbaricons\"><input id=\"magic\" type=\"image\" alt=\"Magic quote\" name=\"magic\" src=\"./png/cancel.png\"></div>\n";
		echo "<input type=\"hidden\" name=\"noquote\" value=\"2\">";
	} else {
                echo "<div class=\"toolbaricons\"><input id=\"magic\" type=\"image\" alt=\"Magic quote\" name=\"magic\" src=\"./png/cancel.png\"></div>\n";
                echo "<input type=\"hidden\" name=\"noquote\" value=\"1\">";
	}


	echo "</div>\n";
	echo "<div class=\"endtoolbar\">&nbsp;</div>";

/////////////////////////////////////////

}


function get_sender()
{
	return FALSE;

}

function post_message($conf, $newgroup, $thread, $article, $message)
{
	echo "<pre>DIO</pre>\n";
}

?>

