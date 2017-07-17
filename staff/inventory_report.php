<?php

require_once('./../functions/config.php');

//error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND ($_SESSION['ilancedata']['user']['isstaff'] == '1' OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
{

	
	$timeStamp = date("Y-m-d-H-i-s");

	if($ilance->GPC['cmd'] == 'pending')
		$fileName = "gchouseian-pending-coins-report$timeStamp";
	else
		$fileName = "gchouseian-live-coins-report$timeStamp";

	header('Content-Type: text/csv; charset=utf-8');
	$fields = array('Coin ID','Title','Minimum Bid','Buy Now');
	header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
	$fp = fopen('php://output', 'w');
	fputcsv($fp, $fields);
	$data_row = array();

	if($ilance->GPC['cmd'] == 'pending')
	{
		$select = $ilance->db->query(" 
			SELECT coin_id,Title,Minimum_bid,Buy_it_now,Quantity
			FROM " . DB_PREFIX . "coins 
			WHERE user_id = '13115'
			AND coin_listed = 'c'
			AND (End_Date = '0000-00-00' OR pending = '1')	
			AND project_id  = '0'
			AND status = '0'
			GROUP BY coin_id
		");

		if($ilance->db->num_rows($select) > 0)
	    {
	    	while( $results = $ilance->db->fetch_array($select))
	        {
	        	$data_row['coinid'] = $results['coin_id'];
				$data_row['title'] = $results['Title'];
				$data_row['min_bid'] = $results['Minimum_bid'];
				$data_row['buy_now'] = $results['Buy_it_now'];

				fputcsv($fp, $data_row);
	        }
	    }


		$select1 = $ilance->db->query(" 
			SELECT c.coin_id,c.Title,c.Minimum_bid,c.Buy_it_now,p.max_qty FROM 
			" . DB_PREFIX . "projects p,
			" . DB_PREFIX . "coins c
			WHERE (p.status ='expired' OR p.status = 'closed')
			AND p.haswinner = '0'
			AND p.buynow_qty > '0'
			AND p.project_id = c.coin_id
			AND c.project_id != 0
			AND p.user_id = '13115'
			GROUP BY p.project_id
		");

		if($ilance->db->num_rows($select1) > 0)
	    {
			
	    	while( $results1 = $ilance->db->fetch_array($select1))
	        {
	        	$data_row['coinid'] = $results1['coin_id'];
				$data_row['title'] = $results1['Title'];
				$data_row['min_bid'] = $results1['Minimum_bid'];
				$data_row['buy_now'] = $results1['Buy_it_now'];

				fputcsv($fp, $data_row);
	        }
	    }
	}
	else
	{
		$select2 = $ilance->db->query(" 
			SELECT project_id, project_title, startprice, buynow_price, max_qty  
			FROM " . DB_PREFIX . "projects WHERE user_id = 13115 AND status = 'open'
		");

		if($ilance->db->num_rows($select2) > 0)
	    {
	    	while( $results2 = $ilance->db->fetch_array($select2))
	        {
	        	$data_row['coinid'] = $results2['project_id'];
				$data_row['title'] = $results2['project_title'];
				$data_row['min_bid'] = $results2['startprice'];
				$data_row['buy_now'] = $results2['buynow_price'];

				fputcsv($fp, $data_row);
	        }  
	    }
	}
    
    exit;
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
		

?>

