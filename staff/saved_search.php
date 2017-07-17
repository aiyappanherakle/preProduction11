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
        'administration'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['savedsearch'] => $ilcrumbs[$ilpage['savedsearch']]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['savedsearch']);


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{



	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search_save')
	{
	 $save_search_value = $ilance->GPC['save_search'];	
     $search = $ilance->GPC['filterby'];
	

	
	$saved_search1 = $ilance->db->query("
                        SELECT s.searchid,s.title,s.searchoptions,u.first_name,u.last_name,u.email,s.added
                        FROM " . DB_PREFIX . "search_favorites s,
                              " . DB_PREFIX . "users u
                        where 
                         u.user_id = s.user_id	
                       and $search  = '".$save_search_value."'
					  				 
                        ORDER BY s.added  DESC
						LIMIT 50
		");
	
	if ($ilance->db->num_rows($saved_search1) > 0)
		{
		
		
		
		while ($row1 = $ilance->db->fetch_array($saved_search1))
			{
			 $row1['searchid']= $row1['searchid'];
			 $row1['title']= $row1['title'];
			 $row1['searchoptions']= $row1['searchoptions'];
			 $row1['first_name']= $row1['first_name'];
			 $row1['last_name']= $row1['last_name'];
			 $row1['email']= $row1['email'];
			 $row1['added']= $row1['added'];
			 $row1['edit'] = '<a href="saved_search.php?subcmd=search_edit&amp;searchid=' . $row1['searchid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
			
            $row1['searchoptions1'] = wordwrap($row1['searchoptions'], 8, "\n", true);
              $row1['title1'] = wordwrap($row1['title'], 8, "\n", true);
			$saved_search_results1[] = $row1;
				$row_count++;
			}
		
		}
		else
		{
		
		$show['no_save_result']=true;
		
		}
	
	
	
$pprint_array = array('v3nav','buildversion','ilanceversion','edit','no_save_result','login_include_admin','guestsonline','membersonline','staffonline','robotsonline','global_connectionsettings','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                ($apihook = $ilance->api('admincp_connections_end')) ? eval($apihook) : false;
			
		$ilance->template->fetch('main', 'saved_searches.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('','','guest_connection_results','member_connection_results','admin_connection_results','saved_search_results1'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	
	
	}
  
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_edit')
	{
	

		$saved_search_edit = $ilance->db->query("
                        SELECT s.searchid,s.title,s.searchoptions,u.first_name,u.last_name,u.email,s.added
                        FROM " . DB_PREFIX . "search_favorites s,
                              " . DB_PREFIX . "users u
                        where 
                         u.user_id = s.user_id	
                        and s.searchid = '".$ilance->GPC['searchid']."'						 
                      
		");
		
		if ($ilance->db->num_rows($saved_search_edit) > 0)
		{
			$row2 = $ilance->db->fetch_array($saved_search_edit);
			
			$searchid = $row2['searchid'];			
			$title =$row2['title'];
			$searchoptions = $row2['searchoptions'];
			$first_name =$row2['first_name'];
			$last_name = $row2['last_name'];
			$email=$row2['email'];
			$added =$row2['added'];						
            $searchoptions1 =$row2['searchoptions1'] = wordwrap($row2['searchoptions'], 8, "\n", true);
            $title1 = $row2['title1'] = wordwrap($row2['title'], 8, "\n", true);
			
		}
		
   $pprint_array = array('v3nav','buildversion','searchid','title','searchoptions','first_name','last_name','email','added','searchoptions1','title1','ilanceversion','no_save_result','login_include_admin','guestsonline','membersonline','staffonline','robotsonline','global_connectionsettings','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                ($apihook = $ilance->api('admincp_connections_end')) ? eval($apihook) : false;
			
		$ilance->template->fetch('main', 'saved_search_edit.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('guest_connection_results','member_connection_results','admin_connection_results'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	
	
	}
      
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search_edit_update')
	{


		 $ilance->db->query("
                        UPDATE " . DB_PREFIX . "search_favorites s,
                              " . DB_PREFIX . "users u
                        SET s.title ='".$ilance->GPC['title1']."' ,
						s.searchoptions ='".$ilance->GPC['searchoptions1']."',u.first_name ='".$ilance->GPC['first_name']."' ,
						u.last_name = '".$ilance->GPC['last_name']."',u.email = '".$ilance->GPC['email']."',s.added = '".$ilance->GPC['added']."'
                        WHERE u.user_id = s.user_id	
                        and s.searchid = '".$ilance->GPC['searchid']."'	
                ");
		
		print_action_success('Updated Successfully', 'saved_search.php');
	    exit();
		

	}
      	  
else
{


	$saved_search = $ilance->db->query("
                        SELECT s.searchid,s.title,s.searchoptions,u.first_name,u.last_name,u.email,s.added
                        FROM " . DB_PREFIX . "search_favorites s,
                              " . DB_PREFIX . "users u
                        where 
                         u.user_id = s.user_id							  
                        ORDER BY s.added  DESC
						LIMIT 50
		");

	if ($ilance->db->num_rows($saved_search) > 0)
		{
			while ($row = $ilance->db->fetch_array($saved_search))
			{
			 $row['searchid']= $row['searchid'];
			 $row['title']= $row['title'];
			 $row['searchoptions']= $row['searchoptions'];
			 $row['first_name']= $row['first_name'];
			 $row['last_name']= $row['last_name'];
			 $row['email']= $row['email'];
			 $row['added']= $row['added'];
			 $row['edit'] = '<a href="saved_search.php?subcmd=search_edit&amp;searchid=' . $row['searchid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
			
            $row['searchoptions1'] = wordwrap($row['searchoptions'], 8, "\n", true);
              $row['title1'] = wordwrap($row['title'], 8, "\n", true);
			$saved_search_results[] = $row;
				$row_count++;
			}
			
			
			
		}


$pprint_array = array('v3nav','buildversion','ilanceversion','login_include_admin','guestsonline','membersonline','staffonline','robotsonline','global_connectionsettings','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                ($apihook = $ilance->api('admincp_connections_end')) ? eval($apihook) : false;
			
		$ilance->template->fetch('main', 'saved_search.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('guest_connection_results','member_connection_results','admin_connection_results','saved_search_results'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();


}
}




else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>