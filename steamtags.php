<?php

require_once('database.php');

function steam_base_profile_url($user)
{
    $profdir = 'profiles';

    if (preg_match('/^[0-9]+$/', $user))
    {
        $basedir = $profdir;
        $id = $user;
    } // if
    else
    {
        $basedir = 'id';
        $id = $user;
    } // else

    return "http://steamcommunity.com/$basedir/$id";
} // steam_base_profile_url

function steam_profile_url($user)
{
    return steam_base_profile_url($user) . "?xml=1";
} // steam_profile_url

function steam_gamelist_url($user)
{
    return steam_base_profile_url($user) . "/games?tab=all&xml=1";
} // steam_gamelist_url

function url_to_simplexml($url)
{
    return simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
} // url_to_simplexml

function load_profile_game_tags(&$profile)
{
    $gamelist = &$profile['gamelist'];
    $id = $profile['steamid'];
    $sql = "select appid, tag from gametags where steamid=$id" .
           " and deleted is null order by appid";
    $query = do_dbquery($sql);
    if ($query === false)
        return false;

    while ( ($row = db_fetch_array($query)) != false )
    {
        if (!isset($gamelist[$row['appid']]))
            continue;  // maybe it was a free weekend game they don't own now?
        $gamelist[$row['appid']]['tags'][] = $row['tag'];
    } // while

    db_free_result($query);
    return true;
} // load_profile_game_tags

function load_steam_profile($user, &$isprivate)
{
    $sxe = url_to_simplexml(steam_profile_url($user));
    if ($sxe === false)
    {
        //write_error("Couldn't load user profile from Steam Community");
        return NULL;
    } // if

    $isprivate = ($sxe->privacyState != 'public');
    if ($isprivate)
    {
        //write_error("This user's profile is marked private");
        return NULL;
    } // if

    $scid = (string) $sxe->customURL;  // Steam Community ID.
    $steamid64 = (string) $sxe->steamID64;
    $profileurl = steam_base_profile_url(empty($scid) ? $steamid64 : $scid);

    $profile = array();
    $profile['valid'] = 1;
    $profile['private'] = 0;
    $profile['nickname'] = (string) $sxe->steamID;
    $profile['steamid'] = (string) $steamid64;
    $profile['name'] = $scid;
    $profile['profileurl'] = $profileurl;
    $profile['onlinestate'] = (string) $sxe->onlineState;
    $profile['statemsg'] = (string) $sxe->stateMessage;
    $profile['avatarurl_small'] = (string) $sxe->avatarIcon;
    $profile['avatarurl_medium'] = (string) $sxe->avatarMedium;
    $profile['avatarurl_large'] = (string) $sxe->avatarFull;
    $profile['vacbanned'] = (int) $sxe->vacBanned;
    $profile['membersince'] = (string) $sxe->memberSince;
    $profile['rating'] = (int) $sxe->steamRating;
    $profile['hoursplayed_2weeks'] = (int) $sxe->hoursPlayed2Wk;
    $profile['headline'] = (string) $sxe->headline;
    $profile['location'] = (string) $sxe->location;
    $profile['realname'] = (string) $sxe->realname;
    $profile['summary'] = (string) $sxe->summary;
    $profile['weblinks'] = array();
    foreach ($sxe->weblinks as $wl)
    {
        $profile['weblinks'][] = array(
            'title' => (string) $wl->weblink->title,
            'url' => (string) $wl->weblink->link
        );
    } // foreach

    // We've grabbed the profile data, now get the game list for the user...

    $sxe = url_to_simplexml(steam_gamelist_url($user));
    if ($sxe === false)
    {
        //write_error("Couldn't load user gamelist from Steam Community");
        return NULL;
    } // if

    $gamelist = array();

    foreach ($sxe->games->game as $g)
    {
        $gamelist[(int) $g->appID] = array(
            'appid' => (int) $g->appID,
            'title' => (string) $g->name,
            'logourl' => (string) $g->logo,
            'storeurl' => (string) $g->storeLink,
            'tags' => array(),
        );
    } // foreach

    $profile['gamelist'] = &$gamelist;

    if (!load_profile_game_tags($profile))
    {
        //write_error("Couldn't load user gametags from our database");
        return NULL;
    } // if

    return $profile;
} // load_steam_profile

// end of steamtags.php ...

