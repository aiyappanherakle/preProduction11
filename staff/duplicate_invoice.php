<?PHP
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='delinvoice')
	{
		
		$ins = $ilance->db->query("INSERT INTO ".DB_PREFIX."duplicate_invoice (projectid,invoiceid,del_date) VALUES('".$ilance->GPC['itemid']."','".$ilance->GPC['id']."','".DATETIME24H."') ");
		$del = $ilance->db->query("DELETE FROM ".DB_PREFIX."invoices WHERE invoiceid = '".$ilance->GPC['id']."' AND projectid ='".$ilance->GPC['itemid']."'");
		print_action_success("Completed",'duplicate_invoice.php');
		exit();
	}

		$sel = $ilance->db->query("SELECT projectid, COUNT( * ) 
								FROM ".DB_PREFIX."invoices
								WHERE DATE( `createdate` ) = '".DATETODAY."'
								AND projectid !=0
								AND invoicetype = 'escrow'
								GROUP BY projectid
								HAVING COUNT( * ) >1 ");
			if($ilance->db->num_rows($sel) > 0)
			{
				$html = 'Multiple Invoice';
				$html.= '<table align="center" border=1><tr><td>Item Id</td><td>Invoice ID</td><td>Delete</td></tr>';
				while($res=$ilance->db->fetch_array($sel))
				{
					$selpjt = $ilance->db->query("SELECT project_id FROM ".DB_PREFIX."projects 
												WHERE project_id = '".$res['projectid']."'												
												AND filtered_auctiontype = 'regular'");
					
					if($ilance->db->num_rows($selpjt) >0)
					{
						$respjt = $ilance->db->fetch_array($selpjt);
						
						$html.='<tr>';
						$selinv = $ilance->db->query("select invoiceid from ".DB_PREFIX."invoices where projectid = '".$respjt['project_id']."' and invoicetype = 'escrow' order by invoiceid DESC limit 1, 10");
						while($resinv = $ilance->db->fetch_array($selinv))
						{
							$html.='<td>'.$respjt['project_id'].'<br></td>';
							$html.='<td>'.$resinv['invoiceid'].'<br></td>';
							$html.='<td><a href="duplicate_invoice.php?cmd=delinvoice&id='.$resinv['invoiceid'].'&itemid='.$respjt['project_id'].'">Delete</a><br></td></tr>';
						}
						
					}
					
				}
				echo $html.='</table>';
			}
			else
			{
				echo 'There is No Multiple Invoices. Check Once Again Later';
			}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>