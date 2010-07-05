<?php
header('Content-type: application/xml; charset=UTF-8');

require_once('steamtags.php');

// !!! FIXME: there has got to be a better way to do this. Maybe move to
// !!! FIXME:  the formal XML writer classes.
$xmlents = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;');
$htmlents = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
function xml_entities($str) 
{ 
    global $xmlents, $htmlents;
    $str = htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
    $str = str_replace($htmlents, $xmlents, $str); 
    $str = str_ireplace($htmlents, $xmlents, $str); 
    return $str; 
} // xml_entities

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
        $txt = xml_entities((string) $v);
        print("<$k>$txt</$k>");
    } // foreach

    if ($name != NULL)
        print("</$name>");
} // dump_array_as_xml


// mainline...

session_start();

if (!isset($_SESSION['steamid']))
{
    print('<profile><steamid>0</steamid></profile>');
    exit(0);
} // if

$steamid = $_SESSION['steamid'];
$profile = load_steam_profile($steamid, $isprivate);

if ($isprivate)
{
    print("<profile><steamid>$steamid</steamid><valid>1</valid><private>1</private></profile>");
    exit(0);
} // if
else if ($profile == NULL)
{
    print("<profile><steamid>$steamid</steamid><valid>0</valid></profile>");
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
                        $tag = xml_entities($t);
                        print("<tag>$tag</tag>");
                    } // foreach
                print("</tags>");
            print("</game>");
        } // foreach
    print("</gamelist>");
print("</profile>");

exit(0);
?>
