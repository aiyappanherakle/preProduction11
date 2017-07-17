<?php

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
        'tabfx',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
//error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'export_consignor_csv') 
	{ 
	$DATETODAY=$ilance->GPC['start_date'];
		 //unbooked
                 $query="
		SELECT sum(i.totalamount-i.taxamount) as unbooked_buynow,i.projectid,c.End_Date ,i.user_id,r.last_listed_time  FROM " . DB_PREFIX . "invoices i 
		left join " . DB_PREFIX . "coins c on c.coin_id=i.projectid and date(i.createdate)< date(c.End_Date) 
		left join (select coin_id, CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date) ELSE '0000-00-00 00:00:00' END as last_listed_time from " . DB_PREFIX . "coin_relist where filtered_auctiontype = 'fixed' and date(actual_end_date)<date('".$DATETODAY."') group by coin_id ) r on c.coin_id = r.coin_id
		WHERE i.buynowid > 0 and i.isfvf=0 and i.isif=0 and i.isbuyerfee=0 and i.ismis=0 and date(c.End_Date)>date('".$DATETODAY."')
		and date(i.createdate) > date(ifnull(r.last_listed_time,'0000-00-00 00:00:00') ) and date(i.createdate) <= date('".$DATETODAY."')
		group by i.invoiceid
		";;
		$result=$ilance->db->query($query);
		if($ilance->db->num_rows($result))
		{
			while($line=$ilance->db->fetch_array($result))
			{
			$data['coin_id']=$line['projectid'];
			$data['END_DATE']=$line['End_Date'];
			$data['last_listed_time']=$line['last_listed_time'];
			$data['BUYER_ID']=$line['user_id'];
			
			 $data['unbooked_buynow']=$line['unbooked_buynow'];
			 $res[]=$data;
			 
			}
		}
	 $headings[0]='COIN_ID';
	$headings[1]='END_DATE';
	$headings[2]='BUYER_ID';
	$headings[3]='last_listed';
	$headings[4]='AMOUNT';
	


	$reportoutput = $ilance->admincp->construct_csv_data($res, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "unbooked_list-$timeStamp";
	$action = 'csv';
		if ($action == 'csv')
		{
			header("Pragma: cache");
			header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
			header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
			echo $reportoutput;
			die();
		}
		
	
 	
	}
	 
	 
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
    ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'consignors.html', 3);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	

}else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}


function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}

function get_statemant_number($user_id,$statement_date)
{
global $ilance;
$query="SELECT *  FROM " . DB_PREFIX . "consignor_satement WHERE user_id = '".$user_id."' and statement_date='".$statement_date."'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
return $line['id'];
}
}else
{
$query1="INSERT INTO gc_ilance.ilance_consignor_satement (user_id ,statement_date)VALUES ('".$user_id."', '".$statement_date."')";
	$ilance->db->query($query1);
	return $ilance->db->insert_id();
}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>