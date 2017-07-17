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

	   
	   
		$sql= $ilance->db->query("SELECT p.project_id,p.project_title,ca.filename, ca.filetype, ca.filesize, ca.filehash, ca.filedata, ca.filetype,
		(SELECT COUNT(attachid) AS picture_count FROM ilance_attachment WHERE project_id=p.project_id) as picture_count
		FROM ilance_projects p 
		left join ilance_attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto' 
		WHERE p.status = 'open' 
		and ca.attachid is Not Null
		group by p.project_id 
		ORDER BY p.project_id  ASC
		limit 0,1000
		");
		
		
		 
		if ($ilance->db->num_rows($sql) > 0) 
			{
			$i=0;
			while ($row = $ilance->db->fetch_array($sql)) {
				
			 $coin_id = $row['project_id'];  
			  
			 $filehash =  $row['filehash']; 
			 
			 $i++;
			 echo $i;echo '<br/>'; 
			 
			  $coin_id; 
			echo  $row['filedata'] = DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/' . $filehash . '.attach';
			echo '<br/>'; 
			 
			 
		 }
		
		}
		
			 
    ?>
