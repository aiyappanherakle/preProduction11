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
        'rfp',
        'buying',
        'selling',
        'search',
        'feedback',
        'portfolio',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'main'
);

// #### setup script location ##################################################
define('LOCATION','dailydeal');

// #### require backend ######################################################## 
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[dailydeal]" => $ilcrumbs["$ilpage[dailydeal]"]);

$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');

   $no='<center>no result found</center>';


$dailydeal=$ilance->db->query("SELECT p.currentprice,p.project_title FROM " . DB_PREFIX . "projects p, " . DB_PREFIX . "dailydeal  d  WHERE p.status='open' AND p.project_id = d.project_id AND d.live_date = p.date_starts");

	if($ilance->db->num_rows($dailydeal)>0)
	{	           

$count=0;
$detail='';
$detail.='<table border="0" width="197">
<tr>';
 while($row=$ilance->db->fetch_array($dailydeal))
 {
 $current= $row['currentprice'];
	   $title= $row['project_title'];
 if($count%4==0)
 {
 $detail.='</tr><tr>';
 } $sql_attya = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$row_pre_fea['project_id']."'
						AND attachtype='itemphoto'
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
				
					if($ilance->db->num_rows($sql_attya) == 1)
					{
						$uselistra = HTTPS_SERVER . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=140'; 
						$htma ='<img src="'.$uselistra.'" style="padding-top: 6px;">';
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						
					    $htma ='<img src="'.HTTP_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;">';
					}

$detail.='
<td>
		   <table border="0" width="197"  cellspacing="10px;">
	<tr>
	    <td><span class="title">'.$title.'</span></td>
	</tr>	
	<tr>
	    <td class="tdf">'.$htma.'</td>
	</tr>	
	<tr>
	<td>
<table width="200" border="0">
  <tr>
    <td><span class="dollar">$</span><span class="current">'.$current.'</span></td>
    <td rowspan="2"><a href="#"><img src="images/gc/bid_now_butt.jpg" style="padding-left:22px;" /></a></td>
  </tr>
</table>
</tr>
</td>
	</table></td>';
$count++;
}
}

//show previous coins
else

{
$detail.=
$dailydeal=$ilance->db->query("SELECT p.currentprice,p.project_title FROM " . DB_PREFIX . "projects p, " . DB_PREFIX . "dailydeal d  WHERE p.status='open' AND p.project_id = d.project_id AND d.live_date != p.date_starts ORDER BY d.live_date DESC limit 6");

		           

$count=0;
$detail='';
$detail.='<table border="0" width="197">
<tr>';
 while($row=$ilance->db->fetch_array($dailydeal))
 {
 $current= $row['currentprice'];
	   $title= $row['project_title'];
 if($count%4==0)
 {
 $detail.='</tr><tr>';
 } $sql_attya = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$row_pre_fea['project_id']."'
						AND attachtype='itemphoto'
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
				
					if($ilance->db->num_rows($sql_attya) == 1)
					{
						$uselistra = HTTPS_SERVER . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=140'; 
						$htma ='<img src="'.$uselistra.'" style="padding-top: 6px;">';
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						
					    $htma ='<img src="'.HTTP_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;">';
					}

$detail.='
<td>
		   <table border="0" width="197"  cellspacing="10px;">
	<tr>
	    <td><span class="title">'.$title.'</span></td>
	</tr>	
	<tr>
	    <td class="tdf">'.$htma.'</td>
	</tr>	
	<tr>
	<td>
<table width="200" border="0">
  <tr>
    <td><span class="dollar">$</span><span class="current">'.$current.'</span></td>
    <td rowspan="2"><a href="#"><img src="images/gc/sold_butt.jpg" style="padding-left:22px;" /></a></td>
  </tr>
</table>
</tr>
</td>
	</table></td>';
$count++;
}

}

$detail.=
'</tr>
</table>';







           $pprint_array = array('detail','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','current','title');

        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
    
        $ilance->template->fetch('main', 'dailydeal.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val');
		$ilance->template->parse_loop('main', 'info_feat');
        $ilance->template->parse_loop('main', 'featuredserviceauctions');
        $ilance->template->parse_loop('main', 'featuredproductauctions');
        $ilance->template->parse_loop('main', 'latestserviceauctions');
        $ilance->template->parse_loop('main', 'latestproductauctions');
        $ilance->template->parse_loop('main', 'productsendingsoon');
        $ilance->template->parse_loop('main', 'servicesendingsoon');
        $ilance->template->parse_loop('main', 'recentreviewedproductauctions');
        $ilance->template->parse_loop('main', 'recentreviewedserviceauctions');
        
        ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
        
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
?>