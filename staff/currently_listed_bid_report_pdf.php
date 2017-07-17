<?php
require_once('../functions/config.php');
//error_reporting(E_ALL);
if (empty($_SESSION['ilancedata']['user']['userid']) or $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo 'login';
exit;
}



		
		$table = '<div style="border:1px solid black; padding : 10px">
				<table   border="0">				
					<tr>
						<td size="23" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
						
					</tr>
					<tr>
						<td valign="top" size="10" family="helvetica" >
							Certified Coin Auctions & Direct Sales<br>
							17500 Red Hill Avenue, Suite 160, Irvine, CA 92614<br>
							Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
							E-mail: info@greatcollections.com
						</td>
						<td >&nbsp;</td>
						<td >&nbsp;</td>

					</tr>
					
			</table>
				<br/><br/>
				<table width="100%" style="text-align:center; color: #FFFFFF;">
					<tr bgcolor="#CD9C9C">
						<td nowrap size="10"><p>Coin ID</p></td>
						<td nowrap size="10" width="60%"><p>Title</p></td>
						<td nowrap size="10"><p>PCGS#</p></td>
						<td nowrap size="10"><p>Current Bids</p></td>
						</tr>';
		
												
		
	//$coming_sunday = date('Y-m-d', strtotime('next Sunday'));
	$date = strtotime(date('Y-m-d'));
	$coming_sunday = date("Y-m-d", strtotime('next sunday', $date));
	$today = date('Y-m-d');
	
	$select = $ilance->db->query(" 
	SELECT p.project_id,p.project_title,p.pcgs,p.currentprice
	FROM ".DB_PREFIX."projects p 
	left join ".DB_PREFIX."users u on u.user_id=p.user_id 
	where p.status='open' and p.bids >0 and (date(p.date_end)>='".$today."' AND date(p.date_end)<='".$coming_sunday."' ) order by p.date_end asc	
	");
	
	
										
		while($res_bid_report = $ilance->db->fetch_array($select))
		{
			
	
				
			$table.='<tr>
				<td nowrap size="10">'.$res_bid_report['project_id'].'</td>
				<td size="10">'.$res_bid_report['project_title'].'</td>
				<td nowrap size="10">'.$res_bid_report['pcgs'].'</td>
				<td size="10">'.$res_bid_report['currentprice'].'</td>
				</tr>';				
			
			$count++;	
		}	
		
			$table.='</table><br/><br/>
			<table width="100%">
			<tr>

			<td size="11" >Total Item Count : <b>'.$count.'</b></td>
			</tr>
			</table>
	
			</div>';
		
		
		
		define('FPDF_FONTPATH','../font/');
		require('pdftable_1.9/lib/pdftable.inc.php');
		$p = new PDFTable();
		$p->AddPage();
		$p->setfont('times','',10);
		$p->htmltable($table);
		$p->output('PDF_Dealer_Bid_Report_'. DATETIME24H .'.pdf','D');
 
?>
