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
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
        'modal',
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'merch');


// #### require backend ########################################################
require_once('../config.php');


//$file = HTTP_SERVER.'kkk/template.html';

//$file = HTTP_SERVER.'newngc/sekar.html';
 


 /*   header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);*/
    //exit;



//$datetoday=DATETODAY;

 $datetoday = date("Y-m-d");



//echo $dates = date("d-m-y",$datetoday);


$date = explode('-', $datetoday);
 
$d = $date[2];
$m = $date[1];
$y = $date[0];

 $diff = ($d)-7;


 $hour = mktime(0,0,0,$m,$diff,$y);


$weeks = date("Y-m-d", $hour);




//(date(b.orderdate) <= '".DATETODAY."' AND date(p.date_end) >= '".SEVENDAYSAGO."')

echo "     SELECT cid,grading_service,filtered_auctiontype,date_end
FROM " . DB_PREFIX . "projects 
WHERE 
winner_user_id=0
and status ='open'
AND filtered_auctiontype='regular'
AND grading_service =  'NGC'
UNION ALL
 SELECT cid,grading_service,filtered_auctiontype,date_end
FROM " . DB_PREFIX . "projects 
WHERE 
buynow_qty !=0
and status ='open'
AND filtered_auctiontype='fixed'
AND grading_service =  'NGC'
";
 
 

$sql = $ilance->db->query("
                       SELECT cid,grading_service,filtered_auctiontype,date_end
FROM " . DB_PREFIX . "projects 
WHERE 
winner_user_id=0
and status ='open'
AND filtered_auctiontype='regular'
AND grading_service =  'NGC'
UNION ALL
 SELECT cid,grading_service,filtered_auctiontype,date_end
FROM " . DB_PREFIX . "projects 
WHERE 
buynow_qty !=0
and status ='open'
AND filtered_auctiontype='fixed'
AND grading_service =  'NGC'");					
						
		while($fetch_cid = $ilance->db->fetch_array($sql))
		  {
		  
		  $auction_end_date =$fetch_cid ['date_end'];
		  
		  $project_details = $ilance->db->query("
                        SELECT project_id, date(date_end),buyer_fee
						   FROM " . DB_PREFIX . "projects
                        WHERE cid = '".$fetch_cid ['cid']."'");

						
						
					        $fetch_projects = $ilance->db->fetch_array($project_details);
							
					//ngc coin from database 		
					$ngc_det = $ilance->db->query("
                        SELECT PCGS,coin_detail_ngc_no		
						   FROM " . DB_PREFIX . "catalog_coin
                        WHERE PCGS = '".$fetch_cid ['cid']."'  ");
						
						
						
							$fetch_net = $ilance->db->fetch_array($ngc_det);	
					
								$projectid = $fetch_projects['project_id'];
								
								$auction_end = $fetch_projects['date(date_end)'];
						
									$date_calc = explode('-', $auction_end);
									 $ye=$date_calc['0'];
									 $mo=$date_calc['1'];
									 $da=$date_calc['2'];
									 
									 $date_fix = mktime(0,0,0,$mo,$da,$ye); 
									
									 $date_set = date("F j,Y", $date_fix);
						 
						         $auction_name = 'GreatCollections '.$date_set.'Auctions';
					    // $datesale=$date_set
					  
					  if($fetch_projects['buyer_fee'] == 0.00)
					  {
					    $buyer_fee = '-';
					  }
					  else
					  {
					     $buyer_fee = $fetch_projects['buyer_fee'];
					  }
					  
					  //$rand = substr(md5(uniqid(rand(), true)),0,5);
					   $rand = 'ngcex';
					  $loturl = 'greatcollections.com/merch.php?id='.$fetch_projects['project_id'].'&referal_id='.$rand;
					  
						
						 $salenumber = $date_set;
		  
		       $details_coins = $ilance->db->query("
                        SELECT Grading_Service,Grade,Certification_No,Star,Plus	
						   FROM " . DB_PREFIX . "coins WHERE pcgs = '".$fetch_cid ['cid']."' AND grading_service =  'NGC'");
						 
						   $fetch_coins = $ilance->db->fetch_array($details_coins);
						   
									 $gradingservice = $fetch_coins['Grading_Service'];
								   
								   
									$grade = $fetch_coins['Grade'];
								   
								   
									$certificationno = $fetch_coins['Certification_No'];
									
									 $star = $fetch_coins['Star'];
									
									$plus = $fetch_coins['Plus'];
						   
						   
						   
						    $attchment = $ilance->db->query("
                        SELECT filehash
						   FROM " . DB_PREFIX . "attachment
                        WHERE category_id = '".$fetch_cid ['cid']."'");
						
						   $attchment_details = $ilance->db->fetch_array($attchment);
						
						      $img_url='greatcollections.com/attachment.php?id='.$attchment_details['filehash'];
							  
							  $textfile['coin_detail_ngc_no'] = $fetch_net['coin_detail_ngc_no'];
							  $textfile['PCGS'] = $fetch_net['PCGS'];
							  $textfile['gradingservice'] = $gradingservice;
							  $textfile['certificationno'] = $certificationno;
							  $textfile['auction_end'] = $auction_end_date;
							  $textfile['auction_name'] = $auction_name;
							  $textfile['date_set'] = $date_set;
							  $textfile['projectid'] = $projectid;
							  $textfile['loturl'] = $loturl;
							  $textfile['buyer_fee'] = $buyer_fee;
							  $textfile['grade'] = $grade;
							  $textfile['plus'] = $plus;
							  $textfile['star'] = $star;
							  $textfile['img_url'] = $img_url;
							  
							 $totalvalues[] = $textfile; 

		}




        $i=0;
  foreach($totalvalues as $values)
    {
		foreach($values as $getval)
		{
		 $val .= $getval.",";
		 $i++;
		}
		$val .= "\n";
	}	
	



                 $ngc_pdf1 ='NGC Coin Number,PCGS Coin Number,Service,Certification Number,Auction Date,AuctionName,SaleNumber,LotNumber,LotURL,PricesRealized,Grade,Plus,Star,URL'."\n";
				 
				  $File = 'newngc/date_unsold(4).txt'; 
                  $Handle = fopen($File, 'w');
                  $Data = $ngc_pdf1; 
                  fwrite($Handle, $Data);  
				  $Data = $val; 
                  fwrite($Handle, $Data); 
                  fclose($Handle); 
					