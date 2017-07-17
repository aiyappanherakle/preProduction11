<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may nbnnot be redistributed in whole or significant part.     # ||
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
	'jquery',
	'jquery_custom_ui',
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


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
		
		//sen on mar24 bug id 1329
	
		
		 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'testimonials')
        {

   if (!empty($ilance->GPC['title'])  )
              {                
        
  $sql = $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "testimonial
                (title, description, firstname,lastname	,email, location,date_added,status)
                VALUES
                (
                '" . $ilance->db->escape_string($ilance->GPC['title']). "',
                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['firstname']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['lastname']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['email']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['location']) . "',
				'".DATETODAY."',
				'accept'
				)
        ", 0, null, __FILE__, __LINE__);
			print_action_success('Thank you for your Testimonial.', 'settings.php','Back');
		    exit();

		
		 	
        }
   }
		
		//sen on mar24 end bug id 1329
		
			
			//venkat missing image list
			// murugan added at july 19 
			//nov24 for bug id 1002
				if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'check_image')
				{
				$img=$ilance->db->query("SELECT coin_id
								FROM " . DB_PREFIX . "attachment
								GROUP BY coin_id");
								
								$total=array();
				
				while($img1=$ilance->db->fetch_array($img))
				{
				$coinid[]=$img1['coin_id'];
				
				}	
				
			$coin_id=implode(",",$coinid);
		
				$image_sql1 = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "attachment
											WHERE coin_id in (".$coin_id.")
											 ORDER BY coin_id,cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED) 
											");
											$total=array();	
				$store=array();	
				$coun = $ilance->db->num_rows($image_sql1);	
						
				while($img2=$ilance->db->fetch_array($image_sql1))
				{
				
				 $c = $img2['filename'];
				 
				 $u[] = $img2['coin_id'].'--'.substr($c,strpos($c,"-")+1,strpos($c,".")-strpos($c,"-")-1);
		         $serial_no[] = substr($c,strpos($c,"-")+1,strpos($c,".")-strpos($c,"-")-1);
				
				 $uw[] = $img2['coin_id'];
								 
				}	
					$s=0;
					
				foreach($u as $key=>$value)
				{
				//  echo $value.'<br>';
				  
				  $t = explode('--',$value);
				 
				if($t['0'] == $uw[$s])
				{
				  
				    if($t['1'] !='1')
					{
					 if($t['1'] <=2)
					 {
					$sec[] = $uw[$s];
					 }
					}
					
					
					 if($t['1'] !='2')
					{
					if($t['1'] <=2)
					 {
					$fir[] = $uw[$s];
					}
					}
					
					
					
					
					
				
				//echo  'pppp='.$uw[$s].'----'.$t['1'].'----'.$t['0'].'<br>';
				}	
				
				   $s++;
				}	
				
				$d = array_diff($fir,$sec);
				$dw = array_diff($sec,$fir);
				
				
			
					//echo '</pre>';
				foreach($d as $result)
				{
				   $res=$result;
				  $sec_arr[]="2.jpg is not available in item no ".$res; 
				
				}
			  foreach($dw as $result1)
				{
				   $res1=$result1;
				  $sec_arr1[]="1.jpg is not available in item no ".$res1; 
				  
				
				}
				
				
					$total=array_merge($sec_arr,$sec_arr1);
					
				
					
					$table='<table align="center"  border="0" cellpadding="5" cellspacing"5"><tr><td><b>Missing coin image list</b></td></tr>';                   
						if(is_array($total))
						{
					foreach($total as $values)
				{
				
				$table.='<tr><td>'.$values.'<td></tr>' ;
									   
				
				}
				}
				else
				{
				$table.='<tr></td>No data found</tr></td>';
				}
				$table.='</table>';
				
				define('FPDF_FONTPATH','../font/');
				require('pdftable_1.9/lib/pdftable.inc.php');
				
				$p = new PDFTable();
				
				$p->AddPage();
				
				 $p->setfont('times','',10);
				
				$p->htmltable($table);
				
				$p->output('missing_coin_list.pdf','D');
				
				}

			
			if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'reset_cat_count')
			{
			
				$ilance->db->query("update ". DB_PREFIX . "catalog_toplevel d set auction_count=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='open' and p.visible=1 and coin_series_denomination_no=d.denomination_unique_no)" , 0, null, __FILE__, __LINE__);
				$ilance->db->query("update ". DB_PREFIX . "catalog_toplevel d set auction_count_hist=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='expired' and visible=1 and coin_series_denomination_no=d.denomination_unique_no)" , 0, null, __FILE__, __LINE__);
				$ilance->db->query("update ". DB_PREFIX . "catalog_second_level d set auction_count=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='open' and p.visible=1 and p.coin_series_unique_no=d.coin_series_unique_no)" , 0, null, __FILE__, __LINE__);
				$ilance->db->query("update ". DB_PREFIX . "catalog_second_level d set auction_count_hist=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='expired' and p.visible=1 and p.coin_series_unique_no=d.coin_series_unique_no)" , 0, null, __FILE__, __LINE__);
				$ilance->db->query("update ". DB_PREFIX . "catalog_coin c set auction_count=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='open' and p.visible=1 and p.pcgs=c.pcgs)" , 0, null, __FILE__, __LINE__);
				$ilance->db->query("update ". DB_PREFIX . "catalog_coin c set auction_count_hist=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='expired' and p.visible=1 and p.pcgs=c.pcgs)" , 0, null, __FILE__, __LINE__);
				$ilance->db->query("update ". DB_PREFIX . "categories c set auctioncount=(select count(*) from ". DB_PREFIX . "projects p LEFT JOIN ". DB_PREFIX . "users u ON p.user_id = u.user_id WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' and p.status='open' and p.visible=1 and p.cid=c.cid)" , 0, null, __FILE__, __LINE__);
				print_action_success('category counts are reset sucessfully', $ilpage['staffsettings'] . '?cmd=referalid');
				exit();
 
			}
            //###### UPDTAE PAGE VIEW COUNT 			
	   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'update_view_count')
	   {
	      //(Query took 0.0169 sec)	
	       $ilance->db->query("UPDATE  ". DB_PREFIX . "projects
				   SET views=views+1
				   WHERE status='open'
				   ORDER BY RAND()
				   LIMIT 5000
				 ");		
			
	       print_action_success('Page View counts are Updated Sucessfully', $ilpage['staffsettings']);
	       exit();
 
	   }
            //###### UPDTAE GC Members Tracking COUNT 			
	   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'update_watch_count')
	   {
	       $ilance->db->query("UPDATE  ". DB_PREFIX . "projects
				   SET users_tracked=users_tracked+1
				   WHERE status='open'
				   ORDER BY RAND()
				   LIMIT 5000
				 ");		
	       print_action_success('Watch counts are Updated Sucessfully', $ilpage['staffsettings']);
	       exit();
 
	   }
		   //june21
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'admin_time')
        { 
	       	$dateexplode = explode('-',$ilance->GPC['date_id']);
			$check = mktime(0, 0, 0, $dateexplode[1], $dateexplode[2], $dateexplode[0]);
			$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		
			if ($check > $today) 
			{
			    //start and end time in admin setting
				$start_time =  $ilance->GPC['from_id'];
				$end_time =   $ilance->GPC['to_id'];
				$nextDay=$start_time>$end_time?1:0;
				$dep=explode(':',$start_time);
				$arr=explode(':',$end_time);
				 
				$diff=abs(mktime($dep[0],$dep[1],0,date('m'),date('d'),date('y')) - mktime($arr[0],$arr[1],0,date('m'),date('d')+$nextDay,date('y')));
				 
				$hours=floor($diff/(60*60));
				$mins=floor(($diff-($hours*60*60))/(60));
				$secs=floor(($diff-(($hours*60*60)+($mins*60))));
				if(strlen($hours)<2){$hours="0".$hours;}
				if(strlen($mins)<2){$mins="0".$mins;}
				if(strlen($secs)<2){$secs="0".$secs;}
				$hours.':'.$mins.':'.$secs;
				
				//total min from start and end time
				$totalhours = (($hours * 60) + $mins) * 60;
			   
			   
			   
		        $date_coin = $ilance->GPC['date_id'];
				
				
				//now i remove c.Site_Id = '0' and for all site id
				$res20=$ilance->db->query("select project_id,cid from 
				" . DB_PREFIX . "projects				
				where date(date_end)='".$date_coin."' 				
				order by CASE WHEN (cid = '6000120' OR cid = '6000127' OR cid = '6000118' OR cid = '6000128' OR cid = '6000129') THEN project_title END asc, Orderno				
				");
				
				$res60=$ilance->db->query("select project_id,cid from 
				" . DB_PREFIX . "projects				
				where date(date_end)='".$date_coin."' 				
				order by CASE WHEN (cid = '6000120' OR cid = '6000127' OR cid = '6000118' OR cid = '6000128' OR cid = '6000129') THEN project_title END asc, Orderno				
				");
				
				$count  = (int)$ilance->db->num_rows($res20);
		       $count2 = (int)$ilance->db->num_rows($res60);
			
		//gap for total min and count 
		 $gap = floor($totalhours/$count);
		 $event_time = $start_time;
		//increment gap for min
		for($u=0;$u<$count;$u++)
		{
		   if($u == '0')
		   {
		   $event_length = '0';
		   }
		   else
		   {
		   $event_length = ($gap * $u);
		   }
	
		   $timestamp = strtotime("$event_time");
		   $etime = strtotime("+$event_length seconds", $timestamp);
		   $my_arr[] = $next_time = date('H:i:s', $etime);
		 }
		
		$r=0;
		if($count == $count2)
		{		
		
			//total update coin for without projectid
			while($row = $ilance->db->fetch_array($res20))
			{  
			
			
			
				$con_data = $ilance->db->query("
				UPDATE " . DB_PREFIX . "projects SET date_end = CONCAT(DATE(date_end),' ".$my_arr[$r]."') where  project_id='".$row['project_id']."'");
			
				$r++;
			}
		}
	}	
	 else
	{
		  print_action_failed("Please Check your End Date Field,it may be past", $ilpage['staffsettings']);
							
	      exit();
	 }		   
		
		        print_action_success('End Date Field have been Updated Successfully', $ilpage['staffsettings']);
					exit();
		
		}		

   		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'referalid')
        {    
				$area_title = $phrase['_referal_id'];
                $page_title = SITE_NAME . ' - ' . $phrase['_referal_id'];
				
				$hiddenfieldsubcmdrefid = 'add-referalid';
				$hiddendo = $hiddenid = '';
				
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
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				
				//ADD NEW REFERAL ID #######################################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-referalid' AND !empty($ilance->GPC['referalid']))
                {
				//id, referalcode, description, date_added
				
					if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                	{
                        $dateadded = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
					}
					
					//id, referalcode, description, date_added
				$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "referal_id
								WHERE referalcode = '".$ilance->GPC['referalid']."'                              
                        ");
					if ($ilance->db->num_rows($sqlarea) > 0)
					{	
						 print_action_failed("Referal Code Already Listed, Please Romove Referal Code Or Modify Existing ", $ilpage['staffsettings'] . '?cmd=referalid');
					}
					else
					{
					$urlpath = $ilance->GPC['url_path'] . '?referal_name='.$ilance->GPC['referalid'].'' ;
					
				 $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "referal_id
                                (id, referalcode, description, date_added,urlpath)
                                VALUES (
                                NULL,
                                '" . $ilance->db->escape_string($ilance->GPC['referalid']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                                '" . $ilance->db->escape_string($dateadded) . "',
								'" . $ilance->db->escape_string($urlpath) . "'
                                )
                        ");
					print_action_success($phrase['_the_referal_code_was_created_successfully'], $ilpage['staffsettings'] . '?cmd=referalid');
					exit();
				}
				}
				//ADD NEW REFERAL ID #######################################################
				
				 // #### DELETE A REFERAL ID  ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-referalid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "referal_id
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success($phrase['_the_selected_referal_id_was_removed_from_the_system'], $ilpage['staffsettings'] . '?cmd=referalid');
                        exit();
                }
				 // #### DELETE A REFERAL ID   ####################################
				
				// #### UPDATE A REFERAL ID ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-referalid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdrefid = 'update-referalid';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "referal_id
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $referalcode = $res['referalcode'];
                                $description = $res['description'];
                                $addeddate = $res['date_added'];
								$datesplit = explode('-', $addeddate);
                                $splityear = $datesplit[0];
                                $splitmonth = $datesplit[1];
                                $splitday = $datesplit[2];
								$url = explode('?',$res['urlpath']);
								$url_path = $url[0];
								$daylist ='<select name="day" id="day"><option value="">DATE</option>';
																	
									for($i=1; $i<=31; $i++)
									if($splitday == $i)
									$daylist .= "<option value='$i' selected>$i</option>";
									else
									$daylist .= "<option value='$i'>$i</option>";
								
								$daylist .='</select>';
								
								$monthlist ='<select name="month" id="month"><option value="">MONTH</option>';									
									
									for($j=1; $j<=12; $j++)
									
									if($splitmonth == $j)
									$monthlist .= "<option value='$j' selected>$j</option>";
									else
									$monthlist .= "<option value='$j'>$j</option>";
								
								
								$monthlist .= '</select>';
								
								$yearlist = '<select name="year" id="year"><option value="">YEAR</option>';
								
									
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($splityear == $k)
									$yearlist .= "<option value='$k' selected>$k</option>";
									else
									$yearlist .= "<option value='$k'>$k</option>";
								
								$yearlist .='</select>';
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-referalid' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
						if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        	$dateadded = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
					    $urlpath = $ilance->GPC['url_path'] . '?referal_name='.$ilance->GPC['referalid'].'' ;
						
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "referal_id
                                SET referalcode = '" . $ilance->db->escape_string($ilance->GPC['referalid']) . "',
                                description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
								date_added = '" . $ilance->db->escape_string($dateadded) . "',
								urlpath ='".$ilance->db->escape_string($urlpath)."'                               
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success($phrase['_the_selected_referal_code_was_updated_successfully'], $ilpage['staffsettings'] . '?cmd=referalid');
                        exit();         
                }
				 // #### UPDATE A REFERAL ID   ####################################
				 
				 //pagination ####################################################
				 $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
				$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
				
				$displayorderfields = array('asc', 'desc');
				$displayorder = '&amp;displayorder=asc';
				$currentdisplayorder = $displayorder;
				$displayordersql = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder = '&amp;displayorder=desc';
					$currentdisplayorder = '&amp;displayorder=asc';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder = '&amp;displayorder=asc';
					$currentdisplayorder = '&amp;displayorder=desc';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
				{
					$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage = $ilpage['staffsettings']. '?cmd=referalid' . $currentdisplayorder . $orderby;
				 
				 //pagination #####################################################
				//LIST REFERAL ID #######################################################
				$sqlreferal = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "referal_id
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
                        );
				$sqlreferal1 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "referal_id
                                
                        ");		
				$number = (int)$ilance->db->num_rows($sqlreferal1);	
				if ($ilance->db->num_rows($sqlreferal) > 0)
                {
                	$row_count = 0;
                    while ($resreferal = $ilance->db->fetch_array($sqlreferal, DB_ASSOC))
                    {	
						$resreferal['referalcode'] = $resreferal['referalcode']; 
						$resreferal['description'] = $resreferal['description'];
						$resreferal['addeddate'] = $resreferal['date_added'];
						$resreferal['edit'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=referalid&amp;subcmd=update-referalid&amp;id=' . $resreferal['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$resreferal['remove'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=referalid&amp;subcmd=remove-referalid&amp;id=' . $resreferal['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$referallist[] = $resreferal;
                        $row_count++;
					}
				
				$prevnext = print_pagnation_new($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);	
				}
				else
				{
					$show['no_referallist'] = true;
				}
				//LIST REFERAL ID #######################################################
				
				
				$pprint_array = array('prevnext','yearlist','monthlist','daylist','description','referalcode','hiddenid','hiddendo','hiddenfieldsubcmdrefid','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','url_path');
				
				$ilance->template->fetch('main', 'settings_referal_id.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('referallist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();
				
				
		}
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'delete_saved_my')
        {
		
		   $sqlusercheck_delete = $ilance->db->query("
														
														DELETE FROM " . DB_PREFIX . "staff_search
				                                        WHERE id = '" . intval($ilance->GPC['id']) . "'
														
														");
													 print_action_success('successfully deleted your search id',  $ilpage['staffsettings'] . '?cmd=searchpage');
                                                     exit();
		
		}
		else if (isset($ilance->GPC['update']) AND $ilance->GPC['update'] == 'update_search_result')
        {
		
		  $ilance->db->query("
				UPDATE " . DB_PREFIX . "staff_search
				SET title = '" . $ilance->GPC['ptitle'] . "',
				url = '" . $ilance->GPC['purl'] . "',
				description = '" . $ilance->GPC['pdesc'] . "'
				WHERE id = '" . intval($ilance->GPC['pid']) . "'
					
			", 0, null, __FILE__, __LINE__);
			
		 print_action_success('successfully updated your search id',  $ilpage['staffsettings'] . '?cmd=searchpage');
		 exit();
		
		}
		//Shipping completed
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'shipping')
        {
				$area_title = $phrase['_shipping'];
                $page_title = SITE_NAME . ' - ' . $phrase['_shipping'];
				
				
				$configuration_shippingsettings = $ilance->admincp->construct_admin_input('shippingsettings', $ilpage['staffsettings'] . '?cmd=shipping');
				
				// #### INSERT NEW SHIPPING PARTNER ############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-shipper'  AND !empty($ilance->GPC['title'])  AND !empty($ilance->GPC['shipcode'])  AND !empty($ilance->GPC['carrier']))
	{
		$ilance->GPC['title'] = isset($ilance->GPC['title']) ? $ilance->GPC['title'] : '';
		$ilance->GPC['shipcode'] = isset($ilance->GPC['shipcode']) ? $ilance->GPC['shipcode'] : '';
		$ilance->GPC['domestic'] = isset($ilance->GPC['domestic']) ? intval($ilance->GPC['domestic']) : 0;
		$ilance->GPC['international'] = isset($ilance->GPC['international']) ? intval($ilance->GPC['international']) : 0;
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "shippers
			(shipperid, title, shipcode, domestic, international, carrier, basefee, addedfee)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['shipcode']) . "',
			'" . $ilance->GPC['domestic'] . "',
			'" . $ilance->GPC['international'] . "',
			'" . $ilance->db->escape_string($ilance->GPC['carrier']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['basefee']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['addedfee']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['staffsettings'] . '?cmd=shipping');
		exit();
	}	
	
	// #### REMOVE SHIPPING PARTNER ################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-shipper' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "shippers
			WHERE shipperid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['staffsettings'] . '?cmd=shipping');
		exit();
	}
	
	// #### UPDATE SHIPPING PARTNERS ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippers')
	{
		foreach ($ilance->GPC['title'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET title = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET domestic = '0', international = '0'
		");
		
		foreach ($ilance->GPC['domestic'] AS $shipperid => $value)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET domestic = '" . intval($value) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['international'] AS $shipperid => $value)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET international = '" . intval($value) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['shipcode'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET shipcode = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['carrier'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET carrier = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['basefee'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET basefee = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['addedfee'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET addedfee = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}

		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['staffsettings'] . '?cmd=shipping');
		exit();
	}
				
				// #### SHIPPING PARTNERS ######################################
	$show['no_shippers_rows'] = true;
	$sql = $ilance->db->query("
		SELECT shipperid, title, shipcode, domestic, international, carrier, basefee, addedfee
		FROM " . DB_PREFIX . "shippers
		ORDER BY shipperid ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['no_shippers_rows'] = false;
		
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$row['class'] = ($row['international']) ? 'alt1' : 'alt1';				
			$row['action'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=shipping&amp;subcmd=remove-shipper&amp;id=' . $row['shipperid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['title'] = '<input type="text" name="title[' . $row['shipperid'] . ']" value="' . stripslashes($row['title']) . '" class="input" style="width:320px" />';
			$row['shipcode'] = '<input type="text" name="shipcode[' . $row['shipperid'] . ']" value="' . stripslashes($row['shipcode']) . '" class="input" style="width:60px; text-align:center" />';
			$row['domestic'] = '<input type="checkbox" name="domestic[' . $row['shipperid'] . ']" value="1" ' . ($row['domestic'] ? 'checked="checked"' : '') . ' />';
			$row['international'] = '<input type="checkbox" name="international[' . $row['shipperid'] . ']" value="1" ' . ($row['international'] ? 'checked="checked"' : '') . ' />';
			
			$row['carrier'] = '<input type="text" name="carrier[' . $row['shipperid'] . ']" value="' . stripslashes($row['carrier']) . '" class="input" style="width:60px" />';
			$row['basefee'] = '<input type="text" name="basefee[' . $row['shipperid'] . ']" value="' . stripslashes($row['basefee']) . '" class="input" style="width:60px" />';
			$row['addedfee'] = '<input type="text" name="addedfee[' . $row['shipperid'] . ']" value="' . stripslashes($row['addedfee']) . '" class="input" style="width:60px" />';
			
			$shippers[] = $row;
			$row_count++;
		}
	}
				
				$pprint_array = array('configuration_shippingsettings','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_shipping.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array('shippers'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'dailydeals')
        {
				
			$coin_id = $ilance->GPC['coin_id'];
			$hiddenfieldsubcmd = 'add-dailydeal';
			$hiddendo = $hiddenid = '';
				$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';

						$day = date('d');
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected='selected'>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';

				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected='selected'>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected='selected'>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				$Type = '<select name="offertype"><option value="dollar">$</option></select>';
				
				$area_title = $phrase['_daily_deals'];
                $page_title = SITE_NAME . ' - ' . $phrase['_daily_deals'];
				
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-dailydeals' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                      
					    $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "dailydeal
                                WHERE dailydeal_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success("Selected Daily Deal is deleted Successfully", $ilpage['settings'] . '?cmd=dailydeals');
                        exit();
                }
				// #### DELETE A  Daily Deal END #####################################
				
								
				 // #### ADD NEW  Daily Deal #####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-dailydeal' AND !empty($ilance->GPC['nameofthedeal']) AND !empty($ilance->GPC['coin_id']))
                {
				
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        	$validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
							$validdateend = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'].' '.'23:59:00';
							
						
						}
						else
						{
						  $validdate = '0000-00-00';
						}
					
						 //$product = '1';
						// $ilance->GPC['coin_id'] = '5';
						
						$product = $ilance->GPC['coin_id'];
						 
						 $checkpromo = $ilance->db->query("SELECT * FROM ".DB_PREFIX."dailydeal
						 									WHERE deal_name = '".$ilance->GPC['nameofthedeal']."' ");
						 
						if($ilance->db->num_rows($checkpromo) > 0 )
						{
						 print_action_failed(" Daily Deal Is Already Added ", $ilpage['settings'] . '?cmd=dailydeals');
                      	  exit();
						}
						else
						{
					   //kkk dailydeal insert
						$act_amount = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '$product'", "Buy_it_now");
						$enddates = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '$product'", "End_Date");
					    $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "dailydeal
                                (deal_name, offer_type, offer_amt, live_date, coin_id, notes, act_amount, enddate)
                                VALUES (
                                '" . $ilance->db->escape_string($ilance->GPC['nameofthedeal']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['offertype']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['offeramount']) . "',								
								'".$ilance->db->escape_string($validdate)."',		
								'" . $product. "',													
								'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
								'" . $act_amount . "',
								'" . $enddates . "'
                                )
                        ");
						  $lastid = $ilance->db->insert_id();
						
						// murugan changes on Mar 31 for Update project as daily deal
						
						$sel_pjt = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
														WHERE project_id = '".$product."'");
						if($ilance->db->num_rows($sel_pjt) > 0)
						{
							$row_value = $ilance->db->fetch_array($sel_pjt);
							$cal = $ilance->db->query("SELECT offer_type, offer_amt FROM ".DB_PREFIX."dailydeal
						 												WHERE coin_id = '".$product."' ");
												
							$calamt = $ilance->db->fetch_array($cal);
							if($calamt['offer_type'] == 'percentage')
							{												
								 $discount = $row_value['buynow_price'] * ($calamt['offer_amt'] / 100);												
								 $buynow = $row_value['buynow_price'] -  $discount;
							}
							if($calamt['offer_type'] == 'dollar')
							{
								$buynow = $row_value['buynow_price'] - $calamt['offer_amt'] ;
							}
							
							$ilance->db->query("UPDATE " . DB_PREFIX . "projects 
												SET buynow_price = '".$buynow."',
												date_end = '".$ilance->db->escape_string($validdateend)."'
												WHERE project_id = '".$row_value['project_id']."'");
							
							$update_deal = $ilance->db->query("
												UPDATE  " . DB_PREFIX . "dailydeal
												SET  project_id = '".$row_value['project_id']."'												     
												WHERE coin_id 	  =  '" .$product. "'												
												");
								
						}
						
						else
						{
								$insert_value = $ilance->db->query("
										SELECT  *
										FROM " . DB_PREFIX . "coins 
										WHERE coin_listed = 'c'
										AND coin_id  = '".$product."'
										AND Site_Id = '0'
										");
										if($ilance->db->num_rows($insert_value) > 0)
										{
											while($row_value = $ilance->db->fetch_array($insert_value))
											{
											// Murugan Changes On Dec 18 For Category questions
											$custom=array();
											
											
											$custom['1']['Cac'] = $row_value['Cac'];
											$custom['2']['Star'] = $row_value['Star'];
											$custom['3']['Plus'] = $row_value['Plus'];
											$custom['4']['Grading_Service'][] = $row_value['Grading_Service'];
											$custom['5']['Grade'][] = $row_value['Grade'];											
											$custom['6']['Condition_Attribute'][] = $row_value['Condition_Attribute'];
											$custom['7']['Coin_Series'] = $row_value['Coin_Series'];
											$custom['8']['Pedigee'][] = $row_value['Pedigee'];
											$custom['9']['Alternate_inventory_No'] = $row_value['Alternate_inventory_No'];
											$custom['10']['Certification_No'] = $row_value['Certification_No'];				
											$custom['11']['Other_information'][] = $row_value['Other_information'];																						
											$kill=array('custom'=>$custom);
											// Murugan Changes On Dec 18 For Category questions End 
											$pro_count = $ilance->db->query("SELECT COUNT(*) AS project_count FROM " . DB_PREFIX . "projects");
											$pro_count_res = $ilance->db->fetch_array($pro_count);	
											$count_set = $pro_count_res['project_count'];		 
											$insert_count = $count_set+1;
											$dataexplode = explode('-', $row_value['End_Date']);
											$date_coin = $dataexplode['0'] .'-'.$dataexplode['1'].'-'.$dataexplode['2'];
											//insert project	
											// murugan changes on jan 25
											$cal = $ilance->db->query("SELECT offer_type, offer_amt FROM ".DB_PREFIX."dailydeal
						 												WHERE coin_id = '".$product."' ");
												
											$calamt = $ilance->db->fetch_array($cal);
											if($calamt['offer_type'] == 'percentage')
											{												
												 $discount = $row_value['Buy_it_now'] * ($calamt['offer_amt'] / 100);												
												 $buynow = $row_value['Buy_it_now'] -  $discount;
											}
											if($calamt['offer_type'] == 'dollar')
											{
												$buynow = $row_value['Buy_it_now'] - $calamt['offer_amt'] ;
											}																			
																						
											//murugan changes on jan 19
											// #### SHIPPING INFORMATION ###################################
											$shipping1 = array(
												'ship_method' => 'flatrate',
												'ship_packagetype' =>  '',
												'ship_length' => '12',
												'ship_width' =>  '12',
												'ship_height' => '12',
												'ship_weightlbs' =>  '1',
												'ship_weightoz' =>  '0',
												'ship_handlingtime' =>  '3',
												'ship_handlingfee' =>  '0.00'
											);
											
											for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
											{
												$shipping2['ship_options_' . $i] =  '';
												$shipping2['ship_service_' . $i] =  '';
												$shipping2['ship_fee_' . $i] =  '0.00';
												$shipping2['freeshipping_' . $i] =  '0';
												$shipping2['ship_options_custom_region_' . $i] =  array();
											}
											
											$shipping = array_merge($shipping1, $shipping2);
											
											unset($shipping1, $shipping2);
										    
											//new change may5
										// murugan changes on Jan 19
									$checkin = $ilance->db->query("SELECT project_id FROM " . DB_PREFIX . "projects WHERE project_id = '".$row_value['coin_id']."'");
											if($ilance->db->num_rows($checkin) > 0)
											{
												$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects
																			   WHERE project_id = '" . intval($row_value['coin_id']) . "'");
											}
											$order_nos = fetch_cat('Orderno',$row_value['pcgs']); 
											//new change jan04
											$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects
																		(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,Orderno,bold,highlite,featured)
																		VALUES (
																		NULL,
																		'".$row_value['coin_id']."',
																		'".$row_value['Category']."',
																		'".$row_value['Description']."',
																		'".DATETIME24H."',
																		'".$ilance->db->escape_string($validdate)."',
																		'".$ilance->db->escape_string($validdateend)."',
																		'".$row_value['user_id']."',
																		'1',
																		'".$row_value['Title']."',
																		'open',
																		'public',
																		'forward',
																		'product',
																		'1',
																		'".$buynow."',
																		'".$row_value['Quantity']."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'fixed',
																		'".$buynow."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$row_value['Alternate_inventory_NO']."',
																		'1',
																		'".$order_nos."',
																		'".$row_value['bold']."',
																		'".$row_value['highlite']."',
																		'".$row_value['featured']."'																		
																		)");
												
												
												//murugan changes on jan 19
												if (isset($shipping) AND is_array($shipping))
												{
												$ilance->auction = construct_object('api.auction');
												$ilance->auction_rfp = construct_object('api.auction_rfp');
												$ilance->auction_rfp->save_item_shipping_logic($insert_count, $shipping);
												
												}										
												// Murugan Changes On Dec 18 For Category questions
												if (isset($custom) AND is_array($custom))
												{
													 $ilance->auction = construct_object('api.auction');
													 $ilance->auction_post = construct_object('api.auction_post');
												 // process our answer input and store them into the datastore
                                       		 		 $ilance->auction_post->process_custom_questions1($custom, $row_value['coin_id'], 'product');
                               			 		}
												
												// murugan changes on jan 31	
												build_category_count($row_value['Category'], 'add', "insert_product_auction(): adding increment count category id '".$row_value['Category']."'");
												
												// Murugan Changes On Dec 18 For Category questions End
												
												//update coin table						
												$con_insert_cointable = $ilance->db->query("
												UPDATE  " . DB_PREFIX . "coins
												SET  project_id = '".$row_value['coin_id']."',
												     status = '0'
												WHERE coin_id 	  =  '" .$product. "'
												AND project_id = '0'
												");
												
												$update_deal = $ilance->db->query("
												UPDATE  " . DB_PREFIX . "dailydeal
												SET  project_id = '".$row_value['coin_id']."'												     
												WHERE coin_id 	  =  '" .$product. "'												
												");
												
												//invoice and listing fees
												$my_var_in = insertion_fee_transaction_new($row_value['listing_fee'], 'product', $buynow, $row_value['coin_id'], $row_value['user_id']);                                 
												//update attachment userid and catergoryid
								                $attach_concumer_sql = $ilance->db->query("SELECT *
														FROM " . DB_PREFIX . "attachment
														WHERE coin_id = '".$product."'
							
														");
														while($row_value_new = $ilance->db->fetch_array($attach_concumer_sql))
											             {
						
								               $attach_concumer = $ilance->db->query("UPDATE " . DB_PREFIX . "attachment
																	  SET project_id     = '".$row_value['coin_id']."'
																		  
																	  WHERE coin_id = '".$product."'
																	  ");
														   }
												
												
				
											}
										}
						}
                        print_action_success("The New Daily Deal was Added Successfully", $ilpage['settings'] . '?cmd=dailydeals');
                        exit();
						}
                }	
				
				// ####  NEW Daily Deal END #####################################
				
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-dailydeals' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND empty($ilance->GPC['do']))
                {
                     	$hiddenfieldsubcmd = 'update-dailydeals';
                        $hiddendo = '<input type="hidden" name="do" value="update" />';
                        $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
                        $showdate['show']= 'datedisp';
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "dailydeal
                                WHERE dailydeal_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
						//'promoCode','offerAmt','conditionAmt','itemID','userID','notes',
                                $res = $ilance->db->fetch_array($sql);
								$nameofthedeal = $res['deal_name'];
								$offerType = $res['offer_type'];								
								$offerAmt = $res['offer_amt'];	
								$coin_id = $res['coin_id'];								
								$notes = $res['notes'];
								
								
								if($offerType == 'percentage')
								{
							
								  $Type = '<select name="offertype"><option value="dollar" >$</option></select>';
								}
								else if($offerType == 'dollar')
								{
								 $Type = '<select name="offertype"><option value="dollar" selected="selected" >$</option></select>';
								}								
							
								$dateofbirth = $res['live_date'];
                                $dobsplit = explode('-', $dateofbirth);
                                $year = $dobsplit[0];
                                $dobmonth = $dobsplit[1];
                                $dobday = $dobsplit[2];
								
								$daylist ='<select name="day" id="day"><option value="">DATE</option>';
																	
									for($i=1; $i<=31; $i++)
									if($dobday == $i)
									$daylist .= "<option value='$i' selected>$i</option>";
									else
									$daylist .= "<option value='$i'>$i</option>";
								
								$daylist .='</select>';
								
								$monthlist ='<select name="month" id="month"><option value="">MONTH</option>';									
									
									for($j=1; $j<=12; $j++)
									
									if($dobmonth == $j)
									$monthlist .= "<option value='$j' selected>$j</option>";
									else
									$monthlist .= "<option value='$j'>$j</option>";
								
								
								$monthlist .= '</select>';
								
								$yearlist = '<select name="year" id="year"><option value="">YEAR</option>';
								
									
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($year == $k)
									$yearlist .= "<option value='$k' selected>$k</option>";
									else
									$yearlist .= "<option value='$k'>$k</option>";
								
								$yearlist .='</select>';
                                
                                
                        }
                }
				
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-dailydeals' AND !empty($ilance->GPC['nameofthedeal']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
                       
					   ////promoID, promoCode, offerType, offerAmt, conditionAmt, validDate, categoryID, salesType, itemID, userID, notes
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}
							//dailydeal_id, deal_name, offer_type, offer_amt, live_date, item_id, notes		
						$ilance->db->query("
                                UPDATE " . DB_PREFIX . "dailydeal
                                SET deal_name = '" . $ilance->db->escape_string($ilance->GPC['nameofthedeal']) . "',
                                offer_type = '" . $ilance->db->escape_string($ilance->GPC['offertype']) . "',
								offer_amt = '" . $ilance->db->escape_string($ilance->GPC['offeramount']) . "',								
								live_date = '".$ilance->db->escape_string($validdate)."',																
                                notes = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
                                WHERE dailydeal_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success("The Selected Daily Deal Was Updated Successfully", $ilpage['settings'] . '?cmd=dailydeals');
                        exit();         
                }
				
				$show['no_dailydeal'] = false;
                $row_count = 0;
				
				//big id 1347
			$counter = ($ilance->GPC['page'] - 1) * 5;
	       $scriptpageprevnext = 'settings.php?cmd=dailydeals';
	      if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	       {
	        $ilance->GPC['page'] = 1;
	       }
	      else
		   {
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		   }
				
					     $sql1= $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "dailydeal
                        ORDER BY dailydeal_id DESC
                         ");
				
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "dailydeal
                        ORDER BY dailydeal_id DESC  LIMIT " . (($ilance->GPC['page'] - 1) * 5) . "," . '5'."
                ");
				
				$number = $ilance->db->num_rows($sql1);
				if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['dailydeal'] = true;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';

                                if($res['live_date'] == date('Y-m-d'))
								{
									$res['edit'] = '<span class="blue">In Live</span>';
									$res['remove'] ='';
								}
								else
								{
									$res['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=dailydeals&amp;subcmd=update-dailydeals&amp;id=' . $res['dailydeal_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                    $res['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=dailydeals&amp;subcmd=remove-dailydeals&amp;id=' . $res['dailydeal_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
								}
								                                                    
								if($res['offer_type']=='dollar')
								{								  
								   $res['offer_type'] = '$';
								}
								if($res['offer_type']=='percentage')
								{
								   $res['offer_type'] = '%';
								}
								if($res['live_date'] == '0000-00-00')
								{
								   $res['live_date'] = 'No Limit';
								}
								if($res['live_date'] != '0000-00-00')
								{
								$dobsplit = explode('-', $res['live_date']);
                                $year = $dobsplit[0];
                                $month = $dobsplit[1];
                                $day = $dobsplit[2];
								$res['live_date'] = $month . '-' . $day . '-' .$year;
								}
								$res['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=dailydeals&amp;subcmd=update-dailydeals&amp;id=' . $res['dailydeal_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                        $res['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=dailydeals&amp;subcmd=remove-dailydeals&amp;id=' . $res['dailydeal_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
								
								
								 
                                $dailydeal[] = $res;
                                $row_count++;
                        }        
                }
				else
				{
				
				$show['no_dailydeal'] = false;
				}
		//3066
				$buynow_listing = $ilance->db->query("SELECT c.coin_id,c.Title,c.Buy_it_now,u.username,ct.denomination_long,ct.denomination_unique_no
							FROM " . DB_PREFIX . "coins c
							LEFT JOIN " . DB_PREFIX . "users u ON c.user_id=u.user_id
							LEFT JOIN " . DB_PREFIX . "catalog_coin cc ON c.pcgs=cc.PCGS
							LEFT JOIN " . DB_PREFIX . "catalog_toplevel ct ON ct.denomination_unique_no = cc.coin_series_denomination_no
							WHERE c.coin_listed = 'c'							
							AND (c.End_Date = '0000-00-00' OR c.pending = '1')							
							AND c.project_id  = '0'
							AND c.status = '0'
							AND (c.user_id = 101 OR c.user_id = 13115)
							AND Buy_it_now > 0	
							ORDER BY ct.denomination_long ASC
							");
				if($ilance->db->num_rows($buynow_listing) > 0){				
				
					while($buynow_res=$ilance->db->fetch_array($buynow_listing))							
					{
						$buynow_res['class_type']= $buynow_res['denomination_unique_no'];
						$buynow_res['user_name']= $buynow_res['username'];
						$buynow_res['item_id']= $buynow_res['coin_id'];
						$buynow_res['item_title']= $buynow_res['Title'];
						$buynow_res['old_price']= $ilance->currency->format($buynow_res['Buy_it_now']);
					
						$discount_range_1=explode("-",'50-100-5');
						$discount_range_2=explode("-",'101-200-10');
						$discount_range_3=explode("-",'201-400-20');
						
						switch($buynow_res['Buy_it_now']){
							case ($buynow_res['Buy_it_now'] >= 0 && $buynow_res['Buy_it_now'] <= $discount_range_1[1]) :
								$buynow_res['offer_price']= $buynow_res['Buy_it_now'] - $discount_range_1[2];
								$offer_amount=$discount_range_1[2];
								break;
							
							case ($buynow_res['Buy_it_now'] >= $discount_range_2[0] && $buynow_res['Buy_it_now'] <= $discount_range_2[1]) :
								$buynow_res['offer_price']= $buynow_res['Buy_it_now'] - $discount_range_2[2];
								$offer_amount=$discount_range_2[2];
								break;
							
							case ($buynow_res['Buy_it_now'] >= $discount_range_3[0] ) :
								$buynow_res['offer_price']= $buynow_res['Buy_it_now'] - $discount_range_3[2];
								$offer_amount=$discount_range_3[2];
								break;
							default:
								$buynow_res['offer_price']= $buynow_res['Buy_it_now'];
								$offer_amount=0;
						}
						
						$buynow_res['offer_price']=$ilance->currency->format($buynow_res['offer_price']);						
						
						$buynow_res['category']= $buynow_res['denomination_long'];
						
						$buynow_res['select_box']= '<input type="checkbox" class="daily_deal_cls" name="daily_deal_select[]" value="'.$buynow_res['coin_id'].'_'.$offer_amount.'"/>';
						
						$dailydeal_buynow[]=$buynow_res;
						
						$categoty_arr[]=$buynow_res['denomination_unique_no'];
						$enddates = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = ".$buynow_res['coin_id']."", "End_Date");
					
					}
					$categoty_arr=implode('|',array_unique($categoty_arr));
					$show['buynow_pending_listing']='true';
				}
				else{
				
					$categoty_arr='';
					$show['buynow_pending_listing']='false';
				}
				
				
				
				//3066
				
				$prof = print_pagnation($number, 5, $ilance->GPC['page'], $counter, $scriptpageprevnext);
				$pprint_array = array('categoty_arr','prof','coin_id','hiddenfieldsubcmd','hiddendo','hiddenid','nameofthedeal','username','list_status','end_date','binprice','fvfvalue','offerAmt','notes','Type','daylist','monthlist','yearlist','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_daily_deals.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage)); 
				$ilance->template->parse_loop('main', array('dailydeal'));
				$ilance->template->parse_loop('main', array('dailydeal_buynow'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		//3066 action
		else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'daily_deal_create_new'){
						
			$checkpromo = $ilance->db->query("SELECT * FROM ".DB_PREFIX."dailydeal
						 									WHERE deal_name = '".$ilance->GPC['nameofthedeal']."' ");
						 
			if($ilance->db->num_rows($checkpromo) > 0 )
			{
				print_action_failed(" Daily Deal Is Already Added ", $ilpage['settings'] . '?cmd=dailydeals');
				exit();
			}
			
			$date= new DateTime(date('Y-m-d'));
			$date->modify('+2 day');			
			$live_date = $date->format('Y-m-d');		
			$deal_end_date = $date->format('Y-m-d').' 23:59:00';			
			$offertype="dollar";
			$daily_deal_arr=$ilance->GPC['daily_deal_select'];
			foreach($daily_deal_arr as $daily_deal_coin_value){
				$daily_deal_coin_value=explode("_",$daily_deal_coin_value);
				$product = $daily_deal_coin_value[0];
				$offeramount = $daily_deal_coin_value[1];
				
				$act_amount = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '$product'", "Buy_it_now");
				$act_end_date = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '$product'", "End_Date");				
				
				$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "dailydeal
							(deal_name, offer_type, offer_amt, live_date, coin_id, notes, act_amount, enddate)
							VALUES (
							'" . $ilance->db->escape_string($ilance->GPC['nameofthedeal']) . "',
							'" . $ilance->db->escape_string($offertype) . "',
							'" . $ilance->db->escape_string($offeramount) . "',								
							'".$ilance->db->escape_string($live_date)."',		
							'" . $product. "',													
							'',
							'" . $act_amount . "',
							'" . $act_end_date . "'
							)
					");
					
				$con_insert_cointable = $ilance->db->query("
							UPDATE  " . DB_PREFIX . "coins
							SET End_Date='".$deal_end_date."',
								 pending=0								 
							WHERE coin_id 	  =  '" .$product. "'
							AND project_id = '0'
							");
					
				$result_arr[]=$product;				
			}
			
			
			print_action_success("The New Daily Deal was Added Successfully for items ".implode(",",$result_arr).", This Daily Deal will be live by a day after tomorrow at 12:00am", $ilpage['settings'] . '?cmd=dailydeals');
			exit();			
			
		
		}
		//3066 action
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'searchpage')
        {
				$area_title = $phrase['_search_page'];
                $page_title = SITE_NAME . ' - ' . $phrase['_search_page'];
				
				//start
				$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
                        $sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', $sortmode);
				// #### TIMER TO PREVENT SEARCH FLOODING #######################################
$show['searcherror'] = $searchwaitleft = 0;
$searchwait = $ilconfig['searchflooddelay'];

if (!empty($ilance->GPC['mode']))
{
        if ($ilconfig['searchfloodprotect'] AND isset($ilance->GPC['q']) AND $ilance->GPC['q'] != '')
        {
                if (empty($_SESSION['ilancedata']['user']['searchexpiry']))
                {
                        // start timer
                        $_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
                }
                else
                {
                        if (($timeexpired = TIMESTAMPNOW - $_SESSION['ilancedata']['user']['searchexpiry']) < $searchwait AND $searchwait != 0)
                        {
                                $show['searcherror'] = 1;
                                $searchwaitleft = ($searchwait - $timeexpired);
                        }
                        else
                        {
                                // restart timer
                                $_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
                        }
                }
        }
        
        ($apihook = $ilance->api('search_mode_condition_end')) ? eval($apihook) : false;
}

// #### SEARCH ENGINE HANDLER ##################################################
$sqlquery = array();

// construct our common classes
$ilance->auction = construct_object('api.auction');
$ilance->categories_skills = construct_object('api.categories_skills');
$ilance->categories_pulldown = construct_object('api.categories_pulldown');
$ilance->distance = construct_object('api.distance');
$ilance->subscription = construct_object('api.subscription');
$ilance->feedback = construct_object('api.feedback');
$ilance->feedback_rating = construct_object('api.feedback_rating');  
$ilance->auction_post = construct_object('api.auction_post');                      

// #### build our service category cache #######################################
$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
$ilance->categories->catservicefetch = $ilance->categories->fetch;

// #### selected category id ###################################################
$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;

// #### print multiple selection category menu #################################
$service_category_selection = $product_category_selection = $provider_category_selection = $search_category_pulldown = '';
if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
{
        $service_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng']);
        $provider_category_selection = $ilance->categories_pulldown->print_cat_pulldown(0, 'service', 'levelmultisearch', 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '350px', $uid = 0, $forcenocount = 0, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $ilance->categories->cats, $onclickjs = true);
	if (isset($ilance->GPC['mode']) AND ($ilance->GPC['mode'] == 'service' OR $ilance->GPC['mode'] == 'experts'))
	{
		$search_category_pulldown = $service_category_selection;
	}
}

$profilebidfilters = '<div id="profile_filters_text">' . $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service') . '</div>';

// build our product category cache
$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
$ilance->categories->catproductfetch = $ilance->categories->fetch;

if ($ilconfig['globalauctionsettings_productauctionsenabled'])
{
	// #### require shipping backend #######################################
	require_once(DIR_CORE . 'functions_shipping.php');
	$drop_sql=$ilance->db->query("SELECT coin_series_unique_no,coin_series_name FROM ilance_catalog_second_level order by coin_series_sort");
$product_category_selection.='<select name="series"><option value="">All Categories</option><option value="">------------------------------------------------</option>';
while($drop_line=$ilance->db->fetch_array($drop_sql))
{
if(isset($ilance->GPC['series']) and $ilance->GPC['series']>0 and $drop_line['coin_series_unique_no']==$ilance->GPC['series'])
$product_category_selection.= '<option value="'.$drop_line['coin_series_unique_no'].'" selected="selected">'.$drop_line['coin_series_name'].'</option>';
else
$product_category_selection.= '<option value="'.$drop_line['coin_series_unique_no'].'">'.$drop_line['coin_series_name'].'</option>';
}
$product_category_selection.='</select>';
        //$product_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['ilancedata']['user']['slng']);
	if (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product')
	{
		$search_category_pulldown = $product_category_selection;
	}
}

if (!empty($ilance->GPC['mode']) AND $show['searcherror'] == 0)
{
        // #### PREPARE DEFAULT URLS ###########################################
        $scriptpage = HTTP_SERVER . $ilpage['staffsettings'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	
	// remove unwanted url vars ############################################
	$searchid = isset($ilance->GPC['searchid']) ? intval($ilance->GPC['searchid']) : '';
	$list = isset($ilance->GPC['list']) ? $ilance->GPC['list'] : '';
	
	$pageurl = rewrite_url(PAGEURL, 'searchid=' . $searchid);
	$pageurl = rewrite_url($pageurl, 'list=' . $list);
	
        $php_self = ($ilconfig['globalauctionsettings_seourls']) ? $pageurl : $scriptpage;
        $php_self_urlencoded = ($ilconfig['globalauctionsettings_seourls']) ? urlencode($pageurl) : urlencode($php_self);
        
        define('PHP_SELF', $php_self);
        unset($pageurl);
		
		
	
        $show['widescreen'] = true;
        
}
				// #### ADVANCED SEARCH MENU ###################################################



$search_bidrange_pulldown_service = print_bid_range_pulldown('', 'bidrange', 'servicebidrange', 'pulldown');
$search_bidrange_pulldown_product = print_bid_range_pulldown('', 'bidrange', 'productbidrange', 'pulldown');
$search_awardrange_pulldown = print_award_range_pulldown('', 'projectrange', 'projectrange', 'pulldown');
$search_ratingrange_pulldown = print_rating_range_pulldown('', 'rating', 'rating');

if (isset($ilance->GPC['country']))
{
        $country = $ilance->GPC['country'];
}
else
{
        $country = !empty($_SESSION['ilancedata']['user']['country']) ? $_SESSION['ilancedata']['user']['country'] : 'all';
}

$search_country_pulldown_experts = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountry', true);
$availableto_pulldown_experts    = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountryto', true);
$locatedin_pulldown_experts      = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountryin', true);
$region_pulldown_experts         = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'expertregionin', 'pulldown', $onchange = true, '3');

$search_country_pulldown_service = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountry', true);
$availableto_pulldown_service    = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountryto', true);
$locatedin_pulldown_service      = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountryin', true);
$region_pulldown_service         = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'serviceregionin', 'pulldown', $onchange = true, '1');

$search_country_pulldown_product = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountry', true);
$availableto_pulldown_product    = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountryto', true);
$locatedin_pulldown_product      = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountryin', true);
$region_pulldown_product         = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'productregionin', 'pulldown', $onchange = true, '2');

//$radiuscountry = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all';
if (isset($ilance->GPC['radiuscountry']) AND $ilance->GPC['radiuscountry'] > 0)
{
        $radiuscountry = $ilance->GPC['radiuscountry'];
}
else
{
        $radiuscountry = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all';
}

//$search_radius_country_pulldown_experts = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertradiuscountry');
//$search_radius_country_pulldown_service = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'serviceradiuscountry');
//$search_radius_country_pulldown_product = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productradiuscountry');

if (isset($ilance->GPC['q']))
{
        if (!empty($ilance->GPC['q']))
        {
                $q = ilance_htmlentities($ilance->GPC['q']);
        }
}

($apihook = $ilance->api('search_menu_start')) ? eval($apihook) : false;

$searcherror = $ilance->language->construct_phrase($phrase['_we_require_that_you_wait_x_seconds_between_searches_please_try_again_in_x_seconds'], array($searchwait, $searchwaitleft));

// #### SEARCH OPTIONS #########################################################
$js_start = print_searchoptions_js();

// #### SEARCH OPTION CONTROLS #################################################
$perpage = print_perpage_searchoption();
$colsperrow = print_colsperrow_searchoption();
$sortpulldown = print_sort_pulldown();

$cb_username = print_checkbox_status('username');
$cb_latestfeedback = print_checkbox_status('latestfeedback');
$cb_online = print_checkbox_status('online');
$cb_description = print_checkbox_status('description');
$cb_icons = print_checkbox_status('icons');
$cb_currencyconvert = print_checkbox_status('currencyconvert');
$cb_displayfeatured = print_checkbox_status('displayfeatured');
$cb_hidelisted = print_checkbox_status('hidelisted');
$cb_hideverbose = print_checkbox_status('hideverbose');
$cb_proxybit = print_checkbox_status('proxybit');
$rb_showtimeas_static = print_time_static_radiobox_status();
$rb_showtimeas_flash = print_time_flash_radiobox_status();
$rb_list_gallery = print_list_gallery_radiobox_status();
$rb_list_list = print_list_list_radiobox_status();

// #### SAVING SEARCH OPTIONS ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'saveoptions')
{
        if (is_array($ilance->GPC))
        {
                $options = array();
                foreach ($ilance->GPC AS $search => $option)
                {
                        if ($search != 'defaultupdate' AND $search != 'membersupdate' AND $search != 'tab' AND $search != 'search' AND $search != 'cmd' AND $search != 'returnurl' AND $search != 'redirect')
                        {
                                $options["$search"] = $option;
                        }
                }
                if (empty($options['online']))
                {
                        $options['online'] = 'false';
                }
                if (empty($options['latestfeedback']))
                {
                        $options['latestfeedback'] = 'false';
                }
                if (empty($options['username']))
                {
                        $options['username'] = 'false';
                }
                if (empty($options['description']))
                {
                        $options['description'] = 'false';
                }
                if (empty($options['icons']))
                {
                        $options['icons'] = 'false';
                }
                if (empty($options['currencyconvert']))
                {
                        $options['currencyconvert'] = 'false';
                }
                if (empty($options['displayfeatured']))
                {
                        $options['displayfeatured'] = 'false';
                }
                if (empty($options['hidelisted']))
                {
                        $options['hidelisted'] = 'false';
                }
                if (empty($options['hideverbose']))
                {
                        $options['hideverbose'] = 'false';
                }
                if (empty($options['proxybit']))
                {
                        $options['proxybit'] = 'false';
                }
                
                ($apihook = $ilance->api('search_saveoptions_submit_end')) ? eval($apihook) : false;
                
                $searchoptions = serialize($options);
                $uid = (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0;
                update_default_searchoptions($uid, $searchoptions);
                
                if (isset($ilance->GPC['defaultupdate']) AND $ilance->GPC['defaultupdate'] == 'true')
                {
                        update_default_searchoptions_guests($searchoptions);
                }
                
                if (isset($ilance->GPC['membersupdate']) AND $ilance->GPC['membersupdate'] == 'true')
                {
                        update_default_searchoptions_users($searchoptions);
                }
                
                if (!empty($ilance->GPC['returnurl']))
                {
                        refresh($ilance->GPC['returnurl']);
                        exit();
                }
                
                refresh($ilpage['staffsettings'] . '?tab=3');
                exit();
        }
        else
        {
                refresh($ilpage['login']);
                exit();      
        }
}

$show['widescreen'] = $show['leftnav'] = false;

$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
$sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', $expertsmode = true);
$returnurl = !empty($ilance->GPC['returnurl']) ? handle_input_keywords($ilance->GPC['returnurl']) : '';

// #### advanced search skills selector for experts ############################
$skills_selection = $ilance->categories_skills->print_skills_columns($_SESSION['ilancedata']['user']['slng'], $showcount = 1, $prepopulate = false);

$headinclude .= '
<script language="javascript" type="text/javascript">
<!-- 
function print_profile_filters()
{
	var ajaxRequest;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	} 
	catch (e)
	{
		// Internet Explorer Browsers
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) 
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (e)
			{
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			var ajaxDisplay = fetch_js_object(\'profile_filters_text\');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	
        var selected_cid = fetch_js_object(\'cid_list\').options[fetch_js_object(\'cid_list\').selectedIndex].value;
	var queryString = "&cid=" + selected_cid + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	
	ajaxRequest.open("GET", "' . HTTP_SERVER . 'ajax.php?do=profilefilters" + queryString, true);
	ajaxRequest.send(null); 
}
//-->
</script>';
				//end
				
				
				
				//saved search listing
				
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_list')
                {
				   $show['filter'] = 'myvalue';
				   
				    $con_listing = $ilance->db->query("

							SELECT *

							FROM " . DB_PREFIX . "staff_search where id='".$ilance->GPC['id']."'

							");
							
							$row_list = $ilance->db->fetch_array($con_listing);
							
							$pid = $ilance->GPC['id'];
							
							$ptitle = $row_list['title'];
							
							$pdesc = $row_list['description'];
							
							$purl =  $row_list['url'];
							
				
				}

		         $con_listing = $ilance->db->query("

							SELECT *

							FROM " . DB_PREFIX . "staff_search

							");

							$number = (int)$ilance->db->num_rows($con_listing);

							           if($ilance->db->num_rows($con_listing) > 0)

										{

										$row_con_list = 0;

										while($row_list = $ilance->db->fetch_array($con_listing))

										{

									       $row_list['delete'] = '<a onclick="return confirm(\'Please take a moment to confirm your action. Continue?\')" href="settings.php?cmd=delete_saved_my&id='.$row_list['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/delete.gif"></a>';
										   
										   
										   $row_list['edit'] = '<a href="settings.php?cmd=searchpage&subcmd=update_list&id='.$row_list['id'].'"><img border="0" alt="" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . '/icons/pencil.gif"></a>';


										$row_list['click'] = '<span class="blue"><a href="'.HTTPS_SERVER .'user_search.php?'.$row_list['url'].'&invitee='.$row_list['title'].'" target="_blank">Click</a></span>'; 
                                      	

										$saved_list[] = $row_list;

										$row_con_list++;

										

										}

										

						                }

										

										else

										{				

										$show['no'] = 'list';

										}

		

		

		

				
				
				$pprint_array = array('purl','pdesc','ptitle','pid','delete','click','number','ilanceversion','login_include_admin','colsperrow','region_pulldown_experts','region_pulldown_service','region_pulldown_product','locatedin_pulldown_experts','locatedin_pulldown_service','locatedin_pulldown_product','availableto_pulldown_experts','availableto_pulldown_service','availableto_pulldown_product','search_bidrange_pulldown_service','search_bidrange_pulldown_product','search_radius_country_pulldown_service','search_radius_country_pulldown_product','search_country_pulldown_service','search_country_pulldown_product','search_country_pulldown_experts','search_radius_country_pulldown_experts','provider_category_selection','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_displayfeatured','cb_hidelisted','cb_proxybit','cb_hideverbose','serviceavailable','serviceselected','productavailable','productselected','expertavailable','expertselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','wantads_category_selection','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','stores_category_selection','product_category_selection','service_category_selection','search_serviceauctions_img','search_serviceauctions_collapse','search_productauctions_img','search_productauctions_collapse','search_experts_collapse','search_experts_img','pfp_category_left','rfp_category_left','input_style','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_search_page.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array('saved_list','consignment_listing_search'));

				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		//alert area completed
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'alertarea')
        {
				$area_title = $phrase['_alert_area'];
                $page_title = SITE_NAME . ' - ' . $phrase['_alert_area'];
				
				$alertarea_pulldown = '<select name="locationid" style="font-family: verdana">';
                        
			$sql_alert = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "alert_location
                        ");
			while ($res_alert = $ilance->db->fetch_array($sql_alert))
                        {
				$sql_prefs = $ilance->db->query("
                                        SELECT locationid
                                        FROM " . DB_PREFIX . "alert_areas
                                        WHERE id = '" . intval($ilance->GPC['id']) . "'
                                ");
				$res_prefs = $ilance->db->fetch_array($sql_prefs);
								
				$alertarea_pulldown .= '<option value="' . $res_alert['id'] . '"';
								if ($res_alert['id'] == $res_prefs['locationid'])
                                { 
                                        $alertarea_pulldown .= ' selected="selected"';
                                }
                               
				$alertarea_pulldown .= '>' . $res_alert['location']. '</option>';
			}
			$alertarea_pulldown .= '</select>';
				
				$hiddenfieldsubcmdalert = 'add-alertarea';
				$hiddendo = $hiddenid = '';
				
				
				// #### DELETE A ALERT AREA ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-alertarea' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "alert_areas
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success($phrase['_the_selected_alert_area_was_removed_from_the_system'], $ilpage['staffsettings'] . '?cmd=alertarea');
                        exit();
                }
				
				// #### ADD A ALERT AREA ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-alertarea' AND !empty($ilance->GPC['message']))
                {
				//id, message, location
				$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "alert_areas
								WHERE locationid = '".$ilance->GPC['locationid']."'                              
                        ");
					if ($ilance->db->num_rows($sqlarea) > 0)
					{	
						 print_action_failed("Message Location Already Listed, Please Romove Message Or Modify Existing ", $ilpage['staffsettings'] . '?cmd=alertarea');
					}
					else
					{
					    $insarea = $ilance->db->query("INSERT INTO ".DB_PREFIX."alert_areas (message,locationid) 
														VALUES ('".$ilance->GPC['message']."','".$ilance->GPC['locationid']."')");
						print_action_success("Alert Area Message Created Successfully", $ilpage['staffsettings'] . '?cmd=alertarea');
					}
							
				
				}
				
				// #### UPDATE A ALERT AREA ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-alertarea' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdalert = 'update-alertarea';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "alert_areas
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $message = $res['message'];
                               // $location = $res['location'];
                                
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-alertarea' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
					
					    $ilance->db->query("
                                UPDATE " . DB_PREFIX . "alert_areas
                                SET message = '" . $ilance->db->escape_string($ilance->GPC['message']) . "'                                
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success($phrase['_the_alert_area_was_updated_successfully'], $ilpage['staffsettings'] . '?cmd=alertarea');
                        exit(); 
					        
                }
				
				$sqlalertarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "alert_areas 
                                
                        ");
				$number = (int)$ilance->db->num_rows($sqlalertarea);	
				if ($ilance->db->num_rows($sqlalertarea) > 0)
                {
                	$row_count = 0;
                    while ($resalert = $ilance->db->fetch_array($sqlalertarea, DB_ASSOC))
                    {	//id, 
						$resalert['message'] = $resalert['message']; 
						//$resalert['location'] = $resalert['locationid']; 
						
						$sqllocation = $ilance->db->query("
                                SELECT location
                                FROM " . DB_PREFIX . "alert_location
								WHERE id = '" . $resalert['locationid'] . "'
                                
                        ");
						
						$reslocation = $ilance->db->fetch_array($sqllocation, DB_ASSOC);
						
						$resalert['location'] = $reslocation['location'];
						$resalert['edit'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=alertarea&amp;subcmd=update-alertarea&amp;id=' . $resalert['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$resalert['remove'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=alertarea&amp;subcmd=remove-alertarea&amp;id=' . $resalert['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$alertarea[] = $resalert;
                        $row_count++;
					}
				
				//$prevnext = print_pagnation_new($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);	
				}
				else
				{
					$show['no_alertarea'] = true;
				}
				
				$pprint_array = array('message','alertarea_pulldown','hiddenfieldsubcmdalert','hiddenid','hiddendo','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_alert_area.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('alertarea'));
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		//affiliatelist completed
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'affiliatelist')
        {
				$area_title = $phrase['_affiliate_listing'];
                $page_title = SITE_NAME . ' - ' . $phrase['_affiliate_listing'];
				
				$hiddenfieldsubcmdaff = 'add-affiliatelist';
				$hiddendo = $hiddenid = '';
				
				// #### DELETE A affiliatelist  ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-affiliatelist' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "affiliate_listing
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success($phrase['_the_selected_site_name_was_removed_from_the_system'], $ilpage['staffsettings'] . '?cmd=affiliatelist');
                        exit();
                }
				
				// #### Add A affiliatelist  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-affiliatelist' AND !empty($ilance->GPC['sitename']))
                {
				//id, site_name
				
				$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "affiliate_listing
								WHERE site_name = '".$ilance->GPC['sitename']."'                              
                        ");
					if ($ilance->db->num_rows($sqlarea) > 0)
					{	
					print_action_failed("affiliate listing Code Already Listed, Please Romove affiliate listing Code Or Modify Existing ", $ilpage['staffsettings'] . '?cmd=affiliatelist');
					}
					else
					{
				 $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "affiliate_listing
                                (id, site_name)
                                VALUES (
                                NULL,
                                '" . $ilance->db->escape_string($ilance->GPC['sitename']) . "'
                               )
                        ");
				print_action_success($phrase['_the_site_name_detail_was_created_successfully'], $ilpage['staffsettings'] . '?cmd=affiliatelist');
				exit();
				}
				}
				
				// #### Update the  affiliatelist  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-affiliatelist' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdaff = 'update-affiliatelist';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "affiliate_listing
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $sitename = $res['site_name'];
                                
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-affiliatelist' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
					
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "affiliate_listing
                                SET site_name = '" . $ilance->db->escape_string($ilance->GPC['sitename']) . "'
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success($phrase['_the_selected_site_name_was_updated'], $ilpage['staffsettings'] . '?cmd=affiliatelist');
                        exit();         
                }
				
				//pagination ####################################################
				 $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
				$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
				
				$displayorderfields = array('asc', 'desc');
				$displayorder = '&amp;displayorder=asc';
				$currentdisplayorder = $displayorder;
				$displayordersql = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder = '&amp;displayorder=desc';
					$currentdisplayorder = '&amp;displayorder=asc';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder = '&amp;displayorder=asc';
					$currentdisplayorder = '&amp;displayorder=desc';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
				{
					$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage = $ilpage['staffsettings']. '?cmd=affiliatelist' . $currentdisplayorder . $orderby;
				
				//pagination ####################################################

				
				$sqlaffliate = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "affiliate_listing
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
                        );
				$sqlaffliate1 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "affiliate_listing
                                
                        ");		
				$number = (int)$ilance->db->num_rows($sqlaffliate1);	
				if ($ilance->db->num_rows($sqlaffliate) > 0)
                {
                	$row_count = 0;
                    while ($resaff = $ilance->db->fetch_array($sqlaffliate, DB_ASSOC))
                    {	//id, site_name
						$resaff['sitename'] = $resaff['site_name']; 
						$resaff['edit'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=affiliatelist&amp;subcmd=update-affiliatelist&amp;id=' . $resaff['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$resaff['remove'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=affiliatelist&amp;subcmd=remove-affiliatelist&amp;id=' . $resaff['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$affiliatelist[] = $resaff;
                        $row_count++;
					}
				
				$prevnext = print_pagnation_new($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);	
				}
				else
				{
					$show['no_affiliatelist'] = true;
				}
				
				$pprint_array = array('prevnext','sitename','hiddenfieldsubcmdaff','hiddenid','hiddendo','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_affiliate_list.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('affiliatelist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		
		//#############  PROMO CODE ADMINCP ############################
		################################################################
		######  Promo Code For Invoice And Shipping			############
		######  Herakle Murugan Coding Oct 19 Starts Here 	############
		################################################################
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'promocode')
        {
				$area_title = $phrase['_promocode'];
                $page_title = SITE_NAME . ' - ' . $phrase['_promocode'];
				
				$hiddenfieldsubcmd = 'add-invoicepromo';
				$hiddenfieldsubcmdship = 'add-shippingpromo';
				$hiddendo = $hiddenid = '';
				$product_category = '';
				// Date Month Year Starts Here 
				$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';

						$day = date('d');
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected='selected'>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';

				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected='selected'>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected='selected'>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				
				$shipdaylist = '';
				$shipmonthlist = '';
				$shipyearlist = '';
				$shipdaylist .='<select name="day" id="day"><option value="">DATE</option>';

						$day = date('d');
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$shipdaylist .= "<option value='$i' selected='selected'>$i</option>";
						else
						$shipdaylist .= "<option value='$i'>$i</option>";
	
				$shipdaylist .='</select>';
				
				$shipmonthlist .='<select name="month" id="month"><option value="">MONTH</option>';

				         $month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$shipmonthlist .= "<option value='$j' selected='selected'>$j</option>";
						else
						$shipmonthlist .= "<option value='$j'>$j</option>";
						
						
				$shipmonthlist .= '</select>';
				
				$shipyearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$shipyearlist .= "<option value='$k' selected='selected'>$k</option>";
					else
					$shipyearlist .= "<option value='$k'>$k</option>";
				
				$shipyearlist .='</select>';
				// Date Month Year End  Here 
				
				$Type = '<select name="offertype"><option value="percentage">%</option><option value="dollar">$</option></select>';
				$available = '<select name="shipavailable"><option value="international" selected="selected">USA and International</option><option value="usa">Only For USA</option></select>';
				$upgradelevel = '<select name="shipupgrade"><option value="NO">No Upgrade</option><option value="usps" selected="selected">USPS Express</option><option value="fedex">Fedex Overnight</option></select>';
				$salestype ='<select name="salestype">
									<option value="Both" selected="selected">BIN & Auction</option>
									<option value="Bin">BIN </option>
									<option value="Auction">Auction</option>
									</select>';
									// murugan added on NOv 2 11
				$product_category = '<select name="productcat" size = "5">';
				$product_category .='<option value ="0">All category</option>';
					$select = $ilance->db->query("SELECT * FROM ".DB_PREFIX."catalog_toplevel");
					while($res=$ilance->db->fetch_array($select))
					{
					  $product_category .='<option value ="'.$res['denomination_unique_no'].'">'.$res['denomination_long'].'</option>';
					}
				$product_category .=	'</select>';
				// #### DELETE A INVENTORY PROMO CODE ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-invoicepromo' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "promo_inventory
                                WHERE promoID = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success("Selected Inventory Promo Code is deleted Successfully", $ilpage['settings'] . '?cmd=promocode');
                        exit();
                }
				// #### DELETE A INVENTARY PROMO CODE END #####################################
				
								
				 // #### ADD NEW PROMO CODE #####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-invoicepromo' AND !empty($ilance->GPC['promocode']))
                {
				//promoID, promoCode, offerType, offerAmt, conditionAmt, validDate, categoryID, salesType, itemID, userID, notes
                     
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}
						if($ilance->GPC['productcat'] != 0)
						{
						// changes on nov 2
						$selecttop = $ilance->db->query("SELECT coin_series_unique_no,coin_series_denomination_no FROM ".DB_PREFIX."catalog_second_level where coin_series_denomination_no = '".$ilance->GPC['productcat']."'");
							while($restop=$ilance->db->fetch_array($selecttop))
							{
							  	$selectcatelog = $ilance->db->query("SELECT PCGS FROM ".DB_PREFIX."catalog_coin where coin_series_denomination_no = '".$restop['coin_series_denomination_no']."' and coin_series_unique_no = '".$restop['coin_series_unique_no']."'");
								while($rescatelog = $ilance->db->fetch_array($selectcatelog))
								{
									$pcgs[] = $rescatelog['PCGS']; 
								}
							}
							
						  $product = implode(",",$pcgs);
						 }
						 else
						 {
						 	$product = '';
						 }
						 $checkpromo = $ilance->db->query("SELECT * FROM ".DB_PREFIX."promo_inventory
						 									WHERE promoCode = '".$ilance->GPC['promocode']."' ");
						 
						if($ilance->db->num_rows($checkpromo) > 0 )
						{
						 print_action_failed(" Inventory Promo Code Is Already Added ", $ilpage['settings'] . '?cmd=promocode');
                      	  exit();
						}
						else
						{
					    $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "promo_inventory
                                (promoCode, offerType, offerAmt, conditionAmt,validDate,categoryID, salesType, itemID, userID, notes)
                                VALUES (
                                '" . $ilance->db->escape_string($ilance->GPC['promocode']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['offertype']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['offeramount']) . "',
								'" . $ilance->db->escape_string($ilance->GPC['condamt']) . "',
								'".$ilance->db->escape_string($validdate)."',		
								'" . $product. "',					
								'" . $ilance->db->escape_string($ilance->GPC['salestype']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['freeitem']) . "',
								 '" . $ilance->db->escape_string($ilance->GPC['userid']) . "',
								  '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
                                )
                        ");
                        
                        print_action_success("The New Inventory Promo Code was Added Successfully", $ilpage['settings'] . '?cmd=promocode');
                        exit();
						}
                }	
				
				// ####  NEW PROMO CODE END #####################################
				
				// ####UPDATE INVENTARY PROMO CODE START  #####################################
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-invoicepromo' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND empty($ilance->GPC['do']))
                {
                        $hiddenfieldsubcmd = 'update-invoicepromo';
                        $hiddendo = '<input type="hidden" name="do" value="update" />';
                        $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
                        $showdate['show']= 'datedisp';
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "promo_inventory
                                WHERE promoID = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
						//'promoCode','offerAmt','conditionAmt','itemID','userID','notes',
                                $res = $ilance->db->fetch_array($sql);
								$promoCode = $res['promoCode'];
								$offerType = $res['offerType'];								
								$offerAmt = $res['offerAmt'];
								$conditionAmt = $res['conditionAmt'];
								$categoryID = $res['categoryID'];
								$itemID = $res['itemID'];
								$userID = $res['userID'];
								$notes = $res['notes'];
								$salesType = $res['salesType'];
								
								if($offerType == 'percentage')
								{
							
								  $Type = '<select name="offertype"><option value="percentage" selected="selected">%</option><option value="dollar" >$</option></select>';
								}
								else if($offerType == 'dollar')
								{
								 $Type = '<select name="offertype"><option value="percentage">%</option><option value="dollar" selected="selected" >$</option></select>';
								}								
								if($salesType == 'Both')
								{
								$salestype ='<select name="salestype">
									<option value="Both" selected="selected">BIN & Auction</option>
									<option value="Bin">BIN </option>
									<option value="Auction">Auction</option>
									</select>';
								}
								if($salesType == 'Bin')
								{
								$salestype ='<select name="salestype">
									<option value="Both">BIN & Auction</option>
									<option value="Bin" selected="selected">BIN </option>
									<option value="Auction">Auction</option>
									</select>';
								}
								if($salesType == 'Auction')
								{
								$salestype ='<select name="salestype">
									<option value="Both">BIN & Auction</option>
									<option value="Bin">BIN </option>
									<option value="Auction" selected="selected">Auction</option>
									</select>';
								}
								
								
								$dateofbirth = $res['validDate'];
                                $dobsplit = explode('-', $dateofbirth);
                                $year = $dobsplit[0];
                                $dobmonth = $dobsplit[1];
                                $dobday = $dobsplit[2];
								
								$daylist ='<select name="day" id="day"><option value="">DATE</option>';
																	
									for($i=1; $i<=31; $i++)
									if($dobday == $i)
									$daylist .= "<option value='$i' selected>$i</option>";
									else
									$daylist .= "<option value='$i'>$i</option>";
								
								$daylist .='</select>';
								
								$monthlist ='<select name="month" id="month"><option value="">MONTH</option>';									
									
									for($j=1; $j<=12; $j++)
									
									if($dobmonth == $j)
									$monthlist .= "<option value='$j' selected>$j</option>";
									else
									$monthlist .= "<option value='$j'>$j</option>";
								
								
								$monthlist .= '</select>';
								
								$yearlist = '<select name="year" id="year"><option value="">YEAR</option>';
								
									
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($year == $k)
									$yearlist .= "<option value='$k' selected>$k</option>";
									else
									$yearlist .= "<option value='$k'>$k</option>";
								
								$yearlist .='</select>';
                                
                                
                        }
                }
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-invoicepromo' AND !empty($ilance->GPC['promocode']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
                       
					   ////promoID, promoCode, offerType, offerAmt, conditionAmt, validDate, categoryID, salesType, itemID, userID, notes
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}
									
						$ilance->db->query("
                                UPDATE " . DB_PREFIX . "promo_inventory
                                SET promoCode = '" . $ilance->db->escape_string($ilance->GPC['promocode']) . "',
                                offerType = '" . $ilance->db->escape_string($ilance->GPC['offertype']) . "',
								offerAmt = '" . $ilance->db->escape_string($ilance->GPC['offeramount']) . "',
								conditionAmt = '" . $ilance->db->escape_string($ilance->GPC['condamt']) . "',
								validDate = '".$ilance->db->escape_string($validdate)."',
								salesType = '" . $ilance->db->escape_string($ilance->GPC['salestype']) . "',
								itemID = '" . $ilance->db->escape_string($ilance->GPC['freeitem']) . "',
								userID = '" . $ilance->db->escape_string($ilance->GPC['userid']) . "',
                                notes = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
                                WHERE promoID = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success("The Selected Inventory Promo Code Was Updated Successfully", $ilpage['settings'] . '?cmd=promocode');
                        exit();         
                }
				// ####UPDATE INVENTARY PROMO CODE END  #####################################
				
				
				// #### ADD NEW SHIPPING PROMO CODE #####################################
				
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-shippingpromo')
                {
				//promoID, promoCode, promoCount, available, upgradeLevel, validDate, userID, notes
                       
					  
						
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}
						 $checkpromo = $ilance->db->query("SELECT * FROM ".DB_PREFIX."promo_shipping
						 									WHERE promoCode = '".$ilance->GPC['shippromocode']."' ");
						 
						if($ilance->db->num_rows($checkpromo) > 0 )
						{
						 print_action_failed(" Shipping Promo Code Is Already Added ", $ilpage['settings'] . '?cmd=promocode');
                      	  exit();
						}
						else
						{				
					    $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "promo_shipping
                                (promoCode, promoCount, available, upgradeLevel, validDate, userID, notes)
                                VALUES (
                                '" . $ilance->db->escape_string($ilance->GPC['shippromocode']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['shipcounts']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['shipavailable']) . "',
								'" . $ilance->db->escape_string($ilance->GPC['shipupgrade']) . "',
								'".$ilance->db->escape_string($validdate)."',								
								 '" . $ilance->db->escape_string($ilance->GPC['shipuserid']) . "',
								  '" . $ilance->db->escape_string($ilance->GPC['shipdescription']) . "'
                                )
                        ");
                        
                        print_action_success("The New Shipping Promo Code was Added Successfully", $ilpage['settings'] . '?cmd=promocode');
                        exit();
						}
                }				
				
				// ####NEW SHIPPING PROMO CODE END  #####################################
				
				// #### DELETE A SHIPPING PROMO CODE ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-shippingpromo' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "promo_shipping
                                WHERE promoshipID = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success("Selected Shipping Promo Code is deleted Successfully", $ilpage['settings'] . '?cmd=promocode');
                        exit();
                }
				// #### DELETE A SHIPPING PROMO CODE END #####################################
				
								
				// ####UPDATE SHIPPING PROMO CODE START  #####################################
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippingpromo' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND empty($ilance->GPC['do']))
                {
                        $hiddenfieldsubcmdship = 'update-shippingpromo';
                        $hiddendo = '<input type="hidden" name="do" value="shipupdate" />';
                        $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
                        
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "promo_shipping
                                WHERE promoshipID = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
						//promoID, promoCode, promoCount, available, upgradeLevel, validDate, userID, notes
                                $res = $ilance->db->fetch_array($sql);
								$shippromoCode = $res['promoCode'];
								$promoCount = $res['promoCount'];								
								$available = $res['available'];
								$upgradeLevel = $res['upgradeLevel'];
								$validDate = $res['validDate'];
								$shipuserID = $res['userID'];
								$shipnotes = $res['notes'];								
								
								if($available == 'usa')
								{
							
								  $available = '<select name="shipavailable"><option value="international">USA and International</option><option value="usa" selected="selected">Only For USA</option></select>';
								}
								if($available == 'international')
								{
								 $available = '<select name="shipavailable"><option value="international" selected="selected">USA and International</option><option value="usa">Only For USA</option></select>';
								}
								if($upgradeLevel == 'NO')
								{
								$upgradelevel = '<select name="shipupgrade"><option value="NO" selected="selected">No Upgrade</option><option value="usps">USPS Express</option><option value="fedex">Fedex Overnight</option></select>';
								}
								if($upgradeLevel == 'usps')
								{
								$upgradelevel = '<select name="shipupgrade"><option value="NO">No Upgrade</option><option value="usps" selected="selected">USPS Express</option><option value="fedex">Fedex Overnight</option></select>';
								}
								if($upgradeLevel == 'fedex')
								{
								$upgradelevel = '<select name="shipupgrade"><option value="NO">No Upgrade</option><option value="usps">USPS Express</option><option value="fedex"  selected="selected">Fedex Overnight</option></select>';
								}						
							    
								$dateofbirth = $res['validDate'];
                                $dobsplit = explode('-', $dateofbirth);
                                $year = $dobsplit[0];
                                $dobmonth = $dobsplit[1];
                                $dobday = $dobsplit[2];
								
								$shipdaylist ='<select name="day" id="day"><option value="">DATE</option>';
																	
									for($i=1; $i<=31; $i++)
									if($dobday == $i)
									$shipdaylist .= "<option value='$i' selected>$i</option>";
									else
									$shipdaylist .= "<option value='$i'>$i</option>";
								
								$shipdaylist .='</select>';
								
								$shipmonthlist ='<select name="month" id="month"><option value="">MONTH</option>';									
									
									for($j=1; $j<=12; $j++)
									
									if($dobmonth == $j)
									$shipmonthlist .= "<option value='$j' selected>$j</option>";
									else
									$shipmonthlist .= "<option value='$j'>$j</option>";
								
								
								$shipmonthlist .= '</select>';
								
								$shipyearlist = '<select name="year" id="year"><option value="">YEAR</option>';
								
									
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($year == $k)
									$shipyearlist .= "<option value='$k' selected>$k</option>";
									else
									$shipyearlist .= "<option value='$k'>$k</option>";
								
								$shipyearlist .='</select>';
								
                        }
                }
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippingpromo' AND !empty($ilance->GPC['shippromocode']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'shipupdate')
                {
                       
					   ////promoID, promoCode, promoCount, available, upgradeLevel, validDate, userID, notes
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}				
						$ilance->db->query("
                                UPDATE " . DB_PREFIX . "promo_shipping
                                SET promoCode = '" . $ilance->db->escape_string($ilance->GPC['shippromocode']) . "',
                                promoCount = '" . $ilance->db->escape_string($ilance->GPC['shipcounts']) . "',
								available = '" . $ilance->db->escape_string($ilance->GPC['shipavailable']) . "',
								upgradeLevel = '" . $ilance->db->escape_string($ilance->GPC['shipupgrade']) . "',
								validDate = '".$ilance->db->escape_string($validdate)."',								
								userID = '" . $ilance->db->escape_string($ilance->GPC['shipuserid']) . "',
                                notes = '" . $ilance->db->escape_string($ilance->GPC['shipdescription']) . "'
                                WHERE promoshipID = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success("The Selected Shipping Promo Code Was Updated Successfully", $ilpage['settings'] . '?cmd=promocode');
                        exit();         
                }
				
				
				// #### INVENTORY LISTING CODE START  #####################################
					$show['no_promocode'] = false;
                $row_count = 0;
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "promo_inventory
                        ORDER BY promoID DESC
                ");
				
				
				if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['promocode'] = true;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
								                                                    
								if($res['offerType']=='dollar')
								{								  
								   $res['offerType'] = '$';
								}
								if($res['offerType']=='percentage')
								{
								   $res['offerType'] = '%';
								}
								if($res['validDate'] == '0000-00-00')
								{
								   $res['validDate'] = 'No Limit';
								}
								if($res['validDate'] != '0000-00-00')
								{
								$dobsplit = explode('-', $res['validDate']);
                                $year = $dobsplit[0];
                                $month = $dobsplit[1];
                                $day = $dobsplit[2];
								$res['validDate'] = $month . '-' . $day . '-' .$year;
								}
								if($res['userID'] == '')
								{
								   $res['userID'] = 'ALL USER';
								}
								if($res['itemID'] == '0')
								{
								   $res['itemID'] = '--';
								}
								if($res['categoryID'] == '0')
								{
								   $res['categoryID'] = '--';
								}                         	
								                                
								
								
								$res['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=promocode&amp;subcmd=update-invoicepromo&amp;id=' . $res['promoID'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                        $res['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=promocode&amp;subcmd=remove-invoicepromo&amp;id=' . $res['promoID'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
								
								 $res['action'] = '<input type="checkbox" name="promoID[]" value="' . $res['promoID'] . '" id="promoID_' . $res['promoID'] . '" />';
                                $promocode[] = $res;
                                $row_count++;
                        }        
                }
				else
				{
				
				$show['no_promocode'] = false;
				}
				// #### INVENTORY LISTING CODE END  #####################################
				
				
				// #### SHIPPING LISTING CODE START  #####################################
				$show['no_shippingpromo'] = false ;
				$rows_count = 0;
				$sql_ship = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "promo_shipping
                        ORDER BY promoshipID DESC
                ");
				if($ilance->db->num_rows($sql_ship)>0)
				{
				   $rows_count = 0;
                        $show['shippingpromo'] = true;
                        while ($result = $ilance->db->fetch_array($sql_ship, DB_ASSOC))
                        {
                                
								if($result['validDate'] == '0000-00-00')
								{
								   $result['validDate'] = 'No Limit';
								}
								if($result['userID'] == '')
								{
								   $result['userID'] = 'ALL USER';
								}
								$result['class'] = ($rows_count % 2) ? 'alt2' : 'alt1';       
                                
								$result['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=promocode&amp;subcmd=update-shippingpromo&amp;id=' . $result['promoshipID'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                        $result['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=promocode&amp;subcmd=remove-shippingpromo&amp;id=' . $result['promoshipID'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
								
								 $result['action'] = '<input type="checkbox" name="promoshipID[]" value="' . $result['promoshipID'] . '" id="promoID_' . $result['promoshipID'] . '" />';
                                $promocodeship[] = $result;
                                $rows_count++;
						}        
                
				}
				else
				{
				 $show['no_shippingpromo'] = false ;
				}
				
				// #### SHIPPING LISTING CODE END  #####################################
									
				$pprint_array = array('hiddenfieldsubcmdship','hiddenid','hiddendo','shippromoCode','shipuserID','shipnotes','promocodeship','promoCount','available','upgradelevel','validDate','product_category','daylist','monthlist','yearlist','shipdaylist','shipmonthlist','shipyearlist','buildversion','promocode','promoCode','offerAmt','Type','salestype','conditionAmt','itemID','userID','notes','hiddenfieldsubcmd','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_promocode.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				 $ilance->template->parse_loop('main', array('promocode','promocodeship'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		
		// Murugan Coding End Here
		
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'coincondition')
        {
				$area_title = $phrase['_coin_condition'];
                $page_title = SITE_NAME . ' - ' . $phrase['_coin_condition'];
				
				$hiddenfieldsubcmdcondition = 'add-coincondition';
				$hiddenfieldsubcmdpedigree = 'add-pedigree';
				$hiddenfieldsubcmdother = 'add-otherdetails';
				$hiddendo = $hiddenid = '';
				
				
				// #### Add A coincondition  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-coincondition' AND !empty($ilance->GPC['coincondition']))
                {
				//id, coincondition
				
				$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_condition
								WHERE coincondition = '".$ilance->GPC['coincondition']."'                              
                        ");
					if ($ilance->db->num_rows($sqlarea) > 0)
					{	
					print_action_failed("coin condition Already Listed, Please Romove coin condition Or Modify Existing ", $ilpage['staffsettings'] . '?cmd=coincondition');
					}
					else
					{
				 $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "coin_condition
                                (id, coincondition)
                                VALUES (
                                NULL,
                                '" . $ilance->db->escape_string($ilance->GPC['coincondition']) . "'
                               )
                        ");
					 // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('6');
					print_action_success($phrase['_the_coin_condition_was_created_successfully'], $ilpage['staffsettings'] . '?cmd=coincondition');
					exit();
				 }
				}
				
				// #### DELETE A coincondition  ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-coincondition' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "coin_condition
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                         // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('6');
                        print_action_success($phrase['_the_selected_coin_condition_was_removed_from_the_system'], $ilpage['staffsettings'] . '?cmd=coincondition');
                        exit();
                }
				
				// #### Update the  coincondition  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-coincondition' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdcondition = 'update-coincondition';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_condition
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $coincondition = $res['coincondition'];
                                
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-coincondition' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
					
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "coin_condition
                                SET coincondition = '" . $ilance->db->escape_string($ilance->GPC['coincondition']) . "'
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                         // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('6');
                        print_action_success($phrase['_the_selected_coin_condition_was_updated_successfully'], $ilpage['staffsettings'] . '?cmd=coincondition');
                        exit();         
                }
				// update the coincondition
				
				// #### Add A pedigree  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-pedigree' AND !empty($ilance->GPC['pedigree']))
                {
				//id, pedigree
				$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "pedigree
								WHERE pedigree = '".$ilance->GPC['pedigree']."'                              
                        ");
					if ($ilance->db->num_rows($sqlarea) > 0)
					{	
					print_action_failed("pedigree Already Listed, Please Romove pedigree Or Modify Existing ", $ilpage['staffsettings'] . '?cmd=coincondition');
					}
					else
					{
				 $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "pedigree
                                (id, pedigree)
                                VALUES (
                                NULL,
                                '" . $ilance->db->escape_string($ilance->GPC['pedigree']) . "'
                               )
                        ");
						 // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('8');
				print_action_success($phrase['_the_pedigree_details_was_created_successfully'], $ilpage['staffsettings'] . '?cmd=coincondition');
				exit();
				}
				}
				
				// #### DELETE A pedigree  ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-pedigree' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "pedigree
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                         // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('8');
                        print_action_success($phrase['_the_selected_pedigree_was_removed_from_the_system'], $ilpage['staffsettings'] . '?cmd=coincondition');
                        exit();
                }
				
				// #### Update the  pedigree  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-pedigree' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdpedigree = 'update-pedigree';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "pedigree
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $pedigree = $res['pedigree'];
                                
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-pedigree' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
					
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "pedigree
                                SET pedigree = '" . $ilance->db->escape_string($ilance->GPC['pedigree']) . "'
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                         // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('8');
                        print_action_success($phrase['_the_selected_pedigree_was_updated_successfully'], $ilpage['staffsettings'] . '?cmd=coincondition');
                        exit();         
                }
				// update the pedigree
				
				// #### Add A otherdetails  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-otherdetails' AND !empty($ilance->GPC['otherdetail']))
                {
				//id, other_details
				$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_other_details
								WHERE other_details = '".$ilance->GPC['otherdetail']."'                              
                        ");
					if ($ilance->db->num_rows($sqlarea) > 0)
					{	
					print_action_failed("pedigree Already Listed, Please Romove pedigree Or Modify Existing ", $ilpage['staffsettings'] . '?cmd=coincondition');
					}
					else
					{
				 $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "coin_other_details
                                (id, other_details)
                                VALUES (
                                NULL,
                                '" . $ilance->db->escape_string($ilance->GPC['otherdetail']) . "'
                               )
                        ");
						 // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('11');
				print_action_success($phrase['_the_coin_details_was_created_successfully'], $ilpage['staffsettings'] . '?cmd=coincondition');
				exit();
				}
				}
				
				// #### DELETE A otherdetails  ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-otherdetails' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "coin_other_details
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('11');
                        print_action_success($phrase['_the_selected_coin_condition_was_removed_from_the_system'], $ilpage['staffsettings'] . '?cmd=coincondition');
                        exit();
                }
				
				// #### Update the  otherdetails  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-otherdetails' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdother = 'update-otherdetails';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_other_details
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $otherdetail = $res['other_details'];
                                
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-otherdetails' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
					
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "coin_other_details
                                SET other_details = '" . $ilance->db->escape_string($ilance->GPC['otherdetail']) . "'
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('11');
                        print_action_success($phrase['_the_selected_coin_condition_was_updated_successfully'], $ilpage['staffsettings'] . '?cmd=coincondition');
                        exit();         
                }
				// update the otherdetails
				
				
				//List the coin condition 
				//pagination coin condition ####################################################
				 $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
				$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
				
				$displayorderfields = array('asc', 'desc');
				$displayorder = '&amp;displayorder=asc';
				$currentdisplayorder = $displayorder;
				$displayordersql = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder = '&amp;displayorder=desc';
					$currentdisplayorder = '&amp;displayorder=asc';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder = '&amp;displayorder=asc';
					$currentdisplayorder = '&amp;displayorder=desc';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
				{
					$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage = $ilpage['staffsettings']. '?cmd=coincondition' . $currentdisplayorder . $orderby;
				
				//pagination coin condition ####################################################
						
				//pagination pedigree ####################################################
				$displayorderfields1 = array('asc', 'desc');
				$displayorder1 = '&amp;displayorder=asc&amp;list=pedigree';
				$currentdisplayorder1 = $displayorder1;
				$displayordersql1 = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder1 = '&amp;displayorder=desc&amp;list=pedigree';
					$currentdisplayorder1 = '&amp;displayorder=asc&amp;list=pedigree';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder1 = '&amp;displayorder=asc&amp;list=pedigree';
					$currentdisplayorder1 = '&amp;displayorder=desc&amp;list=pedigree';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields1))
				{
					$displayordersql1 = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage1 = $ilpage['staffsettings']. '?cmd=coincondition' . $currentdisplayorder1 . $orderby1;
				
				//pagination pedigree ####################################################
				
				//pagination other details ####################################################
				$displayorderfields2 = array('asc', 'desc');
				$displayorder2 = '&amp;displayorder=asc&amp;list=other';
				$currentdisplayorder2 = $displayorder2;
				$displayordersql2 = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder2 = '&amp;displayorder=desc&amp;list=other';
					$currentdisplayorder2 = '&amp;displayorder=asc&amp;list=other';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder2 = '&amp;displayorder=asc&amp;list=other';
					$currentdisplayorder2 = '&amp;displayorder=desc&amp;list=other';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields2))
				{
					$displayordersql2 = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage2 = $ilpage['staffsettings']. '?cmd=coincondition' . $currentdisplayorder2 . $orderby2;
				//pagination other details ####################################################
				
				$sqlcoincondition = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_condition
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
                        );
				$sqlcoincondition1 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_condition
                                
                        ");		
				$number = (int)$ilance->db->num_rows($sqlcoincondition1);	
				if ($ilance->db->num_rows($sqlcoincondition) > 0)
                {
                	$row_count = 0;
                    while ($rescondition = $ilance->db->fetch_array($sqlcoincondition, DB_ASSOC))
                    {	//id, site_name
						$rescondition['coincondition'] = $rescondition['coincondition']; 
						$rescondition['edit'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=coincondition&amp;subcmd=update-coincondition&amp;id=' . $rescondition['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$rescondition['remove'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=coincondition&amp;subcmd=remove-coincondition&amp;id=' . $rescondition['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$coinconditionlist[] = $rescondition;
                        $row_count++;
					}
				
				$prevnext = print_pagnation_new($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);	
				}
				else
				{
					$show['no_coinconditionlist'] = true;
				}
				
				//List the pedigree
				
				$sqlpedigree = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "pedigree
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
                        );
				$sqlpedigree1 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "pedigree
                                
                        ");		
				$number1 = (int)$ilance->db->num_rows($sqlpedigree1);	
				if ($ilance->db->num_rows($sqlpedigree) > 0)
                {
                	$row_count = 0;
                    while ($ressqlpedigree = $ilance->db->fetch_array($sqlpedigree, DB_ASSOC))
                    {	//id, site_name
						$ressqlpedigree['pedigree'] = $ressqlpedigree['pedigree']; 
						$ressqlpedigree['edit'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=coincondition&amp;subcmd=update-pedigree&amp;id=' . $ressqlpedigree['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$ressqlpedigree['remove'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=coincondition&amp;subcmd=remove-pedigree&amp;id=' . $ressqlpedigree['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$pedigreelist[] = $ressqlpedigree;
                        $row_count++;
					}
				
				$prevnext1 = print_pagnation_new($number1, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage1);	
				}
				else
				{
					$show['no_pedigreelist'] = true;
				}
				
				//List the coin other details
				
				$sqlother = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_other_details
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
                        );
				$sqlother1 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "coin_other_details
                                
                        ");		
				$number2 = (int)$ilance->db->num_rows($sqlother1);	
				if ($ilance->db->num_rows($sqlother) > 0)
                {
                	$row_count = 0;
                    while ($resother = $ilance->db->fetch_array($sqlother, DB_ASSOC))
                    {	//id, site_name
						$resother['otherdetails'] = $resother['other_details']; 
						$resother['edit'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=coincondition&amp;subcmd=update-otherdetails&amp;id=' . $resother['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$resother['remove'] = '<a href="' . $ilpage['staffsettings'] . '?cmd=coincondition&amp;subcmd=remove-otherdetails&amp;id=' . $resother['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$otherdetailslist[] = $resother;
                        $row_count++;
					}
				
				$prevnext2 = print_pagnation_new($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage2);	
				}
				else
				{
					$show['no_otherdetailslist'] = true;
				}
				
				
				$pprint_array = array('prevnext','prevnext1','prevnext2','otherdetail','pedigree','coincondition','hiddenid','hiddendo','hiddenfieldsubcmdother','hiddenfieldsubcmdpedigree','hiddenfieldsubcmdcondition','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'settings_coin_condition.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('coinconditionlist','pedigreelist','otherdetailslist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();
		}
		
                                   //******Time_Line*****////
           
    elseif (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'timeline')
    {
       
                                     ////UPDATE//////
                                     
                                     
       if (isset($ilance->GPC['update']) AND $ilance->GPC['update'] == 'update')
   {                                                    
     if (!empty($ilance->GPC['description']))
        {
            $message = $ilance->GPC['description'];
            $message = $ilance->bbcode->prepare_special_codes('PHP', $message);
            $message = $ilance->bbcode->prepare_special_codes('HTML', $message);
            $message = $ilance->bbcode->prepare_special_codes('CODE', $message);
            $message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
            //$message = $ilance->bbcode->strip_bb_tags($message);
            $message = html_entity_decode($message);
             
      
             
            $ilance->db->query("
            UPDATE " . DB_PREFIX . "time_line
            SET message='".$ilance->db->escape_string($message)."',date_time='".$ilance->GPC['datetime']."'
            WHERE id = '".$ilance->GPC['id']."'
            ");
            
            print_action_success('Successfully updated', 'settings.php?cmd=timeline');
           
           exit();
        }
  
    }
    
                                   ///////INSERT////////
     
        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'editor')
        {
            if (!empty($ilance->GPC['description']))
            {
                $message = $ilance->GPC['description'];
                $message = $ilance->bbcode->prepare_special_codes('PHP', $message);
                $message = $ilance->bbcode->prepare_special_codes('HTML', $message);
                $message = $ilance->bbcode->prepare_special_codes('CODE', $message);
                $message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
                //$message = $ilance->bbcode->strip_bb_tags($message);
                $message = html_entity_decode($message);
           
            
                $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "time_line
                                (id,message,date_time)
                                VALUES (
                                NULL,
                                '".$ilance->db->escape_string($message) ."',
                                '".$ilance->GPC['datetime']."'
                                )
                                ");
                
                
                print_action_success('Successfully submitted', 'settings.php?cmd=timeline');
                exit();
            }
        }
   
   
                                    /////////EDIT///////
   
    if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'edit')
    {
        $show['edit']=true;
        $currentmotd = $ilance->db->fetch_field(DB_PREFIX . "time_line","id = '".$ilance->GPC['id']."'","message");
        $currentmotd1 = $ilance->db->fetch_field(DB_PREFIX . "time_line","id = '".$ilance->GPC['id']."'","date_time");
        $id=$ilance->GPC['id'];
       
        $wysiwyg_area = print_wysiwyg_editor('description', $currentmotd, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
        $pprint_array = array('wysiwyg_area','id','currentmotd1','currentmotd2','login_include_admin','ilanceversion'); 

        $ilance->template->fetch('main', 'time_line.html',2);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'purchase_now_activity');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
    }
   
   
   
                                       /////DELETE////////////
   
        if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'delete')
	{
	    $ilance->db->query("DELETE FROM " . DB_PREFIX . "time_line WHERE id='".$ilance->GPC['id']."'");
	}

 
   
    $show['edit']=false;
    $ilance->GPC['description'] = !empty($ilance->GPC['description']) ? $ilance->GPC['description'] : '';
    $wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
   
    $show['con']=true;
    $sql = $ilance->db->query("SELECT id, message, date_time
                            FROM " . DB_PREFIX . "time_line
                            ");
    $html.= '<table  align="center" border=0><tbody>';
    $count=1;
    while($row=mysql_fetch_array($sql))
    {
    $html.= '<tr class="alt1"><td align="center" style="width: 50px;">'.$count.'</td><td style="width: 500px;">'.strtr($ilance->bbcode->bbcode_to_html($row['message']),"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","").'</td><td align="center" style="width: 100px;">'.$row['date_time'].'</td><td align="center"><a href="settings.php?id='.$row['id'].'&cmd=timeline&action=edit"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'icons/pencil.gif" alt="" border="0" /></a></td><td align="center"> <a href="settings.php?id='.$row['id'].'&cmd=timeline&action=delete">  <img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'icons/delete.gif" alt="" border="0" /></a> </td></tr><tr></tr>'   ;
    $count++;
    }
    
    $html.= '</table>';
   
    $pprint_array = array('ilanceversion','html','wysiwyg_area','currentmotd1','currentmotd2','login_include_admin'); 


    $ilance->template->fetch('main', 'time_line.html', 2);
    $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
     $ilance->template->parse_loop('main', 'purchase_now_activity');
    $ilance->template->parse_if_blocks('main');
    $ilance->template->pprint('main', $pprint_array);
    exit();
    
    
   }
  		$hiddenfieldsubcmdgrading = 'add-grading';
		$hiddenfieldsubcmdservice = 'add-service';
		$hiddendo = $hiddenid = '';
   
   //grading settings 
   // #### Add the  grading  #################################### 
   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-grading' AND !empty($ilance->GPC['newgs']))
	{
		if($_FILES['upload'])
		{
		
			//print_r($_SERVER);
			 $name = $_FILES["upload"]["name"];
			
		   $uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/greatcollectionwork/images/gradinglogo';
		   move_uploaded_file($_FILES["upload"]["tmp_name"],"$uploads_dir/$name");
		
		
	//id, grading
	$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "grading_service
								WHERE grading = '".$ilance->GPC['newgs']."'                              
                        ");
	if ($ilance->db->num_rows($sqlarea) > 0)
	{	
	print_action_failed("grading service Already Listed, Please Romove grading service Or Modify Existing ", $ilpage['staffsettings'] );
	}
	else
	{
	 $ilance->db->query("
					INSERT INTO " . DB_PREFIX . "grading_service
					(id, grading, logoname)
					VALUES (
					NULL,
					'" . $ilance->db->escape_string($ilance->GPC['newgs']) . "',
					'". $name ."'
					)
			");
			// Murugan Coding For Product Question Creation On DEC 16
			fetch_question_table('5');
	print_action_success($phrase['_the_grading_level_was_created_successfully'], $ilpage['staffsettings']);
	exit();
	}
	}
	}
	// #### DELETE A grading  ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-grading' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
			$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "grading_service
					WHERE id = '" . intval($ilance->GPC['id']) . "'
					LIMIT 1
			");
			// Murugan Coding For Product Question Creation On DEC 16
			fetch_question_table('5');
			print_action_success($phrase['_the_selected_grading_was_removed_from_the_system'], $ilpage['staffsettings']);
			exit();
	}

	// #### Update the  grading  ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-grading' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		
		$hiddenfieldsubcmdgrading = 'update-grading';
		$hiddendo = '<input type="hidden" name="do" value="update" />';
		$hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
		
		$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "grading_service
					WHERE id = '" . intval($ilance->GPC['id']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				   
					$res = $ilance->db->fetch_array($sql);
					
					$grading = $res['grading'];
					
			}
	
	}
				
	 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-grading' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
	{
		
			$ilance->db->query("
					UPDATE " . DB_PREFIX . "grading_service
					SET grading = '" . $ilance->db->escape_string($ilance->GPC['newgs']) . "'
					WHERE id = '" . intval($ilance->GPC['id']) . "'
			");
			// Murugan Coding For Product Question Creation On DEC 16
			fetch_question_table('5');
			print_action_success($phrase['_the_selected_grading_was_updated_successfully'], $ilpage['staffsettings']);
			exit();         
	}
				// update the grading
				
				// Pagination ###########################################
				 $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
				$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
				
				//grading 
				$displayorderfields = array('asc', 'desc');
				$displayorder = '?displayorder=asc&amp;tab=grading';
				$currentdisplayorder = $displayorder;
				$displayordersql = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder = '?displayorder=desc&amp;tab=grading';
					$currentdisplayorder = '?displayorder=asc&amp;tab=grading';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder = '?displayorder=asc&amp;tab=grading';
					$currentdisplayorder = '?displayorder=desc&amp;tab=grading';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
				{
					$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage = $ilpage['staffsettings'] . $currentdisplayorder . $orderby;
				// grading
				
				//service level
				$displayorderfields1 = array('asc', 'desc');
				$displayorder1 = '?displayorder=asc&amp;tab=service';
				$currentdisplayorder1 = $displayorder1;
				$displayordersql1 = 'DESC';
				if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
				{
					$displayorder1 = '?displayorder=desc&amp;tab=service';
					$currentdisplayorder1 = '?displayorder=asc&amp;tab=service';
				}
				else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
				{
					$displayorder1 = '?displayorder=asc&amp;tab=service';
					$currentdisplayorder1 = '?displayorder=desc&amp;tab=service';
				}
				
				if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields1))
				{
					$displayordersql1 = mb_strtoupper($ilance->GPC['displayorder']);
				}
				
				$scriptpage1 = $ilpage['staffsettings'] . $currentdisplayorder1 . $orderby1;
				
				//service level
							
				// Pagination ###########################################
				$sqlgrading = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "grading_service 
											LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
											
									);
				$sqlgrading1 = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "grading_service 
											
											
									");					
				$number = (int)$ilance->db->num_rows($sqlgrading1);	
				if ($ilance->db->num_rows($sqlgrading) > 0)
                {
                	$row_count = 0;
                    while ($resgrading = $ilance->db->fetch_array($sqlgrading, DB_ASSOC))
                    {	//id, grading
						$resgrading['grading'] = $resgrading['grading']; 
						
						$resgrading['edit'] = '<a href="' . $ilpage['staffsettings'] . '?subcmd=update-grading&amp;id=' . $resgrading['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$resgrading['remove'] = '<a href="' . $ilpage['staffsettings'] . '?subcmd=remove-grading&amp;id=' . $resgrading['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$gradinglist[] = $resgrading;
                        $row_count++;
					}
				
				$prevnext = print_pagnation_new($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);	
				}
				else
				{
					$show['no_gradinglist'] = true;
				}
	//grading settings
	
	
	//service settings
	$service_pulldown = '<select name="serviceid" style="font-family: verdana">';
                        
			$sql_service = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "grading_service
                        ");
			while ($res_service = $ilance->db->fetch_array($sql_service))
                        {
				$sql_level = $ilance->db->query("
                                        SELECT service_id
                                        FROM " . DB_PREFIX . "service_level
                                        WHERE id = '" . intval($ilance->GPC['id']) . "'
                                ");
				$res_level = $ilance->db->fetch_array($sql_level);
								
				$service_pulldown .= '<option value="' . $res_service['id'] . '"';
								if ($res_service['id'] == $res_level['service_id'])
                                { 
                                        $service_pulldown .= ' selected="selected"';
                                }
                               
				$service_pulldown .= '>' . $res_service['grading']. '</option>';
			}
			$service_pulldown .= '</select>';
	
	//add service		
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-service' AND !empty($ilance->GPC['servicename']))
	{
	//id, service_id, service_name, amount, days, sort
	$sqlarea = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "service_level
								WHERE service_name = '".$ilance->GPC['servicename']."'
								AND service_id = '".$ilance->GPC['serviceid']."'                              
                        ");
	if ($ilance->db->num_rows($sqlarea) > 0)
	{	
	print_action_failed("service name Already Listed, Please Romove service name Or Modify Existing ", $ilpage['staffsettings'] );
	}
	else
	{
	 $ilance->db->query("
					INSERT INTO " . DB_PREFIX . "service_level
					(id, service_id, service_name, amount, days)
					VALUES (
					NULL,
					'" . $ilance->db->escape_string($ilance->GPC['serviceid']) . "',
					'" . $ilance->db->escape_string($ilance->GPC['servicename']) . "',
					'" . $ilance->db->escape_string($ilance->GPC['feeamount']) . "',
					'" . $ilance->db->escape_string($ilance->GPC['nodays']) . "'
					)
			");
			// Murugan Coding For Product Question Creation On DEC 16
	fetch_question_table('4');
	print_action_success($phrase['_the_service_level_was_created_successfully'], $ilpage['staffsettings']);
	exit();
	}
	}
	
	// #### DELETE A service  ####################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-service' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "service_level
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        // Murugan Coding For Product Question Creation On DEC 16
						fetch_question_table('4');
                        print_action_success($phrase['_the_selected_service_level_was_removed_from_the_system'], $ilpage['staffsettings']);
                        exit();
                }
	
	// #### Update the  service  ####################################
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-service' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
					
					$hiddenfieldsubcmdservice = 'update-service';
					$hiddendo = '<input type="hidden" name="do" value="update" />';
                    $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "service_level
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                               
							    $res = $ilance->db->fetch_array($sql);
								
                                $servicename = $res['service_name'];
								$feeamount = $res['amount'];
								$nodays = $res['days'];
                                
                        }
				
				}
				
				 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-service' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
					
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "service_level
                                SET service_id = '" . $ilance->db->escape_string($ilance->GPC['serviceid']) . "',
								 service_name = '" . $ilance->db->escape_string($ilance->GPC['servicename']) . "',
                                amount = '" . $ilance->db->escape_string($ilance->GPC['feeamount']) . "',
								days = '" . $ilance->db->escape_string($ilance->GPC['nodays']) . "'
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                        ");
						// Murugan Coding For Product Question Creation On DEC 16
                        fetch_question_table('4');
                        print_action_success($phrase['_the_selected_service_was_updated_successfully'], $ilpage['staffsettings']);
                        exit();         
                }
				// update the service
	
				$sqlservice = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "service_level ORDER BY service_id
											LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
									);
				$sqlservice1 = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "service_level ORDER BY service_id
											
									");					
				$number1 = (int)$ilance->db->num_rows($sqlservice1);	
				if ($ilance->db->num_rows($sqlservice) > 0)
                {
                	$row_count = 0;
                    while ($resservice = $ilance->db->fetch_array($sqlservice, DB_ASSOC))
                    {	//id, 
						$resservice['servicename'] = $resservice['service_name']; 
						$resservice['amount'] = $resservice['amount']; 
						$resservice['days'] = $resservice['days']; 
						
						$sqlgs = $ilance->db->query("
                                SELECT grading
                                FROM " . DB_PREFIX . "grading_service
								 WHERE id = '" . $resservice['service_id'] . "'
                                
                        ");
						$resgs = $ilance->db->fetch_array($sqlgs, DB_ASSOC);
						
						$resservice['level'] = $resgs['grading'];
						
						$resservice['edit'] = '<a href="' . $ilpage['staffsettings'] . '?subcmd=update-service&amp;id=' . $resservice['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$resservice['remove'] = '<a href="' . $ilpage['staffsettings'] . '?subcmd=remove-service&amp;id=' . $resservice['id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
						$servicelist[] = $resservice;
                        $row_count++;
					}
				
				$prevnext1 = print_pagnation_new($number1, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage1);	
				}
				else
				{
					$show['no_servicelist'] = true;
				}
	//service settings
	
	//configuration settings
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-config-settings')
	{
		$ilance->admincp = construct_object('api.admincp');
		
		require_once(DIR_CORE . 'functions_attachment.php');
		
		foreach ($ilance->GPC['config'] AS $varname => $value)
		{
			if (isset($varname) AND $varname == 'attachment_dbstorage')
			{
				if (isset($value) AND $value == 0 AND $ilconfig['attachment_dbstorage'])
				{
					move_attachments_to_filepath();
				}
				else if (isset($value) AND $value == 1 AND $ilconfig['attachment_dbstorage'] == 0)
				{
					move_attachments_to_database();
				}
			}
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . $ilance->db->escape_string($value) . "',
				sort = '" . intval($ilance->GPC['sort'][$varname]) . "'
				WHERE name = '" . $ilance->db->escape_string($varname) . "'
			");
					
			$sql = $ilance->db->query("
				SELECT value, inputname
				FROM " . DB_PREFIX . "configuration
				WHERE name = '" . $ilance->db->escape_string($varname) . "'
					AND inputtype = 'pulldown'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				
				if ($res['inputname'] == 'timezones')
				{
					$writepulldown = $ilance->datetime->construct_timezone_pulldown('admin', $varname);
				}
				else if ($res['inputname'] == 'currencyrates')
				{
					$writepulldown = $ilance->currency->pulldown('admin', $varname);
				}
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET inputcode = '" . $ilance->db->escape_string($writepulldown) . "'
					WHERE name = '" . $ilance->db->escape_string($varname) . "'
				");
			}
			else
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . $ilance->db->escape_string($value) . "'
					WHERE name = '" . $ilance->db->escape_string($varname) . "'
				");
			}
		}
		print_action_success($phrase['_configuration_settings_have_been_saved_to_the_database'], $ilance->GPC['return']);
	exit();
	}
	$global_staff = $ilance->admincp->construct_admin_input_staff('staffsettings', $ilpage['staffsettings']);
	//configuration settings
	
	
	/*sekar works on email and pdf on aug19*/
	 $email_pdf='<form action="" method="post">
	 <table width="100%" border="0" cellspacing="{table_cellspacing}" cellpadding="{table_cellpadding}">
								<tr class="alt1">
								     <td>Date:&nbsp;&nbsp;<input type="text" name="daterange" />&nbsp;&nbsp;<input type="submit" name="submit" value="submit" />&nbsp;(yyyy-mm-dd)</td>
								</tr>
						
                                <tr class="alt1">
								<td   nowrap="nowrap"><a href="projectlist.php?daterannge='.$ilance->GPC['daterange'].'">Reports for consignor in Email</a></td>
							    </tr><tr class="alt1">
								<td   nowrap="nowrap"><a href="projectlistpdf.php?daterannge='.$ilance->GPC['daterange'].'">Reports for consignor in PDF</a></td>
							    </tr>
                                
                                       
                                </table></form>';
	/*sekar finished works on email and pdf on aug19*/
	//echo $ilance->GPC['daterange'];
	
//Banner Tab

 //Upload new banner

 //bug no 1498
   //Pagnation
     $ilconfig['maxrowsdisplay']=50;
	 $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['maxrowsdisplay'];
	 
	 
  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
	            $scriptpage = $ilpage['settings'].'?subcmd=search&banner_name='.$ilance->GPC['banner_name'];
				$show['banner_search'] = true;
				$search_text = $ilance->db->escape_string($ilance->GPC['banner_name']);
				
				$search_splt=explode(" ",$search_text);
				
				$numsql=count($search_splt);
				$sqlfile1="";
				$sqlfile2="";
				$sqlfile3="";
				$sqlfile4="";
								
				for($j=0;$j<$numsql;$j++)
				{
					if($j==0)
					{
					$sqlfile1="( b.filename LIKE '%" .$search_splt[$j]."%'";
					$sqlfile2=" OR (b.inner_text LIKE '%".$search_splt[$j]."%'";
					$sqlfile3=" OR (b.image_link LIKE '%".$search_splt[$j]."%'";
					$sqlfile4=" OR (b.image_text LIKE '%".$search_splt[$j]."%'";
					}	
					else 
					{
					 $sqlfile1.=" and b.filename LIKE '%" .$search_splt[$j]."%'";
					 $sqlfile2.=" and b.inner_text LIKE '%" .$search_splt[$j]."%'";
					 $sqlfile3.=" and b.image_link LIKE '%".$search_splt[$j]."%'";
					 $sqlfile4.=" and b.image_text LIKE '%".$search_splt[$j]."%'";		
						
					}	
					 					
					
				}
				
				$sqlfile1.=")";
				$sqlfile2.=")";
				$sqlfile3.=")";
				$sqlfile4.=")";
				
				 if (isset($search_text) AND $search_text != '')
				{
								
				$level1=$ilance->db->query("SELECT o.id as border,b.id as bid,b.*,o.*,o.sequence 
				FROM " . DB_PREFIX . "banner b 
				LEFT JOIN " . DB_PREFIX . "banner_order o on b.id = o.banner_id 
				WHERE ".$sqlfile1.$sqlfile2.$sqlfile3.$sqlfile4." order by ISNULL(o.id),o.id asc 
				LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['maxrowsdisplay']) . "," . $ilconfig['maxrowsdisplay']);
				
				
				
				$pagen=$ilance->db->query("SELECT o.id as border,b.id as bid,b.*,o.*,o.sequence 
				FROM " . DB_PREFIX . "banner b 
				LEFT JOIN " . DB_PREFIX . "banner_order o on b.id = o.banner_id 
				WHERE  ".$sqlfile1.$sqlfile2.$sqlfile3.$sqlfile4." order by ISNULL(o.id),o.id asc ");
								
				
				}
				else
				{
					
				$level1 = $ilance->db->query("SELECT o.id as border,b.id as bid ,b.*,o.*
				FROM " . DB_PREFIX . "banner 
				b left join ilance_banner_order o on b.id = o.banner_id
				order by ISNULL(o.id),o.id asc
				LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['maxrowsdisplay']) . "," . $ilconfig['maxrowsdisplay']
				);

				$pagen = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "banner
				");
					
				}
				
				$number = (int)$ilance->db->num_rows($pagen);		
				while ($row1= $ilance->db->fetch_array($level1)) 
				{

				if($row1['border'] >1)
				{
				
				$banner1['order']= $row1['sequence'];
				}
				else
				{
				$banner1['order']= '0';
				}
				$banner1['name']=$row1['filename'];
				$banner_list_fname1['f_name']=$row1['filename']."\n";
				
				$banner1['link']= $row1['image_link'];
				$banner1['text']= $row1['inner_text'];
				$banner1['text_link']= $row1['inner_text_link'];
				$banner1['text2']= $row1['inner_text1'];
				$banner1['text_link2']= $row1['inner_text_link1'];
				$banner1['text3']= $row1['inner_text2'];
				$banner1['text_link3']= $row1['inner_text_link2'];
				$uselistra1 = HTTPS_SERVER."banner/images/".$row1['filename']; 
			$banner1['image']='<img src="'.$uselistra1.'" height="72" width="164" ><br />';
            $banner1['edit'] = '<a href="' . $ilpage['settings'] . '?subcmd=updatebanner&amp;id=' . $row1['bid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
            $banner1['remove'] = '<a href="' . $ilpage['settings'] . '?subcmd=deletebanner&amp;id=' . $row1['bid'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
           $banner1['status'] = ($row1['visible'] == '1') ? '<a href="' . $ilpage['settings'] . '?subcmd=suspendbanner&amp;id=' . $row1['bid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to Deactivate banner" border="0"></a>' : '<a href="' . $ilpage['settings'] . '?subcmd=activatebanner&amp;id=' . $row1['bid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to re-activate banner " border="0"></a>';
			  $banner_list1[]=$banner1;
				$banner_list_name1[]=$banner_list_fname1;
			  $i++;
		   }
	  }
	else if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'listingorder')
	{
		
		$scriptpage = $ilpage['settings'].'?';
		$sql_banner = $ilance->db->query("SELECT *
				                        FROM " . DB_PREFIX . "banner
										where display_order!=0
										and visible != 0
				                        order by visible desc,display_order asc"		                        
									  );
		while($res_banner=$ilance->db->fetch_array($sql_banner))
		{
		$old_listing[]=$res_banner['filename'];
		}
		
		
		$new_listing = $ilance->GPC['listing'];
		$new_listing=explode("\n",$new_listing);
	
		$order=1;
		for($i=0;$i<count($new_listing);$i++)
		{
						
			echo "UPDATE " . DB_PREFIX . "banner
					SET visible = 0,
					display_order = 0
					WHERE display_order = '" . $ilance->db->escape_string($order) . "'						
				";
			echo "<br>";
			$ilance->db->query("
						UPDATE " . DB_PREFIX . "banner
						SET visible = 0,
						display_order = 0
						WHERE display_order = '" . $ilance->db->escape_string($order) . "'
					");
			
			echo "
					UPDATE " . DB_PREFIX . "banner
					SET visible = '1',
					display_order = '" . $ilance->db->escape_string($order) . "'
					WHERE filename = '" . $ilance->db->escape_string(trim($new_listing[$i])) . "'					
				";
				echo "<br>";
			$ilance->db->query("
					UPDATE " . DB_PREFIX . "banner
					SET visible = 1,
					display_order = '" . $ilance->db->escape_string($order) . "'
					WHERE filename = '" . $ilance->db->escape_string(trim($new_listing[$i])) . "'					
				");
				
			$order++;
		}
		
		$sql_banner2 = $ilance->db->query("SELECT *
				                        FROM " . DB_PREFIX . "banner
										where display_order!=0
										and visible != 0
				                        order by visible desc,display_order asc"		                        
									  );
		while($res_banner2=$ilance->db->fetch_array($sql_banner2))
		{
		$updated_listing[]=$res_banner2['filename'];
		}
		
		// print_r($old_listing);
		// echo "<br>";
		// print_r($updated_listing);
		exit;
	  }
	  else
	  {
	  $scriptpage = $ilpage['settings'].'?';
	      //Listing Banners
	  $sql_banner = $ilance->db->query("SELECT o.id as border,b.id as bid ,b.*,o.*
										FROM " . DB_PREFIX . "banner 
										b left join ilance_banner_order o on b.id = o.banner_id
										order by ISNULL(o.id),o.id asc
				                        LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['maxrowsdisplay']) . "," . $ilconfig['maxrowsdisplay']
									  );
			
	   $sql_banner1 = $ilance->db->query("SELECT *
				                          FROM " . DB_PREFIX . "banner
			                            ");		
			
	   $number = (int)$ilance->db->num_rows($sql_banner1);			
       while ($row= $ilance->db->fetch_array($sql_banner)) 
	   {	
	   		if($row['border'] >1)
	   		{
	   		$banner['order']= $row['sequence'];
	   		}
	   		else
	   		{
			$banner['order']= '0';
	   		}
			$banner['name']=$row['filename'];
			$banner_list_fname['f_name']=$row['filename']."\n";
			
			$banner['link']= $row['image_link'];
			$banner['text']= $row['inner_text'];
			$banner['text_link']= $row['inner_text_link'];
			$banner['text2']= $row['inner_text1'];
			$banner['text_link2']= $row['inner_text_link1'];
			$banner['text3']= $row['inner_text2'];
			$banner['text_link3']= $row['inner_text_link2'];
			$uselistra = HTTPS_SERVER."banner/images/".$row['filename']; 
			$banner['image']='<img src="'.$uselistra.'" height="72" width="164" ><br />';
            $banner['edit'] = '<a href="' . $ilpage['settings'] . '?subcmd=updatebanner&amp;id=' . $row['bid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
            $banner['remove'] = '<a href="' . $ilpage['settings'] . '?subcmd=deletebanner&amp;id=' . $row['bid'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
           $banner['status'] = ($row['visible'] == '1') ? '<a href="' . $ilpage['settings'] . '?subcmd=suspendbanner&amp;id=' . $row['bid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to Deactivate banner" border="0"></a>' : '<a href="' . $ilpage['settings'] . '?subcmd=activatebanner&amp;id=' . $row['bid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to re-activate banner " border="0"></a>';
 
          $banner_list[]=$banner;
		  $banner_list_name[]=$banner_list_fname;
          $i++;
       }

	  
	  }
// bug no 1498

  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'banner_upload')
	{
	   if($_FILES['upload'])
	   {
	
		   $uploads_dir =DIR_SERVER_ROOT."banner/images/";
		   //move_uploaded_file($_FILES["upload"]["tmp_name"],"$uploads_dir/$name");
		
		   $hash=md5(microtime());
		
		   //$filedata = addslashes(fread(fopen($uploads_dir.$name, 'rb'), filesize($uploads_dir.$name)));
          //  @unlink($uploads_dir.$name);
		  
		
	 
	     if(move_uploaded_file($_FILES["upload"]["tmp_name"], $uploads_dir.$_FILES["upload"]["name"]))
         {
		   
			$name = $_FILES["upload"]["name"];
			$type = $_FILES["upload"]["type"];
			$size = $_FILES["upload"]["size"];
            //$filedata = addslashes(fread(fopen($uploads_dir.$name, 'rb'), filesize($uploads_dir.$name)));
            //@unlink($uploads_dir.$name);
		 }
	     $coinid = implode("_",array_reverse(explode("_",$name)));                
	     $coinid_res= explode("_", $coinid);
		//venkat 
		$inner_ttt= $ilance->GPC['inner_text'];
		$inner_link=$ilance->GPC['inner_text_link'];
		$projectid=$coinid_res[2];
		$inner_text_1=$inner_ttt[0]?$inner_ttt[0]:'';
		$inner_text_2=$inner_ttt[1]?$inner_ttt[1]:'';
		$inner_text_3=$inner_ttt[2]?$inner_ttt[2]:'';
		$inner_link_1=$inner_link[0]?$inner_link[0]:'';
		$inner_link_2=$inner_link[1]?$inner_link[1]:'';
		$inner_link_3=$inner_link[2]?$inner_link[2]:'';

		$sql7="SELECT * FROM " . DB_PREFIX . "banner WHERE filename = '".$ilance->db->escape_string($name)."'";		
		$result7 = $ilance->db->query($sql7, 0, null, __FILE__, __LINE__);
 		if($ilance->db->num_rows($result7) > 0)
		{
			print_action_failed("File name already exists, Please try to defferent file name", $_SERVER['PHP_SELF']);exit();
 		}
		else
		{
			$ilance->db->query("insert into ".DB_PREFIX."banner 
			values (0,'".$ilance->db->escape_string($name)."','".$type."','".$size."','".$hash."','".$ilance->GPC['link']."','".$ilance->db->escape_string($inner_text_1)."','".$inner_link_1."','0','0','".DATETIME24H."','".$ilance->db->escape_string($inner_text_2)."','".$ilance->db->escape_string($inner_text_3)."','".$inner_link_2."','".$inner_link_3."','','".$projectid."')");		
	    }			
	    print_action_success('Banner  Uploaded Successfully', $ilpage['staffsettings']);
	    exit();
	}
 }
	//Activate Banner
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'activatebanner')
	{
	   $ilance->db->query("update ".DB_PREFIX."banner 
	                       set visible='1' where id='".$ilance->GPC['id']."'");
	}
	
	//De-activate Banner
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'suspendbanner')
	{
	    $ilance->db->query("update ".DB_PREFIX."banner 
	                        set visible='0' where id='".$ilance->GPC['id']."'");
	}
	
	//Update Banner
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatebanner')
	{
	     $sql_update = $ilance->db->query("SELECT *
				                           FROM " . DB_PREFIX . "banner
				                           where id='".$ilance->GPC['id']."'");
				
		 $row_update= $ilance->db->fetch_array($sql_update);
				
		$link=$row_update['image_link'];
		$inner_text=$row_update['inner_text'];
		$inner_text_link=$row_update['inner_text_link'];
		$inner_text1=$row_update['inner_text1'];
		$inner_text_link1=$row_update['inner_text_link1'];
		$inner_text2=$row_update['inner_text2'];
		$inner_text_link2=$row_update['inner_text_link2'];
		$order=$row_update['display_order'];
		$status = ($row_update['visible'] == '1') ?"checked=checked":'';
		$bannerid=$ilance->GPC['id'];
		$projectid=$row_update['coin_id'];
	  
	    $show['edit']=true;
	}
	
	//Edit Banner
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit_update')
	{

	     //bug no 1498
	 
	     $sql_order = $ilance->db->query("SELECT *
				                           FROM " . DB_PREFIX . "banner
				                           where  display_order='".$ilance->GPC['order']."'");
										   
		  if($ilance->db->num_rows($sql_order)>0)
		  {
		    while($res = $ilance->db->fetch_array($sql_order))
			{
			 $ilance->db->query("update ".DB_PREFIX."banner 
	                          set display_order ='0',
							   visible ='0'
			         where id='".$res['id']."'");
			}
		  }	
		 		
	   // end bug no 1498
	 
	    $status = $ilance->GPC['status'] ? "1" : "0";
		
	    $ilance->db->query("update ".DB_PREFIX."banner 
	                          set image_link ='".$ilance->GPC['link']."',
			                   inner_text ='".$ilance->GPC['inner_text']."',
							   inner_text_link ='".$ilance->GPC['inner_text_link']."',
							   inner_text1 ='".$ilance->GPC['inner_text1']."',
							   inner_text_link1 ='".$ilance->GPC['inner_text_link1']."',
							   inner_text2 ='".$ilance->GPC['inner_text2']."',
							   inner_text_link2 ='".$ilance->GPC['inner_text_link2']."',
							   display_order ='".$ilance->GPC['order']."',
							   visible ='".$status."',
							   coin_id ='".$ilance->GPC['projectid']."'
			         where id='".$ilance->GPC['banner_id']."'");
				 
		print_action_success('Updated Successfully', $ilpage['staffsettings']);
	    exit();
	 }
	if (isset($ilance->GPC['multiple_banner_download']) AND $ilance->GPC['multiple_banner_download'] == 'multi_banner_download')
	{

		function zipFilesAndDownload($file_names,$archive_file_name,$file_path)
		{


			$zip = new ZipArchive();
			//create the file and throw the error if unsuccessful
			if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE )!==TRUE) {
		    	exit("cannot open <$archive_file_name>\n");
			}
			//add each files of $file_name array to archive
			foreach($file_names as $files)
			{
		  		$zip->addFile($file_path.$files,$files);
				//echo $file_path.$files,$files."<br />";
			}
			$zip->close();
			//then send the headers to foce download the zip file
			header("Content-type: application/zip"); 
			header("Content-Disposition: attachment; filename=Multiple_Banner.zip"); 
			header("Pragma: no-cache"); 
			header("Expires: 0"); 
			readfile("$archive_file_name");
			exit;
		}

 
		$file_names = $ilance->GPC['banner_array'];
		$archive_file_name=tempnam('','Multiple_Banner.zip');
		$file_path='../banner/images/';
		zipFilesAndDownload($file_names,$archive_file_name,$file_path);

	}
		
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'multi_banner_update')
	{
		if(isset($ilance->GPC['image_form_submit']))
		{	 
				$images_arr = array();
				$unique_list_banners = array();
				$row_con_list=0;
	 			$items  = array();
	 			$exit_items = array();
				$count_result = count($_FILES['files']['name']);
				if($count_result <= 15)
				{
					//echo "fine";
				}
				else
				{
						print_action_failed("We're sorry. Please Upload Only 15 files", $_SERVER['PHP_SELF']);
				        exit();
				}

				 

				foreach($_FILES['files']['name'] as $key=>$val)
				{
					$image_name = $_FILES['files']['name'][$key];
					$tmp_name 	= $_FILES['files']['tmp_name'][$key];
					$size 		= $_FILES['files']['size'][$key];
					$type 		= $_FILES['files']['type'][$key];
					$error 		= $_FILES['files']['error'][$key];
					if($size =='')
					{
						print_action_failed("We're sorry. Please Upload valid image files details", $_SERVER['PHP_SELF']);
				        exit();
					}
					if ($type != "image/jpeg")
					{
						print_action_failed("We're sorry. Please Upload valid .jpg extension image files", $_SERVER['PHP_SELF']);
				        exit();
					}
	 				//$string = implode("_",array_reverse(explode("_",$image_name)));                
	                $result= explode(".", $image_name);
	                $sqlquery = $ilance->db->query("select * from ".DB_PREFIX."coins where coin_id='".$result[0]."'");
					$project_res_seller = $ilance->db->fetch_array($sqlquery); 
					if($project_res_seller['coin_id'] == '')
					{
						$items[] = $image_name;
					}
				    $max_coinid = $ilance->db->query("select max(id) as MaxCoinId from ".DB_PREFIX."banner");
					$max_coin = $ilance->db->fetch_array($max_coinid);
					$x = $max_coin['MaxCoinId'];  
					$x += 1;
 					$result_filename= explode(" ", $project_res_seller['Title']);
                    $final_filename = $result_filename[0]."_".$result_filename[1]."_".$result_filename[2]."_".$result[0]."_".$x."_".date('mdy',strtotime($project_res_seller['End_Date'])).".".$result[1];
					$sql7="SELECT * FROM " . DB_PREFIX . "banner WHERE filename = '".$ilance->db->escape_string($final_filename)."'";		
					$result7 = $ilance->db->query($sql7, 0, null, __FILE__, __LINE__);
			 		if($ilance->db->num_rows($result7) > 0)
					{
						$exit_items[] = $image_name;
			 		}
					$images_arr[$tmp_name] = $image_name;
					$unique_list_banners[] = $result[0];
					$row_con_list++;
	 			}
	 			$unique_elements = array_unique($unique_list_banners);
				$totalUniqueElements = count($unique_elements); 
	 			if ($row_con_list != $totalUniqueElements){
				print_action_failed("Please Upload valid Unique Banners file details", $_SERVER['PHP_SELF']);
				exit;
				}
				$Check_coin       = implode("<br>",$items);
			 	$tot_Check_coins  = count($items);
				if($tot_Check_coins > 0)
				{		
				print_action_failed("Please Check the following files coins Id in Live GC:<br> ".$Check_coin."", $_SERVER['PHP_SELF']);
				exit;  	 
			 	}
				$ex_Check_coin       = implode("<br>",$exit_items);
			 	$ex_tot_Check_coins  = count($exit_items);
				if($ex_tot_Check_coins > 0)
				{		
				print_action_failed("Please Check the following File names are already exists:<br> ".$ex_Check_coin."", $_SERVER['PHP_SELF']);
				exit;  	 
			 	}
 
	 			$sucess = array();
				$fail = array();
				foreach($_FILES['files']['name'] as $key=>$val)
			    {
			    	$res_image_name = $_FILES['files']['name'][$key];
					$res_tmp_name 	= $_FILES['files']['tmp_name'][$key];
					$res_size 		= $_FILES['files']['size'][$key];
					$res_type		= $_FILES['files']['type'][$key];
					$res_error 		= $_FILES['files']['error'][$key];
					$hash=md5(microtime());
			    	$coinid = implode("_",array_reverse(explode("_",$res_image_name)));                 
	                $coinid_res= explode(".", $coinid);

	                $sqlquery = $ilance->db->query("select * from ".DB_PREFIX."coins where coin_id='".$coinid_res[0]."'");
					$project_res_seller = $ilance->db->fetch_array($sqlquery); 
 				    $max_coinid = $ilance->db->query("select max(id) as MaxCoinId from ".DB_PREFIX."banner");
					$max_coin = $ilance->db->fetch_array($max_coinid);
					$x = $max_coin['MaxCoinId'];  
					$x += 1;
 					$result_filename= explode(" ", $project_res_seller['Title']);
                    $final_filename = $result_filename[0]."_".$result_filename[1]."_".$result_filename[2]."_".$coinid_res[0]."_".$x."_".date('mdy',strtotime($project_res_seller['End_Date'])).".".$coinid_res[1];

			    	$uploads_dir =DIR_SERVER_ROOT."banner/images/";
			        if(move_uploaded_file($_FILES['files']['tmp_name'][$key], $uploads_dir.$final_filename))
	        		{

	        			$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."coins where coin_id='".$coinid_res[0]."' ");
							$project_res_seller = $ilance->db->fetch_array($sqlquery);
	                    $text = substr($project_res_seller['Title'],0,75);
						$inner_ttt='View the '.$text.' at GC';
						$inner_link=''.HTTP_SERVER.'Coin/'.$project_res_seller['coin_id'].'/'.construct_seo_url_name($project_res_seller['Title']).'';
						$projectid=$project_res_seller['coin_id']; 
						$ilance->db->query("insert into ".DB_PREFIX."banner 
						values (0,'".$ilance->db->escape_string($final_filename)."','".$res_type."','".$res_size."','".$hash."','".$inner_link."','".$ilance->db->escape_string($inner_ttt)."','".$inner_link."','0','0','".DATETIME24H."','','','','','','".$projectid."')");
						 $sucess[] = $final_filename;
						 $resul_variable.='<input type="hidden" name="banner_array[]" value="'.$final_filename.'" />';
	        		}
	 		    }
	 		    $sucess_coin       = implode("<br>",$sucess);
			 	$tot_sucess_coins  = count($sucess);
				if($tot_sucess_coins > 0)
				{		
				print_action_success("Listed Banner files successfully Uploaded in Live GC:<br> ".$sucess_coin."<br><br><form method='post' name='multiple_upload_form' id='multiple_upload_form' enctype='multipart/form-data' action='settings.php'><input type='hidden' name='multiple_banner_download' value='multi_banner_download' />".$resul_variable." <input type='submit' name='downloadzip' id='downloadzip' value='Download Banners' /> </form>", $_SERVER['PHP_SELF']);
				exit;  	 
			 	}

		}		
	}
	
	//Delete Banner
	 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletebanner')
	 {
	    $ilance->db->query("DELETE FROM  ".DB_PREFIX."banner 
	                        where id='".$ilance->GPC['id']."'");
	 }

   
	 
   
       $banner_pagnation = print_pagnation_new($number, $ilconfig['maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);	
	   
	   //New Change on 12Mar02 for #1220

//Add new Show

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] =='coin_show')
{

$date=$ilance->GPC['show_date'];
$desc=$ilance->GPC['show_desc'];

$show_representatives = json_encode($ilance->GPC['show_representatives']); 

$firsttop=$ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_show(coin_date,coin_desc,coin_end_date,coin_show_representatives) values('".$ilance->GPC['show_start_date']."','".$ilance->db->escape_string($ilance->GPC['show_desc'])."','".$ilance->GPC['show_end_date']."','".$show_representatives."')");
	print_action_success("Show Description Added Successfully", $ilpage['settings']);
					exit();
}


//sarath banner order change start


if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] =='banner_order_change')
{



	    $new_listing = $ilance->GPC['banner_order'];	
		$new_listing=explode("\n",$new_listing);	
        $new_listing = array_filter($new_listing, create_function('$a','return preg_match("#\S#", $a);')); 
		$new_listing =array_values($new_listing);
        $str = count($new_listing);
        $banner_list=array();
   
   if ($str >=1 )
   {
		$order=1;
		for($i=0;$i<count($new_listing);$i++)
		{		
			$banner_found = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "banner WHERE filename LIKE  '" . $ilance->db->escape_string(trim($new_listing[$i])) . "'");
			$status = 0;	
		     if ($ilance->db->num_rows($banner_found) ==0)
				{
					$status = 1;
					break;
			    }else
			    { 
			    	while($line = $ilance->db->fetch_array($banner_found))
			    	{
			    		$banner_list[]=$line['id'];
			    	}
			    }
			$order++;
        }


    if($status == 0)
 	{ 		
 		$sql="delete FROM " . DB_PREFIX . "banner_order";
 		$result=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		$bnseq=0;
		foreach($banner_list as $banner_id)
		{
			$bnseq++;
			
			$sql="insert into " . DB_PREFIX . "banner_order (banner_id,sequence) values ('".$banner_id."','".$bnseq."')";
			
 			$result=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		}
	  	print_action_success("Banner updated Successfully", $ilpage['settings']);
					exit();
	}
	else
	{
	   
	   $var =  '<p style="color:red;">ERROR  ::  Image - '.'<b style="font-size:18px;">'.$new_listing[$i].'</b>'.' is not available</p>';

     }

  }
  else
  {
    $var =  '<p style="color:red;">ERROR  :: No Banner Updated </p>';
  
  }
}


//Delete Shows

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

//Update Shows

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
	//testimonial bug id 1329
	
	$counter = ($ilance->GPC['page'] - 1) * 25;
	$scriptpageprevnext = 'settings.php?';
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	 {
	$ilance->GPC['page'] = 1;
	 }
	 else
		 {
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		 }
	$testimonial1 = $ilance->db->query("SELECT *
											FROM " . DB_PREFIX . "testimonial
											");
	
		$testimonial = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "testimonial LIMIT " . (($ilance->GPC['page'] - 1) * 25) . "," . '25'."
											");
											
		$number = (int)$ilance->db->num_rows( $testimonial1);	
											
		while($res_testimonial=$ilance->db->fetch_array($testimonial))
		 {
		  
		    $result['title'] = $res_testimonial['title'];
			$result['description'] = $res_testimonial['description'];
			$result['firstname'] = $res_testimonial['firstname'];
			$result['lastname'] = $res_testimonial['lastname'];
			$result['location'] = $res_testimonial['location'];
			$result['email'] = $res_testimonial['email'];
			$result['date_added'] = $res_testimonial['date_added'];
			
			
	         if ($res_testimonial['status'] == 'accept')
              {
                  $result['status']  = '<a href="settings.php?testcmd=decline&id='.$res_testimonial['id'].'"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to unauthorize credit card" border="0"></a>';
                 }
              else
                {
                       $result['status']  = '<a href="settings.php?testcmd=accept&id='.$res_testimonial['id'].'"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to unauthorize credit card" border="0"></a>';
                   }
				   
				   $result['edit'] = '<a href="settings.php?editcmd=valueedit&id='.$res_testimonial['id'].'"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'icons/pencil.gif" alt="" border="0" /></a>';
				   
				   	  $result['remove'] = '<a href="settings.php?deletecmd=deletevalue&id='.$res_testimonial['id'].'"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'icons/delete.gif" alt="" border="0" /></a>';
				   
				   
				   
			$total_testimonial[] = $result;
			
		 }	
		 
	$prof = print_pagnation($number, 25, $ilance->GPC['page'], $counter, $scriptpageprevnext);	 								
			
  if (isset($ilance->GPC['testcmd']) AND $ilance->GPC['testcmd'] == 'accept')
    {
			$acept=$ilance->db->query("UPDATE " . DB_PREFIX . "testimonial set status='accept' where id ='".$ilance->GPC['id']."'");						
			print_action_success('The Testimonial Status Changed Succecfully', $ilpage['staffsettings']);
			exit();
	}

	
  if (isset($ilance->GPC['testcmd']) AND $ilance->GPC['testcmd'] == 'decline')
    {
			$acept=$ilance->db->query("UPDATE " . DB_PREFIX . "testimonial set status='decline' where id ='".$ilance->GPC['id']."'");						
			print_action_success('The Testimonial Status Changed Succecfully', $ilpage['staffsettings']);
			exit();
	}	
	

	
										
  if (isset($ilance->GPC['editcmd']) AND $ilance->GPC['editcmd'] == 'valueedit')
    {
	$show['edit_coin']=true;
	  $edit_testimonial = $ilance->db->query("SELECT *
											  FROM " . DB_PREFIX . "testimonial where id ='".$ilance->GPC['id']."'
											"); 
		$res_test=$ilance->db->fetch_array($edit_testimonial);
		 $id = $ilance->GPC['id'];
		 $title_monial = $res_test['title'];									
		$description_monial = $res_test['description'];	
		$first_name = $res_test['firstname'];
		$last_name = $res_test['lastname'];
	   $location = $res_test['location'];
	   $email = $res_test['email'];
											
	}
  if (isset($ilance->GPC['monial_update']) AND $ilance->GPC['monial_update'] == 'testimonial_update')
    {
              $ilance->db->query("
                                UPDATE " . DB_PREFIX . "testimonial
                                SET title = '".$ilance->GPC['title']."',
                                 description = '".$ilance->GPC['description']."',
								 firstname = '".$ilance->GPC['firstname']."',
								 lastname = '".$ilance->GPC['lastname']."',
								 email = '".$ilance->GPC['email']."',
								  location = '".$ilance->GPC['location']."'
								 where id = '".$ilance->GPC['id']."'
                        ");
	print_action_success('The Testimonial Edit  Changed Succecfully', $ilpage['staffsettings']);
			exit();
	}
	
	
	
	if (isset($ilance->GPC['deletecmd']) AND $ilance->GPC['deletecmd'] == 'deletevalue' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
    {
	  
	   $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "testimonial
                        WHERE id = '".$ilance->GPC['id']."'
                ");
	  	print_action_success('The Testimonial Deleted  Successfully', $ilpage['staffsettings']);
			exit();
	}
	
  if (isset($ilance->GPC['secrchcmd']) AND $ilance->GPC['secrchcmd'] == 'search_insert')
    {
	  $ilance->db->query("INSERT INTO " . DB_PREFIX . "searchtext(search_text,define_search) values('".$ilance->GPC['search_text']."','".$ilance->GPC['define_search']."')");
	}
	
	
	
 $banner_list_name = $ilance->db->query("SELECT *
				                        FROM " . DB_PREFIX . "banner_order o left join " . DB_PREFIX . "banner b on b.id=o.banner_id order by o.id asc"		                        
									  );
		while($ress_banner=$ilance->db->fetch_array($banner_list_name))
		{
		$ress_banner['filename']=$ress_banner['filename']."\n";
		
		 $banner_name_list[]=$ress_banner;
		}

		 $banner_list_name_live = $ilance->db->query("SELECT b.filename as bannerfile FROM " . DB_PREFIX . "banner b 
left join " . DB_PREFIX . "projects p on b.coin_id = p.project_id
left join " . DB_PREFIX . "banner_order o on b.id = o.banner_id 
WHERE p.status = 'open' and b.id not in (SELECT banner_id
FROM " . DB_PREFIX . "banner_order)");

		while($ress_banner_live=$ilance->db->fetch_array($banner_list_name_live))
		{
		$ress_banner_live['bannerfile']=$ress_banner_live['bannerfile']."\n";
		
		 $banner_name_list_live[]=$ress_banner_live;
		}
		
		// For Bug #4813
$staff_lists = array(1=>array('name'=>'Ian Russell', 'desaignation'=>'Owner/President', 'email'=>'ian@greatcollections.com
'),3=>array('name'=>'Raeleen Endo', 'desaignation'=>'Owner/Client Services', 'email'=>'raeleen@greatcollections.com'), 4=>array('name'=>'Juliann Gim', 'desaignation'=>'Client Services', 'email'=>'juliann@greatcollections.com'));

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
		
	 $pprint_array = array('var','search_text','prof','id','description_monial','title_monial','first_name','last_name','location','email','prevnext1','prevnext','global_staff','nodays','feeamount','servicename','grading','service_pulldown','hiddenfieldsubcmdservice','hiddenfieldsubcmdgrading','hiddenid','hiddendo','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','email_pdf','banner_pagnation','link','inner_text','inner_text_link','inner_text1','inner_text_link1','inner_text2','inner_text_link2','projectid','order','status','bannerid','coin_show_pagnation','coin_date','coin_end_date','coin_desc','show_representatives');
                
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'settings.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('banner_name_list',
	'gradinglist',
	'servicelist',
	'banner_list',
	'coin_shows_list',
	'total_testimonial',
	'banner_list1',
	'banner_list_name1','banner_name_list_live'));
	//	'banner_list_name',
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
