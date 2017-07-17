<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'portfolio',
	'preferences',
	'selling',
	'search',
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
	'inline_edit',
	'flashfix',
	'jquery',
);

// #### define top header nav ##################################################
$topnavlink = array(
	'preferences',
);

// #### setup script location ##################################################
define('LOCATION', 'preferences');

// #### require backend ########################################################
require_once './functions/config.php';

//error_reporting(E_ALL);
$show['widescreen'] = true;
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[preferences]" => $ilcrumbs["$ilpage[preferences]"]);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) {
//error_reporting(E_ALL);

	$area_title = 'Consignor Statement';
	$page_title = SITE_NAME . ' - ' . 'Statement';
	$user_id = $_SESSION['ilancedata']['user']['userid'];

	$date_down = '<select name="date_down" id="date_down" >
	<option value="" selected="selected">Select</option>';

	$month_names['01'] = 'Jan';
	$month_names['02'] = 'Feb';
	$month_names['03'] = 'Mar';
	$month_names['04'] = 'Apr';
	$month_names['05'] = 'May';
	$month_names['06'] = 'Jun';
	$month_names['07'] = 'Jul';
	$month_names['08'] = 'Aug';
	$month_names['09'] = 'Sep';
	$month_names['10'] = 'Oct';
	$month_names['11'] = 'Nov';
	$month_names['12'] = 'Dec';

	$date_down = '<select name="date_down" id="date_down" >
								              <option value="" selected="selected">Select</option>';
	$con_date = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coins
										WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
										AND End_Date != '0000-00-00' and DAYOFWEEK(End_Date)=1
										GROUP BY date(End_Date) order by End_Date desc
										");
	$datecount = 0;
	if ($ilance->db->num_rows($con_date) > 0) {

		while ($res_date = $ilance->db->fetch_array($con_date)) {

			$date_coin = explode('-', $res_date['End_Date']);
			$date_day = explode(' ', $date_coin[2]);
			$month_name = $date_day[0] . '-' . $month_names[$date_coin[1]] . '-' . $date_coin[0];
			$month_namev = $date_coin[0] . '-' . $date_coin[1] . '-' . $date_day[0];

			$con_date_co = $ilance->db->query("
												SELECT COUNT(*) AS endcount
												FROM " . DB_PREFIX . "coins
												WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
												AND date(End_Date) = '" . $month_namev . "'


												");
			$res_date_co = $ilance->db->fetch_array($con_date_co);
			$item_count = $res_date_co['endcount'];

			$date_down .= '<option value="' . $month_namev . '">' . $month_name . ' <b>(' . $item_count . ' items)</b></option>';
			$datecount++;
		}
	}

	$date_down .= '</select>';

	if ((isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search') OR (isset($ilance->GPC['date']))) {
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search') {
			$date_new = $ilance->GPC['date_down'];
			$date_down_next = date('Y-m-d', (strtotime($ilance->GPC['date_down']) - (6 * 24 * 3600)));
			$dateexp = explode('-', $ilance->GPC['date_down']);
		} else if (isset($ilance->GPC['date'])) {
			$date_new = $ilance->GPC['date'];
			$date_down_next = date('Y-m-d', (strtotime($ilance->GPC['date']) - (6 * 24 * 3600)));
			$dateexp = explode('-', $ilance->GPC['date']);
		}

		$ilance->GPC['year'] = $dateexp[0];
		$ilance->GPC['month'] = $dateexp[1];
		$ilance->GPC['day'] = $dateexp[2];
		if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day'])) {
			$validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
			$validdate1 = intval($ilance->GPC['month']) . '-' . $ilance->GPC['day'] . '-' . $ilance->GPC['year'];
			//$settledate = intval($ilance->GPC['month']) + 1 . '-' . $ilance->GPC['day'] . '-' . $ilance->GPC['year'];
			$settledate = date('m-d-Y', strtotime("+1 months", strtotime($date_new))); //+1 month
			$date1 = $validdate1;
			$date = date('Y-m-d', strtotime($validdate));
		}

		$show['no_statement'] = false;

	} else {

		$show['no_statement'] = false;

		$date1 = date('m-d-Y');
		$date = DATETODAY;
		$date_new = DATETODAY;
		$date_down_next = date('Y-m-d', (strtotime(DATETODAY) - (6 * 24 * 3600)));
		$settledate = date(date('m') + 1 . '-' . date('d') . '-' . date('Y'));
		$settledate = date('m-d-Y', strtotime("+1 months", strtotime($date_new))); //+1 month

	}

	$grand_statement_final_total = 0;
	$grand_statement_listing_fee_total = 0;
	$grand_statement_seller_fee_total = 0;
	$grand_statement_seller_total = 0;
	$t = 0;
	$user_details['user_id'] = $_SESSION['ilancedata']['user']['userid'];

	$stmt_date = $date_new;
	$start = $date_down_next;
	$ilance->statement = construct_object('api.statement');
	$select = $ilance->statement->statement_query($user_details['user_id'], $start, $stmt_date);

	$statement_auction_price_total = '';
	$statement_buyer_fee_total = '';
	$coin_consignor_total = '';
	$statement_final_total = '';
	$statement_listing_fee_total = '';
	$statement_seller_fee_total = '';
	$statement_seller_total = '';
	$statement_buynow_total = '';

	$rows = $ilance->db->query($select);
	$listcount = $ilance->db->num_rows($rows);

	if ($ilance->db->num_rows($rows) > 0) {
		$show['statement'] = true;
		$row_count = 0;
		while ($coins_list_line = $ilance->db->fetch_array($rows)) {
			$all_paid = 1;
			$no_of_bids = 0;
			$no_of_buynow = 0;
			$coin_seller_fee = 0;
			$coin_final_price = 0;
			$coin_insertion_fee = 0;
			switch ($coins_list_line['filtered_auctiontype']) {
				case 'regular':
					$no_of_bids = $coins_list_line['bid_count'];
					$coin_final_price = $coins_list_line['escrow_invoice_total'];
					$statement_auction_price_total += $coin_final_price;
					$statement_buyer_fee_total += $coins_list_line['buyer_fee'];
					if ($coins_list_line['no_relist_b4_statement'] == 0) {
						$coin_insertion_fee = $coins_list_line['if_total'];
					} else {
						$coin_insertion_fee = 0;
					}
					$coin_insertion_fee = $coins_list_line['if_total'] + $coins_list_line['enhancementfee_total'];
					$coin_seller_fee = $coins_list_line['fvf_total'] + $coins_list_line['mis_total'];
					$all_paid = $coins_list_line['all_paid'];
					break;
				default:
					$no_of_buynow = intval($coins_list_line['order_count']) + intval($coins_list_line['ebay_order_count']);
					$coin_final_price = $coins_list_line['escrow_invoice_total'];
					$statement_buynow_total += $coin_final_price;

					if ($coins_list_line['no_relist_b4_statement'] == 0) {

					} else {
						$coin_insertion_fee = 0;
					}
					$coin_insertion_fee = $coins_list_line['if_total'] + $coins_list_line['enhancementfee_total'];
					$coin_seller_fee = $coins_list_line['fvf_total'] + $coins_list_line['mis_total'];
					$all_paid = $coins_list_line['all_paid'];
					break;
			}

			//$stmt['stateid'] = $coins_list_line['coin_id'];
			$url = construct_seo_url('productauctionplain', 0, $coins_list_line['coin_id'], stripslashes($coins_list_line['Title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
			$stmt['stateid']='<a href="' . $url . '">'.$coins_list_line['coin_id'].'</a>';
			$stmt['Title'] = $coins_list_line['Title'];
			$stmt['Certification_No'] = $coins_list_line['Certification_No'];
			$stmt['Alternate_inventory_No'] = $coins_list_line['Alternate_inventory_No'];
			$stmt['Site_Id'] = $coins_list_line['site_name'];
			$stmt['bids'] = $no_of_bids;
			$stmt['qty'] = $no_of_buynow > 0 ? $no_of_buynow : '';
			$stmt['bidamount'] = $ilance->currency->format_real_no($coins_list_line['Minimum_bid']);
			$stmt['binamount'] = $ilance->currency->format(isset($coins_list_line['Buy_it_now']) ? $coins_list_line['Buy_it_now'] : 0);
			$stmt['fvf'] = $ilance->currency->format_real_no($coin_final_price);
			$stmt['listing_fee'] = $ilance->currency->format_real_no($coin_insertion_fee, 0, false);
			$stmt['seller_fee'] = $ilance->currency->format_real_no($coin_seller_fee);

			$test4[] = $coins_list_line['Buy_it_now'];
			$test5[] = $coins_list_line['Minimum_bid'];
			$bidtot[] = $no_of_bids;
			$coin_consignor_total = $coin_final_price - ($coin_insertion_fee + $coin_seller_fee);
			$stmt['net_consignor'] = $ilance->currency->format_real_no($coin_consignor_total);
			$statement_final_total += $coin_final_price;
			$statement_listing_fee_total += $coin_insertion_fee;
			$statement_seller_fee_total += $coin_seller_fee;
			$statement_seller_total += $coin_consignor_total;

			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'print') {

				$data_csv['coin_id'] = $coins_list_line['coin_id'];
				$data_csv['Title'] = $coins_list_line['Title'];
				$data_csv['Site_Id'] = $coins_list_line['site_name'];
				$data_csv['Bids'] = $no_of_bids . '/' . $no_of_buynow;
				$res['Site_Id'] = $coins_list_line['site_name'];

				$data_csv['Minimum_bid'] = $ilance->currency->format_real_no($coins_list_line['Minimum_bid']) . ' / ' . $ilance->currency->format(isset($coins_list_line['Buy_it_now']) ? $coins_list_line['Buy_it_now'] : 0);
				$data_csv['Final Price'] = $ilance->currency->format_real_no($coin_final_price);
				$data_csv['Listing Fees'] = $ilance->currency->format_real_no($coin_insertion_fee, 0, false);
				$data_csv['Sellers Fees'] = $ilance->currency->format_real_no($coin_seller_fee);

				if ($coin_consignor_total > 0) {
					$data_csv['Net to Consignor'] = $ilance->currency->format($coin_consignor_total, $ilconfig['globalserverlocale_defaultcurrency']);
				} else {
					$coin_consignor_total_csv = str_replace("-", "", $coin_consignor_total);
					$data_csv['Net to Consignor'] = '-$' . $coin_consignor_total_csv . '.00';
				}

				$data_csv['Cert Number'] = $stmt['Certification_No'];
				$data_csv['Alt Inv'] = $stmt['Alternate_inventory_No'];
				
				$data[] = $data_csv;
			}

			$statement[$row_count] = $stmt;
			$row_count++;

		}

		$advance_received = 0;
		$miscellanious_debit = 0;
		$miscellanious_credit = 0;
		$statement_total = $statement_seller_total - $advance_received + $miscellanious_debit - $miscellanious_credit;
		$totbinamount = $ilance->currency->format(array_sum($test4), $ilconfig['globalserverlocale_defaultcurrency']);
		$totbidamount = $ilance->currency->format(array_sum($test5), $ilconfig['globalserverlocale_defaultcurrency']);
		$tot_bidbuy = $totbidamount . ' / ' . $totbinamount;
		$totbids = array_sum($bidtot);
		$totseller_fee = $ilance->currency->format_real_no($statement_seller_fee_total);
		$totlisting_fee = $ilance->currency->format_real_no($statement_listing_fee_total);
		//$totfvf=$ilance->currency->format_real_no($statement_seller_total);
		$totfvf = $ilance->currency->format_real_no($statement_final_total);
		$totnet_consignor = $ilance->currency->format_real_no($statement_seller_total);
		$total_advance = $ilance->currency->format_real_no($advance_received);
		$tot_mis = $ilance->currency->format_real_no($miscellanious_debit - $miscellanious_credit);
		$lastamount = $ilance->currency->format_real_no($statement_total);
		$statecount = '(' . $listcount . ' Items) will settle on ' . $settledate . ' (' . $lastamount . ')';

		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'print') {
			$ilance->admincp = construct_object('api.admincp');

			$headings = array('Item ID', 'Item Title', 'Listed', 'Bids', 'Min Bid / Buy Now', 'Final Price', 'Listing Fees', 'Sellers Fees', 'Net to Consignor', 'Cert Number', 'Alt Inv');

			$data[] = array('coin_id' => "", 'Title' => "", 'Site_Id' => "", 'Bids' => "", 'Minimum_bid' => "", 'Final Price' => "", 'Listing Fees' => "", 'Sellers Fees' => "", 'Net to Consignor' => "", 'Cert Number' => "", 'Alt Inv' => "" );

			$data[] = array('coin_id' => "Gross Total", 'Title' => "", 'Site_Id' => "", 'Bids' => $totbids, 'Minimum_bid' => $tot_bidbuy, 'Final Price' => $ilance->currency->format_real_no($statement_final_total), 'Listing Fees' => $ilance->currency->format_real_no($statement_listing_fee_total), 'Sellers Fees' => $ilance->currency->format_real_no($statement_seller_fee_total), 'Net to Consignor' => $ilance->currency->format_real_no($statement_seller_total), 'Cert Number' => "", 'Alt Inv' => "");

			$data[] = array('coin_id' => "Advance", 'Title' => "", 'Site_Id' => "", 'Bids' => "", 'Minimum_bid' => "", 'Final Price' => "", 'Listing Fees' => "", 'Sellers Fees' => "", 'Net to Consignor' => $ilance->currency->format_real_no($advance_received), 'Cert Number' => "", 'Alt Inv' => "");

			$data[] = array('coin_id' => "Miscellaneous", 'Title' => "", 'Site_Id' => "", 'Bids' => "", 'Minimum_bid' => "", 'Final Price' => "", 'Listing Fees' => "", 'Sellers Fees' => "", 'Net to Consignor' => $ilance->currency->format_real_no($miscellanious_debit - $miscellanious_credit), 'Cert Number' => "", 'Alt Inv' => "");

			$data[] = array('coin_id' => "Net Total", 'Title' => "", 'Site_Id' => "", 'Bids' => "", 'Minimum_bid' => "", 'Final Price' => "", 'Listing Fees' => "", 'Sellers Fees' => "", 'Net to Consignor' => $ilance->currency->format_real_no($statement_total), 'Cert Number' => "", 'Alt Inv' => "");

			$stmnt_date = date('F d, Y', strtotime($ilance->GPC['date']));
			$reportoutput = $ilance->admincp->construct_csv_data_mygc($data, $headings, $stmnt_date);

			header("Pragma: cache");
			header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
			header('Content-Disposition: attachment; filename="' . 'Consignor_Statement_' . date('Y-m-d h-i-s') . '.csv"');
			echo $reportoutput;
			die();
		}

	} else {
		$show['no_statement'] = true;
	}
	$pprint_array = array('tot_mis', 'user_id', 'date_down', 'lastamount', 'total_advance', 'statecount', 'date1', 'date', 'totbids', 'totbidamount', 'totbinamount', 'totfvf', 'totlisting_fee', 'totseller_fee', 'totnet_consignor', 'daylist', 'monthlist', 'yearlist', 'distance', 'subcategory_name', 'text', 'prevnext', 'prevnext2', 'remote_addr', 'rid', 'default_exchange_rate', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'date_new');

	$ilance->template->fetch('main', 'consigner_statement.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('statement'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

} else {
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['preferences'] . print_hidden_fields(true, array(), true)));
	exit();
}

function getSundaysList($startDate, $endDate, $weekdayNumber) {
	$startDate = strtotime($startDate);
	$endDate = strtotime($endDate);

	$dateArr = array();

	do {
		if (date("w", $startDate) != $weekdayNumber) {
			$startDate += (24 * 3600); // add 1 day
		}
	} while (date("w", $startDate) != $weekdayNumber);

	while ($startDate <= $endDate) {
		$dateArr[] = date('Y-m-d', $startDate);
		$startDate += (7 * 24 * 3600); // add 7 days
	}
	$dateArr = array_reverse($dateArr);
	return ($dateArr);
}