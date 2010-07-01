<?php
header('Content-type: application/xml; charset=UTF-8');

require_once('steamtags.php');

// This only dumps scalar items, because we usually want to tweak the
//  child array output.
function dump_array_as_xml($name, $a)
{
    if ($name != NULL)
        print("<$name>");

    foreach ($a as $k => $v)
    {
        if (is_array($v))
            continue;  // we'll do these later.
        $txt = htmlentities((string) $v);
        print("<$k>$txt</$k>");
    } // foreach

    if ($name != NULL)
        print("</$name>");
} // dump_array_as_xml


// mainline...
if (isset($_REQUEST['user']))
    $user = $_REQUEST['user'];

if (isset($user))
    $profile = load_steam_profile($user);

if ($profile == NULL)
{
    print('<profile><valid>0</valid></profile>');
    exit(0);
} // if

print("<profile>");
    dump_array_as_xml(NULL, $profile);
    print("<weblinks>");
        foreach ($profile['weblinks'] as $wl)
        {
            print("<weblink>");
                dump_array_as_xml(NULL, $wl);
            print("</weblink>");
        } // foreach
    print("</weblinks>");

    print("<gamelist>");
        foreach ($profile['gamelist'] as $g)
        {
            print("<game>");
                dump_array_as_xml(NULL, $g);
                print("<tags>");
                    foreach ($g['tags'] as $t)
                    {
                        $tag = htmlentities($t);
                        print("<tag>$tag</tag>");
                    } // foreach
                print("</tags>");
            print("</game>");
        } // foreach
    print("</gamelist>");
print("</profile>");

exit(0);
?>
