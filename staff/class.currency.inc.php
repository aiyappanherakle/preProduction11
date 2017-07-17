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
* Currency class to perform the majority of currency related functions in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class currency
{
        var $currencies = array();
        
        /**
        * Constructor
        *
        */
        function currency()
        {
                global $ilance, $myapi;
                
                $this->currencies = array();
                
                $query = $ilance->db->query("
                        SELECT currency_id, currency_abbrev AS code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, rate, currency_name, currency_abbrev
                        FROM " . DB_PREFIX . "currency
                ", 0, null, __FILE__, __LINE__);
                while ($currencies = $ilance->db->fetch_array($query, DB_ASSOC))
                {
                        // generate string type values (ie: USD)
                        $this->currencies[$currencies['code']] = array(
                                'symbol_left' => $currencies['symbol_left'],
                                'symbol_right' => $currencies['symbol_right'],
                                'decimal_point' => $currencies['decimal_point'],
                                'thousands_point' => $currencies['thousands_point'],
                                'decimal_places' => $currencies['decimal_places'],
                                'rate' => $currencies['rate'],
                                'currency_id' => $currencies['currency_id'],
                                'currency_name' => $currencies['currency_name'],
                                'currency_abbrev' => $currencies['currency_abbrev']
                        );
                        
                        // generate integer type values (ie: 1)
                        $this->currencies[$currencies['currency_id']] = array(
                                'symbol_left' => $currencies['symbol_left'],
                                'symbol_right' => $currencies['symbol_right'],
                                'decimal_point' => $currencies['decimal_point'],
                                'thousands_point' => $currencies['thousands_point'],
                                'decimal_places' => $currencies['decimal_places'],
                                'rate' => $currencies['rate'],
                                'code' => $currencies['code'],
                                'currency_name' => $currencies['currency_name'],
                                'currency_abbrev' => $currencies['currency_abbrev']
                        );
                }
        }
    
        /**
        * Function to properly format a dollar value based on the database currency settings (symbols, decimal places, thousands place, etc)
        *
        */
        function format($number = 0, $currencyid = 0, $hidesymbols = false, $forcedecimalhide = false)
        {
                global $currencies, $ilconfig;
        
                $html = '';
        
                if ($currencyid == 0)
                {
                        $currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
                }
                
                // remove user input formatting and get a solid number with only decimals
                $number = $this->string_to_number($number) * 1;
                
                if ($hidesymbols == false)
                {
                        $html .= $this->currencies["$currencyid"]['symbol_left'];
                        
                        if ($forcedecimalhide)
                        {
                                $html .= number_format($number);
                        }
                        else
                        {
                                $html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
                        }
                        
                        $html .= $this->currencies["$currencyid"]['symbol_right'];
                }
                else
                {
                        if ($forcedecimalhide)
                        {
                                $html .= number_format($number);
                        }
                        else
                        {
                                $html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
                        }
                }
                
                return $html;
        }
    function format_no_text($number = 0, $currencyid = 0, $hidesymbols = false, $forcedecimalhide = false)
        {
                global $currencies, $ilconfig;
        
                $html = '';
        
                if ($currencyid == 0)
                {
                        $currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
                }
                
                // remove user input formatting and get a solid number with only decimals
                $number = $this->string_to_number($number) * 1;
                
                if ($hidesymbols == false)
                {
                        $html .= '$';
                        
                        if ($forcedecimalhide)
                        {
                                $html .= number_format($number);
                        }
                        else
                        {
                                $html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
                        }
                        
                        $html .= $this->currencies["$currencyid"]['symbol_right'];
                }
                else
                {
                        if ($forcedecimalhide)
                        {
                                $html .= number_format($number);
                        }
                        else
                        {
                                $html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
                        }
                }
                
                return $html;
        }
        
	
        function format_real_no($number = 0, $currencyid = 0, $hidesymbols = false, $forcedecimalhide = false)
        {
                global $currencies, $ilconfig;
        
                $html = '';
        
                if ($currencyid == 0)
                {
                        $currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
                }
				$negative=false;
				
                if($number<0)
				{
				$negative=true;
				}
                // remove user input formatting and get a solid number with only decimals
//                $number = $this->string_to_number($number) * 1;
$number=abs($number);
                if ($hidesymbols == false)
                {
				                  
                        
                        if ($forcedecimalhide)
                        {
                                $realno= number_format($number);
                        }
                        else
                        {
                                $realno= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
                        }
                        
                       
								if($negative==true)
								{
									$html.='<font color="#FF0000">(-'.$this->currencies["$currencyid"]['symbol_left'].$realno.$this->currencies["$currencyid"]['symbol_right'].')</font>';
								}else
								{
									$html.=$this->currencies["$currencyid"]['symbol_left'].$realno.$this->currencies["$currencyid"]['symbol_right'];
								}
                }
                else
                {
                        if ($forcedecimalhide)
                        {
                                $html .= number_format($number);
                        }
                        else
                        {
                                $html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
                        }
                }
                
                return $html;
        }
    
        /**
        * Function to fetch the default currency id installed for the marketplace
        *
        */
        function fetch_default_currencyid()
        {
                global $ilance, $myapi, $ilconfig;
                (isset($ilconfig['globalserverlocale_defaultcurrency']) ? $cid = $ilconfig['globalserverlocale_defaultcurrency'] : $cid = '1');
                return $cid;
        }
        
        /**
        * Function to fetch a user's default currency setup when they registered or edited their profile
        *
        */
        function fetch_user_currency($userid = 0)
        {
                global $ilance, $myapi;
        
                $sql = $ilance->db->query("
                        SELECT currencyid
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);        
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $cur = $ilance->db->fetch_array($sql);
                        return $cur['currencyid'];
                }
                else
                {
                        return '1';
                }
        }
        
        /**
        * Function to build a currency selector pulldown menu element
        *
        */
        function pulldown($inputtype = '', $variableinfo = '')
        {
                global $ilance, $myapi, $ilconfig;
        
                $sql = $ilance->db->query("
                        SELECT currency_id, currency_name, currency_abbrev
                        FROM " . DB_PREFIX . "currency
                ", 0, null, __FILE__, __LINE__);
                if ($inputtype == 'admin')
                {
                        $html = '<select name="config[' . $variableinfo . ']" style="font-family: verdana">';
                        
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '<option value="' . $res['currency_id'] . '"';
                                if ($res['currency_id'] == $ilconfig['globalserverlocale_defaultcurrency'])
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $res['currency_abbrev'] . ' ' . stripslashes($res['currency_name']) . '</option>';
                        }
                }
                else
                {
                        $html = '<select name="currencyid" style="font-family: verdana">';
                        
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
                                        $sqlprefs = $ilance->db->query("
                                                SELECT currencyid
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $prefs = $ilance->db->fetch_array($sqlprefs);
                                }
                                else
                                {
                                        $prefs['currencyid'] = $ilconfig['globalserverlocale_defaultcurrency'];
                                }
                
                                $html .= '<option value="' . $res['currency_id'] . '"';
                                if ($res['currency_id'] == $prefs['currencyid'])
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $res['currency_abbrev'] . ' ' . stripslashes($res['currency_name']) . '</option>';
                        }
                }
                
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to take a string inputted by a user based on a dollar amount to be converted into 2 decimal places.
        * Example: 1,002.23 = 1002.23 or 12 = 12.00, etc.
        *
        * @param        integer         input price to be evaluated
        * 
        * @credit       developer       ratherodd.com
        * @return       integer         return 2 decimal place dollar amount ready for storing into database
        */
        function string_to_number($price)
        {
                // ratherodd.com
                $price = stripslashes(preg_replace('/^\s+|\s+$/', '', $price));
                $decPoint = strrpos($price, '.');
                $decComma = strrpos($price, ',');
                $thous = "' ";
                $first = $second = '';
                
                if ($decPoint > -1 && $decComma > -1)
                {
                        if ($decPoint > $decComma)
                        {
                                $thous .= ',';
                        }
                        else
                        {
                                $thous .= '.';
                        }
                        
                        $decMark = ',';
                }
                
                if ((strpos($price, ' ') OR strpos($price, "'")) AND $decComma)
                {
                        $decMark = ',';
                }
                
                if (strlen(substr($price, $decPoint + 1)) === 3 AND $decComma === false AND strpos($price, '.') < $decPoint)
                {
                        $thous .= '.';
                }
                
                if (strlen(substr($price, $decComma+1)) === 3 AND $decPoint === false AND strpos($price, ',') < $decComma)
                {
                        $thous .= ',';
                }
                
                preg_match('/^(?:(\d{1,3}(?:(?:(?:[' . $thous . ']\d{3})+)?)?|\d+)?([,.]\d{1,})?|\d+)$/', $price, $matches);
                
                if (!isset($matches))
                {
                        //return false;
                        return $price;
                }
                
                if (!isset($matches[1]) AND !isset($matches[2]) AND isset($matches[0]))
                {
                        $matches[1] = $matches[0];
                }
                
                $dec = ((isset($matches[2]) AND $matches[2] AND strlen($matches[2]) === 4 AND !isset($decMark) AND $matches[1] !== '0') ? '' : '.');
                
                if (isset($matches[1]))
                {
                        $first = preg_replace("/[,' .]/", '', $matches[1]);
                }
                
                if (isset($matches[2]))
                {
                        $second = str_replace(',', $dec, $matches[2]);
                }
                
                return (float)($first . $second);
        }
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>