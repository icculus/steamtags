<?php
header('Content-type: application/xml; charset=UTF-8');

require_once('database.php');

// Mainline...

session_start();

$failed = false;

if (!isset($_REQUEST['appid']))
    $failed = true;
else if (!isset($_REQUEST['tags']))
    $failed = true;
else if (!isset($_SESSION['steamid']))
    $failed = true;
else if (!isset($_SERVER['REMOTE_ADDR']))
    $failed = true;

$ipaddr = 0;
if (!$failed)
    $ipaddr = ip2long($_SERVER['REMOTE_ADDR']);

$steamid = '';
if (!$failed)
{
    $steamid = $_SESSION['steamid'];
    $failed = (!preg_match('/^[0-9]+$/', $steamid));
} // if

$appid = '';
if (!$failed)
{
    $appid = $_REQUEST['appid'];
    $failed = (!preg_match('/^[0-9]+$/', $appid));
} // if

$tagstr = '';
if (!$failed)
{
    $tagstr = $_REQUEST['tags'];
    $failed = (!preg_match('/^[a-z0-9 ]*$/', $tagstr));
} // if

// split into an array, and then into a hashtable to kill duplicates.
$tags = explode(' ', $tagstr);
$tagshash = array();
foreach ($tags as $t)
{
    $str = trim($t);
    if ($str != '')
        $tagshash[$str] = true;
} // foreach
$tags = $tagshash;
unset($tagshash);


// !!! FIXME: This is all kinds of nasty. And a race condition.

$existing = array();
$insert = array();
$remove = array();
if (!$failed)
{
    $sql = "select id,tag from gametags where steamid=$steamid" .
           " and appid=$appid and deleted is null";
    $query = do_dbquery($sql);
    if ($query === false)
        $failed = true;
    else
    {
        while ( ($row = db_fetch_array($query)) != false )
        {
            if (isset($existing[$row['tag']]))
                $remove[] = $existing[$row['tag']];  // whoops, duplicate. Kill it.
            $existing[$row['tag']] = $row['id'];
        } // while
        db_free_result($query);
    } // else
} // if

if (!$failed)
{
    foreach ($tags as $t => $unused)
    {
        if (!isset($existing[$t]))
            $insert[] = $t;
    } // foreach

    foreach ($existing as $e => $id)
    {
        if (!isset($tags[$e]))
            $remove[] = $id;
    } // foreach
} // if

if (!$failed && (count($remove) > 0))
{
    $sql = "update gametags set deleted=date('now'), deletedipaddr=$ipaddr where" .
           " steamid=$steamid and appid=$appid and deleted is null and (";
    $or = '';
    foreach ($remove as $id)
    {
        $sql .= "${or}id=$id";
        $or = ' or ';
    } // foreach
    $sql .= ')';

    if (do_dbupdate($sql, -1) === false)
        $failed = true;
} // if

if (!$failed && (count($insert) > 0))
{
    $sql = "insert into gametags (steamid, appid, tag, ipaddr, posted) values";
    $comma = '';
    foreach ($insert as $t)
    {
        $sqltag = db_escape_string($t);
        $sql .= "$comma ($steamid, $appid, $sqltag, $ipaddr, date('now'))";
        $comma = ',';
    } // foreach

    if (do_dbinsert($sql, count($insert)) === false)
        $failed = true;
} // if

$result = $failed ? '0' : '1';

print('<savetags>');
    print("<result>$result</result>");
    print("<appid>$appid</appid>");
print('</savetags>');

exit(0);

?>

