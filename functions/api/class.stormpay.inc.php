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
* Stormpay class to perform the majority of functions including ipn response handling
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class stormpay
{
        var $stormpay_post_vars = array();
        var $stormpay_response;
        var $error_email;
        var $currencies_accepted = array('USD', 'GBP', 'EUR', 'CAD');
        
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted stormpay keys and values
        *
        * @return      array
        */
        function stormpay($stormpay_post_vars = array())
        {
                if (!empty($stormpay_post_vars))
                {
                        $this->stormpay_post_vars = $stormpay_post_vars;
                }
                else
                {
                        $this->stormpay_post_vars = array();
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
                <form method="post" action="https://www.stormpay.com/stormpay/handle_gen.php" accept-charset="UTF-8" style="margin:0px">
                <input type="hidden" name="payee_email" value="' . $merchantid . '" />
                <input type="hidden" name="product_name" value="' . $description . '" />
                <input type="hidden" name="description" value="' . $description . '" />
                <input type="hidden" name="unit_price" value="' . $amount . '">
                <input type="hidden" name="quantity" value="1" />
                <input type="hidden" name="user1" value="' . $customencrypted . '" />
                <input type="hidden" name="require_IPN" value="1" />
                <input type="hidden" name="notify_URL" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_stormpay" />
                <input type="hidden" name="return_URL" value="' . HTTP_SERVER . $ilpage['accounting'] . '" /> 
                <input type="hidden" name="cancel_URL" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />';

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
                <form method="post" action="https://www.stormpay.com/stormpay/handle_gen.php" accept-charset="UTF-8" style="margin:0px">
                <input type="hidden" name="payee_email" value="' . $merchantid . '" />
                <input type="hidden" name="product_name" value="' . $description . '" />
                <input type="hidden" name="description" value="' . $description . '" />
                <input type="hidden" name="unit_price" value="' . $amount . '">
                <input type="hidden" name="quantity" value="1" />
                <input type="hidden" name="user1" value="' . $customencrypted . '" />
                <input type="hidden" name="require_IPN" value="1" />
                <input type="hidden" name="notify_URL" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_stormpay" />
                <input type="hidden" name="return_URL" value="' . $returnurl . '" /> 
                <input type="hidden" name="cancel_URL" value="' . $returnurl . '" />';

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
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_recurring_payment_form($payer_email = '', $amount = 0, $units = '', $length = 0, $title = '', $description = '', $currency = '', $customencrypted = '', $onsubmit = '')
        {
                global $ilance, $ilpage, $ilconfig;
                
                $ilance->subscription = construct_object('api.subscription');
                
                $html = '<form method="post" action="https://www.stormpay.com/stormpay/handle_gen.php" accept-charset="UTF-8" style="margin:0px" onsubmit="' . $onsubmit . '">
                <input type="hidden" name="product_name" value="' . $description . '" />
                <input type="hidden" name="payee_email" value="' . $payer_email . '" />
                <input type="hidden" name="recurrent_charge" value="' . $amount . '" />
                <input type="hidden" name="duration" value="' . $ilance->subscription->subscription_length($units, $length) . '">
                <input type="hidden" name="description" value="' . $description . '" />
                <input type="hidden" name="require_IPN" value="1" />
                <input type="hidden" name="notify_URL" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_stormpay" />
                <input type="hidden" name="return_URL" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
                <input type="hidden" name="cancel_URL" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
                <input type="hidden" name="pending_URL" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
                <input type="hidden" name="user1" value="' . $customencrypted . '" />';
                
                return $html;
        }
        
        /**
        * Function for determining (internally) if the processed transaction has been verified (true or false)
        *
        * @return      bool          true or false
        */
        function is_verified()
        {
                global $ilconfig;
                
                if ($this->get_payment_status() == 'SUCCESS')
                {
                        // Note: The owner of the marketplace needs to put the following IPN MD5 Hashing
                        // variables into their StormPay IPN setup as follows:
                        // transaction_id; transaction_date; amount; user_id; user1
                        
                        $calc_hash = md5($this->get_transaction_id() . ':' . $this->stormpay_post_vars['transaction_date'] . ':' . md5($ilconfig['stormpay_secret_code']) . ':' . $this->get_transaction_amount() . ':' . $this->stormpay_post_vars['user_id'] . ':' . $this->stormpay_post_vars['user1']);
                        $recv_hash = rawurldecode($this->stormpay_post_vars['secret_code']);
                        
                        if ($calc_hash === $recv_hash)
                        {
                                return true;
                        }
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
                return $this->stormpay_post_vars['status'];
        }
        
        /**
        * Function for storing the processed payment transaction id for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_id()
        {
                return $this->stormpay_post_vars['transaction_id'];    
        }
        
        /**
        * Function for storing the processed payment transaction amount for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_amount()
        {
                return $this->stormpay_post_vars['unit_price'];    
        }
                
        /**
        * Function for sending any error emails from the process to the administrator.
        *
        * @param       string         error message text
        * 
        * @return      nothing
        */
        function error_out($text)
        {
                $date = date("D M j G:i:s T Y", time());
                $message = $text;
                $message .= "\n\n".SITE_NAME." received the following IPN response from Stormpay.  Please use the following information for debug purposes only:\n\n
                *****************************\n";
                @reset($this->stormpay_post_vars);
                while (@list($key, $value) = @each($this->stormpay_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n
                *****************************\n\n";
                if ($this->error_email)
                {
                        send_email($this->error_email, 'Stormpay IPN Gateway Error', $message, SITE_EMAIL);
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>