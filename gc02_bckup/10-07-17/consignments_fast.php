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
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['consignment'] => $ilcrumbs[$ilpage['consignment']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
$year_of_pcgs = $coin_detail_coin_series = $certification_no = $minimum_bid = $SetCoins = '';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	
	 //New Changes on 12feb for #1305
    if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'view_list')
	{
	
	 $consign_id = intval($ilance->GPC['consign_id']);
	 
	 if($consign_id > 0)
	 {
				
     header("Location:consignlist_pdf.php?consign_id=".$consign_id."");
	 
	 }
	
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'message_pdf_generator' 
		AND isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id']>0 
		AND isset($ilance->GPC['consignid']) AND $ilance->GPC['consignid']>0)
	{
		error_reporting(E_ALL);
		define('FPDF_FONTPATH','../font/');
		require('pdftable_1.9/lib/pdftable.inc.php');
		$p = new PDFTable();
		$sql1="SELECT * FROM " . DB_PREFIX . "users u left join
		 " . DB_PREFIX . "consignments c on c.user_id=u.user_id left join
		 " . DB_PREFIX . "consignment_pdf_messages m on m.id=".$ilance->GPC['msgid']."
		 WHERE u.user_id = '" . $ilance->GPC['user_id'] . "' and c.consignid='".$ilance->GPC['consignid']."'";
		$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result1)>0)
		{
			while($line1= $ilance->db->fetch_array($result1))
			{
				$user_details=$line1;
			}
		}
		$html=get_header_pdf($user_details);

		$p->AddPage();
		$p->setfont('times','',10);	
		$p->htmltable($html);
		$p->output('Message_'.DATETIME24H.'.pdf','D');
		exit;
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'pdf_message_add')
	{
		$name='';
		$message='';
		$commmand="add";
		if(isset($ilance->GPC['subcmd']))
		{
			$sql="insert into " . DB_PREFIX . "consignment_pdf_messages (name,message) values ('".$ilance->db->escape_string($ilance->GPC['name'])."','".$ilance->db->escape_string($ilance->GPC['message'])."')";
			$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
			print_action_success('Message Added',  HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
    		exit();
		}

		$pprint_array = array('name','message','commmand','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'consignments_pdf_msg.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'pdf_message_edit')
	{
		$name='';
		$message='';
		$id=$ilance->GPC['msgid'];
		$sql2="SELECT * FROM " . DB_PREFIX . "consignment_pdf_messages WHERE id = '" . $ilance->GPC['msgid'] . "'";
		$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result2)>0)
		{
			while($line2= $ilance->db->fetch_array($result2))
			{
				$name=$line2['name'];
				$message=$line2['message'];
			}
		}
		$commmand="edit";
		if(isset($ilance->GPC['subcmd']))
		{
			$sql="Update " . DB_PREFIX . "consignment_pdf_messages set name='".$ilance->db->escape_string($ilance->GPC['name'])."',message='".$ilance->db->escape_string($ilance->GPC['message'])."' where id='".$ilance->db->escape_string($ilance->GPC['id'])."'";
			$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
			print_action_success('Message Updated',  HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
    		exit();
		}

		$pprint_array = array('id','name','message','commmand','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'consignments_pdf_msg.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'pdf_message_delete')
	{
		$sql="Delete from " . DB_PREFIX . "consignment_pdf_messages where id='".$ilance->db->escape_string($ilance->GPC['msgid'])."'";
		$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		print_action_success('Message Deleted',  HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
		exit();
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'coin_list')
    {
    		$show['edit_message']=$show['new_message']=false;
    		$edit_message_id=0;
    		$edit_message='';
	        if($ilance->GPC['user_id'] == '' || $ilance->GPC['consignid'] == '')
			{
			 	print_action_success('sorry', $ilpage['consignment']);
                exit();
			}

			if(isset($ilance->GPC['msgcmd']) and $ilance->GPC['msgcmd']=='new')
			{
				$show['new_message']=true;
			}
			if (isset($ilance->GPC['cmd']) AND $ilance->GPC['msgcmd'] == 'note_pdf_generator' 
				AND isset($ilance->GPC['msgid']) AND $ilance->GPC['msgid']>0 )
			{
				error_reporting(E_ALL);
				define('FPDF_FONTPATH','../font/');
				require('pdftable_1.9/lib/pdftable.inc.php');
				$p = new PDFTable();
				$sql1="SELECT * FROM " . DB_PREFIX . "users u left join
				 " . DB_PREFIX . "consignments c on c.user_id=u.user_id left join
				 " . DB_PREFIX . "consignment_messages m on m.id=".$ilance->GPC['msgid']."
				 WHERE u.user_id = '" . $ilance->GPC['user_id'] . "' and c.consignid='".$ilance->GPC['consignid']."'";
				$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($result1)>0)
				{
					while($line1= $ilance->db->fetch_array($result1))
					{
						$user_details=$line1;
					}
				}
				$html=get_header_pdf($user_details);

				$p->AddPage();
				$p->setfont('times','',10);	
				$p->htmltable($html);
				$p->output('Message_'.DATETIME24H.'.pdf','D');
				exit;
			}
			if(isset($ilance->GPC['msgcmd']) and $ilance->GPC['msgcmd']=='add_new')
			{
				$sql="insert into " . DB_PREFIX . "consignment_messages (consignment_id,message,created_on) value ('".$ilance->GPC['consignid']."','".$ilance->db->escape_string($ilance->GPC['new_message'])."','".DATETIME24H."')";
				$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
				if(isset($ilance->GPC['send_email']))
					{
						//send email
						send_consignor_message_email($ilance->GPC['new_message'],$ilance->GPC['user_id'],$ilance->GPC['consignid']);

						print_action_success('Message Sent and saved',  HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
                		exit();
					}
					print_action_success('Message saved', HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
                	exit();
			}

			if(isset($ilance->GPC['msgcmd']) and $ilance->GPC['msgcmd']=='edit')
			{
				if(isset($ilance->GPC['save_message']) and $ilance->GPC['save_message']=="Save" and isset($ilance->GPC['edit_message_id']) and $ilance->GPC['edit_message_id']>0)
				{
					$sql5="update " . DB_PREFIX . "consignment_messages set message='".$ilance->db->escape_string($ilance->GPC['edit_message'])."' WHERE id = '" . $ilance->GPC['edit_message_id'] . "'";
					$result5 = $ilance->db->query($sql5, 0, null, __FILE__, __LINE__);
					if(isset($ilance->GPC['send_email']))
					{
						//send email
						send_consignor_message_email($ilance->GPC['edit_message'],$ilance->GPC['user_id'],$ilance->GPC['consignid']);

						print_action_success('Message Sent and Edited',  HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
                		exit();
					}
					print_action_success('Message edited', HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
                	exit();
				}
				$sql4="select * from " . DB_PREFIX . "consignment_messages WHERE id = '" . $ilance->GPC['msgid'] . "'";
				$result4 = $ilance->db->query($sql4, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($result4)>0)
				{
					while($line4= $ilance->db->fetch_array($result4))
					{
						$edit_message_id=$line4['id'];
						$edit_message=$line4['message'];
					}
				}
				$show['edit_message']=true;
			}

			if(isset($ilance->GPC['msgcmd']) and $ilance->GPC['msgcmd']=='delete')
			{
				$sql3="delete from " . DB_PREFIX . "consignment_messages WHERE id = '" . $ilance->GPC['msgid'] . "'";
				$result3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
				print_action_success('Message Sucessfully deleted', HTTP_SERVER.'staff/consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid']);
                exit();
			}
			if($ilance->GPC['user_id'] >0 || $ilance->GPC['consignid'] >0)
			{
			     //user details for coin details
				 $sqlcat_user_detail = $ilance->db->query("
				 
								SELECT c.coins, c.referal_id, u.username, u.first_name, u.last_name, u.email
								FROM " . DB_PREFIX . "consignments c
								JOIN " . DB_PREFIX . "users u ON u.user_id = c.user_id
								WHERE c.user_id =  '".$ilance->GPC['user_id']."'
								AND c.consignid =  '".$ilance->GPC['consignid']."'
								
								", 0, null, __FILE__, __LINE__);
								        if($ilance->db->num_rows($sqlcat_user_detail) > 0)
										{
										$row_user   = $ilance->db->fetch_array($sqlcat_user_detail);
										$username   = $row_user['username'];
										$firstname =  $row_user['first_name'];
										$lastname =   $row_user['last_name'];
										$email      = $row_user['email'];
										$consignid  = $ilance->GPC['consignid'];
										$referal_id = $row_user['referal_id'];
										$coins      = $row_user['coins'];
										}
								//count value
								$sqlcat_coin_details = $ilance->db->query("select consignid,
								sum(case when coin_listed = 'c' then 1 else 0 end) cer,
								sum(case when coin_listed = 'r' then 1 else 0 end) raw,
								sum(case when coin_listed = 's' then 1 else 0 end) send
								FROM " . DB_PREFIX . "coins c
								WHERE c.user_id = '".$ilance->GPC['user_id']."'
								AND c.consignid = '".$ilance->GPC['consignid']."'
								group by c.consignid", 0, null, __FILE__, __LINE__);
								$row_coin_lists = $ilance->db->fetch_array($sqlcat_coin_details);



								$raw_count = $row_coin_lists['raw'];
								$listed_count = $row_coin_lists['cer'];
								$send_count = $row_coin_lists['send'];
								//end
							
		
				                //coins list for user
				                $sqlcat_coin_detail = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "coins
								WHERE user_id = '".$ilance->GPC['user_id']."'
								AND consignid = '".$ilance->GPC['consignid']."'
								
								
								", 0, null, __FILE__, __LINE__);
								
								 $posted = (int)$ilance->db->num_rows($sqlcat_coin_detail);
								 if($ilance->db->num_rows($sqlcat_coin_detail) > 0)
										{
										
										$i = 1;
										$row_coin_v_list = 0;
										while($row_coin_list = $ilance->db->fetch_array($sqlcat_coin_detail))
										{	
										
										 if($row_coin_list['project_id'] != '0')
										 { 
										   
										 $certified = '<strong>Posted</strong>';
										 }
										 else
										 {
											 if($row_coin_list['coin_listed'] == 'c')
											 {
											 $certified = '<strong>Certified Coin</strong> <a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&pro='.$row_coin_list['coin_id'].'&coin_id='.$row_coin_list['coin_id'].'">(Update)</a>';
											 }
											 else
											 {
											 $certified = '<strong><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&pro=certcoin&coin_id='.$row_coin_list['coin_id'].'">Relist</a></strong>';
											 }
										 }
										
										 if($row_coin_list['project_id'] != '0')
										 { 
										 $raw = '<strong>Posted</strong>';
										 }
										 else
										 {
											 if($row_coin_list['coin_listed'] == 'r')
											 {
											
									
											 $raw = '<strong>Raw Coin</strong>';
											 }
											 else
											 {
											 
											 $raw = '<a href="consignments_fast.php?cmd=raw_coins&relist=update_con&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&pro=rawup&coin_id='.$row_coin_list['coin_id'].'"><strong>Relist</strong></a>';
											 }
										 }
										 if($row_coin_list['project_id'] != '0')
										 { 
										 $send_certified = '<strong>Posted</strong>';
										 }
										 else
										 { 
											 if($row_coin_list['coin_listed'] == 's')
											 {
											 $send_certified = '<strong>Send Certification</strong>';
											 }
											 else
											 {
											 $send_certified = '<a href="consignments_fast.php?cmd=send_certification&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&pro=sendcert&coin_id='.$row_coin_list['coin_id'].'"><strong>Relist</strong></a>';
											 }
										 }
											
										$row_coin_list['coin'] = $i;
										if($row_coin_list['pcgs'] == '')
										{
										$row_coin_list['pcgs'] = '';
										}
										else
										{
										$row_coin_list['pcgs'] = $row_coin_list['pcgs'];
										}
										$row_coin_list['desci'] = '<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&pro='.$row_coin_list['coin_id'].'&coin_id='.$row_coin_list['coin_id'].'">'.$row_coin_list['Title'].'</a></span>';	
												
									    $row_coin_list['coinid'] = 	'<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&pro='.$row_coin_list['coin_id'].'&coin_id='.$row_coin_list['coin_id'].'">'.$row_coin_list['coin_id'].'</a></span>';	
										
										$row_coin_list['pc'] = '<span class="blue">'.$row_coin_list['pcgs'].'</span>';
										
										
										
										
										$row_coin_list['certified'] = $certified;
										$row_coin_list['raw']  = $raw;
										$row_coin_list['send_certified'] = $send_certified;
										$coin_list[] = $row_coin_list;
										$row_coin_v_list++;
										$i++;
										}
										}
										else
										{
										$show['no'] = 'coin_list_search';
										}
										
					
										$catlogorder = '<span style="cursor:pointer; margin-left: 350px;" class="blue" onClick="window.open(\'consignments_pdf.php?cmd=pdf&subcmd=catlog&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&nocoin='.$coins.'&noposted='.$posted.'&list='.$listed_count.'&raw='.$raw_count.'&send='.$send_count.'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Catalog Sort Order</span>';
										
										
										$coinorder = '<span style="cursor:pointer; margin-left: 120px;"  class="blue" onClick="window.open(\'consignments_pdf.php?cmd=pdf&subcmd=coinord&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&nocoin='.$coins.'&noposted='.$posted.'&list='.$listed_count.'&raw='.$raw_count.'&send='.$send_count.'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Coin Id Sort Order</span>';
										
					//coin disable and enabled
					$sqlcat_coin_count = $ilance->db->query("
								SELECT COUNT(*) AS coins_value
								FROM " . DB_PREFIX . "coins
								WHERE user_id = '".$ilance->GPC['user_id']."'
								AND   consignid = '".$ilance->GPC['consignid']."'
								", 0, null, __FILE__, __LINE__);	
								$row_coin_count = $ilance->db->fetch_array($sqlcat_coin_count);
								
								if($row_coin_count['coins_value'] == $coins)
								{
								$newadd =  '<a onclick="return checkUser('.$ilance->GPC['user_id'].',event);" href="consignments_fast.php?cmd=add_single_coin&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'">Add Another Coin</a>';
								}
								else
								{
								$addsinglecoin = '<a onclick="return checkUser('.$ilance->GPC['user_id'].',event);" href="consignments_fast.php?cmd=add_single_coin&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'">Add New Single Coin</a>';
								$addrawcoin    = '<a href="consignments_fast.php?cmd=raw_coins&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'">Add New Raw Coin</a>';
								$sendcer       = '<a href="consignments_fast.php?cmd=send_certification&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'">Add New Send Certification</a>';
								
								}
							 
								$sql7="SELECT id,name FROM " . DB_PREFIX . "consignment_pdf_messages";
								$result7 = $ilance->db->query($sql7, 0, null, __FILE__, __LINE__);
								if($ilance->db->num_rows($result7)>0)
								{
									$pdf_content_drop_down='<select name="pdf_drop_down" id="pdf_drop_down" >';
									while($line7= $ilance->db->fetch_array($result7))
									{
										$pdf_content_drop_down.='<option value="'.$line7['id'].'">'.$line7['name'].'</options>';
									}
									$pdf_content_drop_down.='</select>';
								}				
														
								$i=1;
								$sql="SELECT *, date_format(created_on,'%D %b %Y %h:%i %p')as created_on FROM " . DB_PREFIX . "consignment_messages WHERE consignment_id = '" . $ilance->GPC['consignid'] . "'";
								$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
								if($ilance->db->num_rows($result)>0)
								{
									while($line= $ilance->db->fetch_array($result))
									{

										$row_message['slno']=$i;
										$row_message['message']=$line['message'];
										$row_message['created_on']=$line['created_on'];
										$row_message['note_pdf_generator_link']='<a href="consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&msgid='.$line['id'].'&msgcmd=note_pdf_generator">Pdf</a>';
										$row_message['edit']=					'<a href="consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&msgid='.$line['id'].'&msgcmd=edit">Edit</a>';
										$row_message['delete']=					'<a href="consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&msgid='.$line['id'].'&msgcmd=delete">Delete</a>';

										$consignment_message_list[] = $row_message;
										$row_message_list++;
										$i++;
									}
									
								}else
								{
									$show['consignment_message']='consignment_message';
								}

								$user_id=$ilance->GPC['user_id'];
								$add_new_note = '<span style="cursor:pointer; margin-left: 350px;" class="blue" > <a href="consignments_fast.php?cmd=coin_list&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&msgid='.$line['id'].'&msgcmd=new">Add New Note</a></span>';				
								$pdf_generator_link='consignments_fast.php?cmd=message_pdf_generator&user_id='.$ilance->GPC['user_id'].'&consignid='.$ilance->GPC['consignid'].'&msgid=';
						
			$pprint_array = array('user_id','pdf_generator_link','pdf_content_drop_down','edit_message_id','edit_message','add_new_note','catlogorder','coinorder','listed_count','send_count','raw_count','count','posted','newadd','sendcer','addrawcoin','addsinglecoin','coins','referal_id','username','firstname','lastname','email','consignid','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
			
			$ilance->template->fetch('main', 'consignments_coin_listing.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('coin_list','consignment_listing_search','consignment_message_list'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
			}
	}
	
    if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'create')
    {        
		         //search user for consignment create
		         if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
                    {
					  $show['con_search'] = 'search';
					
                       $sql2 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
								WHERE user_id = '".$ilance->db->escape_string($ilance->GPC['filtervalue'])."'
								OR    username = '".$ilance->db->escape_string($ilance->GPC['filtervalue'])."'
								OR    email    = '".$ilance->db->escape_string($ilance->GPC['filtervalue'])."'
								OR    zip_code  = '".$ilance->db->escape_string($ilance->GPC['filtervalue'])."'
                       ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                $number = (int)$ilance->db->num_rows($sql2);
                               
                                while ($res = $ilance->db->fetch_array($sql2))
                                {
								    
									$user_id  = $res['user_id'];
						        	$username = $res['username'];
								    $email    = $res['email'];
								    $phone    = $res['phone'];
									$address  = $res['address'];
									$address2 = $res['address2'];
									$country  = $res['country'];
									$state    = $res['state'];
									$city     = $res['city'];
									$zipcode  = $res['zip_code'];
									//search list for country and state
									$countryid_new = $country;
									$country_js_pulldown_new = construct_country_pulldown($countryid_new, $country, 'country', false, 'state');
									$state_js_pulldown_new = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid_new, $state, 'state') . '</div>';
											
								}
						}		
                
					}
				    //save new consignment and update user
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'create-new-consignment')
				    {
					            //print_r($_POST);
								if($_POST['user_id'] == '')
								{
								 print_action_success('sorry user not valid', $ilpage['consignment'] . '?cmd=create');
                                 exit();
								}
								else
								{
								
								if(isset($ilance->GPC['pend']))
									   {
									   $pend = $ilance->GPC['pend'];
									   }
									   else
									   {
									   $pend = '0';
									   }
									 //user updated            
									 $countryid_set = fetch_country_id($_POST['country']);
									 $sqlusercheck_my = $ilance->db->query("
										UPDATE  " . DB_PREFIX . "users
										SET  username   = '" . $ilance->db->escape_string($_POST['username']) . "',
											 email      = '" . $ilance->db->escape_string($_POST['email']) . "',
											 address    = '" . $ilance->db->escape_string($_POST['address']) . "',
											 address2   = '" . $ilance->db->escape_string($_POST['address2']) . "',
											 city       = '" . $ilance->db->escape_string($_POST['city']) . "',
											 state      = '" . $ilance->db->escape_string($_POST['state']) . "',
											 zip_code   = '" . $ilance->db->escape_string($_POST['zipcode']) . "',
											 phone      = '" . $ilance->db->escape_string($_POST['phone']) . "',
											 country    = '" . $countryid_set . "'	
																									 
									   WHERE user_id    = '" . $ilance->db->escape_string($_POST['user_id']) . "'
										
										");
										//for day insert
									    $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];			
										//consignment saved						
										// murugan changes
										$con_insert = $ilance->db->query("
										INSERT INTO " . DB_PREFIX . "consignments
										(consignid, coins, final_fee_percentage, final_fee_min, site_id, end_date, referal_id, listing_fee, notes, user_id, pending, receive_date, fvf_id,consign_type)
										VALUES (
										NULL,
										'" . $_POST['coins'] . "',
										'" . $_POST['feewith'] . "',
										'" . $_POST['feemin'] . "',
										'" . $_POST['site_id'] . "',
										'" . $date_coin . "',
										'" . $ilance->db->escape_string($_POST['referal_id']) . "',
										'" . $ilance->db->escape_string($_POST['listingfee']) . "',
										'" . $_POST['notes'] . "',
										'" . $_POST['user_id'] . "',
										
										'" . $pend . "',
										
										'".DATETODAY."',
										
										'" . $_POST['fvffee'] . "',
										'" . $_POST['consign_type'] . "'
										
										)
								");
								
								print_action_success('Your Consignments Successfully Added', $ilpage['consignment'] . '?cmd=create');
								exit();
                                
								}
						
					
					}	
					//normal country and state display
					$countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
					$country_js_pulldown = construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state');
					$state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state') . '</div>';
					
			    //date function
				$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
				
				$day = date('d');
				for($i=1; $i<=31; $i++)
				if($day == $i)
				$daylist .= "<option value='$i' selected>$i</option>";
				else
				$daylist .= "<option value='$i'>$i</option>";
				
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				
				$month = date('m');
				for($j=1; $j<=12; $j++)
				
				if($month == $j)
				$monthlist .= "<option value='$j' selected>$j</option>";
				else
				$monthlist .= "<option value='$j'>$j</option>";
				
				
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				// murugan added dec 28 +1 
				$year = date('Y');;
				for($k=date("Y"); $k<=date("Y")+5; $k++)
				if($year == $k)
				$yearlist .= "<option value='$k' selected>$k</option>";
				else
				$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
					// site id
					$site_id = '<select name="site_id" id="site_id" >
					<option value="0">Listed in GC</option>';
	          
					 $sqlcat_siteid = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "affiliate_listing
						", 0, null, __FILE__, __LINE__);
						 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
						 {         
							
								while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
								{
												
											$site_id.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';
								}
								
						}
					
						$site_id.='</select>';
						
					//listing fees
					// site id
					$list_id=getif_drop_down('listingfee','listingfee',$rescat['listing_fee']);
					 
						//fvf fees
					//suku
					$fvf_id = getfvf_drop_down('fvffee','fvffee',$selected_id=5);
					
					$consign_type = gettype_drop_down('consign_type','consign_type',$selected_id=0);
					
					//referal id list				
	                $referal_id = '<select name="referal_id" id="referal_id" >';
	          
						 $sqlcat_rfid = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "referal_id
						", 0, null, __FILE__, __LINE__);
						 if ($ilance->db->num_rows($sqlcat_rfid) > 0)
						 {         
							
								while ($rescat_id = $ilance->db->fetch_array($sqlcat_rfid))
								{
												
											$referal_id.='<option value="'.$rescat_id['referalcode'].'">'.$rescat_id['referalcode'].'</option>';
								}
								
						}
					
						$referal_id.='</select>';	
					
				  $area_title = $phrase['_referal_id'];
                  $page_title = SITE_NAME . ' - ' . $phrase['_referal_id'];
				
				$pprint_array = array('consign_type','fvf_id','list_id','yearlist','monthlist','daylist','site_id','referal_id','user_id','state_js_pulldown_new','country_js_pulldown_new','state_js_pulldown','country_js_pulldown','username','email','phone','address','address2','country','state','city','zipcode','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'consignments_create.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array('searchcustomers'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
				
				
		}
		
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'add_single_coin')
	{
	              
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'create_single_coin')
			{
                
				
							if($_POST['user_id'] == '' || $_POST['consignid'] == '')
							{
							 print_action_success('sorry user not valid', $ilpage['consignment'] . '?cmd=add_single_coin');
							 exit();
							}
							else
							{
							$pcg_exp = explode('.',$_POST['pcgs']);
							//coin pcgs no verify
							$sqlcat_coin = $ilance->db->query("
							SELECT PCGS
							FROM " . DB_PREFIX . "catalog_coin
							WHERE PCGS = '".$pcg_exp['0']."'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sqlcat_coin) > 0)
							{ 
									//certification verify for same consignment or not
                                    //jan 19 comment
									/*$sqlcat_coin_verify = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins
									WHERE user_id = '".$_POST['user_id']."'
									AND consignid = '".$_POST['consignid']."'
									AND Certification_No = '".$_POST['Certification_No']."'
									");
									if($ilance->db->num_rows($sqlcat_coin_verify) > 0)
									{
									print_action_success('coin already exit in this consignment and user', $ilpage['consignment']);
					                exit();
									}
									else
									{*/
									   $date_coin = $ilance->GPC['year'] .'-'.$ilance->GPC['month'].'-'.$ilance->GPC['day'];
									   $user_start_time =  print_date(date('Y-m-d').$ilconfig['projectstarttime'],'%I:%M %p',false,false);
		                               $user_end_time =  print_date(date('Y-m-d').$ilconfig['projectendtime'],'%I:%M %p',false,false);
		                               $user_curr_time =  print_date(DATETIME24H,'%I:%M %p',false,false);
		 
									   for($i=0; $i<count($ilance->GPC['other_information']); $i++)
									   {
									   $oth_in[] = $ilance->GPC['other_information'][$i];
									   }
									   $other_info_val = implode(',',$oth_in);
								      
									   if(isset($ilance->GPC['Buy_it_now']))
									   {
									   $bidamount = $ilance->GPC['Buy_it_now'];
									     //fvf calc
									  $finalvalues = $ilance->db->query("
                                                        SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
                                                        FROM " . DB_PREFIX . "finalvalue
                                                        WHERE tierid = '" . $ilance->GPC['fvffee'] . "'
                                                            AND state = 'product'
                                                       
                                                ", 0, null, __FILE__, __LINE__);
									 $fees = $ilance->db->fetch_array($finalvalues);
									                    if ($bidamount >= $fees['finalvalue_from'])
                                                        {
                                                                if ($fees['amountfixed'] > 0)
                                                                {
                                                                        $fvf = $fees['amountfixed'];
                                                                       
                                                                }
                                                                else
                                                                {
                                                                       $fvf = ($bidamount * $fees['amountpercent'] / 100);
                                                                       
                                                                }
                                                        }
														else
														{
														$fvf = '0.00';
														}
									   $buy_now = $ilance->GPC['Buy_it_now'];
									   
									   
									   
									   }
									   else
									   {
									   $buy_now = '';
									   
									   $fvf = '';
									   }
									   if(isset($ilance->GPC['Minimum_bid']))
									   {
									   $min_bid = $ilance->GPC['Minimum_bid'];
									   }
									   else
									   {
									   $min_bid = '';
									   }
									   if(isset($ilance->GPC['Reserve_Price']))
									   {
									   $res_pr = $ilance->GPC['Reserve_Price'];
									   }
									   else
									   {
									   $res_pr = '';
									   }
									     if(isset($ilance->GPC['pend']))
									   {
									   $pend = $ilance->GPC['pend'];
									   }
									   else
									   {
									   $pend = '0';
									   }
									   /*bug id 1146 jan 04*/
									   
	 $ilance->GPC['offline_order']=(isset($ilance->GPC['offline_order']) and $ilance->GPC['offline_order']==1)?1:0;
	 
									 ($ilance->GPC['bolding']=='1')?$bold='1':$bold='0';
									 ($ilance->GPC['highlit']=='2')?$highlit='1':$highlit='0';
									 ($ilance->GPC['featurd']=='3')?$featurd='1':$featurd='0';
									// ($ilance->GPC['genuine_details']==1)?$genuine_details=1:$genuine_details=0;
									 ($ilance->GPC['genuine_details_o']=='1')?$genuine_details='1':$genuine_details='0';
									 ($ilance->GPC['details_o']=='1')?$details='1':$details='0';


									 
										$con_insert = $ilance->db->query("
										INSERT INTO " . DB_PREFIX . "coins
										(coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now,Create_Date, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed,in_notes,Sets,nocoin,pending,listing_fee,fvf_amount,fvf_id,norder,bold,highlite,featured,actual_qty,cost,make_invisible,generics,new_release,Variety,genuine_details,details,QA,spl_toned,hot_list)
										VALUES (
										NULL,
										'" . $ilance->GPC['user_id'] . "',
										'" . $pcg_exp['0'] . "',
										'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
										'" . $ilance->db->escape_string($ilance->GPC['Description']) . "',
										'" . $ilance->db->escape_string($ilance->GPC['grade_service']) . "',
										'" . $ilance->db->escape_string($ilance->GPC['Grade']) . "',
										'" . $ilance->db->escape_string($ilance->GPC['Quantity']) . "',
										'" . $ilance->db->escape_string($ilance->GPC['Max_Quantity_Purchase']) . "',
										'" . $ilance->db->escape_string($ilance->GPC['Certification_No']) . "',
										'" . $ilance->GPC['Condition_Attribute'] . "','" . $ilance->GPC['Cac'] . "','" . $ilance->GPC['Star'] . "','" . $ilance->GPC['Plus'] . "',
										'" . $ilance->GPC['coin_series'] . "','" . $ilance->GPC['Pedigee'] . "','" .$ilance->GPC['site_id'] . "','" . $min_bid . "',
										'" . $res_pr . "','" . $buy_now . "','" . DATETIME24H . "','" . $date_coin . "','" . $ilance->db->escape_string($ilance->GPC['Alternate_inventory_No']) . "',
										'" . $pcg_exp['0'] . "','" . $other_info_val  . "','" . $ilance->GPC['consignid'] . "', 'c',
										'".$ilance->db->escape_string($ilance->GPC['inotes'])."',
										'".$ilance->db->escape_string($ilance->GPC['Sets'])."',
										'".$ilance->GPC['nocoin']."',
										'".$pend."',
										'".$ilance->db->escape_string($ilance->GPC['listingfee'])."',
										'".$fvf."',
										'".$ilance->GPC['fvffee']."',
										'".$ilance->GPC['offline_order']."',
										'".$bold."',
										'".$highlit."',
										'".$featurd."' ,
										'" . $ilance->db->escape_string($ilance->GPC['Quantity']) . "',
										'".$ilance->db->escape_string($ilance->GPC['cost'])."',
										'".$ilance->db->escape_string($ilance->GPC['make_invisible'])."',
										'".$ilance->db->escape_string($ilance->GPC['generic'])."',
										'".$ilance->db->escape_string($ilance->GPC['new_release'])."',
										'".$ilance->db->escape_string($ilance->GPC['Variety'])."',
										'".$genuine_details."',
										'".$details."',
										'".$ilance->db->escape_string($ilance->GPC['QA']) . "',
										'".$spl_toned . "',
										'".$ilance->db->escape_string($ilance->GPC['hot_list']) . "'
										)
								");
							 $coin_id_last = $ilance->db->insert_id();  
							/*//for end date coin function   
							if($ilance->GPC['site_id'] == '0')
							{
							
							
								$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$date_coin."'");
			
								if($ilance->db->num_rows($restotal) > 0)
								{	
								//$my_var_in = fetch_date_time_coin($date_coin);
								
								//$my_var_in = fetch_date_time_coin($date_coin,$pcg_exp['0'],$coin_id_last);
								
								}
							}*/
							//count coin and update 
						    $sqlcat_to_day = $ilance->db->query("
							SELECT coin_id
							FROM " . DB_PREFIX . "coins
							WHERE user_id = '".$ilance->GPC['user_id']."'
							AND consignid = '".$ilance->GPC['consignid']."'
							", 0, null, __FILE__, __LINE__);
							
							$postedd = (int)$ilance->db->num_rows($sqlcat_to_day);
								 
							$sqlcat_user_day = $ilance->db->query("
							SELECT coins
							FROM " . DB_PREFIX . "consignments
							WHERE user_id = '".$ilance->GPC['user_id']."'
							AND consignid = '".$ilance->GPC['consignid']."'
							", 0, null, __FILE__, __LINE__);
							if($ilance->db->num_rows($sqlcat_user_day) > 0)
							{
								$row_user   = $ilance->db->fetch_array($sqlcat_user_day);
								
								$coinss      = $row_user['coins'];
							}
							if($postedd > $coinss)
					        {
								 $sql_update_coin = $ilance->db->query("
								 UPDATE  " . DB_PREFIX . "consignments
								 SET  coins   = '" . $postedd . "'
										 
								 WHERE consignid = '".$ilance->GPC['consignid']."'
								
								");
					        }
							
							if($_POST['randproid'] !='')
							{
							
							/*$insert_coinid = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "coins ORDER BY coin_id DESC LIMIT 1");
							
							$row_value_coinid = $ilance->db->fetch_array($insert_coinid);
							//update attachment userid and catergoryid
							$attach_concumer_sql = $ilance->db->query("SELECT *
													FROM " . DB_PREFIX . "attachment
													WHERE project_id = '".$_POST['randproid']."'
						
													");
													if ($ilance->db->num_rows($attach_concumer_sql) > 0)
													   {
					
							$attach_concumer = $ilance->db->query("UPDATE " . DB_PREFIX . "attachment
																  SET coin_id     = '".$row_value_coinid['coin_id']."',
																	  category_id = '".$_POST['cat']."'
																  WHERE project_id = '".$_POST['randproid']."'
																  ");
													   }
													   */
							}						   
							
							print_action_success('Coin Details Successfully Saved  <br><br>
							 <span class="blue"><a href="'.$ilpage['consignment'] . '?cmd=add_single_coin&user_id='.$_POST['user_id'].'&consignid='.$_POST['consignid'].'" >Add Another Coin</a></span><br><br>
							<span class="blue"><a href="'.$ilpage['consignment'] . '" class="blue">View Completed Consignments</a></span><br><br>
							<span class="blue"><a href="'.$ilpage['consignment'] . '?cmd=pen_con_list" class="blue">Viewing Pending Consignments </a></span><br><br>
							  <span class="blue"><a href="'.$ilpage['consignment'] . '?cmd=create" class="blue">Create New Consignment</a></span>
							',''.$ilpage['consignment'] . '?cmd=add_single_coin&user_id='.$_POST['user_id'].'&consignid='.$_POST['consignid'].'');
							exit();
							 /*}*/
						        }
					 
									else
									{
									print_action_success('verify your pgcs number', $ilpage['consignment'] . '?cmd=add_single_coin&user_id='.$_POST['user_id'].'&consignid='.$_POST['consignid'].'');
									exit();
									}
					}
				
					
			}
			  
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_single_coin')
			{
			       
				   
				
			  
			                if($_POST['user_id'] == '' || $_POST['consignid'] == '' ||  $_POST['coin_id'] == '')
							{
							 print_action_success('sorry user not valid', $ilpage['consignment'] . '?cmd=add_single_coin');
							 exit();
							}
							else
							{
							   $pcg_exp = explode('.',$_POST['pcgs']);
							           for($i=0; $i<count($ilance->GPC['other_information']); $i++)
									   {
									   $oth_in[] = $ilance->GPC['other_information'][$i];
									   }
									   $other_info_val = implode(',',$oth_in);
									   if(isset($ilance->GPC['Buy_it_now']))
									   {
									   
									   
						  $bidamount = $ilance->GPC['Buy_it_now'];
									     //fvf calc
									  $finalvalues = $ilance->db->query("
                                                        SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
                                                        FROM " . DB_PREFIX . "finalvalue
                                                        WHERE tierid = '" . $ilance->GPC['fvffee'] . "'
                                                            AND state = 'product'
                                                       
                                                ", 0, null, __FILE__, __LINE__);
									 $fees = $ilance->db->fetch_array($finalvalues);
									                    if ($bidamount >= $fees['finalvalue_from'])
                                                        {
                                                                if ($fees['amountfixed'] > 0)
                                                                {
                                                                        $fvf = $fees['amountfixed'];
                                                                       
                                                                }
                                                                else
                                                                {
                                                                       $fvf = ($bidamount * $fees['amountpercent'] / 100);
                                                                       
                                                                }
                                                        }
														else
														{
														$fvf = '0.00';
														}
									   $buy_now = $ilance->GPC['Buy_it_now'];
									   }
									   else
									   {
									   $buy_now = '';
									   
									   $fvf = '';
									   }
									   if(isset($ilance->GPC['Minimum_bid']))
									   {
									   $min_bid = $ilance->GPC['Minimum_bid'];
									   }
									   else
									   {
									   $min_bid = '';
									   }
									   if(isset($ilance->GPC['Reserve_Price']))
									   {
									   $res_pr = $ilance->GPC['Reserve_Price'];
									   }
									   else
									   {
									   $res_pr = '';
									   }
									   if(isset($ilance->GPC['pend']))
									   {
									   $pend = $ilance->GPC['pend'];
									   }
									   else
									   {
									   $pend = '0';
									   }
						  $ilance->GPC['offline_order']=(isset($ilance->GPC['offline_order']) and $ilance->GPC['offline_order']==1)?1:0;
									   ($ilance->GPC['bolding']=='1')?$bold='1':$bold='0';
									 ($ilance->GPC['highlit']=='2')?$highlit='1':$highlit='0';
									 ($ilance->GPC['featurd']=='3')?$featurd='1':$featurd='0';
									 //($ilance->GPC['genuine_details']==1)?$genuine_details=1:$genuine_details=0;
									($ilance->GPC['genuine_details_o']=='1')?$genuine_details='1':$genuine_details='0';
									 ($ilance->GPC['details_o']=='1')?$details='1':$details='0';

									 ($ilance->GPC['spl_toned']=='1')?$spl_toned='1':$spl_toned='0';
									 ($ilance->GPC['hot_list']=='1')?$hot_list='1':$hot_list='0';
							    //update send cert in coin table
								$date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
								$con_insert_cointable = $ilance->db->query("
									UPDATE  " . DB_PREFIX . "coins
									SET  user_id = '" . $_POST['user_id'] . "',
									pcgs = '" . $pcg_exp['0'] . "',
									Title = '" . $ilance->db->escape_string($_POST['title']) . "',
									Description	= '" . $ilance->db->escape_string($_POST['Description']) . "',
									Grading_Service	= '" . $ilance->db->escape_string($_POST['grade_service']) . "',
									Grade = '" . $ilance->db->escape_string($_POST['Grade']) . "',
									Quantity  =  '" . $ilance->db->escape_string($_POST['Quantity']) . "',
									Max_Quantity_Purchase	=	'" . $ilance->db->escape_string($_POST['Max_Quantity_Purchase']) . "',
									Certification_No	=	'" . $ilance->db->escape_string($_POST['Certification_No']) . "',
									Condition_Attribute	=	'" . $ilance->db->escape_string($_POST['Condition_Attribute']) . "',
									Cac = '" . $_POST['Cac'] . "',
									Star ='" . $_POST['Star'] . "',
									Plus ='" . $_POST['Plus'] . "',
									Coin_Series	=	'" . $ilance->db->escape_string($_POST['coin_series']) . "',
									Pedigee	= '" . $ilance->db->escape_string($_POST['Pedigee']) . "',
									Site_Id =	'" . $_POST['site_id'] . "',
									Minimum_bid	=	'" . $min_bid . "',
									Reserve_Price		=	'" . $res_pr . "',
									Buy_it_now	=		'" . $buy_now . "',
									End_Date		=	'" . $date_coin . "',
									Alternate_inventory_No = '" . $ilance->db->escape_string($_POST['Alternate_inventory_No']) . "',
									Category	=	'" . $pcg_exp['0'] . "',
									Other_information		=	'" . $other_info_val . "',
									consignid	=	'" . $_POST['consignid'] . "', 
									coin_listed =		'c',
									in_notes = '".$ilance->GPC['inotes']."',
									
									Sets 	 = '".$ilance->GPC['Sets']."',
									
	                                nocoin   = '".$ilance->GPC['nocoin']."',
									
									pending  = '".$pend."',
									
									listing_fee = '".$ilance->db->escape_string($ilance->GPC['listingfee'])."',
									
									fvf_amount = '".$fvf."',
									
									fvf_id = '".$ilance->db->escape_string($ilance->GPC['fvffee'])."',
									norder=".$ilance->GPC['offline_order'].",
									bold = '".$bold."',
									highlite = '".$highlit."',
									featured = '".$featurd."',
									cost ='".$ilance->GPC['cost']."',
									make_invisible = '".$ilance->GPC['make_invisible']."',
									generics = '".$ilance->GPC['generic']."',
									new_release = '".$ilance->GPC['new_release']."',
									Variety = '".$ilance->GPC['Variety']."',
									genuine_details = '".$genuine_details."',
									details = '".$details."',
									QA = '".$ilance->GPC['QA']."',
									spl_toned='".$spl_toned . "',
									hot_list='".$hot_list . "'
									WHERE coin_id 	  =  '" . $_POST['coin_id'] . "'
								 ");
								 
								 
								 $coin_id_last = $_POST['coin_id'];
						 
						 
						 //for end date coin function   
							if($ilance->GPC['site_id'] == '0')
							{
								/*$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$date_coin."'");
			
								if($ilance->db->num_rows($restotal) > 0)
								{	
								//$my_var_in = fetch_date_time_coin($date_coin);
								//$my_var_in = fetch_date_time_coin($date_coin,$pcg_exp['0'],$coin_id_last);
								}*/
							}
						   if($_POST['randproid'] !='')
							{
							
							//update attachment userid and catergoryid
							/*$attach_concumer_sql = $ilance->db->query("SELECT *
													FROM " . DB_PREFIX . "attachment
													WHERE project_id = '".$_POST['randproid']."'
						
													");
													if ($ilance->db->num_rows($attach_concumer_sql) > 0)
													   {
					
							$attach_concumer = $ilance->db->query("UPDATE " . DB_PREFIX . "attachment
																  SET coin_id     = '". $_POST['coin_id']."',
																	  category_id = '".$_POST['cat']."'
																  WHERE project_id = '".$_POST['randproid']."'
																  ");
													   }*/
							}	
							   print_action_success('updated successfully saved', $ilpage['consignment']);
							   exit();
							
							}
			  }
			  
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_edit')
			{
						$show['no'] = 'update_edit';
						$offline_order='';
						$coin_id = $ilance->GPC['coin_id'];
						$con_insert = $ilance->db->query("
						SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id= '".$ilance->GPC['coin_id']."'", 0, null, __FILE__, __LINE__);

						$rescat = $ilance->db->fetch_array($con_insert);
						//$pcgs =  $rescat['pcgs'].'.'.$rescat['Grade']; //old data
						  $pcgs =  $rescat['pcgs']; //5244

						$bold_edit = ($rescat['bold'] == '1')?'checked="checked"':'';
						$high_edit = ($rescat['highlite'] == '1')?'checked="checked"':'';
						$feat_edit = ($rescat['featured'] == '1')?'checked="checked"':'';
						$spl_toned_edit = ($rescat['spl_toned'] == '1')?'checked="checked"':'';
						$hot_list_edit = ($rescat['hot_list'] == '1')?'checked="checked"':'';
						$offline_order=($rescat['norder']==1)?'checked="checked"':'';
						$inotes = $rescat['in_notes'];
						$genuine_details_edit = ($rescat['genuine_details'] == '1')?'checked="checked"':'';
				  
						  $Sets = $rescat['Sets'];
						  
						  $nocoin = $rescat['nocoin'];
						  
						  $pend = $rescat['pending'];
						  if($pend == '0')
						  $checkl = '';
						  else
						  $checkl = 'checked="checked"';
						  
						  $make_invisible =  $rescat['make_invisible'];	
                          if($make_invisible == '0')
						  $check2 = '';
						  else
						  $check2 = 'checked="checked"';	

                          $generic =  $rescat['generic'];	
                          if($generic == '0')
						  $check3 = '';
						  else
						  $check3 = 'checked="checked"';

                         $new_release =  $rescat['new_release'];	
                          if($new_release == '0')
						  $check4 = '';
						  else
						  $check4 = 'checked="checked"';						  
						  						  

						  $Title =  $rescat['Title']; 
						  $Description =  $rescat['Description'];
						  $Certification_No =  $rescat['Certification_No'];
						  $Coin_Series =  $rescat['Coin_Series']; 
						  $Minimum_bid =  $rescat['Minimum_bid']; 
						  $Reserve_Price =  $rescat['Reserve_Price']; 
						  $Buy_it_now =  $rescat['Buy_it_now']; 
						  $Alternate_inventory_No =  $rescat['Alternate_inventory_No'];
						  $Pedigee =  $rescat['Pedigee'];
						  $cat_id =  $rescat['Category'];
						  $cost =  $rescat['cost'];
						 /* if($Buy_it_now == '')
						  $onload = "hidebuy();";
						  else
						  $onload = "hidebid();";*/
						 //new jan 19
						 $sqlcat_pcg = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "catalog_coin
								WHERE PCGS = '".$rescat['pcgs']."'                        
								", 0, null, __FILE__, __LINE__);
								$rescat_pcg = $ilance->db->fetch_array($sqlcat_pcg);
								
								if(empty($rescat_pcg['coin_detail_mintmark']))
								$myvem = '';
								else
								$myverm = '-'.$rescat_pcg['coin_detail_mintmark'];
								if($rescat_pcg['coin_detail_major_variety'] == '')
								$myver = '';
								else
								$myver = ' '.$rescat_pcg['coin_detail_major_variety'];
								
								if(empty($rescat_pcg['coin_detail_suffix']))
								{
								$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
								$suff = '';
								}
								else
								{
								$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
								$suff = $rescat_pcg['coin_detail_suffix'];
								}
								
								
								//$title_eng      =  $rescat_pcg['coin_detail_year'].' '.$rescat_pcg['coin_detail_coin_series'].''.$myver; 
								  
								$pro_tes =  fetch_cat('coin_detail_proof',$rescat['pcgs']);
								if($pro_tes == 'y')
								$text_pro = 'Proof-'.$rescat['Grade'];
								else if($pro_tes == 's')
								$text_pro = 'Specimen-'.$rescat['Grade'];
								else
								$text_pro = $pro_pc.''.$rescat['Grade'];
								
								   $upda_val = '<input type="hidden" name="suff" value="'.$suff.'" id="suff">
								   
								        <input type="hidden"  value="'.$title_eng.'" id="cat_tit_p">
									  <input type="hidden"  value="" id="nbgrade"> <input type="hidden" value="'.$text_pro .'" id="grad_pro">';
						        $editcat = fetch_cat_title('title_eng',$rescat['Category']);
			 
						 //cac and qa and star and plus selected
						if($rescat['genuine_details'] == '1')
						 $seleted_genuine = '<input type="radio" name="genuine_details_o" id="genuine_details_o" value="1" onclick="grade(\'Genuine\')" checked="checked"/>Yes<input type="radio" name="genuine_details_o" id="genuine_details_o_n" value="0"  onclick="grade(\'0\')"/>No';
						 else 
						 $seleted_genuine = '<input type="radio" name="genuine_details_o" id="genuine_details_o" value="1" onclick="grade(\'Genuine\')" />Yes<input type="radio" name="genuine_details_o" id="genuine_details_o_n" value="0" checked="checked" onclick="grade(\'0\')"/>No';
						if($rescat['details'] == '1')
						 $seleted_details = '<input type="radio" name="details_o" id="details_o" value="1" onclick="grade(\'Details\')" checked="checked"/>Yes<input type="radio" name="details_o" id="details_o_n" value="0"  onclick="grade(\'0\')"/>No';
						 else 
						 $seleted_details = '<input type="radio" name="details_o" id="details_o" value="1" onclick="grade(\'Details\')" />Yes<input type="radio" name="details_o" id="details_o_n" value="0" checked="checked" onclick="grade(\'0\')"/>No';
						 
						  if($rescat['Cac'] == '1')
						 $seleted = '<input type="radio" name="Cac" id="Cac" value="1" onclick="grade(\'CAC\')" checked="checked"/>Yes<input type="radio" name="Cac" id="Cacn" value="0"  onclick="grade(\'0\')"/>No';
						 else 
						 $seleted = '<input type="radio" name="Cac" id="Cac" value="1" onclick="grade(\'CAC\')" />Yes<input type="radio" name="Cac" id="Cacn" value="0" checked="checked" onclick="grade(\'0\')"/>No';	
						 if($rescat['QA'] == '1')
						 $seleted_qa = '<input type="radio" name="QA" id="QA" value="1" onclick="grade(\'QA\')" checked="checked"/>Yes<input type="radio" name="QA" id="Qan" value="0"  onclick="grade(\'0\')"/>No';
						 else 
						 $seleted_qa = '<input type="radio" name="QA" id="QA" value="1" onclick="grade(\'QA\')" />Yes<input type="radio" name="QA" id="Qan" value="0" checked="checked" onclick="grade(\'0\')"/>No';	
						 
						 if($rescat['Star'] == '1')
						 $seleted_star = '<input type="radio" name="Star" id="Star" value="1" checked="checked" onclick="grade(\'STAR\')"/>Yes<input type="radio" name="Star" id="Starn" value="0" onclick="grade(\'0\')"/>No';
						 else
						 $seleted_star = '<input type="radio" name="Star" id="Star" value="1"  onclick="grade(\'STAR\')"/>Yes<input type="radio" name="Star" id="Starn" value="0" checked="checked" onclick="grade(\'0\')"/>No';		
						 if($rescat['Plus'] == '1')
						 $seleted_plus = '<input type="radio" name="Plus" id="Plus" value="1" checked="checked" onclick="grade(\'PLUS\')"/>Yes<input type="radio" name="Plus" id="Plusn" value="0"  onclick="grade(\'0\')"/>No';
						 else
						 $seleted_plus = '<input type="radio" name="Plus" id="Plus" value="1"  onclick="grade(\'PLUS\')"/>Yes<input type="radio" name="Plus" id="Plusn" checked="checked" value="0"  onclick="grade(\'0\')"/>No';	
						 
						 if($rescat['Variety'] == '1')
						 $seleted_Variety = '<input type="radio" name="Variety" id="Variety" value="1" checked="checked" onclick="grade(\'Variety\')"/>Yes<input type="radio" name="Variety" id="Variety" value="0"  onclick="grade(\'0\')"/>No';
						 else
						 $seleted_Variety = '<input type="radio" name="Variety" id="Variety" value="1"  onclick="grade(\'Variety\')"/>Yes<input type="radio" name="Variety" id="Variety" checked="checked" value="0"  onclick="grade(\'0\')"/>No';	
							
						$dataexplode = explode('-', $rescat['End_Date']);
						
						$daylist_r ='<select name="day" id="day"><option value="">DATE</option>';
						
						
						for($i=1; $i<=31; $i++)
						if($dataexplode[2] == $i)
						$daylist_r .= "<option value='$i' selected>$i</option>";
						else
						$daylist_r .= "<option value='$i'>$i</option>";
						
						$daylist_r .='</select>';
						
						$monthlist_r ='<select name="month" id="month"><option value="">MONTH</option>';
						
						
						for($j=1; $j<=12; $j++)
						
						if($dataexplode[1] == $j)
						$monthlist_r .= "<option value='$j' selected>$j</option>";
						else
						$monthlist_r .= "<option value='$j'>$j</option>";
						
						
						$monthlist_r .= '</select>';
						
						$yearlist_r = '<select name="year" id="year"><option value="">YEAR</option>';
						
					
						for($k=date("Y"); $k<=date("Y")+5; $k++)
						if($dataexplode[0] == $k)
						$yearlist_r .= "<option value='$k' selected>$k</option>";
						else
						$yearlist_r .= "<option value='$k'>$k</option>";
						
						$yearlist_r .='</select>';
						//other information update
						
					   $listarray = explode(',', $rescat['Other_information']);
					  
					  $w=1;
					  foreach($listarray as $text)
					  {
					  $data[$w]= $text;
					  $w++;
					  }
					 
						   
						   
						$sel_catlist_table = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "coin_other_details
								
								", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sel_catlist_table) > 0)
						{
						
						$other_mul_select = '<select name="other_information[]" id="other_information" multiple="multiple" style="width: 200px; height: 100px;" onchange="grade(this.value);">';
						
						while($row_catlist_table =  $ilance->db->fetch_array($sel_catlist_table))
										   {
										   
										   if(array_search($row_catlist_table['other_details'],$data))
										   {
										   //echo $row_catlist_table['other_details'];
										   $selected_list = 'selected="selected"';
										   }
										   else
										   {
										   $selected_list = '';
										   }
										 $other_mul_select.= '<option value="'.$row_catlist_table['other_details'].'"  '.$selected_list.'>'.$row_catlist_table['other_details'].'</option>';              }
						$other_mul_select.= '</select>';	
									   
						}
						//grade service update
						$grade_service_update = '<select name="grade_service" id="grade_service" onchange="grade(this.value);">
					   <option value="">Select</option>';
						 $sqlcat_gr = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "grading_service
						", 0, null, __FILE__, __LINE__);
						 if ($ilance->db->num_rows($sqlcat_gr) > 0)
						 {         
							
								while ($rescat_s = $ilance->db->fetch_array($sqlcat_gr))
								{
								
								
											if($rescat_s['grading'] == $rescat['Grading_Service'])
											$grade_service_update.='<option value="'.$rescat_s['grading'].'" selected="selected">'.$rescat_s['grading'].'</option>';
											else
											$grade_service_update.='<option value="'.$rescat_s['grading'].'">'.$rescat_s['grading'].'</option>';
								}
								
						}
					
					 $grade_service_update.='</select>';	
					
					 //Condition Attribute update
						$con_update = '<select onchange="grade(this.value);" style="font-family: Verdana;" name="Condition_Attribute" id="Condition_Attribute"><option value="">Select</option>
					   ';
						 $sqlcat_at = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "coin_condition
						", 0, null, __FILE__, __LINE__);
						 if ($ilance->db->num_rows($sqlcat_at) > 0)
						 {         
							
								while ($rescat_a = $ilance->db->fetch_array($sqlcat_at))
								{
								
								
											if($rescat_a['coincondition'] == $rescat['Condition_Attribute'])
											$con_update.='<option value="'.$rescat_a['coincondition'].'" selected="selected">'.$rescat_a['coincondition'].'</option>';
											else
											$con_update.='<option value="'.$rescat_a['coincondition'].'">'.$rescat_a['coincondition'].'</option>';
								}
								
						}
					
					 $con_update.='</select>';	
                    
					 //suku
					 
					 $list_id_up=getif_drop_down('listingfee','listingfee',$rescat['listing_fee']);
					// site id
					$site_id_up = '<select name="site_id" id="site_id" >';
					$sqlcat_siteid = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "affiliate_listing
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
					 {         
							if($rescat['Site_Id'] == '0')
							{
							$site_id_up.='<option value="0" selected="selected">Listed in GC</option>';
							while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
							{                       
							 
							$site_id_up.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';	
													
										
							}
							}
							else
							{
							$site_id_up.='<option value="0" >Listed in GC</option>';
							while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
							{  
								if($rescat_sid['id'] == $rescat['Site_Id'])
								$site_id_up.='<option value="'.$rescat_sid['id'].'" selected="selected">'.$rescat_sid['site_name'].'</option>';
								else
								$site_id_up.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';	
													
										
							}
							}
							
					}
				
					$site_id_up.='</select>';			
			 $user_id = $ilance->GPC['user_id'];   
					// // can user attach media?
			 $ilance->subscription = construct_object('api.subscription');  
			 $ilance->subscription->check_access($user_id, 'uploadlimit');
			 $attachment_style = ($ilance->subscription->check_access($user_id, 'attachments') == 'no') ? 'disabled="disabled"' : '';
			
			//rand value for projectid and projectid change to varchar for search (herakle kkk)
		    $rand_value_edit = $ilance->GPC['pro'];
  
	        $pro = $ilance->GPC['pro'];
			
	         // fetch product photo attachments uploaded 
		  
	     
			 // item photo upload button 
			 $hiddeninput = array(
					'attachtype' => 'itemphoto',
					'project_id' => $pro,
					'user_id' => $user_id,
					'category_id' => $rescat['Category'],
					'filehash' => md5(time()),
					'max_filesize' => $ilance->subscription->check_access($user_id, 'uploadlimit'),
					'attachmentlist' => 'itemphoto_attachmentlist'
			 );                
	         $uploadproductbutton_edit = '<input name="attachment" onclick=Attach("/gc/' . $ilpage['upload'] . '?userid='.$user_id.'&crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' .    $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
	unset($hiddeninput);
	
		
	        //slideshow upload button 
			 $hiddeninput = array(
				'attachtype' => 'slideshow',
				'project_id' => $pro,
				'user_id' => $user_id,
				'category_id' => $rescat['Category'],
				'filehash' => md5(time()),
				'max_filesize' => $ilance->subscription->check_access($user_id, 'uploadlimit'),
				'attachmentlist' => 'slideshow_attachmentlist'
			 );                
		     
		     // slideshow button  
			 $attachment_style = ($ilance->subscription->check_access($user_id, 'attachments') == 'yes') ? '' : 'disabled="disabled"';
			 $uploadbutton_edit = '<input name="attachment" onclick=Attach("/gc/' . $ilpage['upload'] . '?userid='.$user_id.'&crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' .    $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
unset($hiddeninput);
           // fetch slideshow attachments uploaded 
		   $slideshow_attachmentlist_edit = fetch_inline_attachment_filelist($user_id, $pro, 'slideshow', true);
		
		   // fetch product photo attachments uploaded 
		  $itemphoto_attachmentlist_edit = fetch_inline_attachment_filelist($user_id, $pro, 'itemphoto', true);
				   //Grade
					$grade_create_r = '';
					for($i=0; $i<101;$i++)
					{
					 if($rescat['Grade'] == $i)
					 $grade_create_r .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
					 else
					 $grade_create_r .= '<option value="'.$i.'">'.$i.'</option>';		
					}
					//quantity
					$quantity_r = '';
					for($q=0; $q<101;$q++)
					{
					if($rescat['Quantity'] == $q)
					$quantity_r .= '<option value="'.$q.'" selected="selected">'.$q.'</option>';
					else	
					$quantity_r .= '<option value="'.$q.'" >'.$q.'</option>';	
					}
					//Max_Quantity_Purchase 
					$Max_Quantity_Purchase_r = '';
					for($m=0; $m<101;$m++)
					{
					if($rescat['Max_Quantity_Purchase'] == $m)
					$Max_Quantity_Purchase_r .= '<option value="'.$m.'" selected="selected">'.$m.'</option>';
					else	
					$Max_Quantity_Purchase_r .= '<option value="'.$m.'" >'.$m.'</option>';		
					}
					
					//Pedigee update
						$Ped_update = '<select name="Pedigee" style="font-family: Verdana">
					   <option value="">Select</option>';
						 $sqlcat_p = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "pedigree
						", 0, null, __FILE__, __LINE__);
						 if ($ilance->db->num_rows($sqlcat_p) > 0)
						 {         
							
								while ($rescat_p = $ilance->db->fetch_array($sqlcat_p))
								{
								
								
											if($rescat_p['pedigree'] == $rescat['Pedigee'])
											$Ped_update.='<option value="'.$rescat_p['pedigree'].'" selected="selected">'.$rescat_p['pedigree'].'</option>';
											else
											$Ped_update.='<option value="'.$rescat_p['pedigree'].'">'.$rescat_p['pedigree'].'</option>';
								}
								
						}
					
					 $Ped_update.='</select>';	
					 
					   //fvf fees
					$fvf_id_edit = getfvf_drop_down('fvffee','fvffee',$rescat['fvf_id']);
					 
					 
					//coin proof grade
			       $pro_grade_edit  = '<select name="Grade" id="Grade" onchange="newgrade(this.value,document.getElementById(\'pcgs\').value);"><option value="">Select</option>';
				  
					$sqlcat_p_g = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "coin_proof ORDER BY id DESC
					", 0, null, __FILE__, __LINE__);
					 
					 if ($ilance->db->num_rows($sqlcat_p_g) > 0)
					 {         
						
							while ($rescat_p_g = $ilance->db->fetch_array($sqlcat_p_g))
							{
								if($rescat_p_g['value'] == $rescat['Grade'])
								$pro_grade_edit.='<option value="'.$rescat_p_g['value'].'" selected="selected">'.$rescat_p_g['value'].'</option>';
								else
								$pro_grade_edit.='<option value="'.$rescat_p_g['value'].'">'.$rescat_p_g['value'].'</option>';
							}
							
					}
				
			$pro_grade_edit.='</select>';
			  
			  }	
				  
			//con date default
			
			//Tamil for bug 2416 * Start 

			$con_date_end = $ilance->db->query("
								SELECT date(c.end_date) as tese,c.fvf_id,u.house_account 
								FROM " . DB_PREFIX . "consignments c
								join " . DB_PREFIX . "users u on u.user_id = c.user_id
								WHERE c.user_id='".$ilance->GPC['user_id']."' 
								AND c.consignid ='".$ilance->GPC['consignid']."'
								", 0, null, __FILE__, __LINE__);
								
			$row_table_end = $ilance->db->fetch_array($con_date_end);
			
			$house_account_check=$row_table_end['house_account'];
			//add $house_account_check to $pprint_array
			
			//Tamil for bug 2416 * End
			
	        $dataex = explode('-', $row_table_end['tese']);

			//############################## Start Bug ID #8438 by Aiyappan ################################//

			//fvf fees
			$final_value_fee = $ilance->db->query("
				SELECT final_value_fee FROM " . DB_PREFIX . "users 
				WHERE user_id = '" . $ilance->GPC['user_id'] . "'
				AND final_value_fee != ''
			", 0, null, __FILE__, __LINE__);

			if ($ilance->db->num_rows($final_value_fee) > 0)
			{
				$res = $ilance->db->fetch_array($final_value_fee);

				if ($res['final_value_fee'] != '')
				{
					$fvf_id = getfvf_drop_down('fvffee','fvffee',$res['final_value_fee']);
				}
				else
				{
					$fvf_id = getfvf_drop_down('fvffee','fvffee',$row_table_end['fvf_id']);
				}
			}
			else
			{
				$fvf_id = getfvf_drop_down('fvffee','fvffee',$row_table_end['fvf_id']);
			}

			//############################## End Bug ID #8438 by Aiyappan ################################//
			
			 
			 
			//date function
			$daylist = '';
			$monthlist = '';
			$yearlist = '';
			$daylist .='<select name="day" id="day"><option value="">DATE</option>';
			
			$day = date('d');
			for($i=1; $i<=31; $i++)
			if($dataex[2] == $i)
			$daylist .= "<option value='$i' selected>$i</option>";
			else
			$daylist .= "<option value='$i'>$i</option>";
			$daylist .='</select>';
			
			$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
			
			$month = date('m');
			for($j=1; $j<=12; $j++)
			if($dataex[1] == $j)
			$monthlist .= "<option value='$j' selected>$j</option>";
			else
			$monthlist .= "<option value='$j'>$j</option>";
			$monthlist .= '</select>';
			
			$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
			
			$year = date('Y');;
			for($k=date("Y"); $k<=date("Y")+5; $k++)
			if($dataex[0] == $k)
			$yearlist .= "<option value='$k' selected>$k</option>";
			else
			$yearlist .= "<option value='$k'>$k</option>";
			$yearlist .='</select>';
		  
			// site id
		    $site_id = '<select name="site_id" id="site_id" >
		    <option value="0" selected="selected">Listed in GC</option>';
  
		    $sqlcat_siteid = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "affiliate_listing
			", 0, null, __FILE__, __LINE__);
			 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
			 {         
				
					while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
					{
								
								
											$site_id.='<option value="'.$rescat_sid['id'].'" >'.$rescat_sid['site_name'].'</option>';
												
								
					}
					
			}
		
			$site_id.='</select>';
		
			//Condition Attribute
			$con_attri = '<select id="Condition_Attribute" name="Condition_Attribute" style="font-family: Verdana" onchange="grade(this.value);"><option value="" selected="selected">Select</option>';
					$sqlcat_att = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "coin_condition
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_att) > 0)
					 {         
						
							while ($rescat_att = $ilance->db->fetch_array($sqlcat_att))
							{
											
										$con_attri.='<option value="'.$rescat_att['coincondition'].'">'.$rescat_att['coincondition'].'</option>';
							}
							
					}
			$con_attri.='</select>';
			//pedigree
			$con_pedi = '<select name="Pedigee" style="font-family: Verdana"><option value="" selected="selected">Select</option>';
					$sqlcat_con_pedi = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "pedigree
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_con_pedi) > 0)
					 {         
						
							while ($rescat_pedi = $ilance->db->fetch_array($sqlcat_con_pedi))
							{
											
										$con_pedi.='<option value="'.$rescat_pedi['pedigree'].'">'.$rescat_pedi['pedigree'].'</option>';
							}
							
					}
			$con_pedi.='</select>';
			//other inform
			$con_pedi_oth = '<select name="other_information[]" id="other_information" multiple="multiple" style="width: 200px; height: 100px;" onchange="grade(this.value);">
				   ';
					 $sqlcat_pedi_oth = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "coin_other_details
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_pedi_oth) > 0)
					 {         
						
							while ($rescat_pedi_oth = $ilance->db->fetch_array($sqlcat_pedi_oth))
							{
											
										$con_pedi_oth.='<option value="'.$rescat_pedi_oth['other_details'].'">'.$rescat_pedi_oth['other_details'].'</option>';
							}
							
					}
				
			$con_pedi_oth.='</select>';
			
			//grade
			$grade_service = '<select name="grade_service" id="grade_service" onchange="grade(this.value);">
				  <option value="" selected="selected">Select</option>';
					 $sqlcat = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "grading_service
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat) > 0)
					 {         
						
							while ($rescat = $ilance->db->fetch_array($sqlcat))
							{
											
										$grade_service.='<option value="'.$rescat['grading'].'">'.$rescat['grading'].'</option>';
							}
							
					}
				
			$grade_service.='</select>';
			$grade_create = '';
			for($i=0; $i<101;$i++)
			{
			$grade_create .= '<option value="'.$i.'">'.$i.'</option>';
					
			}
			//quantity
			$quantity = '';
			for($q=1; $q<101;$q++)
			{
			$quantity .= '<option value="'.$q.'">'.$q.'</option>';
					
			}
			//Max_Quantity_Purchase
			$Max_Quantity_Purchase = '';
			for($m=1; $m<101;$m++)
			{
			$Max_Quantity_Purchase .= '<option value="'.$m.'">'.$m.'</option>';
					
			}
			//coin proof grade
			$pro_grade  = '<select name="Grade" id="Grade" onchange="newgrade(this.value,document.getElementById(\'pcgs\').value);">
				 <option value="" selected="selected">Select</option> ';
					 $sqlcat_p_g = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "coin_proof ORDER BY id DESC
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_p_g) > 0)
					 {         
						
							while ($rescat_p_g = $ilance->db->fetch_array($sqlcat_p_g))
							{
											
										$pro_grade.='<option value="'.$rescat_p_g['value'].'">'.$rescat_p_g['value'].'</option>';
							}
							
					}
				
			$pro_grade.='</select>';
					
			
									
			 $user_id = $ilance->GPC['user_id'];
		     $consignid = $ilance->GPC['consignid'];
			
			    
			$con_i = $ilance->db->query("
												SELECT * FROM " . DB_PREFIX . "consignments WHERE consignid= '".$ilance->GPC['consignid']."' and user_id='".$ilance->GPC['user_id']."'", 0, null, __FILE__, __LINE__);
												
			 $re = $ilance->db->fetch_array($con_i);
			 
			 //############################## Start Bug ID #8438 by Aiyappan ################################//

			$listing_fee = $ilance->db->query("
				SELECT listing_fee FROM " . DB_PREFIX . "users 
				WHERE user_id = '" . $ilance->GPC['user_id'] . "'
				AND listing_fee != ''
			", 0, null, __FILE__, __LINE__);

			if ($ilance->db->num_rows($listing_fee) > 0)
			{
				$res11 = $ilance->db->fetch_array($listing_fee);

				if ($res11['listing_fee'] != '')
				{
					$list_id = getif_drop_down('listingfee','listingfee',$res11['listing_fee']);
				}
				else
				{
					$list_id = getif_drop_down('listingfee','listingfee',$re['listing_fee']);
				}
			}
			else
			{
				$list_id = getif_drop_down('listingfee','listingfee',$re['listing_fee']);
			}

			//############################## End Bug ID #8438 by Aiyappan ################################//
               
						
			  $pe = $re['pending'];
			  if($pe== '0')
			  $checks = '';
			  else
			  $checks = 'checked="checked"';
	         // can user attach media?
			 $ilance->subscription = construct_object('api.subscription');  
			 $ilance->subscription->check_access($user_id, 'uploadlimit');
			 $attachment_style = ($ilance->subscription->check_access($user_id, 'attachments') == 'no') ? 'disabled="disabled"' : '';
			
			//rand value for projectid and projectid change to varchar for search (herakle kkk)
		     $rand_value = substr(md5(uniqid(rand(), true)),0,6);
			 // item photo upload button 
			 $hiddeninput = array(
					'attachtype' => 'itemphoto',
					'project_id' => $rand_value,
					'user_id' => $user_id,
					'category_id' => '3',
					'filehash' => md5(time()),
					'max_filesize' => $ilance->subscription->check_access($user_id, 'uploadlimit'),
					'attachmentlist' => 'itemphoto_attachmentlist'
			 );                
	         $uploadproductbutton = '<input name="attachment" onclick=Attach("/gc/' . $ilpage['upload'] . '?userid='.$user_id.'&crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' .    $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
	unset($hiddeninput);
	
		
	        //slideshow upload button 
			 $hiddeninput = array(
				'attachtype' => 'slideshow',
				'project_id' => $rand_value,
				'user_id' => $user_id,
				'category_id' => '4',
				'filehash' => md5(time()),
				'max_filesize' => $ilance->subscription->check_access($user_id, 'uploadlimit'),
				'attachmentlist' => 'slideshow_attachmentlist'
			 );                
		     
		     //slideshow button  
			 $attachment_style = ($ilance->subscription->check_access($user_id, 'attachments') == 'yes') ? '' : 'disabled="disabled"';
			// $uploadbutton = '<input name="attachment" onclick=Attach("/gc/' . $ilpage['upload'] . '?userid='.$user_id.'&crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' .    $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
			
			$uploadbutton = '<input name="attachment" onclick=Attach("' . HTTP_SERVER . $ilpage['upload'] . '?userid='.$user_id.'&crypted=' . encrypt_url(array('attachtype' => 'slideshow', 'project_id' => $rand_value, 'user_id' => $user_id, 'category_id' => '3', 'filehash' => md5(time()), 'max_filesize' => $ilance->subscription->check_access($user_id, 'uploadlimit'))) . '") type="button" value="' . $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
			 unset($hiddeninput);
			// fetch slideshow attachments uploaded 

			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'coin_search' AND !empty($ilance->GPC['coin_id']))
			{	
				//echo '<pre>';print_r($ilance->GPC);exit;
				$searched_coinid = $ilance->GPC['coin_id'];
				$csql = $ilance->db->query(" SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id= '".$ilance->GPC['coin_id']."'", 0, null, __FILE__, __LINE__);
				//Title,Grading_Service,Grade,Certification_No,Coin_Series,Minimum_bid,Buy_it_now,Alternate_inventory_No,Category
				if ($ilance->db->num_rows($csql) > 0)
				{	

					$coins_det = $ilance->db->fetch_array($csql);
					//echo '<pre>';print_r($coins_det);exit;
					$pcgs = $coins_det['pcgs'];
					$title = $coins_det['Title'];

					$selected_array = explode(' ', $title);
					//echo '<pre>';print_r($selected_array);exit;

					$grading_service = $coins_det['Grading_Service'];
					$grade = $coins_det['Grade'];
					$certification_no = $coins_det['Certification_No'];
					$minimum_bid = ($coins_det['Minimum_bid']>0)?$coins_det['Minimum_bid']:'';
					$buy_now = ($coins_det['Buy_it_now']>0)?$coins_det['Buy_it_now']:'';
					$alternate_inventory_no = $coins_det['Alternate_inventory_No'];
					$cat = $coins_det['Category'];
					$category = $editcat = fetch_cat_title('title_eng',$coins_det['Category']);
					$coin_series = $coins_det['Coin_Series'];


					$sqlcat_pcg = $ilance->db->query(" SELECT * FROM " . DB_PREFIX . "catalog_coin
								WHERE PCGS = '".$coins_det['pcgs']."' ", 0, null, __FILE__, __LINE__);
					$rescat_pcg = $ilance->db->fetch_array($sqlcat_pcg);
					$year_of_pcgs = $rescat_pcg['coin_detail_year'];
					$coin_detail_coin_series = $rescat_pcg['coin_detail_coin_series'];

					if($coins_det['genuine_details'] == '1')
						$seleted_genuine = '<input type="radio" name="genuine_details_o" id="genuine_details_o" value="1" onclick="grade(\'Genuine\')" checked="checked"/>Yes<input type="radio" name="genuine_details_o" id="genuine_details_o_n" value="0"  onclick="grade(\'0\')"/>No';
					else 
						$seleted_genuine = '<input type="radio" name="genuine_details_o" id="genuine_details_o" value="1" onclick="grade(\'Genuine\')" />Yes<input type="radio" name="genuine_details_o" id="genuine_details_o_n" value="0" checked="checked" onclick="grade(\'0\')"/>No';
					if($coins_det['details'] == '1')
					$seleted_details = '<input type="radio" name="details_o" id="details_o" value="1" onclick="grade(\'Details\')" checked="checked"/>Yes<input type="radio" name="details_o" id="details_o_n" value="0"  onclick="grade(\'0\')"/>No';
					else 
					$seleted_details = '<input type="radio" name="details_o" id="details_o" value="1" onclick="grade(\'Details\')" />Yes<input type="radio" name="details_o" id="details_o_n" value="0" checked="checked" onclick="grade(\'0\')"/>No';
					if($coins_det['Cac'] == '1')
					$seleted = '<input type="radio" name="Cac" id="Cac" value="1" onclick="grade(\'CAC\')" checked="checked"/>Yes<input type="radio" name="Cac" id="Cacn" value="0"  onclick="grade(\'0\')"/>No';
					else 
					$seleted = '<input type="radio" name="Cac" id="Cac" value="1" onclick="grade(\'CAC\')" />Yes<input type="radio" name="Cac" id="Cacn" value="0" checked="checked" onclick="grade(\'0\')"/>No';
					if($coins_det['QA'] == '1')
					$seleted_qa = '<input type="radio" name="QA" id="QA" value="1" onclick="grade(\'QA\')" checked="checked"/>Yes<input type="radio" name="QA" id="Qan" value="0"  onclick="grade(\'0\')"/>No';
					else 
					$seleted_qa = '<input type="radio" name="QA" id="QA" value="1" onclick="grade(\'QA\')" />Yes<input type="radio" name="QA" id="Qan" value="0" checked="checked" onclick="grade(\'0\')"/>No';	
					if($coins_det['Star'] == '1')
					$seleted_star = '<input type="radio" name="Star" id="Star" value="1" checked="checked" onclick="grade(\'STAR\')"/>Yes<input type="radio" name="Star" id="Starn" value="0" onclick="grade(\'0\')"/>No';
					else
					$seleted_star = '<input type="radio" name="Star" id="Star" value="1"  onclick="grade(\'STAR\')"/>Yes<input type="radio" name="Star" id="Starn" value="0" checked="checked" onclick="grade(\'0\')"/>No';		
					if($coins_det['Plus'] == '1')
					$seleted_plus = '<input type="radio" name="Plus" id="Plus" value="1" checked="checked" onclick="grade(\'PLUS\')"/>Yes<input type="radio" name="Plus" id="Plusn" value="0"  onclick="grade(\'0\')"/>No';
					else
					$seleted_plus = '<input type="radio" name="Plus" id="Plus" value="1"  onclick="grade(\'PLUS\')"/>Yes<input type="radio" name="Plus" id="Plusn" checked="checked" value="0"  onclick="grade(\'0\')"/>No';	
					//$ = $coins_det[''];

					if($coins_det['Variety'] == '1')
						$seleted_Variety = '<input type="radio" name="Variety" id="Variety" value="1" checked="checked" onclick="grade(\'Variety\')"/>Yes<input type="radio" name="Variety" id="Variety" value="0"  onclick="grade(\'0\')"/>No';
					else
						$seleted_Variety = '<input type="radio" name="Variety" id="Variety" value="1"  onclick="grade(\'Variety\')"/>Yes<input type="radio" name="Variety" id="Variety" checked="checked" value="0"  onclick="grade(\'0\')"/>No';	



					$sqlcat_pcg = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "catalog_coin
								WHERE PCGS = '".$coins_det['pcgs']."'                        
								");
					$rescat_pcg = $ilance->db->fetch_array($sqlcat_pcg);
					$suffx = $rescat_pcg['coin_detail_suffix'];
					$coin_ser_val = $rescat_pcg['coin_detail_coin_series'];


					$pro_tes =  fetch_cat('coin_detail_proof',$coins_det['pcgs']);
					$pcgdiv = '';
					if($pro_tes == 'y')
						$text_pro = 'Proof-'.$pc_proof;
					else if($pro_tes == 's')
						$text_pro = 'Specimen-'.$pc_proof;
					else
						$text_pro = $pro_pc.''.$pc_proof;


					if($rescat_pcg['coin_detail_major_variety'] == '')
						$myver = '';
					else
						$myver = ' '.$rescat_pcg['coin_detail_major_variety'];
								
								
					if(empty($rescat_pcg['coin_detail_mintmark']))
						$myvem = '';
					else
						$myverm = '-'.$rescat_pcg['coin_detail_mintmark'];
								
					if($uc >0 && $rescat_pcg['coin_detail_suffix'] == 'DCAM')
						$suff = 'UC';
					else
						$suff = $rescat_pcg['coin_detail_suffix'];

					if ((in_array("d", $os)) OR (in_array("D", $os) )) 
					{
						
						$title_enngg = 'D';
						$pcgdiv .= '<input type="hidden"  value="'.$title_enngg.'" id="cat_tit_proo">';
						   $title_details = ' Details';
					}
					else
					{
						 $title_enngg = '';
						$pcgdiv .= '<input type="hidden"  value="'.$title_enngg.'" id="cat_tit_proo">';
						   $title_details = '';
					}
								
					//if(empty($rescat_pcg['coin_detail_suffix']))
						//$title  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver.''.$mypert1.' '.$text_pro.''.$title_details;
					//else
						//$title  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver.''.$mypert1.' '.$text_pro.' '.$suff.''.$title_details;

					if(empty($rescat_pcg['coin_detail_suffix']))
					{
						$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
						$suff = '';
					}
					else
					{
						$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
	
						if($uc > 0&& $rescat_pcg['coin_detail_suffix'] == 'DCAM')
							$suff = 'UC';
						else
							$suff = $rescat_pcg['coin_detail_suffix'];
					}
								

					if(empty($rescat_pcg['coin_detail_mintmark']))
						$title_engg      =  $rescat_pcg['coin_detail_year'].' '.$rescat_pcg['coin_detail_coin_series'];
					else
						$title_engg      =  $rescat_pcg['coin_detail_year'].'-'.$rescat_pcg['coin_detail_mintmark'].' '.$rescat_pcg['coin_detail_coin_series'];													
						$coin_series    =  $rescat_pcg['coin_detail_coin_series'];
						$des            =  $rescat_pcg['coin_detail_year'].' '.$rescat_pcg['coin_detail_coin_series'].' '.$grad.' '.$rescat_pcg['coin_detail_suffix'].' '.$rescat_pcg['coin_detail_major_variety'].' '.$rescat_pcg['coin_detail_die_variety'];
								
								
					$pcgdiv .= '<input type="hidden" name="mypert" value="'.$mypert.'" id="mypert">
								<input type="hidden" name="suff" value="'.$suff.'" id="suff">
					
								<input type="hidden" name="cat" value="'.$pcg_exp['0'].'" id="cat">
								<input type="hidden"  value="'.$title_engg.'" id="cat_tit_pro">

								<input type="hidden"  value="'.$title_eng.'" id="cat_tit_p">
								<input type="hidden" value="'.$title.'" id="tit_pro">
								<input type="hidden" value="'.$coin_series.'" id="coin_ser_val">
								<input type="hidden" value="'.$des.'" id="des_cval">
								<input type="hidden" value="'.$grad .'" id="grad_pro">
								<input type="hidden"  value="" id="nbgrade">';

























					//echo $suffx;
//echo '<pre>';print_r($rescat_pcg);exit;
					$pro_grade  = '<select name="Grade" id="Grade" onchange="newgrade(this.value,document.getElementById(\'pcgs\').value);"><option value="">Select</option>';
				  
					$sqlcat_p_g = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "coin_proof ORDER BY id DESC
					", 0, null, __FILE__, __LINE__);
					 
					 if ($ilance->db->num_rows($sqlcat_p_g) > 0)
					 {         
						
							while ($rescat_p_g = $ilance->db->fetch_array($sqlcat_p_g))
							{
								if($rescat_p_g['value'] == $coins_det['Grade'])
								$pro_grade.='<option value="'.$rescat_p_g['value'].'" selected="selected">'.$rescat_p_g['value'].'</option>';
								else
								$pro_grade.='<option value="'.$rescat_p_g['value'].'">'.$rescat_p_g['value'].'</option>';
							}
							
					}
				
					$pro_grade.='</select>';

					$pro_ng =  '<input type="hidden"  value="'.$coins_det['Grade'].'" id="gra_id">'; 

					//grade service update
					$grade_service = '<select id="grade_service" onchange="grade(this.value);" name="grade_service">
				   <option value="">select</option>';
					 $sqlcat_gr = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "grading_service
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_gr) > 0)
					 {         
						
							while ($rescat = $ilance->db->fetch_array($sqlcat_gr))
							{
								if($rescat['grading'] == $coins_det['Grading_Service'])
								$grade_service.='<option value="'.$rescat['grading'].'" selected="selected">'.$rescat['grading'].'</option>';
								else
								$grade_service.='<option value="'.$rescat['grading'].'">'.$rescat['grading'].'</option>';
							}
							
					}
				
				 $grade_service.='</select>';


				 $listarray = explode(',', $coins_det['Other_information']);
					  
					$w=1;
					foreach($listarray as $text)
					{
						$data[$w]= $text;
						$w++;
					}

				 $sel_catlist_table = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "coin_other_details
								
								", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sel_catlist_table) > 0)
						{
						
							$con_pedi_oth = '<select name="other_information[]" id="other_information" multiple="multiple" style="width: 200px; height: 100px;" onchange="grade(this.value);">';
							
							while($row_catlist_table =  $ilance->db->fetch_array($sel_catlist_table))
							{
											   
								if(array_search($row_catlist_table['other_details'],$data))
								{
									//echo $row_catlist_table['other_details'];
									$selected_list = 'selected="selected"';
								}
								else
								{
									$selected_list = '';
								}
											 $con_pedi_oth.= '<option value="'.$row_catlist_table['other_details'].'"  '.$selected_list.'>'.$row_catlist_table['other_details'].'</option>';              
							}
							
							$con_pedi_oth.= '</select>';	
									   
						}
			  
			 
				}

				//Condition Attribute update
				$con_attri = '<select onchange="grade(this.value);" style="font-family: Verdana;" name="Condition_Attribute" id="Condition_Attribute"><option value="">Select</option>
					   ';
				$sqlcat_at = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "coin_condition
					", 0, null, __FILE__, __LINE__);
				 if ($ilance->db->num_rows($sqlcat_at) > 0)
				 {         
						while ($rescat_a = $ilance->db->fetch_array($sqlcat_at))
						{
							if($rescat_a['coincondition'] == $coins_det['Condition_Attribute'])
								$con_attri.='<option value="'.$rescat_a['coincondition'].'" selected="selected">'.$rescat_a['coincondition'].'</option>';
							else
								$con_attri.='<option value="'.$rescat_a['coincondition'].'">'.$rescat_a['coincondition'].'</option>';
						}
						
				}
			
			 	$con_attri.='</select>';	

			 	$Sets = $coins_det['Sets'];
			 	$SetCoins = $coins_det['nocoin'];
			 	$Description = $coins_det['Description'];

			 	$generic =  $coins_det['generics'];	
	              if($generic == '0')
				  	$check3 = '';
				  else
				  	$check3 = 'checked="checked"';


				//Pedigee update
				$con_pedi = '<select name="Pedigee" style="font-family: Verdana">
			   		<option value="">Select</option>';
				$sqlcat_p = $ilance->db->query(" 
					SELECT *
					FROM " . DB_PREFIX . "pedigree ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlcat_p) > 0)
				{         
					while ($rescat_p = $ilance->db->fetch_array($sqlcat_p))
					{
						if($rescat_p['pedigree'] == $coins_det['Pedigee'])
							$con_pedi.='<option value="'.$rescat_p['pedigree'].'" selected="selected">'.$rescat_p['pedigree'].'</option>';
						else
							$con_pedi.='<option value="'.$rescat_p['pedigree'].'">'.$rescat_p['pedigree'].'</option>';
					}	
				}
			
			 	$con_pedi.='</select>';	  

				$new_release =  $coins_det['new_release'];	
				if($new_release == '0')
					$check4 = '';
				else
					$check4 = 'checked="checked"';
			 	
				$show['search_dtls'] = 'show';		   
				//echo $seleted_plus.$seleted_qa.$seleted_genuine.$seleted;//exit;
			} 
		     
			 
			 // fetch product photo attachments uploaded 
		     $itemphoto_attachmentlist = fetch_inline_attachment_filelist($user_id, $rand_value, 'itemphoto', true);
             //unset($hiddeninput);
			 //coin details
			 $user_name = fetch_user('username',$ilance->GPC['user_id']);
			 $email_user = fetch_user('email',$ilance->GPC['user_id']);
			 $first_name = fetch_user('first_name',$ilance->GPC['user_id']);
			 $last_name = fetch_user('last_name',$ilance->GPC['user_id']);
			 $first_last = $first_name.''.$last_name;
	         $slideshow_attachmentlist = fetch_inline_attachment_filelist($user_id, $rand_value, 'slideshow', true);
				
	
	$pprint_array = array('pro_ng','SetCoins','pcgdiv','coin_detail_coin_series','year_of_pcgs','coin_ser_val','suffx','cat','searched_coinid','coin_series','pcgs','title','grading_service','grade','certification_no','minimum_bid','buy_now','alternate_inventory_no','category','hot_list_edit','spl_toned_edit','genuine_details_edit','house_account_check','cost','check2','check3','check4','generic','new_release','seleted_qa','seleted_Variety','make_invisible','offline_order','bold_edit','high_edit','feat_edit','fvf_id_edit','fvf_id','list_id','list_id_up','checks','checkl','upda_val','Sets','nocoin','inotes','first_last','cat_id','pro_grade_edit','pro_grade','other_mul_select','Ped_update','con_update','editcat','Max_Quantity_Purchase_hide','quantity_hide','attachmentlist','con_attri','con_pedi','con_pedi_oth','rand_value_edit','itemphoto_attachmentlist_edit','slideshow_attachmentlist_edit','uploadproductbutton_edit','uploadbutton_edit','rand_value','email_user','user_name','Max_Quantity_Purchase_r','quantity_r','grade_create_r','Max_Quantity_Purchase','quantity','grade_create','grade_service','site_id_up','yearlist_r','monthlist_r','daylist_r','grade_service_update','seleted_genuine','seleted_details','seleted','seleted_plus','seleted_star','coin_id', 'pcgs', 'Title', 'Description', 'Certification_No', 'Coin_Series', 'Pedigee', 'Minimum_bid', 'Reserve_Price', 'Buy_it_now','Alternate_inventory_No', 'consignid','user_id','site_id','itemphoto_attachmentlist','slideshow_attachmentlist','uploadproductbutton','attachment_style','uploadbutton','cat','yearlist','monthlist','daylist','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_add_single_coin.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
		
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'raw_coins')
	{  
	     //save raw coin 
	     if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'raw_coins_save')
	     {
							//grade value name
							$sqlcat = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "grading_service WHERE id= '".$_POST['grade_service']."'
							", 0, null, __FILE__, __LINE__);
					   
						     $rescat = $ilance->db->fetch_array($sqlcat);
						
						     $grade_value = $rescat['grading'];
						
						   //insert
							if($_POST['user_id'] == '' || $_POST['consignid'] == '')
							{
							 print_action_success('sorry user not valid', $ilpage['consignment']);
							 exit();
							}
							else
							{
							
							        //insert in coin table
							        $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
									$con_insert_cointable = $ilance->db->query("
									INSERT INTO " . DB_PREFIX . "coins
									(user_id, Grading_Service, End_Date, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id,notes)
									VALUES (
									'" . $_POST['user_id'] . "',
									'" . $grade_value . "',
									'" . $date_coin . "',
									'" . $_POST['consignid'] . "',
									'r',
									'" . $_POST['Service_Level'] . "',
									'" . $_POST['present'] . "',
									'" . $_POST['min'] . "',
									'" . $_POST['listingfee'] . "',
									'" . $_POST['referal_id'] . "',
									'" . $_POST['notes'] . "'
									)
							       ");
							
									 //inert coin id get
									$con_insert_cointable_select = $ilance->db->query("
										   SELECT * FROM " . DB_PREFIX . "coins ORDER BY coin_id DESC LIMIT 1", 0, null, __FILE__, __LINE__);
										
									$row_table_coin = $ilance->db->fetch_array($con_insert_cointable_select);
											
									//insert raw coin table
									$con_insert = $ilance->db->query("
									INSERT INTO " . DB_PREFIX . "raw_coins
									(raw_id, user_id, consignid, Grading_Service, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, notes, coin_id, End_Date)
									VALUES (
									NULL,
									'".$_POST['user_id']."',
									'".$_POST['consignid']."',
									'" . $grade_value . "',
									'" . $_POST['Service_Level'] . "',
									'" . $_POST['present'] . "',
									'" . $_POST['min'] . "',
									'" . $ilance->db->escape_string($_POST['listingfee']) . "',
									'" . $_POST['referal_id'] . "',
									'" . $_POST['notes'] . "',
									'" . $row_table_coin['coin_id'] . "',
									'" . $date_coin . "'
									
									)
							");
							
							  print_action_success('Raw coin details successfully saved', $ilpage['consignment']);
							  exit();
							
							}
					
		
		 }
		 if (isset($ilance->GPC['relist']) AND $ilance->GPC['relist'] == 'update_con')
		 {
		 $show['relist'] = 'update_coin';
		 $coin_id = $ilance->GPC['coin_id'];
		 
		 $sqlcat_row = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins WHERE coin_id = '".$ilance->GPC['coin_id']."'
									", 0, null, __FILE__, __LINE__);
									$sen_row =  $ilance->db->fetch_array($sqlcat_row);
									//present and min value update
									$present = '<select name="present">';
									for($i=1; $i<=10; $i++)						
									if($sen_row['final_fee_percentage'] == $i)
									$present .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
									else
									$present .= '<option value="'.$i.'">'.$i.'</option>';
									
									$present .='</select>';
									
									$min = '<select name="min">';
									for($i=1; $i<=10; $i++)						
									if($sen_row['final_fee_min'] == $i)
									$min .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
									else
									$min .= '<option value="'.$i.'">'.$i.'</option>';
									
									$min .='</select>';
									//date function
									$dataexplode = explode('-', $sen_row['End_Date']);
									
									$daylist_1 ='<select name="day" id="day"><option value="">DATE</option>';
									
									
									for($i=1; $i<=31; $i++)
									if($dataexplode[2] == $i)
									$daylist_1 .= "<option value='$i' selected>$i</option>";
									else
									$daylist_1 .= "<option value='$i'>$i</option>";
									
									$daylist_1 .='</select>';
									
									$monthlist_1 ='<select name="month" id="month"><option value="">MONTH</option>';
									
									
									for($j=1; $j<=12; $j++)
									
									if($dataexplode[1] == $j)
									$monthlist_1 .= "<option value='$j' selected>$j</option>";
									else
									$monthlist_1 .= "<option value='$j'>$j</option>";
									
									
									$monthlist_1 .= '</select>';
									
									$yearlist_1 = '<select name="year" id="year"><option value="">YEAR</option>';
									
								
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($dataexplode[0] == $k)
									$yearlist_1 .= "<option value='$k' selected>$k</option>";
									else
									$yearlist_1 .= "<option value='$k'>$k</option>";
									
									$yearlist_1 .='</select>';
									
									//referal id list				
									$referal_id_up = '<select name="referal_id" id="referal_id" >';
							
									$sqlcat_rfid = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "referal_id
									", 0, null, __FILE__, __LINE__);
									 if ($ilance->db->num_rows($sqlcat_rfid) > 0)
									 {         
										
											while ($rescat_id = $ilance->db->fetch_array($sqlcat_rfid))
											{
														
														if($rescat_id['referalcode'] == $sen_row['referal_id'])
														$referal_id_up.='<option value="'.$rescat_id['referalcode'].'" selected="selected">'.$rescat_id['referalcode'].'</option>';
														else
														
														$referal_id_up.='<option value="'.$rescat_id['referalcode'].'">'.$rescat_id['referalcode'].'</option>';
											}
											
									}
								
									$referal_id_up.='</select>';
									//grade service update
									$grade_service_update = '<select name="grade_service" id="grade_service" onchange="return gradeservice(this.value);">
								   <option value="0">select</option>';
									 $sqlcat_gr = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "grading_service
									", 0, null, __FILE__, __LINE__);
									 if ($ilance->db->num_rows($sqlcat_gr) > 0)
									 {         
										
											while ($rescat = $ilance->db->fetch_array($sqlcat_gr))
											{
											
											
														if($rescat['grading'] == $sen_row['Grading_Service'])
														$grade_service_update.='<option value="'.$rescat['id'].'" selected="selected">'.$rescat['grading'].'</option>';
														else
														$grade_service_update.='<option value="'.$rescat['id'].'">'.$rescat['grading'].'</option>';
											}
											
									}
								
								 $grade_service_update.='</select>';
								
								//serive level update 
								 $Service_Level_update = '<div id="service_ajax"><select name="Service_Level" id="Service_Level" >
								   <option value="select">select</option>';
									 $sqlcat_serive = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "service_level
									", 0, null, __FILE__, __LINE__);
									 if ($ilance->db->num_rows($sqlcat_serive) > 0)
									 {         
										
											while ($rescat_ser = $ilance->db->fetch_array($sqlcat_serive))
											{
															if($rescat_ser['service_name'] == $sen_row['Service_Level'])
														$Service_Level_update.='<option value="'.$rescat_ser['service_name'].'" selected="selected">'.$rescat_ser['service_name'].'</option>';
														else
														$Service_Level_update.='<option value="'.$rescat_ser['service_name'].'">'.$rescat_ser['service_name'].'</option>';
											}
											
									}
								
								$Service_Level_update.='</select></div>';
								$notes =  $sen_row['notes'];
		 }
		 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'raw_coins_update')
		 {
		                   //grade name from id            
							$sqlcat = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "grading_service WHERE id= '".$_POST['grade_service']."'
							", 0, null, __FILE__, __LINE__);
						   
							      $rescat = $ilance->db->fetch_array($sqlcat);
							      $grade_value = $rescat['grading'];
							      $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
								    
									//update relist consignment
									    $con_insert_cointable = $ilance->db->query("
									    UPDATE  " . DB_PREFIX . "coins
										SET  user_id = '" . $_POST['user_id'] . "',
										     Grading_Service	= '" . $grade_value . "',
										     End_Date		=	'" . $date_coin . "',
											 consignid	=	'" . $_POST['consignid'] . "', 
											 coin_listed =		'r',
											 Service_Level =		'" . $_POST['Service_Level'] . "', 
											 final_fee_percentage =		'" . $_POST['present'] . "', 
											 final_fee_min =		'" . $_POST['min'] . "', 
											 listing_fee =		'" . $_POST['listingfee'] . "', 
											 referal_id =		'" . $_POST['consignid'] . "',
											 notes =		'" . $_POST['notes'] . "',
											 pcgs = '',
										     Title = '',
										     Description	= '',
										     Grade = '',
											 Quantity  =  '',
											 Max_Quantity_Purchase	=	'',
											 Certification_No	=	'',
											 Condition_Attribute	=	'',
											 Cac = '',
											 Star ='',
											 Plus ='',
											 Coin_Series	=	'',
											 Pedigee	= '',
											 Site_Id =	'',
											 Minimum_bid	=	'',
											 Reserve_Price		=	'',
											 Buy_it_now	=		'',
											 Alternate_inventory_No = '',
											 Category	=	'',
											 Other_information		=	'',
											 QA = '',
											 spl_toned = '',
											 hot_list = ''
											 WHERE coin_id 	  =  '" . $_POST['coin_id'] . "'
							         ");
									
									//update raw coin table
									$con_insert = $ilance->db->query("
									UPDATE " . DB_PREFIX . "raw_coins
									SET user_id = '" . $_POST['user_id'] . "',
									consignid  = '" . $_POST['consignid'] . "',
									Grading_Service  = '" . $grade_value . "',
									Service_Level = '" . $_POST['Service_Level'] . "',
									final_fee_percentage = '" . $_POST['present'] . "',
									final_fee_min = '" . $_POST['min'] . "',
									listing_fee = '" . $_POST['listingfee'] . "',
									referal_id = '" . $_POST['referal_id'] . "',
									notes = '" . $_POST['notes'] . "',
									End_Date  = '" .  $date_coin . "'
									
									WHERE coin_id 	  =  '" . $_POST['coin_id'] . "'
									
							"); 
							 
					               print_action_success('updated successfully saved', $ilpage['consignment']);
					               exit();
		
		 } 
		 //end 
		 
		$daylist = '';
		$monthlist = '';
		$yearlist = '';
		$daylist .='<select name="day" id="day"><option value="">DATE</option>';
		
		$day = date('d');
		for($i=1; $i<=31; $i++)
		
		$daylist .= "<option value='$i'>$i</option>";
		
		$daylist .='</select>';
		
		$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
		
		$month = date('m');
		for($j=1; $j<=12; $j++)
		
		$monthlist .= "<option value='$j'>$j</option>";
		
		
		$monthlist .= '</select>';
		
		$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
		
		$year = date('Y');;
		for($k=date("Y"); $k<=date("Y")+5; $k++)
		
		$yearlist .= "<option value='$k'>$k</option>";
		
		$yearlist .='</select>';
		 //grade list
        $grade_service = '<select name="grade_service" id="grade_service" onchange="return gradeservice(this.value);">
	   <option value="0">select</option>';
		 $sqlcat = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "grading_service
		", 0, null, __FILE__, __LINE__);
		 if ($ilance->db->num_rows($sqlcat) > 0)
		 {         
			
				while ($rescat = $ilance->db->fetch_array($sqlcat))
				{
								
							$grade_service.='<option value="'.$rescat['id'].'">'.$rescat['grading'].'</option>';
				}
				
		}
	
		$grade_service.='</select>';
		//service list					
		$Service_Level = '<div id="service_ajax"><select name="Service_Level" id="Service_Level" >
		<option value="select">select</option>';
		 $sqlcat_serive = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "service_level
		", 0, null, __FILE__, __LINE__);
		 if ($ilance->db->num_rows($sqlcat_serive) > 0)
		 {         
			
				while ($rescat_ser = $ilance->db->fetch_array($sqlcat_serive))
				{
								
							$Service_Level.='<option value="'.$rescat_ser['service_name'].'">'.$rescat_ser['service_name'].'</option>';
				}
				
		}
	
		$Service_Level.='</select></div>';
		//referal id list				
		$referal_id = '<select name="referal_id" id="referal_id" >';
		 $sqlcat_rfid = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "referal_id
		", 0, null, __FILE__, __LINE__);
		 if ($ilance->db->num_rows($sqlcat_rfid) > 0)
		 {         
			
				while ($rescat_id = $ilance->db->fetch_array($sqlcat_rfid))
				{
								
							$referal_id.='<option value="'.$rescat_id['referalcode'].'">'.$rescat_id['referalcode'].'</option>';
				}
				
		}
	
		$referal_id.='</select>';		
				
	      
		$user_id = $ilance->GPC['user_id'];
		$consignid = $ilance->GPC['consignid'];
	$pprint_array = array('referal_id_up','notes','daylist_1','monthlist_1','yearlist_1','present','min','grade_service_update','Service_Level_update','coin_id','yearlist','monthlist','daylist','user_id','consignid','referal_id','Service_Level','grade_service','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_raw_coin.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'send_certification')
	{  
	
	                  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'send_certification_coin')
	                  {                     
		                    //grade name from id            
							$sqlcat = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "grading_service WHERE id= '".$_POST['grade_service']."'
							", 0, null, __FILE__, __LINE__);
						   
							$rescat = $ilance->db->fetch_array($sqlcat);
							$grade_value = $rescat['grading'];
							//referral
							$sqlcat_ref = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "consignments WHERE consignid= '".$_POST['consignid']."'
							", 0, null, __FILE__, __LINE__);
						   
							$rescat_ref = $ilance->db->fetch_array($sqlcat_ref);
							
							$ref = $rescat_ref['referal_id'];
		               
						    //verfiy users id and consignmentid
						    if($_POST['user_id'] == '' || $_POST['consignid'] == '')
							{
							
							 print_action_success('sorry user not valid', $ilpage['consignment']);
							 exit();
							 
							}
							else
							{
							        if(isset($ilance->GPC['coin_id']))
									{
												
							       //update send cert in coin table
							        $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
									$con_insert_cointable = $ilance->db->query("
									UPDATE  " . DB_PREFIX . "coins
										SET  user_id   = '" . $_POST['user_id'] . "',
										     Grading_Service   = '" .  $grade_value . "',
											 End_Date   = '" .  $date_coin . "',
											 consignid   = '" . $_POST['consignid'] . "',
											 coin_listed   = 's',
											 Service_Level   = '" . $_POST['Service_Level'] . "',
											 final_fee_percentage   = '" . $_POST['present'] . "',
											 final_fee_min   = '" . $_POST['min'] . "',
											 listing_fee   = '" . $_POST['listingfee'] . "',
											 referal_id = '" . $ref . "',
											 notes = '" . $_POST['notes'] . "'
									    WHERE coin_id 	  =  '" . $_POST['coin_id'] . "'
							         ");
									 //update send
							        $con_insert = $ilance->db->query("
									UPDATE " . DB_PREFIX . "send_certification
									SET user_id = '" . $_POST['user_id'] . "',
									consignid  = '" . $_POST['consignid'] . "',
									Grading_Service  = '" . $grade_value . "',
									Service_Level = '" . $_POST['Service_Level'] . "',
									final_fee_percentage = '" . $_POST['present'] . "',
									final_fee_min = '" . $_POST['min'] . "',
									listing_fee = '" . $_POST['listingfee'] . "',
									notes = '" . $_POST['notes'] . "',
									End_Date  = '" .  $date_coin . "'
									WHERE coin_id 	  =  '" . $_POST['coin_id'] . "'
									");
					               print_action_success('Raw coin now send for certification and  updated successfully saved', $ilpage['consignment']);
					               exit();
					  
					               }
					               else
					               {
					  			
					               //insert in coin table
							        $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
									$con_insert_cointable = $ilance->db->query("
									INSERT INTO " . DB_PREFIX . "coins
									(user_id, Grading_Service, End_Date, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id,notes)
									VALUES (
									'" . $_POST['user_id'] . "',
									'" . $grade_value . "',
									'" . $date_coin . "',
									'" . $_POST['consignid'] . "',
									's',
									'" . $_POST['Service_Level'] . "',
									'" . $_POST['present'] . "',
									'" . $_POST['min'] . "',
									'" . $_POST['listingfee'] . "',
									'" . $ref . "',
									'" . $_POST['notes'] . "'
									)
							     ");
							     //inert coin id get
								$con_insert_cointable_select = $ilance->db->query("
								SELECT * FROM " . DB_PREFIX . "coins ORDER BY coin_id DESC LIMIT 1");
								
								$row_table_coin = $ilance->db->fetch_array($con_insert_cointable_select);
									
								//insert raw coin table
								$con_insert = $ilance->db->query("
								INSERT INTO " . DB_PREFIX . "send_certification
								(Certification_id, user_id, consignid, Grading_Service, Service_Level, final_fee_percentage, final_fee_min, listing_fee, notes, coin_id, End_Date)
								VALUES (
								NULL,
								'".$_POST['user_id']."',
								'".$_POST['consignid']."',
								'" . $grade_value . "',
								'" . $_POST['Service_Level'] . "',
								'" . $_POST['present'] . "',
								'" . $_POST['min'] . "',
								'" . $_POST['listingfee'] . "',
								'" . $_POST['notes'] . "',
								'" . $row_table_coin['coin_id'] . "',
								'" . $date_coin . "'
								
								)
						");
					
					           print_action_success('send certification details successfully saved', $ilpage['consignment']);
					           exit();
					           }
					
					}
			 }
	                  //update or create send certification
	                  if(isset($ilance->GPC['coin_id']))
					  {
							$coin_id = $ilance->GPC['coin_id'];
								   $show['update'] = 'update';
								   $sqlcat_row = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins WHERE coin_id = '".$ilance->GPC['coin_id']."'
									", 0, null, __FILE__, __LINE__);
									$sen_row =  $ilance->db->fetch_array($sqlcat_row);
									//present and min value update
									$present = '<select name="present">';
									for($i=1; $i<=10; $i++)						
									if($sen_row['final_fee_percentage'] == $i)
									$present .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
									else
									$present .= '<option value="'.$i.'">'.$i.'</option>';
									
									$present .='</select>';
									
									$min = '<select name="min">';
									for($i=1; $i<=10; $i++)						
									if($sen_row['final_fee_min'] == $i)
									$min .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
									else
									$min .= '<option value="'.$i.'">'.$i.'</option>';
									
									$min .='</select>';
									//date function
									$dataexplode = explode('-', $sen_row['End_Date']);
									
									$daylist ='<select name="day" id="day"><option value="">DATE</option>';
									
									
									for($i=1; $i<=31; $i++)
									if($dataexplode[2] == $i)
									$daylist .= "<option value='$i' selected>$i</option>";
									else
									$daylist .= "<option value='$i'>$i</option>";
									
									$daylist .='</select>';
									
									$monthlist ='<select name="month" id="month"><option value="">MONTH</option>';
									
									
									for($j=1; $j<=12; $j++)
									
									if($dataexplode[1] == $j)
									$monthlist .= "<option value='$j' selected>$j</option>";
									else
									$monthlist .= "<option value='$j'>$j</option>";
									
									
									$monthlist .= '</select>';
									
									$yearlist = '<select name="year" id="year"><option value="">YEAR</option>';
									
								
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($dataexplode[0] == $k)
									$yearlist .= "<option value='$k' selected>$k</option>";
									else
									$yearlist .= "<option value='$k'>$k</option>";
									
									$yearlist .='</select>';
									//grade service update
									$grade_service_update = '<select name="grade_service" id="grade_service" onchange="return gradeservice(this.value);">
								   <option value="0">select</option>';
									 $sqlcat_gr = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "grading_service
									", 0, null, __FILE__, __LINE__);
									 if ($ilance->db->num_rows($sqlcat_gr) > 0)
									 {         
										
											while ($rescat = $ilance->db->fetch_array($sqlcat_gr))
											{
											
											
														if($rescat['grading'] == $sen_row['Grading_Service'])
														$grade_service_update.='<option value="'.$rescat['id'].'" selected="selected">'.$rescat['grading'].'</option>';
														else
														$grade_service_update.='<option value="'.$rescat['id'].'">'.$rescat['grading'].'</option>';
											}
											
									}
								
								 $grade_service_update.='</select>';
								
								//serive level update 
								 $Service_Level_update = '<div id="service_ajax"><select name="Service_Level" id="Service_Level" >
								   <option value="select">select</option>';
									 $sqlcat_serive = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "service_level
									", 0, null, __FILE__, __LINE__);
									 if ($ilance->db->num_rows($sqlcat_serive) > 0)
									 {         
										
											while ($rescat_ser = $ilance->db->fetch_array($sqlcat_serive))
											{
															if($rescat_ser['service_name'] == $sen_row['Service_Level'])
														$Service_Level_update.='<option value="'.$rescat_ser['service_name'].'" selected="selected">'.$rescat_ser['service_name'].'</option>';
														else
														$Service_Level_update.='<option value="'.$rescat_ser['service_name'].'">'.$rescat_ser['service_name'].'</option>';
											}
											
									}
								
								$Service_Level_update.='</select></div>';
						
						
						$notes =  $sen_row['notes'];
						
					  }
					  else
					  {
							  $grade_service = '<select name="grade_service" id="grade_service" onchange="return gradeservice(this.value);">
							   <option value="0">select</option>';
								 $sqlcat = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "grading_service
								", 0, null, __FILE__, __LINE__);
								 if ($ilance->db->num_rows($sqlcat) > 0)
								 {         
									
										while ($rescat = $ilance->db->fetch_array($sqlcat))
										{
														
													$grade_service.='<option value="'.$rescat['id'].'">'.$rescat['grading'].'</option>';
										}
										
								}
							
							 $grade_service.='</select>';
								
							 $Service_Level = '<div id="service_ajax"><select name="Service_Level" id="Service_Level" >
							   <option value="select">select</option>';
								 $sqlcat_serive = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "service_level
								", 0, null, __FILE__, __LINE__);
								 if ($ilance->db->num_rows($sqlcat_serive) > 0)
								 {         
									
										while ($rescat_ser = $ilance->db->fetch_array($sqlcat_serive))
										{
														
													$Service_Level.='<option value="'.$rescat_ser['service_name'].'">'.$rescat_ser['service_name'].'</option>';
										}
										
								}
							
							$Service_Level.='</select></div>';
					
									 $daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
				
				$day = date('d');
				for($i=1; $i<=31; $i++)
				
				$daylist .= "<option value='$i'>$i</option>";
				
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				
				$month = date('m');
				for($j=1; $j<=12; $j++)
				
				$monthlist .= "<option value='$j'>$j</option>";
				
				
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
				$year = date('Y');;
				for($k=date("Y"); $k<=date("Y")+5; $k++)
				
				$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
		
		                     
					}
					
			 		
	         $user_id = $ilance->GPC['user_id'];
		     $consignid = $ilance->GPC['consignid'];
	$pprint_array = array('coin_id','notes','Service_Level_update','grade_service_update','min','present','user_id','consignid','yearlist','monthlist','daylist','Service_Level','grade_service','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_send_certification.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'list_raw_send')
	{
	     //list raw form coin and update
	     
		  $con_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "coins 
							WHERE coin_listed = 's'
							", 0, null, __FILE__, __LINE__);
							            $number = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{			
										$date_coin = explode('-',$row_list['End_Date']);
										$month_name = date( 'M', strtotime(date("Y-m-d", strtotime($row_list['date_end'])) ));									
										$row_list['coin_id'] = $row_list['coin_id'];
										$row_list['consignid'] = $row_list['consignid']; 
										$row_list['date_sent'] = $date_coin[1].'-'.$month_name.'-'.$date_coin[0]; 
										$row_list['certification'] = $row_list['Grading_Service'].'-'.$row_list['Service_Level'];
										$row_list['user'] = fetch_user('username', $row_list['user_id']); 
										$row_list['relist']    = '<a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&pro=sendcert&coin_id='.$row_list['coin_id'].'" >Click</a>';        
										$holding_area_list[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'list_raw_one';
										}
	    
	$pprint_array = array('number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_listing_raw.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('holding_area_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
	{
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_now')
	{
		
	 $row_count = 0;
	
	 $show['search_list'] = true ;	
	 if(empty($ilance->GPC['pcgs']) and empty($ilance->GPC['subcmd']))
	 {
	   					
		 $query_search = $ilance->db->query("SELECT *
                                FROM " . DB_PREFIX . "coins
								WHERE Site_Id = '".$ilance->GPC['site_id']."'
								
								", 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($query_search) > 0)
		{
		while($search_result = $ilance->db->fetch_array($query_search))
			{
$search_result['desci'] = '<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$search_result['user_id'].'&consignid='.$search_result['consignid'].'&pro='.$search_result['coin_id'].'&coin_id='.$search_result['coin_id'].'">'.$search_result['Title'].'</a></span>';	
								$search_result['pc'] = '<span class="blue">'.$search_result['pcgs'].'</span>';				
									    $search_result['coinid'] = 	'<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$search_result['user_id'].'&consignid='.$search_result['consignid'].'&pro='.$search_result['coin_id'].'&coin_id='.$search_result['coin_id'].'">'.$search_result['coin_id'].'</a></span>';
			
			 if($search_result['Site_Id'] == '0')
			 $search_result['er'] = 'Listed in GC';
			 else
			 $search_result['er'] = fetch_user_siteid('site_name',$search_result['Site_Id']);
			$coin_listing_search[] = $search_result;
			 $row_count++;
			}
		}
		else
		{
		  $show['no'] = 'no_result' ;
		}
	 }
	 else
	 {		
	 $pcgsexplode = explode('.',$ilance->GPC['pcgs']);
	 
	         
			 
			 if($ilance->GPC['item'] == '')
			 $item = '';
			 else if($ilance->GPC['pcgs'] == '' && $ilance->GPC['certificate']== '' && $ilance->GPC['keyword']== '')
			 $item = 'project_id ='.$ilance->GPC['item'];
			 else
			 $item = 'project_id =\''.$ilance->GPC['item'] .'\' '.$ilance->GPC['radio_item'].' ';
			 
			 if($ilance->GPC['pcgs'] == '')
			 $pc = '';
			 else if($ilance->GPC['keyword']== '' && $ilance->GPC['pedigree'] == '')
			 $pc = 'pcgs ='.$pcgsexplode[0];
			 else
			 $pc = 'pcgs =\''.$pcgsexplode[0] .' \''.$ilance->GPC['radio_pcgs'].' ' ;
			 
			 if($ilance->GPC['keyword'] == '')
			 $des = '';
			 else if($ilance->GPC['keyword']== '')
			 $des = 'Description LIKE \''.$ilance->GPC['keyword'].'%\'';
			 else
			 $des = 'Description LIKE  \''.$ilance->GPC['keyword'] .'% \''.$ilance->GPC['radio_keyword'].' ' ;
			 
			 if($ilance->GPC['certificate'] == '')
			 $cer = '';
			 else
			 $cer = 'Certification_No =\''.$ilance->GPC['certificate'].'\'';
			 
			 if($ilance->GPC['pedigree'] == '')
			 $ped = '';
			 else if($ilance->GPC['certificate']== '')
			 $ped = 'Pedigee =\''.$ilance->GPC['pedigree'].'\'';
			 else
			 $ped = 'Pedigee =\''.$ilance->GPC['pedigree'] .'\' '.$ilance->GPC['radio_ped'].' ' ;
			 
			 
	
	   
	  $query_search = $ilance->db->query("SELECT *
                                FROM " . DB_PREFIX . "coins
								WHERE Site_Id = '".$ilance->GPC['site_id']."'
								AND (".$item."".$pc."".$des."".$ped."".$cer.")
								", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($query_search) > 0)
			{
				while($search_result = $ilance->db->fetch_array($query_search))
				{
				
										$search_result['desci'] = '<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$search_result['user_id'].'&consignid='.$search_result['consignid'].'&pro='.$search_result['coin_id'].'&coin_id='.$search_result['coin_id'].'">'.$search_result['Title'].'</a></span>';	
	$search_result['pc'] = '<span class="blue">'.$search_result['pcgs'].'</span>';
											
									    $search_result['coinid'] = 	'<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$search_result['user_id'].'&consignid='.$search_result['consignid'].'&pro='.$search_result['coin_id'].'&coin_id='.$search_result['coin_id'].'">'.$search_result['coin_id'].'</a></span>';	
				 if($search_result['Site_Id'] == '0')
			     $search_result['er'] = 'Listed in GC';
			     else
			     $search_result['er'] = fetch_user_siteid('site_name',$search_result['Site_Id']);
				$coin_listing_search[] = $search_result;
				 $row_count++;
				}
			}
			else
			{
		 	 $show['no'] = 'no_result' ;
			}
		}
			
			$pprint_array = array('coin_listing_search','number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'consignments_search.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('coin_listing_search'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
		}
	else
	{
	   $counter = ($ilance->GPC['page'] - 1) * 25;
				 $scriptpageprevnext = $ilpage['consignments']. '?cmd=search';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }							
		$query_search = $ilance->db->query("SELECT *
                                FROM " . DB_PREFIX . "coins								
								GROUP BY coin_id asc LIMIT " . (($ilance->GPC['page'] - 1) * 25) . "," . '25'."");
		if($ilance->db->num_rows($query_search) > 0)
			{
			
			    $query_search1 = $ilance->db->query("SELECT *
                                FROM " . DB_PREFIX . "coins	
							", 0, null, __FILE__, __LINE__);
                              
							  $number = (int)$ilance->db->num_rows($query_search1);
		while($search_result = $ilance->db->fetch_array($query_search))
			{
			$search_result['desci'] = '<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$search_result['user_id'].'&consignid='.$search_result['consignid'].'&pro='.$search_result['coin_id'].'&coin_id='.$search_result['coin_id'].'">'.$search_result['Title'].'</a></span>';	
									$search_result['pc'] = '<span class="blue">'.$search_result['pcgs'].'</span>';				
									    $search_result['coinid'] = 	'<span class="blue"><a href="consignments_fast.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$search_result['user_id'].'&consignid='.$search_result['consignid'].'&pro='.$search_result['coin_id'].'&coin_id='.$search_result['coin_id'].'">'.$search_result['coin_id'].'</a></span>';
             if($search_result['Site_Id'] == '0')
			 $search_result['er'] = 'Listed in GC';
			 else
			 $search_result['er'] = fetch_user_siteid('site_name',$search_result['Site_Id']);
			$coin_listing_search[] = $search_result;
			 $row_count++;
			}
			}
		else
			{
		 	 $show['no'] = 'no_result' ;
			}
			
      $search_pagnation = print_pagnation($number, 25, $ilance->GPC['page'], $counter, $scriptpageprevnext);
			
			$pprint_array = array('coin_listing_search','number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','search_pagnation');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'consignments_search.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('coin_listing_search'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	 }
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'update_consignment')
	{
	
	 if(isset($ilance->GPC['pend']))
									   {
									   $pend = $ilance->GPC['pend'];
									   }
									   else
									   {
									   $pend = '0';
									   }
	
	 $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];	
								
	 $sqlusercheck_my = $ilance->db->query("
										UPDATE  " . DB_PREFIX . "consignments
										SET  coins   = '" . $ilance->GPC['coins'] . "',
											 final_fee_percentage      = '" . $ilance->GPC['feewith'] . "',
											 final_fee_min    = '" . $ilance->GPC['feemin'] . "',
											 site_id   = '" . $ilance->GPC['site_id'] . "',
											 end_date       = '" . $date_coin . "',
											 referal_id      = '" . $ilance->GPC['referal_id'] . "',
											 listing_fee   = '" . $ilance->db->escape_string($ilance->GPC['listingfee']) . "',
											 notes      = '" . $ilance->GPC['notes'] . "',
											 
											 pending  = '".$pend."'	,
											 
											 fvf_id = 	'" . $ilance->GPC['fvffee'] . "',
											 consign_type   = '" . $ilance->GPC['consign_type'] . "'
									   WHERE user_id    = '" . $ilance->GPC['user_id'] . "'
									   and consignid    = '" . $ilance->GPC['consignid'] . "'
										
										");
										
										 print_action_success('Successfully Update consignment', $ilpage['consignment']);
							             exit();
	}
	
    if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consign_edit')
	{
	                   
					    $con_insert = $ilance->db->query("
												SELECT * FROM " . DB_PREFIX . "consignments WHERE consignid= '".$ilance->GPC['consignid']."' and user_id='".$ilance->GPC['user_id']."'", 0, null, __FILE__, __LINE__);
												
						  $rescat = $ilance->db->fetch_array($con_insert);
						  $username =  fetch_user('username',$ilance->GPC['user_id']);
						  $email    =  fetch_user('email',$ilance->GPC['user_id']);
                          $notes   = $rescat['notes'];
						  $coins    = $rescat['coins'];	
						  $user_id =  $ilance->GPC['user_id'];
						  $consignid =  $ilance->GPC['consignid'];
						
						  //fvf fees
					$fvf_id = getfvf_drop_down('fvffee','fvffee',$rescat['fvf_id']);
					 
					 $consign_type_id = gettype_drop_down('consign_type','consign_type',$rescat['consign_type']);
						
						 $list_id_up = getif_drop_down('listingfee','listingfee',$rescat['listing_fee']);
									 
						  $pend = $rescat['pending'];
						  if($pend == '0')
						  $checkl = '';
						  else
						  $checkl = 'checked="checked"';
						  
				     $referal_id_up = '<select name="referal_id" id="referal_id" >';
							
									$sqlcat_rfid = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "referal_id
									", 0, null, __FILE__, __LINE__);
									 if ($ilance->db->num_rows($sqlcat_rfid) > 0)
									 {         
										
											while ($rescat_id = $ilance->db->fetch_array($sqlcat_rfid))
											{
														
														if($rescat_id['referalcode'] == $rescat['referal_id'])
														$referal_id_up.='<option value="'.$rescat_id['referalcode'].'" selected="selected">'.$rescat_id['referalcode'].'</option>';
														else
														
														$referal_id_up.='<option value="'.$rescat_id['referalcode'].'">'.$rescat_id['referalcode'].'</option>';
											}
											
									}
								error_reporting(E_ALL);
									$referal_id_up.='</select>';
	               //con date default
		           	$con_date_end = $ilance->db->query("
								SELECT date(end_date) as end_date FROM " . DB_PREFIX . "consignments WHERE user_id='".$ilance->GPC['user_id']."' AND consignid ='".$ilance->GPC['consignid']."'", 0, null, __FILE__, __LINE__);
								
			$row_table_end = $ilance->db->fetch_array($con_date_end);
	         $end_date=$row_table_end['end_date'];
	        	   //quantity
					$final_per = '';
					for($q=0; $q<6;$q++)
					{
					if($rescat['final_fee_percentage'] == $q)
					$final_per .= '<option value="'.$q.'" selected="selected">'.$q.'</option>';
					else	
					$final_per .= '<option value="'.$q.'" >'.$q.'</option>';	
					}
					//Max_Quantity_Purchase 
					$final_min = '';
					for($m=0; $m<6;$m++)
					{
					if($rescat['final_fee_min'] == $m)
					$final_min .= '<option value="'.$m.'" selected="selected">'.$m.'</option>';
					else	
					$final_min .= '<option value="'.$m.'" >'.$m.'</option>';		
					}
					
				   // site id
                   
					$site_id_up = '<select name="site_id" id="site_id" >';
					$sqlcat_siteid = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "affiliate_listing
					", 0, null, __FILE__, __LINE__);
					 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
					 {         
							if($rescat['site_id'] == '0')
							{
							$site_id_up.='<option value="0" selected="selected">Listed in GC</option>';
							while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
							{                       
							 
							$site_id_up.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';	
													
										
							}
							}
							else
							{
							$site_id_up.='<option value="0" >Listed in GC</option>';
							while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
							{  
								if($rescat_sid['id'] == $rescat['site_id'])
								$site_id_up.='<option value="'.$rescat_sid['id'].'" selected="selected">'.$rescat_sid['site_name'].'</option>';
								else
								$site_id_up.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';	
													
										
							}
							}
							
					}
				
					$site_id_up.='</select>';	
					
					
			$pprint_array = array('consign_type_id','fvf_id','list_id_up','checkl','user_id','consignid','coins','notes','referal_id_up','end_date','final_per','final_min','site_id_up','username','email','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
				
		
				($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
			
		
			$ilance->template->fetch('main', 'consignments_edit.html', 2);
		
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		
			$ilance->template->parse_loop('main', array('consignment_listing','consignment_listing_search'));
		
			$ilance->template->parse_if_blocks('main');
		
			$ilance->template->pprint('main', $pprint_array);
		
			exit();
	}
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'pen_con_list')
	{
		
		
	   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list_new')
       {
					
					$show['search_list'] = 'search_list';
					 
						$filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'user_id';
                        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
						$where = "WHERE user_id != '' ";                        
                        if (!empty($filtervalue) AND !empty($filterby))
                        {
                                $where .= "AND " . $filterby . " = '" . $filtervalue . "'";
                        }
						
					 $sql2_search = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
								$where
								
                       ", 0, null, __FILE__, __LINE__);
						
						 if ($ilance->db->num_rows($sql2_search) > 0)
                        { 		                         
                               
                               $res_list_sec = $ilance->db->fetch_array($sql2_search);
							   $con_listing = $ilance->db->query("
							SELECT user_id,consignid,coins
							FROM " . DB_PREFIX . "consignments 
							WHERE user_id= '".$res_list_sec['user_id']."'							     
								 GROUP BY consignid
								 ORDER BY consignid DESC
							", 0, null, __FILE__, __LINE__);					
							
							    $number_search = (int)$ilance->db->num_rows($con_listing);
							           if($ilance->db->num_rows($con_listing) > 0)
										{
										
										$row_con_list = 0;										
										while($row_list = $ilance->db->fetch_array($con_listing))
										{
											
											$con_listing_co = $ilance->db->query("
													SELECT COUNT(coin_id) AS total
													FROM " . DB_PREFIX . "coins WHERE user_id ='".$row_list['user_id']."'
													AND consignid = '".$row_list['consignid']."'
														", 0, null, __FILE__, __LINE__);
													 
										 $row_list_co = $ilance->db->fetch_array($con_listing_co);																			
										$total_value = $row_list_co['total'];								
								 			
										$row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $row_list['user_id'] . '">' . fetch_user('username', $row_list['user_id']) . '</a>';		
										$row_list['firstname'] =  fetch_user('first_name', $row_list['user_id']);
										$row_list['lastname'] =  fetch_user('last_name', $row_list['user_id']);					
										
										$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'">View</a>'; 
                                        $row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'">Edit</a>'; 
										$row_list['posted']    = $total_value; 
										$row_list['coins'] = $row_list['coins']; 
										$consignment_listing_search[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'list_search';
										}
                                
					    }
						else
						{
							$show['no'] = 'list_search';
						}
						
					} 	
	                        
											
		 //consignment listing
		 
		 
	//sekar works on sep 20 for bug id 900 for page slow	 	
  //counter for page 
                 $counter = ($ilance->GPC['page'] - 1) * 25;
				 $scriptpageprevnext = 'consignments_fast.php?cmd=pen_con_list';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
/*		  $con_listing1 = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "consignments c 
							where c.coins not in (SELECT COUNT(*) AS total
													FROM " . DB_PREFIX . "coins n WHERE c.user_id = n.user_id
													AND c.consignid = n.consignid)
							GROUP BY c.consignid
							ORDER BY c.consignid DESC 
							");
			
							
		  $number = (int)$ilance->db->num_rows($con_listing1);
		  $con_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "consignments c 
							where c.coins not in (SELECT COUNT(*) AS total
													FROM " . DB_PREFIX . "coins n WHERE c.user_id = n.user_id
													AND c.consignid = n.consignid)
							GROUP BY c.consignid
							ORDER BY consignid DESC 
							
							LIMIT " . (($ilance->GPC['page'] - 1) * 10) . ",10
							");
							    $number1 = (int)$ilance->db->num_rows($con_listing);
							           if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{
											
											$con_listing_co = $ilance->db->query("
													SELECT COUNT(*) AS total
													FROM " . DB_PREFIX . "coins WHERE user_id ='".$row_list['user_id']."'
													AND consignid = '".$row_list['consignid']."'
														 ");
													 
										 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
										{
										$total_va[] = $row_list_co['total'];
										$total_value = $row_list_co['total'];
										
										
										}
								 									
										$row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $row_list['user_id'] . '">' . fetch_user('username', $row_list['user_id']) . '</a>';		
										
										
										$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'">View</a>'; 
                                        $row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'">Edit</a>'; 
										$row_list['posted']    = $total_value; 
										$row_list['coins'] = $row_list['coins']; 
										$consignment_listing[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'list';
										}*/
		/*$test = "SELECT c.user_id,c.consignid,c.coins,count(n.coin_id) AS total FROM ".DB_PREFIX."consignments c, ".DB_PREFIX."coins n
				WHERE c.user_id = n.user_id AND c.consignid = n.consignid group by c.consignid LIMIT " . (($ilance->GPC['page'] - 1) * 10) . ",10";
			$querytest = $ilance->db->query($test);	
			while($restest = $ilance->db->fetch_array($querytest))
			{
				if($restest['coins'] != $restest['total'])
				{
					$row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $restest['user_id'] . '">' . fetch_user('username', $restest['user_id']) . '</a>';
				$row_list['consignid'] = $restest['consignid'];
				$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$restest['user_id'].'&consignid='.$restest['consignid'].'">View</a>'; 
				$row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$restest['user_id'].'&consignid='.$restest['consignid'].'">Edit</a>'; 
				$row_list['posted']    = $restest['total']; 
				$row_list['coins'] = $restest['coins'];
				$consignment_listing[] = $row_list;	
				}
			}*/
		
		/* $consign_list = $ilance->db->query("SELECT user_id,consignid,coins FROM ".DB_PREFIX."consignments ORDER BY consignid desc");
		 
		 while($resconsign_list = $ilance->db->fetch_array($consign_list))
		 {
		 $soxp[] = $resconsign_list['consignid'];
			$countxarr[] = $resconsign_list['user_id'];
		 }
		 	$filterc = array_filter($sop);
			$filterc = array_filter($sop);
		$filtercount = array_filter($countarr);
		
		$consval1 = implode(',',$filterc);
		$consval2 = implode(',',$filterc);
			$totcoin = $ilance->db->query("SELECT COUNT(*) AS total
										FROM " . DB_PREFIX . "coins WHERE user_id ='".$resconsign_list['user_id']."'
										AND consignid = '".$resconsign_list['consignid']."'
										");
			while($restotcoin = $ilance->db->fetch_array($totcoin)){
			
			if($resconsign_list['coins'] != $restotcoin['total'])
			{
			 	
			$sop[] = $resconsign_list['consignid'];
			$countarr[$resconsign_list['consignid']] = $restotcoin['total'];
				
			}
		 }
		 $filterc = array_filter($sop);
		$filtercount = array_filter($countarr);
		
		$consval = implode(',',$filterc);
		echo '<pre>';
		print_r($filterc);
		exit();			*/		
			
/*				
		 $totcoin = $ilance->db->query("SELECT COUNT(coin_id) AS total,user_id,consignid,coin_id
										FROM " . DB_PREFIX . "coins 
										GROUP BY user_id,consignid
										");
		while($restotcoin = $ilance->db->fetch_array($totcoin))
		{
		   
			$consign_list = $ilance->db->query("SELECT user_id,consignid,coins FROM ".DB_PREFIX."consignments 
			where user_id = '".$restotcoin['user_id']."' AND consignid = '".$restotcoin['consignid']."' AND coins != '".$restotcoin['total']."'   ORDER BY consignid desc ");
			$resconsign_list = $ilance->db->fetch_array($consign_list);
			$sop[] = $resconsign_list['consignid'];
			
			$countarr[$resconsign_list['consignid']] = $restotcoin['total'];
			
			
		}
		
		
		
			
		
		$filterc = array_filter($sop);
		
		
		$filtercount = array_filter($countarr);
		
		$consval = implode(',',$filterc);
		
		$consign = $ilance->db->query("SELECT * FROM ".DB_PREFIX."consignments 
			where  consignid in (".$consval.")   ORDER BY consignid desc LIMIT " . (($ilance->GPC['page'] - 1) * 10) . "," . '10'."");
			
		$consign1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."consignments 
			where  consignid in (".$consval.")   ORDER BY consignid desc");
		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($consign1);
		while($resconsign_list = $ilance->db->fetch_array($consign))
		{
			   $row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $resconsign_list['user_id'] . '">' . fetch_user('username', $resconsign_list['user_id']) . '</a>';
				$row_list['consignid'] = $resconsign_list['consignid'];
				$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'">View</a>'; 
				$row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'">Edit</a>'; 
				$row_list['posted']    = $filtercount[$resconsign_list['consignid']]; 
				$row_list['coins'] = $resconsign_list['coins'];
				$consignment_listing[] = $row_list;		
				$row_con_list++; 
			
		}*/		
		// Added on Nov 01 By Sekar
		$totcoin = $ilance->db->query("SELECT c.user_id, c.coins , c.consignid, COUNT(DISTINCT n.coin_id) AS countid
        FROM ".DB_PREFIX."consignments c 
		left join ".DB_PREFIX."coins n on n.user_id = c.user_id 
        WHERE c.consignid = n.consignid 
		GROUP BY c.consignid HAVING countid != c.coins ORDER BY c.consignid DESC", 0, null, __FILE__, __LINE__);
		while($restotcoin = $ilance->db->fetch_array($totcoin))
		{
		    $sop[] = $restotcoin['consignid'];
			$countarr[$restotcoin['consignid']] = $restotcoin['countid'];
		}	
		
		$totcoin2 = $ilance->db->query("SELECT c.user_id, c.coins , c.consignid, COUNT(DISTINCT n.coin_id) AS countid
        FROM ".DB_PREFIX."consignments c 
		left join ".DB_PREFIX."coins n on n.user_id = c.user_id 
        WHERE c.consignid = n.consignid 
		GROUP BY c.consignid HAVING countid = c.coins ORDER BY c.consignid DESC", 0, null, __FILE__, __LINE__);
		while($restotcoin2 = $ilance->db->fetch_array($totcoin2))
		{
		    $sop2[] = $restotcoin2['consignid'];
		}
		$consign1 = $ilance->db->query("SELECT consignid FROM ".DB_PREFIX."consignments 
			 ORDER BY consignid desc", 0, null, __FILE__, __LINE__);
		$row_con_list=0;	
		
		while($resconsign_list = $ilance->db->fetch_array($consign1))
		{
		$sop3[] = $resconsign_list['consignid'];
		}
		
		$merg = array_merge($sop,$sop2);
		$merg2 = array_diff($sop3,$merg);		
		$filterc = array_merge($merg2,$sop);		
		$filtercount = array_filter($countarr);		
		$consval = implode(',',$filterc);		
		$consign = $ilance->db->query("SELECT * FROM ".DB_PREFIX."consignments 
			where  consignid in (".$consval.")   ORDER BY consignid desc LIMIT " . (($ilance->GPC['page'] - 1) * 10) . "," . '10'."");
			
		$consign1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."consignments 
			where  consignid in (".$consval.")   ORDER BY consignid desc", 0, null, __FILE__, __LINE__);
		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($consign1);
		while($resconsign_list = $ilance->db->fetch_array($consign))
		{
			   $row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $resconsign_list['user_id'] . '">' . fetch_user('username', $resconsign_list['user_id']) . '</a>';
			   $row_list['firstname'] =  fetch_user('first_name', $resconsign_list['user_id']);
				$row_list['lastname'] =  fetch_user('last_name', $resconsign_list['user_id']);	
				$row_list['consignid'] = $resconsign_list['consignid'];
				$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'">View</a>'; 
				$row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'">Edit</a>'; 
				if($filtercount[$resconsign_list['consignid']] !='')
				$row_list['posted']    = $filtercount[$resconsign_list['consignid']]; 
				else
				$row_list['posted']    = '0'; 
				$row_list['coins'] = $resconsign_list['coins'];
				$consignment_listing[] = $row_list;		
				$row_con_list++; 
			
		}									
		$prof = print_pagnation($number, 10, $ilance->GPC['page'], $counter, $scriptpageprevnext);
		
 	$pprint_array = array('number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','prof');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_pending_listing.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('consignment_listing','consignment_listing_search'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	    
	}
	
	  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_quick_page')
	       {
		     
			  
			  
			   $coinval = $ilance->GPC['coin_id'];
			   $bidval = $ilance->GPC['min_bid'];
			   $buyval = $ilance->GPC['buy_now'];
			   
			   $coin = array_filter($coinval);
			  
			   $bid = array_filter($bidval);
			   
			   $buy = array_filter($buyval);
			 
			 
			
			  
			  for($p=0;$p<count($coinval);$p++)
			  {  
			  
			                $con_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "coins  where coin_id = '".$coinval[$p]."'
							", 0, null, __FILE__, __LINE__);
							 if($ilance->db->num_rows($con_listing) > 0)
							 {
							 
							        $coin_id=$coinval[$p];
								   
									$min_amout = $bid[$p];
									
									$buy_amout = $buy[$p];
									
									
									$sqlusercheck_my = $ilance->db->query("
									UPDATE  " . DB_PREFIX . "coins
									SET  Minimum_bid   = '" . $min_amout . "', Buy_it_now = '" . $buy_amout . "' where coin_id = '".$coin_id."'");
									 
							
							    
							 }
			     
			  
			  }
			  print_action_success('Your Coin Successfully updated', $ilpage['consignment'] . '?cmd=quick_page');
								exit();
		   
		   }
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'quick_page')
	{
		
		 
		
		
		
		   for($i=0;$i<10;$i++)
		   {
		   
		   $inner_loop.= '<tr><td>Coin Id <input type="text" name="coin_id['.$i.']" value="" /></td><td> Min Bid <input type="text" name="min_bid['.$i.']" value="" /></td><td> Buy Now <input type="text" name="buy_now['.$i.']" value="" /></td></tr>';
		   
		   }
 	$pprint_array = array('inner_loop','number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_quick_page.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('consignment_listing','consignment_listing_search'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
	
	
	/*sekar working for editing fvf on july 19*/
	
	  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_listingseller_page')
	       {
		     
			  //coin id = project id
			  
			   $project_id = $ilance->GPC['coin_id'];
			   $listingfee = $ilance->GPC['listing_fees'];
			   $sellerfee = $ilance->GPC['sellers_fees'];
			   
			  $projectid = array_filter($project_id);
			  
			   $listing = array_filter($listingfee);
			   
			   $seller = array_filter($sellerfee);
			 
			
			
			  
			  for($p=0;$p<count($project_id);$p++)
			  {  
			  
			
			  
			  
			                $project_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "projects  where project_id = '".$project_id[$p]."'
							", 0, null, __FILE__, __LINE__);
							 if($ilance->db->num_rows($project_listing) > 0)
							 {
							  $res = $ilance->db->fetch_array($project_listing);
						             $project= $project_id[$p];
								   
									 $listing_fees = $listing[$p];
									
									$seller_fees = $seller[$p];
									
									if(!empty($listing_fees))
									{
										$insertion_amount = $ilance->db->query("
	
										UPDATE  " . DB_PREFIX . "projects
	
										SET  insertionfee = '" . $listing_fees . "' where project_id = '".$project_id[$p]."'");
									}
									if(!empty($seller_fees))
									{
										if($res['filtered_auctiontype'] == 'regular' AND $res['status'] == 'expired')
										{
											$insertion_amount = $ilance->db->query("
												UPDATE  " . DB_PREFIX . "projects
												SET  fvf = '" . $seller_fees . "' where project_id = '".$project_id[$p]."'");
										}
									}
									
									$inv_listing = $ilance->db->query("
												SELECT *
												FROM " . DB_PREFIX . "invoices  WHERE projectid = '".$project_id[$p]."'
												AND isif = 1
												", 0, null, __FILE__, __LINE__);
									if($ilance->db->num_rows($inv_listing) > 0)
									{
										$invoice_amount = $ilance->db->query("
	
										UPDATE  " . DB_PREFIX . "invoices
	
										SET  amount = '" . $listing_fees . "' , paid = '" . $listing_fees . "' , totalamount = '" . $listing_fees . "' where projectid = '".$project_id[$p]."' AND isif = '1'");
									}
									else
									{
									// murugan changes in June 17 for listing fee creation
									$transactionid = construct_transaction_id ();
									$ilance->db->query("INSERT INTO ".DB_PREFIX."invoices 
									(projectid , user_id , description ,  amount,	paid, totalamount, status, invoicetype, createdate,	duedate	,paiddate, custommessage, transactionid, isif)
									VALUES
									(
									'".$project_id[$p]."',
									'".$res['user_id']."',
									'Insertion Fees for ".$project_id[$p]."',
									'".$ilance->db->escape_string($listing_fees)."',
									'".$ilance->db->escape_string($listing_fees)."',
									'".$ilance->db->escape_string($listing_fees)."',
									'paid',
									'debit',
									'".DATETIME24H."',
									'".DATETIME24H."',
									'".DATETIME24H."',
									'Insertion Fee',
									'".$transactionid."',
									'1'
									
									)
									");
									$insid = $ilance->db->insert_id();
									$ilance->db->query("	
										UPDATE  " . DB_PREFIX . "projects	
										SET  ifinvoiceid = '" . $$insid . "',
										isifpaid = 1
										where project_id = '".$project_id[$p]."'");
									}
									
									// murugan changes on jun 17 for seller fee update
									$inv_listing = $ilance->db->query("
												SELECT *
												FROM " . DB_PREFIX . "invoices  WHERE projectid = '".$project_id[$p]."'
												AND isfvf = 1
												", 0, null, __FILE__, __LINE__);
									if($ilance->db->num_rows($inv_listing) > 0)
									{
										$invoice_amount = $ilance->db->query("
	
										UPDATE  " . DB_PREFIX . "invoices
	
										SET  amount = '" . $seller_fees . "' , paid = '" . $seller_fees . "' , totalamount = '" . $seller_fees . "' where projectid = '".$project_id[$p]."' AND isfvf = '1'");
									}
									else
									{
									// murugan changes in June 17 for listing fee creation
									$transactionid = construct_transaction_id ();
									$ilance->db->query("INSERT INTO ".DB_PREFIX."invoices 
									(projectid , user_id , description ,  amount,	paid, totalamount, status, invoicetype, createdate,	duedate	,paiddate, custommessage, transactionid, isfvf)
									VALUES
									(
									'".$project_id[$p]."',
									'".$res['user_id']."',
									'Insertion Fees for ".$project_id[$p]."',
									'".$ilance->db->escape_string($seller_fees)."',
									'".$ilance->db->escape_string($seller_fees)."',
									'".$ilance->db->escape_string($seller_fees)."',
									'paid',
									'debit',
									'".DATETIME24H."',
									'".DATETIME24H."',
									'".DATETIME24H."',
									'Insertion Fee',
									'".$transactionid."',
									'1'
									
									)
									");
									$fvfid = $ilance->db->insert_id();
									$ilance->db->query("	
										UPDATE  " . DB_PREFIX . "projects	
										SET  fvfinvoiceid = '" . $$fvfid . "',
										isfvfpaid = 1
										where project_id = '".$project_id[$p]."'");
									}
									
									
									
								
							
							    
							 }
							 
							// $sel_listing = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id");
			     
			  
			  }
			  print_action_success('Your Coin Successfully updated', $ilpage['consignment'] . '?cmd=listing_page');
								exit();
		   
		   }
		   
	// murugan changes on june 09 For miscellance Fees
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_mis_page')
	       {
		     
			  //coin id = project id
			  
			   $project_id = $ilance->GPC['coin_id'];
			   $listingfee = $ilance->GPC['miscell'];
			   $sellerfee = $ilance->GPC['inv_type'];
			   
			  $projectid = array_filter($project_id);
			  
			   $listing = array_filter($listingfee);
			   
			   $seller = array_filter($sellerfee);
			 
			
			
			  
			  for($p=0;$p<count($project_id);$p++)
			  {  
			  
			
			  
			  
			                $project_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "projects  where project_id = '".$project_id[$p]."'
							", 0, null, __FILE__, __LINE__);
							 if($ilance->db->num_rows($project_listing) > 0)
							 {
							 	$res = $ilance->db->fetch_array($project_listing);
							  
						             $project= $project_id[$p];
								   
									 $listing_fees = $listing[$p];
									
									 $seller_fees = $seller[$p];
									
									if($seller_fees == 0 || $seller_fees == '')
									{
										$type = 'debit';
									}
									if($seller_fees == 1)
									{
										$type = 'credit';
									}
									
									//projectid , user_id , description ,  amount	paid	totalamount status invoicetype createdate	duedate	paiddate custommessage transactionid ismis
									
									$transactionid = construct_transaction_id ();
									$ilance->db->query("INSERT INTO ".DB_PREFIX."invoices 
									(projectid , user_id , description ,  amount,	paid, totalamount, status, invoicetype, createdate,	duedate	,paiddate, custommessage, transactionid, ismis)
									VALUES
									(
									'".$project."',
									'".$res['user_id']."',
									'Miscellaneous Fees',
									'".$ilance->db->escape_string($listing_fees)."',
									'".$ilance->db->escape_string($listing_fees)."',
									'".$ilance->db->escape_string($listing_fees)."',
									'paid',
									'".$type."',
									'".DATETIME24H."',
									'".DATETIME24H."',
									'".DATETIME24H."',
									'Miscellaneous',
									'".$transactionid."',
									'1'
									
									)
									");
									
							    
							 }
			     
			  
			  }
			  print_action_success('Your Coin Successfully updated', $ilpage['consignment'] . '?cmd=listing_page');
								exit();
		   
		   }
	
	
	
	
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listing_page')
		{
		
		 
		
		
		
		   for($i=0;$i<10;$i++)
		   {
		   
		   $listing_loop.= '<tr><td>Coin Id <input type="text" name="coin_id['.$i.']" value="" /></td><td> Listing Fees<input type="text" name="listing_fees['.$i.']" value="" /></td><td> Sellers Fees <input type="text" name="sellers_fees['.$i.']" value="" /></td></tr>';
		   
		   }
		   
		   for($j=0;$j<10;$j++)
		   {
		   
		   $mis_loop.= '<tr><td>Coin Id <input type="text" name="coin_id['.$j.']" value="" /></td><td> Miscellaneous Fees<input type="text" name="miscell['.$j.']" value="" /></td><td> Invoice Type <input type="text" name="inv_type['.$j.']" value="" /></td></tr>';
		   
		   }
 	$pprint_array = array('mis_loop','listing_loop','inner_loop','number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_listing_seller.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('consignment_listing','consignment_listing_search'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
	
	/*sekar finished working for editing fvf on july 19*/
	
	
	
	else 
	{	
		
	   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
       {
					
					$show['search_list'] = 'search_list';
					$filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'user_id';
                        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
						$where = "WHERE user_id != '' ";   
			// bug # 4499 kumaravel start
			if (!empty($filtervalue) AND !empty($filterby))
			{
				if($filterby == 'consignid')
				{
					$get_user_details = $ilance->db->query("SELECT user_id,consignid
															FROM " . DB_PREFIX . "consignments 
															WHERE consignid= '".$ilance->GPC['filtervalue']."' ", 0, null, __FILE__, __LINE__);
					
					if($ilance->db->num_rows($get_user_details) > 0)
					{
						$res_user_details = $ilance->db->fetch_array($get_user_details);
						
						$get_consign_details = $ilance->db->query("	SELECT user_id,consignid,coins,
																	DATE_FORMAT(receive_date,'%m-%d-%y') as receive_date
															FROM " . DB_PREFIX . "consignments 
															WHERE user_id= '".$res_user_details['user_id']."'
															AND consignid = '".$res_user_details['consignid']."' ", 0, null, __FILE__, __LINE__);
						$res_consign_details = $ilance->db->fetch_array($get_consign_details);
					
						$get_consign_postcoin_cnt = $ilance->db->query("SELECT COUNT(coin_id) AS total, 
																COALESCE(SUM(actual_qty*cost),'0.00') as total_cost
																FROM " . DB_PREFIX . "coins 
																WHERE user_id ='".$res_user_details['user_id']."'
																AND consignid = '".$res_user_details['consignid']."' ", 0, null, __FILE__, __LINE__);
								

						$res_consign_postcoin_cnt = $ilance->db->fetch_array($get_consign_postcoin_cnt);
						
						
						
						if($ilance->db->num_rows($get_consign_postcoin_cnt) > 0 )
						{
							$total_posted_coin = $res_consign_postcoin_cnt['total'];
							if($res_user_details['user_id']=='101')
							{
								$res_consign_details['total_cost'] = $res_consign_postcoin_cnt['total_cost'];
							}							
						}	
						else
						{
							if($res_user_details['user_id']=='101')
							{
								$res_consign_postcoin_cnt['total_cost'] ='0.00';
							}
						}
												
								if($res_user_details['user_id']=='101')
								{
									$res_consign_postcoin_cnt['receive_date'] = $res_consign_details['receive_date'];
									$show['gcho_con_rec_date']=true;
								}
								else
								{
									$show['gcho_con_rec_date']=false;
								}
								
						
						
													
						$res_consign_details['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $res_consign_details['user_id'] . '">' . fetch_user('username', $res_consign_details['user_id']) . '</a>';				
						$res_consign_details['firstname'] =  fetch_user('first_name', $res_consign_details['user_id']);
						$res_consign_details['lastname'] =  fetch_user('last_name', $res_consign_details['user_id']);			
														
						$res_consign_details['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$res_consign_details['user_id'].'&consignid='.$res_consign_details['consignid'].'">View</a>'; 
						$res_consign_details['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$res_consign_details['user_id'].'&consignid='.$res_consign_details['consignid'].'">Edit</a>'; 
						$res_consign_details['posted']    = $total_posted_coin; 
						$res_consign_details['coins'] = $res_consign_details['coins']; 						
													/*for Bug 3662 Starts */
													//count value
						$sqlcat_coins_detail = $ilance->db->query("select consignid,
						sum(case when coin_listed = 'c' then 1 else 0 end) cer,
						sum(case when coin_listed = 'r' then 1 else 0 end) raw,
						sum(case when coin_listed = 's' then 1 else 0 end) send
						FROM " . DB_PREFIX . "coins c
						WHERE c.user_id = '".$res_consign_details['user_id']."'
						AND   c.consignid = '".$res_consign_details['consignid']."'
						group by c.consignid", 0, null, __FILE__, __LINE__);
						$row_coins_list = $ilance->db->fetch_array($sqlcat_coins_detail);



						$raw_count = $row_coins_list['raw'];
						$listed_count = $row_coins_list['cer'];
						$send_count = $row_coins_list['send'];
													
				$res_consign_details['coinorder'] = '<span style="cursor:pointer;"  class="blue" onClick="window.open(\'consignments_pdf.php?cmd=pdf&subcmd=coinord&user_id='.$res_consign_details['user_id'].'&consignid='.$res_consign_details['consignid'].'&nocoin='.$res_consign_details['coins'].'&noposted='.$total_posted_coin.'&list='.$listed_count.'&raw='.$raw_count.'&send='.$send_count.'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">PDF</span>';															
					
					$consignment_listing_search[] = $res_consign_details;
					
					}
					else
					{				
						$show['no'] = 'list_search';
					}									
				}
				else
				{
					$where .= "AND " . $filterby . " = '" . $filtervalue . "'";
					
						$sql2_search = $ilance->db->query("SELECT user_id
															FROM " . DB_PREFIX . "users
															$where ", 0, null, __FILE__, __LINE__);
						
						
						if ($ilance->db->num_rows($sql2_search) > 0)
						{
							$number = (int)$ilance->db->num_rows($sql2_search);
							$res_list_sec = $ilance->db->fetch_array($sql2_search);
						}
								 
						$con_listing = $ilance->db->query("	SELECT user_id,consignid,coins,
																	DATE_FORMAT(receive_date,'%m-%d-%y') as receive_date
															FROM " . DB_PREFIX . "consignments 
															WHERE user_id= '".$res_list_sec['user_id']."'
															GROUP BY consignid
															ORDER BY consignid DESC", 0, null, __FILE__, __LINE__);
						$number_search = (int)$ilance->db->num_rows($con_listing);
						
						if($ilance->db->num_rows($con_listing) > 0)
						{
							$row_con_list = 0;
							while($row_list = $ilance->db->fetch_array($con_listing))
							{
								$con_listing_co = $ilance->db->query("	SELECT COUNT(coin_id) AS total, 
																				COALESCE(SUM(actual_qty*cost),'0.00') as total_cost
																		FROM " . DB_PREFIX . "coins 
																		WHERE user_id ='".$row_list['user_id']."'
																		AND consignid = '".$row_list['consignid']."' ", 0, null, __FILE__, __LINE__);
																 
													/*Tamil for Bug 2660 * 17/05/13 * Starts */										
													
								if($ilance->db->num_rows($con_listing_co) > 0 )
								{
									while($row_list_co = $ilance->db->fetch_array($con_listing_co))
									{
										$total_value = $row_list_co['total'];
										if($res_list_sec['user_id']=='101')
										{
											$row_list['total_cost'] = $row_list_co['total_cost'];
										}
									}
								}
								else
								{
									if($res_list_sec['user_id']=='101')
									{
										$row_list['total_cost'] ='0.00';
									}
								}
												
								if($res_list_sec['user_id']=='101')
								{
									$row_list['receive_date'] = $row_list['receive_date'];
									$show['gcho_con_rec_date']=true;
								}
								else
								{
									$show['gcho_con_rec_date']=false;
								}
													
													/*Tamil for Bug 2660 * 17/05/13 * Ends */
													
								$row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $row_list['user_id'] . '">' . fetch_user('username', $row_list['user_id']) . '</a>';				
								$row_list['firstname'] =  fetch_user('first_name', $row_list['user_id']);
								$row_list['lastname'] =  fetch_user('last_name', $row_list['user_id']);			
														
								$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'">View</a>'; 
								$row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'">Edit</a>'; 
								$row_list['posted']    = $total_value; 
								$row_list['coins'] = $row_list['coins']; 
													
													/*for Bug 3662 Starts */
													//count value
								$sqlcat_coin_detail = $ilance->db->query("select consignid,
								count(*) total,
								sum(case when coin_listed = 'c' then 1 else 0 end) cer,
								sum(case when coin_listed = 'r' then 1 else 0 end) raw,
								sum(case when coin_listed = 's' then 1 else 0 end) send
								FROM " . DB_PREFIX . "coins c
								WHERE c.user_id = '".$row_list['user_id']."'
								AND   c.consignid = '".$row_list['consignid']."'
								group by c.consignid", 0, null, __FILE__, __LINE__);
								$row_coin_list = $ilance->db->fetch_array($sqlcat_coin_detail);



								$raw_count = $row_coin_list['raw'];
								$listed_count = $row_coin_list['cer'];
								$send_count = $row_coin_list['send'];
													
				$row_list['coinorder'] = '<span style="cursor:pointer;"  class="blue" onClick="window.open(\'consignments_pdf.php?cmd=pdf&subcmd=coinord&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&nocoin='.$row_list['coins'].'&noposted='.$total_value.'&list='.$listed_count.'&raw='.$raw_count.'&send='.$send_count.'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">PDF</span>';
												
													/*for Bug 3662 Ends */
													
								$consignment_listing_search[] = $row_list;
								$row_con_list++;
							}
						}
						else
						{				
							$show['no'] = 'list_search';
						}
								
				}	
			}
			// bug # 4499 kumaravel end			
						
						
						

					} 	
	              								
		            								
		 //consignment listing
		 
		   $counter = ($ilance->GPC['page'] - 1) * 10;
				 $scriptpageprevnext = $ilpage['consignments']. '?cmd=listing';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
				 
		
										
			$totcoin = $ilance->db->query("SELECT COUNT(coin_id) AS total,user_id,consignid,coin_id
										FROM " . DB_PREFIX . "coins 
										GROUP BY user_id,consignid
										", 0, null, __FILE__, __LINE__);
		while($restotcoin = $ilance->db->fetch_array($totcoin))
		{
		   
			$consign_list = $ilance->db->query("SELECT user_id,consignid,coins FROM ".DB_PREFIX."consignments 
			where user_id = '".$restotcoin['user_id']."' AND consignid = '".$restotcoin['consignid']."' AND coins = '".$restotcoin['total']."'   ORDER BY consignid desc ", 0, null, __FILE__, __LINE__);
			$resconsign_list = $ilance->db->fetch_array($consign_list);
			$sop[] = $resconsign_list['consignid'];
			$countarr[$resconsign_list['consignid']] = $restotcoin['total'];
			
			
		}
		
		$filterc = array_filter($sop);
		$filtercount = array_filter($countarr);
		
		$consval = implode(',',$filterc);
		
		
		
		$consign = $ilance->db->query("SELECT c.consignid,u.username,cn.consignid,cn.coins,c.coincount,cn.user_id
										FROM " . DB_PREFIX . "consignments cn 
										left join " . DB_PREFIX . "users u on u.user_id=cn.user_id
										left join (select count(*) as coincount,consignid 
										from " . DB_PREFIX . "coins group by consignid) c on c.consignid=cn.consignid
										ORDER BY cn.consignid desc LIMIT " . (($ilance->GPC['page'] - 1) * 10) . "," . '10'."", 0, null, __FILE__, __LINE__);
		
			
		$consign1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."consignments 
			   ORDER BY consignid desc", 0, null, __FILE__, __LINE__);
		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($consign1);
		while($resconsign_list = $ilance->db->fetch_array($consign))
		{
		
				
			    $row_list['username'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $resconsign_list['user_id'] . '">' . fetch_user('username', $resconsign_list['user_id']) . '</a>';
			    $row_list['firstname'] = fetch_user('first_name', $resconsign_list['user_id']);
				$row_list['lastname'] = fetch_user('last_name', $resconsign_list['user_id']);
				$row_list['consignid'] = $resconsign_list['consignid'];
				$row_list['View'] = '<a href="consignments_fast.php?cmd=coin_list&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'">View</a>'; 
				$row_list['edit'] = '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'">Edit</a>'; 
				if(($resconsign_list['coincount'])> 0 )
				{
				$row_list['posted']    = $resconsign_list['coincount']; 
				}
				else
				{
				$row_list['posted']    = "0"; 
				}
				
				$row_list['coins'] = $resconsign_list['coins'];
				
				
				/*for Bug 3662 Starts */
				//count value
				
				
				$sqlcat_coin_detail = $ilance->db->query("select consignid,
				count(*) total,
				sum(case when coin_listed = 'c' then 1 else 0 end) cer,
				sum(case when coin_listed = 'r' then 1 else 0 end) raw,
				sum(case when coin_listed = 's' then 1 else 0 end) send
				FROM " . DB_PREFIX . "coins c
				WHERE c.user_id = '".$resconsign_list['user_id']."'
				AND   c.consignid = '".$resconsign_list['consignid']."'
				group by c.consignid", 0, null, __FILE__, __LINE__);
				$row_coin_list = $ilance->db->fetch_array($sqlcat_coin_detail);
				
	
				
				$raw_count = $row_coin_list['raw'];
				$listed_count = $row_coin_list['cer'];
				$send_count = $row_coin_list['send'];
				$total_value== $row_coin_list['total'];
				
				$row_list['coinorder'] = '<span style="cursor:pointer;"  class="blue" onClick="window.open(\'consignments_pdf.php?cmd=pdf&subcmd=coinord&user_id='.$resconsign_list['user_id'].'&consignid='.$resconsign_list['consignid'].'&nocoin='.$resconsign_list['coins'].'&noposted='.$total_value.'&list='.$listed_count.'&raw='.$raw_count.'&send='.$send_count.'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">PDF</span>';
				
				/*for Bug 3662 Ends */
				
				$consignment_listing[] = $row_list;		
				$row_con_list++; 
			
		}			
	
									
		
    $listing_pagnation = print_pagnation($number, 10, $ilance->GPC['page'], $counter, $scriptpageprevnext);
 	$pprint_array = array('number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consignments_listing.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('consignment_listing','consignment_listing_search'));
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
function getfvf_drop_down($htmlname,$htmlid,$selected_id=0)
{
global $ilance;
$sql="SELECT g.*  FROM " . DB_PREFIX . "finalvalue_groups g
left join " . DB_PREFIX . "finalvalue f on f.groupid=g.groupid
WHERE  g.state='product' and f.tierid>0  group by g.groupid order by g.sort";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
$html='<select name="'.$htmlname.'" id="'.$htmlid.'"   style="width: 200px" >';
 while($line=$ilance->db->fetch_array($res))
 {
	if($line['groupid']==$selected_id)
	{
		$html.='<option value="'.$line['groupid'].'" selected="selected">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
	}
	else
	{	
		$html.='<option value="'.$line['groupid'].'">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
	}					 
 }
 $html.='</select>';
}
return $html;
}


//for bug #4292
function gettype_drop_down($htmlname,$htmlid,$selected_id=0)
{
	$typess = array(1=>'Certified Coins',2=>'Uncertified Coins',3=>'Certified Currency',4=>'Uncertified Currency',5=>'Other Items');
	
	$html='<select name="'.$htmlname.'" id="'.$htmlid.'"   style="width: 120px" >';
	
	foreach($typess as $key=>$types)
	{
		if($key==$selected_id)
		{
			$html.='<option value="'.$key.'" selected="selected">'.$types.'</option>';
		}
		else
		{	
			$html.='<option value="'.$key.'">'.$types.'</option>';
		}	
	}
	
	 $html.='</select>';
	 
return $html;
}
//end

//for bug #5191
function get_consing_type($selected_id=0)
{
	if($selected_id == '1')
	{
		$html.= 'Certified Coins';
	}
	else if($selected_id == '2')
	{
		$html.= 'Uncertified Coins';
	}
	else if($selected_id == '3')
	{
		$html.= 'Certified Currency';
	}
	else if($selected_id == '4')
	{
		$html.= 'Uncertified Currency';
	}
	else if($selected_id == '5')
	{
		$html.= 'Other Items';
	}
	else
	{
		$html.= '-';
	}

	return $html;
		 
}

// end 
function getif_drop_down($htmlname,$htmlid,$selected_id=0)
{
global $ilance, $ilconfig, $phrase, $ilpage;
 $sql="SELECT g.*  FROM " . DB_PREFIX . "insertion_groups g
left join " . DB_PREFIX . "insertion_fees f on f.groupid=g.groupid
WHERE  g.state='product' and f.insertionid>0 group by g.sort";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
$html='<select name="'.$htmlname.'" id="'.$htmlid.'"   style="width: 200px" >';
 while($line=$ilance->db->fetch_array($res))
 {
	if($line['groupname']==$selected_id)
	{
		$html.='<option value="'.$line['groupname'].'" selected="selected">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
	}
	else
	{	
		$html.='<option value="'.$line['groupname'].'">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
	}					 
 }
 $html.='</select>';
}
return $html;
}

function send_consignor_message_email($message,$user_id,$consignment_id)
{
	global $ilance, $ilconfig, $phrase, $ilpage;
	$sql6="SELECT u.username,u.email FROM " . DB_PREFIX . "consignments c 
	left join " . DB_PREFIX . "users u on u.user_id=c.user_id
	 WHERE c.consignid='".$consignment_id."' and u.user_id = '" . $user_id . "'";
	$result6 = $ilance->db->query($sql6, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($result6)>0)
	{
		while($line6= $ilance->db->fetch_array($result6))
		{
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->mail = $line6['email'];
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('consignor_note_email_notification');		
			$ilance->email->set(array(
				'{{consignid}}' =>$consignment_id,
				'{{message}}' => $message,
				'{{date_time}}' => DATETIME24H,
				'{{referrer}}' => REFERRER,
				'{{username}}' => $line6['username']
			));
			$ilance->email->send();
			
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('consignor_note_email_notification');		
			$ilance->email->set(array(
			'{{consignid}}' =>$consignment_id,
			'{{message}}' => $message,
			'{{date_time}}' => DATETIME24H,
			'{{referrer}}' => REFERRER,
			'{{username}}' => 'Admin,
			username :'.$line6['username'].',
			Email id :'.$line6['email']
			));
			$ilance->email->send();
			
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('consignor_note_email_notification');		
			$ilance->email->set(array(
			'{{consignid}}' =>$consignment_id,
			'{{message}}' => $message,
			'{{date_time}}' => DATETIME24H,
			'{{referrer}}' => REFERRER,
			'{{username}}' => 'Admin,
			username :'.$line6['username'].',
			Email id :'.$line6['email']
			));
			$ilance->email->send();
			
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->mail = $ilconfig['globalserversettings_staff_juliann'];
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('consignor_note_email_notification');		
			$ilance->email->set(array(
			'{{consignid}}' =>$consignment_id,
			'{{message}}' => $message,
			'{{date_time}}' => DATETIME24H,
			'{{referrer}}' => REFERRER,
			'{{username}}' => 'juliann,
			username :'.$line6['username'].',
			Email id :'.$line6['email']
			));
			$ilance->email->send();
		}
	}
}


function get_header_pdf($FETCH_USER)
{
	$name = $FETCH_USER['username'];
	$email=$FETCH_USER['email'];
	$check_payable=$FETCH_USER['Check_Payable'];
	$first_name=$FETCH_USER['first_name'];
	$last_name=$FETCH_USER['last_name'];
	$address=$FETCH_USER['address'];
	$address2=$FETCH_USER['address2'];
	$city=$FETCH_USER['city'];
	$state=$FETCH_USER['state'];
	$zipcode=$FETCH_USER['zip_code'];
	$consignid=$FETCH_USER['consignid'];
	$dateted_on= date('F d, Y',(strtotime("now")));
	$new_header = '<table width="100%">
	<tr>
	<td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
	<td>&nbsp;</td>

	</tr>
	<tr>
	<td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
	17500 Red Hill Avenue, Suite 160, Irvine, CA 92614<br>
	Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
	E-mail: info@greatcollections.com</td>
	<td >&nbsp;</td>
	</tr>					
	<tr >
	<td size="12">&nbsp;Consignor Username : '.$name.' <br>E-mail : '.$email;
	$new_header.='</td>
	</tr>
	<tr>
	<td size="12"><br>'.$first_name.' &nbsp; '.$last_name.'<br>'.$address.'<br>';
	if($address2 != '')
	{
		$new_header.=$address2.'<br>';
	}
	$new_header.=$city.' &nbsp; '.$state.' &nbsp; '.$zipcode.' </td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>'.$dateted_on.'</td></tr>
	<tr><td>Re: Consignment ID: '.$consignid.'</td></tr>
	<tr><td>Dear '.$first_name.',</tr>
	
	<tr><td><p>'.nl2br($FETCH_USER['message']).'</p></td></tr>
	</table>';
	return $new_header;
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>