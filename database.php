<?php

// This file adds a little bit of a wrapper over sqlite3, and a little
//  bit of convenience functionality.

require_once 'localcfg.php';

$GDebugSQL = false;
$GDebugSQLErrors = true;
$dblink = NULL;

function write_error($err)
{
    global $GDebugSQLErrors;
    if ($GDebugSQLErrors)
        print("\n\nERROR: $err\n\n");
} // write_error

function write_debug($err)
{
    global $GDebugSQL;
    if ($GDebugSQL)
        print("\n\nDEBUG: $err\n\n");
} // write_debug

function get_dblink()
{
    global $dblink;

    if ($dblink == NULL)
    {
        global $dbfile;
        $dblink = new SQLite3($dbfile, SQLITE3_OPEN_READWRITE);
        if ($dblink == false)
        {
            $err = "please try again later";
            write_error("Failed to open database link: ${err}.");
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
        $closeme->close();
} // close_dblink


function db_escape_string($str)
{
    global $dblink;
    return("'" . $dblink->escapeString($str) . "'");
} // db_escape_string


function do_dbquery($sql, $link = NULL, $suppress_output = false)
{
    if ($link == NULL)
        $link = get_dblink();

    if ($link == NULL)
        return(false);

    if (!$suppress_output)
        write_debug("SQL query: [$sql;]");

    $rc = $link->query($sql);
    if ($rc == false)
    {
        if (!$suppress_output)
        {
            $err = $link->lastErrorMsg();
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

    $rc = $link->query($sql);
    if ($rc == false)
    {
        $err = $link->lastErrorMsg();
        $upperverb = strtoupper($verb);
        write_error("Problem in $upperverb statement: {$err}");
        return(false);
    } // if

    $retval = db_num_rows($rc);
/*
    if (($expected_rows >= 0) and ($retval != $expected_rows))
    {
        $err = $link->lastErrorMsg();
        write_error("Database $verb error: {$err}");
    } // if
*/

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


function db_num_rows($rows)
{
    $nrows = 0;
    $rows->reset();
    while ($rows->fetchArray())
        $nrows++;
    $rows->reset();
    return $nrows;
} // db_num_rows


function db_reset_array($query)
{
    $query->reset();
} // db_reset_array


function db_fetch_array($query)
{
    return $query->fetchArray();
} // db_fetch_array


function db_free_result($query)
{
    return($query->finalize());
} // db_free_result

?>
