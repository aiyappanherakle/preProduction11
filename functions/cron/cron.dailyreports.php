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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

($apihook = $ilance->api('cron_dailyreports_start')) ? eval($apihook) : false;

$overall_subscription_earnings = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(paid) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE paid != ''
            AND invoicetype = 'subscription'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $overall_subscription_earnings = number_format($res['paid'], 2);
}

// #### OVERALL COMMISSION SALES ###############################################
$overall_commission_earnings = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(paid) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE paid != ''
            AND invoicetype = 'commission'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $overall_commission_earnings = number_format($res['paid'], 2);
}

// #### OVERALL PROVIDER SALES #################################################
$overall_provider_sales_earnings = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(paid) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE paid != ''
            AND (invoicetype = 'p2b' OR invoicetype = 'buynow')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $overall_provider_sales_earnings = number_format($res['paid'], 2);
}

// #### SUBSCRIPTION FEES PENDING ##############################################
$overall_provider_subscriptions_pending = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE paid = ''
            AND invoicetype = 'subscription'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $overall_provider_subscriptions_pending = number_format($res['paid'], 2);
}

// #### COMMISSION FEES PENDING ################################################
$commission_payments_pending_total = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE paid = ''
            AND invoicetype = 'commission'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $commission_payments_pending_total = number_format($res['paid'], 2);
}

// #### PENDING PAYMENTS SELLERS ARE WAITING ON ################################
$provider_sales_pending_total_count = '0';
$provider_sales_pending_total = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS amount
        FROM " . DB_PREFIX . "invoices
        WHERE paid = ''
            AND (invoicetype = 'p2b' OR invoicetype = 'buynow')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $provider_sales_pending_total_count = $ilance->db->num_rows($sql);
        $provider_sales_pending_total = number_format($res['amount'], 2);
}

// #### TOTAL MEMBER COUNT #####################################################
$member_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "users
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $member_count = $ilance->db->num_rows($sql);
}

// #### NEW REGISTRATIONS ######################################################
$member_registrations_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "users
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $member_registrations_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF SERVICE PROJECTS POSTED ######################################
$projects_posted_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "projects
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
            AND project_state = 'service'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $projects_posted_today_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF PRODUCT AUCTIONS POSTED ######################################
$products_posted_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "projects
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
            AND project_state = 'product'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $products_posted_today_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF BIDS #########################################################
$project_bids_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "project_bids
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $project_bids_today_count = $ilance->db->num_rows($sql);
}

// #### COMMISSION FEE COUNT ###################################################
$number_commission_fees_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%')
            AND invoicetype = 'commission'
");
if ($ilance->db->num_rows($sql) > 0)
{
        $number_commission_fees_today_count = $ilance->db->num_rows($sql);
}

// #### COMMISSION FEES PAID TODAY #############################################
$amount_paid_commission_fees_today_count_amount = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'commission' 
            AND status = 'paid'
");
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $amount_paid_commission_fees_today_count_amount = number_format($res['paid'], 2);
}

// #### CREDENTIAL PAYMENT COUNT ###############################################
$number_credential_fees_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'credential'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_credential_fees_today_count = $ilance->db->num_rows($sql);
}

// #### CREDENTIAL FEES PAID TODAY #############################################
$amount_paid_credential_fees_today_count_amount = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'credential' 
            AND status = 'paid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $amount_paid_credential_fees_today_count_amount = number_format($res['paid'], 2);
}

// #### SUBSCRIPTION PAYMENT COUNT #############################################
$number_paid_subscription_fees_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'subscription' 
            AND status = 'paid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_paid_subscription_fees_today_count = $ilance->db->num_rows($sql);
}

// #### SUBSCRIPTION PAYMENTS TODAY ############################################
$amount_paid_subscription_fees_today_count_amount = number_format("0", 2);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS amount
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'subscription' 
            AND status = 'paid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql);
        $amount_paid_subscription_fees_today_count_amount = number_format($res['amount'], 2);
}

// #### FAILED LOGINS ##########################################################
$number_of_failed_logins_today_count = '0';
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "failed_logins
        WHERE datetime_failed LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_failed_logins_today_count = $ilance->db->num_rows($sql);
}

		################################################################
		######  Create Advance Payment For Users 			############
		######  Herakle Murugan Coding Oct 29 Starts Here 	############
		######  Cron Job For Payment of Advance to users	############
		################################################################
		// Here Changes On insert_transaction Nov 24 credit to advance
		$sqladvance = $ilance->db->query("
						SELECT * 
						FROM " . DB_PREFIX . "user_advance
						WHERE date_made LIKE ('%".DATETODAY."%')
						AND statusnow = 'unpaid'
						");
		if($ilance->db->num_rows($sqladvance) > 0)
		{
			while($resadvance = $ilance->db->fetch_array($sqladvance))
			{
			   $sql = $ilance->db->query("
									SELECT available_balance, total_balance
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . intval($resadvance['user_id']) . "'
							");
			   if ($ilance->db->num_rows($sql) > 0)
					{
							$res = $ilance->db->fetch_array($sql);
							$new_credit_amount = $resadvance['amount'];
							
							$total_now = $res['total_balance'];
							$avail_now = $res['available_balance'];
							
							$new_total_now = ($total_now + $new_credit_amount);
							$new_avail_now = ($avail_now + $new_credit_amount);
							$ilance->GPC['custom'] = 'Advance Payment For User';
							$ilance->db->query("
									UPDATE " . DB_PREFIX . "users
									SET total_balance = '" . $new_total_now . "',
									available_balance = '" . $new_avail_now . "'
									WHERE user_id = '" . intval($resadvance['user_id']) . "'");	
							$ilance->db->query("
									UPDATE " . DB_PREFIX . "user_advance
									SET statusnow = 'paid'											
									WHERE id = '" . $resadvance['id'] . "'");	
			
							$ilance->accounting = construct_object('api.accounting');
							$ilance->accounting->insert_transaction(
									0,
									0,
									0,
									intval($resadvance['user_id']),
									0,
									0,
									0,
									$resadvance['description'],
									sprintf("%01.2f", $new_credit_amount),
									sprintf("%01.2f", $new_credit_amount),
									'paid',
									'advance',
									'account',
									DATETIME24H,
									DATEINVOICEDUE,
									DATETIME24H,
									$ilance->GPC['custom'],
									0,
									0,
									0
							);
				
							$sqlemail = $ilance->db->query("
									SELECT email, username, first_name, last_name
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . intval($resadvance['user_id']) . "'
							");
							if ($ilance->db->num_rows($sqlemail) > 0)
							{
									$resemail = $ilance->db->fetch_array($sqlemail);
									
									$ilance->email = construct_dm_object('email', $ilance);
			
									$ilance->email->mail = $resemail['email'];
									$ilance->email->slng = fetch_user_slng(intval($resadvance['user_id']));
									
									$ilance->email->get('account_credit_notification');		
									$ilance->email->set(array(
											'{{customer}}' => $resemail['username'],
											'{{amount}}' => $ilance->currency->format($resadvance['amount']),
									));
									
									$ilance->email->send();
									
									$ilance->email->mail = SITE_EMAIL;
									$ilance->email->slng = fetch_site_slng();
									
									$ilance->email->get('account_credit_notification_admin');		
									$ilance->email->set(array(
											'{{customer}}' => $resemail['username'],
											'{{amount}}' => $ilance->currency->format($resadvance['amount']),
									));
									
									$ilance->email->send();									
									
							}
					}
			}
		}
		
		// Date On Dec 23 For Invoice Statement
		// murugan changes on jan 27 for 7 day and 14 days
		$sqltest = $ilance->db->query("
        SELECT user_id
        FROM " . DB_PREFIX . "invoices
        WHERE status = 'unpaid'
		AND (date(createdate)  = '".SIXDAYSAGO."' 
		OR  date(createdate)  = '".FIFETEENDAYSAGO."')       
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqltest) > 0)
		{
        	while($resinv = $ilance->db->fetch_array($sqltest))
			{
			   $sqlamt = $ilance->db->query("
        			SELECT SUM(amount) AS amount
        			FROM " . DB_PREFIX . "invoices
       				WHERE status = 'unpaid'
					AND (date(createdate)  = '".SIXDAYSAGO."' 
						OR  date(createdate)  = '".FIFETEENDAYSAGO."')
					AND user_id = '".$resinv['user_id']."'            
					", 0, null, __FILE__, __LINE__);
					
					$resamt = $ilance->db->fetch_array($sqlamt);
									
					$ilance->email = construct_dm_object('email', $ilance);

					$ilance->email->mail = fetch_user('email',$resinv['user_id']);
					$ilance->email->slng = fetch_user_slng(intval($resinv['user_id']));
					
					$ilance->email->get('unpaid_invoice_notification');		
					$ilance->email->set(array(
							'{{customer}}' =>fetch_user('username',$resinv['user_id']),
							'{{amount}}' => $ilance->currency->format($resamt['amount']),
					));
					
					//$ilance->email->send();
			}
			
		}
		
		// Date On Dec 23 For Recommedation Items for user
		
		$selbid = $ilance->db->query("
				SELECT pb.user_id,pb.project_id,pjt.cid FROM " . DB_PREFIX . "project_bids pb, " . DB_PREFIX . "projects pjt
				WHERE pb.bidstatus != 'awarded'
				AND pjt.status = 'closed'
				AND pb.project_id = pjt.project_id
				AND date(pb.date_awarded) = '".DATETODAY."'
				  ");
		if ($ilance->db->num_rows($selbid) > 0)
		{
        	while($resbid = $ilance->db->fetch_array($selbid))
			{
			
			//ensure that the user have enabled Email preference for Recommedation
			
			$email_notify = fetch_user('emailnotify',$resbid['user_id']);
			
			$query_recommendations = $ilance->db->query("
			                            SELECT recommend FROM " . DB_PREFIX . "email_preference 
						                WHERE user_id ='".$resbid['user_id']."'");
						
			$row_recommendations = $ilance->db->fetch_array($query_recommendations);							
				 
			if( $row_recommendations['recommend'] == '1' AND $email_notify =='1')
			{
				$selectpjt = $ilance->db->query("
				SELECT * FROM " . DB_PREFIX . "projects
				WHERE cid = '".$resbid['cid']."'
				AND user_id != '".$resbid['user_id']."'
				AND date(date_added) = '".DATETODAY."'
				AND status = 'open'
				");
				
				if ($ilance->db->num_rows($selectpjt) > 0)
				{
					$respjt = $ilance->db->fetch_array($selectpjt);
									
					$ilance->email = construct_dm_object('email', $ilance);

					$ilance->email->mail = fetch_user('email',$resbid['user_id']);
					$ilance->email->slng = fetch_user_slng(intval($resbid['user_id']));
					
					$ilance->email->get('recommendation_notification');		
					$ilance->email->set(array(
							'{{customer}}' =>fetch_user('username',$resbid['user_id']),
							'{{project_title}}' => $respjt['project_title'],
							
					));
					
					$ilance->email->send();
				}
				
			  }
			  
			}
		}

		################################################################
		######  Create Advance Payment For Users 			############
		######  Herakle Murugan Coding Oct 29 Ends Here 	############
		######  Cron Job For Payment of Advance to users	############
		################################################################

// cron logic to ensure daily reports only send once per day
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "emaillog
        WHERE user_id = '-1'
            AND logtype = 'dailyreport'
            AND date LIKE '%" . DATETODAY . "%'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) == 0)
{
        $ilance->email = construct_dm_object('email', $ilance);
                                                        
        ($apihook = $ilance->api('cron_dailyreports_start')) ? eval($apihook) : false;                                                                
                                                        
        // email admin
        $ilance->email->logtype = 'dailyreport';
        $ilance->email->mail = SITE_EMAIL;
        $ilance->email->slng = fetch_site_slng();
        
        $ilance->email->get('cron_daily_reports');		
        $ilance->email->set(array(
                '{{membercount}}' => (int)$member_count,
                '{{memberregistercount}}' => (int)$member_registrations_count,
                '{{subscriptionpaymentstoday}}' => (int)$number_paid_subscription_fees_today_count,
                '{{subscriptionpaymentsamounttoday}}' => $amount_paid_subscription_fees_today_count_amount,
                '{{credentialpaymentstoday}}' => (int)$number_credential_fees_today_count,
                '{{credentialpaymentsamounttoday}}' => $amount_paid_credential_fees_today_count_amount,
                '{{commissionfeestoday}}' => (int)$number_commission_fees_today_count,
                '{{commissionpaymentsamounttoday}}' => $amount_paid_commission_fees_today_count_amount,
                '{{servicespostedtoday}}' => (int)$projects_posted_today_count,
                '{{productspostedtoday}}' => (int)$products_posted_today_count,
                '{{servicebidstoday}}' => (int)$project_bids_today_count,
                '{{failedlogincount}}' => (int)$number_of_failed_logins_today_count,
        ));
        
        $ilance->email->send();
        
        ($apihook = $ilance->api('cron_dailyreports_end')) ? eval($apihook) : false;
        
        log_cron_action('The daily marketplace report was successfully emailed to ' . SITE_EMAIL, $nextitem);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>