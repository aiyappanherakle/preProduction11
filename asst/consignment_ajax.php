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
	'flashfix',
	'jquery'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1' or $_SESSION['ilancedata']['user']['isadmin'] == '1' )
{
    
	 
if(isset($ilance->GPC['set_title'])) 
	 {
	 
	 $project_id_array	=	explode(',',$ilance->GPC['project_id']);
	 if($ilance->GPC['project_id']=="")

	 {
		echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Listing</a></li></ul></div></div>
		<table style="border:1px solid #000000;border-collapse:collapse; float: left;" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Please Select Atleast one Ebay coins To List ?</b></td></tr></tbody><table>';	
		exit;


	 }else
	 {
		// echo '<pre>';
		// print_r($project_id_array);
		// exit;
		foreach($project_id_array	 as $vproject_id)
		{
		
				$listed_query = $ilance->db->query("
				SELECT  *
				FROM " . DB_PREFIX . "coins 
				WHERE coin_listed = 'c'
				AND coin_id  = '".$vproject_id."'
				AND Site_Id = '0'
				"); 


				if($ilance->db->num_rows($listed_query) > 0)
				{
				echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Listing</a></li></ul></div></div>
				<table style="border:1px solid #000000;border-collapse:collapse; float: left;" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Please Select only Ebay coins To List ?</b></td></tr></tbody><table>';	
				exit;
										
				}
			
		}
		$image_array	=	array();
		foreach($project_id_array	 as $project_id)
		{
			if(!fetch_image_check($project_id, 'itemphoto'))
			{
				$image_array[]	=	$project_id;
			}
		}
		
		if(count($image_array)>0){
		
		
		echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Listing Image</a></li></ul></div></div>
		<table style="border:1px solid #000000;border-collapse:collapse; float: left;" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Following Coins not having image</b></td></tr>
		<tr><td width="175px" style="background-color:#ffffff;border:1px solid #000000;color:#000000"><b>'.implode(',',$image_array).'</b></td></tr></tbody><table>';	
					
		}else{
		foreach($project_id_array	 as $projs)
		{
		post_images_to_ebay($projs);
		}		
		$set_title_frm	.=	'<form id="titleFrm" name="titleFrm" method="post" action="">
		';
		$set_title_frm	.=	'<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Listing</a></li></ul></div></div><table width="100%" cellspacing="0" cellpadding="9" border="0" >';

		$set_title_frm	.='	<tbody>
                                <tr class="alt2">
								<td>Edit Title</td>
								</tr>';
		$i=0;
		$set_ajax_data	=	0;
			foreach($project_id_array	 as $project_id)
			{
			$i++;
			
				$actual_title	=	fetch_image_title($project_id);
				
				$sql2="SELECT `ebay_title`  FROM " . DB_PREFIX . "coins WHERE  coin_id='".$project_id."'"; //itemphoto
				$flag	=	false;
				$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($res2)>0)
				{
					if($line2=$ilance->db->fetch_array($res2))
					{
						if($line2['ebay_title']!=""){
							$flag	=	true;
						}
					}
				}
			/*if($flag	==	false){
					if(strlen($actual_title)<80 ){
						$sql2="update  " . DB_PREFIX . "coins set ebay_title ='$actual_title' WHERE  coin_id='".$project_id."'"; //itemphoto
					$res2 = $ilance->db->query($sql2);
					}else{*/
						$set_ajax_data++;
						$title			=	substr($actual_title,0,79);
						//$title	=	'test'.$project_id;
						

						$set_title_frm	.=	'<tr><td ><strong> Coin id:'.$project_id.'</strong></tr>';
						$set_title_frm	.=	'<tr><td><input style="padding: 6px;height: 29px;width: 516px;" type="text" maxlength=80 name="set_ebay_title['.$project_id.']" value="'.$title.'"/></td></tr><tr><td><hr></td></tr>';
					//}
				//}
			}
		$set_title_frm	.=	'<tr><td><input type="button" name="submit" value="Submit" onclick="set_ebay_title_to_coins();"/></td></tr>';
		$set_title_frm	.=	'</table>';
		$set_title_frm	.="</form>";
		if($set_ajax_data!=0){
			echo $set_title_frm;
		}
		}
	 }
	 exit;
		
	 }
	 if(isset($ilance->GPC['set_title_db'])) 
	 {
	 $coin_array	=	array();
	 foreach($ilance->GPC['set_ebay_title'] as $project_id	=>	$set_ebay_title){
		 $sql2="update  " . DB_PREFIX . "coins set ebay_title ='$set_ebay_title' WHERE  coin_id='".$project_id."'"; //itemphoto
		$res2 = $ilance->db->query($sql2);
		 //$sql2="update  " . DB_PREFIX . "projects set ebay_title ='$set_ebay_title' WHERE  project_id='".$project_id."'"; //itemphoto
		//$res2 = $ilance->db->query($sql2);
		$coin_array[]	=	$project_id;
		}
		
		echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Listing</a></li></ul></div></div>
		<table style="border:1px solid #000000;border-collapse:collapse" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Coin Ebay title updated</b></td></tr><tr><td width="175px" style="background-color:#ffffff;border:1px solid #000000;color:#000000"><b>'.implode(',',$coin_array).'</b></td></tr></tbody><table>
		<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Image Uploads</a></li></ul></div></div>
		<table style="border:1px solid #000000;border-collapse:collapse; float: left;" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Selected Ebay coins Images Uploads are sucessfully through Ebay Api.</b></td></tr></tbody><table>';	
		
		/*
		echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Listing</a></li></ul></div></div>
		<table style="border:1px solid #000000;border-collapse:collapse" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Coin Ebay title updated</b></td></tr><tr><td width="175px" style="background-color:#ffffff;border:1px solid #000000;color:#ffffff"><b>'.implode(',',$coin_array).'</b></td></tr></tbody><table>';	*/
		exit;
		
	 }
	 
	 
    
	//year search of pcgs
	 if(isset($ilance->GPC['text']))
		{
		
		$open = explode('-',$ilance->GPC['query']);
		
		if(isset($open['1']))
		{
		$quer = "and coin_detail_mintmark='".$open['1']."'";
		}
		else
		{
		$quer = '';
		}
		//id, Orderno, PCGS, coin_series_unique_no, coin_series_denomination_no, coin_detail_year, coin_detail_mintmark, coin_detail_coin_series, coin_detail_denom_long, coin_detail_denom_short, coin_detail_proof, coin_detail_suffix, coin_detail_major_variety, coin_detail_die_variety, coin_detail_key_date, coin_detail_mintage, coin_detail_low_mintage, coin_detail_weight, coin_detail_composition, coin_detail_diameter, coin_detail_designer, coin_detail_description_long, coin_detail_description_short, coin_detail_notes, coin_detail_ngc_no, coin_detail_ebay_heading, coin_detail_ebay_category, coin_detail_related_coins, coin_detail_meta_description, coin_detail_meta_title, coin_detail_image, coin_detail_image_alt, coin_detail_sort, coin_detail_coin_series_no, nmcode
		$searchpart=addslashes(trim($ilance->GPC['query']));
		
		
		$result=$ilance->db->query("SELECT PCGS,coin_detail_year,coin_detail_coin_series,coin_detail_major_variety,coin_detail_mintmark,coin_detail_suffix,coin_detail_denom_short,coin_detail_proof FROM " . DB_PREFIX . "catalog_coin WHERE coin_detail_year  = '".$open[0]."' ".$quer." ORDER BY  Orderno ASC");
			$count=$ilance->db->num_rows($result);
			if($ilance->db->num_rows($result)>0)
			{
			$i=0;
			/*$html="{query:'".$open['0']."',suggestions:[";*/
			
			$html='<table width="100%" cellspacing="0" cellpadding="9" border="0">
                                <tbody><tr class="alt2">
								
								<td>PCGS</td>
								<td></td>
								<td>Title</td>
								<td></td>
								<td>Select</td>
							    </tr>';
				while($row=$ilance->db->fetch_array($result))
				{
				
				            
				                $pro_tes =  $row['coin_detail_proof'];
								if($pro_tes == 'y')
								$text_pro = ' PR-'.$pc_proof;
								else if($pro_tes == 's')
								$text_pro = ' SP-'.$pc_proof;
								else
								$text_pro = ' '.$open['1'];
								
								
								
								
								//$title  =  $row['coin_detail_year'].''.(empty($row['coin_detail_mintmark'])) ? '' : '-'.$row['coin_detail_mintmark'].' '.$row['coin_detail_coin_series'].''.(empty($row['coin_detail_major_variety'])) ? '' : '-'.$row['coin_detail_major_variety'].''.(empty($row['coin_detail_suffix'])) ? '' : '-'.$row['coin_detail_suffix'].''.$text_pro;
								$myverm = ($row['coin_detail_mintmark'] == '') ? '' : '-'.$row['coin_detail_mintmark'];
								
								$myver = ($row['coin_detail_major_variety'] == '') ? '' : '-'.$row['coin_detail_major_variety'];
								
								$suf = ($row['coin_detail_suffix'] == '') ? '' : ''.$row['coin_detail_suffix'];
								
								//normal ajax title
								
								 $title  =  $row['coin_detail_year'].''.$myverm.' '.$row['coin_detail_coin_series'].''.$myver.' '.$suf.''.$text_pro;
								 
					$html.='<tr><td><span class="blue">'.$row['PCGS'].'</span><td><td><strong>'.$title.'</strong><td><td>
					<span class="blue" style="cursor:pointer;" onclick="return pcgs_select(\''.$row['PCGS'].'\');">Click</span><td></tr>';			 
				}	 
				$html.='</table>';
		/*$suggestions[$i]="'".$row['PCGS']." ".$title."'";
		$ids[$i]="'".$row['PCGS']."'";
		$i++;
				}
			$html.=implode(",",$suggestions);
			$html.="],data:[".implode(",",$ids)."]}";	*/
				echo $html;
		}
		}
		
		
//sekar works on listings on may 10
		
	if (isset($ilance->GPC['txt']))
 {
 
 $itemid = $ilance->GPC['txt'];
        
		$boldsel=$ilance->db->query("SELECT bold FROM " . DB_PREFIX . "projects WHERE project_id = '".$itemid."'");
		  $boldlist = $ilance->db->fetch_array($boldsel);
		  
		  if($boldlist['bold'])
		         {
		    $upbold = $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET bold = '0' WHERE  project_id = '".$itemid."'"); 
		  }
		  
		  else
		     {
			     $upbold = $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET bold='1' WHERE  project_id = '".$itemid."'"); 
			   }
			   
 }
 
		
//highlite values
	if (isset($ilance->GPC['hight']))
 {
 
 $itemid = $ilance->GPC['hight'];
        
		$boldsel=$ilance->db->query("SELECT highlite FROM " . DB_PREFIX . "projects WHERE project_id = '".$itemid."'");
		  $boldlist = $ilance->db->fetch_array($boldsel);
		  
		  if($boldlist['highlite'])
		         {
		    $upbold = $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET highlite = '0' WHERE  project_id = '".$itemid."'"); 
		  }
		  
		  else
		     {
			     $upbold = $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET highlite = '1' WHERE  project_id = '".$itemid."'"); 
			   }
			   
 }
		
//featured values
		if (isset($ilance->GPC['feat']))
 {
 
 $itemid = $ilance->GPC['feat'];
        
		$boldsel=$ilance->db->query("SELECT featured FROM " . DB_PREFIX . "projects WHERE project_id = '".$itemid."'");
		  $boldlist = $ilance->db->fetch_array($boldsel);
		  
		  if($boldlist['featured'])
		         {
		    $upbold = $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET featured = '0' WHERE  project_id = '".$itemid."'"); 
		  }
		  
		  else
		     {
			     $upbold = $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET featured = '1' WHERE  project_id = '".$itemid."'"); 
			   }
			   
 }	
 
 //sekar finished on may10		
	//new id for attchment image search
	if(isset($ilance->GPC['newidsearch']))
	{
	         echo $table_row = ' <form method="post" action="listings.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				             <input type="hidden" name="cmd" value="image_list" />
							 <input type="hidden" name="subcmd" value="image_list_deleted" />
				             <input type="hidden" name="return" value="listings.php?cmd=image_list" /><table width="100%" cellspacing="0" cellpadding="6" border="0" class=""><tr>';
			  
	                        $image_sql1 = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "attachment
							WHERE coin_id='".$ilance->GPC['item']."'
							 ORDER BY coin_id,cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED)
							");
						
						    $image_atc_total = $ilance->db->query("
							 SELECT count(coin_id) as end
							 FROM " . DB_PREFIX . "attachment 
							 WHERE coin_id='".$ilance->GPC['item']."'
							 ORDER BY coin_id,cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED)
							 ");
							 
						    $res_total= $ilance->db->fetch_array($image_atc_total);
						
						    //count by divide
						    $count_tol = $res_total['end'];
						
						    $cou = 1;	
							if($ilance->db->num_rows($image_sql1) > 0)
				            {	    
								 while ($res_atc = $ilance->db->fetch_array($image_sql1))
								 {	
									
									 echo $table_row = '<tr><td nowrap="nowrap"><input type="checkbox" value="'.$res_atc['attachid'].'" name="attach[]"> '.$res_atc['filename'].' -- '.$res_atc['attachtype'].'</td></tr>';
									  
									  $txt =  (fetch_coin_table('project_id',$res_atc['coin_id']) == '0') ? 'Holdings' : 'Listings';
							   
									  if($cou == $count_tol)
									   {
									  echo $table_row = '<tr><td nowrap="nowrap"><strong>Coin ID : </strong>'.$res_atc['coin_id'].'</td><td nowrap="nowrap"><strong>Consignor Name :</strong> '.fetch_user('username',fetch_coin_table('user_id',$res_atc['coin_id'])).'</td></tr><tr><td nowrap="nowrap"><strong>Title :</strong> '.fetch_coin_table('Title',$res_atc['coin_id']).'</td></tr><tr><td nowrap="nowrap"><strong>Min Bid/Buy Now :</strong> '.$ilance->currency->format(fetch_coin_table('Minimum_bid',$res_atc['coin_id'])).' / '.$ilance->currency->format(fetch_coin_table('Buy_it_now',$res_atc['coin_id'])).'</td></tr><tr><td nowrap="nowrap"><strong>Live on website : </strong>'.$txt.'</td></tr><tr class="alt1" valign="top"><td nowrap="nowrap"></td><td nowrap="nowrap"></td></tr>';
									   }
											 
									
											
										
										$cou++;
								 }
								 
							echo $table_row = '<tr><input type="submit" value="Delete" /></tr>															
									</table>
									
									</form>';	 
						    }
						 
						   else
				           {
				            echo $table_row = '<tr><td nowrap="nowrap">No Result Found</td></tr>';
				           }
				
				
				
				
				
	   
	}
			
     //seller amount for fvf cal
	 if(isset($_GET['amount_calc']))
	 {
		$amount = $_GET['amount_calc'];
		$fvf    = $_GET['fvfvalue'];
		
		echo $calc = $amount * $fvf / 100;
	 }
	 //email check for buyer in aff.listing
	 if(isset($_GET['email_check']))
	 {
	 
		   if ($ilance->common->is_email_valid(trim($ilance->GPC['email_check'])) == false)
			{
					echo 'email_not_valid';
					exit();
			}
			$sqlusercheck = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "users
					WHERE email = '" . trim($ilance->GPC['email_check']) . "'
			");
			if ($ilance->db->num_rows($sqlusercheck) > 0)
			{
			$row_user = $ilance->db->fetch_array($sqlusercheck);
			
					echo 'exit';exit();
			}
			else
			{
				   echo 'not_exit';
				   exit();
				
			}
	
	 }
	 //same hidden value
	 if(isset($_GET['email_details_user']))
	 {
	   $sqlusercheck = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "users
					WHERE email = '" . trim($ilance->GPC['email_details_user']) . "'
			");
		
			if ($ilance->db->num_rows($sqlusercheck) > 0)
			{
			$row_user = $ilance->db->fetch_array($sqlusercheck);
			
			  echo '<table width="100%" cellspacing="0" cellpadding="9" border="0"><tr class="alt1"><td>Username</td><td>'.$row_user['username'].'</td></tr><tr class="alt1"> 
								  
									<td nowrap="nowrap" class="alt1"><span class="gray">First name</span></td>
									<td>'.$row_user['first_name'].'<input type="hidden" name="buyer_id" value="'.$row_user['user_id'].'"></td></tr><tr class="alt1"> 
									<td nowrap="nowrap" class="alt1"><span class="gray">Last name</span></td>
									<td>'.$row_user['last_name'].'</td>
								</tr></table>';
					
			}
	 }
	 if(isset($_GET['all_site_name']))
	 {
	   if($_GET['all_site_name'] == '0')
	   {
	   echo 'GC';
	   }
	   else
	   {
	    echo fetch_user_siteid('site_name',$_GET['all_site_name']);
	   }
	 
	 }
	 
	 if(isset($_GET['all_listed_value']))
	 {
		if($_GET['all_listed_value'] == '0')
		{
											if($_GET['searchid'] == '1')
											{         
											$con_listing = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "coins c,
											" . DB_PREFIX . "projects p
											WHERE c.status = '0'
											AND c.project_id = p.project_id
											");
											
											}
											else
											{
											$con_listing = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "coins c,
											" . DB_PREFIX . "projects p
											WHERE c.status = '0'
											AND c.project_id = p.project_id
											 AND  (c.coin_id = '".$_GET['searchid']."' OR
												  c.user_id = '".$_GET['searchid']."')
											");
											
									
											}
								
											$number = (int)$ilance->db->num_rows($con_listing);
											if($ilance->db->num_rows($con_listing) > 0)
											{
											$row_con_list = 0;
											 
											 echo '<table width="100%" cellpadding="9" cellspacing="0">
									<tr class="alt2">
									<td width="12%" align="left">Coin ID</td>
									<td width="12%">User</td>
									<td width="12%">Status</td>
									<td width="12%">End Date</td>
									<td width="12%">No.of.bids</td>
									<td width="12%">Buy Now Price</td>
									<td width="12%">Finalvalue</td>
									
								  </tr>';
												while($row_list = $ilance->db->fetch_array($con_listing))
												{			
												$date_coin = explode('-',$row_list['date_end']);	
												$month_name = date( 'M', mktime(0, 0, 0, $date_coin[1]) );					
												$row_list['coin_id'] = $row_list['coin_id'];
												$row_list['status'] = 'GC'; 
												$row_list['date_sent'] = $date_coin[1].'-'.$month_name.'-'.$date_coin[0]; 
												$row_list['bids'] = $row_list['bids'];
												$row_list['user'] = fetch_user('username', $row_list['user_id']); 
												$row_list['price']    = $row_list['Reserve_Price'];  
												$row_list['final']    = $row_list['final_fee_percentage'];         
												$gc_list[] = $row_list;
												
												echo '<tr class="alt1" valign="top">
									<td width="12%" align="left">'.$row_list['coin_id'].'</td>
									<td width="12%">'.$row_list['user'].'</td>
									<td width="12%">'.$row_list['status'].'</td>
									<td width="12%">'.$row_list['date_sent'].'</td>
									<td width="12%">'.$row_list['bids'].'</td>
									<td width="12%">'.$row_list['price'].'</td>
									<td width="12%">'.$row_list['final'].'</td></tr>';
												$row_con_list++;
												
												}
												echo '</table>';
											
											}
											
											else
											{				
											  echo '<table width="100%" cellpadding="9" cellspacing="0">
													<tr class="alt2">
													<td width="12%" align="left">Coin ID</td>
													<td width="12%">User</td>
													<td width="12%">Status</td>
													<td width="12%">Buy Now Price</td>
													<td width="12%">Finalvalue</td>
													<td width="12%">Update Sale</td>
													</tr><tr class="alt1" valign="top">
													<td align="center" nowrap="nowrap" colspan="11">No results found</td></tr></table>';
											}
							  
							
		}
		else
		{ 
		  if($_GET['searchid'] == '1')
		  {
		   $con_listing = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins
									WHERE status = '".$_GET['all_listed_value']."'
									");
		  }
		  else
		  {
		  
		
		  $con_listing = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins
									WHERE status = '".$_GET['all_listed_value']."'
									 AND  (coin_id = '".$_GET['searchid']."' OR
										   user_id = '".$_GET['searchid']."')
									");
									
									
		  }
		
		  
								   
						
									$number = (int)$ilance->db->num_rows($con_listing);
									if($ilance->db->num_rows($con_listing) > 0)
									{
									$row_con_list = 0;
									 
									echo  '<table width="100%" cellpadding="9" cellspacing="0">
											<tr class="alt2">
											<td width="12%" align="left">Coin ID</td>
											<td width="12%">User</td>
											<td width="12%">Status</td>
											<td width="12%">Buy Now Price</td>
											<td width="12%">Finalvalue</td>
											<td width="12%">Update Sale</td>
											</tr>';
										while($row_list = $ilance->db->fetch_array($con_listing))
										{			
										//$date_coin = explode('-',$row_list['date_end']);	
										//$month_name = date( 'M', mktime(0, 0, 0, $date_coin[1]) );					
										$row_list['coin_id'] = $row_list['coin_id'];
										$row_list['status'] = fetch_user_siteid('site_name',$row_list['Site_Id']);
										//$row_list['date_sent'] = $date_coin[1].'-'.$month_name.'-'.$date_coin[0]; 
										//$row_list['bids'] = $row_list['bids'];
										$row_list['user'] = fetch_user('username', $row_list['user_id']); 
										$row_list['price']    = $row_list['Reserve_Price'];  
										$row_list['final']    = $row_list['final_fee_percentage']; 
										$aff_buyer = fetch_user_aff_buyer('coin_id',$row_list['coin_id']); 
										if($row_list['coin_id'] == $aff_buyer)
										{
										  $update_list = '<strong>Sold</strong>';
										} 
										else
										{
										 $update_list = '<span style="cursor:pointer;"  onClick="window.open(\'listings.php?cmd=affiliate_update&coin_id='.$row_list['coin_id'].'&seller_id='.$row_list['user_id'].'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')"><strong>Update</strong></span>';
										}      
										$gc_list[] = $row_list;
												
									  echo '<tr class="alt1" valign="top">
											<td width="12%" align="left">'.$row_list['coin_id'].'</td>
											<td width="12%">'.$row_list['user'].'</td>
											<td width="12%">'.$row_list['status'].'</td>
											<td width="12%">'.$row_list['price'].'</td>
											<td width="12%">'.$row_list['final'].'</td>
											<td width="12%">'.$update_list.'</td>
											</tr>';
												$row_con_list++;
												
												}
												echo '</table>';
											
											}
											
											else
											{
												
											 echo '<table width="100%" cellpadding="9" cellspacing="0">
													<tr class="alt2">
													<td width="12%" align="left">Coin ID</td>
													<td width="12%">User</td>
													<td width="12%">Status</td>
													<td width="12%">Buy Now Price</td>
													<td width="12%">Finalvalue</td>
													<td width="12%">Update Sale</td>
													</tr>
													<tr class="alt1" valign="top">
													<td align="center" nowrap="nowrap" colspan="11">No results found</td>
													</tr></table>';
									
											}
		}
	 
	 
	 } 
	 
	 //pending list 
 
	 if(isset($_GET['pending_autobuild_con']))
	 {
								   $con_listing_co = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins 
									WHERE user_id ='".$_GET['pending_autobuild_user']."'
									AND consignid = '".$_GET['pending_autobuild_con']."'
									AND coin_listed = 'c'
									AND (End_Date = '0000-00-00' OR pending = '1')	
									AND project_id  = '0'
									AND status = '0'
									
									group by pcgs,Grade,nocoin order by pcgs desc,Grade desc;
									");
									
									
									if($ilance->db->num_rows($con_listing_co) > 0)
									{
									    while($row_list = $ilance->db->fetch_array($con_listing_co))
										{	
										
										   $row[] = $row_list['coin_id'];
										   
										   
										  
										
										}
										
										 echo $txt = implode('|',$row);
									}
									else
									{
									
									}
										 
		}	
		
	 if(isset($_GET['pending_autobuild_con_new']))
	 {
								   $con_listing_co = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins 
									WHERE user_id ='".$_GET['pending_autobuild_user']."'
									AND coin_listed = 'c'
									AND (End_Date = '0000-00-00' OR pending = '1')	
									AND project_id  = '0'
									AND status = '0'
									
									group by Title,pcgs,Grade,nocoin order by pcgs desc,Grade desc;
									");
									
									
									if($ilance->db->num_rows($con_listing_co) > 0)
									{
									    while($row_list = $ilance->db->fetch_array($con_listing_co))
										{	
										
										   $row[] = $row_list['coin_id'];
										}
										
										 echo $txt = implode('|',$row);
									}
									else
									{
									
									}
										 
		}	
		
		//New change on 12Jan-04
		
		 if(isset($_GET['block_coin_id']))

	 {
	 
	     $con_listing = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
											WHERE project_id = '".$_GET['block_coin_id']."'
											AND visible = '3'
											AND (status ='expired'
										    OR status ='closed')
							              ");
						
			if($ilance->db->num_rows($con_listing) > 0)
			{
			  
			  echo 'Already'.$_GET['block_coin_id'].' have been Blocked from Displaying in Auction Archive';
			}
			else
			{  
	 
			 $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
							     SET  visible = '3'
								 WHERE  project_id = '".$_GET['block_coin_id']."'
								   AND (status ='expired'
										OR status ='closed')
																		");
			 echo $_GET['block_coin_id'].' have been Blocked from Displaying in Auction Archive';
	 
	     }
	 
	 }
	 
	   if(isset($_GET['ended_list_user']))

	 {

								   $con_listing_co = $ilance->db->query("

									SELECT *

									FROM " . DB_PREFIX . "projects 

									WHERE user_id ='".$_GET['ended_list_user']."'

									AND (status =  'expired'
                                            OR status =  'closed')
											
									order by date_end desc		

										 ");

									$number = (int)$ilance->db->num_rows($con_listing_co);

									if($ilance->db->num_rows($con_listing_co) > 0)

									{

									$row_con_list = 0;

									 

									echo  '<div class="block-wrapper">

						                        <div class="block3">

						

								<div class="block3-top">

										<div class="block3-right">

												<div class="block3-left"></div>

										</div>

								</div>

								

								<div class="block3-header">

								'.$number.' List of Auction Archive by <strong>'.fetch_user('username', $_GET['ended_list_user']).'</strong>  
								
								</strong>  

								</div>

								<div class="block3-content" style="padding:0px">
								
											 <form method="post" action="auction_archive.php" accept-charset="UTF-8"  style="margin: 0px;">
				                              <input type="hidden" name="subcmd" value="update_visible" />
											  <input type="hidden" name="user_id" value="'.$_GET['ended_list_user'].'" />

											<table width="100%" cellpadding="9" cellspacing="0">

											<tr class="alt2">
											

											<td width="12%" align="left">Coin ID</td>

									         <td width="12%">PCGS</td>

									         <td width="12%">Coin</td>
											 
											 <td width="12%">Date Ended</td>

									        <td width="12%" align="left">Block Coin</td>

											</tr>';

										while($row_list = $ilance->db->fetch_array($con_listing_co))

										{	$row_list['coin_id'] = $row_list['project_id'];

											$row_list['pcgs'] = $row_list['cid']; 

											$row_list['Description'] = $row_list['Description']; 

                                           if($row_list['visible']=='3')
										   {
										     $sel='checked="yes"';
											}
											else
											{
											$sel='';
											} 
											 $row_list['check']='<input type="checkbox" value="'.$row_list['coin_id'].'" name="incheckdate[]" '.$sel.'>';

									  echo '<tr class="alt1" valign="top">

									

									<td width="12%" align="left">'.$row_list['coin_id'].'</td>

									<td width="12%">'.$row_list['pcgs'].'</td>

									<td width="12%" >'.$row_list['project_title'].'</td>
									
									<td width="12%" >'.date("F d, Y",strtotime($row_list['date_end'])).'</td>
									
									<td width="12%" align="left">'.$row_list['check'].'</td>

								
									</tr>';

												

												

												}

												echo '<tr valign="top"><td width="12%" nowrap="nowrap" align="center"> <input type="submit" value="Update" name="datevalnew_save"></td></tr></table></form></div>

								

								<div class="block3-footer">

										<div class="block3-right">

												<div class="block3-left"></div>

										</div>

								</div>

								

						</div>

					</div>';

											

											}

											

											else

											{

												

											 echo '<div class="block-wrapper">

						<div class="block3">

						

								<div class="block3-top">

										<div class="block3-right">

												<div class="block3-left"></div>

										</div>

								</div>

								

								<div class="block3-header">'.$number.' List of Auction Archive by '.fetch_user('username', $_GET['ended_list_user']).'</div>

								<div class="block3-content" style="padding:0px"><table width="100%" cellpadding="9" cellspacing="0">

													<tr class="alt2">

													<td width="12%" align="left">Coin ID</td>

													<td width="12%">User</td>

													<td width="12%">Status</td>

													<td width="12%">Buy Now Price</td>

													<td width="12%">Finalvalue</td>

													<td width="12%">Update Sale</td>

													</tr>

													<tr class="alt1" valign="top">

													<td align="center" nowrap="nowrap" colspan="11">No results found</td>

													</tr></table></div>

								

								<div class="block3-footer">

										<div class="block3-right">

												<div class="block3-left"></div>

										</div>

								</div>

								

						</div>

					</div>';

									

											}

	 }
		
			
	 if(isset($_GET['pending_list_user']))
	 {
								   $con_listing_co = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins 
									WHERE user_id ='".$_GET['pending_list_user']."'
									AND consignid = '".$_GET['pending_list_con']."'
									AND coin_listed = 'c'
									AND (End_Date = '0000-00-00' OR pending = '1')	
									AND project_id  = '0'
									AND status = '0'
										 ");
									$number = (int)$ilance->db->num_rows($con_listing_co);
									if($ilance->db->num_rows($con_listing_co) > 0)
									{
									$row_con_list = 0;
									 
									echo  '<div class="block-wrapper">
						                        <div class="block3">
						
								<div class="block3-top">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
								<div class="block3-header">
								'.$number.' List of pendings by <strong>'.fetch_user('username', $_GET['pending_list_user']).'</strong>  
								
								</strong>  
								</div>
								<div class="block3-content" style="padding:0px"><table width="100%" cellpadding="9" cellspacing="0">
								<tr class="alt2" nowrap="nowrap">
											
									<td width="12%" nowrap="nowrap">Set end date for consignment <strong>'.fetch_user('username', $_GET['pending_list_user']).'</strong> <span class="blue" style="cursor:pointer;"  onClick="window.open(\'pendings.php?cmd=all_update&subcmd=allcoin&user_id='.$_GET['pending_list_user'].'&consignid='.$_GET['pending_list_con'].'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Click Here</span></td><td width="12%"></td><td width="12%"></td><td width="12%"></td><td width="12%"></td><td width="12%"></td><td width="12%"><span class="blue" style="cursor:pointer;" onclick="checkautobuild('.$_GET['pending_list_con'].','.$_GET['pending_list_user'].');"><b>Autobuild</b></span></td><td width="12%"></td>
											</tr></table>
											 <form method="post" action="pendings.php" accept-charset="UTF-8"  style="margin: 0px;">
				    <input type="hidden" name="subcmd" value="allcheck_update_date" />
											<table width="100%" cellpadding="9" cellspacing="0">
											<tr class="alt2">
											<td width="12%" align="left">Check</td>
											<td width="12%" align="left">Coin ID</td>
									<td width="12%">PCGS</td>
									<td width="12%">Coin</td>
									<td width="12%">Min Bid</td>
									<td width="12%">Reserve</td>
									<td width="12%">Bin</td>
									<td width="12%">End Date</td>
									<td width="12%">Edit</td>
									
									<td width="12%">Return</td>
											</tr>';
										while($row_list = $ilance->db->fetch_array($con_listing_co))
										{	$row_list['coin_id'] = $row_list['coin_id'];
											$row_list['pcgs'] = $row_list['pcgs']; 
											$row_list['Description'] = $row_list['Description']; 
											$row_list['Minimum_bid'] = $row_list['Minimum_bid']; 
											$row_list['date_sent'] = 'pending';	
											$row_list['Reserve_Price'] = $row_list['Reserve_Price'];
											$row_list['Buy_it_now'] = $row_list['Buy_it_now'];  
											$row_list['edit'] = '<span style="cursor:pointer;"  onClick="window.open(\'pendings.php?cmd=all_update&subcmd=onecoin&coin_id='.$row_list['coin_id'].'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Edit</span>';
										  $row_list['return_con'] = '<span class="blue"><a href="listings.php?cmd=list_return_user&consignid='.$row_list['consignid'].'&user_id='.$row_list['user_id'].'&coin_id='.$row_list['coin_id'].'">Return</a></span>';
												
									  echo '<tr class="alt1" valign="top">
									<td width="12%" align="left"><input type="checkbox" value="'.$row_list['coin_id'].'" name="incheckdate[]" id="coin_'.$row_list['coin_id'].'"></td>
									<td width="12%" align="left">'.$row_list['coin_id'].'</td>
									<td width="12%">'.$row_list['pcgs'].'</td>
									<td width="12%" >'.$row_list['Title'].'</td>
									<td width="12%">'.$row_list['Minimum_bid'].'</td>
									<td width="12%">'.$row_list['Reserve_Price'].'</td>
									<td width="12%">'.$row_list['Buy_it_now'].'</td>
									<td width="12%">'.$row_list['date_sent'].'</td>
									<td width="12%" class="blue">'.$row_list['edit'].'</td>
									
									<td width="12%" class="blue">'.$row_list['return_con'].'</td>
									</tr>';
												
												
												}
$sqlcat_siteid = $ilance->db->query("
						                                            SELECT *
						                                            FROM " . DB_PREFIX . "affiliate_listing
						                                            ");
										 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
										 {         
											$site_id_drop_down = '<select name="site_id" id="site_id" >
														<option value="0" selected="selected">List in GC</option>';
												while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
												{
												$site_id_drop_down.='<option value="'.$rescat_sid['id'].'">List in '.$rescat_sid['site_name'].'</option>';	
												}
											$site_id_drop_down.='</select>';
										 }
					
						      
												echo '<tr valign="top"><td>'.$site_id_drop_down.'</td><td width="12%" nowrap="nowrap">End Date</td><td width="12%">  <input type="text" value="" name="datevalnew"> Format(Y-m-d)</td><td width="12%" nowrap="nowrap" > <input type="submit" value="Save" name="datevalnew_save"></td></tr></table></form></div>
								
								<div class="block3-footer">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
						</div>
					</div>';
											
											}
											
											else
											{
												
											 echo '<div class="block-wrapper">
						<div class="block3">
						
								<div class="block3-top">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
								<div class="block3-header">'.$number.' List of pendings by '.fetch_user('username', $_GET['pending_list_user']).'</div>
								<div class="block3-content" style="padding:0px"><table width="100%" cellpadding="9" cellspacing="0">
													<tr class="alt2">
													<td width="12%" align="left">Coin ID</td>
													<td width="12%">User</td>
													<td width="12%">Status</td>
													<td width="12%">Buy Now Price</td>
													<td width="12%">Finalvalue</td>
													<td width="12%">Update Sale</td>
													</tr>
													<tr class="alt1" valign="top">
													<td align="center" nowrap="nowrap" colspan="11">No results found</td>
													</tr></table></div>
								
								<div class="block3-footer">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
						</div>
					</div>';
									
											}
	 }
if(isset($_GET['pending_list_user_new']))
	 {
	 
								   $con_listing_co = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins 
									WHERE user_id ='".$_GET['pending_list_user_new']."'
									AND coin_listed = 'c'
									AND (End_Date = '0000-00-00' OR pending = '1')	
									AND project_id  = '0'
									AND status = '0' order by coin_id
										 ");
									 $number = (int)$ilance->db->num_rows($con_listing_co);
									if($ilance->db->num_rows($con_listing_co) > 0)
									{
									$row_con_list = 0;
									 
									echo  '<div class="block-wrapper">
						                        <div class="block3">
						
								<div class="block3-top">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
								<div class="block3-header">
								'.$number.' List of pendings by <strong>'.fetch_user('username', $_GET['pending_list_user_new']).'</strong>  
								</div>
								<div class="block3-content" style="padding:0px"><table width="100%" cellpadding="9" cellspacing="0">
								<tr class="alt2" nowrap="nowrap">
											
									<td width="12%" nowrap="nowrap">Set end date for consignment <strong>'.fetch_user('username', $_GET['pending_list_user_new']).'</strong> <span class="blue" style="cursor:pointer;"  onClick="window.open(\'pendings.php?cmd=all_update&subcmd=allcoin&user_id='.$_GET['pending_list_user_new'].'&consignid='.$_GET['pending_list_con'].'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Click Here</span></td><td width="12%"></td><td width="12%"></td><td width="12%"></td><td width="12%"></td><td width="12%"></td><td width="12%"><span class="blue" style="cursor:pointer;" onclick="checkautobuildnew('.$_GET['pending_list_con'].','.$_GET['pending_list_user_new'].');"><b>Autobuild</b></span></td><td width="12%"></td>
											</tr></table>
											 <form method="post" action="pendings.php" accept-charset="UTF-8"  style="margin: 0px;">
				    <input type="hidden" name="subcmd" value="allcheck_update_date" />
											<table width="100%" cellpadding="9" cellspacing="0">
											<tr class="alt2">
											<td align="left">Check</td>
											<td align="left">Coin ID</td>
											<td align="left">Relist Count</td>
											<td  align="left">Imaged</td>
									        <td>PCGS</td>
									        <td width="20%">Coin</td>
									        <td>Min Bid</td>
									        <td>Reserve</td>
									        <td>Bin</td>
									        <td>Alt inv</td>
									        <td>End Date</td>
									        <td>Edit</td>								
									<td>Return</td>
											</tr>';
										while($row_list = $ilance->db->fetch_array($con_listing_co))
										{	
											// murugan change on nov 21
											$sel_attach = $ilance->db->query("
									SELECT * FROM " . DB_PREFIX . "attachment
									WHERE project_id ='".$row_list['coin_id']."'
										 ");
										 if($ilance->db->num_rows($sel_attach) > 0)
										 {
											$row_list['attachment'] = 'YES';									
										 }
										 else
										 {
											$row_list['attachment'] = 'NO';
										 }
										 
										// kumaravel change on august 14,2014
										
											$sel_relist_count = $ilance->db->query("
									SELECT *,COUNT(coin_id) as cnt FROM " . DB_PREFIX . "coin_relist
									WHERE coin_id ='".$row_list['coin_id']."'
										 ");
										
										 if($ilance->db->num_rows($sel_relist_count) > 0)
										 {
											while($row_relist_cnt = $ilance->db->fetch_array($sel_relist_count))
											{
												$row_list['relist_count'] = $row_relist_cnt['cnt'];
											}
										 
																				
										 }
										 else
										 {
											$row_list['relist_count'] = 0;
										 }										 
										
											$row_list['coin_id'] = $row_list['coin_id'];
											$row_list['relist_count'] = $row_list['relist_count'];
											$row_list['pcgs'] = $row_list['pcgs']; 
											$row_list['Description'] = $row_list['Description']; 
											$row_list['Minimum_bid'] = $row_list['Minimum_bid']; 
											$row_list['date_sent'] = 'pending';	
											$row_list['Reserve_Price'] = $row_list['Reserve_Price'];
											$row_list['Buy_it_now'] = $row_list['Buy_it_now']; 
                                            $row_list['Alternate_inventory_No'] = $row_list['Alternate_inventory_No'];											
											$row_list['edit'] = '<span style="cursor:pointer;"  onClick="window.open(\'pendings.php?cmd=all_update&subcmd=onecoin&coin_id='.$row_list['coin_id'].'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Edit</span>';
										    $row_list['return_con'] = '<span class="blue"><a href="listings.php?cmd=list_return_user&consignid='.$row_list['consignid'].'&user_id='.$row_list['user_id'].'&coin_id='.$row_list['coin_id'].'">Return</a></span>';
												
									  echo '<tr class="alt1" valign="top">
									<td align="left"><input type="checkbox" value="'.$row_list['coin_id'].'" name="incheckdate[]" id="coin_'.$row_list['coin_id'].'"></td>
									<td align="left">'.$row_list['coin_id'].'</td>
									<td align="left">'.$row_list['relist_count'].'</td>
									<td>'.$row_list['attachment'].'</td>
									<td>'.$row_list['pcgs'].'</td>
									<td>'.$row_list['Title'].'</td>
									<td>'.$row_list['Minimum_bid'].'</td>
									<td>'.$row_list['Reserve_Price'].'</td>
									<td>'.$row_list['Buy_it_now'].'</td>
									<td>'.$row_list['Alternate_inventory_No'].'</td>
									<td>'.$row_list['date_sent'].'</td>
									<td class="blue">'.$row_list['edit'].'</td>
									
									<td class="blue">'.$row_list['return_con'].'</td>
									</tr>';
												
												
												}
$sqlcat_siteid = $ilance->db->query("
						                                            SELECT *
						                                            FROM " . DB_PREFIX . "affiliate_listing
						                                            ");
										 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
										 {         
											$site_id_drop_down = '<select name="site_id" id="site_id" >
														<option value="0" selected="selected">List in GC</option>';
												while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
												{
												$site_id_drop_down.='<option value="'.$rescat_sid['id'].'">List in '.$rescat_sid['site_name'].'</option>';	
												}
											$site_id_drop_down.='</select>';
										 }
												echo '</table>
												<table><tr valign="top">
												<td width="12%" nowrap="nowrap">'.$site_id_drop_down.'End Date</td>
												<td width="12%"><input id="datepicker" type="text" value="" name="datevalnew"> Format(Y-m-d)</td>
												<td width="12%" nowrap="nowrap" ><input type="submit" value="Save" name="datevalnew_save"></td>
												</tr>
												</table>
												</form>
												</div>
								<script>

								$(function() {

									$("#datepicker").datepicker({

											dateFormat: \'yyyy-mm-dd\',

										});
								});

								</script>
								<div class="block3-footer">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
						</div>
					</div>';
											
											}
											
											else
											{
												
											 echo '<div class="block-wrapper">
						<div class="block3">
						
								<div class="block3-top">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
								<div class="block3-header">'.$number.' List of pendings by '.fetch_user('username', $_GET['pending_list_user_new']).'</div>
								<div class="block3-content" style="padding:0px"><table width="100%" cellpadding="9" cellspacing="0">
													<tr class="alt2">
													<td width="12%" align="left">Coin ID</td>
													<td width="12%">User</td>
													<td width="12%">Status</td>
													<td width="12%">Buy Now Price</td>
													<td width="12%">Finalvalue</td>
													<td width="12%">Update Sale</td>
													</tr>
													<tr class="alt1" valign="top">
													<td align="center" nowrap="nowrap" colspan="11">No results found</td>
													</tr></table></div>
								
								<div class="block3-footer">
										<div class="block3-right">
												<div class="block3-left"></div>
										</div>
								</div>
								
						</div>
					</div>';
									
											}
	 }
	 
	 if(isset($_GET['holding']))
	 {
	 $sqlcat_pcg = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "coins 
												
								");
								
								while($rescat_pcg = $ilance->db->fetch_array($sqlcat_pcg))
								{
								 $fg[] = $rescat_pcg['coin_id'];
									 // echo  $rescat_pcg['coin_id'];
									  
									 // echo 'one';
								}
								$fh = implode(',',$fg);
								 
									 echo '<input type="text" value="'.$fh.'" id="er" name="dd[]">'; 
										 $sqlcat_pcgd = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coins WHERE Site_id = '".abs($_GET['holding'])."'
														
										");
										while($rescat_pcgd = $ilance->db->fetch_array($sqlcat_pcgd))
										{
										 
										$t[] =  $rescat_pcgd['coin_id'];
										
										
										
										}
										$ee = implode(',',$t);
										echo '<input type="text" value="'.$ee.'" id="td" name="dd[]">'; 
								
								
								
								
	 }
	 
	 if(isset($_GET['check_pcgs']))
	 {
	  
		                        $pcg_exp = explode('.',$_GET['check_pcgs']);
								
								$lan = strlen($pcg_exp['1']);
								 
								// Murugan changes on Jan 26 for NGC if($lan > '2' AND $lan <= '3') FROM if($lan > '2')
                                if($lan > '2' AND $lan <= '3')
		                        {								
								$myrep = substr($pcg_exp['1'], -1);
								$pc_proof =rtrim($pcg_exp['1'], $myrep);
								
								$sqlcat_per = $ilance->db->query("
								SELECT grading
								FROM " . DB_PREFIX . "grading_service
								WHERE grading LIKE '".$myrep."%'                        
								");
								        if ($ilance->db->num_rows($sqlcat_per) > 0)
		                                {
										$rescat_per = $ilance->db->fetch_array($sqlcat_per);
										
										$mypert1 = ' '.$rescat_per['grading'];
										$mypert = $rescat_per['grading'];
										}
										else
										{
										$mypert = '';
										$mypert1 = ' ';
										}
								
								}
								// Murugan changes on Jan 26 for NGC
								elseif($lan > '3')
								{
								$myrep = substr($pcg_exp['1'], -2);
								$pc_proof =rtrim($pcg_exp['1'], $myrep);
								
								// murugan changes on dec 13
								if($myrep == 'ng')
								{
									$uc = 1;
								}
								$sqlcat_per = $ilance->db->query("
								SELECT grading
								FROM " . DB_PREFIX . "grading_service
								WHERE grading LIKE '".$myrep."%'                        
								");
								        if ($ilance->db->num_rows($sqlcat_per) > 0)
		                                {
										$rescat_per = $ilance->db->fetch_array($sqlcat_per);
										
										$mypert1 = ' '.$rescat_per['grading'];
									    $mypert = $rescat_per['grading'];
										}
										else
										{
										$mypert = '';
										$mypert1 = ' ';
										}
								}
								// Murugan changes on Jan 26 for NGC END here
							    else{
								$pc_proof = $pcg_exp['1'];
                                }
								 if(isset($pc_proof) and $pc_proof != '')
								 {
								 	    $sqlcat_proof = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coin_proof
										WHERE value = '".$pc_proof."'                        
										");
										if ($ilance->db->num_rows($sqlcat_proof) > 0)
		                                {
										$rescat_proof = $ilance->db->fetch_array($sqlcat_proof);
										$tex =  '<input type="hidden"  value="'.$pc_proof.'" id="gra_id">';
										$pro_tes =  fetch_cat('coin_detail_proof',$pcg_exp['0']);
										if($pro_tes == 'y')
										$text_pro = 'Proof-'.$pc_proof;
										else if($pro_tes == 's')
										$text_pro = 'Specimen-'.$pc_proof;
										else
										$text_pro = $rescat_proof['proof'].'-'.$pc_proof;
										$proof_val = $text_pro;
										$pro_pc = $rescat_proof['proof'].'-';
										}
										else
										{
                                        $pro_pc = '';
										$proof_val = '';
										}
								 }
								 else
								 {
                                 $pro_pc = '';
								 $proof_val = '';
								 }
								
								$sqlcat_pcg = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "catalog_coin
								WHERE PCGS = '".$pcg_exp['0']."'                        
								");
								$rescat_pcg = $ilance->db->fetch_array($sqlcat_pcg);
								$grad           =  $proof_val;
								
                             
								  
								$pro_tes =  fetch_cat('coin_detail_proof',$pcg_exp['0']);
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
								// murugan changes on dec 13
								if($uc >0 && $rescat_pcg['coin_detail_suffix'] == 'DCAM')
								$suff = 'UC';
								else
								$suff = $rescat_pcg['coin_detail_suffix'];
								
								//normal ajax title
								// murugan $rescat_pcg['coin_detail_suffix']
								if(empty($rescat_pcg['coin_detail_suffix']))
								 $title  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver.''.$mypert1.' '.$text_pro;
								else
								 $title  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver.''.$mypert1.' '.$text_pro.' '.$suff;
								
								//javascript title
								if(empty($rescat_pcg['coin_detail_suffix']))
								{
								$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
								$suff = '';
								}
								else
								{
								$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
							// murugan changes on dec 13
								if($uc > 0&& $rescat_pcg['coin_detail_suffix'] == 'DCAM')
								$suff = 'UC';
								else
								$suff = $rescat_pcg['coin_detail_suffix'];
								}
								//$title_eng      =  $rescat_pcg['coin_detail_year'].' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
								
								//catergory title
								if(empty($rescat_pcg['coin_detail_mintmark']))
								$title_engg      =  $rescat_pcg['coin_detail_year'].' '.$rescat_pcg['coin_detail_coin_series'];
								else
								$title_engg      =  $rescat_pcg['coin_detail_year'].'-'.$rescat_pcg['coin_detail_mintmark'].' '.$rescat_pcg['coin_detail_coin_series'];													
								$coin_series    =  $rescat_pcg['coin_detail_coin_series'];
								$des            =  $rescat_pcg['coin_detail_year'].' '.$rescat_pcg['coin_detail_coin_series'].' '.$grad.' '.$rescat_pcg['coin_detail_suffix'].' '.$rescat_pcg['coin_detail_major_variety'].' '.$rescat_pcg['coin_detail_die_variety'];
								
								
								echo '<input type="hidden" name="mypert" value="'.$mypert.'" id="mypert">
								
								      <input type="hidden" name="suff" value="'.$suff.'" id="suff">
								
								      <input type="hidden" name="cat" value="'.$pcg_exp['0'].'" id="cat">
								      <input type="hidden"  value="'.$title_engg.'" id="cat_tit_pro">
									  
									  <input type="hidden"  value="'.$title_eng.'" id="cat_tit_p">
								      <input type="hidden" value="'.$title.'" id="tit_pro">
								      <input type="hidden" value="'.$coin_series.'" id="coin_ser_val">
									  <input type="hidden" value="'.$des.'" id="des_cval">
									  <input type="hidden" value="'.$grad .'" id="grad_pro">
									   <input type="hidden"  value="" id="nbgrade">';
									  if(isset($pc_proof))
									  {
									  echo $tex;
									  }
								
					
	 }
	 //new chnage
	  if(isset($_GET['grade_prsent']))
	 {
	$pc_proof = $_GET['grade_prsent'];
	
	$pcg_exp = explode('.',$_GET['check_pcgs']);
	                                   $sqlcat_proof = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coin_proof
										WHERE value = '".$pc_proof."'                        
										");
										if ($ilance->db->num_rows($sqlcat_proof) > 0)
		                                {
										$rescat_proof = $ilance->db->fetch_array($sqlcat_proof);
										$tex =  '<input type="hidden"  value="'.$pc_proof.'" id="gra_id">';
										$pro_tes =  fetch_cat('coin_detail_proof',$pcg_exp['0']);
										if($pro_tes == 'y')
										$text_pro = 'Proof-'.$pc_proof;
										else if($pro_tes == 's')
										$text_pro = 'Specimen-'.$pc_proof;
										else
										$text_pro = $rescat_proof['proof'].'-'.$pc_proof;
										$proof_val = $text_pro;
										$pro_pc = $rescat_proof['proof'].'-';
										}
										else
										{
                                        $pro_pc = '';
										$proof_val = '';
										}
										
										
										
								$pro_tes =  fetch_cat('coin_detail_proof',$pcg_exp['0']);
								if($pro_tes == 'y')
								$text_pro = 'Proof-'.$pc_proof;
								else if($pro_tes == 's')
								$text_pro = 'Specimen-'.$pc_proof;
								else
								$text_pro = $pro_pc.''.$pc_proof;
								
								
								echo $text_pro;
	}									
	 if(isset($_GET['check_grade_service']))
	 {
	 if($_GET['check_grade_service'] == '0')
	 {
	 $Service_Level = '<div id="service_ajax"><select name="Service_Level" id="Service_Level" >
				  <option value="select" selected="selected">select</option>';
							 $sqlcat_serive = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "service_level
							");
							 if ($ilance->db->num_rows($sqlcat_serive) > 0)
							 {         
								
									while ($rescat_ser = $ilance->db->fetch_array($sqlcat_serive))
									{
													
										$Service_Level.='<option value="'.$rescat_ser['service_name'].'">'.$rescat_ser['service_name'].'</option>';
									}
									
							}
						
							$Service_Level.='</select></div>';
							
						echo  $Service_Level;
	 }
	 else
	 {
			 $Service_Level = '<div id="service_ajax"><select name="Service_Level" id="Service_Level" >
				  <option value="select">select</option>';
							 $sqlcat_serive = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "service_level WHERE service_id = '".$_GET['check_grade_service']."'
							");
							 if ($ilance->db->num_rows($sqlcat_serive) > 0)
							 {         
								
									while ($rescat_ser = $ilance->db->fetch_array($sqlcat_serive))
									{
													
										$Service_Level.='<option value="'.$rescat_ser['service_name'].'">'.$rescat_ser['service_name'].'</option>';
									}
									
							}
						
						$Service_Level.='</select></div>';
						
						echo  $Service_Level;
	 }
	 }
 
	   
}

function fetch_image_check($project_id, $type)
	{
		
		global $ilance;
		$sql2="SELECT filehash  FROM " . DB_PREFIX . "attachment WHERE  project_id='".$project_id."' and attachtype='".$type."'"; //itemphoto
		$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res2)>0)
		{
			while($line2=$ilance->db->fetch_array($res2))
			{
				 return true;
			}
		}else
		{
			return false;
		}
	}

function fetch_image_title($project_id)
{
	global $ilance;
		$sql2="SELECT `Title`  FROM " . DB_PREFIX . "coins WHERE  coin_id='".$project_id."'"; //itemphoto
		$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res2)>0)
		{
			while($line2=$ilance->db->fetch_array($res2))
			{
				 return $itemTitle       = $line2['Title'];
			}
		}
}

//vijay work for multiple image upload 

function post_images_to_ebay($coin_id)
{
global $ilance;
$con_listifng_gc = $ilance->db->query("
								SELECT  *
								FROM " . DB_PREFIX . "coins 
								WHERE coin_listed = 'c'
								AND coin_id  = '".$coin_id."'
								AND Site_Id = '1'
								");
								if($ilance->db->num_rows($con_listifng_gc) > 0)
								{
								
									while($row_lisst_gc = $ilance->db->fetch_array($con_listifng_gc))
									{
									
									
										$query1 = $ilance->db->query("SELECT attachid,project_id,filehash,filename,attachtype FROM " . DB_PREFIX . "attachment  WHERE project_id = '".$coin_id."'", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($query1) > 0)
										{    

										//$ebayimageflag =false;
										while ($res = $ilance->db->fetch_array($query1, DB_ASSOC))
										{
										$project_id= $res['project_id'];
										$filehash= $res['filehash'];
										$filename=$res['filename'];
										$DIR_AUCTION_ATTACHMENT= '/home/gc/public_html/uploads/attachments/auctions/';
										$file_path=$DIR_AUCTION_ATTACHMENT.floor($project_id/100).'00/'.  $project_id . '/' . $filehash . '.attach';

										if(file_exists($file_path))
										{
										$project_id= $res['project_id'];
										$filehash= $res['filehash'];
										$filename=$res['filename'];


										// $imageuplchk = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_images WHERE coin_id = '".$coin_id."' group by attach_id", 0, null, __FILE__, __LINE__);

										// if ($ilance->db->num_rows($imageuplchk) == 0)
										// {
										$full_url=post_image_ebay($file_path,$filename);

										$sql="INSERT INTO " . DB_PREFIX . "ebay_images (coin_id, attach_id, filename, ebay_url, ebay_id,attachtype,upload_date) VALUES ('".$project_id."', '".$res['attachid']."', '".$filename."', '".$full_url."','1','".$res['attachtype']."',NOW())";

										$ilance->db->query($sql);
										//	$ebayimageflag =true;
										//	}

										}
										else
										{
										echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Image Upload</a></li></ul></div></div>
										<table style="border:1px solid #000000;border-collapse:collapse; float: left;" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>file Not Found !</b></td></tr>
										<tr><td width="175px" style="background-color:#ffffff;border:1px solid #000000;color:#000000"><b>'.$file_path.'</b></td></tr>
										</tbody><table>';
										
										return false;
										}
										}
										// if($ebayimageflag == false)
										// {
										// //echo '<strong>Ebay Image is ALready Exists</strong></br><br>';
										// }

										return $inserted;
										}else
										{
										return false;
										}
									
									}
								}
echo '<div class="bigtabs" style="padding-bottom:5px; padding-top:12px"><div class="bigtabsheader"><a href="javascript:void(0)" onclick="close_ebay_pop();"<span class="smaller gray" style="padding-top:6px; float:right">Click away to close and cancel this menu</span></a><ul id="bidtabs"><li title="" class="highlight"><a href="javascript:void(0)">Ebay Image Upload</a></li></ul></div></div>
<table style="border:1px solid #000000;border-collapse:collapse;float: left;" cellpadding="10"><tbody><tr><td width="175px" style="background-color:#5b5f68;border:1px solid #000000;color:#ffffff"><b>Please select only Ebay coins To List ?</b></td></tr></tbody><table>';								
exit;							

//ilance_ebay_images
}

function post_image_ebay($file_path,$filename)
{		
	$devID = '0ad39d96-573f-47e7-9345-1cffd412549e';   // these prod keys are different from sandbox keys
    $appID = 'herakle26-0679-4e6f-9155-39794874fde';
    $certID = 'd32b96ca-b01e-407a-8575-7b0e44d76dc1';
    //the token representing the eBay user to assign the call with
    $userToken = 'AgAAAA**AQAAAA**aAAAAA**wNuAUw**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wJkoKjCpmKpA+dj6x9nY+seQ**1OwBAA**AAMAAA**4bmRLHRSN7fp+zQHHvTGptnBWwI1Stnwv2mqCFLMSKSwuoiTxr0KrpUPXIY5nPomb/5VcltYsN8jPQg5+CX9kiJNt0aRLXLpn0F2wZbI95FQtMBchNXNpHCp31OfzZJMNA1U3NNcyNwXJsHRdHPlWnUnUkaA+JlulxhWxe2sCrS8f0yJpLpE+FWFFFXUYKb3lkvoIGwbuuAH7gnlwrgAFHJoGmNjI/YFPkVFwbBNW5zp9ytza+tDs6gBWndel8C1aG3glY735k+obFfyXFV67ZMPyS/NjUPJ4Hr/ioTnE5liyD0g6473NBH7zNJKfcgzjcteQNlJa6Q5QUE0IhHqVw3KT7I94HptJjTOQEp7Z8A5w3u0mZJUCfk3yL4DwlTh/r65d+hnmYoJez/YH1nxpK3AOcNyRw4fr/o0Zze/eNHGAzjW6HA/wxw8lhO1pRm2jQXXH9tMerhwMwI9bI/ipscRNSyP5gxkgEYlFAxNi+6xYBSNKNMXAI9Re/KpLoB5XKvXm/hfCnwcIYNfhvDesYckq3fhbA7SO8lTuaOCT1QxwhZ6yQDXDYjHVpwKTQ1JwYAVSSZSh6JLULLvi7n6/CB91SyzeOEMV/kC9bQw/NgV+2dRTJ0jXtlj6GmsH+K81DMhRRX/7LuDglp7+LwB6df481oq1TI6ZKgnMnCnfwsF8iIQgrxZzlvCIj+TxchS7a/yjdtIi7Jhz7s+YIV1tUtJ/3EAp9FWEjQgcH2ylc166d4qTS2XyWNl/Lh/8dIY';                 

		
   
    $siteID  = 0;                            // siteID needed in request - US=0, UK=3, DE=77...
    $verb    = 'UploadSiteHostedPictures';   // the call being made:
    $version = 517;                          // eBay API version
    
	$file=$file_path;

    $picNameIn = $filename;
    $handle = fopen($file,'r');         // do a binary read of image
    $multiPartImageData = fread($handle,filesize($file));
    fclose($handle);

    ///Build the request XML request which is first part of multi-part POST
    $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $xmlReq .= '<' . $verb . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
    $xmlReq .= "<Version>$version</Version>\n";
    $xmlReq .= "<PictureName>$picNameIn</PictureName>\n";    
    $xmlReq .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>\n";
    $xmlReq .= '</' . $verb . 'Request>';

    $boundary = "MIME_boundary";
    $CRLF = "\r\n";
    
    // The complete POST consists of an XML request plus the binary image separated by boundaries
    $firstPart   = '';
    $firstPart  .= "--" . $boundary . $CRLF;
    $firstPart  .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
    $firstPart  .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
    $firstPart  .= $xmlReq;
    $firstPart  .= $CRLF;
    
    $secondPart = "--" . $boundary . $CRLF;
    $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
    $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
    $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
    $secondPart .= $multiPartImageData;
    $secondPart .= $CRLF;
    $secondPart .= "--" . $boundary . "--" . $CRLF;
    
    $fullPost = $firstPart . $secondPart;
    
    // Create a new eBay session (defined below) 
    $session = new eBaySession1($userToken, $devID, $appID, $certID, false, $version, $siteID, $verb, $boundary);

    $respXmlStr = $session->sendHttpRequest($fullPost);   // send multi-part request and get string XML response
    
    if(stristr($respXmlStr, 'HTTP 404') || $respXmlStr == '')
        die('<P>Error sending request');
        
    $respXmlObj = simplexml_load_string($respXmlStr);    
	$ack        = $respXmlObj->Ack;
    $picNameOut = $respXmlObj->SiteHostedPictureDetails->PictureName;
    $picURL     = $respXmlObj->SiteHostedPictureDetails->FullURL;
	$PictureSetMember=$respXmlObj->SiteHostedPictureDetails->PictureSetMember;
	/*
	//$hosted_image['PictureSetMember']=$PictureSetMember;
	print_r($respXmlObj);
	print "<P>Picture Upload Outcome : $ack </P>\n";
			print "<P>picNameOut = $picNameOut </P>\n";
			print "<P>picURL = $picURL</P>\n";
			print "<IMG SRC=\"$picURL\">";*/
			return $picURL;

	//return $hosted_image;

}
	
	class eBaySession1
{
	private $requestToken;
	private $devID;
	private $appID;
	private $certID;
	private $serverUrl;
	private $compatLevel;
	private $siteID;
	private $verb;
    private $boundary;

	public function __construct($userRequestToken, $developerID, $applicationID, $certificateID, $useTestServer,
								$compatabilityLevel, $siteToUseID, $callName, $boundary)
	{
	    $this->requestToken = $userRequestToken;
	    $this->devID = $developerID;
            $this->appID = $applicationID;
	    $this->certID = $certificateID;
	    $this->compatLevel = $compatabilityLevel;
	    $this->siteID = $siteToUseID;
	    $this->verb = $callName;
            $this->boundary = $boundary;
	    if(!$useTestServer)
		$this->serverUrl = 'https://api.ebay.com/ws/api.dll';
	    else
	        $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
	}
	
	/**	sendHttpRequest
		Sends a HTTP request to the server for this session
		Input:	$requestBody
		Output:	The HTTP Response as a String
	*/
	public function sendHttpRequest($requestBody)
	{        
        $headers = array (
            'Content-Type: multipart/form-data; boundary=' . $this->boundary,
            'Content-Length: ' . strlen($requestBody),
	    'X-EBAY-API-COMPATIBILITY-LEVEL: 517' . $this->compatLevel,  // API version
		
			
	    'X-EBAY-API-DEV-NAME: ' . $this->devID,     //set the keys
	    'X-EBAY-API-APP-NAME: ' . $this->appID,
	    'X-EBAY-API-CERT-NAME: ' . $this->certID,

            'X-EBAY-API-CALL-NAME: ' . $this->verb,		// call to make	
	    'X-EBAY-API-SITEID: ' . $this->siteID,      // US = 0, DE = 77...
        );
	//initialize a CURL session - need CURL library enabled
	$connection = curl_init();
	curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
        curl_setopt($connection, CURLOPT_TIMEOUT, 30 );
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($connection, CURLOPT_FAILONERROR, 0 );
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1 );
        //curl_setopt($connection, CURLOPT_HEADER, 1 );           // Uncomment these for debugging
        //curl_setopt($connection, CURLOPT_VERBOSE, true);        // Display communication with serve
        curl_setopt($connection, CURLOPT_USERAGENT, 'ebatns;xmlstyle;1.0' );
        curl_setopt($connection, CURLOPT_HTTP_VERSION, 1 );       // HTTP version must be 1.0
	$response = curl_exec($connection);
        
        if ( !$response ) {
            print "curl error " . curl_errno($connection ) . "\n";
        }
	curl_close($connection);
	return $response;
    } // function sendHttpRequest
}  // class eBaySession
 
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>