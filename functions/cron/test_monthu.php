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

// if (!isset($GLOBALS['ilance']->db))
// {
        // die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
// }


require_once('../config.php');
echo CURRENTAUCTION;//exit;


$project_list = $ilance->db->query("select * from " . DB_PREFIX . "projects where date( date_end ) = '".CURRENTAUCTION."' AND status = 'open' group by user_id ");

if($ilance->db->num_rows($project_list)>0)
{
                                    $messagebody .= "*********************************"."<br/>";
									$messagebody .= "Auction Details"."<br/>";
									$messagebody .= "*********************************"."<br/>";	

      while($totallist=$ilance->db->fetch_array($project_list))
        {
		
		   $userid=$totallist['user_id'];
		     $username= fetch_user('username',$totallist['user_id']); 
			   $email=fetch_user('email',$totallist['user_id']);
			   
			
			  
			  $user_list = $ilance->db->query("select count(*) as list from " . DB_PREFIX . "projects where user_id = '".$totallist['user_id']."' AND date( date_end ) = '".CURRENTAUCTION."' AND status = 'open'");
			     
		                $xlist = $ilance->db->fetch_array($user_list);    
					$total = $xlist['list'];
					 
					 $total_1=$total_1+$total;
					  
							  $bids_zero = $ilance->db->query("select sum(currentprice) as crrprice from " . DB_PREFIX . "projects where user_id = '".$totallist['user_id']."' AND date( date_end ) = '".CURRENTAUCTION."' AND status = 'open' AND bids = 0 ");
							
							$ylist = $ilance->db->fetch_array($bids_zero); 
		             $zerobids = $ylist['crrprice'];
					 $total_2=$total_2+$zerobids;					
		
		        			  $bids_nunzero = $ilance->db->query("select sum(currentprice) as bidcrrpric from " . DB_PREFIX . "projects where user_id = '".$totallist['user_id']."' AND date( date_end ) = '".CURRENTAUCTION."' AND status = 'open' AND bids !=0 ");
							  
							  $zlist = $ilance->db->fetch_array($bids_nunzero);
							  $nonzerobids=$zlist['bidcrrpric'];
							  $total_3=$total_3+$nonzerobids;  
							  
							  $ilance->email = construct_dm_object('email', $ilance);
							  /*$existing = array(
									'{{username}}' => fetch_user('username',$totallist['user_id']),
									
									'{{xitems}}' => $xlist['kkk'],
									'{{yitems}}' => $ylist['crrprice'],
									'{{zitems}}' => $zlist['bidcrrpric'],
								);*/
								
								
								   			
								    $messagebody .= "Username :". $username . "<br/>";
									$messagebody .= "Email :". $email . "<br/>";
									$messagebody .= "Total No of Item Count :" . $total . "<br/>";
									$messagebody .= "Total No of Unsold Items :" . ": " . $zerobids . "<br/>";
									$messagebody .= "Total No of Sold Items :".$nonzerobids."<br/>";
									$messagebody .= "*********************************"."<br/>";
									
									/*$messagebody .=$maxamt."\n";
									$messagebody .= $phrase['_time_left'] . ": " . $ilance->auction->auction_timeleft($res_new['project_id'], '', '', 0, 0, 1) . "\n\n";*/
								 
						
							    
			  
		}
		
		                            $messagebody .= "Total Details"."<br/>";
									$messagebody .= "Total Item Count :". $total_1 . "<br/>";
									$messagebody .= "Total Unsold Price :". $total_2 . "<br/>";
									$messagebody .= "Total Sold Price :". $total_3 . "<br/>";
									
									
									echo $messagebody;exit;
		
		                    // $ilance->email = construct_dm_object('email', $ilance);
			                // $ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
							 // //$ilance->email->mail = 'info@greatcollections.com';
							// $ilance->email->slng = fetch_user_slng($totallist['user_id']);	
							// $ilance->email->get('reports_allusers');	
						// /*	$ilance->email->set(array(
                                        // '{{username}}' => $username,
                                        // '{{itemcount}}' => $total,
                                        // '{{zerobids}}' => $zerobids,
                                        // '{{nonzerobids}}' => $nonzerobids
                                        
                                // ));*/
								
										// $ilance->email->set(array(
                                                // '{{message}}' => $messagebody,
                                               
                                        // ));
								
							//$ilance->email->send();
							
							unset($messagebody);
 }
 