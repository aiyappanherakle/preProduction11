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
* Template files class to perform recursive array operations for files and folders in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class template_files extends template
{ 
        var $newarr = array();
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function loop($stack) 
        { 
                if (count($stack) > 0)
                { 
                        $arr = array(); 
                        foreach($stack as $key => $value) 
                        { 
                                array_push($this->newarr, $stack[$key]); 
                                if ($dir = @opendir($stack[$key])) 
                                { 
                                        while (($file = readdir($dir)) !== false) 
                                        { 
                                                if (($file != '.') AND ($file != '..')) 
                                                { 
                                                        array_push($arr, $file); 
                                                } 
                                        } 
                                } 
                                @closedir($dir); 
                        } 
                        $this->loop($arr); 
                } 
                else 
                { 
                    $sorted = sort($this->newarr); 
                    return($sorted); 
               } 
        } 
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>