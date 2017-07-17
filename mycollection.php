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
        'search',
        'tabfx',
        'jquery',
        'modal',
        'yahoo-jar',
	'flashfix'
);

// #### define top header nav ##################################################
$topnavlink = array(
	'mycollection'
);

// #### setup script location ##################################################
define('LOCATION', 'mycollection');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################

if(isset($ilance->GPC['mycollection_name']))
{

        $query = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "mynew_collection WHERE myname='".$ilance->GPC['mycollection_name']."' and user_id = '".$_SESSION['ilancedata']['user']['userid']."'");
        if($ilance->db->num_rows($query) > 0)
		{
		echo 'exit';
		}
		else
		{
		             //insert myname value 
					 $con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "mynew_collection
					 (id, myname, user_id)
					 VALUES (
						   NULL,
						   '".$ilance->GPC['mycollection_name']."',
						   '".$ilance->GPC['user_te']."'
						   
					 )");
		}
}
else if(isset($ilance->GPC['myitemid']))
{

		 $con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "mynew_stuff
		 (id, item_id, pcgs, myname, user_id, description, gc, collection, visible, grade, rand_no)
		 VALUES (
			   NULL,
			   '".$ilance->GPC['myitemid']."',
			   '".$ilance->GPC['pcgs']."',
			   '".$ilance->GPC['mycolid']."',
			   '".$ilance->GPC['user_te']."',
			   '',
			   'yes',
			   '',
			   '1',
			   '',
			   ''
			)");
         echo 'sav';
 
}
else if(isset($ilance->GPC['moveti']))
{
 $con_up = $ilance->db->query("UPDATE  " . DB_PREFIX . "mynew_stuff
										   SET myname ='".$ilance->GPC['myna']."'
										   WHERE id ='".$ilance->GPC['moveti']."'
										   AND user_id = '".$_SESSION['ilancedata']['user']['userid']."'
										   ");
echo 'move';
}
else if(isset($ilance->GPC['mycoinsave_del']))
{

		 $con_up = $ilance->db->query("UPDATE  " . DB_PREFIX . "mynew_stuff
										   SET visible ='0'
										   WHERE id ='".$ilance->GPC['mycoinsave_del']."'
										   AND user_id = '".$ilance->GPC['user_te']."'
										   ");
         echo 'upd';
 
}
else if(isset($ilance->GPC['mycollection_del']))
{                  

                    
					 $checkname = $ilance->db->query("select myname as name from " . DB_PREFIX . "mynew_collection where id ='".$ilance->GPC['mycollection_del']."'");
					 $row_list= $ilance->db->fetch_array($checkname);
                     $check = $ilance->db->query("select * from " . DB_PREFIX . "mynew_stuff where myname ='".$row_list['name']."'");   
                     if($ilance->db->num_rows($check) > 0)
					 {
					 echo 'sor';
					
					 }
					 else
					 {               
                     //delete myname value 
                     $con_insert = $ilance->db->query("DELETE FROM " . DB_PREFIX . "mynew_collection WHERE id ='".$ilance->GPC['mycollection_del']."' and user_id = '".$ilance->GPC['user_te']."'");
					 
					 echo 'del';
					 }
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'mydata_coin')
{
     
	     if($ilance->GPC['pcgs'] == '')
		 {
		  print_notice('Sorry', 'Please enter atleast pcgs number', $ilpage['mydata'], ucwords($phrase['_click_here']));
		   exit();
		 }
		 else
		 {

		 $con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "mynew_stuff
		 (id, item_id, pcgs, myname, user_id, description, gc, collection, visible, grade, rand_no)
		 VALUES (
			   NULL,
			   '',
			   '".$ilance->GPC['pcgs']."',
			   '',
			   '".$_SESSION['ilancedata']['user']['userid']."',
			   '".$ilance->GPC['description']."',
			   'no',
			   '',
			   '1',
			   '".$ilance->GPC['grade']."',
			   '".$ilance->GPC['ranval']."'
			)");
        
 

           print_notice('Success', 'Successfully added to your list', $ilpage['mydata'], ucwords($phrase['_click_here']));
		   exit();
		   }
}
else
{
        $query_solds_bid = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "project_bids");
	    if($ilance->db->num_rows($query_solds_bid) > 0)
										{
										$pro_bis = "" . DB_PREFIX ."project_bids b,";
										$pb = 'AND b.bidstatus = \'awarded\'';
										$pro_and ="and b.user_id='".$_SESSION['ilancedata']['user']['userid']."'";
										$both_val = 'AND (p.project_id = b.project_id OR ';
										}
										else
										{
										$pb = '';
										$pro_bis = '';
										$pro_and = '';
										$both_val = '';
										}
		$query_solds_now = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "buynow_orders");
	    if($ilance->db->num_rows($query_solds_now) > 0)
										{
										$pro_now = "" . DB_PREFIX ."buynow_orders sh,";
										$pro_now_and ="and sh.buyer_id='".$_SESSION['ilancedata']['user']['userid']."'";
										$both_val1 = 'p.project_id = sh.project_id)';
										}
										else
										{
										$pro_now = '';
										$pro_now_and = '';
										$both_val1='';
										}
										

	    $query_solds = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "coins c,
										 $pro_bis
										 $pro_now
										 " . DB_PREFIX . "projects p
										 WHERE  p.project_id not in (select item_id from " . DB_PREFIX . "mynew_stuff where user_id='".$_SESSION['ilancedata']['user']['userid']."')
										        AND p.project_state = 'product'
										        AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
                                                AND p.visible = '1'
												$pb
												$both_val$both_val1
												$pro_and
												$pro_now_and
												group by p.project_id
												
                                ");
								
								
								   
							    if($ilance->db->num_rows($query_solds) > 0)
								{
								
								$query_solds_count = 0;
								while($row_list_sold = $ilance->db->fetch_array($query_solds))
									{
								 
									$row_list_sold['Seller'] = fetch_user('username',$row_list_sold['user_id']);
									
									if($row_list_sold['winner_user_id'] != '0')
									{
									$row_list_sold['Buyer']  = fetch_user('username',$row_list_sold['winner_user_id']);
									$s_user = $row_list_sold['winner_user_id'];
									$row_list_sold['Amount'] = $row_list_sold['bidamount'] + $row_list_sold['buyershipcost'];
									$order_k =  explode('-',$row_list_sold['date_awarded']);
									$dt = explode(' ',$order_k['2']);
									
									$order_e = $order_k['1'].'-'.$dt['0'].'-'.$order_k['0'];
									$row_list_sold['SoldDate'] = $order_e;
									}
									else
									{
									$s_user = $row_list_sold['buyer_id'];
									$row_list_sold['Buyer']  = fetch_user('username',$row_list_sold['buyer_id']);
									$row_list_sold['Amount'] = $row_list_sold['buynow_price'] + $row_list_sold['buyershipcost'];
									$order =  explode('-',$row_list_sold['orderdate']);
									$dt    = explode(' ',$order['2']);
									
									$order_list = $order['1'].'-'.$dt['0'].'-'.$order['0'];
									$row_list_sold['SoldDate'] = $order_list;
									}
								
									$row_list_sold['CancelSale'] = '<span style="cursor:pointer; color:#5A8CAF;" onclick="addmystuff('.$row_list_sold['project_id'].','.$row_list_sold['cid'].','.$_SESSION['ilancedata']['user']['userid'].');"><strong>ADD</strong></span>';
									
								    $row_list_sold['class'] = ($query_solds_count % 2) ? 'fir' : 'sec';
									$item_won_list[] = $row_list_sold;
									
									
									$query_solds_count++;
									}
								}
								else
								{				
								 $show['er'] = 'won_list';
								}
								
						$user_id = $_SESSION['ilancedata']['user']['userid'];
						
						$query = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "mynew_collection WHERE user_id='".$user_id."'");
						$listall = '<select name="mycolc" id="myt">';
						if($ilance->db->num_rows($query) > 0)
		                {
						$st = 0;
						while($row_c =  $ilance->db->fetch_array($query))
							{
							
							$listall.= '<option value="'.$row_c['myname'].'">'.$row_c['myname'].'</option>';
							$row_c['del'] = '<span style="cursor:pointer;color:#5A8CAF;" onclick="delmycp('.$row_c['id'].','.$row_c['user_id'].');"><strong>Delete</strong></span>';
							 $row_c['class'] = ($st % 2) ? 'fir' : 'sec';
							 $my_coll[] = $row_c;
							 $st++;
							}
						}
						else
						{
						  $show['dno'] = 'listmy';
						}
                        $listall.= '</select>';
						
						$querysta = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "mynew_stuff WHERE user_id='".$user_id."' and visible='1'");
						if($ilance->db->num_rows($querysta) > 0)
		                {
						$stw = 0;
						while($row_d =  $ilance->db->fetch_array($querysta))
							{
									if($row_d['gc'] == 'no')
									$row_d['edit'] = 'Edit';
									else
									$row_d['edit'] = '';
									  
							 
									$query_st = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "mynew_collection WHERE myname !='".$row_d['myname']."' and user_id='".$user_id."'");
									
									$listallr = '<select name="mycolc" id="mnew" onchange="clic('.$row_d['id'].',this.value);"><option value="0">Select</option>';
									$listallk = '';
									while($row_s =  $ilance->db->fetch_array($query_st))
									{
									
									$listallr.= '<option value="'.$row_s['myname'].'">'.$row_s['myname'].'</option>';
								
									$listallk.=$row_s['myname'];
									}
									$listallr.= '</select>';
							$row_d['mov'] = $listallr; 
							$row_d['move'] =  '<span style="cursor:pointer;color:#5A8CAF;" onclick="moveti('.$row_d['id'].',document.getElementById(\''.$row_d['id'].'\').value);"><strong>Move</strong></span>'; 
							$row_d['movre'] = '<input type="hidden" id="'.$row_d['id'].'" value=""  />';
	
							$row_d['dele'] = '<span style="cursor:pointer;color:#5A8CAF;" onclick="delmycoinsave('.$row_d['id'].','.$row_d['user_id'].');"><strong>Delete</strong></span>';
							$row_d['class'] = ($stw % 2) ? 'fir' : 'sec';
							$fav_quer=$ilance->db->query("SELECT *  FROM " . DB_PREFIX ."myfav WHERE pcgs=".$row_d['pcgs']." AND user_id=".$_SESSION['ilancedata']['user']['userid']." ");
							if($ilance->db->num_rows($fav_quer) > 0)
							{
							$checked='checked=checked';
							} 
							else
							{
							$checked='';
							}
							$row_d['fav']='<input type="checkbox" id="'.$row_d['pcgs'].'" value="'.$row_d['pcgs'].'"'.$checked .' onclick="showfav(this.value)" >';
							$my_coll_total[] = $row_d;
							$stw++;
							}
						}
						else
						{
						  $show['no'] = 'total';
						}
						
						//coin proof grade
						$pro_grade  = '<select name="grade" id="Grade">
							 <option value="" selected="selected">Select</option> ';
						 $sqlcat_p_g = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "coin_proof ORDER BY id DESC
						");
						 if ($ilance->db->num_rows($sqlcat_p_g) > 0)
						 {         
							
								while ($rescat_p_g = $ilance->db->fetch_array($sqlcat_p_g))
								{
												
											$pro_grade.='<option value="'.$rescat_p_g['value'].'">'.$rescat_p_g['value'].'</option>';
								}
								
						}
							
						$pro_grade.='</select>';
					$ilance->subscription = construct_object('api.subscription');  
			 $ilance->subscription->check_access($user_id, 'uploadlimit');
			 $attachment_style = ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'no') ? 'disabled="disabled"' : '';
				$rand_value = substr(md5(uniqid(rand(), true)),0,6);
				$varcase = '<input type="hidden" value="'.$rand_value.'" name="ranval">';
					     // item photo upload button 
			 $hiddeninput = array(
					'attachtype' => 'project',
					'project_id' => $rand_value,
					'user_id' => $_SESSION['ilancedata']['user']['userid'],
					'category_id' => '0',
					'filehash' => md5(time()),
					'max_filesize' => $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit'),
					'attachmentlist' => 'itemphoto_attachmentlist'
			 );                
			 
	         $uploadproductbutton = '<input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?userid='.$_SESSION['ilancedata']['user']['userid'].'&crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' .    $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
	unset($hiddeninput);
                        $show['widescreen'] = true;
			//my fav coin venkat
						
						$my_fav_new = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "myfav WHERE user_id='".$_SESSION['ilancedata']['user']['userid']."'");
						 if ($ilance->db->num_rows($my_fav_new) > 0)
						 {
						
						$y = 0;
						while($row_myfav= $ilance->db->fetch_array($my_fav_new))
						{
					
						$des=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "mynew_stuff WHERE pcgs=".$row_myfav['pcgs']."");
                        $des_row=$ilance->db->fetch_array($des);
                        
						$row_myfav['des']=$des_row['description'];
						 $my_fav[]=$row_myfav;
						
						
						$y++;
						}
												
						}
						else
						{
						$show['favno'] == 'listmy';
						}			
						
						$coin_txt = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "mynew_collection");
						
						
						$txt_coin=' You have not set up a Collection Name yet, please “Add a New Collection Name”.  For example, “My Lincoln Cents MS” or “Bob’s Morgan Dollars”.<br><br> ';
						
						//my fav coin venkat
						
						 			//$onload = 'test();';

$pprint_array = array('varcase','uploadproductbutton','pro_grade','listall','user_id','site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','fav','txt_coin');
                        
			$ilance->template->fetch('main', 'mycollection.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('item_won_list','my_coll','my_coll_total','watchlist_rfp','my_fav'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
}			
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>