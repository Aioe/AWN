<?php
function init_awn()
{
	include("config.php");

	if (!isset($_SERVER['PHP_AUTH_USER'])) $activefile = $conf["homedir"] . "/Defaults/active";
	if ( isset($_SERVER['PHP_AUTH_USER'])) $activefile = $conf["homedir"] . "/".  $_SERVER['PHP_AUTH_USER'] . "/active";        

	$active = file($activefile);
       	if ((!$active) and (!isset($_SERVER['PHP_AUTH_USER'])))
       	{
		$conf["active"] = array( "", "");		
        } 

	for ($x = 0; $x < count($active); $x++) $conf["active"][($x+1)] = trim($active[$x]);

	if ( isset($_SERVER['PHP_AUTH_USER'])) $conf["User"] = $_SERVER['PHP_AUTH_USER'];
	

	return $conf;
}

function subscribe_groups($conf, $format, $subscribe, $unsubscribe)
{

	if ( isset($conf["User"])) $activefile = $conf["homedir"] . "/" . $conf["User"] . "/active";
	if (!isset($conf["User"])) return FALSE;

	plot_toolbar(0, $conf, "subscribe", 0, 0, 0, 0);

	$active = file($activefile);
	if (!isset($active)) $active = array("hh");

	$totalactivefile = $conf["homedir"] . "/Defaults/active";
	$total = file($totalactivefile);
	if (!$total)
	{
		show_error_string("Unable to open general active file: $totalactivefile!", 1);
	}


        if ($subscribe != 0)
        { 
                $success = subgroup($conf, trim($total[($subscribe-1)]));
		$active = file($activefile);
		if (!$active) $active = array("");
        }

	if ($unsubscribe != 0)
	{
		$success = unsubgroup($conf, trim($total[($unsubscribe-1)]));
		$active = file($activefile);
                if (!$active) $active = array("");
	}
	
	$id = 0;

	echo "<dl><dt class=\"titolo\">Available groups</dt>\n";


	foreach($total as $group)
	{
		$id++;
		$sel = 0;
		$group = trim($group);
		$url = "?screen=subscribe&amp;format=$format&amp;";


		foreach($active as $subgroup) 
		{
			$subgroup = trim($subgroup);
			if ($subgroup == $group)
			{
				$htmlclass = "subgroupselected";
				$url .= "unsubscribe=$id";
				$sel = 1;
				break;
			} 
		}

		if ($sel == 0)
		{
			$url .= "subscribe=$id";
			$htmlclass = "subgroupunselected";
		}
		echo "<dd class=\"$htmlclass\"><a href=\"$url\">$group</a></dd>\n";
	}
	
	echo "</dl>";
	

}


function subgroup($conf, $group)
{
	$file = $conf["homedir"] . "/" . $conf["User"] . "/active";

	$fh = fopen($file, "a+");
	fputs($fh, "$group\n");
	fclose($fh);

}

function unsubgroup($conf, $group)
{
	$file = $conf["homedir"] . "/" . $conf["User"] . "/active";
	$active = file($file);
	$fh = fopen($file, "w");
	
	foreach($active as $oldgroup)
	{
		$oldgroup = trim($oldgroup);
		if ($group != $oldgroup) fputs($fh, "$oldgroup\n");
	}

	fclose($fh);
}


?>
