
# AWN v. 0.1 (ALPHA RELEASE)

AWN is a simple web based newsreader written in php5 with no other dependancies and designed for smartphones with tight screens. 


Due security reasons, mostly in order to avoid floods, the main script doesn't directly query the remote news server but reads all data that needs from a file based spool managed by getnews.php, 
another script included in the source code. 

Those who wish to test AWN should follow this checklist:

* Download the full sources
* change files owner
* edit config.php
1. server
2. port
3. base (urlbase ie if script runs at  https://news.aioe.org/nr/ base is "/nr/"
4. spooldir
5. active (list of groups)

* run in a terminal 'php getnews.php' (only the first time is needed to execute it by hands)

Those who need help can send a message to aioe.helpdesk (an USENET group) or by mail to estasi@aioe.org

