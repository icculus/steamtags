<?php

// This file adds a little bit of a wrapper over MySQL, and a little
//  bit of convenience functionality.

require_once 'dbpass.php';

$dblink = NULL;

function write_error($err)
{
    //print("\n\n$err\n\n");
} // write_error

function write_debug($err)
{
    //print("\n\n$err\n\n");
} // write_debug

function get_dblink()
{
    global $dblink;

    if ($dblink == NULL)
    {
        global $dbhost, $dbuser, $dbpass, $dbname;
        $dblink = mysql_connect($dbhost, $dbuser, $dbpass);
        if (!$dblink)
        {
            $err = mysql_error();
            write_error("Failed to open database link: ${err}.");
            $dblink = NULL;
        } // if

        if (!mysql_select_db($dbname))
        {
            $err = mysql_error();
            write_error("Failed to select database: ${err}.");
            mysql_close($dblink);
            $dblink = NULL;
        } // if
    } // if

    return($dblink);
} // get_dblink


function close_dblink($link = NULL)
{
    global $dblink;

    $closeme = $link;
    if ($closeme == NULL)
    {
        $closeme = $dblink;
        $dblink = NULL;
    } // if

    if ($closeme != NULL)
        mysql_close($closeme);
} // close_dblink


function db_escape_string($str)
{
    return("'" . mysql_escape_string($str) . "'");
} // db_escape_string


function do_dbquery($sql, $link = NULL, $suppress_output = false)
{
    if ($link == NULL)
        $link = get_dblink();

    if ($link == NULL)
        return(false);

    if (!$suppress_output)
        write_debug("SQL query: [$sql;]");

    $rc = mysql_query($sql, $link);
    if ($rc == false)
    {
        if (!$suppress_output)
        {
            $err = mysql_error();
            write_error("Problem in SELECT statement: {$err}");
        } // if
        return(false);
    } // if

    return($rc);
} // do_dbquery


function do_dbwrite($sql, $verb, $expected_rows = 1, $link = NULL)
{
    if ($link == NULL)
        $link = get_dblink();

    if ($link == NULL)
        return(false);

    write_debug("SQL $verb: [$sql;]");

    $rc = mysql_query($sql, $link);
    if ($rc == false)
    {
        $err = mysql_error();
        $upperverb = strtoupper($verb);
        write_error("Problem in $upperverb statement: {$err}");
        return(false);
    } // if

    $retval = mysql_affected_rows($link);
    if (($expected_rows >= 0) and ($retval != $expected_rows))
    {
        $err = mysql_error();
        write_error("Database $verb error: {$err}");
    } // if

    return($retval);
} // do_dbwrite


function do_dbinsert($sql, $expected_rows = 1, $link = NULL)
{
    return(do_dbwrite($sql, 'insert', $expected_rows, $link));
} // do_dbinsert


function do_dbupdate($sql, $expected_rows = 1, $link = NULL)
{
    return(do_dbwrite($sql, 'update', $expected_rows, $link));
} // do_dbupdate


function do_dbdelete($sql, $expected_rows = 1, $link = NULL)
{
    return(do_dbwrite($sql, 'delete', $expected_rows, $link));
} // do_dbdelete


function db_num_rows($query)
{
    return(mysql_num_rows($query));
} // db_num_rows


function db_reset_array($query)
{
    return(mysql_data_seek($query, 0));
} // db_reset_array


function db_fetch_array($query)
{
    return(mysql_fetch_assoc($query));
} // db_fetch_array


function db_free_result($query)
{
    return(mysql_free_result($query));
} // db_free_result

?>
