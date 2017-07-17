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

$phrase['groups'] = array(
        'wantads',
        'search',
        'feedback',
        'lancebb',
        'buying',
        'selling',
        'accounting',
        'rfp'
);

// #### setup script location ##################################################
define('LOCATION', 'main');

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
        'tabfx',
	'flashfix',
	'search',
        'wysiwyg'
);

// #### setup script location ##################################################

//error_reporting(E_ALL);
// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("main-timeline.php" => $phrase['_time_line']);

     
    
    
   


$sql=$ilance->db->query("SELECT DATE_FORMAT(date_time, '%Y') as dat  FROM " . DB_PREFIX . "time_line  GROUP BY DATE_FORMAT(date_time, '%Y') ORDER BY date_time DESC ");

$c='';


while ($res = $ilance->db->fetch_array( $sql, DB_ASSOC)){
    
  $c.='<b>'.$res['dat'].'</b><br/><br/>';
    
    
    
    $sql1=$ilance->db->query("SELECT DATE_FORMAT(date_time,'%Y-%m') as dat1  FROM ".DB_PREFIX."time_line  WHERE DATE_FORMAT(date_time,'%Y') = '".$res['dat']."' GROUP BY DATE_FORMAT(date_time, '%Y-%m') ORDER BY DATE_FORMAT(date_time, '%Y-%m') DESC");
    
    
    while ($res1 = $ilance->db->fetch_array( $sql1, DB_ASSOC)){
        
       $c.='<strong>'.date('F',strtotime($res1['dat1'])).'</strong><br/><br/>';
        
        
         $sql2=$ilance->db->query("SELECT *  FROM ".DB_PREFIX."time_line  WHERE DATE_FORMAT(date_time,'%Y-%m') = '".$res1['dat1']."'");
    
       
         while ($res2 = $ilance->db->fetch_array( $sql2, DB_ASSOC)){
            
           
        
         $c.=strtr($ilance->bbcode->bbcode_to_html($res2['message']),"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","").'<br/><br/><br/>';
        
        
    }
      
    
   
        
    }
    
}
 

 
 $pprint_array = array('html','login_include','c'); 
 
 
 $ilance->template->fetch('main', 'time_line1.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
    exit();
?>