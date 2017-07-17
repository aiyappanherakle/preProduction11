<?php
require_once('./../functions/config.php');

error_reporting(e_all);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$sql=$ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices
							WHERE projectid=0
							AND combine_project !=''
							");
	$html='<table border="1"><tr><td>Parent Invoice</td><td>User ID</td><td>Non exists</td><td>Exists</td></tr>';
	$count=0;
	while($res=$ilance->db->fetch_array($sql))
	{
		$html.='<tr><td>'.$res['invoiceid'].'</td>';		
		$html.='<td>'.$res['user_id'].'</td>';
		$comb_array=explode(",",$res['combine_project']);
		$exist=array();
		$non_exist=array();	
		for($i=0;$i<count($comb_array);$i++)
		{
			
			$sql1=$ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices
									WHERE invoiceid='".$comb_array[$i]."'
									");
			if($ilance->db->num_rows($sql1) > 0)
			{
				$exist[]=$comb_array[$i];
			}
			else
			{
				$non_exist[]=$comb_array[$i];
				$count++;
			}
			
		}
		
		$exist_invoice=implode(",",$exist);
		$non_exist_invoice=implode(",",$non_exist);
		
		$html.='<td>'.$non_exist_invoice.'</td>';
		$html.='<td>'.$exist_invoice.'</td></tr>';
	
	}
	
	$html.='</table>';
	
	$final_html='Total no of missing child invoices ->'.$count.'<br>';
	$final_html.=$html;
	echo $final_html;
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>