<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Common class which holds the majority of common ILance functions in the system
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class common
{
        /**
	* Function to determine what the visiting users web browser is
	*
	* @param       string        browser
	* @param       integer       version (optional)
	* 
        * @return      string        Returns browser info
	*/
	function is_webbrowser($browser, $version = 0)
	{
		global $_SERVER;
                
		static $is;
                
                $agent = mb_strtolower(USERAGENT);
                
		if (!is_array($is))
		{
			$useragent = $agent;
			$is = array(
                                'opera' => 0,
                                'ie' => 0,
                                'mozilla' => 0,
                                'firebird' => 0,
                                'firefox' => 0,
                                'camino' => 0,
                                'konqueror' => 0,
                                'safari' => 0,
                                'webtv' => 0,
                                'netscape' => 0,
                                'mac' => 0,
                                'chrome' => 0,
                                'aol' => 0,
                                'lynx' => 0,
                                'phoenix' => 0,
                                'omniweb' => 0,
                                'icab' => 0,
                                'mspie' => 0,
                                'netpositive' => 0,
                                'galeon' => 0,
                        );
                        
			if (mb_strpos($useragent, 'opera') !== false)
			{
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			}
			if (mb_strpos($useragent, 'msie ') !== false AND !$is['opera'])
			{
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}
			if (mb_strpos($useragent, 'mac') !== false)
			{
				$is['mac'] = 1;
			}
                        if (mb_strpos($useragent, 'chrome') !== false)
			{
				$is['chrome'] = 1;
			}
			if (mb_strpos($useragent, 'safari') !== false OR mb_strpos($useragent, 'safari') !== false AND $is['mac'])
			{
				preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
				$is['safari'] = $regs[1];
			}
			if (mb_strpos($useragent, 'konqueror') !== false)
			{
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}
			if (mb_strpos($useragent, 'gecko') !== false AND !$is['safari'] AND !$is['konqueror'] AND !$is['chrome'])
			{
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = $regs[1];
				if (mb_strpos($useragent, 'firefox') !== false OR mb_strpos($useragent, 'firebird') !== false OR mb_strpos($useragent, 'phoenix') !== false)
				{
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];
					if ($regs[1] == 'firefox')
					{
						$is['firefox'] = $regs[3];
					}
				}
				if (mb_strpos($useragent, 'chimera') !== false OR mb_strpos($useragent, 'camino') !== false)
				{
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}
			if (mb_strpos($useragent, 'webtv') !== false)
			{
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs))
			{
				$is['netscape'] = "$regs[1].$regs[2]";
			}
                        if (mb_strpos($useragent, 'aol') !== false)
			{
				$is['aol'] = 1;
			}
                        if (mb_strpos($useragent, 'lynx') !== false)
			{
				$is['lynx'] = 1;
			}
                        if (mb_strpos($useragent, 'phoenix') !== false)
			{
				$is['phoenix'] = 1;
			}
                        if (mb_strpos($useragent, 'omniweb') !== false)
			{
				$is['omniweb'] = 1;
			}
                        if (mb_strpos($useragent, 'icab') !== false)
			{
				$is['icab'] = 1;
			}
                        if (mb_strpos($useragent, 'mspie') !== false)
			{
				$is['mspie'] = 1;
			}
                        if (mb_strpos($useragent, 'netpositive') !== false)
			{
				$is['netpositive'] = 1;
			}
                        if (mb_strpos($useragent, 'galeon') !== false)
			{
				$is['galeon'] = 1;
			}
		}
                
		$browser = mb_strtolower($browser);
		if (mb_substr($browser, 0, 3) == 'is_')
		{
			$browser = mb_substr($browser, 3);
		}
                
		if ($is["$browser"])
		{
			if ($version)
			{
				if ($is["$browser"] >= $version)
				{
					return $is["$browser"];
				}
			}
			else
			{
				return $is["$browser"];
			}
		}
                
		return 0;
	}
	
        /**
	* Function to return a utf-8 string based on a numeric entity string
	*
	* @param       string        numeric entity character eg: &320; 
	* 
        * @return      string        Returns utf-8 character based on numeric entities supplied
	*/
	function numeric_to_utf8($t = '')
	{
		$convmap = array(0x0, 0x2FFFF, 0, 0xFFFF);
                
		return mb_decode_numericentity($t, $convmap, 'UTF-8');
	}
    
	/**
	* Function to return numeric entities from htmlentity characters
	* 
	* @param string
	*
	* @return      string
	*/
	function entities_to_numeric($text = '', $flip = 0, $skip = '')
	{
		$to_ncr = array(
                        '¡'         => '&#161;',
                        '¢'         => '&#162;',
                        '£'         => '&#163;',
                        '¤'         => '&#164;',
                        '¥'         => '&#165;',
                        '¦'         => '&#166;',
                        '§'         => '&#167;',
                        '¨'         => '&#168;',
                        '©'         => '&#169;',
                        'ª'         => '&#170;',
                        '«'         => '&#171;',
                        '¬'         => '&#172;',
                        '®'         => '&#174;',
                        '¯'         => '&#175;',
                        '°'         => '&#176;',
                        '±'         => '&#177;',
                        '²'         => '&#178;',
                        '³'         => '&#179;',
                        '´'         => '&#180;',
                        'µ'         => '&#181;',
                        '¶'         => '&#182;',
                        '·'         => '&#183;',
                        '¸'         => '&#184;',
                        '¹'         => '&#185;',
                        'º'         => '&#186;',
                        '»'         => '&#187;',
                        '¼'         => '&#188;',
                        '½'         => '&#189;',
                        '¾'         => '&#190;',
                        '¿'         => '&#191;',
                        'À'         => '&#192;',
                        'Á'         => '&#193;',
                        'Â'         => '&#194;',
                        'Ã'         => '&#195;',
                        'Ä'         => '&#196;',
                        'Å'         => '&#197;',
                        'Æ'         => '&#198;',
                        'Ç'         => '&#199;',
                        'È'         => '&#200;',
                        'É'         => '&#201;',
                        'Ê'         => '&#202;',
                        'Ë'         => '&#203;',
                        'Ì'         => '&#204;',
                        'Í'         => '&#205;',
                        'Î'         => '&#206;',
                        'Ï'         => '&#207;',
                        'Ð'         => '&#208;',
                        'Ñ'         => '&#209;',
                        'Ò'         => '&#210;',
                        'Ó'         => '&#211;',
                        'Ô'         => '&#212;',
                        'Õ'         => '&#213;',
                        'Ö'         => '&#214;',
                        '×'         => '&#215;',
                        'Ø'         => '&#216;',
                        'Ù'         => '&#217;',
                        'Ú'         => '&#218;',
                        'Û'         => '&#219;',
                        'Ü'         => '&#220;',
                        'Ý'         => '&#221;',
                        'Þ'         => '&#222;',
                        'ß'         => '&#223;',
                        'à'         => '&#224;',
                        'á'         => '&#225;',
                        'â'         => '&#226;',
                        'ã'         => '&#227;',
                        'ä'         => '&#228;',
                        'å'         => '&#229;',
                        'æ'         => '&#230;',
                        'ç'         => '&#231;',
                        'è'         => '&#232;',
                        'é'         => '&#233;',
                        'ê'         => '&#234;',
                        'ë'         => '&#235;',
                        'ì'         => '&#236;',
                        'í'         => '&#237;',
                        'î'         => '&#238;',
                        'ï'         => '&#239;',
                        'ð'         => '&#240;',
                        'ñ'         => '&#241;',
                        'ò'         => '&#242;',
                        'ó'         => '&#243;',
                        'ô'         => '&#244;',
                        'õ'         => '&#245;',
                        'ö'         => '&#246;',
                        '÷'         => '&#247;',
                        'ø'         => '&#248;',
                        'ù'         => '&#249;',
                        'ú'         => '&#250;',
                        'û'         => '&#251;',
                        'ü'         => '&#252;',
                        'ý'         => '&#253;',
                        'þ'         => '&#254;',
                        'ÿ'         => '&#255;',
                        '&quot;'    => '&#34;',
                        '&amp;'     => '&#38;',
                        '&frasl;'   => '&#47;',
                        '&lt;'      => '&#60;',
                        '&gt;'      => '&#62;',
                        '|'         => '&#124;',
                        '&nbsp;'    => '&#160;',
                        '&iexcl;'   => '&#161;',
                        '&cent;'    => '&#162;',
                        '&pound;'   => '&#163;',
                        '&curren;'  => '&#164;',
                        '&yen;'     => '&#165;',
                        '&brvbar;'  => '&#166;',
                        '&brkbar;'  => '&#166;',
                        '&sect;'    => '&#167;',
                        '&uml;'     => '&#168;',
                        '&die;'     => '&#168;',
                        '&copy;'    => '&#169;',
                        '&ordf;'    => '&#170;',
                        '&laquo;'   => '&#171;',
                        '&not;'     => '&#172;',
                        '&shy;'     => '&#173;',
                        '&reg;'     => '&#174;',
                        '&macr;'    => '&#175;',
                        '&hibar;'   => '&#175;',
                        '&deg;'     => '&#176;',
                        '&plusmn;'  => '&#177;',
                        '&sup2;'    => '&#178;',
                        '&sup3;'    => '&#179;',
                        '&acute;'   => '&#180;',
                        '&micro;'   => '&#181;',
                        '&para;'    => '&#182;',
                        '&middot;'  => '&#183;',
                        '&cedil;'   => '&#184;',
                        '&sup1;'    => '&#185;',
                        '&ordm;'    => '&#186;',
                        '&raquo;'   => '&#187;',
                        '&frac14;'  => '&#188;',
                        '&frac12;'  => '&#189;',
                        '&frac34;'  => '&#190;',
                        '&iquest;'  => '&#191;',
                        '&Agrave;'  => '&#192;',
                        '&Aacute;'  => '&#193;',
                        '&Acirc;'   => '&#194;',
                        '&Atilde;'  => '&#195;',
                        '&Auml;'    => '&#196;',
                        '&Aring;'   => '&#197;',
                        '&AElig;'   => '&#198;',
                        '&Ccedil;'  => '&#199;',
                        '&Egrave;'  => '&#200;',
                        '&Eacute;'  => '&#201;',
                        '&Ecirc;'   => '&#202;',
                        '&Euml;'    => '&#203;',
                        '&Igrave;'  => '&#204;',
                        '&Iacute;'  => '&#205;',
                        '&Icirc;'   => '&#206;',
                        '&Iuml;'    => '&#207;',
                        '&ETH;'     => '&#208;',
                        '&Ntilde;'  => '&#209;',
                        '&Ograve;'  => '&#210;',
                        '&Oacute;'  => '&#211;',
                        '&Ocirc;'   => '&#212;',
                        '&Otilde;'  => '&#213;',
                        '&Ouml;'    => '&#214;',
                        '&times;'   => '&#215;',
                        '&Oslash;'  => '&#216;',
                        '&Ugrave;'  => '&#217;',
                        '&Uacute;'  => '&#218;',
                        '&Ucirc;'   => '&#219;',
                        '&Uuml;'    => '&#220;',
                        '&Yacute;'  => '&#221;',
                        '&THORN;'   => '&#222;',
                        '&szlig;'   => '&#223;',
                        '&agrave;'  => '&#224;',
                        '&aacute;'  => '&#225;',
                        '&acirc;'   => '&#226;',
                        '&atilde;'  => '&#227;',
                        '&auml;'    => '&#228;',
                        '&aring;'   => '&#229;',
                        '&aelig;'   => '&#230;',
                        '&ccedil;'  => '&#231;',
                        '&egrave;'  => '&#232;',
                        '&eacute;'  => '&#233;',
                        '&ecirc;'   => '&#234;',
                        '&euml;'    => '&#235;',
                        '&igrave;'  => '&#236;',
                        '&iacute;'  => '&#237;',
                        '&icirc;'   => '&#238;',
                        '&iuml;'    => '&#239;',
                        '&eth;'     => '&#240;',
                        '&ntilde;'  => '&#241;',
                        '&ograve;'  => '&#242;',
                        '&oacute;'  => '&#243;',
                        '&ocirc;'   => '&#244;',
                        '&otilde;'  => '&#245;',
                        '&ouml;'    => '&#246;',
                        '&divide;'  => '&#247;',
                        '&oslash;'  => '&#248;',
                        '&ugrave;'  => '&#249;',
                        '&uacute;'  => '&#250;',
                        '&ucirc;'   => '&#251;',
                        '&uuml;'    => '&#252;',
                        '&yacute;'  => '&#253;',
                        '&thorn;'   => '&#254;',
                        '&yuml;'    => '&#255;',
                        '&OElig;'   => '&#338;',
                        '&oelig;'   => '&#339;',
                        '&Scaron;'  => '&#352;',
                        '&scaron;'  => '&#353;',
                        '&Yuml;'    => '&#376;',
                        '&fnof;'    => '&#402;',
                        '&circ;'    => '&#710;',
                        '&tilde;'   => '&#732;',
                        '&Alpha;'   => '&#913;',
                        '&Beta;'    => '&#914;',
                        '&Gamma;'   => '&#915;',
                        '&Delta;'   => '&#916;',
                        '&Epsilon;' => '&#917;',
                        '&Zeta;'    => '&#918;',
                        '&Eta;'     => '&#919;',
                        '&Theta;'   => '&#920;',
                        '&Iota;'    => '&#921;',
                        '&Kappa;'   => '&#922;',
                        '&Lambda;'  => '&#923;',
                        '&Mu;'      => '&#924;',
                        '&Nu;'      => '&#925;',
                        '&Xi;'      => '&#926;',
                        '&Omicron;' => '&#927;',
                        '&Pi;'      => '&#928;',
                        '&Rho;'     => '&#929;',
                        '&Sigma;'   => '&#931;',
                        '&Tau;'     => '&#932;',
                        '&Upsilon;' => '&#933;',
                        '&Phi;'     => '&#934;',
                        '&Chi;'     => '&#935;',
                        '&Psi;'     => '&#936;',
                        '&Omega;'   => '&#937;',
                        '&alpha;'   => '&#945;',
                        '&beta;'    => '&#946;',
                        '&gamma;'   => '&#947;',
                        '&delta;'   => '&#948;',
                        '&epsilon;' => '&#949;',
                        '&zeta;'    => '&#950;',
                        '&eta;'     => '&#951;',
                        '&theta;'   => '&#952;',
                        '&iota;'    => '&#953;',
                        '&kappa;'   => '&#954;',
                        '&lambda;'  => '&#955;',
                        '&mu;'      => '&#956;',
                        '&nu;'      => '&#957;',
                        '&xi;'      => '&#958;',
                        '&omicron;' => '&#959;',
                        '&pi;'      => '&#960;',
                        '&rho;'     => '&#961;',
                        '&sigmaf;'  => '&#962;',
                        '&sigma;'   => '&#963;',
                        '&tau;'     => '&#964;',
                        '&upsilon;' => '&#965;',
                        '&phi;'     => '&#966;',
                        '&chi;'     => '&#967;',
                        '&psi;'     => '&#968;',
                        '&omega;'   => '&#969;',
                        '&thetasym;'=> '&#977;',
                        '&upsih;'   => '&#978;',
                        '&piv;'     => '&#982;',
                        '&ensp;'    => '&#8194;',
                        '&emsp;'    => '&#8195;',
                        '&thinsp;'  => '&#8201;',
                        '&zwnj;'    => '&#8204;',
                        '&zwj;'     => '&#8205;',
                        '&lrm;'     => '&#8206;',
                        '&rlm;'     => '&#8207;',
                        '&ndash;'   => '&#8211;',
                        '&mdash;'   => '&#8212;',
                        '&lsquo;'   => '&#8216;',
                        '&rsquo;'   => '&#8217;',
                        '&sbquo;'   => '&#8218;',
                        '&ldquo;'   => '&#8220;',
                        '&rdquo;'   => '&#8221;',
                        '&bdquo;'   => '&#8222;',
                        '&dagger;'  => '&#8224;',
                        '&Dagger;'  => '&#8225;',
                        '&bull;'    => '&#8226;',
                        '&hellip;'  => '&#8230;',
                        '&permil;'  => '&#8240;',
                        '&prime;'   => '&#8242;',
                        '&Prime;'   => '&#8243;',
                        '&lsaquo;'  => '&#8249;',
                        '&rsaquo;'  => '&#8250;',
                        '&oline;'   => '&#8254;',
                        '&frasl;'   => '&#8260;',
                        '&euro;'    => '&#8364;',
                        '&image;'   => '&#8465;',
                        '&weierp;'  => '&#8472;',
                        '&real;'    => '&#8476;',
                        '&trade;'   => '&#8482;',
                        '&alefsym;' => '&#8501;',
                        '&larr;'    => '&#8592;',
                        '&uarr;'    => '&#8593;',
                        '&rarr;'    => '&#8594;',
                        '&darr;'    => '&#8595;',
                        '&harr;'    => '&#8596;',
                        '&crarr;'   => '&#8629;',
                        '&lArr;'    => '&#8656;',
                        '&uArr;'    => '&#8657;',
                        '&rArr;'    => '&#8658;',
                        '&dArr;'    => '&#8659;',
                        '&hArr;'    => '&#8660;',
                        '&forall;'  => '&#8704;',
                        '&part;'    => '&#8706;',
                        '&exist;'   => '&#8707;',
                        '&empty;'   => '&#8709;',
                        '&nabla;'   => '&#8711;',
                        '&isin;'    => '&#8712;',
                        '&notin;'   => '&#8713;',
                        '&ni;'      => '&#8715;',
                        '&prod;'    => '&#8719;',
                        '&sum;'     => '&#8721;',
                        '&minus;'   => '&#8722;',
                        '&lowast;'  => '&#8727;',
                        '&radic;'   => '&#8730;',
                        '&prop;'    => '&#8733;',
                        '&infin;'   => '&#8734;',
                        '&ang;'     => '&#8736;',
                        '&and;'     => '&#8743;',
                        '&or;'      => '&#8744;',
                        '&cap;'     => '&#8745;',
                        '&cup;'     => '&#8746;',
                        '&int;'     => '&#8747;',
                        '&there4;'  => '&#8756;',
                        '&sim;'     => '&#8764;',
                        '&cong;'    => '&#8773;',
                        '&asymp;'   => '&#8776;',
                        '&ne;'      => '&#8800;',
                        '&equiv;'   => '&#8801;',
                        '&le;'      => '&#8804;',
                        '&ge;'      => '&#8805;',
                        '&sub;'     => '&#8834;',
                        '&sup;'     => '&#8835;',
                        '&nsub;'    => '&#8836;',
                        '&sube;'    => '&#8838;',
                        '&supe;'    => '&#8839;',
                        '&oplus;'   => '&#8853;',
                        '&otimes;'  => '&#8855;',
                        '&perp;'    => '&#8869;',
                        '&sdot;'    => '&#8901;',
                        '&lceil;'   => '&#8968;',
                        '&rceil;'   => '&#8969;',
                        '&lfloor;'  => '&#8970;',
                        '&rfloor;'  => '&#8971;',
                        '&lang;'    => '&#9001;',
                        '&rang;'    => '&#9002;',
                        '&loz;'     => '&#9674;',
                        '&spades;'  => '&#9824;',
                        '&clubs;'   => '&#9827;',
                        '&hearts;'  => '&#9829;',
                        '&diams;'   => '&#9830;'
                );
    
                if (isset($flip) AND $flip)
                {
                        $to_ncr = array_flip($to_ncr);
                }
        
                foreach ($to_ncr AS $entity => $ncr)
                {
                        if (isset($skip) AND $skip != '')
                        {
                                if ($skip != $entity)
                                {
                                        $text = str_replace($entity, $ncr, $text);
                                }
                        }
                        else
                        {
                                $text = str_replace($entity, $ncr, $text);
                        }
                        
                }
                
                return $text;
	}        

	/**
	* Function to 
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function xhtml_entities_to_numeric_entities($text = '')
        {
                if (preg_match('~&#x([0-9a-fA-F]+);~', $text, $matches))
                {
                        //echo 'Found: &#x' . $matches[1] . '; (' . chr(hexdec('0x' . $matches[1])) . '). Replaced: &#' . hexdec('0x' . $matches[1]) . ';' . "\n";
                        $text = str_replace('&#x' . $matches[1] . ';', '&#' . hexdec('0x' . $matches[1]) . ';', $text);
                        $text = str_replace(array('&#62;', '&#60;'), array('&gt;', '&lt;'), $text);
                }
                
                return $text;
        }
        
        /**
	* Function to 
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function js_escaped_to_xhtml_entities($text = '')
        {
                $text = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($text));
                return $text;
        }
        
        /**
	* Function to strips invalid html such as javascript code
	* 
	* @param       string        text
	*
	* @return      string
	*/
	function xss_clean(&$var)
	{
		static
			$find = array('#javascript#i', '#ilancescript#i'),
			$replace = array('java script', 'ilance script');

		$var = preg_replace($find, $replace, htmlspecialchars_uni($var));
		return $var;
	}
	
        /**
	* Function to display the login information bar for members
	* 
	* @param       string        text
	*
	* @return      string
	*/
	function login_include()
	{
		global $phrase, $ilance, $ilpage, $ilconfig;
                
		$hour = gmdate("H", time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']));
		$ampm = gmdate("A", time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']));
                
		if (($hour < 12 AND $ampm == 'AM' OR $hour < 18 AND $ampm == 'AM'))
		{
			if (isset($phrase['_good_morning']))
			{
                                $greeting = $phrase['_good_morning'] . ', ';
			}
		}
		else if ($hour < 12 AND $ampm == 'PM')
		{
			if (isset($phrase['_good_evening']))
			{
                                $greeting = $phrase['_good_evening'] . ', ';
			}
		}
		else if ($hour < 18 AND $ampm == 'PM')
		{
			if (isset($phrase['_good_afternoon']))
			{
                                $greeting = $phrase['_good_afternoon'] . ', ';
			}
		}
		else
		{
			if (isset($phrase['_good_evening']))
			{
                                $greeting = $phrase['_good_evening'] . ', ';
			}
		}
                
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND !empty($_SESSION['ilancedata']['user']['username']) AND !empty($_SESSION['ilancedata']['user']['password']))
                {
                        // session is active
                        //$login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' ' . $_SESSION['ilancedata']['user']['username'] . ' - <span class="blue"><a href="' . HTTPS_SERVER . 'signout" target="_self" onclick="return log_out();">' . $phrase['_log_out'] . '</a></span>' : $greeting . ' ' . $_SESSION['ilancedata']['user']['username'] . ' - <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?cmd=_logout" target="_self" onclick="return log_out();">' . $phrase['_log_out'] . '</a></span>';
						 /*$login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' ' . fetch_user('first_name',$_SESSION['ilancedata']['user']['userid']) .' '.fetch_user('last_name',$_SESSION['ilancedata']['user']['userid']).' - <span class="blue"><a href="' . HTTPS_SERVER . 'signout" target="_self" onclick="return log_out();">' . $phrase['_log_out'] . '</a></span>' : $greeting . ' ' . fetch_user('first_name',$_SESSION['ilancedata']['user']['userid']) .' '.fetch_user('last_name',$_SESSION['ilancedata']['user']['userid']). ' - <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?cmd=_logout" target="_self" onclick="return log_out();">' . $phrase['_log_out'] . '</a></span>';*/
						 
						 $login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' ' . $_SESSION['ilancedata']['user']['firstname'] .' '.$_SESSION['ilancedata']['user']['lastname'].' - <span class="blue"><a href="' . HTTPS_SERVER . 'signout" target="_self" onclick="return log_out();">' . $phrase['_log_out'] . '</a></span>' : $greeting . ' ' . $_SESSION['ilancedata']['user']['firstname'] .' '.$_SESSION['ilancedata']['user']['lastname']. ' - <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?cmd=_logout" target="_self" onclick="return log_out();">' . $phrase['_log_out'] . '</a></span>';





						 

                }
                else
                {
                        // do we have a cookie?
                        if (!empty($_COOKIE[COOKIE_PREFIX . 'username']))
                        {
                                // most likely just finished quick registration
                                $login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' ' . $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']) . ' - <span class="blue"><a href="' . HTTPS_SERVER . 'signin?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '" target="_self">' . $phrase['_please_log_in'] . '</a></span>' : $greeting . ' ' . $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']) . ' - <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '" target="_self">' . $phrase['_please_log_in'] . '</a></span>';
                        }
                        else
                        {
                                // no cookie
                                $login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' <span class="blue"><a href="' . HTTPS_SERVER . 'register">' . $phrase['_register'] . '</a></span> <strong>' . $phrase['_or'] . '</strong> <span class="blue"><a href="' . HTTPS_SERVER . 'signin?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '">' . $phrase['_sign_in'] . '</a></span>.' : $greeting . ' <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['registration'] . '">' . $phrase['_register'] . '</a></span> <strong>' . $phrase['_or'] . '</strong> <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '">' . $phrase['_sign_in'] . '</a></span>';;
                        }
                }
                
                ($apihook = $ilance->api('login_include_end')) ? eval($apihook) : false;
                
		if (!empty($login_include))
		{
			return $login_include;
		}
                
                return false;
                
	}

        /**
	* Function to display the login information bar for admins
	* 
	* @param       string        text
	*
	* @return      string
	*/
	function admin_login_include()
	{
		global $ilance, $myapi, $ilpage, $ilconfig, $phrase;
	
		$hour = gmdate("H", time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']));
		$ampm = gmdate("A", time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']));
	
		if ($hour < 12 AND $ampm == 'AM')
		{
			$greeting = $phrase['_good_morning'] . " ";
		}
		else if ($hour < 12 AND $ampm == 'PM')
		{
			$greeting = $phrase['_good_evening'] . " ";
		}
		else if ($hour < 18 AND $ampm == 'AM')
		{
			$greeting = $phrase['_good_morning'] . " ";
		}
		else if ($hour < 18 AND $ampm == 'PM')
		{
			$greeting = $phrase['_good_afternoon'] . " ";
		}
		else
		{
			$greeting = $phrase['_good_evening'] . " ";
                }
                
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND ($_SESSION['ilancedata']['user']['isadmin'] == '1' or $_SESSION['ilancedata']['user']['isstaff'] == '1') )
		{
			if (!empty($_SESSION['ilancedata']['user']['username']))
			{
				$greetuser = $_SESSION['ilancedata']['user']['username'];
			}
                        
			$membersonline = members_online();
			$login_include = $greeting . " <strong>" . $greetuser . "</strong> - " . $membersonline . ". " . $phrase['_you_are_logged_in'] . ", <span class=\"blue\"><a href=\"" . HTTPS_SERVER_ADMIN . $ilpage['login'] . "?cmd=_logout\" target=\"_self\" onclick=\"return log_out();\">" . $phrase['_log_out'] . "</a></span>";
		}
		else
		{
			$login_include = $greeting . " " . $phrase['_guest'] . " - " . $phrase['_you_are_logged_out'] . ".";
		}
                
		return $login_include;
	}
    
	/**
	* Function to display a date when a supplied user id was last seen
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function last_seen($userid, $location = false)
	{
		global $ilance, $myapi, $ilconfig, $phrase;
                
		$sql = $ilance->db->query("
                        SELECT lastseen FROM " . DB_PREFIX . "users
                        WHERE user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);	
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			if ($res['lastseen'] == "0000-00-00 00:00:00")
			{
				$lastseen = $phrase['_more_than_a_month_ago'];
			}
			else
			{
				$lastseen = print_date($res['lastseen'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			}
		}
                
		return $lastseen;
	}
	
	/**
	* Function to determine if an email address is valid.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function is_email_valid($email = '')
	{
		return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
	}
	
	/**
	* Function to determine if an email address is banned.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function is_email_banned($email = '')
	{
		global $ilconfig;
    
		if (!empty($ilconfig['registrationdisplay_emailban']))
		{
			$bans = preg_split('/\s+/', $ilconfig['registrationdisplay_emailban'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($bans as $banned)
			{
				if ($this->is_email_valid($banned))
				{
					$regex = '^' . preg_quote($banned, '#') . '$';
				}
				else
				{
					$regex = preg_quote($banned, '#');
				}
				if (preg_match("#$regex#i", $email))
				{
					return 1;
				}
			}
		}
                
		return 0;
	}
	
	/**
	* Function to determine if a username is banned.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function is_username_banned($username = '')
	{
		global $ilconfig;
		
		if (!empty($ilconfig['registrationdisplay_userban']))
		{
			$bans = preg_split('/\s+/', $ilconfig['registrationdisplay_userban'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($bans AS $banned)
			{
				$regex = '^' . preg_quote($banned, '#') . '$';
				if (preg_match("#$regex#i", $username))
				{
					return 1;
				}
			}
		}
		if (preg_match('/[^a-zA-Z0-9\_]+/', $username))
		{
                        return 1;
		}
                
		return 0;
	}
	
	/**
	* Function to download a file to a web browser.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function download_file($filestring = '', $filename = '', $filetype = '')
	{
		if (!isset($isIE))
		{
			static $isIE;
			$isIE = iif($this->is_webbrowser('ie') OR $this->is_webbrowser('opera'), true, false);
		}
		if ($isIE)
		{
			$filetype = 'application/octetstream';
		}
		else
		{
			$filetype = 'application/octet-stream';
		}
		header('Content-Type: ' . $filetype);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		//header('Content-Length: ' . strlen($filestring));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
                
		echo $filestring;
		exit();
	}
	
	/**
	* Function to generate a random string based on a supplied number of characters.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function construct_random_value($num)
	{
		switch($num)
		{
			case "1":
			$rand = "A";
			break;
			case "2":
			$rand = "B";
			break;
			case "3":
			$rand = "C";
			break;
			case "4":
			$rand = "D";
			break;
			case "5":
			$rand = "E";
			break;
			case "6":
			$rand = "F";
			break;
			case "7":
			$rand = "G";
			break;
			case "8":
			$rand = "H";
			break;
			case "9":
			$rand = "I";
			break;
			case "10":
			$rand = "J";
			break;
			case "11":
			$rand = "K";
			break;
			case "12":
			$rand = "L";
			break;
			case "13":
			$rand = "M";
			break;
			case "14":
			$rand = "N";
			break;
			case "15":
			$rand = "O";
			break;
			case "16":
			$rand = "P";
			break;
			case "17":
			$rand = "Q";
			break;
			case "18":
			$rand = "R";
			break;
			case "19":
			$rand = "S";
			break;
			case "20":
			$rand = "T";
			break;
			case "21":
			$rand = "U";
			break;
			case "22":
			$rand = "V";
			break;
			case "23":
			$rand = "W";
			break;
			case "24":
			$rand = "X";
			break;
			case "25":
			$rand = "Y";
			break;
			case "26":
			$rand = "Z";
			break;
			case "27":
			$rand = "0";
			break;
			case "28":
			$rand = "1";
			break;
			case "29":
			$rand = "2";
			break;
			case "30":
			$rand = "3";
			break;
			case "31":
			$rand = "4";
			break;
			case "32":
			$rand = "5";
			break;
			case "33":
			$rand = "6";
			break;
			case "34":
			$rand = "7";
			break;
			case "35":
			$rand = "8";
			break;
			case "36":
			$rand = "9";
			break;
		}
                
		return $rand;
	}
	
	/**
	* Function to fetch the active web browser name.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function fetch_browser_name($showicon = 0, $readname = '')
	{
		global $ilance, $myapi, $ilconfig, $phrase;
                
		if (isset($readname) AND $readname != '')
		{
			if ($readname == 'ie')
			{
				$name = 'Internet Explorer';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ie.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'opera')
			{
				$name = 'Opera';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/opera.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'firefox')
			{
				$name = 'FireFox';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/firefox.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'camino')
			{
				$name = 'Camino';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/camino.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'konqueror')
			{
				$name = 'Konqueror';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/konqueror.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'netscape')
			{
				$name = 'Netscape';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/netscape.gif" border="0" alt="' . $name . '" />';
			}
                        else if ($readname == 'chrome')
			{
				$name = 'Chrome';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/chrome.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'safari')
			{
				$name = 'Safari';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/safari.gif" border="0" alt="' . $name . '" />';
			}
			else
			{
				$name = $phrase['_unknown'];
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/unknown.gif" border="0" alt="' . $name . '" />';
			}    
		}
		else
		{
			if ($this->is_webbrowser('ie'))
			{
				$name = 'ie';
				$real = 'Internet Explorer';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ie.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('opera'))
			{
				$name = 'opera';
				$real = 'Opera';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/opera.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('firefox'))
			{
				$name = 'firefox';
				$real = 'FireFox';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/firefox.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('camino'))
			{
				$name = 'camino';
				$real = 'Camino';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/camino.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('konqueror'))
			{
				$name = 'konqueror';
				$real = 'Konqueror';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/konqueror.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('chrome'))
			{
				$name = 'chrome';
				$real = 'Chrome';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/chrome.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('safari'))
			{
				$name = 'safari';
				$real = 'Safari';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/safari.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('netscape'))
			{
				$name = 'netscape';
				$real = 'Netscape';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/netscape.gif" border="0" alt="' . $real . '" />';
			}
			else
			{
				$name = 'unknown';
				$real = $phrase['_unknown'];
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/unknown.gif" border="0" alt="' . $real . '" />';
			}    
		}
		
		if (isset($showicon) AND $showicon)
		{
			return $icon;   
		}
                
		return $name;
	}
	
	
	
function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}
	
	
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>