<?php

include("lib/newsagent.php");
include("lib/init.php");
include("lib/backend.php");
include("lib/style.php");

$conf = init_awn();

check_nntp($conf);
?>
