<?php

function nntp_xover($config, $group)
{
        $xover = array();

	$xoverfile = $config["spooldir"] . "/xover/$group";
	$xover_data = file($xoverfile);

        foreach($xover_data as $line)
        {
                if (preg_match("/^\./", $line)) break;
                $elems = explode("\t", $line );
                $num = $elems[0];
                $qpsubject      = quoted_printable_decode($elems[1]);
                $qpfrom         = quoted_printable_decode($elems[2]);

                $todelete = array("=?UTF-8?Q?", "=?ISO-8859-15?Q?", "iso-8859-1?Q?", "=?", "?=" );

                $qpsubject = str_replace($todelete, "", $qpsubject);
                $qpfrom = str_replace($todelete, "", $qpfrom);

                $qpsubject = str_replace("_", " ", $qpsubject);
                $qpfrom = str_replace("_", " ", $qpfrom);

                $xover[$num]["Subject"]         = $qpsubject;
                $xover[$num]["From"]            = $qpfrom;
                $xover[$num]["Date"]            = $elems[3];
                $old = $num;
                $time = strtotime($elems[3]);

                if ($time == 0)
                { 
                        $date = preg_replace("/\(.+\)/", "", $xover[$num]["Date"] );
                        $time = strtotime($date);
                        $xover[$num]["Date"] = $date;
                }

                $xover[$num]["Time"]            = $time;
                $xover[$num]["Mid"]             = $elems[4];
                $xover[$num]["References"]      = $elems[5];
                $xover[$num]["Size"]            = $elems[6];
                $xover[$num]["Group"]           = $group;
                $xover["Mids"][]                = $elems[4];
        }

        return $xover;
}

function get_nntp_body($config, $group, $article, $html)
{

	$file = $config["spooldir"] . "/data/$group/$article";
	$art = file($file);
	if (!$art)
	{
		show_error_string("Unable to fetch body content from file $file, aborting", 0);
		return 0;
	}
	$body = "";
	$headers = 1;
	$quote = 0;
	$swit = 0;
	$signature = 0;

	foreach($art as $line)
	{
		if ($line[0] == "\r") $headers = 0;
		if ($headers == 0)
		{
			$clean = clean_body_line($line, $config, $group, $article);
			if ($html == 1)
			{
				if (($quote == 1) and (!preg_match("/^&gt;/i", $clean)))
				{
					$quote = 0;
					$clean = "</div>$clean<br />\n";
				}
				if ((preg_match("/^&gt\;/i", $clean ))  and ($quote == 0))
				{
					if ($swit > 0) $clean = "<br><div class=\"quote\">$clean";
					else $clean = "<div class=\"quote\">$clean";
					$quote = 1;
					$swit++;
				}		
			} else $clean = "$clean\n";
			if (preg_match("/^\-\-/", $clean)) $signature = 1;
			if (($signature == 0) or (($signature == 1) and ($html == 1))) $body .= $clean;
		}
	}

	return $body;

}

function  GET_header($header)
{
        if (isset($_GET[$header])) 
	{	
		$result = $_GET[$header];
		if ($result < 0) $result = $result * -1; // sanitize
        	if ($result)
        	{
                	$res = filter_var($result, FILTER_VALIDATE_INT);
			if (is_int($res)) return $res;
			else fatal_error($header, $result);
        	}  else  return ""; 
	} else if (isset($_POST[$header])) 
        {
                $result = $_POST[$header];
		if ($result < 0) $result = $result * -1;
                if ($result)
                {
                        $res = filter_var($result, FILTER_VALIDATE_INT);
                        if (is_int($res)) return $res;
                        else fatal_error($header, $result);
                }  else  return ""; 
        } 
	else return "";
}

function nntp_connect($host, $port)
{
        $fp = fsockopen ($host, $port, $errno, $errstr, 1); 
        if (!$fp) { 
                show_error_string("Error opening socket connection with $host:$port: error nr $errno $errstr", 1);
                return FALSE;
        } 

        $welcome = fgets($fp, 1024);

        if ( !preg_match("/^200/", $welcome) )
        {
                show_error_string("Error getting greetings from server: $host:$port replies $welcome", 1);
                return FALSE;
        }

        fputs($fp, "MODE READER\r\n");
        $welcome = fgets($fp, 1024);

        if ( !preg_match("/^200/", $welcome) )
        {
                show_error_string("Error getting MODE READER greetings from server: $host:$port replies $welcome", 1);
                return FALSE;
        }

        return $fp;
}

function check_article_exist($xover, $value)
{
        foreach($xover as $num => $array)
        {
                if ($num == $value) return TRUE;
        }
        return FALSE;
}

function nntp_get_header($conf, $group, $article, $header, $html)
{
        $file = $conf["spooldir"] . "/data/" . "$group/" . "$article";
        $lines = file($file);
        if (!$lines)
        {
                show_error_string("Unable to read data from $file, which is needed to post an article", $html);
                return 0;
        }

        $headers = 1;
	$multilineheader = 0;
	$multiline = "";

        foreach($lines as $line)
        {
                if ($line[0] == "\r") $headers = 0;
		if ($multilineheader == 1)
		{
			if (preg_match("/charset/i", $line))
			{
				$multiline .= $line;
				return rtrim($multiline);
			} else $multiline .= $line;
			continue;
		}
                if ($headers == 1)
                {
                        $elems = explode(": ", $line, 2);
                        if (preg_match("/$header/i", $elems[0])) 
			{
				if ($header != "Content-Type") return rtrim($elems[1]);
				else {
					if (preg_match("/charset/i", $elems[1])) return rtrim($elems[1]);
					$multiline .= $elems[1];
					$multilineheader = 1;
					continue;
				}
			}
                }
        }

        return FALSE;
}

?>
