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
* Paypal class to perform the majority of functions including ipn response handling.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class authorizenet
{
        var $authnet_post_vars = array();
        var $authnet_response;
        var $timeout;
        var $error_email;
        var $send_time;
        
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted authnet keys and values
        *
        * @return      array
        */
        function authorizenet($authnet_post_vars = array())
        {
                if (!empty($authnet_post_vars))
                {
                        $this->authnet_post_vars = $authnet_post_vars;
                }
                else
                {
                        $this->authnet_post_vars = array();
                }
        }
        
        /**
        * Function for printing the recurring payment processor custom generated form via POST method.
        *
        * @param       string        subscription start date (default now/today) format: YYYY-MM-DD
        * @param       integer       subscription id
        * @param       string        amount to process
        * @param       integer       total occurrences (max 9999) default 9999 (no end date until user cancels themselve)
        * @param       string        trial amount to process (default 0)
        * @param       integer       trial occurrences (max 99) default 0
        * @param       string        unit (format: months or days)
        * @param       integer       length (format: can be 1 - 12 or 7 - 365)
        * @param       string        transaction description
        * @param       string        js onsubmit form code
        * @param       integer       ismodify subscription modify update for authorize.net (default 0 = no)
        * @param       integer       iscancel subscription cancellation request (default 0 = no)
        * @param       string        custom subscription info
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_recurring_payment_form($startdate = '', $subscriptionid = 0, $roleid = 0, $amount = 0, $totaloccurrences = 9999, $trialamount = 0, $trialoccurrences = 0, $units = '', $length = 0, $description = '', $onsubmit = '', $ismodify = 0, $iscancel = 0)
        {
                global $ilance, $ilpage, $ilconfig, $show;
                
                $html = '<form name="ilform" action="' . HTTPS_SERVER . $ilpage['payment'] . '" method="post" accept-charset="UTF-8" onsubmit="' . $onsubmit . '" style="margin:0px">
                <input type="hidden" name="do" value="_authorizenet" />
                <input type="hidden" name="refId" value="" />
                <input type="hidden" name="subscriptionid" value="' . $subscriptionid . '" />
                <input type="hidden" name="roleid" value="' . $roleid . '" />
                <input type="hidden" name="name" value="' . $description . '" />
                <input type="hidden" name="length" value="' . $this->format_length($length, $units) . '" />
                <input type="hidden" name="unit" value="' . $this->format_unit($length, $units) . '" />
                <input type="hidden" name="units" value="' . $units . '" />
                <input type="hidden" name="startDate" value="' . $startdate . '" />
                <input type="hidden" name="totalOccurrences" value="' . $totaloccurrences . '" />
                <input type="hidden" name="trialOccurrences" value="' . $trialoccurrences . '" />
                <input type="hidden" name="amount" value="' . $amount . '" />
                <input type="hidden" name="trialAmount" value="' . $trialamount . '" />';
                if ($ismodify)
                {
                        $html .= '<input type="hidden" name="mode" value="update" /><input type="hidden" name="subscriptionId" value="' . $subscriptionid . '" />';
                }
                else if ($iscancel)
                {
                        $html .= '<input type="hidden" name="mode" value="cancel" /><input type="hidden" name="subscriptionId" value="' . $subscriptionid . '" />';
                }
                else
                {
                        $html .= '<input type="hidden" name="mode" value="create" />';
                }
                
                return $html;
        }
        
        function format_length($length = 0, $unit = '')
        {
                if ($unit == 'Y')
                {
                       $length = ($length * 12);
                }
                
                return $length;
        }
        
        function format_unit($length = 0, $unit = '')
        {
                if ($unit == 'Y')
                {
                       $unit = 'months';
                }
                else if ($unit == 'M')
                {
                       $unit = 'months'; 
                }
                else if ($unit == 'D')
                {
                        $unit = 'days';
                }
                
                return $unit;
        }
        
        /**
        * Function to build a valid authorize.net recurring subscription document
        *
        * @param       string       mode (create, update or cancel) default is create
        * 
        * @return      nothing
        */
        function build_recurring_subscription_xml($mode = 'create', $data = array())
        {
                global $ilance, $ilconfig;
                
                $cc_user = $ilance->db->fetch_field(DB_PREFIX . "payment_configuration", "name = 'cc_login' AND configgroup = 'authnet'", "value");
                $cc_auth = $ilance->db->fetch_field(DB_PREFIX . "payment_configuration", "name = 'cc_key' AND configgroup = 'authnet'", "value");
                
                $xml = '';
                if ($mode == 'create')
                {
                        $xml =
                        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                        "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                        "<merchantAuthentication>".
                                "<name>" . $cc_user . "</name>".
                                "<transactionKey>" . $cc_auth . "</transactionKey>".
                        "</merchantAuthentication>".
                        "<refId>" . $data['refId'] . "</refId>".
                        "<subscription>".
                                "<name>" . $data['name'] . "</name>".                        
                                "<paymentSchedule>".
                                        "<interval>".
                                        "<length>". $data['length'] ."</length>".
                                        "<unit>". $data['unit'] ."</unit>".
                                        "</interval>".
                                        "<startDate>" . $data['startDate'] . "</startDate>".
                                        "<totalOccurrences>". $data['totalOccurrences'] . "</totalOccurrences>".
                                        "<trialOccurrences>". $data['trialOccurrences'] . "</trialOccurrences>".
                                "</paymentSchedule>".                        
                                "<amount>". $data['amount'] ."</amount>".
                                "<trialAmount>" . $data['trialAmount'] . "</trialAmount>".
                                "<payment>".
                                        "<creditCard>".
                                                "<cardNumber>" . $data['cardNumber'] . "</cardNumber>".
                                                "<expirationDate>" . $data['expirationDate'] . "</expirationDate>".
                                        "</creditCard>".
                                "</payment>".
                                "<billTo>".
                                        "<firstName>". $data['firstName'] . "</firstName>".
                                        "<lastName>" . $data['lastName'] . "</lastName>".
                                "</billTo>".
                        "</subscription>".
                        "</ARBCreateSubscriptionRequest>";
                }
                else if ($mode == 'update')
                {
                        $xml =
                        "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
                        "<ARBUpdateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
                        "<merchantAuthentication>".
                                "<name>" . $cc_user . "</name>".
                                "<transactionKey>" . $cc_auth . "</transactionKey>".
                        "</merchantAuthentication>".
                        "<subscriptionId>" . $data['subscriptionId'] . "</subscriptionId>".
                        "<subscription>".
                                "<payment>".
                                        "<creditCard>".
                                                "<cardNumber>" . $data['cardNumber'] ."</cardNumber>".
                                                "<expirationDate>" . $data['expirationDate'] . "</expirationDate>".
                                        "</creditCard>".
                                "</payment>".
                        "</subscription>".
                        "</ARBUpdateSubscriptionRequest>";
                }
                else if ($mode == 'cancel')
                {
                        $xml =
                        "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
                        "<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
                        "<merchantAuthentication>".
                                "<name>" . $cc_user . "</name>".
                                "<transactionKey>" . $cc_auth . "</transactionKey>".
                        "</merchantAuthentication>" .
                        "<subscriptionId>" . $data['subscriptionId'] . "</subscriptionId>".
                        "</ARBCancelSubscriptionRequest>";
                }
                
                return $xml;      
        }
        
        /**
        * Function for sending a repsonse to the payment gateway for verification of payment authentication and status.
        *
        * @param       string      gateway php communication mode (default curl) (curl or fsockopen can be used)
        * @param       string      xml payment gateway data to send for a response
        * @param       string      host name of the merchant gateway
        * @param       string      server path of the script we're posting (optional)
        *
        * @return      nothing
        */
        function send_response($type = 'curl', $xml = '', $host = '', $path = '')
        {
                if ($type == 'curl')
                {
                        if (!extension_loaded('curl'))
			{
                                $response = false;
				$this->error_out('Warning: could not communicate with Authorize.Net through curl (function does not exist on this server)');
			}
                        else
                        {
                                $posturl = $host . $path;
                                
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $posturl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
                                curl_setopt($ch, CURLOPT_HEADER, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 240);
                                
                                $response = curl_exec($ch);
                                
                                if (curl_errno($ch) > 0)
                                {
                                        $response = false;
                                        $this->error_out(curl_error($ch));
                                }
                                
                                $this->authnet_response = $response;
                        }
                }
                else if ($type == 'fsockopen')
                {
                        $posturl = "ssl://" . $host;
                        
                        $header = "Host: $host\r\n";
                        $header .= "User-Agent: " . USERAGENT . "\r\n";
                        $header .= "Content-Type: text/xml\r\n";
                        $header .= "Content-Length: " . strlen($xml) . "\r\n";
                        $header .= "Connection: close\r\n\r\n";
                        
                        $fp = fsockopen($posturl, 443, $errno, $errstr, $this->timeout);
                        if (!$fp)
                        {
                                $response = false;
                                $this->authnet_response = $response;
                                $this->error_out('Warning: could not communicate with Authorize.Net via PHP function: fsockopen() error: ' . $errstr);
                        }
                        else
                        {
                                $this->authnet_response = '';
                                
                                fputs($fp, "POST $path HTTP/1.1\r\n");
                                fputs($fp, $header . $xml);
                                fwrite($fp, $out);
                                $this->send_time = time();
                                while (!feof($fp))
                                {
                                        $this->authnet_response .= fgets($fp, 128);
                                        if ($this->send_time < time() - $this->timeout)
                                        {
                                                $this->error_out('Timed out waiting for a response from Authorize.Net. (' . $this->timeout . ' seconds)');
                                        }
                                }
                                fclose($fp);
                                
                                $response = $this->authnet_response;
                        }
                }
                else
                {
                        $response = false;
                        $this->authnet_response = $response;
                        $this->error_out('Warning: could not communicate with Authorize.Net gateway due to unsupported connection function');
                }
                
                return $response;
        }
        
        /**
        * Function for parsing Authorize.Net response
        *
        * @param       string         xml content data
        * 
        * @return      array          Returns formatted array based on Authorize.Net response values
        */
        function parse_return($content = '')
        {
                $refId = $this->substring_between($content, '<refId>', '</refId>');
                $resultCode = $this->substring_between($content, '<resultCode>', '</resultCode>');
                $code = $this->substring_between($content, '<code>', '</code>');
                $text = $this->substring_between($content, '<text>', '</text>');
                $subscriptionId = $this->substring_between($content, '<subscriptionId>', '</subscriptionId>');
                
                return array($refId, $resultCode, $code, $text, $subscriptionId);
        }
        
        /**
        * Function for parsing xml response from Authorize.Net gateway
        *
        * @param       string         haystack
        * @param       string         start tag
        * @param       string         end tag
        * 
        * @return      string         Returns string between start and eng tags
        */
        function substring_between($haystack, $start, $end) 
        {
                if (strpos($haystack, $start) === false || strpos($haystack, $end) === false) 
                {
                        return false;
                } 
                else 
                {
                        $start_position = strpos($haystack, $start) + strlen($start);
                        $end_position = strpos($haystack, $end);
                        
                        return substr($haystack, $start_position, $end_position - $start_position);
                }
        }
        
        /**
        * Function for sending any error emails from the process to the administrator.
        *
        * @param       string         error message text
        * 
        * @return      nothing
        */
        function error_out($text = '')
        {
                $date = date("D M j G:i:s T Y", time());
                $message = $text;
                $message .= "\n\n" . SITE_NAME . " received the following response from Authorize.Net.  Please use the following information for debug purposes only:\n\n*****************************\n";
                
                @reset($this->authnet_post_vars);
                while (@list($key, $value) = @each($this->authnet_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n*****************************\n\n";
                
                if ($this->error_email)
                {
                        send_email($this->error_email, 'Authorize.Net Gateway Error', $message, SITE_EMAIL);
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>