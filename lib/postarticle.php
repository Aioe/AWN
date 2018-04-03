<?php

include("config.php");
include("style.php");
include("backend.php");
include("toolbar.php");
include("newsagent.php");

///////////////////////////////////////////////////////////////////////////

if (isset($_POST["message"])) 	$message = $_POST["message"];
if (isset($_POST["nick"])) 	$nick = $_POST["nick"];
if (isset($_POST["email"])) 	$email = $_POST["email"];
if (isset($_POST["subject"])) 	$subject = $_POST["subject"];

if (!isset($nick)) $nick = "Nomen Nescio";
if (!isset($email)) $email = "&lt;fake@null.invalid&gt;";

$newsgroup      = GET_header("group");
$type           = GET_header("type");

if ($type == 1)
{
	$thread         = GET_header("thread");
	$article        = GET_header("art");
	$noquote 	= GET_header("noquote");
}

if ($type == 2)
{
	$thread = 0;
	$article = 0;
	$noquote = 0;
}

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
		$subject = nntp_get_header($conf, $conf["active"][$newsgroup], $article, "Subject");
		if (!preg_match("/Re:/i", $subject)) $subject = "Re: $subject";
		if (strlen($message) > 0) 
		{
			$output = post_reply($conf, $newsgroup, $thread, $article, $message, $subject, $nick, $email);
			if ($output == TRUE)
                        {
                                check_nntp($conf);
                                $url = $conf["home"] . $conf["base"] . "index.php?screen=threadlist&group=$newsgroup";
                                header("Location: $url");
                        }
		}
	}
}


if ($type == 2)
{
	if ($send > 0)
	{
		if (strlen($message) > 0) 
		{	$output = post_new_message($conf, $conf["active"][$newsgroup], $message, $subject, $nick, $email);
			if ($output == TRUE)
			{
				check_nntp($conf);
				$url = $conf["home"] . $conf["base"] . "index.php?screen=tree&group=$newsgroup&thread=thread&article=$article";
				header("Location: $url");
			}		
		}
                //////////////////////////////////////////
	}

}


print_html_head();

if (($type == 1) or ($type == 2))
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
}


if ($type == 1)
{
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

}

if ($type == 2) $text_to_quote = "";

if (($type == 1) or ($type == 2))
{	
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


	if (!isset($sender))
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

print_html_tail();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function post_toolbar($conf, $type, $newsgroup, $thread, $article, $noquote)
{
	echo "<div class=\"top\">\n";

////////////////////////////////////////

        plot_single_icon($conf, "left", "index.php?screen=messages&amp;group=$newsgroup&amp;thread=$thread&amp;art=$article");

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
	$mail  = str_replace("&gt;", ">", $email);
	$mail  = str_replace("&lt;", "z", $mail);
	$sender =  trim($nick) . " " . trim($mail);
	$destination_groups = nntp_get_header($conf, $conf["active"][$newsgroup], $article, "Followup-To");
	if (!$destination_groups) $destination_groups = nntp_get_header($conf, $conf["active"][$newsgroup], $article, "Newsgroups");
	
	$mexbody = explode("\n", $message);

	foreach($mexbody as $mexline)
	{
		$mexline = rtrim($mexline);
		if ($mexline[0] == ".") $usenet_body .= " $mexline\r\n";
		else $usenet_body[] = "$mexline\r\n"; 
	}


////////////////////////////////////////

        $fh = nntp_connect($conf["server"], $conf["port"]);
        if (!$fh) $return(0);

	fputs($fh, "POST\r\n");
	$banner = fgets($fh, 1024);
	if (!preg_match("/^340/", $banner))
	{
		show_error_string("Sending POST, server replies $banner, aborting");
		fclose($fh);
		return FALSE;
	}	
	fputs($fh, "From: $sender\r\n");
	fputs($fh, "Newsgroups: $destination_groups\r\n");
	fputs($fh, "Date: $date\r\n");
	fputs($fh, "Subject: rrrrr\r\n");
	fputs($fh, "References: $references\n");
	fputs($fh, "User-Agent: AWN v. 0.1 (https://github.com/Aioe/AWN)\r\n");
	fputs($fh, "\r\n"); // End of headers

	foreach($usenet_body as $mexline) fputs($fh, $mexline); // body

        fputs($fh, "\r\n.\r\n"); // end of message


        $banner = fgets($fh, 1024);
        if (!preg_match("/^240/", $banner))
	{
		show_error_string("After sending message, server replies <b>$banner</b>, aborting");
		fclose($fh);
		return FALSE;
	}

	return TRUE;
}

function nntp_get_header($conf, $group, $article, $header)
{
	$file = $conf["spooldir"] . "/data/" . "$group/" . "$article";
	$lines = file($file);
	if (!$lines)
	{
		show_error_string("Unable to read data from $file, which is needed to post an article");
		return 0;
	}

	$headers = 1;
	foreach($lines as $line)
        {
                if ($line[0] == "\r") $headers = 0;
                if ($headers == 1)
                {
			$elems = explode(": ", $line, 2);
			if (preg_match("/$header/i", $elems[0])) return rtrim($elems[1]);
		}
	}

	fclose($fh);

	return FALSE;
}

function post_new_message($conf, $group, $message, $subject, $nick, $email)
{
	$fh = nntp_connect($conf["server"], $conf["port"]);
        if (!$fh) $return(0);

        fputs($fh, "POST\r\n");
        $banner = fgets($fh, 1024);
        if (!preg_match("/^340/", $banner))
        {
                show_error_string("Sending POST, server replies $banner, aborting");
                fclose($fh);
                return FALSE;
        }

	$email = str_replace(">", "", $email);
	$email = str_replace("<", "", $email);	
	$email = str_replace("\"", "", $email);
	$sender = "$nick <$email>";

	fputs($fh, "From: $sender\r\n");
	fputs($fh, "Subject: $subject\r\n");
	fputs($fh, "Newsgroups: $group\r\n");
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
                show_error_string("After sending message, server replies <b>$banner</b>, aborting");
                fclose($fh);
                return FALSE;
        }

	fclose($fh);
	return TRUE;

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

