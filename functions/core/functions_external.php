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
* External functions for ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/**
* Function to fetch a users feedback score directly from eBay or other Auction sites.
*
* @param        string          username
* @param        string          feedback website identifier
*
* @return	boolean         Returns HTML formatted result of the score
*/
function fetch_feedback_score($username = '', $website = 'ebay')
{
	if ($website == 'ebay')
	{
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, 'http://myworld.ebay.com/' . trim($username));
		curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 300);
		$str = curl_exec ($ch);
		curl_close ($ch);
		$str = split('<div class="areacont">',$str);
		$str = $str[1];
		$str = split('</a>',$str);
		$str = $str[0];
		$str = split('xmlns="">',$str);
		$str = $str[1];
	}
	
	return $str;
}

function populate_categories_with_products($location = 'c:/wamp/www/__PRODUCTS_A.txt')
{
	//return split("\n", file_get_contents($location));
	$array = file($location);
	//echo count($array); exit;
	for ($i = 20000; $i < 50000; $i++)
	{
	    echo $array[$i]. '<br />';
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>