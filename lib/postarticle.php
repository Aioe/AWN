<?php

include("config.php");
include("style.php");
include("backend.php");
include("toolbar.php");
include("newsagent.php");

date_default_timezone_set('Europe/Rome');

///////////////////////////////////////////////////////////////////////////

if (isset($_POST["message"])) 	$message = $_POST["message"];
if (isset($_POST["nick"])) 	$nick = $_POST["nick"];
if (isset($_POST["email"])) 	$email = $_POST["email"];
if (isset($_POST["subject"])) 	$subject = $_POST["subject"];

if (!isset($nick)) $nick = "Nomen Nescio";
if (!isset($email)) $email = "&lt;fake@null.invalid&gt;";

$newsgroup      = GET_header("group");
$type           = GET_header("type");

$groupcount = count($conf["active"]);
if (($newsgroup >= $groupcount) or ($newsgroup == 0)) show_error_string("Parameter 'group' has an invalid value '<i>$newsgroup</i>', aborting", 1);

if ($type == 1)
{
	$thread         = GET_header("thread");
	$article        = GET_header("art");
	$noquote 	= GET_header("noquote");
}

if ($type == 2)
{
	$thread         = GET_header("thread");
	$article = 0;
	$noquote = 0;
}
$group = $conf["active"][$newsgroup];

$xover = nntp_xover($conf, $group);
if (!$xover)
{
        return 0;
}
krsort($xover);
$xover = build_dep($xover);


if (($thread  > 0) and (!check_article_exist($xover, $thread))) show_error_string("Parameter 'thread' has an invalid value of '<i>$thread</i>'", 1);
if (($article > 0) and (!check_article_exist($xover, $article))) show_error_string("Parameter 'art' has an invalid value of '<i>$article</i>'", 1);

$send 		= GET_header("x");

if ($type == 0) fatal_error("type", $type);
if ($newsgroup == 0) fatal_error("group", $newgroup);

if ($type == 1) 
{
	if ($thread == 0)  fatal_error("thread", $thread);
	if ($article == 0) fatal_error("art", $article);
}

///////////////////////////////////////////////////////


if ($type == 1) // Reply
{

	if ($send > 0)
	{
		$subject = nntp_get_header($conf, $conf["active"][$newsgroup], $article, "Subject", 1);
		if (!preg_match("/Re:/i", $subject)) $subject = "Re: $subject";
		if (strlen($message) > 0) 
		{
			$output = post_reply($conf, $newsgroup, $thread, $article, $message, $subject, $nick, $email);
			if ($output == TRUE)
                        {
                                check_nntp($conf);
                                $url = $conf["home"] . $conf["base"] . "index.php?screen=tree&group=$newsgroup&thread=$thread";
                                header("Location: $url");
                        }
		}
	}
}


if ($type == 2) // New message
{
	if ($send > 0)
	{
		if (strlen($message) > 0) 
		{
			$output = post_new_message($conf, $conf["active"][$newsgroup], $message, $subject, $nick, $email);
			check_nntp($conf);
			$url = $conf["home"] . $conf["base"] . "index.php?screen=threadlist&group=$newsgroup";
			header("Location: $url");
		}
                //////////////////////////////////////////
	}

}


print_html_head();

if ($type == 1) 
{
	if (!isset($subject)) $subject = "";
	if (!isset($message)) $message = "";
	plot_reply_form($conf, $type, $newsgroup, $article, $thread, $noquote, $subject, $nick, $email, $message);
}

if ($type == 2) 
{
	if (!isset($subject)) $subject = "";
        if (!isset($message)) $message = "";
	plot_newmessage_form($conf, $type, $newsgroup, $thread, $article, $subject, $nick, $email, $noquote, $message);
}
print_html_tail();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function post_toolbar($conf, $type, $newsgroup, $thread, $article, $noquote)
{
	echo "<div class=\"top\">\n";

////////////////////////////////////////

        if ($type == 1) plot_single_icon($conf, "left", "index.php?screen=messages&amp;group=$newsgroup&amp;thread=$thread&amp;art=$article");
	if ($type == 2) plot_single_icon($conf, "left", "index.php?screen=threadlist&amp;group=$newsgroup&amp;thread=$thread");

	echo "<div class=\"toolbaricons\"><input type=\"image\" alt=\"Send message\" src=\"./png/send.png\"></div>\n";

	if (strlen($thread) > 0) 
	{
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
	} else // new message
	{
		plot_single_icon($conf, "quote", "post.php?screen=messages&amp;group=$newsgroup&amp;type=$type");
		plot_single_icon($conf, "cancel", "");
	}

	echo "</div>\n";
	echo "<div class=\"endtoolbar\">&nbsp;</div>";

/////////////////////////////////////////

}


function get_sender($conf)
{
	return FALSE;
}

function post_reply($conf, $newsgroup, $thread, $article, $message, $subject, $nick, $email)
{
	$group = $conf["active"][$newsgroup];
        $xover = nntp_xover($conf, $group);		

////////////////////////////////////////

	$references = $xover[$article]["References"] . " " . $xover[$article]["Mid"];
	$date  = date("r");
        $email = str_replace(">", "", $email);
        $email = str_replace("<", "", $email);  
        $email = str_replace("\"", "", $email);
        $sender = "$nick <$email>";
	$destination_groups = nntp_get_header($conf, $conf["active"][$newsgroup], $article, "Followup-To", 1);
	if (!$destination_groups) $destination_groups = nntp_get_header($conf, $conf["active"][$newsgroup], $article, "Newsgroups", 1);

	$mexbody = explode("\n", $message);

	$usenet_body = array();	

	foreach($mexbody as $mexline)
	{
		$mexline = rtrim($mexline);
		if (preg_match("/^\./", $mexline)) $usenet_body[] = " $mexline\r\n";
		else $usenet_body[] = "$mexline\r\n"; 
	}

////////////////////////////////////////

        $fh = nntp_connect($conf["server"], $conf["port"]);
        if (!$fh) $return(0);

	fputs($fh, "POST\r\n");
	$banner = fgets($fh, 1024);
	if (!preg_match("/^340/", $banner))
	{
		show_error_string("Sending POST, server replies $banner, aborting", 1);
		fclose($fh);
		return FALSE;
	}	

	$senderip = get_sender_ip();

	fputs($fh, "From: $sender\r\n");
	fputs($fh, "Newsgroups: $destination_groups\r\n");
	fputs($fh, "Date: $date\r\n");
	fputs($fh, "X-Sender-IP: $senderip\r\n");
	fputs($fh, "Subject: $subject\r\n");
	fputs($fh, "References: $references\n");
	fputs($fh, "User-Agent: AWN v. 0.1 (https://github.com/Aioe/AWN)\r\n");
	fputs($fh, "Content-Type: text/plain; charset=UTF-8\r\n");
	fputs($fh, "\r\n"); // End of headers

	foreach($usenet_body as $mexline) fputs($fh, $mexline); // body

        fputs($fh, "\r\n.\r\n"); // end of message


        $banner = fgets($fh, 1024);
        if (!preg_match("/^240/", $banner))
	{
		show_error_string("After sending message, server replies <b>$banner</b>, aborting", 1);
		fclose($fh);
		return FALSE;
	}

	return TRUE;
}

function post_new_message($conf, $group, $message, $subject, $nick, $email)
{
	$fh = nntp_connect($conf["server"], $conf["port"]);
        if (!$fh) $return(0);

        fputs($fh, "POST\r\n");
        $banner = fgets($fh, 1024);
        if (!preg_match("/^340/", $banner))
        {
                show_error_string("Sending POST, server replies $banner, aborting", 1);
                fclose($fh);
                return FALSE;
        }

	$email = str_replace(">", "", $email);
	$email = str_replace("<", "", $email);	
	$email = str_replace("\"", "", $email);
	$sender = "$nick <$email>";

	$senderip = get_sender_ip();

	fputs($fh, "X-Sender-IP: $senderip\r\n");
	fputs($fh, "From: $sender\r\n");
	fputs($fh, "Subject: $subject\r\n");
	fputs($fh, "Newsgroups: $group\r\n");
	fputs($fh, "Content-Type: text/plain; charset=UTF-8\r\n");
	fputs($fh, "\r\n");

	$body = explode("\n", $message);

	foreach($body as $line) 
	{
		$line = rtrim($line);
		fputs($fh, "$line\r\n");
        }

	fputs($fh, "\r\n.\r\n"); // end of message

        $banner = fgets($fh, 1024);
        if (!preg_match("/^240/", $banner))
        {
                show_error_string("After sending message, server replies <b>$banner</b>, aborting", 1);
                fclose($fh);
                return FALSE;
        }

	fclose($fh);
	return TRUE;

}

function plot_reply_form($conf, $type, $newsgroup, $article, $thread, $noquote, $subject, $nick, $email, $message)
{
 	echo "<form action=\"post.php\" method=\"post\">";
        if ($article > 0) echo "<input type=\"hidden\" name=\"art\" value=\"$article\">";
        if ($thread  > 0) echo "<input type=\"hidden\" name=\"thread\" value=\"$thread\">";
        echo "
<input type=\"hidden\" name=\"group\" value=\"$newsgroup\">
<input type=\"hidden\" name=\"type\" value=\"$type\">
";
        post_toolbar($conf, $type, $newsgroup, $thread, $article, $noquote);
        echo "<div class=\"article\">\n";
        $group = $conf["active"][$newsgroup];
	$xover = nntp_xover($conf, $group);
        $subject = $xover[$article]["Subject"];
        $from = $xover[$article]["From"];
        $date = $xover[$article]["Date"];
        if ($noquote == 0)
        {
                $text_to_quote = quote_text($conf, $xover, $group, $article);
        } else if ($noquote == 1) $text_to_quote = "";
        else if ($noquote == 2) {
		$text_to_quote = quote_text($conf, $xover, $group, $article);
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

        if ((isset($subject)) and (!preg_match("/^RE: /i", $subject))) $subject = "Re: $subject";

        echo "
<div class=\"postheaders\">
        <div class=\"header\">Group</div>
        <div class=\"value\">$group</div>
</div>";

        if (isset($subject)) echo "
<div class=\"postheaders\">
        <div class=\"header\">Subject</div>
        <div class=\"value\">$subject</div>
</div>
";
        if (!isset($subject))
        {
                echo "
<fieldset>
    <legend>Subject</legend>
    <div class=\"postingident\"><input style=\"width: 95%;\" type=\"text\" name=\"subject\" value=\"my subject\"></div>
 </fieldset>\n";

        }

        if (!$sender)
        {
                echo "
<fieldset>
    <legend>Posting identity</legend>
    <div class=\"postingident\"><input style=\"width: 25%;\" type=\"text\" name=\"nick\" value=\"$nick\"> <input style=\"width: 70%;\" type=\"text\" name=\"email\" value=\"$email\"></div>
 </fieldset>\n";


        }

        echo "
<textarea name=\"message\">
$text_to_quote
</textarea>
</form></div>\n";

}

function plot_newmessage_form($conf, $type, $newsgroup, $thread, $article, $subject, $nick, $email, $noquote, $message)
{
	echo "<form action=\"post.php\" method=\"post\">";
        if ($article > 0) echo "<input type=\"hidden\" name=\"art\" value=\"$article\">";
        if ($thread  > 0) echo "<input type=\"hidden\" name=\"thread\" value=\"$thread\">";
        echo "
<input type=\"hidden\" name=\"group\" value=\"$newsgroup\">
<input type=\"hidden\" name=\"type\" value=\"$type\">
";
        post_toolbar($conf, $type, $newsgroup, $thread, $article, $noquote);
        echo "<div class=\"article\">\n";
        $group = $conf["active"][$newsgroup];
	$text_to_quote = "";
	$sender = get_sender($conf);
        if ($sender)
        {
                echo "<div class=\"postheaders\">
        <div class=\"header\">Sender</div>
        <div class=\"value\">$sender</div>
</div>";
        }

        echo "
<div class=\"postheaders\">
        <div class=\"header\">Group</div>
        <div class=\"value\">$group</div>
</div>";

        if (!empty($subject)) echo "
<div class=\"postheaders\">
        <div class=\"header\">Subject</div>
        <div class=\"value\">$subject</div>
</div>
";
        else {
                echo "
<fieldset>
    <legend>Subject</legend>
    <div class=\"postingident\"><input style=\"width: 95%;\" type=\"text\" name=\"subject\" value=\"my subject\"></div>
 </fieldset>\n";

        }

        if (!$sender)
        {
                echo "
<fieldset>
    <legend>Posting identity</legend>
    <div class=\"postingident\"><input style=\"width: 25%;\" type=\"text\" name=\"nick\" value=\"$nick\"> <input style=\"width: 70%;\" type=\"text\" name=\"email\" value=\"$email\"></div>
 </fieldset>\n";


        }

        echo "
<textarea name=\"message\">
$text_to_quote
</textarea>
</form></div>\n";

}

function quote_text($conf, $xover, $group, $article)
{
	$from = $xover[$article]["From"];
        $date = $xover[$article]["Date"];
	$text = get_nntp_body($conf, $group, $article);

	$quoted_text = "";
	$lines = explode("\n", $text);
	foreach($lines as $line)
	{
	        $quoted_text .= "> $line\n";
	}

	$quoted_text = str_replace("<br />", "", $quoted_text);
        $quote_head  = "On $date $from wrote:";
        $text_to_quote = "$quote_head\n$quoted_text\n";

	return $text_to_quote;
}

function get_sender_ip()
{
	if (isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['REMOTE_HOST'])) return $_SERVER['REMOTE_HOST'];
	if (isset($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
	return "Unknown address";
}


?>

