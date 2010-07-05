<?php

require_once 'database.php';

// !!! FIXME: absolutely don't let this script run on a production site!

if (isset($_SERVER['REMOTE_ADDR']))
{
    header('Content-type: text/plain; charset=UTF-8');
    print("\n\nThis isn't allowed over the web anymore.\n\n");
    exit(0);
} // if

if ((!isset($argv[1])) || ($argv[1] != '--confirm'))
{
    echo "You have to run this with --confirm to do anything.\n";
    echo "...BECAUSE DOING SO DESTROYS ANY EXISTING DATABASE!!!\n";
    exit(0);
} // if


echo "Nuking any existing database (too late to go back, now!)...\n";
do_dbquery("drop database if exists $dbname");

echo "Creating new database from scratch...\n";
do_dbquery("create database $dbname");

close_dblink();  // force it to reselect the new database.

echo "Building gametags table...\n";
do_dbquery(
    "create table gametags (" .
        " id int unsigned not null auto_increment," .
        " steamid bigint not null," .
        " appid int unsigned not null," .
        " tag varchar(64) not null," .
        " ipaddr unsigned int not null," .
        " posted datetime not null," .
        " deleted datetime default null," .
        " deletedipaddr unsigned int default null," .
        " index gametag_index (steamid, appid)," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "...all done!\n\n";

echo "If there were no errors, you're good to go.\n";
echo "\n\n\n";

?>
