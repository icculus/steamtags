<?php

function write_error($err)
{
    echo "\n\n<p><font color='#FF0000'>\n\nERROR: $err\n\n</font></p>\n\n";
} // write_error

function convert_steamid_str($user)
{
    write_error("!!! FIXME: write me");
    return $user;
} // convert_steamid_str

function steam_base_profile_url($user)
{
    $profdir = 'profiles';

    if (preg_match('/^[0-9]+$/i', $user))
    {
        $basedir = $profdir;
        $id = $user;
    } // if
    else if (preg_match('/^STEAM_[0-9]:[0-9]:[0-9]{1,}/i', $user))
    {
        $basedir = $profdir;
        $id = convert_steamid_str($user);
    } // else if
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
print("url == '$url'\n");
    return simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
} // url_to_simplexml

function load_steam_profile($user)
{
    $sxe = url_to_simplexml(steam_profile_url($user));
    if ($sxe === false)
    {
        write_error("Couldn't load user profile from Steam Community");
        return NULL;
    } // if

    if ($sxe->privacyState != 'public')
    {
        write_error("This user's profile is marked private");
        return NULL;
    } // if

    $scid = (string) $sxe->customURL;  // Steam Community ID.
    $steamid64 = (string) $sxe->steamID64;
    $profileurl = steam_base_profile_url(empty($scid) ? $steamid64 : $scid);

    $profile = array();
    $profile['nickname'] = (string) $sxe->steamID;
    $profile['steamid'] = (string) $steamid64;
    $profile['name'] = $scid;
    $profile['profileurl'] = $profileurl;
    $profile['onlinestate'] = (string) $sxe->onlineState;
    $profile['statemsg'] = (string) $sxe->stateMessage;
    $profile['avatarurl_small'] = (string) $sxe->avatarIcon;
    $profile['avatarurl_medium'] = (string) $sxe->avatarMedium;
    $profile['avatarurl_large'] = (string) $sxe->avatarFull;
    $profile['vacbanned'] = (string) $sxe->vacBanned;
    $profile['membersince'] = (string) $sxe->memberSince;
    $profile['rating'] = (int) $sxe->steamRating;
    $profile['hoursplayed_2weeks'] = (int) $sxe->hoursPlayed2Wk;
    $profile['headline'] = (string) $sxe->headline;
    $profile['location'] = (string) $sxe->location;
    $profile['realname'] = (string) $sxe->realname;
    $profile['summary'] = (string) $sxe->summary;
    $profile['weblinks'] = array();
    foreach ($sxe->weblinks as $wl) {
        $profile['weblinks'][] = array(
            'title' => (string) $wl->weblink->title,
            'url' => (string) $wl->weblink->link
        );
    } // foreach

    // We've grabbed the profile data, now get the game list for the user...

    $sxe = url_to_simplexml(steam_gamelist_url($user));
    if ($sxe === false)
    {
        write_error("Couldn't load user gamelist from Steam Community");
        return NULL;
    } // if

    $gamelist = array();

    foreach ($sxe->games->game as $g)
    {
        $gamelist[] = array(
            'appid' => (int) $g->appID,
            'title' => (string) $g->name,
            'logourl' => (string) $g->logo,
            'storeurl' => (string) $g->storeLink
        );
    } // foreach

    $profile['gamelist'] = $gamelist;

    return $profile;
} // load_steam_profile

// end of steamtags.php ...

