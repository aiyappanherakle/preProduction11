<?php 
require_once('./../functions/config.php');error_reporting(E_ALL);
echo $taxamount1 = fetch_amount(4961,861, 'buynow', 0);
echo "asdasd";

function fetch_amount($userid = 0, $total, $taxtype, $formatted = 0)
        {
                global $ilance, $myapi;
                
                $return = '';
        
            
				
				$user_sql = $ilance->db->query("SELECT country, state, zip_code FROM " . DB_PREFIX . "users where user_id='".$userid."'", 0, null, __FILE__, __LINE__);
			$user_details=$ilance->db->fetch_array($user_sql);
			$usercountry=$user_details['country'];
			$userstate=$user_details['state'];
			echo $userzipcode=$user_details['zip_code'];
			$userzipcode=91702;
				if(!empty($userzipcode))
				{
				$sql = $ilance->db->query("
                        SELECT taxlabel, countryid, state, city, amount, invoicetypes, entirecountry
                        FROM " . DB_PREFIX . "taxes where zipcode='".$userzipcode."'
                ", 0, null, __FILE__, __LINE__);
				}else
				{
                $sql = $ilance->db->query("
                        SELECT taxlabel, countryid, state, city, amount, invoicetypes, entirecountry
                        FROM " . DB_PREFIX . "taxes
                ", 0, null, __FILE__, __LINE__);
				}
                if ($ilance->db->num_rows($sql) > 0)
                {
				
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                // country checkup
                                if ($res['countryid'] == $usercountry)
                                {
                                        // state or province checkup
                                        if ($res['entirecountry'] OR !empty($res['state']) AND mb_strtolower($res['state']) == mb_strtolower($userstate))
                                        {
                                                // we found a match (country and state both match for this user)
                                                if (!empty($res['invoicetypes']))
                                                {
                                                        $invoicetypetax = unserialize($res['invoicetypes']);
                                                        foreach ($invoicetypetax AS $invoicetype => $value)
                                                        {
                                                                if ($invoicetype == $taxtype)
                                                                {
                                                                        if ($formatted)
                                                                        {
                                                                                $return .= $ilance->currency->format(($total * $res['amount'] / 100)) . ' ' . stripslashes($res['taxlabel']) . '<!-- @ ' . $res['amount'] . '%-->, ';
                                                                        }
                                                                        else
                                                                        {
                                                                                $return += ($total * $res['amount'] / 100);
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                        
                        if (isset($formatted) AND $formatted)
                        {
                                $return = mb_substr($return, 0, -2);
                        }
                }
        
                return $return;
        }
		
		
?>