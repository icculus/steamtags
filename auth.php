<?php

header("HTTP/1.0 307 Temporary redirect");
header('Content-type: text/plain; charset=UTF-8');

session_start();

if (isset($_GET['logout']))
{
    header('Location: /');
    unset($_SESSION['steamid']);
    session_unset();
    session_destroy();
    print("We have now forgotten your SteamID.\n");
    print("You'll have to log in to use this site again.\n\n");
    exit(0);
} // if

if (isset($_SESSION['steamid']))
{
    // Already logged in.
    header('Location: /');
    exit(0);
} // if

require_once('openid.php');

if (!isset($_GET['openid_mode']))
{
    $url = 'http://steamcommunity.com/openid';
    try
    {
        $openid = new LightOpenID;
        $openid->identity = $url;
        header('Location: ' . $openid->authUrl());
        print("Redirecting you to Steam Community OpenID provider:\n");
        print("    $url\n\n");
    } // try
    catch (ErrorException $e)
    {
        $msg = $e->getMessage();
        print("OpenID library threw an exception:\n   $msg\n\n");
        header('Location: /');
    } // catch
    exit(0);
} // if

header('Location: /');  // always going here now.

if ($_GET['openid_mode'] == 'cancel')
    print("User has canceled authentication!\n\n");
else
{
    $okay = false;
    try
    {
        $openid = new LightOpenID;
        $okay = $openid->validate();
    } // try
    catch (ErrorException $e)
    {
        $msg = $e->getMessage();
        print("OpenID library threw an exception:\n   $msg\n\n");
    } // catch

    $m = array();
    if (!$okay)
        print("User has not logged in!\n\n");
    else if (!preg_match('/^.*\/(\d+)$/', $_GET['openid_identity'], $m))
        print("User logged in, but we can't figure out SteamID!\n\n");
    else
    {
        $steamid = $m[1];
        $_SESSION['steamid'] = $steamid;
        print("User logged in! SteamID is $steamid!\n\n");
    } // else
} // else

exit(0);

?>
