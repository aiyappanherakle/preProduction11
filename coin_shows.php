<?php

 ######################################################################## ||
 # ILance Marketplace Software 3.2.0 Build 1352
 # -------------------------------------------------------------------- # ||
 # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
 # -------------------------------------------------------------------- # ||
# Copyright 20002010 ILance Inc. All Rights Reserved.                # ||
 # This file may not be redistributed in whole or significant part.     # ||
 # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
# http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
 # -------------------------------------------------------------------- # ||
 ######################################################################## ||

 

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'autocomplete',
);

// #### setup script location ##################################################
define('LOCATION', 'main');

// #### require backend ########################################################
require_once('./functions/config.php');
// #### setup default breadcrumb ##################
 $area_title = "Shows";
                $page_title = SITE_NAME . ' - ' . $phrase['_coin_shows_page_title'];
                
                // construct breadcrumb trail
  $navcrumb = array();
  $navcrumb[""] = $phrase['_coin_shows_breadcrumb'];
				
	// For Bug #4813
	$staff_lists = array(1=>array('name'=>'Ian Russell', 'desaignation'=>'Owner/President', 'email'=>'ian@greatcollections.com
','img'=>'/images/ian_russell.jpg'),2=>array('name'=>'Nina Ann Phan', 'desaignation'=>'Consignment Director', 'email'=>'nina@greatcollections.com','img'=>'/images/Nina.jpg'),3=>array('name'=>'Raeleen Endo', 'desaignation'=>'Owner/Client Services', 'email'=>'raeleen@greatcollections.com','img'=>'/images/raeleen_endo.jpg'), 4=>array('name'=>'Juliann Gim', 'desaignation'=>'Client Services', 'email'=>'juliann@greatcollections.com','img'=>'/images/deflt_user.jpg'));			
				
			$sql=$ilance->db->query("SELECT coin_date,coin_end_date,id,coin_desc,DATE_FORMAT(coin_date,'%M') as curmnth,DATE_FORMAT(coin_end_date,'%M') as endmonth,year(coin_date) as yer,coin_show_representatives FROM " . DB_PREFIX . "coin_show                                     WHERE coin_end_date >= '".DATETODAY."' GROUP BY coin_date order by coin_date ASC 
			                      ");
			
			$coin_shows='';	
			$count=0;
			while($res = $ilance->db->fetch_array($sql))
			{
				$date=$res['coin_date'];
				$end_date=$res['coin_end_date'];
				$month=$res['curmnth'];
				$end_month = $res['endmonth'];
				$year=$res['yer'];
				$desc=$res['coin_desc'];
				
				 $str_dt=explode("-",$date);
				 $end_dt=explode("-",$end_date);
				 
				 $end_month = ($month!=$end_month)?'<b>'.$end_month.'</b> ':'';
				
				 $staff_detls = '';
				 if($res['coin_show_representatives'] != '')
				 {
					$representatives = json_decode($res['coin_show_representatives']);
					foreach($representatives as $representativ)
					{
						$staff_detls .= '<div class="info">
											<img width="48" height="63" alt="" src="'.$staff_lists[$representativ]['img'].'">
											<div class="desc">
												<h5>'.$staff_lists[$representativ]['name'].'</h5>
												<span>'.$staff_lists[$representativ]['desaignation'].'</span>
												<img width="20" height="15" alt="" src="/images/email-icon.png">
												<a target="_top" class="sprite" href="mailto:'.$staff_lists[$representativ]['email'].'">Mail</a>
											</div>
										</div>';
					}
					
				 }
				
				$coin_shows.='<tr class="evlang-en">
							<td class="evDate"><b>
							'.$month.'</b> '.$str_dt[2].' - '.$end_month.''.$end_dt[2].', '.$year.'
							</td>
							<td class="evDetails">'.$desc.'</td>
							<td class="evPeople">
								'.$staff_detls.'	
							</td></tr>';
				// if($last_month == $month && $last_year == $year)
				 
				 // {
				 // $coin_shows.=$month.'&nbsp;'.$str_dt[2].'-'.$end_month.'&nbsp;'.$end_dt[2].'&nbsp-&nbsp;'.$desc.'<br /><br />';
				 // }  
					 
				// else
				// {
			
				// $coin_shows.='<h2>'.$month.'&nbsp;'.$year.'</h2><br /><br />'.$month.'&nbsp;'.$str_dt[2].'-'.$end_month.'&nbsp;'.$end_dt[2].'&nbsp-&nbsp;'.$desc.'<br /><br />';
				
				// }
				$lyear = $year;
				$last_month = $month;
				$last_year = $year;
				$count++;
			}	
			
			if(!$count)
			{
			$coin_shows.="Currently no Shows Available<br><br>";
			}
			
          $pprint_array = array('coin_shows','login_include');

        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
	
	    $ilance->template->fetch('main', 'main_shows.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	    ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
	
?>