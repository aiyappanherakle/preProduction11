<?php
require_once('./../functions/config.php');
require_once('../fpdf.php'); 



       

        //sprt order by catalog and coinid
		
		if($ilance->GPC['subcmd'] == 'catlog')
		{
		 
		$orderby = 'CatlogOrder';
		 $sqlcat_coin_detail=$ilance->db->query("select c.coin_id,c.Title,c.pcgs,c.Alternate_inventory_No,c.cost,c.Certification_No,c.Minimum_bid,c.Buy_it_now 
		from 
		" . DB_PREFIX . "coins c left join
		ilance_catalog_coin cc on  c.pcgs=cc.PCGS left join
		ilance_catalog_second_level cs on cc.coin_series_unique_no=cs.coin_series_unique_no left join
		ilance_catalog_toplevel cd on cc.coin_series_denomination_no=cd.denomination_unique_no
		where 
		c.user_id = '".$ilance->GPC['user_id']."' AND 
		c.consignid = '".$ilance->GPC['consignid']."'
		order by cd.denomination_sort,
		cs.coin_series_sort,
		cc.coin_detail_year
		");
		
		}
		else
		{
		$orderby = 'CoinOrder';
		$sqlcat_coin_detail = $ilance->db->query("

		SELECT coin_id,Title,pcgs,Alternate_inventory_No,cost,Certification_No,Minimum_bid,Buy_it_now

		FROM " . DB_PREFIX . "coins

		WHERE user_id = '".$ilance->GPC['user_id']."'

		AND consignid = '".$ilance->GPC['consignid']."'
		
		ORDER BY  coin_id ASC

		
		");
		
		}

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
				//echo '<pre>';print_r($row_coin_list);exit;
					$coinval[] = $row_coin_list;

				}
		}	

         $total_coin = $ilance->GPC['nocoin'];
		 $total_post = $ilance->GPC['noposted'];
		 $name = fetch_user('username',$ilance->GPC['user_id']);
		 $cid = $ilance->GPC['consignid'];
		 
		  
								
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
	$this->Write(10, "GreatCollections - Pre-Auction Consignment Report",'Arial', '12');
	$this->Ln(10);
	
	$this->SetFont('Arial','',8,'B');
	$this->SetTextColor(133,172,221);
	$this->Write(10, "Consignor Name : ".$name."    Consignment ID : ".$cid."    Items Total : ".$total_coin."    Items Entered : ".$total_post."",'Arial');
			
	$this->Ln(15); 
	//ttile value
	$title_field = array('Coin ID','Title','PCGS','Alt','Cost','Cert','Min Bid','Buy Now');
	$col = array();
	for($r=0;$r<count($title_field);$r++)
	{
		   if($title_field[$r] == 'Title')
		   	$width = '70';
		   else if($title_field[$r] == 'Coin ID')
		   	$width = '15';
		   else
		   	$width = '18';
		   
		   $col[] = array('text' => $title_field[$r], 'width' => $width, 'height' => '7', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '7', 'font_style' => 'B', 'fillcolor' => '62 ,70 ,72 ', 'textcolor' => '236,237,241', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
	
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
			 
			   if($coin_columns == '' || $coin_columns == '0')
			   $clval = ' -';
			   else
			   $clval = $coin_columns;
			   
			   
			  
			   if($coinval[$r]['Title'] == $coin_columns)
			   $width = '70';
			   else if($coinval[$r]['coin_id'] == $coin_columns)
			   $width = '15';
			   else
			   $width = '18';
			  
			   $row_content_arr[] = array('text' => $clval, 'width' => $width, 'height' => '5', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '7', 'font_style' => 'B', 'fillcolor' => '255 ,255 ,255 ', 'textcolor' => '76 ,84 ,86 ', 'drawcolor' => '0,0,0', 'linewidth' => '', 'linearea' => '');
			 }
			 
		 $columns[]=$row_content_arr;
		 $r++;
		 
		 unset($row_content_arr);
		 }
	 
         $pdf->WriteTable($columns); 
		 
		   $pdf->Ln(5);
           $pdf->Cell(148);
           $pdf->SetFont('Arial','B',7);
	       $pdf->SetTextColor(76,84 ,86);
	       $pdf->Write(10,"Total:  " ,'Arial');
           $pdf->SetFont('Arial','B',7);
		   $pdf->SetTextColor(76,84 ,86);
           $pdf->Write(10,$ilance->currency->format($min_bid));
		   
		   //Buynow Total
		   $pdf->SetFont('Arial','B',7);
		   $pdf->SetTextColor(76,84 ,86);
           $pdf->Write(10,'   '.$ilance->currency->format($buynow));
		   
		   //Summary Total
           $pdf->Ln(10);
		   $pdf->Cell(140);
	       $pdf->Write(10,"Summary Total:",'Arial');
		   $pdf->Write(10,'   '.$ilance->currency->format($sum_tot));
			   
		
		$pdf->SetFont('Times','',12); 
		$pdf->Output(''.$name.'_Consignment_Report_'.$orderby.'.pdf', 'D'); 		

								
?>
