
# AWN v. 0.1 (ALPHA RELEASE -- DO NOT USE IT YET)

AWN is a web based newsreader, a reader for USENET newsgroups,  written in php5 (>= 5.1.0) and designed for smartphones with tight screens. 
AWN doesn't make use of SQL, Javascript and cookies. It's released under the GPLv2 license.

At the current state of its development AWN is still an alpha release: the main functions work roughly and many additional features are missing. 
For this reason it is dangerous at the moment to use AWN on production hosts.

Due security reasons, mostly in order to avoid that abusers flood remote news servers, the main script doesn't directly query the remote news server 
but reads all data that is needed from a file based spool managed by getnews.php, another script included in the source code. 

Those who wish to test AWN should follow this checklist:

* Download the full sources
* change files owner
* edit config.php
1. server
2. port
3. base (urlbase ie if script runs at  https://news.aioe.org/nr/ base is "/nr/"
4. spooldir
5. active (list of groups)

* create directories
1. ./spool/
2. ./spool/xover
3. ./spool/data  

* run in a terminal 'php getnews.php' (only the first time is needed to execute it by hands)

Those who need help can send a message to aioe.helpdesk (an USENET group) or by mail to estasi@aioe.org

