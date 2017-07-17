<?php

require_once('./../functions/config.php');

//error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND ($_SESSION['ilancedata']['user']['isstaff'] == '1' OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
{

	//$coming_sunday = date('Y-m-d', strtotime('next Sunday'));
	$date = strtotime(date('Y-m-d'));
	$coming_sunday = date("Y-m-d", strtotime('next sunday', $date));
	$today = date('Y-m-d');

	$select = $ilance->db->query(" 
	SELECT p.project_id,p.project_title,p.pcgs,p.bids,
	concat('http://www.greatcollections.com/Coin/',p.project_id,'/',replace(replace(p.project_title,' ','-'),',','')) as url,
	p.currentprice,p.bids,concat(u.first_name,' ',u.last_name) as username, case when p.cac=1 then 'Y' else 'N' end as cac 
	FROM ilance_projects p 
	left join ilance_users u on u.user_id=p.user_id 
	where p.status='open' and p.bids >0 and (date(p.date_end)>='".$today."' AND date(p.date_end)<='".$coming_sunday."' ) order by p.date_end asc	
	");

	if($ilance->db->num_rows($select) > 0)
    {



    	$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Currently-Listed-report_$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		//$fields = array('Coin ID','Title','PCGS#','URL','current Price','Bids','User Name','CAC');
		$fields = array('Coin ID','Title','PCGS#','URL','current Price','Bids','CAC');
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');
		fputcsv($fp, $fields);	

		$data_row = array();
    	while( $results = $ilance->db->fetch_array($select))
        {
        	$data_row['coinid'] = $results['project_id'];
			$data_row['title'] = $results['project_title'];
			$data_row['pcgs'] = $results['pcgs'];
			$data_row['url'] = $results['url'];
			$data_row['currentprice'] = $results['currentprice'];
			$data_row['bids'] = $results['bids'];
			//$data_row['username'] = $results['username'];
			$data_row['cac'] = $results['cac'];

			fputcsv($fp, $data_row);
        }
        exit;
    }

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
		

?>

