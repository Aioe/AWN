<?php

$conf["spooldir"] = "/var/www/html/spool/";
$conf["home"]   = "https://news.aioe.org/";
$conf["base"]   = "/";
$conf["start"]  = "-500";
$conf["server"] = "127.0.0.1";
$conf["port"]   = 119;
$conf["active"] = array(
                "",
                "aioe.news.assistenza",
                "aioe.news.helpdesk",
                "aioe.test",
                "it.discussioni.auto",
		"it.comp.console",
		"it.hobby.fai-da-te",
		"it.hobby.viaggi"
                );

$conf["colors"]["background"] = array(
					(86400*4) => "#fff", 
					(86400*2) => "#CCFF99",
					 86400    => "#D1FA94",
					 43200    => "#D9F28C",
					(3600*8)  => "#E0EB85",
					(3600*6)  => "#E8E37D",
					(3600*4)  => "#F0DB75",
                                        (3600*3)  => "#F5D670",
					(3600*2)  => "#FAD16B",
					  3600    => "#FFCC66",

				    );


$conf["colors"]["border"] = array( 
                                        (86400*4) => "#000066", 
                                        (86400*2) => "#00FF00",
                                         86400    => "#1AE600",
                                         43200    => "#33CC00",
                                        (3600*8)  => "#4CB200",
                                        (3600*6)  => "#669900",
                                        (3600*4)  => "#8C7300",
                                        (3600*3)  => "#A65900",
                                        (3600*2)  => "#CC3300",
                                          3600    => "#F20D00",

				    );
?>
