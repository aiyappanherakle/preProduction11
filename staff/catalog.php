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
error_reporting(E_ALL);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


	
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{      
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']== "_update-product-category-for-catalog")   
	{
	switch ($ilance->GPC['breakup'])
	{
	case "sort_order_catalog":	
		//update to coin table and project table
		$sql1 = $ilance->db->query("select PCGS from ".DB_PREFIX."catalog_coin order by Orderno");
				$i=100;
		if ($ilance->db->num_rows($sql1) > 0)
		{
		  while($res1=$ilance->db->fetch_array($sql1, DB_ASSOC))
			{
				$orderno=$i;
				 $query1="UPDATE ".DB_PREFIX."catalog_coin set Orderno= '".$orderno."' where PCGS='".$res1['PCGS']."'";
				 $result1=$ilance->db->query($query1);
				// echo $query.'<br>'.$query1.'<br>';
				$i=$i+100;
			}
		}
	break;
	case "sort_order_project":
			//update to coin table and project table
		$sql1 = $ilance->db->query("select PCGS,Orderno from ".DB_PREFIX."catalog_coin order by Orderno");
		if ($ilance->db->num_rows($sql1) > 0)
		{
		  while($res1=$ilance->db->fetch_array($sql1, DB_ASSOC))
			{
				$query= "UPDATE " . DB_PREFIX . "projects SET Orderno = '".$res1['Orderno']."' WHERE cid='".$res1['PCGS']."'";
				$result=$ilance->db->query($query, 0, null, __FILE__, __LINE__);
			}
		}
	break;
	case "category_update":
	$sql=$ilance->db->query("select * from " . DB_PREFIX . "catalog_coin");
	 if($ilance->db->num_rows($sql) > 0)
		{
		$row = 0;
		$ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
		while($line = $ilance->db->fetch_array($sql))
			{
			if(!empty($line['coin_detail_coin_series']))
			{
				$sql1=$ilance->db->query("select * from " . DB_PREFIX . "categories where cattype='product' and cid='".intval($line['PCGS'])."'");
				if($ilance->db->num_rows($sql1) == 0)
				{
				//insert query
				// murugan Changes on jan 30 hidden here 
				/*if(empty($line['coin_detail_suffix']))
				$cat_title=empty($line['coin_detail_mintmark'])?$line['coin_detail_year'].' '.$line['coin_detail_coin_series']:$line['coin_detail_year'].'-'.$line['coin_detail_mintmark'].' '.$line['coin_detail_coin_series'];
				else
				$cat_title=empty($line['coin_detail_mintmark'])?$line['coin_detail_year'].' '.$line['coin_detail_coin_series'].' '.$line['coin_detail_suffix']:$line['coin_detail_year'].'-'.$line['coin_detail_mintmark'].' '.$line['coin_detail_coin_series'].' '.$line['coin_detail_suffix'];
*/
				
				$cat_title=empty($line['coin_detail_mintmark'])?$line['coin_detail_year'].' '.$line['coin_detail_coin_series']:$line['coin_detail_year'].'-'.$line['coin_detail_mintmark'].' '.$line['coin_detail_coin_series'];
				$ilance->db->query("
									insert into " . DB_PREFIX . "categories(
									cid,
									title_eng,
									description_eng,
									xml,
									portfolio,
									newsletter,
									insertiongroup,
									finalvaluegroup,
									incrementgroup,
									cattype,
									keywords,
									useproxybid,
									usereserveprice,
									hidebuynow,
									lft,
									rgt
									)values(
									'".intval($line['PCGS'])."',
									'".$ilance->db->escape_string($line['coin_detail_year'].' '.$line['coin_detail_coin_series'])."',
									'".$ilance->db->escape_string($line['coin_detail_description_long'])."',
									1,
									1,
									1,
									'default',
									'default',
									'default',
									'product',
									'".$ilance->db->escape_string($line['coin_detail_meta_title'].'|'.$line['coin_detail_meta_description'])."',
									1,
									1,
									1,
									1,
									2
									)
									");
				}else
				{
				//update query
				$cat_title=empty($line['coin_detail_mintmark'])?$line['coin_detail_year'].' '.$line['coin_detail_coin_series']:$line['coin_detail_year'].'-'.$line['coin_detail_mintmark'].' '.$line['coin_detail_coin_series'];
					$ilance->db->query("update " . DB_PREFIX . "categories set
										title_eng='".$ilance->db->escape_string($line['coin_detail_year'].' '.$line['coin_detail_coin_series'])."',
										description_eng='".$ilance->db->escape_string($line['coin_detail_description_long'])."',
										keywords='".$ilance->db->escape_string($line['coin_detail_meta_title'].'|'.$line['coin_detail_meta_description'])."'
										where cid='".$line['PCGS']."'
										");
				}
			}
			}
			
		$ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
		
		// #### update the new level bit for the category tree structure
		$ilance->categories->set_levels();
		$ilance->categories->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories->rebuild_category_geometry();
		
	break;
	}
	 
		
		}
		print_action_success("Cion details and category tables are sucessfully Synchronized", "catalog.php");
		exit;
	}             
             if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'update-catalog') 
			 {           
			              //top level start
			             //delete toplevel
						  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletetoplevel') 
						 {
						  $sqlusercheck_delete = $ilance->db->query("
														
														DELETE FROM " . DB_PREFIX . "catalog_toplevel
				                                        WHERE id = '" . intval($ilance->GPC['id']) . "'
														
														");
													 print_action_success('successfully update your toplevel catalog', $ilpage['catalog']);
                                                     exit();
						 } 
						  //updated toplevel value save
						  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_toplist') 
						 {
						 
						                           if($_POST['id']=='')
								                    {
						  
						                             print_action_success('sorry', $ilpage['catalog']);
                                                     exit();
														
													}
													else
													{
													  $sqlusercheck_my = $ilance->db->query("
														UPDATE  " . DB_PREFIX . "catalog_toplevel
														SET  denomination_unique_no       = '" . $_POST['denomination_unique_no'] . "',
														     denomination_short 	      = '" . $_POST['denomination_short'] . "',
															 denomination_long            = '" . $_POST['denomination_long'] . "',
															 denomination_home            = '" . $_POST['denomination_home'] . "',
															 denomination_sort            = '" . $_POST['denomination_sort'] . "',
															 denomination_description     = '" . $_POST['denomination_description'] . "',
															 denomination_image           = '" . $_POST['denomination_image'] . "',
															 denomination_image_alt       = '" . $_POST['denomination_image_alt'] . "',
															 denomination_meta_title      = '" . $_POST['denomination_meta_title'] . "',
															 denomination_meta_description = '" . $_POST['denomination_meta_description'] . "'	
															 														 
													         WHERE id   = '" . $_POST['id'] . "'
														
														");
													 print_action_success('successfully update your toplevel catalog', $ilpage['catalog']);
                                                     exit();
													 
													}
						 }
			             //update list for toplevel catalog
						 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-toplevel') 
						 {
						         $show['update-toplevel'] = 'update-toplevel';
								 if(isset($ilance->GPC['id']))
								 {
								 
								  
								  //toplevel db column name fetch
									 $top_level_fieldname = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_toplevel");
									  $i = 0;
									  
									 while ($i < mysql_num_fields($top_level_fieldname)) {
							  
									 $meta = mysql_fetch_field($top_level_fieldname, $i);
									 $test[$i] = $meta->name;
									 $testmy[] = $test[$i];
									 $i++;
									 }
									 //update item id select
									 $top_level_update = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_toplevel
															WHERE id ='".$ilance->GPC['id']."'
															");
															
															$id = $ilance->GPC['id'];
															
															while($row_top_update = $ilance->db->fetch_array($top_level_update))
															{
																	for($d = 1; $d < count($testmy); $d++)
																	{
																	 if($testmy[$d] == 'denomination_description')
																	 {
																	
																	 $inp = '<textarea style="width: 300px; height: 100px;" name="'.$testmy[$d].'">'.$row_top_update[$testmy[$d]].'</textarea>';
																	 }
																	 else
																	 {
																	 $inp =  '<input type="text" name="'.$testmy[$d].'" value="'.$row_top_update[$testmy[$d]].'" style="width: 300px;">';
																	 }
																	 
																	$update_table .= '<tr>';
																	$update_table .= '<td width="10%"><span class="gray" style="text-transform:capitalize">'.$testmy[$d].'</span></td>';
																	$update_table .= '<td>'.$inp.'</td>';
																	$update_table .= '</tr>';
																	}
															}
															
								  
								 }
								 
						 }
						 
						 //second level start
						 //delete secondlevel
						  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletesecondlevel') 
						 {
						  $sqlusercheck_delete = $ilance->db->query("
														
														DELETE FROM " . DB_PREFIX . "catalog_second_level
				                                        WHERE id = '" . intval($ilance->GPC['id']) . "'
														
														");
													 print_action_success('successfully update your toplevel catalog', $ilpage['catalog']);
                                                     exit();
						 } 
						  //updated secondlevel value save
						  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_secondlist') 
						 {
						 
						                           if($_POST['id']=='')
								                    {
						  
						                             print_action_success('sorry', $ilpage['catalog']);
                                                     exit();
														
													}
													else
													{
						  $sqlusercheck_my = $ilance->db->query("
							UPDATE  " . DB_PREFIX . "catalog_second_level
							SET          coin_series_denomination_no 	      = '" . $ilance->db->escape_string($_POST['coin_series_denomination_no']) . "',
										 coin_series_name                     = '" . $ilance->db->escape_string($_POST['coin_series_name']) . "',
										 coin_series_date_from                = '" . $ilance->db->escape_string($_POST['coin_series_date_from']) . "',
										 coin_series_date_to                  = '" . $ilance->db->escape_string($_POST['coin_series_date_to']) . "',
										 coin_series_description_long         = '" . $ilance->db->escape_string($_POST['coin_series_description_long']) . "',
										 coin_series_description_short        = '" . $ilance->db->escape_string($_POST['coin_series_description_short']) . "',
										 coin_series_sort                     = '" . $ilance->db->escape_string($_POST['coin_series_sort']) . "',
										 coin_series_meta_title               = '" . $ilance->db->escape_string($_POST['coin_series_meta_title']) . "',	
										 coin_series_meta_description         = '" . $ilance->db->escape_string($_POST['coin_series_meta_description']) . "',
										 coin_series_image                    = '" . $ilance->db->escape_string($_POST['coin_series_image']) . "',
										 coin_series_image_alt                = '" . $ilance->db->escape_string($_POST['coin_series_image_alt']) . "',
										 coin_series_designer                 = '" . $ilance->db->escape_string($_POST['coin_series_designer']) . "',
										 coin_series_key                      = '" . $ilance->db->escape_string($_POST['coin_series_key']) . "',
										 coin_series_meta_keywords            = '" . $ilance->db->escape_string($_POST['coin_series_meta_keywords']) . "',
										 coin_series_notes                    = '" . $ilance->db->escape_string($_POST['coin_series_notes']) . "'		
																						 
								 WHERE id   = '" . $_POST['id'] . "'
							
							");
													 print_action_success('successfully update your toplevel catalog', $ilpage['catalog']);
                                                     exit();
													 
													}
						 }
			             //update list for secondlevel catalog
						 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-secondlevel') 
						 {
						          $show['update-secondlevel'] = 'update-secondlevel';
								 if(isset($ilance->GPC['id']))
								 {
								 
								  
								  //toplevel db column name fetch
									 $top_level_fieldname = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_second_level");
									  $i = 0;
									  
									 while ($i < mysql_num_fields($top_level_fieldname)) {
							  
									 $meta = mysql_fetch_field($top_level_fieldname, $i);
									 $test[$i] = $meta->name;
									 $testmy[] = $test[$i];
									 $i++;
									 }
									 //update item id select
									 $top_level_update = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_second_level
															WHERE id ='".$ilance->GPC['id']."'
															");
															
															$id = $ilance->GPC['id'];
															
															while($row_top_update = $ilance->db->fetch_array($top_level_update))
															{
																	for($d = 1; $d < count($testmy); $d++)
																	{
																	 if($testmy[$d] == 'coin_series_description_short')
																	 {
																	
																	 $inp = '<textarea style="width: 300px; height: 100px;" name="'.$testmy[$d].'">'.$row_top_update[$testmy[$d]].'</textarea>';
																	 }
																	 else
																	 {
																	 $inp =  '<input type="text" name="'.$testmy[$d].'" value="'.$row_top_update[$testmy[$d]].'" style="width: 300px;">';
																	 }
																	 
																	$update_second_table .= '<tr  class="alt1">';
																	$update_second_table .= '<td width="10%"><span class="gray" style="text-transform:capitalize">'.$testmy[$d].'</span></td>';
																	$update_second_table .= '<td>'.$inp.'</td>';
																	$update_second_table .= '</tr>';
																	}
															}
															
								  
								 }
								 
						 }
						
						
						//coin list level start
						//delete coinlist
						  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletecoinlist') 
						 {
						  $sqlusercheck_delete = $ilance->db->query("
														
														DELETE FROM " . DB_PREFIX . "catalog_coin
				                                        WHERE id = '" . intval($ilance->GPC['id']) . "'
														
														");
													 print_action_success('successfully update your toplevel catalog', $ilpage['catalog']);
                                                     exit();
						 } 
						  //updated coinlist value save
						  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_coinlist_save') 
						 {
						 
						                           if($_POST['id']=='')
								                    {
						  
						                             print_action_success('sorry', $ilpage['catalog']);
                                                     exit();
														
													}
													else
													{
						  $sqlusercheck_my = $ilance->db->query("
																UPDATE  " . DB_PREFIX . "catalog_coin
																SET Orderno = '".$ilance->db->escape_string($_POST['Orderno'])."',
																    PCGS = '".$ilance->db->escape_string($_POST['PCGS'])."',
																	coin_series_unique_no = '".$ilance->db->escape_string($_POST['coin_series_unique_no'])."',
																	coin_series_denomination_no = '".$ilance->db->escape_string($_POST['coin_series_denomination_no'])."',
																	coin_detail_year = '".$ilance->db->escape_string($_POST['coin_detail_year'])."',
																	coin_detail_mintmark = '".$ilance->db->escape_string($_POST['coin_detail_mintmark'])."',
																	coin_detail_coin_series = '".$ilance->db->escape_string($_POST['coin_detail_coin_series'])."',
																	coin_detail_denom_long = '".$ilance->db->escape_string($_POST['coin_detail_denom_long'])."',
																	coin_detail_denom_short = '".$ilance->db->escape_string($_POST['coin_detail_denom_short'])."',
																	coin_detail_proof = '".$ilance->db->escape_string($_POST['coin_detail_proof'])."',
																	coin_detail_suffix = '".$ilance->db->escape_string($_POST['coin_detail_suffix'])."',
																	coin_detail_major_variety = '".$ilance->db->escape_string($_POST['coin_detail_major_variety'])."',
																	coin_detail_die_variety = '".$ilance->db->escape_string($_POST['coin_detail_die_variety'])."',
																	coin_detail_key_date = '".$ilance->db->escape_string($_POST['coin_detail_key_date'])."',
																	coin_detail_mintage = '".$ilance->db->escape_string($_POST['coin_detail_mintage'])."',
																	coin_detail_low_mintage = '".$ilance->db->escape_string($_POST['coin_detail_low_mintage'])."',
																	coin_detail_weight = '".$ilance->db->escape_string($_POST['coin_detail_weight'])."',
																	coin_detail_composition = '".$ilance->db->escape_string($_POST['coin_detail_composition'])."',
																	coin_detail_diameter = '".$ilance->db->escape_string($_POST['coin_detail_diameter'])."',
																	coin_detail_designer = '".$ilance->db->escape_string($_POST['coin_detail_designer'])."',
																	coin_detail_description_long = '".$ilance->db->escape_string($_POST['coin_detail_description_long'])."',
																	coin_detail_description_short = '".$ilance->db->escape_string($_POST['coin_detail_description_short'])."',
																	coin_detail_notes = '".$ilance->db->escape_string($_POST['coin_detail_notes'])."',
																	coin_detail_ngc_no = '".$ilance->db->escape_string($_POST['coin_detail_ngc_no'])."',
																	coin_detail_ebay_heading = '".$ilance->db->escape_string($_POST['coin_detail_ebay_heading'])."',
																	coin_detail_ebay_category = '".$ilance->db->escape_string($_POST['coin_detail_ebay_category'])."',
																	coin_detail_related_coins = '".$ilance->db->escape_string($_POST['coin_detail_related_coins'])."',
																	coin_detail_meta_description = '".$ilance->db->escape_string($_POST['coin_detail_meta_description'])."',
																	coin_detail_meta_title = '".$ilance->db->escape_string($_POST['coin_detail_meta_title'])."',
																	coin_detail_image = '".$ilance->db->escape_string($_POST['coin_detail_image'])."',
																	coin_detail_image_alt = '".$ilance->db->escape_string($_POST['coin_detail_image_alt'])."',
																	coin_detail_sort = '".$ilance->db->escape_string($_POST['coin_detail_sort'])."',
																	coin_detail_coin_series_no = '".$ilance->db->escape_string($_POST['coin_detail_coin_series_no'])."'		
																						 
								                                    WHERE id   = '" . $_POST['id'] . "'
							
							");
													 print_action_success('successfully update your toplevel catalog', $ilpage['catalog']);
                                                     exit();
													 
													}
						 }
			             //update list for coinlist catalog
						 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-coinlist') 
						 {
						          $show['update-coinlist'] = 'update-coinlist';
								 if(isset($ilance->GPC['id']))
								 {
								 
								  
								  //toplevel db column name fetch
									 $top_level_fieldname = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_coin");
									  $i = 0;
									  
									 while ($i < mysql_num_fields($top_level_fieldname)) {
							  
									 $meta = mysql_fetch_field($top_level_fieldname, $i);
									 $test[$i] = $meta->name;
									 $testmy[] = $test[$i];
									 $i++;
									 }
									 //update item id select
									 $coin_level_update = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_coin
															WHERE id ='".$ilance->GPC['id']."'
															");
															
															$id = $ilance->GPC['id'];
															$update_coin_table ='';
															while($row_coin_update = $ilance->db->fetch_array($coin_level_update))
															{
																	for($d = 1; $d < count($testmy); $d++)
																	{
																	 if($testmy[$d] == 'coin_detail_description_long')
																	 {
																	
																	 $inp = '<textarea style="width: 300px; height: 100px;" name="'.$testmy[$d].'">'.$row_coin_update[$testmy[$d]].'</textarea>';
																	 }
																	 else
																	 {
																	 $inp =  '<input type="text" name="'.$testmy[$d].'" value="'.$row_coin_update[$testmy[$d]].'" style="width: 300px;">';
																	 }
																	 
																	$update_coin_table .= '<tr>';
																	$update_coin_table .= '<td width="10%"><span class="gray" style="text-transform:capitalize">'.$testmy[$d].'</span></td>';
																	$update_coin_table .= '<td>'.$inp.'</td>';
																	$update_coin_table .= '</tr>';
																	}
															}
															
								  
								 }
								 
						 }				
			 
			 $pprint_array = array('update_coin_table','update_second_table','id','update_table','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'catalog_update.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('toplevelcatergorysearch','toplevelcatergory'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
			 }
else
{

                    //top level search 
					if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
                    {
					//print_r($_POST);
					
					$show['topsearch'] = 'topsearch';
					
					$top_level_search = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "catalog_toplevel
										WHERE denomination_unique_no = '".$ilance->GPC['filtervalue']."'
										OR denomination_short        = '".$ilance->GPC['filtervalue']."'
										OR denomination_long         = '".$ilance->GPC['filtervalue']."'
										OR denomination_sort         = '".$ilance->GPC['filtervalue']."'
										OR denomination_description  = '".$ilance->GPC['filtervalue']."'
										OR denomination_image        = '".$ilance->GPC['filtervalue']."'
										OR denomination_image_alt    = '".$ilance->GPC['filtervalue']."'
										OR denomination_meta_title   = '".$ilance->GPC['filtervalue']."'
										OR denomination_meta_description = '".$ilance->GPC['filtervalue']."'
										");
										
										if($ilance->db->num_rows($top_level_search) > 0)
										{
										$row_count_search = 0;
										while($row_top_search = $ilance->db->fetch_array($top_level_search))
										{
										
										$row_top_search['denomination_short'] = $row_top_search['denomination_short'];
										$row_top_search['denomination_long'] = $row_top_search['denomination_long'];
										$row_top_search['denomination_description'] = substr($row_top_search['denomination_description'], 0, 50);
										$row_top_search['edit'] = '<a href="catalog.php?cmd=update-catalog&subcmd=update-toplevel&id='.$row_top_search['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';
										$row_top_search['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="catalog.php?cmd=update-catalog&subcmd=deletetoplevel&amp;id='.$row_top_search['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										$toplevelcatergorysearch[] = $row_top_search;
										$row_count_search++;
										}
						                }
										else
										{				
										$showsearch['no'] = 'no';
										}
					}  
                    //top level CSV file upload  Save
                    if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'toplevel_upload')
                    {
					                                                              
		$column_names = array('denomination_unique_no', 'denomination_short', 'denomination_long', 'denomination_home', 'denomination_sort', 'denomination_description', 'denomination_image', 'denomination_image_alt', 'denomination_meta_title', 'denomination_meta_description', 'traffic_count', 'auction_count');
				
				
				//denomination_unique_no	denomination_short	denomination_long	denomination_home	denomination_sort	denomination_description	denomination_image	denomination_image_alt	denomination_meta_title	denomination_meta_description	traffic_count	auction_count																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					

				
				if((!empty($_FILES['fileup'])) && ($_FILES['fileup']['error'] == 0))
				{						
					if($_FILES['fileup']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
					{						
						if($_FILES['fileup']['size'] > 10000000)
						{
							print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							exit();
						}
						else
						{
							 $handle = fopen($_FILES['fileup']['tmp_name'],'r');
							 $row_count = 0;	
																	
							 while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
							 { 	
							  if ($row_count == '0')
								 {
								 $new = $data;
								 }
								
							    if($new != $column_names)
								{
								 print_action_failed("We're sorry.  Please check your csv file column will correct.", $_SERVER['PHP_SELF'].'');
							    exit();
								}
								$row_count++;
								if ($row_count == '1') continue;
								
								else if(count($data) != count($column_names))
								{
								print_action_failed("We're sorry. CSV file is not correct. Number of columns in
 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							    exit();
								
								
								}
								//empty field check
								else if(($data[0] == '') || ($data[1] == '') || ($data[2] == '') ||  ($data[4] == ''))
								{
								print_action_failed("We're sorry. CSV file empty filed. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							    exit();
								}
								else
								{	
								//data value
								$temp_data['denomination_unique_no'] = $data[0];								
								$temp_data['denomination_short'] = $data[1];
								$temp_data['denomination_long'] = $data[2];	
								$temp_data['denomination_home'] = $data[3];
								$temp_data['denomination_sort'] = $data[4];								
								$temp_data['denomination_description'] = $data[5];
								$temp_data['denomination_image'] = $data[6];	
								$temp_data['denomination_image_alt'] = $data[7];								
								$temp_data['denomination_meta_title'] = $data[8];
								$temp_data['denomination_meta_description'] = $data[9];	
								$temp_data['traffic_count'] = $data[10];	
								$temp_data['auction_count'] = $data[11];	
								//Appendtolist or Overwrite check
								if($_POST['type_insert'] == 'appendtolist')
								{
										//data check already
										$top_type_insert = $ilance->db->query("
												SELECT * FROM " . DB_PREFIX . "catalog_toplevel  WHERE denomination_unique_no ='".$data[0]."'");
												
												if($ilance->db->num_rows($top_type_insert) > 0)
												{
												$already_update = $ilance->db->query("
																UPDATE  " . DB_PREFIX . "catalog_toplevel
																SET  denomination_short 	      = '" . $data[1] . "',
																	 denomination_long            = '" . $data[2] . "',
																	 denomination_home            = '" . $data[3] . "',
																	 denomination_sort            = '" . $data[4] . "',
																	 denomination_description     = '" . $data[5] . "',
																	 denomination_image           = '" . $data[6] . "',
																	 denomination_image_alt       = '" . $data[7] . "',
																	 denomination_meta_title      = '" . $data[8] . "',
																	 denomination_meta_description = '" . $data[9] . "'	
																															 
																	 WHERE denomination_unique_no   = '" . $data[0] . "'
																
																");
												}
												else
												{
												$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "catalog_toplevel
												(id,denomination_unique_no,denomination_short,denomination_long,denomination_home,denomination_sort,denomination_description,denomination_image,denomination_image_alt,
												denomination_meta_title,denomination_meta_description,traffic_count,auction_count)
												VALUES
												(
												NULL,
												'".$temp_data['denomination_unique_no']."',
												'".$temp_data['denomination_short']."',
												'".$temp_data['denomination_long']."',
												'".$temp_data['denomination_home']."',												
												'".$temp_data['denomination_sort']."',
												'".$temp_data['denomination_description']."',
												'".$temp_data['denomination_image']."',
												'".$temp_data['denomination_image_alt']."',
												'".$temp_data['denomination_meta_title']."',
												'".$temp_data['denomination_meta_description']."',
												'0',
												'0'
												)
												
												");	
												}
								
								}
								else
								{          //overwrite value update
											$top_type_insert = $ilance->db->query("
													SELECT * FROM " . DB_PREFIX . "catalog_toplevel  WHERE denomination_unique_no ='".$data[0]."'");
													
													if($ilance->db->num_rows($top_type_insert) > 0)
													{
													$already_update = $ilance->db->query("
																	UPDATE  " . DB_PREFIX . "catalog_toplevel
																	SET  denomination_short 	      = '" . $data[1] . "',
																		 denomination_long            = '" . $data[2] . "',
																		 denomination_home            = '" . $data[3] . "',
																		 denomination_sort            = '" . $data[4] . "',
																		 denomination_description     = '" . $data[5] . "',
																		 denomination_image           = '" . $data[6] . "',
																		 denomination_image_alt       = '" . $data[7] . "',
																		 denomination_meta_title      = '" . $data[8] . "',
																		 denomination_meta_description = '" . $data[9] . "'	
																																 
																		 WHERE denomination_unique_no   = '" . $data[0] . "'
																	
																	");
													}
													else
													{
													$ilance->db->query("
													INSERT INTO " . DB_PREFIX . "catalog_toplevel
													(id,denomination_unique_no,denomination_short,denomination_long,denomination_home,denomination_sort,denomination_description,denomination_image,denomination_image_alt,
													denomination_meta_title,denomination_meta_description,traffic_count,auction_count)
													VALUES
													(
													NULL,
													'".$temp_data['denomination_unique_no']."',
													'".$temp_data['denomination_short']."',
													'".$temp_data['denomination_long']."',
													'".$temp_data['denomination_home']."',
													'".$temp_data['denomination_sort']."',
													'".$temp_data['denomination_description']."',
													'".$temp_data['denomination_image']."',
													'".$temp_data['denomination_image_alt']."',
													'".$temp_data['denomination_meta_title']."',
													'".$temp_data['denomination_meta_description']."',
													'0',
												    '0'
													)
													
													");	
										            }
								}
								 
									
																		 						
								}
								
							//end								
							 }
							 
						//else end	 
						}							
					}
					
					else
					{
						print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
						exit();
					}	
					fclose($handle);
					print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF'].'');
								exit();									
				}			
				else 
				{
				   
					print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
					exit();							
				}
			
			
				
	
					}
					//top level catalog select
					$top_level = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "catalog_toplevel");
										if($ilance->db->num_rows($top_level) > 0)
										{
										$row_count = 0;
										while($row_top = $ilance->db->fetch_array($top_level))
										{
										$row_top['denomination_short'] = $row_top['denomination_short'];
										$row_top['denomination_long'] = $row_top['denomination_long'];
										$row_top['denomination_description'] = substr($row_top['denomination_description'], 0, 50);
										$row_top['edit'] = '<a href="catalog.php?cmd=update-catalog&subcmd=update-toplevel&id='.$row_top['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';
										$row_top['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="catalog.php?cmd=update-catalog&subcmd=deletetoplevel&amp;id='.$row_top['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										$toplevelcatergory[] = $row_top;
										$row_count++;
										}
						                }
										else
										{				
										$show['no'] = 'no';
										}
								
					//second level search 
				    if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search_second')
                    {
					//print_r($_POST);
					
					$show['secondsearch'] = 'secondsearch';
					
					$top_level_search = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "catalog_second_level
										WHERE coin_series_unique_no = '".$ilance->GPC['filtervalue']."'
										OR coin_series_denomination_no        = '".$ilance->GPC['filtervalue']."'
										OR coin_series_name         = '".$ilance->GPC['filtervalue']."'
										OR coin_series_date_from         = '".$ilance->GPC['filtervalue']."'
										OR coin_series_date_to  = '".$ilance->GPC['filtervalue']."'
										OR coin_series_description_long        = '".$ilance->GPC['filtervalue']."'
										OR coin_series_description_short    = '".$ilance->GPC['filtervalue']."'
										OR coin_series_sort   = '".$ilance->GPC['filtervalue']."'
										OR coin_series_meta_title = '".$ilance->GPC['filtervalue']."'
										OR coin_series_meta_description = '".$ilance->GPC['filtervalue']."'
										OR coin_series_image = '".$ilance->GPC['filtervalue']."'
										OR coin_series_image_alt = '".$ilance->GPC['filtervalue']."'
										OR coin_series_designer = '".$ilance->GPC['filtervalue']."'
										OR coin_series_key = '".$ilance->GPC['filtervalue']."'
										OR coin_series_notes = '".$ilance->GPC['filtervalue']."'
										");
										
										if($ilance->db->num_rows($top_level_search) > 0)
										{
										$row_count_search = 0;
										while($row_top_search = $ilance->db->fetch_array($top_level_search))
										{
										
										$row_top_search['coin_series_description_short'] = substr($row_top_search['coin_series_description_short'], 0, 50);
										$row_top_search['coin_series_name'] = substr($row_top_search['coin_series_name'], 0, 50);
										$row_top_search['coin_series_date_from'] = $row_top_search['coin_series_date_from'];
										$row_top_search['coin_series_date_to'] = $row_top_search['coin_series_date_to'];
										$row_top_search['edit'] = '<a href="catalog.php?cmd=update-catalog&subcmd=update-secondlevel&id='.$row_top_search['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';
										$row_top_search['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="catalog.php?cmd=update-catalog&subcmd=deletesecondlevel&amp;id='.$row_top_search['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										$secondlevelcatergorysearch[] = $row_top_search;
										$row_count_search++;
										}
						                }
										else
										{				
										$showsearch['no'] = 'secyes';
										}
					} 			
				    //	second level CSV file upload  Save					
					if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'secondlevel_upload')
                    {
					
					$column_names = array('coin_series_unique_no', 'coin_series_denomination_no', 'coin_series_name', 'coin_series_date_from', 'coin_series_date_to', 'coin_series_description_long', 'coin_series_description_short', 'coin_series_sort', 'coin_series_meta_title', 'coin_series_meta_description', 'coin_series_meta_keywords', 'coin_series_image', 'coin_series_image_alt', 'coin_series_designer', 'coin_series_key', 'coin_series_notes', 'traffic_count', 'auction_count');
				
					//coin_series_unique_no	coin_series_denomination_no	coin_series_name	coin_series_date_from	coin_series_date_to	coin_series_description_long	coin_series_description_short	coin_series_sort	coin_series_meta_title	coin_series_meta_description	coin_series_meta_keywords	coin_series_image	coin_series_image_alt	coin_series_designer	coin_series_key	coin_series_notes	traffic_count	auction_count																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																															
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																													

				if((!empty($_FILES['fileup'])) && ($_FILES['fileup']['error'] == 0))
				{						
					if($_FILES['fileup']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
					{						
						if($_FILES['fileup']['size'] > 10000000)
						{
							print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							exit();
						}
						else
						{
							$handle = fopen($_FILES['fileup']['tmp_name'],'r');
							$row_count = 0;	
																	
							while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
			
							 { 	
							 
							 
							 	 if ($row_count == '0')
								 {
								 $new = $data;
								 }
							 
							    if($new != $column_names)
								{
								 print_action_failed("We're sorry.  Please check your csv file column will correct.", $_SERVER['PHP_SELF'].'');
							    exit();
								}				
							 	$row_count++;
								if ($row_count==1) continue;
							 	if(count($data) != count($column_names))
								{
								print_action_failed("We're sorry. CSV file is not correct. Number of columns in
 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							    exit();
								}
								
								//empty field check
								else if(($data[0] == '') || ($data[1] == ''))
								{
								print_action_failed("We're sorry. CSV file empty filed. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							    exit();
								}
								else
								{	
								//coin_series_unique_no	coin_series_denomination_no	coin_series_name	coin_series_date_from	coin_series_date_to	coin_series_description_long	coin_series_description_short	coin_series_sort	coin_series_meta_title	coin_series_meta_description	coin_series_meta_keywords	coin_series_image	coin_series_image_alt	coin_series_designer	coin_series_key	coin_series_notes	traffic_count	auction_count	
								
								//coin_series_unique_no	coin_series_denomination_no	coin_series_sort	coin_series_name	coin_series_date_from	coin_series_date_to	coin_series_description_long	coin_series_description_short	coin_series_meta_title	coin_series_meta_description	coin_series_meta_keywords	coin_series_image	coin_series_image_alt	coin_series_key	coin_series_designer	coin_series_notes	
								//data value
								$temp_data['coin_series_unique_no'] = $data[0];								
								$temp_data['coin_series_denomination_no'] = $data[1];
								$temp_data['coin_series_name'] = $data[2];	
								$temp_data['coin_series_date_from'] = $data[3];								
								$temp_data['coin_series_date_to'] = $data[4];
								$temp_data['coin_series_description_long'] = $data[5];	
								$temp_data['coin_series_description_short'] = $data[6];	
								$temp_data['coin_series_sort'] = $data[7];							
								$temp_data['coin_series_meta_title'] = $data[8];	
								$temp_data['coin_series_meta_description'] = $data[9];
								$temp_data['coin_series_meta_keywords'] = $data[10];
								$temp_data['coin_series_image'] = $data[11];	
								$temp_data['coin_series_image_alt'] = $data[12];
								$temp_data['coin_series_designer'] = $data[13];	
								$temp_data['coin_series_key'] = $data[14];		
								$temp_data['coin_series_notes'] = $data[15];	
								$temp_data['traffic_count'] = $data[16];
								$temp_data['auction_count'] = $data[17];
								
								//Appendtolist or Overwrite check
								if($_POST['type_insert'] == 'appendtolist')
								{
										//data check already
										$top_type_insert = $ilance->db->query("
												SELECT * FROM " . DB_PREFIX . "catalog_second_level  WHERE coin_series_unique_no ='".$data[0]."'");
												
												if($ilance->db->num_rows($top_type_insert) > 0)
												{
												$already_update = $ilance->db->query("
																UPDATE  " . DB_PREFIX . "catalog_second_level
																SET  coin_series_denomination_no 	      = '" . $ilance->db->escape_string($data[1]) . "',
																     coin_series_name                     = '" . $ilance->db->escape_string($data[2]) . "',
																	 coin_series_date_from                = '" . $ilance->db->escape_string($data[3]) . "',
																	 coin_series_date_to                  = '" . $ilance->db->escape_string($data[4]) . "',
																	 coin_series_description_long         = '" . $ilance->db->escape_string($data[5]) . "',
																	 coin_series_description_short        = '" . $ilance->db->escape_string($data[6]) . "',
																	 coin_series_sort                     = '" . $ilance->db->escape_string($data[7]) . "',
																	 coin_series_meta_title               = '" . $ilance->db->escape_string($data[8]) . "',	
																	 coin_series_meta_description         = '" . $ilance->db->escape_string($data[9]) . "',
																	 coin_series_meta_keywords            = '" . $ilance->db->escape_string($data[10]) . "',
																	 coin_series_image                    = '" . $ilance->db->escape_string($data[11]) . "',
																	 coin_series_image_alt                = '" . $ilance->db->escape_string($data[12]) . "',
																	 coin_series_designer                 = '" . $ilance->db->escape_string($data[13]) . "',
																	 coin_series_key                      = '" . $ilance->db->escape_string($data[14]) . "',                                                                     coin_series_notes                    = '" . $ilance->db->escape_string($data[15]) . "'
																	 
																															 
																	 WHERE coin_series_unique_no   = '" . $data[0] . "'
																
																");
												}
												else
												{
												
												$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "catalog_second_level
												(id,coin_series_unique_no,coin_series_denomination_no,coin_series_name,coin_series_date_from,coin_series_date_to,coin_series_description_long,coin_series_description_short,coin_series_sort,coin_series_meta_title,coin_series_meta_description,coin_series_meta_keywords,coin_series_image,coin_series_image_alt,coin_series_designer,coin_series_key,coin_series_notes,'traffic_count','auction_count')
												VALUES
												(
												NULL,
												'".$ilance->db->escape_string($temp_data['coin_series_unique_no'])."',								
												'".$ilance->db->escape_string($temp_data['coin_series_denomination_no'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_name'])."',	
												'".$ilance->db->escape_string($temp_data['coin_series_date_from'])."',							
												'".$ilance->db->escape_string($temp_data['coin_series_date_to'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_description_long'])."',	
												'".$ilance->db->escape_string($temp_data['coin_series_description_short'])."',								
												'".$ilance->db->escape_string($temp_data['coin_series_sort'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_meta_title'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_meta_description'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_meta_keywords'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_image'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_image_alt'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_designer'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_key'])."',	
												'".$ilance->db->escape_string($temp_data['coin_series_notes'])."',
												'0',
												'0'
												)
												
												");	
												}
								
								}
								else
								{          //overwrite value update
											$top_type_insert = $ilance->db->query("
													SELECT * FROM " . DB_PREFIX . "catalog_second_level  WHERE coin_series_unique_no ='".$data[0]."'");
													
													if($ilance->db->num_rows($top_type_insert) > 0)
													{
													$already_update = $ilance->db->query("
																UPDATE  " . DB_PREFIX . "catalog_second_level
																SET  coin_series_denomination_no 	      = '" . $ilance->db->escape_string($data[1]) . "',
																     coin_series_name                     = '" . $ilance->db->escape_string($data[2]) . "',
																	 coin_series_date_from                = '" . $ilance->db->escape_string($data[3]) . "',
																	 coin_series_date_to                  = '" . $ilance->db->escape_string($data[4]) . "',
																	 coin_series_description_long         = '" . $ilance->db->escape_string($data[5]) . "',
																	 coin_series_description_short        = '" . $ilance->db->escape_string($data[6]) . "',
																	 coin_series_sort                     = '" . $ilance->db->escape_string($data[7]) . "',
																	 coin_series_meta_title               = '" . $ilance->db->escape_string($data[8]) . "',	
																	 coin_series_meta_description         = '" . $ilance->db->escape_string($data[9]) . "',
																	 coin_series_meta_keywords            = '" . $ilance->db->escape_string($data[10]) . "',
																	 coin_series_image                    = '" . $ilance->db->escape_string($data[11]) . "',
																	 coin_series_image_alt                = '" . $ilance->db->escape_string($data[12]) . "',
																	 coin_series_designer                 = '" . $ilance->db->escape_string($data[13]) . "',
																	 coin_series_key                      = '" . $ilance->db->escape_string($data[14]) . "',                                                                     coin_series_notes                    = '" . $ilance->db->escape_string($data[15]) . "'
																	 
																															 
																	 WHERE coin_series_unique_no   = '" . $data[0] . "'
																
																");
													}
													else
													{
													$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "catalog_second_level
												(id,coin_series_unique_no,coin_series_denomination_no,coin_series_name,coin_series_date_from,coin_series_date_to,coin_series_description_long,coin_series_description_short,coin_series_sort,coin_series_meta_title,coin_series_meta_description,coin_series_meta_keywords,coin_series_image,coin_series_image_alt,coin_series_designer,coin_series_key,coin_series_notes,'traffic_count','auction_count'))
												VALUES
												(
												NULL,
												'".$ilance->db->escape_string($temp_data['coin_series_unique_no'])."',								
												'".$ilance->db->escape_string($temp_data['coin_series_denomination_no'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_name'])."',	
												'".$ilance->db->escape_string($temp_data['coin_series_date_from'])."',							
												'".$ilance->db->escape_string($temp_data['coin_series_date_to'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_description_long'])."',	
												'".$ilance->db->escape_string($temp_data['coin_series_description_short'])."',								
												'".$ilance->db->escape_string($temp_data['coin_series_sort'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_meta_title'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_meta_description'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_meta_keywords'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_image'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_image_alt'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_designer'])."',
												'".$ilance->db->escape_string($temp_data['coin_series_key'])."',	
												'".$ilance->db->escape_string($temp_data['coin_series_notes'])."',
												'0',
												'0'
												)
												
												");	
										            }
								}
								 
									
																		 						
								}							
							 }
						}							
					}
					
					else
					{
						print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
						exit();
					}	
					fclose($handle);
					print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF'].'');
								exit();									
				}			
				else 
				{
				   
					print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
					exit();							
				}
			
			
				
	
					}					
										
                    //second level catalog select
					/*$orderby = '';
					 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
					 $realdisplayorder = '&amp;displayorder=desc';
                        $scriptpage = $ilpage['catalog'] .  $displayorder . $orderby;
                        $scriptpageprevnext = $ilpage['catalog'] .  $realdisplayorder . $orderby;
						*/
						 
					$second_level = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "catalog_second_level");
										if($ilance->db->num_rows($second_level) > 0)
										{
										$row_count_sec = 0;
										while($row_second = $ilance->db->fetch_array($second_level))
										{
										$row_second['coin_series_description_short'] = substr($row_second['coin_series_description_short'], 0, 50);
										$row_second['coin_series_name'] = substr($row_second['coin_series_name'], 0, 50);
										$row_second['coin_series_date_from'] = $row_second['coin_series_date_from'];
										$row_second['coin_series_date_to'] = $row_second['coin_series_date_to'];
										$row_second['edit'] = '<a href="catalog.php?cmd=update-catalog&subcmd=update-secondlevel&id='.$row_second['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';
										$row_second['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="catalog.php?cmd=update-catalog&subcmd=deletesecondlevel&amp;id='.$row_second['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										$secondlevelcatergory[] = $row_second;
										$row_count_sec++;
										// $prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
										}
										
						                }
										
										else
										{				
										$show['no'] = 'second';
										}
					
					//Coin List CSV file upload  Save					
					if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'coin_upload')
                    {
					 $top_level_fieldname = $ilance->db->query("
															SELECT * FROM " . DB_PREFIX . "catalog_coin");
									  $i = 1;
									  
									 while ($i < mysql_num_fields($top_level_fieldname)) {
							  
									 $meta = mysql_fetch_field($top_level_fieldname, $i);
									 $test[$i] = $meta->name;
									 $testmy[] = $test[$i];
									 $i++;
									 }
					 $column_names = $testmy;
					 
					 if((!empty($_FILES['fileup'])) && ($_FILES['fileup']['error'] == 0))
				{						
					if($_FILES['fileup']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
					{						
						if($_FILES['fileup']['size'] > 10000000)
						{
							print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							exit();
						}
						else
						{
							$handle = fopen($_FILES['fileup']['tmp_name'],'r');
							$row_count = 0;	
																	
							while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
			
							 {
							    if ($row_count == '0')
								 {
								 $new = $data;
								 }
							 
							    if($new != $column_names)
								{
								 print_action_failed("We're sorry.  Please check your csv file column will correct.", $_SERVER['PHP_SELF'].'');
							    exit();
								}	 							
							 	$row_count++;
								if ($row_count==1) continue;
							 	if(count($data) != count($column_names))
								{
								print_action_failed("We're sorry. CSV file is not correct. Number of columns in
 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							exit();
								}
								//empty field check
//								else if(($data[0] == '') || ($data[1] == '') || ($data[2] == '') || ($data[3] == '') || ($data[4] == ''))
								else if(($data[1] == ''))
								{
							
								print_action_failed("We're sorry. CSV file empty filed. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
							    exit();
								}
								else
								{	
								
								//Appendtolist or Overwrite check
								if($_POST['type_insert'] == 'appendtolist')
								{
										//data check already
										$top_type_insert = $ilance->db->query("
												SELECT * FROM " . DB_PREFIX . "catalog_coin  WHERE Orderno ='".$data[0]."'");
												
												if($ilance->db->num_rows($top_type_insert) > 0)
												{
												$already_update = $ilance->db->query("
																UPDATE  " . DB_PREFIX . "catalog_coin
																SET PCGS = '".$ilance->db->escape_string($data[1])."',
																	coin_series_unique_no = '".$ilance->db->escape_string($data[2])."',
																	coin_series_denomination_no = '".$ilance->db->escape_string($data[3])."',
																	coin_detail_year = '".$ilance->db->escape_string($data[4])."',
																	coin_detail_mintmark = '".$ilance->db->escape_string($data[5])."',
																	coin_detail_coin_series = '".$ilance->db->escape_string($data[6])."',
																	coin_detail_denom_long = '".$ilance->db->escape_string($data[7])."',
																	coin_detail_denom_short = '".$ilance->db->escape_string($data[8])."',
																	coin_detail_proof = '".$ilance->db->escape_string($data[9])."',
																	coin_detail_suffix = '".$ilance->db->escape_string($data[10])."',
																	coin_detail_major_variety = '".$ilance->db->escape_string($data[11])."',
																	coin_detail_die_variety = '".$ilance->db->escape_string($data[12])."',
																	coin_detail_key_date = '".$ilance->db->escape_string($data[13])."',
																	coin_detail_mintage = '".$ilance->db->escape_string($data[14])."',
																	coin_detail_low_mintage = '".$ilance->db->escape_string($data[15])."',
																	coin_detail_weight = '".$ilance->db->escape_string($data[16])."',
																	coin_detail_composition = '".$ilance->db->escape_string($data[17])."',
																	coin_detail_diameter = '".$ilance->db->escape_string($data[18])."',
																	coin_detail_designer = '".$ilance->db->escape_string($data[19])."',
																	coin_detail_description_long = '".$ilance->db->escape_string($data[20])."',
																	coin_detail_description_short = '".$ilance->db->escape_string($data[21])."',
																	coin_detail_notes = '".$ilance->db->escape_string($data[22])."',
																	coin_detail_ngc_no = '".$ilance->db->escape_string($data[23])."',
																	coin_detail_ebay_heading = '".$ilance->db->escape_string($data[24])."',
																	coin_detail_ebay_category = '".$ilance->db->escape_string($data[25])."',
																	coin_detail_related_coins = '".$ilance->db->escape_string($data[26])."',
																	coin_detail_meta_description = '".$ilance->db->escape_string($data[27])."',
																	coin_detail_meta_title = '".$ilance->db->escape_string($data[28])."',
																	coin_detail_image = '".$ilance->db->escape_string($data[29])."',
																	coin_detail_image_alt = '".$ilance->db->escape_string($data[30])."',
																	coin_detail_sort = '".$ilance->db->escape_string($data[31])."',
																	coin_detail_coin_series_no = '".$ilance->db->escape_string($data[32])."',	
																	nmcode	= '".$ilance->db->escape_string($data[33])."'														 
																	 WHERE Orderno   = '" . $ilance->db->escape_string($data[0]) . "'
																
																");
												}
												else
												{
											  
												$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "catalog_coin
												(id, Orderno, PCGS, coin_series_unique_no, coin_series_denomination_no, coin_detail_year, coin_detail_mintmark, coin_detail_coin_series, coin_detail_denom_long, coin_detail_denom_short, coin_detail_proof, coin_detail_suffix, coin_detail_major_variety, coin_detail_die_variety, coin_detail_key_date,coin_detail_mintage, coin_detail_low_mintage, coin_detail_weight, coin_detail_composition, coin_detail_diameter, coin_detail_designer, coin_detail_description_long, coin_detail_description_short, coin_detail_notes, coin_detail_ngc_no, coin_detail_ebay_heading, coin_detail_ebay_category, coin_detail_related_coins, coin_detail_meta_description, coin_detail_meta_title, coin_detail_image, coin_detail_image_alt, coin_detail_sort, coin_detail_coin_series_no,nmcode)
												VALUES
												(
												NULL,
												'".$ilance->db->escape_string($data[0])."',
												'".$ilance->db->escape_string($data[1])."',
												'".$ilance->db->escape_string($data[2])."',
												'".$ilance->db->escape_string($data[3])."',
												'".$ilance->db->escape_string($data[4])."',
												'".$ilance->db->escape_string($data[5])."',
												'".$ilance->db->escape_string($data[6])."',
												'".$ilance->db->escape_string($data[7])."',
												'".$ilance->db->escape_string($data[8])."',
												'".$ilance->db->escape_string($data[9])."',
												'".$ilance->db->escape_string($data[10])."',
												'".$ilance->db->escape_string($data[11])."',
												'".$ilance->db->escape_string($data[12])."',
												'".$ilance->db->escape_string($data[13])."',
												'".$ilance->db->escape_string($data[14])."',
												'".$ilance->db->escape_string($data[15])."',
												'".$ilance->db->escape_string($data[16])."',
												'".$ilance->db->escape_string($data[17])."',
												'".$ilance->db->escape_string($data[18])."',
												'".$ilance->db->escape_string($data[19])."',
												'".$ilance->db->escape_string($data[20])."',
												'".$ilance->db->escape_string($data[21])."',
												'".$ilance->db->escape_string($data[22])."',
												'".$ilance->db->escape_string($data[23])."',
												'".$ilance->db->escape_string($data[24])."',
												'".$ilance->db->escape_string($data[25])."',
												'".$ilance->db->escape_string($data[26])."',
												'".$ilance->db->escape_string($data[27])."',
												'".$ilance->db->escape_string($data[28])."',
												'".$ilance->db->escape_string($data[29])."',
												'".$ilance->db->escape_string($data[30])."',
												'".$ilance->db->escape_string($data[31])."',
												'".$ilance->db->escape_string($data[32])."',
												'".$ilance->db->escape_string($data[33])."'
												
												)
												
												");	
												}
								
								}
								else
								{          //overwrite value update
											$top_type_insert = $ilance->db->query("
													SELECT * FROM " . DB_PREFIX . "catalog_coin  WHERE Orderno ='".$data[0]."'");
													
													if($ilance->db->num_rows($top_type_insert) > 0)
													{
													$already_update = $ilance->db->query("
																UPDATE  " . DB_PREFIX . "catalog_coin
																SET PCGS = '".$ilance->db->escape_string($data[1])."',
																	coin_series_unique_no = '".$ilance->db->escape_string($data[2])."',
																	coin_series_denomination_no = '".$ilance->db->escape_string($data[3])."',
																	coin_detail_year = '".$ilance->db->escape_string($data[4])."',
																	coin_detail_mintmark = '".$ilance->db->escape_string($data[5])."',
																	coin_detail_coin_series = '".$ilance->db->escape_string($data[6])."',
																	coin_detail_denom_long = '".$ilance->db->escape_string($data[7])."',
																	coin_detail_denom_short = '".$ilance->db->escape_string($data[8])."',
																	coin_detail_proof = '".$ilance->db->escape_string($data[9])."',
																	coin_detail_suffix = '".$ilance->db->escape_string($data[10])."',
																	coin_detail_major_variety = '".$ilance->db->escape_string($data[11])."',
																	coin_detail_die_variety = '".$ilance->db->escape_string($data[12])."',
																	coin_detail_key_date = '".$ilance->db->escape_string($data[13])."',
																	coin_detail_mintage = '".$ilance->db->escape_string($data[14])."',
																	coin_detail_low_mintage = '".$ilance->db->escape_string($data[15])."',
																	coin_detail_weight = '".$ilance->db->escape_string($data[16])."',
																	coin_detail_composition = '".$ilance->db->escape_string($data[17])."',
																	coin_detail_diameter = '".$ilance->db->escape_string($data[18])."',
																	coin_detail_designer = '".$ilance->db->escape_string($data[19])."',
																	coin_detail_description_long = '".$ilance->db->escape_string($data[20])."',
																	coin_detail_description_short = '".$ilance->db->escape_string($data[21])."',
																	coin_detail_notes = '".$ilance->db->escape_string($data[22])."',
																	coin_detail_ngc_no = '".$ilance->db->escape_string($data[23])."',
																	coin_detail_ebay_heading = '".$ilance->db->escape_string($data[24])."',
																	coin_detail_ebay_category = '".$ilance->db->escape_string($data[25])."',
																	coin_detail_related_coins = '".$ilance->db->escape_string($data[26])."',
																	coin_detail_meta_description = '".$ilance->db->escape_string($data[27])."',
																	coin_detail_meta_title = '".$ilance->db->escape_string($data[28])."',
																	coin_detail_image = '".$ilance->db->escape_string($data[29])."',
																	coin_detail_image_alt = '".$ilance->db->escape_string($data[30])."',
																	coin_detail_sort = '".$ilance->db->escape_string($data[31])."',
																	coin_detail_coin_series_no = '".$ilance->db->escape_string($data[32])."',
																	nmcode	= '".$ilance->db->escape_string($data[33])."'
																															 
																	 WHERE Orderno   = '" . $ilance->db->escape_string($data[0]) . "'
																
																");
													}
													else
													{
													
											  $ilance->db->query("
												INSERT INTO " . DB_PREFIX . "catalog_coin
												(id, Orderno, PCGS, coin_series_unique_no, coin_series_denomination_no, coin_detail_year, coin_detail_mintmark, coin_detail_coin_series, coin_detail_denom_long, coin_detail_denom_short, coin_detail_proof, coin_detail_suffix, coin_detail_major_variety, coin_detail_die_variety, coin_detail_key_date,coin_detail_mintage, coin_detail_low_mintage, coin_detail_weight, coin_detail_composition, coin_detail_diameter, coin_detail_designer, coin_detail_description_long, coin_detail_description_short, coin_detail_notes, coin_detail_ngc_no, coin_detail_ebay_heading, coin_detail_ebay_category, coin_detail_related_coins, coin_detail_meta_description, coin_detail_meta_title, coin_detail_image, coin_detail_image_alt, coin_detail_sort, coin_detail_coin_series_no,nmcode)
												VALUES
												(
												NULL,
												'".$ilance->db->escape_string($data[0])."',
												'".$ilance->db->escape_string($data[1])."',
												'".$ilance->db->escape_string($data[2])."',
												'".$ilance->db->escape_string($data[3])."',
												'".$ilance->db->escape_string($data[4])."',
												'".$ilance->db->escape_string($data[5])."',
												'".$ilance->db->escape_string($data[6])."',
												'".$ilance->db->escape_string($data[7])."',
												'".$ilance->db->escape_string($data[8])."',
												'".$ilance->db->escape_string($data[9])."',
												'".$ilance->db->escape_string($data[10])."',
												'".$ilance->db->escape_string($data[11])."',
												'".$ilance->db->escape_string($data[12])."',
												'".$ilance->db->escape_string($data[13])."',
												'".$ilance->db->escape_string($data[14])."',
												'".$ilance->db->escape_string($data[15])."',
												'".$ilance->db->escape_string($data[16])."','".$ilance->db->escape_string($data[17])."','".$ilance->db->escape_string($data[18])."','".$ilance->db->escape_string($data[19])."','".$ilance->db->escape_string($data[20])."','".$ilance->db->escape_string($data[21])."','".$ilance->db->escape_string($data[22])."','".$ilance->db->escape_string($data[23])."','".$ilance->db->escape_string($data[24])."','".$ilance->db->escape_string($data[25])."','".$ilance->db->escape_string($data[26])."','".$ilance->db->escape_string($data[27])."','".$ilance->db->escape_string($data[28])."','".$ilance->db->escape_string($data[29])."','".$ilance->db->escape_string($data[30])."','".$ilance->db->escape_string($data[31])."','".$ilance->db->escape_string($data[32])."','".$ilance->db->escape_string($data[33])."'
												
												)
												
												");	
											  
												/*$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "catalog_coin
												(id, Orderno, PCGS, Modern, cat, series, series_seq, date_colin, Denom, Variety, Grade, Mintage, Total_Graded, coin_series_unique_no, coin_series_denomination_no, coin_detail_year, coin_detail_mintmark, coin_detail_coin_series, coin_detail_denom_long, coin_detail_denom_short, coin_detail_proof, coin_detail_suffix, coin_detail_major_variety, coin_detail_die_variety, coin_detail_key_date,coin_detail_mintage, coin_detail_low_mintage, coin_detail_weight, coin_detail_composition, coin_detail_diameter, coin_detail_designer, coin_detail_description_long, coin_detail_description_short, coin_detail_notes, coin_detail_ngc_no, coin_detail_ebay_heading, coin_detail_ebay_category, coin_detail_related_coins, coin_detail_meta_description, coin_detail_meta_title, coin_detail_image, coin_detail_image_alt, coin_detail_sort, coin_detail_coin_series_no,nmcode)
												VALUES
												(
												NULL,
												'".$ilance->db->escape_string($data[0])."',
												'".$ilance->db->escape_string($data[1])."',
												'".$ilance->db->escape_string($data[2])."',
												'".$ilance->db->escape_string($data[3])."',
												'".$ilance->db->escape_string($data[4])."',
												'".$ilance->db->escape_string($data[5])."',
												'".$ilance->db->escape_string($data[6])."',
												'".$ilance->db->escape_string($data[7])."',
												'".$ilance->db->escape_string($data[8])."',
												'".$ilance->db->escape_string($data[9])."',
												'".$ilance->db->escape_string($data[10])."',
												'".$ilance->db->escape_string($data[11])."',
												'".$ilance->db->escape_string($data[12])."',
												'".$ilance->db->escape_string($data[13])."',
												'".$ilance->db->escape_string($data[14])."',
												'".$ilance->db->escape_string($data[15])."',
												'".$ilance->db->escape_string($data[16])."','".$ilance->db->escape_string($data[17])."','".$ilance->db->escape_string($data[18])."','".$ilance->db->escape_string($data[19])."','".$ilance->db->escape_string($data[20])."','".$ilance->db->escape_string($data[21])."','".$ilance->db->escape_string($data[22])."','".$ilance->db->escape_string($data[23])."','".$ilance->db->escape_string($data[24])."','".$ilance->db->escape_string($data[25])."','".$ilance->db->escape_string($data[26])."','".$ilance->db->escape_string($data[27])."','".$ilance->db->escape_string($data[28])."','".$ilance->db->escape_string($data[29])."','".$ilance->db->escape_string($data[30])."','".$ilance->db->escape_string($data[31])."','".$ilance->db->escape_string($data[32])."','".$ilance->db->escape_string($data[33])."'
												
												)
												
												");	*/
												
													}
								}
								 
									
																		 						
								}							
							 
							 }
						}							
					}
					
					else
					{
						print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
						exit();
					}	
					fclose($handle);
					print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF'].'');
								exit();									
				}			
				else 
				{
				   
					print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
					exit();							
				}
				
				  }	
				  
				   //Coin list select
				       $coin_list_level = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "catalog_coin LIMIT 10");
										if($ilance->db->num_rows($coin_list_level) > 0)
										{
										$row_count_list = 0;
										while($row_coin_list = $ilance->db->fetch_array($coin_list_level))
										{
										$row_coin_list['Orderno'] = substr($row_coin_list['Orderno'], 0, 50);
										$row_coin_list['PCGS'] = substr($row_coin_list['PCGS'], 0, 50);
										$row_coin_list['coin_detail_denom_short'] = $row_coin_list['coin_detail_denom_short'];
										$row_coin_list['coin_detail_coin_series'] = $row_coin_list['coin_detail_coin_series'];
										$row_coin_list['edit'] = '<a href="catalog.php?cmd=update-catalog&subcmd=update-coinlist&id='.$row_coin_list['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';
										$row_coin_list['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="catalog.php?cmd=update-catalog&subcmd=deletecoinlist&amp;id='.$row_coin_list['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										$coinlistcatergory[] = $row_coin_list;
										$row_count_list++;
										// $prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
										}
										
						                }
										
										else
										{				
										$show['no'] = 'coin';
										}
	               //coin list search
				   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search_coin')
                    {
					//print_r($_POST);
					
					$show['searchcoin'] = 'searchcoin';
					
					$top_level_search = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "catalog_coin
										        WHERE   Orderno	= '".$ilance->GPC['filtervalue']."'
													OR	PCGS	= '".$ilance->GPC['filtervalue']."'
													OR	coin_series_unique_no	= '".$ilance->GPC['filtervalue']."'
													OR	coin_series_denomination_no	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_year	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_mintmark	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_coin_series	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_denom_long	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_denom_short	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_proof	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_suffix	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_major_variety	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_die_variety	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_key_date	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_mintage	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_low_mintage	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_weight	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_composition	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_diameter	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_designer	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_description_long	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_description_short	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_notes	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_ngc_no	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_ebay_heading	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_ebay_category	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_related_coins	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_meta_description	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_meta_title	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_image	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_image_alt	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_sort	= '".$ilance->GPC['filtervalue']."'
													OR	coin_detail_coin_series_no	= '".$ilance->GPC['filtervalue']."'

										");
										
										if($ilance->db->num_rows($top_level_search) > 0)
										{
										$row_coin_search_v = 0;
										while($row_coin_search = $ilance->db->fetch_array($top_level_search))
										{
										$row_coin_search['Orderno'] = substr($row_coin_search['Orderno'], 0, 50);
										$row_coin_search['PCGS'] = substr($row_coin_search['PCGS'], 0, 50);
										$row_coin_search['coin_detail_denom_short'] = $row_coin_search['coin_detail_denom_short'];
										$row_coin_search['coin_detail_coin_series'] = $row_coin_search['coin_detail_coin_series'];
										$row_coin_search['edit'] = '<a href="catalog.php?cmd=update-catalog&subcmd=update-coinlist&id='.$row_coin_search['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';
										$row_coin_search['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="catalog.php?cmd=update-catalog&subcmd=deletecoinlist&amp;id='.$row_coin_search['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										$coincatergorysearch[] = $row_coin_search;
										$row_coin_search_v++;
										}
						                }
										else
										{				
										$showsearch['no'] = 'coinyes';
										}
					} 										
				   			
										
										
 	$pprint_array = array('prevnext','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'catalog_listing.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('toplevelcatergorysearch','toplevelcatergory','secondlevelcatergory','secondlevelcatergorysearch','coinlistcatergory','coincatergorysearch'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

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