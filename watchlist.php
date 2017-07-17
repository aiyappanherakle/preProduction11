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
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### load required phrase groups ############################################
$phrase['groups'] = array(
        'watchlist',
        'feedback'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'flashfix',
	'jquery'
);

// #### define top header nav ##################################################
$topnavlink = array(
	'watchlist'
);

// #### setup script location ##################################################
define('LOCATION', 'watchlist');

// #### require backend ########################################################
require_once('./functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[watchlist]" => $ilcrumbs["$ilpage[watchlist]"]);

$ilance->watchlist = construct_object('api.watchlist');
$ilance->auction = construct_object('api.auction');
$ilance->feedback = construct_object('api.feedback');

$show['widescreen'] = true;
						



if (isset($ilance->GPC['wtype']) AND $ilance->GPC['wtype'] == 'rfp' AND isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'add' OR $ilance->GPC['cmd'] == 'upd') AND isset($ilance->GPC['id']))
{
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$id = intval($ilance->GPC['id']);
		if ($ilance->GPC['cmd'] == 'add')
		{
			$area_title = $phrase['_adding_rfp'].' ('.$id.') '.$phrase['_information_to_watchlist'];
			$page_title = SITE_NAME . ' - ' . $phrase['_adding_rfp'].' ('.$id.') '.$phrase['_information_to_watchlist'];
			$ilance->GPC['cmd'] = 'insert';
		}
		else if ($ilance->GPC['cmd'] == 'upd')
		{
			$area_title = $phrase['_updating'].' RFP ('.$id.') '.$phrase['_information_to_watchlist'];
			$page_title = SITE_NAME . ' - ' . $phrase['_updating'].' RFP ('.$id.')';
			$ilance->GPC['cmd'] = 'update';
		}
		
		$cmd = $ilance->GPC['cmd'];
		$wtype = $ilance->GPC['wtype'];
		$comment = '';
                
		$sql_users = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "watchlist
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND watching_project_id = '" . $id . "'
                                AND state = 'auction'
                ");
		if ($ilance->db->num_rows($sql_users) > 0)
		{
			$res = $ilance->db->fetch_array($sql_users);
			$comment = handle_input_keywords(stripslashes($res['comment']));


		}
		
		$ilance->template->load_popup('popupheader', 'popup_header.html');
		$ilance->template->load_popup('popupmain', 'popup_watchlist_add.html');
		$ilance->template->load_popup('popupfooter', 'popup_footer.html');
		$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('popupheader');
		$ilance->template->parse_if_blocks('popupmain');
		$ilance->template->parse_if_blocks('popupfooter');
		$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time'));
		$ilance->template->pprint('popupmain', array('comment','wtype','cmd','id','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','lanceads_header','lanceads_footer'));
		$ilance->template->pprint('popupfooter', array('headinclude','onload','area_title','page_title','site_name','lanceads_header','lanceads_footer','finaltime','finalqueries'));
		exit();
	}
	else
	{
		$area_title = $phrase['_access_denied_to_watchlist_resource'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_watchlist_resource'];

		$ilance->template->load_popup('popupheader', 'popup_header.html');
		$ilance->template->load_popup('popupmain', 'popup_denied.html');
		$ilance->template->load_popup('popupfooter', 'popup_footer.html');
		$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('popupheader');
		$ilance->template->parse_if_blocks('popupmain');
		$ilance->template->parse_if_blocks('popupfooter');
		$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time') );
		$ilance->template->pprint('popupmain', array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','lanceads_header','lanceads_footer'));
		$ilance->template->pprint('popupfooter', array('headinclude','onload','area_title','page_title','site_name','lanceads_header','lanceads_footer','finaltime','finalqueries'));
		exit();
	}
}

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) {
	// #### SUBMIT BID FOR PRODUCT AUCTION #########################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-submit' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'product-bid' AND $ilance->GPC['subcmd'] == 'product-bid') {
		global $ilance, $myapi, $ilpage, $phrase, $ilconfig;

		$ilance->email = construct_dm_object('email', $ilance);
		// #### define top header nav ##########################################
		$topnavlink = array(
			'main_buying',
		);

		if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0) {
			refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
			exit();
		}
		if ($_SESSION['ilancedata']['user']['enable_batch_bid'] == '0') {
			refresh(HTTPS_SERVER . $ilpage['watchlist'] . print_hidden_fields(true, array(), true));
			exit();
		}

		$show['widescreen'] = false;

		$confirmbid_array = array();
		$html = '';
		$confirmbidamount = $ilance->GPC['confirm_bid_amount'];
		foreach ($confirmbidamount as $key => $confirmbidamt) {

			$message_item_arr[] = $confirmbidamt['projectlist_id'];
			$mytest = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $confirmbidamt['projectlist_id'] . "'
						AND bidamount = '" . $confirmbidamt['next_bid_amount'] . "'
					", 0, null, __FILE__, __LINE__);

			$sql_bid_validate = $ilance->db->query("
					SELECT startprice,currentprice,bids,user_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $confirmbidamt['projectlist_id'] . "'

					", 0, null, __FILE__, __LINE__);

			$res_bid_validate = $ilance->db->fetch_array($sql_bid_validate);
			$current_bid_amount = $res_bid_validate['currentprice'];
			$start_bid_amount = $res_bid_validate['startprice'];
			$bid_count = $res_bid_validate['bids'];

			$bidplacd = 0;



			if ($ilance->db->num_rows($mytest) == 0) 
			{



				$ilance->auction = construct_object('api.auction');
				$ilance->bid = construct_object('api.bid');
				$ilance->bid_coins = construct_object('api.bid_coins');

				$confirmbidamt['higherbid'] = isset($confirmbidamt['higherbid']) ? intval($confirmbidamt['higherbid']) : 0;
				$confirmbidamt['lasthour'] = isset($confirmbidamt['lasthour']) ? intval($confirmbidamt['lasthour']) : 0;
				$confirmbidamt['subscribed'] = isset($confirmbidamt['subscribed']) ? intval($confirmbidamt['subscribed']) : 0;
				$confirmbidamt['shipperid'] = isset($confirmbidamt['shipperid']) ? intval($confirmbidamt['shipperid']) : 0;
				$fetched_minimum_bid = $ilance->bid->fetch_minimum_bid($ilance->bid->fetch_highest_bid($confirmbidamt['projectlist_id']));
				if ($confirmbidamt['next_bid_amount'] < $fetched_minimum_bid and $confirmbidamt['next_bid_amount'] > $start_bid_amount and $bid_count > 0) 
				{ 
					$not_bid = $confirmbidamt['projectlist_id'];
					$bidplacd = 0;
				}
				else
				{
					$bidplacd = 1; 
					$show['is_bidplaced'] = true;
					$buyershipcost = array('total' => 0);
					$ilance->GPC['buyershipcost'] = $buyershipcost['total'];
					$ilance->bid_coins->multiple_placebid($confirmbidamt['higherbid'], $confirmbidamt['lasthour'], $confirmbidamt['subscribed'], intval($confirmbidamt['projectlist_id']), intval($res_bid_validate['user_id']), $confirmbidamt['next_bid_amount'], $confirmbidamt['qty'], $_SESSION['ilancedata']['user']['userid'], $ilconfig['productbid_enableproxybid'], $confirmbidamt['current_bid'], $ilance->auction->fetch_reserve_price(intval($confirmbidamt['projectlist_id'])), false, $ilance->GPC['buyershipcost'], $confirmbidamt['shipperid']);





				}
				
			}
			else { }





				//work for batch bid result summary

				$SQL = "SELECT  pr.maxamount,p.project_details,p.filtered_auctiontype,p.bids,p.project_state,p.description,p.status,p.close_date,p.currencyid,p.startprice,p.currentprice,b.bid_id,p.project_id,p.project_title,UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime,ca.filename as filehash,(SELECT COUNT(attachid) AS picture_count FROM " . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,
								 (select user_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id  order by bidamount desc,date_added asc limit 1) as winning_user_id
								FROM " . DB_PREFIX . "projects p
								left join " . DB_PREFIX . "project_bids b on b.project_id = p.project_id and b.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
								left join " . DB_PREFIX . "proxybid pr on pr.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' and pr.project_id = p.project_id
								left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
								WHERE  p.project_id='" . $confirmbidamt['projectlist_id'] . "'
								group by p.project_id
								ORDER BY UNIX_TIMESTAMP(p.date_end)  ASC ";

				$result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
				

				if ($ilance->db->num_rows($result) > 0) {
					
					$row_count=0;
					while ($rows = $ilance->db->fetch_array($result)) {
						$confirmbidamt['title'] = $rows['project_title'];
						$row['auctionpage'] = $ilpage['merch'];
						if ($ilconfig['globalauctionsettings_seourls']) {
							$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							$row['title'] = construct_seo_url('productauction', 0, $rows['project_id'], stripslashes($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						} else {
							$row['title'] = '<a href="' . $row['auctionpage'] . '?id=' . $rows['project_id'] . '">' . stripslashes($rows['project_title']) . '</a>';
						}
						$row['bids'] = $rows['bids'] == 0 ? '-' : $rows['bids'] . ' ' . $phrase['_bids_lower'];
						$row['filehash'] = $rows['filehash'];
						$row['picture_count'] = $rows['picture_count'];
						$row['comment'] = $rows['comment'];
						$row['attach'] = '';
						$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']));
						if (!empty($rows['filehash'])) {
							$row['sample'] = '
												<div class="gallery-thumbs-cell">
												<div class="gallery-thumbs-entry">
														<div class="gallery-thumbs-main-entry">
																<div class="gallery-thumbs-wide-wrapper">
																		<div class="gallery-thumbs-wide-inner-wrapper">
												<a href="' . $url . '"><img src="' . HTTPS_SERVER . 'image/72/96/' . $rows['filehash'] . '" border="0" alt="" style="border-color:#ffffff" class="gallery-thumbs-image-cluster" /></a>
												<div class="gallery-thumbs-corner-text"><span>' . $rows['picture_count'] . ' photos</span></div>
																		</div>
																</div>
														</div>
												</div>
												</div>';
						} else {
							$row['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
						}
						$row['watching_project_id'] = '<a href="' . $row['auctionpage'] . '?id=' . $rows['project_id'] . '">' . stripslashes($rows['project_id']) . '</a>';
						$row['description'] = $ilance->bbcode->strip_bb_tags($rows['description']);
						$row['description'] = short_string($row['description'], 100);
						$row['description'] = handle_input_keywords($row['description']);


						$currencyid = $rows['currencyid'];
						$bids = $rows['bids'];
						$startprice = $rows['startprice'];
						$currentbid = $rows['currentprice'];
						$confirmbidamt['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
						$confirmbidamt['timeleft'] = $ilance->auction->auction_time_left_internal_email($rows, false);
						$row['timeleft'] = $ilance->auction->auction_time_left_internal($rows, false);
						$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
						$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
						$maxbid = $rows['maxamount'] > 0 ? $rows['maxamount'] : '';

						if(isset($bidplacd) AND $bidplacd == 1)
						{
							$row['status'] = '<img alt="" title="Bid Placed" src="http://www.greatcollections.com/images/gc/Green_Tick.png" width="35" />';
						}
						else
						{
							$row['status'] = '<img alt="" title="Bid not Placed" src="http://www.greatcollections.com/images/gc/DeleteRed.png" width="35" />';
						}
							

						if ($rows['bid_id'] > 0) {
							$highbidderid = $rows['winning_user_id'];
							if ($highbidderid == $_SESSION['ilancedata']['user']['userid']) {
								$row['currentbid'] .= "<br><span class=\"green\">You are currently winning</span><br><span class=\"green\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
								
							} 
							else 
							{
								if($maxbid !=''AND $maxbid>0)

									$row['currentbid'] .= "<br><span class=\"red\">You were outbid by another bidder! Place a new maximum bid.</span><br><span class=\"red\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
								else
									$row['currentbid'] .= "<br><span class=\"red\">You were outbid by another bidder!</span><br><span class=\"red\">Place a new maximum bid.</span>";
							}
						} 
						else 
						{
							$row['currentbid'] .= ($rows['filtered_auctiontype'] == 'fixed') ? '<div class="smaller gray">Buy Now</div>' : '';
						}

						$watchlist_summary[] = $row;
						$row_count++;
					}

				if(isset($bidplacd) AND $bidplacd == 1)
				{
					$link = HTTPS_SERVER . $ilpage['merch'] . '?id=' . intval($confirmbidamt['projectlist_id']);
					$html .= "***********************************" . "\n";
					$html .= "Listing Information" . "\n";
					$html .= "***********************************" . "\n";
					$html .= 'Title: ';
					$html .= $confirmbidamt['title'] . ' (Listing #:';
					$html .= $confirmbidamt['projectlist_id'] . ")\n";
					$html .= 'Link: ';
					$html .= $link . "\n";
					$html .= 'Bidding Ends:';
					$html .= $confirmbidamt['timeleft'] . "\n\n";
					$html .= 'Current Bid: ';
					$html .= $ilance->currency->format(fetch_auction('currentprice', $confirmbidamt['projectlist_id']), $currencyid) . "\n";
					$html .= 'Your Secret Maximum Bid: ';
					$html .= $ilance->currency->format($confirmbidamt['next_bid_amount'], $currencyid) . "\n\n";

				}
			
			}
		}

		if(isset($show['is_bidplaced']) AND $show['is_bidplaced'] == true)
		{
			$provider .= fetch_user('username', $_SESSION['ilancedata']['user']['userid']);
			$email_notify1 = fetch_user('emailnotify', $_SESSION['ilancedata']['user']['userid']);

			$query_bid_confirmation1 = $ilance->db->query("SELECT bidconfirm FROM " . DB_PREFIX . "email_preference

											  WHERE user_id ='" . intval($_SESSION['ilancedata']['user']['userid']) . "'");

			$row_bid_confirmation1 = $ilance->db->fetch_array($query_bid_confirmation1);


			if ($row_bid_confirmation1['bidconfirm'] == '1' AND $email_notify1 == '1') {


				$ilance->email->mail = fetch_user('email', $_SESSION['ilancedata']['user']['userid']);
				$ilance->email->logtype = 'Batch Biddings';
				$ilance->email->slng = fetch_user_slng($_SESSION['ilancedata']['user']['userid']);
				$ilance->email->get('bid_notification_alert_bidder_for_batch_biddings');
				$ilance->email->set(array('{{provider}}' => $provider, '{{details}}' => $html));
				$ilance->email->send();


			}
			$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];

			$ilance->email->logtype = 'Batch Biddings';
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('bid_notification_alert_bidder_for_batch_biddings');
			$ilance->email->set(array('{{provider}}' => $provider, '{{details}}' => $html));
			$ilance->email->send();

			$ilance->email->mail = $ilconfig['globalserversettings_testemail'];
			$ilance->email->logtype = 'Batch Biddings';
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('bid_notification_alert_for_batch_biddings');
			$ilance->email->set(array('{{provider}}' => $provider, '{{details}}' => $html));
			$ilance->email->send();

			$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
			$ilance->email->logtype = 'Batch Biddings';
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('bid_notification_alert_bidder_for_batch_biddings');
			$ilance->email->set(array('{{provider}}' => $provider, '{{details}}' => $html));
			$ilance->email->send();
		}



















		// work for mail single mail ends
		$pprint_array = array('tabindex', 'tab', 'input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'prevnext');
		$ilance->template->fetch('main', 'watchlist_summary.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('watchlist_summary'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();

	}

	$ilance->subscription = construct_object('api.subscription');
	if (true) {
		// #### ADD AUCTION TO WATCHLIST ###############################
		if (isset($ilance->GPC['wtype']) AND $ilance->GPC['wtype'] == 'rfp' AND isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'insert' OR $ilance->GPC['cmd'] == 'update') AND isset($ilance->GPC['id']) AND !empty($ilance->GPC['id'])) {
			if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insert') {
				$id = intval($ilance->GPC['id']);
				$area_title = $phrase['_adding_rfp_pound'] . $id . ' ' . $phrase['_to_watchlist'];
				$page_title = SITE_NAME . ' - ' . $phrase['_adding_rfp_pound'] . $id . ' ' . $phrase['_to_watchlist'];
				$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], $id, 'auction', $ilance->GPC['comment']);
			} else if ($ilance->GPC['cmd'] == 'update') {
				$id = intval($ilance->GPC['id']);
				$area_title = $phrase['_updating'] . ' (' . $id . ')';
				$page_title = SITE_NAME . ' - ' . $phrase['_updating'] . ' (' . $id . ')';
				$ilance->watchlist->update_item($_SESSION['ilancedata']['user']['userid'], $id, 'auction', $ilance->GPC['comment']);
			}

			$ilance->template->load_popup('popupheader', 'popup_header.html');
			$ilance->template->load_popup('popupmain', 'popup_watchlist_added.html');
			$ilance->template->load_popup('popupfooter', 'popup_footer.html');
			$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
			$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
			$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('popupheader');
			$ilance->template->parse_if_blocks('popupmain');
			$ilance->template->parse_if_blocks('popupfooter');
			$ilance->template->pprint('popupheader', array('headinclude', 'onload', 'onbeforeunload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'meta_desc', 'meta_keyw', 'official_time'));
			$ilance->template->pprint('popupmain', array('input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name',  'lanceads_header', 'lanceads_footer'));
			$ilance->template->pprint('popupfooter', array('headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'lanceads_header', 'lanceads_footer', 'finaltime', 'finalqueries'));
			exit();
		} else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-watch-cmd') {
			// remove auctions
			if (isset($ilance->GPC['state']) AND $ilance->GPC['state'] == 'auction' AND isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'delete') {
				if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'delete') {
					if (isset($ilance->GPC['project_id'])) {
						foreach ($ilance->GPC['project_id'] as $value) {
							$ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "watchlist
                                                                WHERE watching_project_id = '" . intval($value) . "'
                                                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                                        AND state = '" . $ilance->GPC['state'] . "'
                                                        ");
						}

						refresh($ilpage['watchlist']);
						exit();
					} else {
						refresh($ilpage['watchlist']);
						exit();
					}
				}
			} else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-wantlist' AND isset($ilance->GPC['id'])) {
				$ilance->db->query(" DELETE FROM " . DB_PREFIX . "wantlist
                                                                WHERE wantid = '" . $ilance->GPC['id'] . "'
                                                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'

                                ");
				refresh($ilpage['watchlist']);
				exit();
			}
			//Murugan Coding For Want List On Nov 01 2010  End Here
		}
		// murugan changes on Jun 22 for remove all watchlist item

		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removeall') {
			$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND watching_project_id != 0
								");
			print_notice("Success", "All watchlist items are deleted successfully", $ilpage['watchlist'], $phrase['_return_to_the_previous_menu']);
			exit();
		} else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removeend') {
			$select = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "watchlist
										WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
										AND watching_project_id != 0
										");
			if ($ilance->db->num_rows($select) > 0) {
				while ($res = $ilance->db->fetch_array($select)) {
					if (fetch_auction('status', $res['watching_project_id']) == 'expired') {
						$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND watching_project_id = '" . $res['watching_project_id'] . "'
								");
					} else {
						continue;
					}
				}

			}
			print_notice("Success", "All Ended watchlist items are deleted successfully", $ilpage['watchlist'], $phrase['_return_to_the_previous_menu']);
			exit();
		}
		// Murugan end
		else {

			//watch list starts
			$area_title = $phrase['_watchlist_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_watchlist_menu'];

			$navcrumb = array();
			$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
			$navcrumb[""] = $phrase['_watchlist_menu'];

			$ilance->bbcode = construct_object('api.bbcode');
			$ilance->bid = construct_object('api.bid');
			//$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');

			// inline auction ajax controls
			$headinclude .= "
<script type=\"text/javascript\">
<!--
var watchlistid = 0;
var type = '';
var value = '';
var imgtag = '';
var watchlisticon = '';
var status = '';
function reset_image()
{
        imgtag.src = watchlisticon;
}
function reset_image2()
{
        imgtag.src = watchlisticon2;
}
function fetch_response(type)
{
        if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
        {
                // format response
                response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
                phpstatus = xmldata.fetch_data(response);

                watchiconsrc = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid).src;
                status = watchiconsrc.match(/\/unchecked.gif/gi);
                if (status == '/unchecked.gif')
                {
                       status = 'unchecked';
                }
                else
                {
                       status = 'checked';
                }
                if (status == 'unchecked')
                {
                        if (phpstatus == 'on' || phpstatus == 'off')
                        {
                                // updating from enabled to disabled
                                watchlisticonsrc = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid).src;
                                imgtag = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid);

                                watchlisticon2 = watchlisticonsrc.replace(/unchecked.gif/gi, 'working.gif');
                                imgtag.src = watchlisticon2;

                                watchlisticon = watchlisticonsrc.replace(/unchecked.gif/gi, 'checked.gif');
                                var t = window.setTimeout('reset_image()', 700);

                                if (xmldata.type != 'subscribed')
                                {
                                        watchlisticonsrc2 = fetch_js_object('inline_watchlist_subscribed_' + xmldata.watchlistid).src;
                                        imgtag2 = fetch_js_object('inline_watchlist_subscribed_' + xmldata.watchlistid);

                                        substatus = watchlisticonsrc2.match(/\/unchecked.gif/gi);
                                        if (substatus == '/unchecked.gif')
                                        {
                                                imgtag2.src = watchlisticonsrc2.replace(/unchecked.gif/gi, 'checked.gif');
                                        }
                                }

                        }
                        else
                        {
                                alert(phpstatus);
                        }

                }
                else if (status == 'checked')
                {
                        if (phpstatus == 'on' || phpstatus == 'off')
                        {
                                // updating from disabled to enabled
                                watchlisticonsrc = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid).src;
                                imgtag = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid);

                                watchlisticon2 = watchlisticonsrc.replace(/checked.gif/gi, 'working.gif');
                                imgtag.src = watchlisticon2;

                                watchlisticon = watchlisticonsrc.replace(/checked.gif/gi, 'unchecked.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert(phpstatus);
                        }

                }
                xmldata.handler.abort();
        }
}
function update_watchlist(type, watchlistid)
{
        // set ajax handler
        xmldata = new AJAX_Handler(true);

        // url encode the vars
        watchlistid = urlencode(watchlistid);
        xmldata.watchlistid = watchlistid;
        type = urlencode(type);
        xmldata.type = type;

        watchiconsrc = fetch_js_object('inline_watchlist_' + type + '_' + watchlistid).src;
        status = watchiconsrc.match(/\/unchecked.gif/gi);
        if (status == '/unchecked.gif')
        {
               value = 'on';
        }
        else
        {
               value = 'off';
        }
        xmldata.onreadystatechange(fetch_response);

        // send data to php
        xmldata.send('ajax.php', 'do=watchlist&type=' + type + '&value=' + value + '&watchlistid=' + watchlistid + '&s=' + ILSESSION + '&token=' + ILTOKEN);
}
//-->
</script>";

			// #### AUCTIONS #######################################

			//New Changes on Dec-26
			//pagnation

			$show['active_list'] = '1';
			$show['recently_ended'] = '0';
			$show['ended_list'] = '0';
			$active_scriptpage = 'watchlist.php?';

			$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

			$ilconfig['globalfilters_maxrowsdisplay'] = '75';

			$active_counter = (intval($ilance->GPC['page']) - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$active_row_count = 0;
			
				$sqlenablebatchbidding = $ilance->db->query("SELECT enable_batch_bid  FROM ".DB_PREFIX."users
					WHERE   user_id = '".$_SESSION['ilancedata']['user']['userid']."' and enable_batch_bid = '1'
					");

				$countenablebatchbidding = $ilance->db->num_rows($sqlenablebatchbidding);
				$enablebatchbidding ='0';
				if ($countenablebatchbidding > 0)
				{
				$enablebatchbidding ='1';

				}
			$active = $ilance->db->query("SELECT  pr.maxamount,w.comment,p.project_details,p.filtered_auctiontype,p.user_id as project_user_id ,p.buynow_qty,p.buynow_price,p.max_qty,p.currencyid,p.bids,p.project_state,p.description,p.status,p.currencyid,p.startprice,p.currentprice,w.watching_project_id,p.project_id,p.project_title,UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime,ca.filename as filehash,(SELECT COUNT(attachid) AS picture_count FROM " . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,
												  (select user_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id  order by bidamount desc,date_added asc limit 1) as winning_user_id,
												  ifnull((SELECT bidamount FROM " . DB_PREFIX . "project_bids WHERE project_id = p.project_id ORDER BY bidamount DESC, date_added ASC LIMIT 1),1 ) as bid_amount,(select bid_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id and user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' order by bidamount desc,date_added asc limit 1) as bid_id,
												  (select bid_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id and user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' order by bidamount desc,date_added asc limit 1) as bid_id,
												  (SELECT amount FROM " . DB_PREFIX . "increments  WHERE ((increment_from <= bid_amount AND increment_to >= bid_amount) OR (increment_from < bid_amount AND increment_to < bid_amount)) AND groupname = 'default' ORDER BY amount DESC limit 1) as increment
						                          FROM " . DB_PREFIX . "watchlist w
												  left join " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
												  left join " . DB_PREFIX . "proxybid pr on pr.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' and pr.project_id = w.watching_project_id
												  left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
						                          WHERE p.status = 'open' and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
						                          ORDER BY UNIX_TIMESTAMP(date_end)  ASC
						                          LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
				, 0, null, __FILE__, __LINE__);

			//total active
			$activepag = $ilance->db->query("SELECT w.watching_project_id,p.project_id, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
													FROM " . DB_PREFIX . "watchlist w
													left join  " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
													WHERE p.status = 'open' and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
													ORDER BY UNIX_TIMESTAMP(p.date_end)  DESC
													", 0, null, __FILE__, __LINE__);

			$active_number = (int) $ilance->db->num_rows($activepag);

			if ($ilance->db->num_rows($active) > 0) {
				$c = 1;
				while ($rows = $ilance->db->fetch_array($active)) {

					if ($rows['bids'] == 0) {
						$row['bids'] = '-';
					} else {
						$row['bids'] = $rows['bids'] . ' ' . $phrase['_bids_lower'];
					}

					$row['action'] = '<input type="checkbox" name="project_id[]" value="' . $rows['project_id'] . '" />';

					if ($rows['project_state'] == 'product') {
						$row['comment'] = $rows['comment'];
						$row['attach'] = '';
						$row['auctionpage'] = $ilpage['merch'];
						$pictures = $rows['picture_count'];
						$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						$borderwidth = 0;
						$bordercolor = "#ffffff";
						if (!empty($rows['filehash'])) {
							$row['sample'] = '
								<div class="gallery-thumbs-cell">
								<div class="gallery-thumbs-entry">
										<div class="gallery-thumbs-main-entry">
												<div class="gallery-thumbs-wide-wrapper">
														<div class="gallery-thumbs-wide-inner-wrapper">
								<a href="' . $url . '"><img src="' . HTTPS_SERVER . 'image/72/96/' . $rows['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>
								<div class="gallery-thumbs-corner-text"><span>' . $pictures . ' photos</span></div>
														</div>
												</div>
										</div>
								</div>
								</div>';
						} else {
							$row['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
						}

						// display thumbnail
						if ($ilconfig['globalauctionsettings_seourls']) {
							$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);

							// $row['sample'] = print_item_photo($url, 'thumb', $rows['project_id']);
							$row['title'] = construct_seo_url('productauction', 0, $rows['project_id'], stripslashes($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						} else {
							//  $row['sample'] = print_item_photo($ilpage['merch'] . '?id=' . $rows['project_id'], 'thumb', $rows['project_id']);
							$row['title'] = '<a href="' . $row['auctionpage'] . '?id=' . $rows['project_id'] . '">' . stripslashes($rows['project_title']) . '</a>';
						}
					}
					$row['watching_project_id'] = $rows['project_id'];
					$row['description'] = $ilance->bbcode->strip_bb_tags($rows['description']);
					$row['description'] = short_string($row['description'], 100);
					$row['description'] = handle_input_keywords($row['description']);
					$row['status'] = print_auction_status($rows['status']);

					// is bid placed?

					$row['bidplaced'] = ($rows['bid_id'] > 0)
					? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="' . $phrase['_you_have_placed_a_bid_on_this_auction'] . '" />'
					: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="' . $phrase['_place_a_bid'] . '" />';

					// currency
					$currencyid = $rows['currencyid'];

					// bids
					$bids = $rows['bids'];

					// starting price
					$startprice = $rows['startprice'];

					// current bid
					$currentbid = $rows['currentprice'];
					$row['timeleft'] = $ilance->auction->auction_time_left_internal($rows, false);

					if ($rows['project_state'] == 'product') {

						if ($bids > 0 AND $currentbid > $startprice) {
							$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
						} else if ($bids > 0 AND $currentbid == $startprice) {
							$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
						} else {
							//$row['currentbid'] = $ilance->currency->format($startprice, $currencyid);
							// murugan changes on july 20
							$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
							$currentbid = $startprice;
						}

						// murugan changes on march 12
						$row['invoice_status'] = '';
						if ($rows['maxamount'] > 0) {
							$maxbid = $rows['maxamount'];
						} else {
							$maxbid = '';
						}
						if ($rows['bid_id'] > 0) {
							$highbidderid = $rows['winning_user_id'];
							if ($highbidderid == $_SESSION['ilancedata']['user']['userid']) {
								$row['currentbid'] .= "<br><span class=\"green\">You are currently winning</span>";
								$row['currentbid'] .= "<br><span class=\"green\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
							} else {
								$row['currentbid'] .= "<br><span class=\"red\">You were outbid</span>";
								$row['currentbid'] .= "<br><span class=\"red\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
							}
						} else {
							$row['currentbid'] .= ($rows['filtered_auctiontype'] == 'fixed') ? '<div class="smaller gray">Buy Now</div>' : '';
						}

					} else {
						$row['ajax_highbidnotify'] = '';
					}

					//sarath bid box in watchlist bug id 2797 start

					if ($rows['status'] == 'open') {
						$project_status = $phrase['_event_open_for_bids'];
						$row['ended'] = '1';
					} else {
						$project_status = print_auction_status($rows['status']);
						$timeleft = $phrase['_ended'];
						$row['ended'] = '0';
					}
					$rows['timeleft'] = $ilance->auction->auction_time_left_internal($rows, false);
					if ($rows['status'] == 'finished') {
						$row['ended'] = '0';
					} else if ($rows['status'] == 'expired') {
						$row['ended'] = '0';
					} else if ($rows['status'] != 'open' AND $rows['close_date'] != '0000-00-00 00:00:00') {
						$row['ended'] = '0';
					} else if ($rows['timeleft'] == 'Ended') {
						$row['ended'] = '0';
					}

					/*$bid_amount_val = $ilance->db->query("
					SELECT bidamount
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $rows['project_id'] . "'
					ORDER BY bidamount DESC, date_added ASC LIMIT 1
					", 0, null, __FILE__, __LINE__);

					$sql_bid_val = $ilance->db->fetch_array($bid_amount_val, DB_ASSOC);

					$show['increments'] = false;
					$increment = '';
					$cbid = ($sql_bid_val['bidamount'] > 0) ? $sql_bid_val['bidamount'] : 0;
					$incrementgroup = 'default';

					$sqlincrements = $ilance->db->query("
					SELECT amount
					FROM " . DB_PREFIX . "increments
					WHERE ((increment_from <= $cbid AND increment_to >= $cbid)
					OR (increment_from < $cbid AND increment_to < $cbid))
					AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
					ORDER BY amount DESC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sqlincrements) > 0) {
					$show['increments'] = true;

					// increment
					$resincrement = $ilance->db->fetch_array($sqlincrements, DB_ASSOC);

					}*/

					$resincrement['amount'] = $rows['increment'];

					$min_bidamount = sprintf("%.02f", '0.01');
					$min_bidamountformatted = $ilance->currency->format('0.01', $rows['currencyid']);
					$highestbid = 0;

					if ($rows['bids'] <= 0) {
						// do we have starting price?
						if ($rows['startprice'] > 0) {
							$min_bidamount = sprintf("%.02f", $rows['startprice']);
							$min_bidamountformatted = $ilance->currency->format($rows['startprice'], $rows['currencyid']);
						}
					} else if ($rows['bids'] > 0) {
						// highest bid amount placed for this auction
						//$highestbid = $ilance->bid->fetch_highest_bid($rows['project_id']);
						$highestbid = $rows['bid_amount'];

						// if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
						if (isset($resincrement['amount']) AND !empty($resincrement['amount']) AND $resincrement['amount'] > 0) {
							$min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
							$min_bidamountformatted = $ilance->currency->format(($highestbid + $resincrement['amount']), $rows['currencyid']);
						} else {
							$min_bidamount = sprintf("%.02f", $highestbid);
							$min_bidamountformatted = $ilance->currency->format($highestbid, $rows['currencyid']);
						}
					}

					if ($rows['bids'] > 0) {
						$show['currentbid'] = 1;

						// current bid amount display
						$currentbid = '<strong>' . $ilance->currency->format($resincrement['amount']) . '</strong>';
						$proxybit = '';

						$pbit = $rows['maxamount'];
						if ($pbit > 0) {

							if ($pbit >= $min_bidamount) {
								$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
								$min_bidamountformatted = $ilance->currency->format_no_text($min_bidamount, $rows['currencyid']);
							}
						}

					} else {
						$show['startprice'] = true;
					}

					if ($_SESSION['ilancedata']['user']['userid'] == $rows['project_user_id']) {
						$row['is_owner'] = '0';
						$row['buy_now'] = '1';
					} else {
						$row['is_owner'] = '1';
					

						$row['min_bidamountformatted'] = $min_bidamountformatted;
						$row['min_bidamount'] = $min_bidamount;
						$row['project_title'] = $rows['project_title'];

						$proxy_bidamount = substr($min_bidamountformatted, 1, 11);

						if ($rows['filtered_auctiontype'] == 'regular') {
							$row['bid_amount'] = '<input type="text" name="bid_amount[' . $rows['project_id'] . '][next_bid_amount]" tabindex="' . $c . '"  class="bid_amount" id="bid_amount_' . $c . '"  size="8"/>';

							$row['hidden_min_bid'] = '<input type="hidden" class="min_bid_amount" id="min_bid_amount_' . $c . '" name="bid_amount[' . $rows['project_id'] . '][min_bid_amount]" value="' . $min_bidamount . '"/>';

							$row['projectlist_id'] = '<input type="hidden" class="projectlist_id" id="projectlist_id_' . $c . '" name="bid_amount[' . $rows['project_id'] . '][projectlist_id]" value="' . $rows['project_id'] . '"/>';

							$row['error_icon'] = '<div id="bid_error_' . $c . '" style="color: red; font-size: 22px; height: 20px; width: 20px; display: none; margin-left: 103px;  ">*</div>';

							$row['buy_now'] = '1';
							$c++;
						} else {
							$row['bid_amount'] = '<div class="smaller gray">Buy Now</div>';
							if ($rows['buynow_qty'] >= 1) {
								$show['buynow_available'] = true;
								$qty_pulldown = '';
								$buynow_price = $ilance->currency->format($rows['buynow_price'], $rows['currencyid']);
								$buynow_price_plain = $rows['buynow_price'];
								$buynow_qty = intval($rows['buynow_qty']);
								$amount = $rows['buynow_price'];
								$maxqty = $rows['max_qty'];
								if ($maxqty == 1) {
									$qty_pulldown = '<input type="hidden" name="qty" value="1" />';
								} else {
									$show['multipleqty'] = true;
									$qty_pulldown = '<select name="qty" id="qty_dropdown_' . $rows['project_id'] . '" style="font-family: verdana" id="check_id"><optgroup label="' . $phrase['_qty'] . '">';
									for ($i = 1; $i <= $buynow_qty; $i++) {
										if ($i <= $maxqty) {
											$qty_pulldown .= '<option value="' . $i . '">Qty ' . $i . '</option>';
										}
									}
									$qty_pulldown .= '</optgroup></select>';
								}
							}
							$row['qty_pulldown'] = $qty_pulldown;

							$row['buy_now'] = '0';
						}

					//sarath bid box in watchlist bug id 2797 end
					}
					($apihook = $ilance->api('show_watchlist_options')) ? eval($apihook) : false;

					$row['class'] = ($active_row_count % 2) ? 'alt2' : 'alt1';
					$watchlist_rfp[] = $row;
					$active_row_count++;

				}

				$active_prevnext = print_pagnation($active_number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $active_counter, $active_scriptpage);

			} // Active Listings End

			//Ended Listings starts on 5 may

			if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ended') {

				$show['active_list'] = '0';
				$show['recently_ended'] = '0';
				$show['ended_list'] = '1';
				//pagnation
				$ended_scriptpage = 'watchlist.php?cmd=ended';

				$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

				$ilconfig['globalfilters_maxrowsdisplay'] = '25';

				$ended_counter = (intval($ilance->GPC['page']) - 1) * $ilconfig['globalfilters_maxrowsdisplay'];

				$ended_row_count = 0;
				$ended = $ilance->db->query("SELECT pr.maxamount,w.comment,p.project_details,p.filtered_auctiontype,p.bids,p.project_state,p.description,p.status,p.currencyid,p.startprice,p.currentprice,w.watching_project_id,p.project_id,p.project_title,p.date_end as ended_on,UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime ,(SELECT COUNT(attachid) AS picture_count FROM " . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,ca.filename as filehash,
											(select bid_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id and user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' order by bidamount desc,date_added asc limit 1) as bid_id,
											(select user_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id  order by bidamount desc,date_added asc limit 1) as winning_user_id
											FROM " . DB_PREFIX . "watchlist w
											left join  " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
											left join " . DB_PREFIX . "proxybid pr on pr.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' and pr.project_id = w.watching_project_id
											left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
											WHERE p.status = 'expired' and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'

											ORDER BY UNIX_TIMESTAMP(p.date_end)  DESC
											LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
					, 0, null, __FILE__, __LINE__);
				//total ended
				$ended_pagin = $ilance->db->query("SELECT w.watching_project_id,p.project_id,UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
											FROM " . DB_PREFIX . "watchlist w
											left join  " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
						                    WHERE p.status = 'expired' and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
											ORDER BY UNIX_TIMESTAMP(p.date_end) DESC
											", 0, null, __FILE__, __LINE__);

				$ended_number = (int) $ilance->db->num_rows($ended_pagin);

				if ($ilance->db->num_rows($ended) > 0) {
					while ($rows = $ilance->db->fetch_array($ended)) {
						if ($rows['bids'] == 0) {
							$row['bids'] = '-';
						} else {
							$row['bids'] = $rows['bids'] . ' ' . $phrase['_bids_lower'];
						}

						$row['ended_date'] = date('F d, Y', strtotime($rows['ended_on']));
						$row['filehash'] = $rows['filehash'];
						$row['picture_count'] = $rows['picture_count'];

						$row['action'] = '<input type="checkbox" name="project_id[]" value="' . $rows['project_id'] . '" />';
						$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						$row['comment'] = $rows['comment'];
						$pictures = $rows['picture_count'];
						$borderwidth = 0;
						$bordercolor = "#ffffff";
						if (!empty($rows['filehash'])) {
							$row['sample'] = '
								<div class="gallery-thumbs-cell">
								<div class="gallery-thumbs-entry">
										<div class="gallery-thumbs-main-entry">
												<div class="gallery-thumbs-wide-wrapper">
														<div class="gallery-thumbs-wide-inner-wrapper">
								<a href="' . $url . '"><img src="' . HTTPS_SERVER . 'image/72/96/' . $rows['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>
								<div class="gallery-thumbs-corner-text"><span>' . $pictures . ' photos</span></div>
														</div>
												</div>
										</div>
								</div>
								</div>';
						} else {
							$row['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
						}

						if ($rows['project_state'] == 'product') {
							$row['attach'] = '';
							$row['auctionpage'] = $ilpage['merch'];

							// display thumbnail
							if ($ilconfig['globalauctionsettings_seourls']) {
								$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);

								//$row['sample'] = print_item_photo($url, 'thumb', $rows['project_id']);
								$row['title'] = construct_seo_url('productauction', 0, $rows['project_id'], stripslashes($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							} else {
								//$row['sample'] = print_item_photo($ilpage['merch'] . '?id=' . $rows['project_id'], 'thumb', $rows['project_id']);
								$row['title'] = '<a href="' . $row['auctionpage'] . '?id=' . $rows['project_id'] . '">' . stripslashes($rows['project_title']) . '</a>';
							}
						}
						$row['watching_project_id'] = $rows['project_id'];
						$row['description'] = $ilance->bbcode->strip_bb_tags($rows['description']);
						$row['description'] = short_string($row['description'], 100);
						$row['description'] = handle_input_keywords($row['description']);
						$row['status'] = print_auction_status($rows['status']);

						// is bid placed?

						$row['bidplaced'] = ($rows['bid_id'] > 0)
						? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="' . $phrase['_you_have_placed_a_bid_on_this_auction'] . '" />'
						: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="' . $phrase['_place_a_bid'] . '" />';

						// is realtime auction?
						$row['realtime'] = ($rows['project_details'] == 'realtime')
						? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'realtime.gif" alt="' . $phrase['_realtime_auction'] . '" border="0" alt="" />'
						: '';

						// currency
						$currencyid = $rows['currencyid'];

						// bids
						$bids = $rows['bids'];

						// starting price
						$startprice = $rows['startprice'];

						// current bid
						$currentbid = $rows['currentprice'];

						$row['timeleft'] = $ilance->auction->auction_time_left_internal($rows, false);

						if ($rows['project_state'] == 'product') {
							if ($bids > 0 AND $currentbid > $startprice) {
								$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
							} else if ($bids > 0 AND $currentbid == $startprice) {
								$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
							} else {
								$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
								$currentbid = $startprice;
							}
							// murugan changes on march 12
							$row['invoice_status'] = '';
							if ($rows['maxamount'] > 0) {
								$maxbid = $rows['maxamount'];
							} else {
								$maxbid = '';
							}
							if ($rows['bid_id'] > 0) {
								$highbidderid = $rows['winning_user_id'];
								if ($highbidderid == $_SESSION['ilancedata']['user']['userid']) {

									$invoice_status = $ilance->db->query("
										SELECT *,date(paiddate) as date_paid
										FROM " . DB_PREFIX . "invoices
										WHERE projectid = '" . $rows['project_id'] . "'
										AND user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
										AND isbuyerfee !='1'
										AND isfvf !='1'
										AND isenhancementfee !='1'

									", 0, null, __FILE__, __LINE__);
									$invoice_info = $ilance->db->fetch_array($invoice_status);

									if ($invoice_info['status'] == 'paid') {
										$row['invoice_status'] = 'Paid ' . $invoice_info['date_paid'] . ' - Thank you!';
									} else if ($invoice_info['status'] == 'unpaid') {
										$row['invoice_status'] = '<a href = "buyer_invoice.php">Click to Pay Invoice </a> ';
									} else {
										$row['invoice_status'] = 'Payment Pending';
									}

									$row['currentbid'] .= "<br><span class=\"green\">You won this item</span>";
									$row['currentbid'] .= "<br><span class=\"green\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
								} else {
									$row['currentbid'] .= "<br><span class=\"red\">You were outbid</span>";
									$row['currentbid'] .= "<br><span class=\"red\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
								}
							} else {
								$row['currentbid'] .= ($rows['filtered_auctiontype'] == 'fixed') ? '<div class="smaller gray">Buy Now</div>' : '';
							}

						} else {
							$row['ajax_highbidnotify'] = '';
						}
						($apihook = $ilance->api('show_watchlist_options')) ? eval($apihook) : false;
						$row['class'] = ($ended_row_count % 2) ? 'alt2' : 'alt1';
						$watchlist_rfp1[] = $row;
						$ended_row_count++;
					}

					$ended_prevnext = print_pagnation($ended_number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $ended_counter, $ended_scriptpage);

				}

				if ($ended_row_count <= 0) {
					$show['no_watchlist_ended'] = true;
				} else {
					$show['no_watchlist_ended'] = false;
				}

				$tab = '0';
				if (isset($ilance->GPC['tab'])) {
					$tab = intval($ilance->GPC['tab']);
				}

				$pprint_array = array('enablebatchbidding','tab', 'input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'ended_prevnext');

				$ilance->template->fetch('main', 'watchlist.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array('watchlist_rfp1'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}

			//Ended Listings starts on 5 may

			if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'recently_ended') {

				$show['active_list'] = '0';
				$show['ended_list'] = '0';
				$show['recently_ended'] = '1';
				//pagnation
				$recently_ended_scriptpage = 'watchlist.php?cmd=recently_ended';

				$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

				$ilconfig['globalfilters_maxrowsdisplay'] = '25';

				$recently_ended_counter = (intval($ilance->GPC['page']) - 1) * $ilconfig['globalfilters_maxrowsdisplay'];

				$recently_ended_row_count = 0;

				$recently_ended_sql = $ilance->db->query("SELECT pr.maxamount,w.comment,p.project_details,p.filtered_auctiontype,p.bids,p.project_state,p.description,p.status,p.currencyid,p.startprice,p.currentprice,b.bid_id,w.watching_project_id,p.project_id,p.project_title,p.date_end as ended_on, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime ,(SELECT COUNT(attachid) AS picture_count FROM " . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,ca.filename as filehash
											, (select user_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id  order by bidamount desc,date_added asc limit 1) as winning_user_id
											FROM " . DB_PREFIX . "watchlist w
											left join  " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
											left join " . DB_PREFIX . "project_bids b on b.project_id = p.project_id and b.user_id=w.user_id
											left join " . DB_PREFIX . "proxybid pr on pr.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' and pr.project_id = w.watching_project_id
											left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
											WHERE p.status = 'expired' and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
											and p.date_end > now()-INTERVAL 6 DAY
											ORDER BY UNIX_TIMESTAMP(p.date_end) DESC
											LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
					, 0, null, __FILE__, __LINE__);
				//total ended
				$recently_ended_pagin = $ilance->db->query("SELECT w.watching_project_id,p.project_id, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
											FROM " . DB_PREFIX . "watchlist w
											left join  " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
						                    WHERE p.status = 'expired' and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
						                    and p.date_end > now()-INTERVAL 6 DAY
											ORDER BY UNIX_TIMESTAMP(p.date_end) DESC
											", 0, null, __FILE__, __LINE__);

				$recently_ended_number = (int) $ilance->db->num_rows($recently_ended_pagin);

				if ($ilance->db->num_rows($recently_ended_sql) > 0) {
					while ($rows = $ilance->db->fetch_array($recently_ended_sql)) {
						if ($rows['bids'] == 0) {
							$row['bids'] = '-';
						} else {
							$row['bids'] = $rows['bids'] . ' ' . $phrase['_bids_lower'];
						}

						$row['ended_date'] = date('F d, Y', strtotime($rows['ended_on']));
						$row['filehash'] = $rows['filehash'];
						$row['picture_count'] = $rows['picture_count'];

						$row['action'] = '<input type="checkbox" name="project_id[]" value="' . $rows['project_id'] . '" />';
						$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						$row['comment'] = $rows['comment'];
						$pictures = $rows['picture_count'];
						$borderwidth = 0;
						$bordercolor = "#ffffff";
						if (!empty($rows['filehash'])) {
							$row['sample'] = '
								<div class="gallery-thumbs-cell">
								<div class="gallery-thumbs-entry">
										<div class="gallery-thumbs-main-entry">
												<div class="gallery-thumbs-wide-wrapper">
														<div class="gallery-thumbs-wide-inner-wrapper">
								<a href="' . $url . '"><img src="' . HTTPS_SERVER . 'image/72/96/' . $rows['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>
								<div class="gallery-thumbs-corner-text"><span>' . $pictures . ' photos</span></div>
														</div>
												</div>
										</div>
								</div>
								</div>';
						} else {
							$row['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
						}

						if ($rows['project_state'] == 'product') {
							$row['attach'] = '';
							$row['auctionpage'] = $ilpage['merch'];

							// display thumbnail
							if ($ilconfig['globalauctionsettings_seourls']) {
								$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);

								//$row['sample'] = print_item_photo($url, 'thumb', $rows['project_id']);
								$row['title'] = construct_seo_url('productauction', 0, $rows['project_id'], stripslashes($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							} else {
								//$row['sample'] = print_item_photo($ilpage['merch'] . '?id=' . $rows['project_id'], 'thumb', $rows['project_id']);
								$row['title'] = '<a href="' . $row['auctionpage'] . '?id=' . $rows['project_id'] . '">' . stripslashes($rows['project_title']) . '</a>';
							}
						}
						$row['watching_project_id'] = $rows['project_id'];
						$row['description'] = $ilance->bbcode->strip_bb_tags($rows['description']);
						$row['description'] = short_string($row['description'], 100);
						$row['description'] = handle_input_keywords($row['description']);
						$row['status'] = print_auction_status($rows['status']);

						// is bid placed?

						$row['bidplaced'] = ($rows['bid_id'] > 0)
						? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="' . $phrase['_you_have_placed_a_bid_on_this_auction'] . '" />'
						: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="' . $phrase['_place_a_bid'] . '" />';

						// is realtime auction?
						$row['realtime'] = ($rows['project_details'] == 'realtime')
						? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'realtime.gif" alt="' . $phrase['_realtime_auction'] . '" border="0" alt="" />'
						: '';

						// currency
						$currencyid = $rows['currencyid'];

						// bids
						$bids = $rows['bids'];

						// starting price
						$startprice = $rows['startprice'];

						// current bid
						$currentbid = $rows['currentprice'];

						$row['timeleft'] = $ilance->auction->auction_time_left_internal($rows, false);

						if ($rows['project_state'] == 'product') {
							if ($bids > 0 AND $currentbid > $startprice) {
								$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
							} else if ($bids > 0 AND $currentbid == $startprice) {
								$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
							} else {
								$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
								$currentbid = $startprice;
							}
							// murugan changes on march 12
							$row['invoice_status'] = '';
							if ($rows['maxamount'] > 0) {
								$maxbid = $rows['maxamount'];
							} else {
								$maxbid = '';
							}
							if ($rows['bid_id'] > 0) {
								$highbidderid = $rows['winning_user_id'];
								if ($highbidderid == $_SESSION['ilancedata']['user']['userid']) {

									$invoice_status = $ilance->db->query("
										SELECT *,date(paiddate) as date_paid
										FROM " . DB_PREFIX . "invoices
										WHERE projectid = '" . $rows['project_id'] . "'
										AND user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
										AND isbuyerfee !='1'
										AND isfvf !='1'
										AND isenhancementfee !='1'

									", 0, null, __FILE__, __LINE__);
									$invoice_info = $ilance->db->fetch_array($invoice_status);

									if ($invoice_info['status'] == 'paid') {
										$row['invoice_status'] = 'Paid ' . $invoice_info['date_paid'] . ' - Thank you!';
									} else if ($invoice_info['status'] == 'unpaid') {
										$row['invoice_status'] = '<a href = "buyer_invoice.php">Click to Pay Invoice </a> ';
									} else {
										$row['invoice_status'] = 'Payment Pending';
									}

									$row['currentbid'] .= "<br><span class=\"green\">You won this item</span>";
									$row['currentbid'] .= "<br><span class=\"green\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
								} else {
									$row['currentbid'] .= "<br><span class=\"red\">You were outbid</span>";
									$row['currentbid'] .= "<br><span class=\"red\">Your Secret Maximum Bid: US$" . $maxbid . "</span>";
								}
							} else {
								$row['currentbid'] .= ($rows['filtered_auctiontype'] == 'fixed') ? '<div class="smaller gray">Buy Now</div>' : '';
							}

						} else {
							$row['ajax_highbidnotify'] = '';
						}
						($apihook = $ilance->api('show_watchlist_options')) ? eval($apihook) : false;
						$row['class'] = ($recently_ended_row_count % 2) ? 'alt2' : 'alt1';
						$watchlist_rfp2[] = $row;
						$recently_ended_row_count++;
					}

					$recently_ended_prevnext = print_pagnation($recently_ended_number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $recently_ended_counter, $recently_ended_scriptpage);

				}

				if ($recently_ended_row_count <= 0) {
					$show['no_watchlist_recently_ended'] = true;
				} else {
					$show['no_watchlist_recently_ended'] = false;
		
				}
				$tab = '0';
				if (isset($ilance->GPC['tab'])) {
					$tab = intval($ilance->GPC['tab']);
				}

				$pprint_array = array('enablebatchbidding','tab', 'input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'recently_ended_prevnext');

				$ilance->template->fetch('main', 'watchlist.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array('watchlist_rfp2'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}

			if ($active_row_count <= 0) {
				$show['no_watchlist_active'] = true;
			} else {
				$show['no_watchlist_active'] = false;
			}

			$tab = '0';
			if (isset($ilance->GPC['tab'])) {
				$tab = intval($ilance->GPC['tab']);
			}

			$pprint_array = array('enablebatchbidding','buy_now','ended','is_owner','tab', 'input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'active_prevnext');

			$ilance->template->fetch('main', 'watchlist.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('watchlist_rfp'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	} else {
		$area_title = $phrase['_access_denied_to_watchlist_resource'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_watchlist_resource'];

		// construct breadcrumb trail
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
		$navcrumb["$ilpage[watchlist]"] = $phrase['_watchlist'];
		$navcrumb[""] = $phrase['_access_denied_to_watchlist_resource'];

		print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . " <span class=\"blue\"><a href='" . $ilpage['subscription'] . "'><strong>" . $phrase['_click_here'] . "</strong></a></span>.<div style=\"padding-top:9px\" class=\"gray\">" . $phrase['_additionally_you_may_be_seeing_this_message_due_to_an_unpaid'] . "</div>", $ilpage['subscription'], ucwords($phrase['_subscription_manager']), fetch_permission_name('addtowatchlist'));
		exit();
	}
} else {
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['watchlist'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
