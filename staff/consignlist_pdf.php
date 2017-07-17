<?php
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

  $consign_id = $ilance->GPC['consign_id'];
	   
	   	$new_header = '<table width="100%">
						<tr>
							<td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
							<td>&nbsp;</td>
							<td size="13" family="helvetica" style="bold" nowrap><b>Consignment Details:'.$consign_id.'</b></td>
						</tr>
                         <tr><td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
	                                               17500 Red Hill Avenue, Suite 160, Irvine, CA 92614-7290<br>
                                                    Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
                                                     E-mail: info@greatcollections.com
	                     </td></tr>					
						<tr><td>&nbsp;</td></tr>
						</table><table width="100%"><tr bgcolor="#CD9C9C"> 
																		  <td>Consign ID</td>
																		  <td>CoinID</td>
																		  <td width = "45%">Item Title</td>
																		  <td>Listing Fees</td>
																		  <td>Sellers Fees</td>	
																		  <td>Bold</td>
																		  <td>Highlight</td>
																		  <td>Featured</td>
													</tr></table>';
													
	  $table1=$new_header;	
	
	  $sql = $ilance->db->query("SELECT consignid, coin_id, Title, listing_fee, bold, highlite, featured FROM ".DB_PREFIX."coins WHERE consignid ='".$consign_id."' ");
  
     if($ilance->db->num_rows($sql) > 0)
     {
        while($res = $ilance->db->fetch_array($sql))
		{
		
		  $sql_project = $ilance->db->query("SELECT * FROM ".DB_PREFIX."projects WHERE project_id ='".$res['coin_id']."'");
		  
		  $res_project = $ilance->db->fetch_array($sql_project);
		  
		  
		  $boldfee = ($res_project['bold']==1)?$ilance->currency->format($ilconfig['productupsell_boldfee'],$ilconfig['globalserverlocale_defaultcurrency']):'---';
		  $highlightfee = ($res_project['highlite']==1)?$ilance->currency->format($ilconfig['productupsell_highlightfee'],$ilconfig['globalserverlocale_defaultcurrency']):'---';
		  $featuredfee = ($res_project['featured']==1)?$ilance->currency->format($ilconfig['productupsell_featuredfee'],$ilconfig['globalserverlocale_defaultcurrency']):'---';
		
		  $listingfee =($res_project['insertionfee']>0)?$ilance->currency->format($res_project['insertionfee'],$ilconfig['globalserverlocale_defaultcurrency']):'---';
		  
		  $sellerfee =($res_project['fvf']>0)?$ilance->currency->format($res_project['fvf'],$ilconfig['globalserverlocale_defaultcurrency']):'---';
	
		  
		 $table1.='<tr>
		
		            <td>'.$res['consignid'].'</td> 

					<td>'.$res['coin_id'].'</td> 

                    <td width = "45%">'.$res['Title'].'</td>
		
					<td>'.$listingfee.'</td>
					
					<td>'.$sellerfee.'</td>

					<td>'.$boldfee.'</td>

					<td>'.$highlightfee.'</td>

					<td>'.$featuredfee.'</td>			
					
  </tr>';
	  
      }	  


     }

	else
	{
	 $table1.='<tr><td colspan="2"> NO Result Found </td></tr>';
	}

define('FPDF_FONTPATH','../font/');

require('pdftable_1.9/lib/pdftable.inc.php');

$p = new PDFTable();

$p->AddPage();

$p->setfont('times','',10);

$p->htmltable($table1);

$p->output('Consignor_List_'.date('Y-m-d h-i-s').'.pdf','D');

}

else
	{
		 refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	     exit();
	 }		 
			

?>

