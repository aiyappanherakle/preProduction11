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
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### load required phrase groups ############################################
$phrase['groups'] = array(
        'watchlist',
        'feedback'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'flashfix',
	'jquery'
);

// #### define top header nav ##################################################
$topnavlink = array(
	'watchlist'
);

// #### setup script location ##################################################
define('LOCATION', 'watchlist');

// #### require backend ########################################################
require_once('./functions/config.php');		
			
			
global $ilance, $myapi, $ilpage, $phrase, $ilconfig;

	   
	   
		$sql= $ilance->db->query("SELECT p.project_id
		FROM ilance_projects p 
		WHERE p.status = 'open' 
		group by p.project_id 
		ORDER BY p.project_id  desc
		limit 0,200
		");
		
		
		 
		if ($ilance->db->num_rows($sql) > 0) 
			{
			$i=0;
			while ($row = $ilance->db->fetch_array($sql)) {
				
			 $coin_id = $row['project_id'];  
			  
			 $filehash =  $row['filehash']; 
			 
			 $i++;
			// echo $i;echo '<br/>'; 
			 
			  $coin_id; 
			echo  $row['filedata'] = 'www.greatcollections.com/coins/'.  $coin_id;
			echo '<br/>'; 
			 
			 
		 }
		
		}
		
			 
    ?>

