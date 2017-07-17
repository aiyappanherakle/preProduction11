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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration',
	'search'
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
	'search'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');
error_reporting(0);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


if (!empty($_SESSION['ilancedata']['user']['userid']) AND (( $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1') OR  ($_SESSION['ilancedata']['user']['userid'] == '13662' AND $_SESSION['ilancedata']['user']['isstaff'] == '2') ))
{

	// #### ADD COIN SHOW  ####################################
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] =='coin_show')
	{

		$date=$ilance->GPC['show_date'];
		$desc=$ilance->GPC['show_desc'];

		$show_representatives = json_encode($ilance->GPC['show_representatives']); 

		$firsttop=$ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_show(coin_date,coin_desc,coin_end_date,coin_show_representatives) values('".$ilance->GPC['show_start_date']."','".$ilance->db->escape_string($ilance->GPC['show_desc'])."','".$ilance->GPC['show_end_date']."','".$show_representatives."')");
		print_action_success("Show Description Added Successfully", $ilpage['settings']);
		exit();
	}
	
	
	// #### DELETE COIN SHOW  ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'delete_coin_shows')
	{
		if(isset($ilance->GPC['id']))
		{
			$del_id=$ilance->GPC['id'];
			
			$ilance->db->query("Delete FROM " . DB_PREFIX . "coin_show WHERE id='".$del_id."'");
			print_action_success("Show Deleted Successfully", $ilpage['settings']);
			exit();
		}
	}


	// #### EDIT COIN SHOW  ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_coin_shows')
	{
	
		$show_update = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coin_show
									   where id='".$ilance->GPC['id']."'");
					
		$res_update= $ilance->db->fetch_array($show_update);
		
		$coin_date=$res_update['coin_date'];
		$coin_end_date=$res_update['coin_end_date'];
		$coin_desc=$res_update['coin_desc'];
		$id=$ilance->GPC['id'];
		
		$representatives = array();
		if(isset($res_update['coin_show_representatives']) AND $res_update['coin_show_representatives'] != '')
		{
			$representatives = json_decode($res_update['coin_show_representatives']);
		}
		  
		  $show['edit_coin_show']=true;

	}

	
	// #### UPDATE COIN SHOW  ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit_coin_show')
	{
		$update_representatives = json_encode($ilance->GPC['show_representatives']); 
		$ilance->db->query("UPDATE ".DB_PREFIX."coin_show 
					  SET coin_date ='".$ilance->GPC['show_start_date']."',
						  coin_desc ='".$ilance->GPC['show_desc']."',
						  coin_end_date ='".$ilance->GPC['show_end_date']."',
						  coin_show_representatives ='".$update_representatives."'
					 where id='".$ilance->GPC['id']."'");
					 
		print_action_success('Updated Successfully', $ilpage['staffsettings']);
		exit();
	}

	$ilconfig['maxrowsdisplay']=25;
	 
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	 
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['maxrowsdisplay'];
				
	$scriptpage = $ilpage['settings'].'?';
	
    $sql_shows=$ilance->db->query("SELECT coin_date,coin_end_date,id,coin_desc FROM " . DB_PREFIX . "coin_show
                               ORDER BY coin_date ASC 
							   LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['maxrowsdisplay']) . "," . $ilconfig['maxrowsdisplay']
							 );
							 
	$sql_shows_count=$ilance->db->query("SELECT coin_date,coin_end_date,id,coin_desc FROM " . DB_PREFIX . "coin_show
                                                     ORDER BY coin_date ASC 
							                       ");	
	$number = (int)$ilance->db->num_rows($sql_shows_count);										   		 

	$count=0;
		while($res_shows = $ilance->db->fetch_array($sql_shows))
		{
			$shows['id'] = $res_shows['id'];
			$shows['start_date'] = $res_shows['coin_date'];
			$shows['end_date'] = $res_shows['coin_end_date'];
			$shows['description'] = $res_shows['coin_desc'];

			$shows['edit'] = '<a href="' . $ilpage['settings'] . '?subcmd=update_coin_shows&amp;id=' . $res_shows['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';

			$shows['delete'] = '<a href="' . $ilpage['settings'] . '?subcmd=delete_coin_shows&amp;id=' . $res_shows['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';

			$coin_shows_list[]=$shows;
			$count++;
		
		}
		
	$coin_show_pagnation = print_pagnation_new($number, $ilconfig['maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);

		
	// For Bug #4813
	$staff_lists = array(1=>array('name'=>'Ian Russell', 'desaignation'=>'Owner/President', 'email'=>'ian@greatcollections.com
	'),2=>array('name'=>'Nina Ann Phan', 'desaignation'=>'Consignment Director', 'email'=>'nina@greatcollections.com'),3=>array('name'=>'Raeleen Endo', 'desaignation'=>'Owner/Client Services', 'email'=>'raeleen@greatcollections.com'), 4=>array('name'=>'Juliann Gim', 'desaignation'=>'Client Services', 'email'=>'juliann@greatcollections.com'));

	$show_representatives = '<select multiple name="show_representatives[]">';
	foreach($staff_lists as $id=>$staff)
	{
		if(isset($representatives) AND in_array($id, $representatives))
		{
			$show_representatives .= '<option value="'.$id.'" selected="selected">'.$staff['name'].'</option>';
		}
		else
		{
			$show_representatives .= '<option value="'.$id.'">'.$staff['name'].'</option>';
		}
	}
	$show_representatives .= '</select>';
		
	 $pprint_array = array('id','page','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','id','coin_show_pagnation','coin_date','coin_end_date','coin_desc','show_representatives','ilanceversion');
                

	
	$ilance->template->fetch('main', 'settings.html', 4);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('coin_shows_list'));

	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>