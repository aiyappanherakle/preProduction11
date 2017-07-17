<?php

require_once('./../functions/config.php');
require_once('../fpdf.php'); 



						
   
			$mylink = HTTP_SERVER;
			

class PDF extends FPDF
{

//Page header
function Header()
{
   	global $mylink, $pdfshipping,$shipping_list,$no;
	//Logo 
	$this->Image(DIR_SERVER_ROOT.'greatcollections_logo.jpg',10,8,70);
	//$this->Image("..".$ilance['template_relativeimagepath'].$ilconfig['template_logo'],10,8,33);

	$this->SetFont('Arial','B',15); 
	//Move to the right 
	$this->Cell(120);
	$this->Ln(15);
	$this->SetFont('Arial','',15,'B');
	
	$this->SetTextColor(220,120,189);
	$this->Write(10, "GreatCollections - Shipping Report",'Arial', '11');
	$this->Ln(15);
	
		$title_field = array('SHIPPING ID','ITEM ID','CUSTOMER ID','SHIPPER ID','SHIPPING DATE');
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





$pdf->SetFont('Times','',5);



$checkdate = $ilance->GPC['start'];

$shippingpdflist= $ilance->db->query("SELECT ship_id,item_id,cust_id,shipper_id,shipment_date FROM " . DB_PREFIX . "shippnig_details
						WHERE shipment_date = '".$checkdate."'");
						
						
			if($ilance->db->num_rows($shippingpdflist) > 0)
			{
			
				while($shipping_list = $ilance->db->fetch_array($shippingpdflist,MYSQL_ASSOC))

				{	
			
				
                 $pdfshipping[] = $shipping_list;
			
                 }
				 
		}		
		
		
				
						

// create table
		$columns = array(); 

		  
	    $columns[] = $row_content_arr;
		$total_record=count($pdfshipping);
		
	if($total_record)
		{
      
		 foreach($pdfshipping as $shiplist)
		 {
		 
		     foreach($shiplist as $coin_columns)
			 {
			
			   $clval = $coin_columns;	
			

		   $row_content_arr[] = array('text' => $clval, 'width' => '31', 'height' => '5', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '7', 'font_style' => 'B', 'fillcolor' => '255 ,255 ,255 ', 'textcolor' => '76 ,84 ,86 ', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
		   
			 
			 }
			 
			  $columns[]=$row_content_arr;
			   unset($row_content_arr);
         
		  }
	}
		 else
		 {
		  $row_content_arr[] = array('text' => 'no record found', 'width' => '31', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '7', 'font_style' => 'B', 'fillcolor' => '255 ,255 ,255 ', 'textcolor' => '76 ,84 ,86 ', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
		  $columns[]=$row_content_arr;
			   unset($row_content_arr);
		 
		 }
		 
		
		 
		   $pdf->WriteTable($columns); 
		
		$pdf->SetFont('Times','',12); 
		$pdf->Output(); 
?>