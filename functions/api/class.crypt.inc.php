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
* Crypt class to perform the majority of encryption and decryption functions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class crypt
{
        /**
        * Constructor
        */
	function crypt()
	{
	}
	
        /**
        * Callback function to handle characters that should be encrypted along with an encryption key
        *
        * @param       string        text
        * @param       string        encryption key
        *
        * @return      string        Returns encrypted text
        */
	function key_ed($txt = '', $encrypt_key = '')
	{
		$ctr = 0;
		$tmp = '';
                
		for ($i = 0; $i < mb_strlen($txt); $i++)
		{
			if ($ctr == mb_strlen($encrypt_key))
                        {
                                $ctr = 0;
                        }
			$tmp .= mb_substr($txt, $i, 1) ^ mb_substr($encrypt_key, $ctr, 1);
			$ctr++;
		}
                
		return $tmp;
	}

        /**
        * Function to process and encrypt a text string along with an encryption key
        *
        * @param       string        text
        * @param       string        encryption key
        *
        * @return      string        Returns encrypted text
        */
	function encrypt($txt = '', $key = '')
	{
                $ctr = 0;
		$tmp = '';
		srand((double)microtime()*1000000);
		$encrypt_key = md5(rand(0, 32000));
                
		for ($i = 0; $i < mb_strlen($txt); $i++)
		{
                        if ($ctr == mb_strlen($encrypt_key))
                        {
                                    $ctr = 0;
                        }
                        $tmp .= mb_substr($encrypt_key, $ctr, 1) . (mb_substr($txt, $i, 1) ^ mb_substr($encrypt_key, $ctr, 1));
                        $ctr++;
		}
                
		return $this->key_ed($tmp,$key);
	}

	/**
        * Function to process and decrypt a text string along with an encryption key
        *
        * @param       string        text
        * @param       string        encryption key
        *
        * @return      string        Returns unencrypted text
        */
        function decrypt($txt = '', $key = '')
	{
                $tmp = '';
		$txt = $this->key_ed($txt, $key);
                
		for ($i = 0; $i < mb_strlen($txt); $i++)
		{
                        $md5 = mb_substr($txt, $i, 1);
                        $i++;
                        $tmp .= (mb_substr($txt, $i, 1) ^ $md5);
		}
                
		return $tmp;
        }
        
        /**
        * Function to process and encrypt a text string based on 3 primary encryption keys
        *
        * @param       string        text plain
        * @param       string        encryption key 1
        * @param       string        encryption key 2
        * @param       string        encryption key 3
        *
        * @return      string        Returns encrypted text
        */
        function three_layer_encrypt($text_plain = '', $key1 = '', $key2 = '', $key3 = '')
        {
		return base64_encode($this->key_ed($this->encrypt($this->key_ed($text_plain, $key1), $key2), $key3));
        }

        /**
        * Function to process and decrypt a text string based on 3 primary encryption keys
        *
        * @param       string        text plain
        * @param       string        encryption key 1
        * @param       string        encryption key 2
        * @param       string        encryption key 3
        *
        * @return      string        Returns decrypted text
        */
        function three_layer_decrypt($text_encrypted = '', $key1 = '', $key2 = '', $key3 = '')
        {
		return $this->key_ed($this->decrypt($this->key_ed(base64_decode($text_encrypted), $key3), $key2), $key1);
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>