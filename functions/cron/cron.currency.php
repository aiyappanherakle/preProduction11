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

if (!isset($GLOBALS['ilance']->db))
{
    die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

($apihook = $ilance->api('cron_currency_start')) ? eval($apihook) : false;

$searchfor = '<Cube currency';
if (($fcontents = @file($ilconfig['globalserverlocale_defaultcurrencyxml'])))
{
        $i = 0;
        foreach ($fcontents AS $line)
        {
                if ($sp = mb_strpos($line, $searchfor))
                { 
                        $xmlarray = explode("'", $line);
                        $xmlabbrev = trim($xmlarray[3]);
                        $xmlrate[$i]['abbv'] = mb_strtoupper(trim($xmlarray[1]));
                        $xmlrate[$i]['rate'] = $xmlabbrev;                        
                        $i++;
                }
        }
        
        $rates = '';
        for ($x = 0; $x < $i; $x++)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "currency
                        SET rate = '" . $ilance->db->escape_string($xmlrate[$x]['rate']) . "',
                        time = '" . DATETIME24H . "' 
                        WHERE currency_abbrev = '" . $ilance->db->escape_string($xmlrate[$x]['abbv']) . "'
                ", 0, null, __FILE__, __LINE__);
                
                $rates .= $xmlrate[$x]['abbv'] . ' = ' . $xmlrate[$x]['rate'] . ', ';
        }
        
        if (!empty($rates))
        {
                $rates = mb_substr($rates, 0, -2);
        }
        
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "currency
                SET rate = '1.0000',
                time = '" . DATETIME24H . "'
                WHERE currency_abbrev = 'EUR'
        ", 0, null, __FILE__, __LINE__);
        
        ($apihook = $ilance->api('cron_currency_end')) ? eval($apihook) : false;
        
        log_cron_action('The following currency rates were updated: ' . $rates, $nextitem);
}
else
{
        log_cron_action('Error: currency rates could not be updated.  Could not execute php function file()', $nextitem);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>