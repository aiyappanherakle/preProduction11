<?php
require_once('./../functions/config.php');
require_once('../fpdf.php');


$id = $ilance->GPC['id'];


if (isset($ilance->GPC['id']))
{ 
	 $sql_ship = $ilance->db->query("
                               SELECT *
                        	   FROM " . DB_PREFIX . "shippnig_details where 
                              ship_id = '".$ilance->GPC['id']."'
							   
                        ");
						
					
		
		                             while ($result = $ilance->db->fetch_array($sql_ship, DB_ASSOC))
                                         {
										 /*
												 $firstname = fetch_user('first_name',$result['buyer_id']);
												 $lastname = fetch_user('last_name',$result['buyer_id']);
										        
												 $firstname = fetch_user('first_name',$result['cust_id']);
								                 $lastname = fetch_user('last_name',$result['cust_id']);*/
												 $result['seller_id'] = fetch_user('username', $result['cust_id']);
												 $result['shipper_id'] = fetch_shipper('title',$result['shipper_id']);
												 $result['buyer_id'] = fetch_user('username', $result['buyer_id']); 
												 
												  $sellerid =  $result['seller_id'];
												  $service=  $result['shipper_id'];
												  $recivername = $result['buyer_id'];
												  $shipid = $result['ship_id'];
												  $trackno = $result['track_no']; 
												  $shipmentdate = $result['shipment_date'];
												  $email = $result['email'];       
					                            
												$listship['shipid'] = $shipid;
												$listship['recivername'] = $recivername;  
										        $listship['shipmentdate'] = $shipmentdate;
												$listship['sellerid'] = $sellerid;
												$listship['trackno'] = $trackno;
												$listship['service'] = $service;
												$listship['email'] = $email;
												
												
												$shiplist[] = $listship;
										 }
		 }
		 









class PDF extends FPDF
{
//Page header
function Header()
{
     	global $mylink,$result,$shiplist,$listship;
	//Logo 
	$this->Image(DIR_SERVER_ROOT.'greatcollections_logo.jpg',10,8,70);
	$this->SetFont('Arial','B',15); 
	//Move to the right 
	$this->Cell(120);
	$this->Ln(15);
	$this->SetFont('Arial','',15,'B');
	
	$this->SetTextColor(220,120,189);
	$this->Write(10, "GreatCollections - Shipping Listing",'Arial', '11');
	$this->Ln(15);
	
		$title_field = array('ID','Reciver Name','Date of Shipment','Seller Name','Tracking No','Service','Email');
	$col = array();
	for($r=0;$r<count($title_field);$r++)
	{
		   
		   
		   $col[] = array('text' => $title_field[$r], 'width' => '30', 'height' => '7', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '6', 'font_style' => 'B', 'fillcolor' => '62 ,70 ,72 ', 'textcolor' => '236,237,241', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
	
    } 
		   $you[] =$col;
			
		$this->WriteTable($you);
		$this->Ln(5);
	
}

//Page footer
function Footer()
{
    //Position at 1.5 cm from bottom
    $this->SetY(-15);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}









//Instanciation of inherited class
$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);


									
									


    // create table
		$columns = array();
		
			//mutiple value

         $columns[] = $row_content_arr;
         $r=0;
		 
		  foreach($shiplist as $each_list)
		 { 
		     foreach($each_list as $list_columns)
			 {
			 
			   if($list_columns == '')
			   $clval = '-';
			   else
			   $clval = $list_columns;
			   
			
			   
			   		   $row_content_arr[] = array('text' => $clval, 'width' => '30', 'height' => '5', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '7', 'font_style' => 'B', 'fillcolor' => '255 ,255 ,255 ', 'textcolor' => '76 ,84 ,86 ', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
			 }
			 
		 $columns[]=$row_content_arr;
		 $r++;
		 
		 unset($row_content_arr);
		 
		 }
		 
		 
	    $pdf->WriteTable($columns); 

$pdf->Output();
?>