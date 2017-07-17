<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright 20002010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/


// #### setup script location ##################################################
define('LOCATION', 'dailydeal');

// #### require backend ########################################################
require_once('./functions/config.php');


                
                // construct breadcrumb trail
                $navcrumb = array();
                
				$navcrumb[""] = ' Reviews about GreatCollections Coin Auctions
				';



if($_SESSION['ilancedata']['user']['userid']>0)
{

$details = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users where user_id = '".$_SESSION['ilancedata']['user']['userid']."' ");

 $res_test=$ilance->db->fetch_array($details);	
 						
 $username=$res_test['username'];
 $firstname = $res_test['first_name'];
 $lastname = $res_test['last_name'];
 $city =  $res_test['city'];
 $state =  $res_test['state'];
 $email =  $res_test['email'];


  $html = '<tr>
              <td>Firstname</td>
	          <td><input type="text" name="firstname" id="firstname" value="'.$firstname .'"/></td><td id="error_firstnam" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Lastname</td>
	          <td><input type="text" name="lastname"  id="lastname" value="'.$lastname .'"/></td><td id="error_lastname" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Email</td>
	          <td><input type="text" name="email"  id="email" value="'.$email .'"/></td><td id="error_email" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Client Location</td>
	          <td><input type="text" name="location" id="location" value="'.$city.' '.$state.'"/></td><td id="error_location" style="color:#FF0000"></td>
          </tr>
		   <tr>
              <td>Security Image</td>
	          <td><img src = "'.HTTPS_SERVER.'attachment.php?do=captcha"/></td>
          </tr>
		    <tr>
			<td></td>
              <td>If you unable to see this image, please refresh the page</td>
	          
          </tr>
		   <tr>
              <td>Enter the Code Shown on the Image</td>
	          <td><input type="text" name="captcha" id="captcha" value=""/></td><td id="error_captcha" style="color:#FF0000"></td>
          </tr>
		  ';
}
else
{

$html = '<tr>
              <td>Firstname</td>
	          <td><input type="text" name="firstname" id="firstname" value=""/></td><td id="error_firstnam" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Lastname</td>
	          <td><input type="text" name="lastname" id="lastname" value=""/></td><td id="error_lastname" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Email</td>
	          <td><input type="text" name="email" id="email" value=""/></td><td id="error_email" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Client Location</td>
	          <td><input type="text" name="location" id="location" value=""/></td><td id="error_location" style="color:#FF0000"></td>
          </tr>
		  <tr>
              <td>Security Image</td>
	          <td><img src = "'.HTTPS_SERVER.'attachment.php?do=captcha"/></td>
          </tr>
		     <tr>
			 <td style="padding-top: 0px;" ></td>
              <td style="padding-top: 0px;">If you unable to see this image, please refresh the page</td>
	          
          </tr>
		   <tr>
              <td>Enter the Code Shown on the Image</td>
	          <td><input type="text" name="captcha" id="captcha" value=""/></td><td id="error_captcha" style="color:#FF0000"></td>
          </tr>
		  ';

}

 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'testimonials')
{

   if (!empty($ilance->GPC['title']) AND !empty($ilance->GPC['description']) AND !empty($ilance->GPC['firstname']) AND !empty($ilance->GPC['lastname']) AND !empty($ilance->GPC['email']) AND !empty($ilance->GPC['location']))
        {                
        
  $sql = $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "testimonial
                (title, description, firstname,lastname	,email, location,date_added)
                VALUES
                (
                '" . $ilance->db->escape_string($ilance->GPC['title']). "',
                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['firstname']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['lastname']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['email']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['location']) . "',
				'".DATETODAY."'
				)
        ", 0, null, __FILE__, __LINE__);
		 	  print_notice('Success','Thank you for your Testimonial.','testimonials.php','Back');
		  exit(); 
  }
}

$ilance->GPC['page']=isset($ilance->GPC['page'])?$ilance->GPC['page']:1;
$counter = ($ilance->GPC['page'] - 1) * 20;
$scriptpageprevnext = 'testimonials.php?';
if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	 {
	$ilance->GPC['page'] = 1;
	 }
	 else
		 {
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		 }

$testimoniallist1 = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "testimonial where status = 'accept' order by id desc 
										");
			$number = (int)$ilance->db->num_rows( $testimoniallist1);							

	$testimoniallist = $ilance->db->query("
											SELECT title,description,firstname,lastname,location,DATE_FORMAT(date_added,'%M %d, %Y') as date_added
											FROM " . DB_PREFIX . "testimonial where status = 'accept' order by id desc LIMIT " . (($ilance->GPC['page'] - 1) * 20) . "," . '20'."");
											
										
			$raw_testimonial = '<div align="center" style="font-family:futura;font-size:40px;font-weight:bold;color:#009900;">Reviews and Testimonials ('.$number.' Reviews)</div>';	
			
			// $count_gal=1;  
			 // $raw_testimonial.= '<table width="930px" cellspacing="5" cellpadding="16" border="0" align=""><tr>';
		while($res_testimonial=$ilance->db->fetch_array($testimoniallist))
		 {
		 
		  $raw_testimonial.= '<div><table><tr><td><div style="width: 930px;"><div style="margin-left:50px; width: 900px; font-family:futura; font-weight:bold;color:#666666;font-size:20px;">'.ucfirst($res_testimonial['title']).'</div>
		  <div><img src = "'.HTTPS_SERVER.'images/gc/icons/quote_left.jpg"  width="34" height="30"></div>
		  <div style="padding-left: 40px; border-top-width: 0px; margin-top: -20px; width: 850px; font-family:futura; color:#666666;font-size:14px;">'.$res_testimonial['description'].'
		  <span><img src = "images/gc/icons/quote_right.jpg" width="34" height="30" style="margin-top:2px;margin-left:5px;position:absolute;"></span>
		  </div>
		  <div>
		   <div  style="margin-left: 650px;margin-top: 12px; font-weight:bold;font-size:17px;"><i>'.$res_testimonial['firstname'] .' '.$res_testimonial['lastname'] . '&nbsp;<span style="font-size:17px;"> ('.$res_testimonial['date_added'].')</span></i></div>
		  <div style="margin-left: 650px; margin-top: 3px; font-weight:bold;font-size:17px;"><i>'.$res_testimonial['location'] .'</i></div></div></div></td></tr></table></div></tr><tr>'; 
		  
		
		 }
		// $raw_testimonial.= '</tr ></table>';
		 $prof = print_pagnation($number, 20, $ilance->GPC['page'], $counter, $scriptpageprevnext);
		 
		 $area_title = 'Reviews and Testimonials ("'.$number.'" Reviews)';
				
         $page_title = SITE_NAME . ' - Testimonials Form by GreatCollections Coin Auctions';

		$pprint_array = array('prof','raw_testimonial','html','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'testimonials.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_sell','res_gc_daily_deal','res_gcdealing','res_gcsolding','res_gcdeal'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();