<?php
require_once('./../functions/config.php');
define('FPDF_FONTPATH','../font/');

require('pdftable_1.9/lib/pdftable1.inc.php');
$p = new PDFTable(); 

if($ilance->GPC['subcmd'] == 'catlog')
{
$orderby = 'CatlogOrder';
$sql['orderby']=' cd.denomination_sort,cs.coin_series_sort,cc.coin_detail_year';
}
else
{
$orderby = 'CoinOrder';
$sql['orderby']='c.coin_id';
}

$SQL="select
 c.coin_id,c.Title,c.pcgs,c.Alternate_inventory_No,c.Certification_No,c.Minimum_bid,c.Buy_it_now, 
 c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no
from 
" . DB_PREFIX . "coins c left join
" . DB_PREFIX . "catalog_coin cc on  c.pcgs=cc.PCGS left join
" . DB_PREFIX . "catalog_second_level cs on cc.coin_series_unique_no=cs.coin_series_unique_no left join
" . DB_PREFIX . "catalog_toplevel cd on cc.coin_series_denomination_no=cd.denomination_unique_no
where 
c.user_id = '".$ilance->GPC['user_id']."' AND 
c.consignid = '".$ilance->GPC['consignid']."'
order by ".$sql['orderby'];
 $sqlcat_coin_detail=$ilance->db->query($SQL);
 $sum_tot=0;
 $min_bid=0;
 $buynow=0;
 
 $name=fetch_user('username',$ilance->GPC['user_id']);
$html=header1($name,$ilance->GPC['consignid'],$ilance->GPC['nocoin'],$ilance->GPC['noposted']);

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
		 $row_coin_list['apr']=get_coin_history_price($row_coin_list['coin_id'],$row_coin_list['pcgs'],$row_coin_list['Grade'],$row_coin_list['Grading_Service'],$row_coin_list['Cac'],$row_coin_list['Star'],$row_coin_list['plus'],$row_coin_list['coin_series_unique_no'],$row_coin_list['coin_series_denomination_no']);
	 
		$coinval[] = $row_coin_list;
		$html.='<tr>
				<td>'.$row_coin_list['coin_id'].'</td>
				<td>'.$row_coin_list['Title'].'</td>
				<td>'.$row_coin_list['pcgs'].'</td>
				<td>'.$row_coin_list['Alternate_inventory_No'].'</td>
				<td>'.$row_coin_list['Certification_No'].'</td>
				<td>'.$row_coin_list['Minimum_bid'].'</td>
				<td>'.$row_coin_list['Buy_it_now'].'</td>
				<td>'.$row_coin_list['apr'].'</td>
				</tr>';
		
		
		
	}
	$html.='<tr><td colspan="5" align="right">Total : </td><td>'.$min_bid.'</td><td>'.$buynow.'</td><td></td>	</tr>
	<tr><td  colspan="5" align="right">Summary Total : </td><td>'.$sum_tot.'</td><td></td><td></td></tr>';
	 	
	  
}	
$p->AddPage();
$p->setfont('times','',10);	
$p->htmltable($html);
$p->output($name.'_Consignment_Report_'.$orderby.'.pdf','D');



function header1($email,$consignmentid,$estimated_count,$posted_count)
{
$new_header = '<table width="100%">
<tr>
	<td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
	<td>&nbsp;</td>
	<td size="13" family="helvetica" style="bold" nowrap color="#C878BD"><b>GreatCollections - Pre-Auction Consignment Report</b></td>
</tr>
<tr>
	<td colspan="3">Consignor Name : <strong>'.$email.'</strong>&nbsp;&nbsp;&nbsp;Consignment ID : <strong>'.$consignmentid.'</strong>&nbsp;&nbsp;&nbsp; Items Total : <strong>'.$estimated_count.'</strong> &nbsp;&nbsp;&nbsp; Items Entered : <strong>'.$posted_count.'</strong> </td>
</tr>
</table><table width="100%" border=1>
<tr bgcolor="#3E4648" color="#FFFFFF">
<!--<td></td>-->
<td size="9" width="2%"  color="#FFFFFF">Coin ID</td>
<td size="9" width="58%" color="#FFFFFF">Title</td>
<td size="9" width="2%" color="#FFFFFF">PCGS</td>	
<td size="9" width="2%" color="#FFFFFF">Alt</td>
<td size="9" width="2%" color="#FFFFFF">Cert</td>
<td size="9" width="2%" color="#FFFFFF">Min Bid</td>
<td size="9" width="2%" color="#FFFFFF">Buy Now</td>
<td size="9" width="30%" color="#FFFFFF">APR</td>
</tr>';
return $new_header;
}
		  
function get_coin_history_price($coin_id,$pcgs,$grade,$grading_service,$cac,$star,$plus,$seried_id,$denomination_id)
{
	global $ilance;
	//except denomination Raw Collections & Lots , World and Ancient Coins
	$excepmt_array=array();
	$excepmt_array[]=30;
	$excepmt_array[]=33;
	$excepmt_array[]=29; //except Error Coins by Bug #8397
	if(in_array($denomination_id,$excepmt_array))
	{
		return '';
	}
 
	$query="SELECT p.project_id,p.date_end,p.grade,p.grading_service,p.currentprice,p.Cac
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "coins c ON c.coin_id = p.project_id
				WHERE p.cid='$pcgs' 
				and p.date_end>= DATE_SUB(NOW(),INTERVAL 2 YEAR)
				and p.status='expired' 
				and p.grade='".$grade."'
				and p.project_title not like '%DETAILS%'
				and (p.haswinner=1 or p.hasbuynowwinner=1)  
				AND c.Plus = '".$plus."'
				order by 
				case when p.grading_service='".$grading_service."' then 1 else 2 end, 
				p.date_end desc, p.currentprice desc limit 5";
 
	 $result=$ilance->db->query($query);
	 if($ilance->db->num_rows($result))
	 {
		while($line=$ilance->db->fetch_array($result))
		{
			//$list[]='<a href="'.HTTP_SERVER.'coins/'.$line['project_id'].'">'.$line['currentprice'].substr($line['grading_service'],0,1).'</a>';
			if($line['Cac'])
				$list[]=$line['currentprice'].substr($line['grading_service'],0,1).'C';
			else	
				$list[]=$line['currentprice'].substr($line['grading_service'],0,1);
		}
		 return implode(', ',$list);
	 }
 }
								
?>
