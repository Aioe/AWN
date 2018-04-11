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

function get_nntp_body($conf, $group, $article, $html, $format)
{
	$file = $conf["spooldir"] . "/data/$group/$article";
	$art = file($file);
	if (!$art)
	{
		show_error_string("Unable to fetch body content from file $file, aborting", 0);
		return 0;
	} 

	$body = "";
	$headers = 1;

	foreach($art as $line)
	{
		if ($line[0] == "\r") 
                {
                        $headers = 0;
                        continue;
                }
                if ($headers == 0)
                {
			if (preg_match("/=\r\n$/", $line)) $line = str_replace("=\r\n", "", $line);
			$body .= $line;

		}	
	}

        if ($format == 1) {
                $lines = explode("\r\n", $body);
                $body = "";
                $rem = 0;
                foreach($lines as $output)
                { 
                        if ($output[0] == ">") 
                        {       
                                $output = "";
                                if ($rem == 0) $rem = 1;
                        } else $rem = 0;
                        if ($rem == 1) 
                        {
                                $output = "[Quoted Text Removed]";
                                $rem = 2;
                        }

			if ($rem == 0) $output = "$output\r\n";
			$body .= "$output";
                }
        }

        if ($html == 0)
        {
                $lines = explode("\r\n", $body);
                $body = "";
                $signature = 0;
                foreach($lines as $output)
                {
                        if (preg_match("/^--/", $output)) $signature = 1;
                        if ($signature == 1) $output = "";
			if (strlen($output) > 0) $body .= "$output\n";
                }
        }


	if (($html == 1) and ($format == 0))
        {
                $lines = explode("\r\n", $body);
		$body = "";
		$signature = 0;
                foreach($lines as $output)
                {
                        $quote = 0;
                        $string = "";

			if (($quote == 0) and ($output[0] == ">") and (strlen($output) < 4)) continue;

                        for ($n = 0; $n < strlen($output); $n++)
                        {
                                if ($output[$n] == ">") $quote++;
                                if (($output[$n] != " ") and ($output[$n] != ">")) break;
                        }
                        for ($x = 0; $x != $quote; $x++)
			{
				$output = preg_replace("/>/", "", $output);
                	}
		        if ($quote > $quotelevel)
                        {
                                $diff = $quote - $quotelevel;
                                for ($quotelevel; $quotelevel != $quote; $quotelevel++) $output = "STARTDIVSTYLEQUOTE$output";
                        }
                        if ($quote < $quotelevel)
                        {
                                for ($quotelevel; $quotelevel != $quote; $quotelevel--) $output = "ENDDIVSTYLEQUOTE$output";
                        } else {
				if ($quote == 0) $output = "$output\r\n";
				else {
					if (strlen($output) > 0) $output = "$output\r\n";
				}		
			}
			if (preg_match("/^--/", $output))
			{
				$output = "STARTDIVSTYLESIGNATURE$output";
				$signature = 1;
			}			
			if ($signature == 1) $body .= "$output\r\n";
			else $body .= "$output ";                    
                }
		if ($signature == 1) $body .= "ENDDIVSTYLESIGNATURE";
	}	

	$charset = "ISO8859-15"; // Default
	$ct =  nntp_get_header($conf, $group, $article, "Content-Type", 1);
	$ct = str_replace("\"", "", $ct);
	$ce =  nntp_get_header($conf, $group, $article, "Content-Transfer-Encoding", 1);
	
	if ($format == 2)
	{
		$body = "";
		$headers = 1;
		$ref = 0;
		foreach ($art as $line) 
		{
			if ((preg_match("/^\r\n$/", $line)) and ($headers == 1))
			{
				$headers = 0;
				$body .= "</div><hr />";
			}
			if ($headers == 1)
			{
				if (preg_match("/^([a-z0-9\-]+):\ (.+)/i", $line, $match))
				{
					$header = htmlentities($match[1], ENT_SUBSTITUTE, $charset);
					$value  = htmlentities($match[2], ENT_SUBSTITUTE, $charset);
					$line 	= "</div><br /><div class=\"header\">$header: </div><div class=\"value\"> $value"; 
				} else 	$line = htmlentities($line, ENT_SUBSTITUTE, $charset);
				$body .= $line;
			} else $body .= htmlentities($line, ENT_SUBSTITUTE, $charset) . "<br />";
		}
		return $body;
	}

	$flowed = 0;
	if (preg_match("/format=flowed/i", $ct)) $flowed = 1;

        if (preg_match("/charset=([a-z0-9\-]+)/i", $ct, $match)) $charset = trim($match[1]);
        if (preg_match("/format=flowed/i", $ct)) $flowed = 1;
        $charset = strtoupper($charset);      

	if ((!preg_match("/quoted\-printable/i", $ce)) and  (!preg_match("/base64/i", $ce)))
	{
		$body = htmlentities($body, ENT_SUBSTITUTE, $charset);
	} else if (preg_match("/quoted\-printable/i", $ce)) {
		$body = quoted_printable_decode($body);
		$body = htmlentities($body, ENT_SUBSTITUTE, $charset);
	}  else if (preg_match("/base64/i", $ce)) {
		$body = base64_decode($body);
                $body = htmlentities($body, ENT_SUBSTITUTE, $charset);
	}

	if ($html == 1) 
	{
		$body = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\-\#\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $body);
		$body = preg_replace('@(\*[\w_\-\#]+\*)@', '<b>$1</b>', $body );
	}
	if ($flowed == 1)
	{
		$lines = explode("\r\n", $body);
		$body 		= "";
		$cr		= 0;
		foreach($lines as $line)
		{
			if (preg_match("/\ $/", $line)) $cr = 1;
			else $cr = 0;
			if (preg_match("/&gt;/i", $line)) $line = "$line<br />";
			else if ($cr == 0) $line = "$line<br />";
			$body .= "$line";
		}
	}

	if ($html == 1)
	{
	        $body = str_replace("STARTDIVSTYLEQUOTE", "<div class=\"quote\">", $body);
                $body = str_replace("ENDDIVSTYLEQUOTE", "</div>", $body);
                $body = str_replace("STARTDIVSTYLESIGNATURE", "<div class=\"signature\">", $body);
                $body = str_replace("ENDDIVSTYLESIGNATURE", "</div>", $body);
		$body = str_replace("[Quoted Text Removed]", "<div class=\"textremoved\">[Quoted Text Removed]</div>", $body);

	}

	if ($format == 1)
	{
		$body = str_replace("<br /><br /><br />", "<br />", $body);
		$body = str_replace("<br /><br />", "<br />", $body);
	}
	if ($flowed == 0) $body = str_replace("\r\n", "<br />\n", $body);
	return "$body";

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
                show_error_string("Unable to read data from $file", $html);
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
			if (preg_match("/:\ /i", $line))
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
