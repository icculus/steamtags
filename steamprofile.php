<?php
ob_start('ob_gzhandler');  // compress the XML we send.
header('Content-type: application/xml; charset=UTF-8');

require_once('steamtags.php');

// mainline...
if (isset($_REQUEST['user']))
    $user = $_REQUEST['user'];

if (isset($user))
    $profile = load_steam_profile($user);

if ($profile == NULL)
    print("<profile><valid>0</valid></profile>\n");
else
{
    print("<profile>");
    print("<valid>1</valid>");
    foreach ($profile as $k => $v)
    {
        if (is_array($v))
            continue;  // we'll do these later.
        $txt = htmlentities($v);
        print("<$k>$txt</$k>");
    } // foreach

    print("<weblinks>");
    foreach ($profile['weblinks'] as $wl)
    {
        $title = htmlentities($wl['title']);
        $url = htmlentities($wl['url']);
        print("<weblink><title>$title</title><url>$url</url></weblink>");
    } // foreach
    print("</weblinks>");

    print("<gamelist>");
    foreach ($profile['gamelist'] as $g)
    {
        $appid = htmlentities($g['appid']);
        $title = htmlentities($g['title']);
        $logourl = htmlentities($g['logourl']);
        $storeurl = htmlentities($g['storeurl']);
        print(
            "<game><appid>$appid</appid><title>$title</title>" .
            "<logourl>$logourl</logourl><storeurl>$storeurl</storeurl></game>"
        );
    } // foreach
    print("</gamelist>");
    print("</profile>");
} // else

exit(0);
?>
