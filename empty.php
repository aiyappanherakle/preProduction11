<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1575
|| # -------------------------------------------------------------------- # ||
|| # Customer License # =ryotOqStzEoc1gDhm2kyaoC2VZLPe-ZTcK=-2d-y-SXgzbKia
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
 
// #### setup script location ##################################################
define('LOCATION', 'main');
// #### require backend ########################################################
require_once('./functions/config.php');
// #### setup default breadcrumb ###############################################
 
        $pprint_array = array('myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','jobcount','itemcount','recentlyviewedflash','tagcloud','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
    
        $ilance->template->fetch('main', 'dfdf.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
 
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>