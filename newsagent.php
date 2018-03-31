<?php
include("/var/www/html/config.php");

$fh = nntp_connect($conf["server"], $conf["port"]);

$start = $conf["start"];
$spooldir = $conf["spooldir"];

foreach($conf["active"] as $group)
{
	if (strlen($group) == 0) continue;
	$retgroup 	= get_nntp_group($fh, $group, $start);
	if (!$retgroup) return(0);
	$xover 		= get_xover_data($fh, $retgroup[0], $retgroup[1]);
	$success 	= save_xover_data($group, $xover, $conf["spooldir"]);
	if ($success == FALSE) return 0;
	$success	= download_articles($fh, $group, $xover, $conf["spooldir"]);
}


function download_articles($fh, $group, $xover, $spooldir)
{
	$path = "$spooldir/data/$group/";
	if (!file_exists($path)) mkdir($path);
	$articles = scandir($path);
	$xoverspool = array();

	foreach($xover as $line)
	{
		$elems = explode("\t", $line);
		$xoverspool[] = $elems[0];
	}

// Expire, se un articolo è salvato in file ma non è nell'overview va cancellato
	foreach( $articles as $spoolfile) if (!array_search($spoolfile, $xoverspool) and ($spoolfile[0] != ".")) unlink("$path/$spoolfile");

// Download new articles
	foreach( $xoverspool as $newarticle)
	{
		if (!array_search($newarticle, $articles))
		{
			$success = get_nntp_article($fh, $path, $group, $newarticle);
//			if ($success == FALSE) return FALSE;
		}
	}
	return TRUE;
}

function get_nntp_article($fh, $path, $group, $article)
{
	fputs($fh, "ARTICLE $article\r\n");
	$banner = fgets($fh, 1024);
	if (!preg_match("/^220/", $banner))
	{
		show_error_string("Sending ARTICLE $article server replies $banner\n");
		return FALSE;
	}	
	$art = "";
        while (1)
        {
                $line = fgets($fh, 1000000);
		if (($line[0] == ".") and (strlen($line) == 3)) break;
                $art .= $line;
        }
	$file = "$path/$article";
	$fg = fopen($file, "w+");
	fwrite($fg, $art);
	fclose($fg);
	return TRUE;
}


function save_xover_data($group, $xover, $spooldir)
{
	$path = "$spooldir/xover/$group";
	touch($path);

	$fg = fopen($path, "w+");
	if (!$fg)
	{
		show_error_string("Unable to open $path\n");
		return FALSE;
	}

	foreach($xover as $line) fwrite($fg, $line);
	fclose($fg);
	return TRUE;
} 


function get_xover_data($fh, $min, $max)
{
	$xover_command = "XOVER $min-$max\r\n";
        fputs($fh, $xover_command);
        $banner = fgets($fh, 1024);
        if (!preg_match("/^224/", $banner))
        {
                show_error_string("Error getting XOVER $min- output: server replies $banner");
		exit(5);
        }
        $xover = array();
        while (1)
        {
                $line = fgets($fh, 8164);
                if ($line[0] == ".") break;
		$xover[] = $line;
        }
	return $xover;
}


function get_nntp_group($fh, $group, $start)
{
	if ($start < 0) $start = $start * -1;
        fputs($fh, "GROUP $group\r\n");
        $banner = fgets($fh, 1024);
        if (!preg_match("/^211/", $banner))
        {
                show_error_string("Error getting GROUP $group output: server replies $banner");
                return FALSE;
        }
        $elems = explode(" ", $banner );
        $max = $elems[3];
        $min = $elems[2];

	$tot = $max - $min;
	if ($tot > $start) $min = $max - $start;

	return array($min,$max);

}



function show_error_string($string)
{
	echo $string;
}


function nntp_connect($host, $port)
{
        $fp = fsockopen ($host, $port, $errno, $errstr, 1); 
        if (!$fp) { 
                show_error_string("Error opening socket connection with $host:$port: error nr $errno $errstr");
                return FALSE;
        } 

        $welcome = fgets($fp, 1024);

        if ( !preg_match("/^200/", $welcome) )
        {
                show_error_string("Error getting greetings from server: $host:$port replies $welcome");
                return FALSE;
        }

        fputs($fp, "MODE READER\r\n");
        $welcome = fgets($fp, 1024);

        if ( !preg_match("/^200/", $welcome) )
        {
                show_error_string("Error getting MODE READER greetings from server: $host:$port replies $welcome");
                return FALSE;
        }

        return $fp;
}
?>
