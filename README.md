# AWN

AWN is a simple, experimental, web newsreader optimized for mobile terminals with tight screens. 
Code requires only php5 at the moment: no SQL or javascript are supported.
Due security reasons, main script doen't connect the remote news server but reads the data from a local spool populated by newsagent.php that must be executed by cron on regular basis. 
