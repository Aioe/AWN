<?php

$conf["spooldir"] = "/var/www/html/spool";
$conf["etcdir"]	= "/var/www/html/etc/";
$conf["home"]   = "http://5.9.252.135";
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
		"it.hobby.viaggi",
		"it.cultura.linguistica.italiano",
		"it.politica"
                );

$conf["colors"]["background"] = array(
					(86400*15) => "#99FF66",
					(86400*10) => "#9EFC66",
					(86400*8) => "#A3FA66",
					(86400*7) => "#A8F766",
					(86400*6) => "#ADF566",
					(86400*5) => "#B2F266",
					(86400*4) => "#B8F066",
					(86400*3) => "#BDED66",
					(86400*2) => "#C2EB66",
					 86400    => "#C7E866",
					(3600*16) => "#CCE666",
					 43200    => "#D1E366",
					(3600*8)  => "#D6E066",
					(3600*6)  => "#DBDE66",
					(3600*4)  => "#E0DB66",
                                        (3600*3)  => "#E6D966",
					(3600*2)  => "#EBD666",
					  3600    => "#F0D466",
					  1800    => "#F5D166",
					   900	  => "#FACF66",
					   600	  => "#FFCC66"
				    );

$conf["colors"]["border"] = array( 
                                        (86400*15) => "#00CC00",
                                        (86400*10) => "#0DC400",
                                        (86400*8) => "#1ABD00",
                                        (86400*7) => "#26B500",
                                        (86400*6) => "#33AD00",
                                        (86400*5) => "#40A600",
                                        (86400*4) => "#4C9E00",
                                        (86400*3) => "#599600",
                                        (86400*2) => "#668F00",
                                         86400    => "#738700",
                                        (3600*16) => "#808000",
                                         43200    => "#8C7800",
                                        (3600*8)  => "#997000",
                                        (3600*6)  => "#A66900",
                                        (3600*4)  => "#B26100",
                                        (3600*3)  => "#BF5900",
                                        (3600*2)  => "#CC5200",
                                          3600    => "#D94A00",
                                          1800    => "#E64200",
                                           900    => "#F23B00",
                                           600    => "#FF3300"
				    );
?>
