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
class paypal
{
        var $paypal_post_vars = array();
        var $paypal_response;
        var $timeout;
        var $error_email;
        var $send_time;
        var $currencies_accepted = array('USD', 'GBP', 'EUR', 'CAD');
        
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted paypal keys and values
        *
        * @return      array
        */
        function paypal($paypal_post_vars = array())
        {
                if (!empty($paypal_post_vars))
                {
                        $this->paypal_post_vars = $paypal_post_vars;
                }
                else
                {
                        $this->paypal_post_vars = array();
                }
        }
        
        /**
        * Function for printing the payment processor custom generated form via POST method.
        *
        * @param       integer       user id
        * @param       string        payer email address
        * @param       string        amount to process
        * @param       integer       associated invoice id
        * @param       integer       associated subscription id
        * @param       string        transaction description
        * @param       string        merchant id
        * @param       string        master currency
        * @param       string        pass phrase used in some processors (usually stored with processor also)
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       bool          defines if this payment form should return a test - mode parameter (if available)
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_payment_form($userid = 0, $payer_email = '', $amount = 0, $invoiceid = 0, $subscriptionid = 0, $description = '', $merchantid = '', $currency = '', $passphrase = '', $customencrypted = '', $testmode = 0)
        {
                global $ilpage;
                
                $html = '
                <form method="post" action="https://www.paypal.com/cgi-bin/webscr"   onsubmit="combine_trigger('.$invoiceid.')"  accept-charset="UTF-8" style="margin:0px">
                <input type="hidden" name="cmd" value="_xclick" />
                <input type="hidden" name="business" value="' . $merchantid . '" />
                <input type="hidden" name="return" value="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&subcmd=complete&id=' . $invoiceid . '" />
                <input type="hidden" name="custom" value="' . $customencrypted . '" />
                <input type="hidden" name="undefined_quantity" value="0" />
                <input type="hidden" name="item_name" value="' . stripslashes($description) . '" />
                <input type="hidden" name="amount" value="' . $amount . '" />
                <input type="hidden" name="currency_code" value="' . $currency . '" />
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="cancel_return" value="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&subcmd=cancel&id=' . $invoiceid . '" />
                <input type="hidden" name="no_note" value="1" />
                <input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />
                ' . ((isset($payer_email) AND !empty($payer_email)) ? '<input type="hidden" name="payer_email" value="' . $payer_email . '" />' : '');

                return $html;   
        }

		 function print_payment_form_staff_split_invoice($userid = 0, $payer_email = '', $amount = 0, $invoiceid = 0, $subscriptionid = 0, $description = '', $merchantid = '', $currency = '', $passphrase = '', $customencrypted = '', $testmode = 0)
        {
                global $ilpage;
                
                $html = '
                <form method="post" action="https://www.paypal.com/cgi-bin/webscr"   onsubmit="combine_trigger('.$invoiceid.')"  accept-charset="UTF-8" style="margin:0px">
                <input type="hidden" name="cmd" value="_xclick" />
                <input type="hidden" name="business" value="' . $merchantid . '" />
                <input type="hidden" name="return" value="' . HTTPS_SERVER . 'staff/split_next_invoice.php?cmd=did_invoice&subcmd=complete&id=' . $invoiceid . '&uid='.$userid.'" />
                <input type="hidden" name="custom" value="' . $customencrypted . '" />
                <input type="hidden" name="undefined_quantity" value="0" />
                <input type="hidden" name="item_name" value="' . stripslashes($description) . '" />
                <input type="hidden" name="amount" value="' . $amount . '" />
                <input type="hidden" name="currency_code" value="' . $currency . '" />
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="cancel_return" value="' . HTTPS_SERVER . 'staff/split_next_invoice.php?cmd=did_invoice&subcmd=cancel&id=' . $invoiceid . '&uid='.$userid.'" />
                <input type="hidden" name="no_note" value="1" />
                <input type="hidden" name="notify_url" value="' . HTTPS_SERVER . 'staff/split_next_invoice_payment.php?do=_paypal&uid='.$userid.'" />
                ' . ((isset($payer_email) AND !empty($payer_email)) ? '<input type="hidden" name="payer_email" value="' . $payer_email . '" />' : '');

                return $html;   
        }


        function print_payment_form_staff($userid = 0, $payer_email = '', $amount = 0, $invoiceid = 0, $subscriptionid = 0, $description = '', $merchantid = '', $currency = '', $passphrase = '', $customencrypted = '', $testmode = 0)
        {
                global $ilpage;
                
                $html = '
                <form method="post" action="https://www.paypal.com/cgi-bin/webscr"   onsubmit="combine_trigger('.$invoiceid.')"  accept-charset="UTF-8" style="margin:0px">
                <input type="hidden" name="cmd" value="_xclick" />
                <input type="hidden" name="business" value="' . $merchantid . '" />
                <input type="hidden" name="return" value="' . HTTPS_SERVER . 'staff/users_invoice.php?cmd=did_invoice&subcmd=complete&id=' . $invoiceid . '&uid='.$userid.'" />
                <input type="hidden" name="custom" value="' . $customencrypted . '" />
                <input type="hidden" name="undefined_quantity" value="0" />
                <input type="hidden" name="item_name" value="' . stripslashes($description) . '" />
                <input type="hidden" name="amount" value="' . $amount . '" />
                <input type="hidden" name="currency_code" value="' . $currency . '" />
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="cancel_return" value="' . HTTPS_SERVER . 'staff/users_invoice.php?cmd=did_invoice&subcmd=cancel&id=' . $invoiceid . '&uid='.$userid.'" />
                <input type="hidden" name="no_note" value="1" />
                <input type="hidden" name="notify_url" value="' . HTTPS_SERVER . 'staff/users_payment.php?do=_paypal&uid='.$userid.'" />
                ' . ((isset($payer_email) AND !empty($payer_email)) ? '<input type="hidden" name="payer_email" value="' . $payer_email . '" />' : '');

                return $html;   
        }
        
        /**
        * Function for printing the recurring payment processor custom generated form via POST method.
        *
        * @param       string        payer email address
        * @param       string        amount to process
        * @param       string        unit
        * @param       integer       length
        * @param       string        transaction description
        * @param       string        gateway currency to use
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       string        js onsubmit form code
        * @param       integer       ismodify subscription modify update for paypal
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_recurring_payment_form($payer_email = '', $subscriptionid = 0, $amount = 0, $units = '', $length = 0, $title = '', $description = '', $currency = '', $customencrypted = '', $onsubmit = '', $ismodify = 0)
        {
                global $ilance, $ilpage, $ilconfig, $show;
                
                $html = '<form name="ilform" action="https://www.paypal.com/cgi-bin/webscr" method="post" accept-charset="UTF-8" onsubmit="' . $onsubmit . '" style="margin:0px">
                <input type="hidden" name="cmd" value="_xclick-subscriptions" />
                <input type="hidden" name="business" value="' . $payer_email . '" />
                <input type="hidden" name="item_name" value="' . $description . '" />
                <input type="hidden" name="item_number" value="' . $subscriptionid . '" />
                <input type="hidden" name="currency_code" value="' . mb_strtoupper($currency) . '" />
                <input type="hidden" name="a3" value="' . $amount . '" />
                <input type="hidden" name="p3" value="' . $length . '" />
                <input type="hidden" name="t3" value="' . $units . '" />
                <input type="hidden" name="src" value="1" />
                <input type="hidden" name="sra" value="1" />
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="shipping" value="0.00" />
                <input type="hidden" name="return" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
                <input type="hidden" name="cancel_return" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
                <input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />
                <input type="hidden" name="custom" value="' . $customencrypted . '" />
                <input type="hidden" name="no_note" value="1" />
                <input type="hidden" name="undefined_quantity" value="0" />';
                if ($ismodify)
                {
                        $html .= '<input type="hidden" name="modify" value="1" />';
                        $show['subscriptionmodify'] = 1;
                }
                else
                {
                        $show['subscriptionmodify'] = 0;
                }
                
                return $html;
        }
        
        /**
        * Function for printing the payment processor custom generated form via POST method.
        *
        * @param       string        amount to process
        * @param       string        transaction description
        * @param       string        sellers payment email
        * @param       string        master currency
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       string        return url
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_direct_payment_form($amount = 0, $description = '', $merchantid = '', $currency = '', $customencrypted = '', $returnurl = '')
        {
                global $ilpage;
                
                $html = '
                <form method="post" action="https://www.paypal.com/cgi-bin/webscr" accept-charset="UTF-8" style="margin:0px">
                <input type="hidden" name="cmd" value="_xclick" />
                <input type="hidden" name="business" value="' . $merchantid . '" />
                <input type="hidden" name="return" value="' . $returnurl . '" />
                <input type="hidden" name="cancel_return" value="' . $returnurl . '" />
                <input type="hidden" name="custom" value="' . $customencrypted . '" />
                <input type="hidden" name="undefined_quantity" value="0" />
                <input type="hidden" name="item_name" value="' . stripslashes($description) . '" />
                <input type="hidden" name="amount" value="' . $amount . '" />
                <input type="hidden" name="currency_code" value="' . $currency . '" />
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="no_note" value="1" />
                <input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />';

                return $html;   
        }
        
        /**
        * Function for sending a repsonse to the payment gateway for verification of payment authentication and status.
        *
        * @return      nothing
        */
        function send_response()
        {
                if ($fp = fsockopen('www.paypal.com', 80, $errno, $errstr, $this->timeout))
                {
                        if (!empty($this->paypal_post_vars) AND is_array($this->paypal_post_vars))
                        {
                                foreach ($this->paypal_post_vars AS $key => $value)
                                {
                                        $values[] = "$key" . "=" . urlencode(stripslashes($value));
                                }
                                
                                $response = @implode('&', $values);
                                $response .= '&cmd=_notify-validate';
                                
                                fputs($fp, "POST /cgi-bin/webscr HTTP/1.0\r\n");
                                fputs($fp, "Host: https://www.paypal.com\r\n");
                                fputs($fp, "User-Agent: " . USERAGENT ."\r\n");
                                fputs($fp, "Accept: */*\r\n");
                                fputs($fp, "Accept: image/gif\r\n");
                                fputs($fp, "Accept: image/x-xbitmap\r\n");
                                fputs($fp, "Accept: image/jpeg\r\n");
                                fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
                                fputs($fp, "Content-Length: " . mb_strlen($response) . "\r\n\n");
                                fputs($fp, "$response\n\r");
                                fputs($fp, "\r\n");
                                
                                $this->send_time = time();
                                $this->paypal_response = '';                
                                while (!feof($fp))
                                {
                                        $this->paypal_response .= fgets($fp, 1024);
                                        if ($this->send_time < time() - $this->timeout)
                                        {
                                                $this->error_out('Timed out waiting for a response from PayPal. (' . $this->timeout . ' seconds)');
                                        }
                                }
                                fclose($fp);
                                
                                // #### WRITE PAYPAL IPN RESPONSE TO LOG FILE IN CACHE FOLDER ######
                                $fp2 = fopen(DIR_TMP . 'paypal-ipn-' . date('Y-m-d-H-i-s') . '.log', 'a');
                                fwrite($fp2, "BEGIN " . date('Y-m-d h:i:s') . "\n");
                                fwrite($fp2, "PAYPAL SENT: " . $response . "\n");
                                fwrite($fp2, "END " . date('Y-m-d h:i:s') . "\n");
                                fclose($fp2);   
                        }
                }
                else
                {
                        $this->error_out('Warning: could not communicate with Paypal.com via PHP function: fsockopen() error: ' . $errstr);            
                }        
        }
        
        /**
        * Function for determining (internally) if the processed transaction has been verified (true or false)
        *
        * @return      bool          true or false
        */
        function is_verified()
        {
                if (mb_ereg('VERIFIED', $this->paypal_response) OR $this->get_payment_status() == 'Completed')
                {
                        return true;
                }
                
                return false;
        }
        
        /**
        * Function for storing the processed payment status for later retrevial.
        *
        * @return      string         payment status
        */
        function get_payment_status()
        {
                return $this->paypal_post_vars['payment_status'];
        }
        
        /**
        * Function for storing the processed payment type for later retrevial.
        *
        * @return      string         payment type
        */
        function get_payment_type()
        {
                // echeck - payment funded with e-check
                // instant - payment was funded with paypal balance, credit card, or instant transfer
                return $this->paypal_post_vars['payment_type'];
        }
        
        /**
        * Function for storing the processed payment transaction id for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_id()
        {
                return $this->paypal_post_vars['txn_id'];    
        }
        
        /**
        * Function for storing the processed payment transaction amount for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_amount()
        {
                return $this->paypal_post_vars['payment_gross'];
        }
        
        /**
        * Function for storing the processed payment transaction type for later retrevial.
        *
        * @return      string         transaction type
        */
        function get_transaction_type()
        {
                return $this->paypal_post_vars['txn_type'];    
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
                $message .= "\n\n" . SITE_NAME . " received the following IPN response from Paypal.  Please use the following information for debug purposes only:\n\n*****************************\n";
                
                @reset($this->paypal_post_vars);
                while (@list($key, $value) = @each($this->paypal_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n*****************************\n\n";
                
                if ($this->error_email)
                {
                        send_email($this->error_email, 'Paypal IPN Gateway Error', $message, SITE_EMAIL);
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>