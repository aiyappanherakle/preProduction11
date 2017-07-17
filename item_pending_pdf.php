<?php
require_once('functions/config.php');
require_once('fpdf.php'); 



        

        //sprt order by catalog and coinid
		
		
		
		
		$sqlcat_coin_detail = $ilance->db->query("

			SELECT  co.coin_id,co.Title,co.Minimum_bid,co.Buy_it_now,DATE_FORMAT(co.Create_Date,'%b-%d-%Y') 
			FROM  " . DB_PREFIX . "coins co,
			" . DB_PREFIX . "catalog_coin cc, 
			" . DB_PREFIX . "catalog_second_level cs, 
			" . DB_PREFIX . "catalog_toplevel cd 
			WHERE  	 co.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
			AND		co.project_id='0'		 
			AND 	co.status = '0'							
			AND co.pcgs=cc.PCGS 
			AND	cc.coin_series_unique_no=cs.coin_series_unique_no
			AND	cc.coin_series_denomination_no=cd.denomination_unique_no
			GROUP BY co.coin_id
			ORDER BY  cc.Orderno ,(CASE WHEN (co.pcgs = '6000120' OR co.pcgs = '6000127' OR co.pcgs = '6000128' OR co.pcgs = '6000129') THEN co.title END) ASC,co.grade DESC
					
		");
		
		

        //user consignment deatils
		
		if($ilance->db->num_rows($sqlcat_coin_detail) > 0)
		{

				

				while($row_coin_list = $ilance->db->fetch_array($sqlcat_coin_detail,MYSQL_ASSOC))

				{	
				
				    //Summary Total Calc
					if($row_coin_list['Minimum_bid']=='' AND $row_coin_list['Buy_it_now']!='')
					{
					   $sum_tot=$sum_tot+$row_coin_list['Buy_it_now'];
					}
					else
					{
					   $sum_tot=$sum_tot+$row_coin_list['Minimum_bid'];
					}
					
					$min_bid= $min_bid+$row_coin_list['Minimum_bid'];  //Total Min-Bid
					
					$buynow= $buynow+$row_coin_list['Buy_it_now'];  //Total Buynow

					if($row_coin_list['Minimum_bid'] == 0)
						$row_coin_list['Minimum_bid'] = '_blank';

					if($row_coin_list['Buy_it_now'] == 0)
						$row_coin_list['Buy_it_now'] = '_blank';
				
					$coinval[] = $row_coin_list;

				}
		}	

        
		 
		  
								
class PDF extends FPDF 
{ 

//Page header 
function Header() 
{ 
	global $mylink, $name, $total_post, $cid, $total_coin;
	//Logo 
	$this->Image(DIR_SERVER_ROOT.'greatcollections_logo.jpg',10,8,70);
	$this->SetFont('Arial','B',15); 
	//Move to the right 
	$this->Cell(120);
	$this->Ln(15);
	$this->SetFont('Arial','',15,'B');
	
	$this->SetTextColor(220,120,189);
	$this->Write(10, "GreatCollections - Consignments Pending Report",'Arial', '12');
	$this->Ln(10);
 
			
	$this->Ln(5); 
	//ttile value
	$title_field = array('Coin ID','Title','Min Bid','Buy Now','Entered Date');
	$col = array();
	for($r=0;$r<count($title_field);$r++)
	{
		   if($title_field[$r] == 'Title')
		   $width = '78';
		   else
		   $width = '28';
		   
		   $col[] = array('text' => $title_field[$r], 'width' => $width, 'height' => '7', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => 'B', 'fillcolor' => '62 ,70 ,72 ', 'textcolor' => '236,237,241', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
	
    } 
		   $you[] =$col;
			
		$this->WriteTable($you);
		$this->Ln(5);
} 


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
		
		//$title_field = array('Coin ID','Title','PCGS','Alt','Cert','Min Bid','Buy Now');
		
		$title_value = array($coin, $title, $pcgs, $alt, $cert, $mbid, $bnow);
		
      
		// create table
		$columns = array();      
		   
		
		//mutiple value

         $columns[] = $row_content_arr;
         $r=0;
		 foreach($coinval as $each_coin)
		 { 
		 	 
		     foreach($each_coin as $coin_columns)
			 {
			 
			   if($coin_columns == '')
			   $clval = '-';
			   else if($coin_columns == '_blank')
			   $clval = '';
			   else
			   $clval = $coin_columns;
			   
			   
			  
			   if($coinval[$r]['Title'] == $coin_columns)
			   $width = '78';
			   else
			   $width = '28';
			  
			   $row_content_arr[] = array('text' => $clval, 'width' => $width, 'height' => '5', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => 'B', 'fillcolor' => '255 ,255 ,255 ', 'textcolor' => '76 ,84 ,86 ', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
			 }
			 
		 $columns[]=$row_content_arr;
		 $r++;
		 
		 unset($row_content_arr);
		 }
	 
         $pdf->WriteTable($columns); 
		 
		   $pdf->Ln(5);
           $pdf->Cell(138);
           $pdf->SetFont('Arial','B',8);
	       $pdf->SetTextColor(76,84 ,86);
	       $pdf->Write(10,"Total:  " ,'Arial');
           $pdf->SetFont('Arial','B',8);
		   $pdf->SetTextColor(76,84 ,86);
           $pdf->Write(10,$ilance->currency->format($min_bid));
		   
		   //Buynow Total
		   $pdf->SetFont('Arial','B',8);
		   $pdf->SetTextColor(76,84 ,86);
           $pdf->Write(10,'   '.$ilance->currency->format($buynow));
		   
		   //Summary Total
          // $pdf->Ln(10);
		   //$pdf->Cell(130);
	      // $pdf->Write(10,"Summary Total:",'Arial');
		  // $pdf->Write(10,'   '.$ilance->currency->format($sum_tot));
			   
		
		$pdf->SetFont('Times','',12); 
		$pdf->Output('Item_Pending_Report.pdf', 'D'); 		

								
?>
