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
* Timer class to debug how long a function takes within ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class timer
{
        var $stime;
        var $etime;
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function timer()
        {
                $this->stime = 0.0;
        }
       
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function get_microtime()
        {
                $tmp = explode(" ",microtime());
                $rtime = (double)$tmp[0] + (double)$tmp[1];
                return $rtime;
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function start()
        {
                $this->stime = $this->get_microtime();
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function stop()
        {
                $this->etime = $this->get_microtime();
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function get($decimal = 3)
        {
                return round(($this->etime - $this->stime), $decimal);
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>