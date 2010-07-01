<html><head><title>Steam Tags</title></head><body>
<?php
require_once('steamtags.php');
$profile = load_steam_profile('icculus');
if (!profile)
    exit(1);

    print("<img src='${profile['avatarurl_small']}'/>\n");
    print("<a href='${profile['profileurl']}'>${profile['nickname']}</a>\n");
//    $profile['steamid'] = (string) $steamid64;
//    $profile['name'] = $scid;
    print(": ${profile['onlinestate']} (${profile['statemsg']})");
    if ($profile['vacbanned'])
        print(" <font color='#FF0000'>[VAC BANNED]</font>");

//    $profile['avatarurl_small'] = (string) $sxe->avatarIcon;
//    $profile['avatarurl_medium'] = (string) $sxe->avatarMedium;
//    $profile['avatarurl_large'] = (string) $sxe->avatarFull;
//    $profile['membersince'] = (string) $sxe->memberSince;
//    $profile['rating'] = (int) $sxe->steamRating;
//    $profile['hoursplayed_2weeks'] = (int) $sxe->hoursPlayed2Wk;
//    $profile['headline'] = (string) $sxe->headline;
//    $profile['location'] = (string) $sxe->location;
//    $profile['realname'] = (string) $sxe->realname;
//    $profile['summary'] = (string) $sxe->summary;
//    $profile['weblinks'] = array();
//    foreach ($sxe->weblinks as $wl) {
//        $profile['weblinks'][] = array(
//            'title' => (string) $wl->weblink->title,
//            'url' => (string) $wl->weblink->link
//        );
//    }

    print("<br/>\n<hr/>\n<table border='0'>\n");

    foreach ($profile['gamelist'] as $g)
    {
        // unused: 'storeurl'
        $launchurl = "steam://run/${g['appid']}";
        print("<tr><td>");
        print("<a href='$launchurl'>");
        print("<img src='${g['logourl']}'/>");
        print("${g['title']}</a>");
        print("</td></tr>\n");
    } // foreach

    print("</table><br/>\n<hr/>\n");
?>
</body></html>


