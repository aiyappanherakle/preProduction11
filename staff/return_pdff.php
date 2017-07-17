<?php
require_once('../functions/config.php');
//error_reporting(E_ALL);
if (empty($_SESSION['ilancedata']['user']['userid']) or $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo 'login';
exit;
}


$coin_id_list='391902,393854';
		$sql_return = $ilance->db->query("SELECT c.*,u.first_name,u.last_name,u.email,u.address,u.address2,u.city,u.state,u.zip_code,u.phone
											FROM ".DB_PREFIX."coin_return c,
											".DB_PREFIX."users u
											WHERE 	c.coin_id in(".$coin_id_list.")
											AND  c.user_id = u.user_id
											ORDER BY c.return_id DESC LIMIT 1
												");
		$res_return = $ilance->db->fetch_array($sql_return);
		$prt_addr2='';
		if(!empty($res_return['address2']))
		{
		$prt_addr2=	$res_return['address2']."<br/>";			
		}
		
		//$seller_name=$res_return['first_name'].$res_return['last_name'];
		
		$seller_name=$res_return['first_name']." ".$res_return['last_name']."<br/>
						Address: ".$res_return['address']."<br/>".
						$prt_addr2.
						$res_return['city']." ".$res_return['state']." ".$res_return['zip_code']."<br/>
						E-mail: ".$res_return['email']."<br/>
						Telephone: ".$res_return['phone']."<br/><br/>";		
		
		if($res_return['return_opt'] == '0')
		{
		
			$sql_shipper  = $ilance->db->query("SELECT title
										FROM " . DB_PREFIX . "shippers WHERE shipperid='".$res_return['shipper_id']."'
										");
			$res_shipper = $ilance->db->fetch_array($sql_shipper);
			
			$return_via = $res_shipper['title'];
		
		}
		else
		{
			$return_via = $res_return['return_opt'];		 
		}
		
		
		$table = '<div style="border:1px solid black; padding : 10px">
				<table   border="0">				
					<tr>
						<td size="23" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
						<td size="12" align="right" width="100%" family="helvetica" style="bold"><b>RETURN TO CONSIGNOR</b></td>
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
					
					<tr>
						<td size="10">Consignor: '.$seller_name.'</td>
					</tr>
				</table>
				<br/><br/>
				<table width="100%" style="text-align:center; color: #FFFFFF;">
					<tr bgcolor="#CD9C9C">
						<td nowrap size="10"><p>ID</p></td>
						<td nowrap size="10" width="60%"><p>Item Title</p></td>
						<td nowrap size="10"><p>Consign ID</p></td>
						<td nowrap size="10"><p>Return Date</p></td> 
						<td nowrap size="10"><p>Return Via</p></td>
						<td nowrap size="10"><p>Notes</p></td>
					</tr>';
		// $sql_multiple_return = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coin_return
											// WHERE 	coin_id in(".$coin_id_list.")
												// ");
												
		$sql_multiple_return = 	$ilance->db->query("SELECT c.*,r.consignid as multiple_conisgn_id,r.Title,r.Minimum_bid as Minimum_bid ,r.Buy_it_now as Buy_it_now FROM ".DB_PREFIX."coin_return  c
									left join " . DB_PREFIX . "coins_retruned r on c.coin_id = r.coin_id 
									WHERE c.coin_id in(".$coin_id_list.")
									ORDER BY coin_id ASC
								");
								
		$count=0;								
		while($res_multiple_return = $ilance->db->fetch_array($sql_multiple_return))
		{
			//echo "UPDATE ilance_coin_return SET consign_id = ".$res_multiple_return['multiple_conisgn_id']." WHERE coin_id =". $res_multiple_return['coin_id']."";		
			
			$ilance->db->query("UPDATE " . DB_PREFIX . "coin_return SET consign_id = ".$res_multiple_return['consign_id']." WHERE coin_id = ".$res_multiple_return['coin_id']."");
			
				
			$table.='<tr>
				<td nowrap size="10">'.$res_multiple_return['coin_id'].'</td>
				<td size="10">'.$res_multiple_return['Title'].'</td>
				<td align="center" nowrap size="10">'.$res_multiple_return['multiple_conisgn_id'].'</td>
				<td align="center" nowrap size="10">'. date("m-d-Y", strtotime($res_multiple_return['return_date'])).'</td>
				<td nowrap size="10">'.$return_via.'</td>
				<td size="10">'.$res_multiple_return['notes_client'].'</td>
				</tr>';

				$totalminbid+=$res_multiple_return['Minimum_bid'];
				
			if($res_multiple_return['Buy_it_now'] > 0)
			{

			$totalBuy_it_now+=$res_multiple_return['Buy_it_now'];

			}
				
			$count++;	
		}	
		
		$table.='</table> <br/><br/>
					<table width="100%">
					<tr>
						
						<td size="11" >Total Item Count : <b>'.$count.'</b></td>
					</tr>
					</table>
					<table width="100%">
					<tr>
						
						<td size="11" >Total Min Bid : <b>'.$ilance->currency->format($totalminbid).'</b></td></b>
						<td size="11" >Total Buy Now : <b>'.$ilance->currency->format($totalBuy_it_now).'</b></td>
					</tr> 
					</table> 
				</div>';
				
		define('FPDF_FONTPATH','../font/');
		require('pdftable_1.9/lib/pdftable.inc.php');
		$p = new PDFTable();
		$p->AddPage();
		$p->setfont('times','',10);
		$p->htmltable($table);
		$p->output('Return consignor_'. DATETIME24H .'.pdf','D');





?>
