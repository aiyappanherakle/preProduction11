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

// #### setup script location ##################################################
define('LOCATION', 'main');

// #### load required javascript ###############################################
$jsinclude = array(
	'jquery',
	'functions',
	'autocomplete'
);

 require_once('./functions/config.php');


// #### setup default breadcrumb ###############################################
$navcrumb = array("discount_grading.php" => "discount grading");

 $pprint_array = array('html','login_include','c');


 $ilance->template->fetch('main', 'discountgrading.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
    exit();
?>