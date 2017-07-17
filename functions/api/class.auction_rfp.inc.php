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

if (!class_exists('auction'))
{
	exit;
}

/**
* Class to handle inserting new product or service auctions within the database.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class auction_rfp extends auction
{
        /**
        * Function to create a new randomly generated auction id number.
        *
        * @return      integer       auction id number
        */
        function construct_new_auctionid()
        {
                global $ilance, $myapi, $ilconfig;
                
                $rfpid = rand(1, 9) . mb_substr(time(), -7, 10);
                
                $sql = $ilance->db->query("
                        SELECT project_id
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($rfpid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $rfpid = rand(1, 9) . mb_substr(time(), -6, 10);
                        
                        $sql = $ilance->db->query("
                                SELECT project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . intval($rfpid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $rfpid = rand(1, 9) . mb_substr(time(), -8, 10);
                                
                                $sql = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects
                                        WHERE project_id = '" . intval($rfpid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $rfpid = rand(1, 9) . mb_substr(time(), -8, 10);
                                        return $rfpid;
                                }
                                else
                                {
                                        return $rfpid;
                                }
                        }
                        else
                        {
                                return $rfpid;
                        }
                }
                else
                {
                        return $rfpid;
                }
        }
        
        /**
        * Function to create a new randomly generated auction id number for bulk upload.
        *
        * @return      integer       auction id number
        */
        function construct_new_auctionid_bulk()
        {
                global $ilance, $myapi, $ilconfig;
                
                do 
                {
                		$rfpid = mt_rand(1, 999) . substr(microtime(), 2,5);
                		$sql = $ilance->db->query("
		                       	SELECT project_id
		                       	FROM " . DB_PREFIX . "projects
		                       	WHERE project_id = '" . intval($rfpid) . "'
		                       	LIMIT 1
                               	", 0, null, __FILE__, __LINE__);
                }
                while($ilance->db->num_rows($sql) > 0);  
               
                return $rfpid;     
        } 
        
        /**
        * Function to insert foto from bulk upload
        *
        * @return      nothing
        */
        function upload_bulk_foto_auction()
        {
		global $ilance;
		
		$a = $b = $c = $d = $e = 0;
		
		$sql = $ilance->db->query("
			SELECT rfpid, sample, id, user_id
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE sample_uploaded = '0'
				AND correct = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (!empty($res['rfpid']) AND $res['rfpid'] != '0')
				{
					if (is_valid_project_id($res['rfpid']))
					{
						//if (!empty($res['sample']) AND file_exists($res['sample']))
						if (!empty($res['sample']))
						{
							$cid = fetch_auction('cid', $res['rfpid']);
							
							$sql1 = $ilance->db->query("
								SELECT attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $res['rfpid'] . "'
									AND category_id = '" . intval($cid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql1) == 0)
							{	
								if ($this->handle_remote_image_url_bulk($res['sample'], $res['rfpid'], $res['user_id'], $cid, 'itemphoto'))
								{
									$ilance->db->query("
										DELETE FROM " . DB_PREFIX . "bulk_tmp
										WHERE id = '" . $res['id'] . "'
									", 0, null, __FILE__, __LINE__);
									
									$a++;
								}
							}
						}
						else 
						{
							/*$ilance->db->query("
								DELETE FROM " . DB_PREFIX . "bulk_tmp
								WHERE id = '" . $res['id'] . "'
							", 0, null, __FILE__, __LINE__);*/
							
							$b++;
						}
					}
					else 
					{
						/*$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "bulk_tmp
							WHERE id = '" . $res['id'] . "'
						", 0, null, __FILE__, __LINE__);*/
						
						$c++;
					}
				}
				else 
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "bulk_tmp
						WHERE id = '" . $res['id'] . "'
					", 0, null, __FILE__, __LINE__);
					
					$d++;
				}
			}
		}
		else 
		{
			$sql2 = $ilance->db->query("
				SELECT rfpid, sample, id, user_id
				FROM " . DB_PREFIX . "bulk_tmp
				WHERE correct = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
				{
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "bulk_tmp WHERE id = '" . $res['id'] . "'", 0, null, __FILE__, __LINE__);
					$e++;
				}
			}
			
		}
		
		return "added ".$a." photos, 
		deleted ".$b." auctions with empty sample or valid url to sample, 
		deleted ".$c." valid auctions, 
		deleted ".$d." zombie auctions, 
		deleted ".$e." auctions from incomplet bulkupload";
        }
        
        /**
        * Function for handling a remote image url through a bulk file upload..
        * This will fetch the file from the remote server via curl, check if the file is valid (is an image),
        * extract it's contents and save to the filepath or the database.
        *
        * @param          string       image url (full url with image, ie: http://www.domain.com/image1.jpg
        * @param          integer      project id we're assigning this image to
        * @param          integer      category id
        *
        * @return         boolen
        */
        function handle_remote_image_url_bulk($imgurl = '', $project_id = 0, $user_id, $cid = 0, $attachtype = 'itemphoto')
        {
                global $ilance, $ilconfig;
                
                $upload_file_size = 0;
                $exif = $filedata = $data = $newfilename = $filename = $filehash = $filetype = '';
                $sql = false;
                $uploaded = 0;
                
		// #### parse the image on the remote server ###################
                $data = save_url_image($imgurl);
                if (!empty($data) AND is_array($data))
                {
                        $newfilename = $data['fullpath'];
                        $filename = $data['filename'];
                        $filehash = $data['filehash'];
                        $filetype = $data['filetype'];
                }
                
                if (!empty($newfilename) AND !empty($filename))
                {
                        // #### fetch exif information (extended image support)
                        if (function_exists('exif_read_data'))
                        {
                                $exifdata = @exif_read_data($newfilename, 'EXIF');
                                if (!empty($exifdata))
                                {
                                        $exif = addslashes(serialize($exifdata));
                                        unset($exifdata);
                                }
                        }
			
                        $upload_file_size = filesize($newfilename);
			
			// #### determine if we're using the database to save
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                // if we are using the database, put file data in temp space and remove actual file from server
                                $filedata = addslashes(fread(fopen($newfilename, 'rb'), filesize($newfilename)));
                                @unlink($newfilename);
                        }
                        
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, project_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress)
                                VALUES(
                                NULL,
                                '" . $ilance->db->escape_string($attachtype) . "',
                                '" . $user_id . "',
                                '" . intval($project_id) . "',
                                '" . intval($cid) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($filename) . "',
                                '" . $filedata . "',
                                '" . $ilance->db->escape_string($filetype) . "',
                                '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                '0',
                                '" . $ilance->db->escape_string($upload_file_size) . "',
                                '" . $ilance->db->escape_string($filehash) . "',
                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "')
                        ", 0, null, __FILE__, __LINE__);
                		
                        $sql = $ilance->db->query("
				SELECT attachid
				FROM " . DB_PREFIX . "attachment
				WHERE filehash = '" . $ilance->db->escape_string($filehash) . "'
			", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                        	$uploaded = 1;
                        }
                }
		
                return $uploaded;
        }
        
        /**
        * Function to insert a new service auction
        *
        * @param       integer       user id
        * @param       string        project type (reverse/forward)
        * @param       string        status (open/draft)
        * @param       string        auction state (service/product)
        * @param       integer       category id
        * @param       integer       rfp id (custom auction id)
        * @param       string        auction title
        * @param       string        auction description
        * @param       string        auction description video embed
        * @param       string        auction additional info
        * @param       string        auction keywords/tags
        * @param       array         array holding custom questions answered
        * @param       array         array holding profile answers
        * @param       bool          filter option: is bidding privacy enabled
        * @param       string        filter answer: bidding privacy filter answer
        * @param       bool          filter option: is budget filter is enalbed
        * @param       string        filter answer: budget filter answer
        * @param       string        filtered auction type
        * @param       bool          filter option: is escrow being used for this project?
        * @param       bool          filter option: are direct gateway payments being used for this project? (default 0)
        * @param       bool          filter option: are offline payment methods being used for this project?
        * @param       string        payment method defined by the project buyer
        * @param       string        payment method gateway selected by the project buyer
        * @param       string        payment method gateway email address entered by the project buyer
        * @param       string        project details (public, realtime, invite_only)
        * @param       string        bid details
        * @param       array         invitation list (external emailed users)
        * @param       string        invitation message
        * @param       array         invited registered members array
        * @param       integer       year (used for start of year for realtime auction only)
        * @param       integer       month (used for start of month for realtime auction only)
        * @param       integer       day (used for start of day for realtime auction only)
        * @param       integer       hour (used for start of hour for realtime auction only)
        * @param       integer       min (used for start of min for realtime auction only)
        * @param       integer       sec (used for start of sec for realtime auction only)
        * @param       integer       duration (1 - 30) usually handling (days, hours or minutes) answer
        * @param       string        duration unit (D, H, M)
        * @param       integer       filtered rating answer
        * @param       string        filtered country answer
        * @param       string        filtered state answer
        * @param       string        filtered city answer
        * @param       string        filtered zip code answer
        * @param       integer       filtered rating by answer
        * @param       bool          filter by country?
        * @param       bool          filter by state?
        * @param       bool          filter by city?
        * @param       bool          filter by zip code?
        * @param       bool          filter by underage disabled?
        * @param       bool          filter by business number requirement?
        * @param       bool          filter public board enabled?
        * @param       array         auction upsell enhancements array
        * @param       bool          saving auction in draft mode? (default no)
        * @param       string        service location city
        * @param       string        service location state/province
        * @param       string        service location zip or postal code
        * @param       string        service location country 
        * @param       bool          skip all email process? (default no) - useful to use this function as API and to not send 1000's of emails if 1000's of auctions are added.
        * @param       mixed         api custom hooks
        * @param       bool          is bulk upload? (default false)
        * @param       integer       currency id
        *
        * @return      nothing
        */
        function insert_service_auction($userid = 0, $project_type = 'reverse', $status = 'open', $project_state = 'service', $cid = 0, $rfpid = 0, $project_title = '', $description = '', $description_videourl = '', $additional_info = '', $keywords = '', $custom = array(), $profileanswer = array(), $filter_bidtype, $filtered_bidtype, $filter_budget, $filtered_budgetid, $filtered_auctiontype, $filter_escrow, $filter_gateway = 0, $filter_offline = 0, $paymethod = array(), $paymethodoptions = array(), $paymethodoptionsemail = array(), $project_details, $bid_details, $invitelist, $invitemessage, $invitedmember, $year, $month, $day, $hour, $min, $sec, $duration, $duration_unit, $filtered_rating = 1, $filtered_country = '', $filtered_state = '', $filtered_city = '', $filtered_zip = '', $filter_rating, $filter_country, $filter_state, $filter_city, $filter_zip, $filter_underage, $filter_businessnumber, $filter_publicboard = 0, $enhancements = array(), $saveasdraft = 0, $city = '', $state = '', $zipcode = '', $country = '', $skipemailprocess = 0, $apihookcustom = array(), $isbulkupload = false, $currencyid = 0)
        {
                global $ilance, $myapi, $ilconfig, $ilpage, $phrase, $url, $area_title, $page_title;
                
                $ilance->auction = construct_object('api.auction');
                $ilance->auction_post = construct_object('api.auction_post');
                $ilance->email = construct_dm_object('email', $ilance);
                
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($rfpid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) == 0)
                {
                        if ($isbulkupload == false)
                        {
                                // #### PROCESS CUSTOM PROJECT ANSWERS #########
                                if (isset($custom) AND is_array($custom))
                                {
                                        // process our answer input and store them into the datastore
                                        $ilance->auction_post->process_custom_questions($custom, $rfpid, 'service');
                                }
                                
                                // #### PROCESS CUSTOM PROFILE ANSWER FILTERS
                                if (isset($profileanswer) AND is_array($profileanswer))
                                {
                                        // process our answer input and store them into the datastore
                                        //$ilance->auction_post->process_custom_profile_questions($profileanswer, $rfpid, $userid, 'service');
                                        $ilance->auction_rfp->insert_profile_answers($profileanswer, $rfpid);
                                }
                                
                                // #### HANDLE AUCTION LISTING ENHANCEMENTS ####
                                // this will attempt to debit the acocunt of the users account balance if possible
                                $enhance = array();
                                $featured = $highlite = $bold = $autorelist = 0;
                                $featured_date = '0000-00-00 00:00:00';
                                if (isset($enhancements) AND is_array($enhancements))
                                {
                                        $enhance = $this->process_listing_enhancements_transaction($enhancements, $userid, $rfpid, 'insert', 'service');
                                                                        
                                        // #### PROCESS SELECTED AUCTION ENHANCEMENTS
                                        if (isset($enhance) AND is_array($enhance) AND count($enhance) > 0)
                                        {
                                                $featured = (int)$enhance['featured'];
                                                $highlite = (int)$enhance['highlite'];
                                                $bold = (int)$enhance['bold'];
                                                $autorelist = (int)$enhance['autorelist'];
                                        }
                                }
                                if ($featured)
                                {
                                        $featured_date = DATETIME24H;
                                }
                        }
                        
                        // #### HANDLE START AND END DATES #####################
                        
                        switch ($duration_unit)
                        {
                                // #### DAYS ###################################
                                case 'D':
                                {
                                        $moffset = $duration * 86400;
                                        break;
                                }                            
                                // #### HOURS ##################################
                                case 'H':
                                {
                                        $moffset = $duration * 3600;
                                        break;
                                }                            
                                // #### MINUTES ################################
                                case 'M':
                                {
                                        $moffset = $duration * 60;
                                        break;
                                }
                        }
                        
                        if ($project_details == 'public' OR $project_details == 'invite_only')
                        {
                                // starts now
                                $start_date = DATETIME24H;
                        }
                        else if ($project_details == 'realtime')
                        {
                                // starts now or sometime in the future (scheduled by user)
                                $start_date = intval($year) . '-' . intval($month) . '-' . intval($day) . ' ' . intval($hour) . ':' . intval($min) . ':' . intval($sec);                                
                        }
                        
                        $end_date = date("Y-m-d H:i:s", (strtotime($start_date) + $moffset));
                        
                        // #### HANDLE AUCTION MODERATION LOGIC ################
                        
                        // even though auction moderation might be enabled, we'll make this
                        // visible so the user will not see his/her auction in the pending rfp queue                    
                        if ($ilconfig['moderationsystem_disableauctionmoderation'])
                        {
                                // moderation is disabled, make listing visible
                                $visible = '1';
                        }
                        else
                        {
                                // moderation is enabled, make listing not visible
                                $visible = '0';
                                
                                // does user want this listing saved as draft?
                                if ($status == 'draft')
                                {
                                        // we will make this visible for the user
                                        // but it will still not be seen by the public
                                        // because the status is 'draft' waiting to become 'open'
                                        // it becomes open when buyer visits draft buying activity and lists it manually
                                        $visible = '1';
                                }
                        }
                        
                        // fetch this category's budget group
                        $budgetgroup = $this->fetch_category_budgetgroup($cid);
                        
                        // strip unwanted <script> tags to prevent XSS
                        $project_title = strip_tags($project_title);
                        $additional_info = strip_tags($additional_info);
                        $description_videourl = strip_tags($description_videourl);
                        
			if ($currencyid == 0)
			{
				$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
			}
			
			if ($country != '')
			{
				$countryid = fetch_country_id($country, $_SESSION['ilancedata']['user']['slng']);
			}
			
                        // api hook usage only
                        $query_field_info = $query_field_data = '';
                        
                        ($apihook = $ilance->api('insert_service_auction_query_fields')) ? eval($apihook) : false;
                        
                        // build the service auction in the datastore
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "projects
                                (id,
                                project_id,
                                cid,
                                project_title,
                                description,
                                description_videourl,
                                additional_info,
                                date_added,
                                date_starts,
                                date_end,
                                user_id,
                                visible,
                                budgetgroup,
                                status,
                                project_details,
                                project_type,
                                project_state,
                                bid_details,
                                filter_rating,
                                filter_country,
                                filter_state,
                                filter_city,
                                filter_zip,
                                filter_underage,
                                filter_businessnumber,
                                filter_bidtype,
                                filter_budget,
                                filter_escrow,
				filter_gateway,
				filter_offline,
                                filter_publicboard,
                                filtered_rating,
                                filtered_country,
                                filtered_state,
                                filtered_city,
                                filtered_zip,
                                filtered_bidtype,
                                filtered_budgetid,
                                filtered_auctiontype,
                                featured,
                                featured_date,
                                highlite,
                                bold,
                                autorelist,
                                paymethod,
                                paymethodoptions,
                                paymethodoptionsemail,
                                keywords,
				countryid,
				country,
				state,
				city,
				zipcode,
				currencyid,
                                $query_field_info
                                updateid)
                                VALUES(
                                NULL,
                                '" . intval($rfpid) . "',
                                '" . intval($cid) . "',
                                '" . $ilance->db->escape_string($project_title) . "',
                                '" . $ilance->db->escape_string($description) . "',
                                '" . $ilance->db->escape_string($description_videourl) . "',
                                '" . $ilance->db->escape_string($additional_info) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($start_date) . "',
                                '" . $ilance->db->escape_string($end_date) . "',
                                '" . intval($userid) . "',
                                '" . $visible . "',
                                '" . $ilance->db->escape_string($budgetgroup) . "',
                                '" . $ilance->db->escape_string($status) . "',
                                '" . $ilance->db->escape_string($project_details) . "',
                                '" . $ilance->db->escape_string($project_type) . "',
                                '" . $ilance->db->escape_string($project_state) . "',
                                '" . $ilance->db->escape_string($bid_details) . "',
                                '" . intval($filter_rating) . "',
                                '" . intval($filter_country) . "',
                                '" . intval($filter_state) . "',
                                '" . intval($filter_city) . "',
                                '" . intval($filter_zip) . "',
                                '" . intval($filter_underage) . "',
                                '" . intval($filter_businessnumber) . "',
                                '" . intval($filter_bidtype) . "',
                                '" . intval($filter_budget) . "',
                                '" . intval($filter_escrow) . "',
				'" . intval($filter_gateway) . "',
				'" . intval($filter_offline) . "',
                                '" . intval($filter_publicboard) . "',
                                '" . $ilance->db->escape_string($filtered_rating) . "',
                                '" . $ilance->db->escape_string($filtered_country) . "',
                                '" . $ilance->db->escape_string($filtered_state) . "',
                                '" . $ilance->db->escape_string($filtered_city) . "',
                                '" . $ilance->db->escape_string($filtered_zip) . "',
                                '" . $ilance->db->escape_string($filtered_bidtype) . "',
                                '" . $ilance->db->escape_string($filtered_budgetid) . "',
                                '" . $ilance->db->escape_string($filtered_auctiontype) . "',
                                '" . intval($featured) . "',
                                '" . $ilance->db->escape_string($featured_date) . "',
                                '" . intval($highlite) . "',
                                '" . intval($bold) . "',
                                '" . intval($autorelist) . "',
                                '" . $ilance->db->escape_string(serialize($paymethod)) . "',
                                '" . $ilance->db->escape_string(serialize($paymethodoptions)) . "',
                                '" . $ilance->db->escape_string(serialize($paymethodoptionsemail)) . "',
                                '" . $ilance->db->escape_string($keywords) . "',
				'" . intval($countryid) . "',
				'" . $ilance->db->escape_string($country) . "',
				'" . $ilance->db->escape_string($state) . "',
				'" . $ilance->db->escape_string($city) . "',
				'" . $ilance->db->escape_string($zipcode) . "',
				'" . intval($currencyid) . "',
                                $query_field_data
                                '0')
                        ", 0, null, __FILE__, __LINE__);
                        
                        if ($isbulkupload == false)
                        {
                                // #### INSERTION FEES IN THIS CATEGORY ################
                                // if this fee cannot be paid from online account balance, it will end up in rfp pending area waiting to be paid
                                $this->process_insertion_fee_transaction($cid, 'service', '', $rfpid, $userid, $filter_budget, $filtered_budgetid);
                        }
                        
                        // #### OTHER DETAILS ##################################
			$category = $ilance->categories->recursive($cid, 'service', $_SESSION['ilancedata']['user']['slng'], 1, '', 0);
			
                        $budget = $this->construct_budget_overview($cid, $filtered_budgetid);
                        
                        ($apihook = $ilance->api('auction_submit_end')) ? eval($apihook) : false;
                        
                        // #### AUCTION MODERATION #############################
                        if ($ilconfig['moderationsystem_disableauctionmoderation'])
                        {
                                // #### OPEN STATUS (NOT DRAFT) ################
                                if ($status == 'open')
                                {                                        
                                        // #### REFERRAL SYSTEM TRACKER ########
                                        // we'll track that this user has posted a valid auction from the AdminCP.
                                        update_referral_action('postauction', intval($userid));                                    
    
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                // no api being used, proceed to dispatching email
                                                $ilance->email->mail = fetch_user('email', $userid);
                                                $ilance->email->slng = fetch_user_slng($userid);                                                
                                                $ilance->email->get('new_auction_open_for_bids');		
                                                $ilance->email->set(array(
                                                        '{{username}}' => fetch_user('username', $userid),
                                                        '{{projectname}}' => $project_title,
                                                        '{{description}}' => $description,
                                                        '{{bids}}' => '0',
                                                        '{{category}}' => $category,
                                                        '{{budget}}' => $budget,
                                                        '{{p_id}}' => intval($rfpid),
                                                        '{{details}}' => ucfirst($project_details),
                                                        '{{privacy}}' => ucfirst($bid_details),
                                                        '{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                ));                                                
                                                $ilance->email->send();
                                        }
                                        
                                        $area_title = $phrase['_new_service_auctions_posted_menu'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
                        
                                        if ($isbulkupload == false)
                                        {
                                                // did this buyer actually visit the search pages and profile menus for providers and invited them to this project?
						if (is_array($invitedmember) AND count($invitedmember) > 0)
						{
							$this->dispatch_invited_members_email($invitedmember, 'service', $rfpid, $userid);
						}
                                                
                                                // did this buyer manually enter email addresses to invite users outside the marketplace to bid?
                                                $this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage);
                                        }
                                        
                                        // rebuild category count
                                        build_category_count($cid, 'add', "insert_service_auction(): adding increment count category id $cid");
                                        
                                        $url = ($ilconfig['globalauctionsettings_seourls'])
						? construct_seo_url('serviceauction', 0, $rfpid, $project_title, $phrase['_view_auction_here'], $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
						: '<a href="' . $ilpage['rfp'] . '?id=' . $rfpid . '">' . $phrase['_view_auction_here'] . '</a>';
                                        
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();                                                
                                                $ilance->email->get('service_auction_posted_admin');		
                                                $ilance->email->set(array(
                                                        '{{buyer}}' => fetch_user('username', $userid),
                                                        '{{project_title}}' => $project_title,
                                                        '{{description}}' => $description,
                                                        '{{bids}}' => '0',
                                                        '{{category}}' => $category,
                                                        '{{budget}}' => $budget,
                                                        '{{p_id}}' => intval($rfpid),
                                                        '{{details}}' => ucfirst($project_details),
                                                        '{{privacy}}' => ucfirst($bid_details),
                                                        '{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                ));                                                
                                                $ilance->email->send();
                                                
						$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
						
                                                $ilance->template->fetch('main', 'listing_reverse_auction_complete.html');
                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                $ilance->template->parse_if_blocks('main');
                                                $ilance->template->pprint('main', $pprint_array);
                                                exit();
                                        }
                                }
                                
                                // #### DRAFT MODE #############################
                                else if ($status == 'draft')
                                {
                                        $area_title = $phrase['_new_service_auctions_posted_menu'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
                                        
                                        if ($isbulkupload == false)
                                        {
                                                // handle invitation logic for non moderated draft auctions (this will not send email out yet, only log it)
                                                $this->dispatch_invited_members_email($invitedmember, 'service', $rfpid, $userid, 1);
                                                
                                                // did this buyer manually enter email addresses to invite users to bid?
                                                $this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, 1);
                                        }
                                        
                                        $url = '<a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;sub=drafts">' . $phrase['_view_draft_auctions_here'] . '</a>';
                                        
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                // no api being used, proceed to dispatching email
                                                $ilance->template->fetch('main', 'listing_reverse_auction_draft.html');
                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                $ilance->template->parse_if_blocks('main');
                                                $ilance->template->pprint('main', array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
                                                exit();
                                        }
                                }
                        }
                        
                        // #### AUCTIONS ARE BEING MODERATED ###################
                        else
                        {
                                // do not send email if this is a draft as the user doesn't want it posted right now anyways
                                // it will resend this email when they decide to make it public manually on their own
                                if ($status == 'draft')
                                {
                                        $area_title = $phrase['_new_service_auctions_posted_menu'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
                                        
                                        if ($isbulkupload == false)
                                        {
                                                // handle invitation logic for moderated draft auctions (this will not send email out yet)
                                                $this->dispatch_invited_members_email('service', $rfpid, $userid, $dontsendemail = 1);
                                                
                                                // did this buyer manually enter email addresses to invite users to bid?
                                                $this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
                                        }
                                        
                                        // todo: make url use seo if enabled
                                        $url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts"><strong>' . $phrase['_view_draft_auctions_here'] . '</strong></a>';
                                        
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                // no api being used, proceed to dispatching email
                                                $ilance->template->fetch('main', 'listing_reverse_auction_draft.html');
                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                $ilance->template->parse_if_blocks('main');
                                                $ilance->template->pprint('main', array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
                                                exit();
                                        }
                                }
                                else
                                {
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                // no api being used, proceed to dispatching email
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                
                                                $ilance->email->get('auction_moderation_admin');		
                                                $ilance->email->set(array(
                                                        '{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
                                                        '{{project_title}}' => $project_title,
                                                        '{{description}}' => $description,
                                                        '{{category}}' => $category,
                                                        '{{budget}}' => $budget,
                                                        '{{p_id}}' => intval($rfpid),
                                                        '{{details}}' => ucfirst($project_details),
                                                        '{{privacy}}' => ucfirst($bid_details),
                                                        '{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                ));
                                                
                                                $ilance->email->send();
                                        }
                                }
                                
                                if ($isbulkupload == false)
                                {
                                        // handle invitation logic for moderated auctions (this will not send email out yet)
                                        $this->dispatch_invited_members_email('service', $rfpid, $userid, $dontsendemail = 1);
                                        
                                        // did this buyer manually enter email addresses to invite users to bid?
                                        $this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
                                }
                                
                                // send user to his pending rfp's to show they are in the moderation queue
                                $area_title = $phrase['_new_service_auctions_posted_menu'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
                                
                                // todo: make url use seo if enabled
                                $url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending"><strong>' . $phrase['_pending_auctions_menu'] . '</strong></a>';
                                
                                // are we constructing new auction from an API call?
                                if ($skipemailprocess == 0)
                                {
                                        // no api being used, proceed to dispatching email
                                        $ilance->template->fetch('main', 'listing_reverse_auction_moderation.html');
                                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                        $ilance->template->parse_if_blocks('main');
                                        $ilance->template->pprint('main', array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
                                        exit();
                                }
                        }
                }
        }
        /**
        * Function for handling a remote image url through a bulk file upload..
        * This will fetch the file from the remote server via curl, check if the file is valid (is an image),
        * extract it's contents and save to the filepath or the database.
        *
        * @param          string       image url (full url with image, ie: http://www.domain.com/image1.jpg
        * @param          integer      project id we're assigning this image to
        * @param          integer      category id
        *
        * @return         nothing
        */
        function handle_remote_image_url($imgurl = '', $project_id = 0, $cid = 0, $attachtype = 'itemphoto')
        {
                global $ilance, $ilconfig;
                
                $upload_file_size = 0;
                $exif = $filedata = $data = $newfilename = $filename = $filehash = $filetype = '';
                
                $data = save_url_image($imgurl);
                if (!empty($data) AND is_array($data))
                {
                        $newfilename = $data['fullpath'];
                        $filename = $data['filename'];
                        $filehash = $data['filehash'];
                        $filetype = $data['filetype'];
                }
                
                if (!empty($newfilename) AND !empty($filename))
                {
                        // fetch exif information (extended image support)
                        if (function_exists('exif_read_data'))
                        {
                                $exifdata = @exif_read_data($newfilename, 'EXIF');
                                if (!empty($exifdata))
                                {
                                        $exif = addslashes(serialize($exifdata));
                                        unset($exifdata);
                                }
                        }
                                            
                        $upload_file_size = filesize($newfilename);
        
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                // if we are using the database, put file data in temp space and remove actual file from server
                                $filedata = addslashes(fread(fopen($newfilename, 'rb'), filesize($newfilename)));
                                @unlink($newfilename);
                        }
                        
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, project_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress)
                                VALUES(
                                NULL,
                                '" . $ilance->db->escape_string($attachtype) . "',
                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                '" . intval($project_id) . "',
                                '" . intval($cid) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($filename) . "',
                                '" . $filedata . "',
                                '" . $ilance->db->escape_string($filetype) . "',
                                '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                '0',
                                '" . $ilance->db->escape_string($upload_file_size) . "',
                                '" . $ilance->db->escape_string($filehash) . "',
                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "')
                        ", 0, null, __FILE__, __LINE__);
                }
        }
        
        /**
        * Function to insert a new product auction
        *
        * @param       integer       user id
        * @param       string        project type (reverse/forward)
        * @param       string        status (open/draft)
        * @param       string        auction state (service/product)
        * @param       integer       category id
        * @param       integer       rfp id (custom auction id)
        * @param       string        auction title
        * @param       string        auction description
        * @param       string        auction description video embed
        * @param       string        auction additional info
        * @param       string        auction keywords/tags
        * @param       array         array holding custom questions answered
        * @param       array         array holding profile answers
        * @param       string        filtered auction type
        * @param       string        start price
        * @param       string        project details (public, realtime, invite_only)
        * @param       string        bid details
        * @param       bool          filter by rating?
        * @param       bool          filter by country?
        * @param       bool          filter by state?
        * @param       bool          filter by city?
        * @param       bool          filter by zip code?
        * @param       integer       filtered rating answer
        * @param       string        filtered country answer
        * @param       string        filtered state answer
        * @param       string        filtered city answer
        * @param       string        filtered zip code answer
        * @param       string        item location city
        * @param       string        item location state/province
        * @param       string        item location zip or postal code
        * @param       string        item location country
        * @param       array         shipping information array
        * @param       bool          buy now?
        * @param       string        buy now price
        * @param       string        buy now qty available
        * @param       array         auction upsell enhancements array
        * @param       bool          using reserve price?
        * @param       string        reserve price
        * @param       bool          filter by underage disabled?
        * @param       bool          filter option: is escrow being used for this listing?
        * @param       bool          filter option: are gateway payment methods being used for this listing?
        * @param       bool          filter option: are offline payment methods being used for this listing?
        * @param       bool          filter public board enabled?
        * @param       array         invitation list (external emailed users)
        * @param       string        invitation message
        * @param       integer       year (used for start of year for realtime auction only)
        * @param       integer       month (used for start of month for realtime auction only)
        * @param       integer       day (used for start of day for realtime auction only)
        * @param       integer       hour (used for start of hour for realtime auction only)
        * @param       integer       min (used for start of min for realtime auction only)
        * @param       integer       sec (used for start of sec for realtime auction only)
        * @param       integer       duration (1 - 30) usually handling (days, hours or minutes) answer
        * @param       string        duration unit (D, H, M)
        * @param       string        payment method defined by the project buyer
        * @param       string        payment method gateway option selected by the seller
        * @param       string        payment method gateway email address selected by the seller
        * @param       string        retail price
        * @param       integer       unique bid count until bidding closes (optional)
        * @param       bool          saving auction in draft mode? (default no)
        * @param       bool          return policy: returns accepted?
        * @param       integer       return policy: return within days
        * @param       string        return policy: return given as (default none)
        * @param       string        return policy: return shipping paid by
        * @param       string        return policy text
        * @param       bool          is donation associated (default 0 - false)
        * @param       integer       charity id of the doner associated (default 0)
        * @param       integer       donation percentage (default 0)
        * @param       bool          skip all email process? (default no) - useful to use this function as API and to not send 1000's of emails if 1000's of auctions are added.
        * @param       array         custom api hook (optional)
        * @param       bool          is bulk upload? (default false) - note: if true, insertion fees and enhancements will not be created
        * @param       string        string url to the sample bulk item photo
        * 
        * @return      bool          Returns true or false when (skip all email process = true) otherwise this function returns HTML formatted text of actions occured.
        */
        function insert_product_auction($userid = 0, $project_type = 'forward', $status = 'open', $project_state = 'product', $cid = 0, $rfpid = 0, $project_title = '', $description = '', $description_videourl = '', $additional_info = '', $keywords = '', $custom = array(), $profileanswer = array(), $filtered_auctiontype = '', $start_price = 0, $project_details = '', $bid_details = '', $filter_rating = 0, $filter_country = 0, $filter_state = 0, $filter_city = 0, $filter_zip = 0, $filtered_rating = '', $filtered_country = '', $filtered_state = '', $filtered_city = '', $filtered_zip = '', $city = '', $state = '', $zipcode = '', $country = '', $shipping = array(), $buynow = 0, $buynow_price = 0, $buynow_qty = 1, $enhancements = array(), $reserve = 0, $reserve_price = 0, $filter_underage = 0, $filter_escrow = 0, $filter_gateway = 0, $filter_offline = 0, $filter_publicboard = 0, $invitelist, $invitemessage, $year, $month, $day, $hour, $min, $sec, $duration, $duration_unit, $paymethod = array(), $paymethodoptions = array(), $paymethodoptionsemail = array(), $retailprice = 0, $uniquebidcount = 0, $saveasdraft = 0, $returnaccepted = 0, $returnwithin = 0, $returngivenas = 'none', $returnshippaidby = 'buyer', $returnpolicy = '', $donation = '', $charityid = 0, $donationpercentage = 0, $skipemailprocess = 0, $apihookcustom = array(), $isbulkupload = false, $sample = '', $currencyid = 0)
        {
                global $ilance, $myapi, $ilconfig, $ilconfig, $ilpage, $phrase;
                
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->auction = construct_object('api.auction');
                $ilance->auction_post = construct_object('api.auction_post');
                $ilance->subscription = construct_object('api.subscription');
		
		require_once(DIR_CORE . 'functions_shipping.php');
                
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($rfpid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) == 0)
                {
                        $enhance = array();
                        $featured = $highlite = $bold = $autorelist = 0;
                        $featured_date = '0000-00-00 00:00:00';
                        
                        if ($isbulkupload == false)
                        {
                                // #### PROCESS CUSTOM PROJECT ANSWERS #################                        
                                if (isset($custom) AND is_array($custom))
                                {
                                        // process our answer input and store them into the datastore
                                        $ilance->auction_post->process_custom_questions($custom, $rfpid, 'product');
                                }
                                
                                // #### PROCESS CUSTOM PROFILE FILTERS #################                        
                                if (isset($profileanswer) AND is_array($profileanswer))
                                {
                                        // process our answer input and store them into the datastore
                                        $ilance->auction_post->process_custom_profile_questions($profileanswer, $rfpid, $userid, 'product');
                                }
                        
                                // #### HANDLE AUCTION LISTING ENHANCEMENTS ############
                                // this will attempt to debit the acocunt of the users account balance if possible
                                if (isset($enhancements) AND is_array($enhancements))
                                {
                                        // #### capture buynow and reserve price fees if available
                                        if ($buynow > 0)
                                        {
                                                $enhancements['buynow'] = 1;
                                        }
                                        if ($reserve > 0)
                                        {
                                                $enhancements['reserve'] = 1;
                                        }
                                        
                                        $enhance = $this->process_listing_enhancements_transaction($enhancements, $userid, $rfpid, 'insert', 'product');
                                                                        
                                        // #### PROCESS SELECTED AUCTION ENHANCEMENTS
                                        if (isset($enhance) AND is_array($enhance) AND count($enhance) > 0)
                                        {
                                                $featured = (int)$enhance['featured'];
                                                $highlite = (int)$enhance['highlite'];
                                                $bold = (int)$enhance['bold'];
                                                $autorelist = (int)$enhance['autorelist'];
                                        }
                                }
                                
                                if ($featured)
                                {
                                        $featured_date = DATETIME24H;
                                }
                        }
                        
                        // #### HANDLE START AND END DATES #####################                        
                        switch ($duration_unit)
                        {
                                // #### DAYS ###################################
                                case 'D':
                                {
                                        $moffset = $duration * 86400;
                                        break;
                                }                            
                                // #### HOURS ##################################
                                case 'H':
                                {
                                        $moffset = $duration * 3600;
                                        break;
                                }                            
                                // #### MINUTES ################################
                                case 'M':
                                {
                                        $moffset = $duration * 60;
                                        break;
                                }
                        }
                        
                        // starts now or sometime in the future (scheduled by user)
                        $start_date = ($project_details == 'realtime')
				? intval($year) . '-' . intval($month) . '-' . intval($day) . ' ' . intval($hour) . ':' . intval($min) . ':' . intval($sec)
				: DATETIME24H;
                        
                        $end_date = date("Y-m-d H:i:s", (strtotime($start_date) + $moffset));
                        
                        // #### HANDLE AUCTION MODERATION LOGIC ################
                        
                        // even though auction moderation might be enabled, we'll make this
                        // visible so the user will not see his/her auction in the pending rfp queue                    
                        if ($ilconfig['moderationsystem_disableauctionmoderation'])
                        {
                                // moderation is disabled, make listing visible
                                $visible = '1';
                        }
                        else
                        {
                                // moderation is enabled, make listing not visible
                                $visible = '0';
                                
                                // does user want this listing saved as draft?
                                if ($status == 'draft')
                                {
                                        // we will make this visible for the user
                                        // but it will still not be seen by the public
                                        // because the status is 'draft' waiting to become 'open'
                                        // it becomes open when buyer visits draft buying activity and lists it manually
                                        $visible = '1';
                                }
                        }
                        
                        // run auction title through vulgar words filter
                        $project_title = strip_tags($project_title);
                        
                        // run auction additional info/requirements via vular words filter
                        $additional_info = strip_tags($additional_info);
                        
                        // if we do not have a start price then start the bid at 1 cent
                        if ($start_price <= 0)
                        {
                                $start_price = 0.01;    
                        }
                        
                        if ($filtered_auctiontype == 'fixed')
                        {
                                $start_price = $buynow_price;
                                $retailprice = $buynow_price;
                        }
                        
                        $currentprice = $start_price;
                        
                        // if we are creating a unique bid event we will update the current price
                        // to that of the retail price specified
                        if ($project_details == 'unique' AND $retailprice > 0)
                        {
                                $currentprice = $retailprice;
                        }
                        
                        if ($isbulkupload AND !empty($sample))
                        {
                                // handle bulk item sample photo from a valid url source
                                $this->handle_remote_image_url($sample, $rfpid, $cid, 'itemphoto');
                        }
			
			if ($currencyid == 0)
			{
				$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
			}
			
			if ($country != '')
			{
				$countryid = fetch_country_id($country, $_SESSION['ilancedata']['user']['slng']);
			}
                        
                        // api hook usage only
                        $query_field_info = $query_field_data = '';
                        
                        ($apihook = $ilance->api('insert_product_auction_query_fields')) ? eval($apihook) : false;
                        
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "projects
                                (id,
                                project_id,
                                cid,
                                description,
                                description_videourl,
                                keywords,
                                date_added,
                                date_starts,
                                date_end,
                                user_id,
                                visible,
                                views,
                                project_title,
                                status,
                                project_details,
                                project_type,
                                project_state,
                                bid_details,
                                filter_escrow,
				filter_gateway,
				filter_offline,
                                filter_rating,
                                filter_country,
                                filter_state,
                                filter_city,
                                filter_zip,
                                filtered_rating,
                                filtered_country,
                                filtered_state,
                                filtered_city,
                                filtered_zip,
                                filter_underage,
                                filter_publicboard,
                                filtered_auctiontype,
                                buynow,
                                buynow_price,
                                buynow_qty,
                                reserve,
                                reserve_price,
                                bold,
                                featured,
                                featured_date,
                                highlite,
                                autorelist,
                                startprice,
                                retailprice,
                                uniquebidcount,
                                paymethod,
                                paymethodoptions,
                                paymethodoptionsemail,
                                currentprice,
                                returnaccepted,
                                returnwithin,
                                returngivenas,
                                returnshippaidby,
                                returnpolicy,
                                donation,
                                charityid,
                                donationpercentage,
				countryid,
				country,
				state,
				city,
				zipcode,
				currencyid,
                                $query_field_info
                                updateid)
                                VALUES(
                                NULL,
                                '" . intval($rfpid) . "',
                                '" . intval($cid) . "',
                                '" . $ilance->db->escape_string($description) . "',
                                '" . $ilance->db->escape_string($description_videourl) . "',
                                '" . $ilance->db->escape_string($keywords) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($start_date) . "',
                                '" . $ilance->db->escape_string($end_date) . "',
                                '" . intval($userid) . "',
                                '" . $ilconfig['moderationsystem_disableauctionmoderation'] . "',
                                '0',
                                '" . $ilance->db->escape_string($project_title) . "',
                                '" . $ilance->db->escape_string($status) . "',
                                '" . $ilance->db->escape_string($project_details) . "',
                                '" . $ilance->db->escape_string($project_type) . "',
                                '" . $ilance->db->escape_string($project_state) . "',
                                '" . $ilance->db->escape_string($bid_details) . "',
                                '" . intval($filter_escrow) . "',
				'" . intval($filter_gateway) . "',
				'" . intval($filter_offline) . "',
                                '" . intval($filter_rating) . "',
                                '" . intval($filter_country) . "',
                                '" . intval($filter_state) . "',
                                '" . intval($filter_city) . "',
                                '" . intval($filter_zip) . "',
                                '" . $ilance->db->escape_string($filtered_rating) . "',
                                '" . $ilance->db->escape_string($filtered_country) . "',
                                '" . $ilance->db->escape_string($filtered_state) . "',
                                '" . $ilance->db->escape_string($filtered_city) . "',
                                '" . $ilance->db->escape_string($filtered_zip) . "',
                                '" . $ilance->db->escape_string($filter_underage) . "',
                                '" . $ilance->db->escape_string($filter_publicboard) . "',
                                '" . $ilance->db->escape_string($filtered_auctiontype) . "',
                                '" . intval($buynow) . "',
                                '" . $ilance->db->escape_string($buynow_price) . "',
                                '" . intval($buynow_qty) . "',
                                '" . intval($reserve) . "',
                                '" . $ilance->db->escape_string($reserve_price) . "',
                                '" . intval($bold) . "',
                                '" . intval($featured) . "',
                                '" . $ilance->db->escape_string($featured_date) . "',
                                '" . intval($highlite) . "',
                                '" . intval($autorelist) . "',
                                '" . $ilance->db->escape_string($start_price) . "',
                                '" . $ilance->db->escape_string($retailprice) . "',
                                '" . intval($uniquebidcount) . "',
                                '" . $ilance->db->escape_string(serialize($paymethod)) . "',
                                '" . $ilance->db->escape_string(serialize($paymethodoptions)) . "',
                                '" . $ilance->db->escape_string(serialize($paymethodoptionsemail)) . "',
                                '" . $ilance->db->escape_string($currentprice) . "',
                                '" . intval($returnaccepted) . "',
                                '" . intval($returnwithin) . "',
                                '" . $ilance->db->escape_string($returngivenas) . "',
                                '" . $ilance->db->escape_string($returnshippaidby) . "',
                                '" . $ilance->db->escape_string($returnpolicy) . "',
                                '" . $ilance->db->escape_string($donation) . "',
                                '" . intval($charityid) . "',
                                '" . $ilance->db->escape_string($donationpercentage) . "',
				'" . intval($countryid) . "',
				'" . $ilance->db->escape_string($country) . "',
				'" . $ilance->db->escape_string($state) . "',
				'" . $ilance->db->escape_string($city) . "',
				'" . $ilance->db->escape_string($zipcode) . "',
				'" . intval($currencyid) . "',
                                $query_field_data
                                '0')
                        ", 0, null, __FILE__, __LINE__);
			
			// #### SAVE SHIPPING DETAILS ##########################
			if (isset($shipping) AND is_array($shipping))
			{
				$this->save_item_shipping_logic($rfpid, $shipping);
			}
			
                        // #### INSERTION FEES IN THIS CATEGORY ################
                        // this will generate insertion fee to be paid by the auction owner before listing is live
                        // if seller has no funds in their account the auction will go into pending auction queue
                        $ifbaseamount = 0;
                        if ($start_price > 0)
                        {
                                $ifbaseamount = $start_price;
                                if ($reserve AND $reserve_price > 0)
                                {
                                        if ($reserve_price > $start_price)
                                        {
                                                $ifbaseamount = $reserve_price;
                                        }
                                }
                        }
                        
                        // if seller is supplying a buy now price, check to see if it's higher than our current
                        // insertion fee amount, if so, use this value for the insertion fee base amount
                        if ($buynow AND $buynow_price > 0 AND $buynow_qty > 0)
                        {
                                $totalbuynow = ($buynow_price * $buynow_qty);
                                if ($totalbuynow > $ifbaseamount)
                                {
                                        $ifbaseamount = $totalbuynow;
                                }
                        }
                        
                        if ($isbulkupload == false)
                        {
                                $this->process_insertion_fee_transaction($cid, 'product', $ifbaseamount, $rfpid, $userid, 0, 0);
                        }
                        
                        // #### OTHER DETAILS ##################################
			$category = $ilance->categories->recursive($cid, 'product', fetch_user_slng($userid), 1, '', 0);
                        
                        ($apihook = $ilance->api('product_auction_submit_end')) ? eval($apihook) : false;
                        
                        // #### AUCTION MODERATION #############################
                        if ($ilconfig['moderationsystem_disableauctionmoderation'])
                        {
                                // are we constructing new auction from an API call?
                                if ($skipemailprocess == 0)
                                {
                                        // email admin
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('product_auction_posted_admin');		
                                        $ilance->email->set(array(
                                                '{{buyer}}' => fetch_user('username', $userid),
                                                '{{project_title}}' => $project_title,
                                                '{{description}}' => $description,
                                                '{{bids}}' => '0',
                                                '{{category}}' => $category,
                                                '{{minimum_bid}}' => $ilance->currency->format($start_price, $currencyid),
                                                '{{p_id}}' => $rfpid,
                                                '{{details}}' => ucfirst($project_details),
                                                '{{privacy}}' => ucfirst($bid_details),
                                                '{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                        ));
                                        $ilance->email->send();
                                        
                                        // email user
                                        $ilance->email->mail = fetch_user('email', $userid);
                                        $ilance->email->slng = fetch_user_slng($userid);
                                        $ilance->email->get('new_product_auction_open_for_bids');		
                                        $ilance->email->set(array(
                                                '{{username}}' => fetch_user('username', $userid),
                                                '{{projectname}}' => stripslashes($project_title),
                                                '{{description}}' => $description,
                                                '{{category}}' => $category,
                                                '{{p_id}}' => $rfpid,
                                                '{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                
                                        ));
                                        $ilance->email->send();
                                }
                                
                                // #### OPEN STATUS (NOT DRAFT) ################
                                if ($status == 'open')
                                {
                                        // #### REFERRAL SYSTEM TRACKER ########
                                        // we'll track that this user has posted a valid auction from the AdminCP.
                                        update_referral_action('postauction', $userid);                                    
    
                                        if ($isbulkupload == false)
                                        {
                                                // did this buyer actually visit the search pages and profile menus for providers and added them for this project?
                                                $this->dispatch_invited_members_email('product', $rfpid, $userid);
                                                
                                                // did this buyer manually enter email addresses to invite users to bid?
                                                $this->dispatch_external_members_email('product', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage);
                                        }
                                        
                                        // rebuild category count
                                        build_category_count($cid, 'add', "insert_product_auction(): adding increment count category id $cid");
                                        
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                $url = ($ilconfig['globalauctionsettings_seourls'])
							? construct_seo_url('productauction', 0, $rfpid, $project_title, $phrase['_view_auction_here'], $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
							: '<a href="' . $ilpage['merch'] . '?id=' . $rfpid . '">' . $phrase['_view_auction_here'] . '</a>';
							
						$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                
                                                $ilance->template->fetch('main', 'listing_forward_auction_complete.html');
                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                $ilance->template->parse_if_blocks('main');
                                                $ilance->template->pprint('main', $pprint_array);
                                                exit();
                                        }
                                }
                                // #### DRAFT MODE #############################
                                else if ($status == 'draft')
                                {
                                        $area_title = $phrase['_new_service_auctions_posted_menu'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
                                        
                                        if ($isbulkupload == false)
                                        {
                                                // handle invitation logic for non moderated draft auctions (this will not send email out yet, only log it)
                                                $this->dispatch_invited_members_email('service', $rfpid, $userid, $dontsendemail = 1);
                                                
                                                // did this buyer manually enter email addresses to invite users to bid?
                                                $this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
                                        }
                                        
                                        $url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts">' . $phrase['_view_draft_auctions_here'] . '</a>';
                                        
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                // no api being used, proceed to dispatching email
                                                $ilance->template->fetch('main', 'listing_forward_auction_draft.html');
                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                $ilance->template->parse_if_blocks('main');
                                                $ilance->template->pprint('main', array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
                                                exit();
                                        }
                                }
                                
                        }
                        
                        // #### AUCTIONS ARE BEING MODERATED ###################
                        else
                        {
                                // are we constructing new auction from an API call?
                                if ($skipemailprocess == 0)
                                {
                                        // auctions require moderation
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        $ilance->email->get('auction_moderation_admin');		
                                        $ilance->email->set(array(
                                                '{{buyer}}' => fetch_user('username', $userid),
                                                '{{project_title}}' => stripslashes($project_title),
                                                '{{description}}' => $description,
                                                '{{category}}' => $category,
                                                '{{minimum_bid}}' => $ilance->currency->format($start_price, $currencyid),
                                                '{{p_id}}' => $rfpid,
                                                '{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                '{{details}}' => ucfirst($project_details),
                                                '{{privacy}}' => ucfirst($bid_details),
                                                
                                        ));
                                        
                                        $ilance->email->send();
                                }
                                
                                if ($isbulkupload == false)
                                {
                                        // handle invitation logic for moderated auctions
                                        $this->dispatch_invited_members_email('product', $rfpid, $userid, $dontsendemail = 1);
                                        
                                        // did this buyer manually enter email addresses to invite users to bid?
                                        $this->dispatch_external_members_email('product', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
                                }
                                
                                // are we constructing new auction from an API call?
                                if ($skipemailprocess == 0)
                                {
                                        // send user to his pending rfp's to show they are in the moderation queue
                                        $area_title = $phrase['_new_service_auctions_posted_menu'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
                                        
                                        // no api being used, proceed to dispatching email
                                        $url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending"><strong>' . $phrase['_pending_auctions_menu'] . '</strong></a>';
					
					$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
					
                                        $ilance->template->fetch('main', 'listing_forward_auction_moderation.html');
                                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                        $ilance->template->parse_if_blocks('main');
                                        $ilance->template->pprint('main', $pprint_array);
                                        exit();
                                }
                        }
                }
        }
        
	/**
        * Function to handle saving the shipping logic for an item within the appropriate areas of the database
        *
        * @param       integer     listing id
        * @param       array       shipping array with all details
        *
        * @return      nothing
        */
        function save_item_shipping_logic($rfpid = 0, $shipping = array())
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
		
		if (isset($shipping) AND is_array($shipping) AND $rfpid > 0)
		{
			require_once(DIR_CORE . 'functions_shipping.php');
			
			// #### item shipping info #####################
			$sql = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects_shipping
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) == 0)
			{
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "projects_shipping
					(project_id, ship_method, ship_handlingtime, ship_handlingfee, ship_packagetype, ship_length, ship_width, ship_height, ship_weightlbs, ship_weightoz)
					VALUES(
					'" . intval($rfpid) . "',
					'" . $ilance->db->escape_string($shipping['ship_method']) . "',
					'" . intval($shipping['ship_handlingtime']) . "',
					'" . $ilance->db->escape_string($shipping['ship_handlingfee']) . "',
					'" . $ilance->db->escape_string($shipping['ship_packagetype']) . "',
					'" . intval($shipping['ship_length']) . "',
					'" . intval($shipping['ship_width']) . "',
					'" . intval($shipping['ship_height']) . "',
					'" . intval($shipping['ship_weightlbs']) . "',
					'" . intval($shipping['ship_weightoz']) . "')
				", 0, null, __FILE__, __LINE__);
			}
			else
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects_shipping
					SET ship_method = '" . $ilance->db->escape_string($shipping['ship_method']) . "',
					ship_handlingtime = '" . intval($shipping['ship_handlingtime']) . "',
					ship_handlingfee = '" . $ilance->db->escape_string($shipping['ship_handlingfee']) . "',
					ship_packagetype = '" . $ilance->db->escape_string($shipping['ship_packagetype']) . "',
					ship_length = '" . intval($shipping['ship_length']) . "',
					ship_width = '" . intval($shipping['ship_width']) . "',
					ship_height = '" . intval($shipping['ship_height']) . "',
					ship_weightlbs = '" . intval($shipping['ship_weightlbs']) . "',
					ship_weightoz = '" . intval($shipping['ship_weightoz']) . "'
					WHERE project_id = '" . intval($rfpid) . "'
				", 0, null, __FILE__, __LINE__);	
			}
			
			// #### item shipping destinations info ########
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "projects_shipping_regions
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				if (isset($shipping['ship_options_' . $i]) AND isset($shipping['ship_service_' . $i]) AND !empty($shipping['ship_options_' . $i]) AND !empty($shipping['ship_service_' . $i]))
				{
					// #### item ship-to regions ###########
					if ($shipping['ship_options_' . $i] == 'domestic')
					{
						$countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], fetch_site_slng());
						$region = fetch_region_by_countryid($countryid);
						
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "projects_shipping_regions
							(project_id, country, countryid, region, row)
							VALUES(
							'" . intval($rfpid) . "',
							'" . $ilance->db->escape_string($ilconfig['registrationdisplay_defaultcountry']) . "',
							'" . intval($countryid) . "',
							'" . $ilance->db->escape_string($region) . "',
							'" . $i . "')
						", 0, null, __FILE__, __LINE__);						
						unset($countryid, $region);
					}
					else if ($shipping['ship_options_' . $i] == 'worldwide')
					{
						$countries = fetch_countries_by_region_array('worldwide');							
						foreach ($countries AS $countryinfo)
						{
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "projects_shipping_regions
								(project_id, country, countryid, region, row)
								VALUES(
								'" . intval($rfpid) . "',
								'" . $ilance->db->escape_string($countryinfo['country']) . "',
								'" . intval($countryinfo['countryid']) . "',
								'" . $ilance->db->escape_string($countryinfo['region']) . "',
								'" . $i . "')
							", 0, null, __FILE__, __LINE__);
						}	
					}
					else
					{
						if (isset($shipping['ship_options_custom_region_' . $i]) AND count($shipping['ship_options_custom_region_' . $i]) > 0)
						{
							foreach ($shipping['ship_options_custom_region_' . $i] AS $key => $region)
							{
								if (!empty($region))
								{
									$countries = fetch_countries_by_region_array($region);							
									foreach ($countries AS $countryinfo)
									{
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "projects_shipping_regions
											(project_id, country, countryid, region, row)
											VALUES(
											'" . intval($rfpid) . "',
											'" . $ilance->db->escape_string($countryinfo['country']) . "',
											'" . intval($countryinfo['countryid']) . "',
											'" . $ilance->db->escape_string($region) . "',
											'" . $i . "')
										", 0, null, __FILE__, __LINE__);
									}
								}
							}
						}
					}
					
					$sql = $ilance->db->query("
						SELECT project_id
						FROM " . DB_PREFIX . "projects_shipping_destinations
						WHERE project_id = '" . intval($rfpid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) == 0)
					{
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "projects_shipping_destinations
							(destinationid, project_id, ship_options_" . $i . ", ship_service_" . $i . ", ship_fee_" . $i . ", freeshipping_" . $i . ")
							VALUES(
							NULL,
							'" . intval($rfpid) . "',
							'" . $ilance->db->escape_string($shipping['ship_options_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_service_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_fee_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['freeshipping_' . $i]) . "')
						", 0, null, __FILE__, __LINE__);
					}
					else
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects_shipping_destinations
							SET ship_options_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_options_' . $i]) . "',
							ship_service_" . $i . " = '" . intval($shipping['ship_service_' . $i]) . "',
							ship_fee_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_fee_' . $i]) . "',
							freeshipping_" . $i . " = '" . intval($shipping['freeshipping_' . $i]) . "'
							WHERE project_id = '" . intval($rfpid) . "'
						", 0, null, __FILE__, __LINE__);	
					}
				}	
			}
		}
		
		return true;
	}
	
        /**
        * Function to handle invitation logic for moderated and non-moderated auctions.  This function will
        * only send any "members" that were invited to this auction when the buyer was using the search provider
        * menus and/or inviting a user directly from their profile menu.  The logic will detect if any invite
        * logic has been stored in a session and if so, dispatch any invitation email to the end users.
        *
        * @param       array       invited users array
        * @param       string      category type (service/product)
        * @param       integer     project id
        * @param       integer     user id
        * @param       bool        do not send email? (default false)
        *
        * @return      nothing
        */
        function dispatch_invited_members_email($invitedusers = array(), $cattype = 'service', $rfpid = 0, $userid = 0, $dontsendemail = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                if (!empty($_SESSION['ilancedata']['tmp']['invitations']) AND is_serialized($_SESSION['ilancedata']['tmp']['invitations']))
                {
                        $invited = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
                        $count = count($invited);
                        if ($count > 0)
                        {
                                for ($i = 0; $i < $count; $i++)
                                {
                                        $this->insert_auction_invitation($userid, $invited[$i], $rfpid, $dontsendemail, $cattype);
                                }
                        }
                        
                        // remove temp invitation session data so we don't attach same users to new auctions created after this one
                        $_SESSION['ilancedata']['tmp']['invitations'] = '';
                        unset($_SESSION['ilancedata']['tmp']['invitations']);
                }
                else
                {
                        // invite list is empty (we must be opening a previously saved draft)
                        // check if we have any invited users to dispatch email to
                        $sql3 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "project_invitations
                                WHERE project_id = '" . intval($rfpid) . "'
                                        AND buyer_user_id > 0
                                        AND seller_user_id > 0
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql3) > 0)
                        {
                                while ($res3 = $ilance->db->fetch_array($sql3, DB_ASSOC))
                                {
                                        // are we constructing new auction from an API call?
                                        if ($dontsendemail == 0)
                                        {
                                                $comment = $phrase['_no_message_was_provided'];
                                                if (!empty($res3['invite_message']))
                                                {
                                                        $comment = strip_tags($res3['invite_message']);
                                                }
                                                
                                                if ($cattype == 'service')
                                                {
                                                        // service auction
                                                        $email = fetch_user('email', $res3['seller_user_id']);
                                                        $invitee = fetch_user('username', $res3['seller_user_id']);
                                                }
                                                else
                                                {
                                                        // product auction
                                                        $email = fetch_user('email', $res3['buyer_user_id']);
                                                        $invitee = fetch_user('username', $res3['buyer_user_id']);
                                                }
                                                
                                                // todo: detect if seo is enabled
                                                $url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&id=' . intval($rfpid) . '&invited=1&e=' . $email;
                                                
                                                $ilance->email->mail = $email;
                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                $ilance->email->get('invited_to_place_bid');		
                                                $ilance->email->set(array(
                                                        '{{invitee}}' => $invitee,
                                                        '{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
                                                        '{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
                                                        '{{username}}' => $_SESSION['ilancedata']['user']['username'],
                                                        '{{projectname}}' => stripslashes(fetch_auction('project_title', $rfpid)),
                                                        '{{bidprivacy}}' => ucfirst(fetch_auction('bid_details', $rfpid)),
                                                        '{{bidends}}' => print_date(fetch_auction('date_end', $rfpid), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0),
                                                        '{{message}}' => $comment,
                                                        '{{url}}' => $url,
                                                        '{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
                                                ));
                                                $ilance->email->send();
                                        }        
                                }
                        }
                }
        }
        
        /**
        * Function to handle invitation logic for moderated and non-moderated auctions for externally invited users.
        * This function will also be called when a user is setting a draft auction to open and any invted users will
        * then get email invitation notice.
        *
        * @param       string       category type (service/product)
        * @param       integer      project id
        * @param       integer      user id
        * @param       string       project title
        * @param       string       bid details
        * @param       string       end date
        * @param       array        invitation list
        * @param       string       invitation message
        * @param       bool         do not send email? (default false)
        *
        * @return      nothing
        */
        function dispatch_external_members_email($cattype = 'service', $rfpid = 0, $userid = 0, $project_title = '', $bid_details = '', $end_date = '', $invitelist = array(), $invitemessage = '', $skipemailprocess = 0)
        {
                global $ilance, $ilpage, $ilconfig, $phrase;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                // send out invitations based on any email address and first names supplied on the auction posting interface
                if (isset($invitelist) AND !empty($invitelist) AND is_array($invitelist))
                {
                        foreach ($invitelist['email'] as $key => $email)
                        {
                                $name = $invitelist['name']["$key"];
                                if (!empty($email) AND !empty($name))
                                {
                                        if ($email == $_SESSION['ilancedata']['user']['email'] OR is_valid_email($email) == false)
                                		//if (fetch_user('user_id', '', '', $email) > 0 OR $email == $_SESSION['ilancedata']['user']['email'] OR is_valid_email($email) == false)
                                        {
                                                // don't send if:
                                                // 1. if user is sending invitation to himself
                                                // 2. if user email is email being used to send invitation to himself
                                                // 3. if is_valid_email() determines that email is bad or not formatted properly
                                        }
                                        else
                                        {
                                        		$inv_user_id = (fetch_user('user_id', '', '', $email) > 0) ? fetch_user('user_id', '', '', $email) : '-1';
                                                // todo: detect is seo is enabled
                                                $url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&id=' . intval($rfpid) . '&invited=1&e=' . $email;
                                                
                                                $comment = $phrase['_no_message_was_provided'];
                                                if (isset($invitemessage) AND !empty($invitemessage))
                                                {
                                                        $comment = strip_tags($invitemessage);
                                                }
                                                
                                                $sql3 = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "project_invitations
                                                        WHERE email = '" . trim($ilance->db->escape_string($email)) . "'
                                                            AND project_id = '" . intval($rfpid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql3) == 0)
                                                {
                                                        // invited users don't exist for this auction.. invite them
                                                        if ($cattype == 'service')
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "project_invitations
                                                                        (id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, bid_placed)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($rfpid) . "',
                                                                        '" . intval($userid) . "',
                                                                        '" . intval($inv_user_id) . "',
                                                                        '" . $ilance->db->escape_string($email) . "',
                                                                        '" . $ilance->db->escape_string($name) . "',
                                                                        '" . $ilance->db->escape_string($comment) . "',
                                                                        '" . DATETIME24H . "',
                                                                        'no')
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else if ($cattype == 'product')
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "project_invitations
                                                                        (id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, bid_placed)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($rfpid) . "',
                                                                        '" . intval($inv_user_id) . "',
                                                                        '" . intval($userid) . "',
                                                                        '" . $ilance->db->escape_string($email) . "',
                                                                        '" . $ilance->db->escape_string($name) . "',
                                                                        '" . $ilance->db->escape_string($comment) . "',
                                                                        '" . DATETIME24H . "',
                                                                        'no')
                                                                ", 0, null, __FILE__, __LINE__);        
                                                        }
                                                        
                                                        // are we constructing new auction from an API call?
                                                        if ($skipemailprocess == 0)
                                                        {
                                                                // no api being used, proceed to dispatching email
                                                                $ilance->email->mail = $email;
                                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                                
                                                                $ilance->email->get('invited_to_place_bid');		
                                                                $ilance->email->set(array(
                                                                        '{{invitee}}' => ucfirst($name),
                                                                        '{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
                                                                        '{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
                                                                        '{{username}}' => $_SESSION['ilancedata']['user']['username'],
                                                                        '{{projectname}}' => $project_title,
                                                                        '{{bidprivacy}}' => ucfirst($bid_details),
                                                                        '{{bidends}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0),
                                                                        '{{message}}' => $comment,
                                                                        '{{url}}' => $url,
                                                                        '{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
                                                                ));
                                                                
                                                                $ilance->email->send();
                                                        }
                                                }
                                                else
                                                {
                                                        // this invited user was already invited.. send email..
                                                        if ($skipemailprocess == 0)
                                                        {
                                                                // no api being used, proceed to dispatching email
                                                                $ilance->email->mail = $email;
                                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                                
                                                                $ilance->email->get('invited_to_place_bid');		
                                                                $ilance->email->set(array(
                                                                        '{{invitee}}' => ucfirst($name),
                                                                        '{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
                                                                        '{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
                                                                        '{{username}}' => $_SESSION['ilancedata']['user']['username'],
                                                                        '{{projectname}}' => $project_title,
                                                                        '{{bidprivacy}}' => ucfirst($bid_details),
                                                                        '{{bidends}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0),
                                                                        '{{message}}' => $comment,
                                                                        '{{url}}' => $url,
                                                                        '{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
                                                                ));
                                                                
                                                                $ilance->email->send();
                                                        }
                                                }
                                        }
                                }
                        }
                }
                else
                {
                        // invite list is empty (we must be opening a previously saved draft)
                        // check if we have any externally invited users to dispatch email to
                        $sql3 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "project_invitations
                                WHERE project_id = '" . intval($rfpid) . "'
                                        AND email != ''
                                        AND name != ''
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql3) > 0)
                        {
                                while ($res3 = $ilance->db->fetch_array($sql3))
                                {
                                        // are we constructing new auction from an API call?
                                        if ($skipemailprocess == 0)
                                        {
                                                $comment = $phrase['_no_message_was_provided'];
                                                if (!empty($res3['invite_message']))
                                                {
                                                        $comment = strip_tags($res3['invite_message']);
                                                }
                                                
                                                // todo: detect is seo is enabled
                                                $url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&amp;id=' . intval($rfpid) . '&amp;invited=1&amp;e=' . $res3['email'];
                                                
                                                $ilance->email->mail = $res3['email'];
                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                
                                                $ilance->email->get('invited_to_place_bid');		
                                                $ilance->email->set(array(
                                                        '{{invitee}}' => ucfirst($res3['name']),
                                                        '{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
                                                        '{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
                                                        '{{username}}' => $_SESSION['ilancedata']['user']['username'],
                                                        '{{projectname}}' => $project_title,
                                                        '{{bidprivacy}}' => ucfirst($bid_details),
                                                        '{{bidends}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0),
                                                        '{{message}}' => $comment,
                                                        '{{url}}' => $url,
                                                        '{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
                                                ));
                                                
                                                $ilance->email->send();
                                        }        
                                }
                        }
                }
        }
        
        /**
        * Function to process selected auction enhancements which also creates necessary transactions.
        * Additionally, this function cooperates with the admin defined tax logic for listing enhancements.
        * This function will also update the invoice to isenhancementfee = '1' on successful transaction.
        *
        * @param       array        enhancements array selected during the posting of the auction
        * @param       integer      user id posting auction
        * @param       integer      auction id
        * @param       string       mode (insert or update) for letting this work when updating auction as well
        * @param       string       category type (service or product)
        *
        * @return      nothing
        */
        function process_listing_enhancements_transaction($enhancements = array(), $userid = 0, $rfpid = 0, $mode = 'insert', $cattype = '')
        {
                global $ilance, $ilconfig, $phrase;
                
                if ($mode == 'insert')
                {
                        $options = array(
                                'bold' => 0,
                                'highlite' => 0,
                                'featured' => 0,
                                'autorelist' => 0,
                                'buynow' => 0,
                                'reserve' => 0,
                                'video' => 0,
                        );
                }
                else if ($mode == 'update')
                {
                        $options = array(
                                'bold' => (isset($ilance->GPC['old']['bold']) ? $ilance->GPC['old']['bold'] : '0'),
                                'highlite' => (isset($ilance->GPC['old']['highlite']) ? $ilance->GPC['old']['highlite'] : '0'),
                                'featured' => (isset($ilance->GPC['old']['featured']) ? $ilance->GPC['old']['featured'] : '0'),
                                'autorelist' => (isset($ilance->GPC['old']['autorelist']) ? $ilance->GPC['old']['autorelist'] : '0'),
                                'buynow' => (isset($ilance->GPC['old']['buynow']) ? $ilance->GPC['old']['buynow'] : '0'),
                                'reserve' => (isset($ilance->GPC['old']['reserve']) ? $ilance->GPC['old']['reserve'] : '0'),
                                'video' => ((isset($ilance->GPC['old']['description_videourl']) AND !empty($ilance->GPC['old']['description_videourl'])) ? '1' : '0'),
                        );
                }
                
                $sumfees = 0;
                $htmlenhancements = '';
                
                foreach ($enhancements AS $enhancement => $value)
                {
                        // #### bold ###########################################
                        if (isset($enhancement) AND $enhancement == 'bold')
                        {
                                $htmlenhancements .= $phrase['_bold'] . ', ';
                                $options['bold'] = 0;
                                
                                if ($cattype == 'service')
                                {
                                        if ($ilconfig['serviceupsell_boldfees'])
                                        {
                                                $sumfees += $ilconfig['serviceupsell_boldfee'];
                                        }
                                        if ($ilconfig['serviceupsell_boldactive'])
                                        {
                                                $options['bold'] = 1;
                                        }
                                }
                                else if ($cattype == 'product')
                                {
                                        if ($ilconfig['productupsell_boldfees'])
                                        {
                                                $sumfees += $ilconfig['productupsell_boldfee'];
                                        }
                                        if ($ilconfig['productupsell_boldactive'])
                                        {
                                                $options['bold'] = 1;
                                        }
                                }
                        }
                        
                        // #### highlight ######################################
                        if (isset($enhancement) AND $enhancement == 'highlight')
                        {
                                $htmlenhancements .= $phrase['_listing_highlight'] . ', ';
                                $options['highlite'] = 0;
                                
                                if ($cattype == 'service')
                                {
                                        if ($ilconfig['serviceupsell_highlightfees'])
                                        {
                                                $sumfees += $ilconfig['serviceupsell_highlightfee'];
                                        }
                                        if ($ilconfig['serviceupsell_highlightactive'])
                                        {
                                                $options['highlite'] = 1;
                                        }
                                }
                                else if ($cattype == 'product')
                                {
                                        if ($ilconfig['productupsell_highlightfees'] AND $ilconfig['productupsell_highlightactive'])
                                        {
                                                $sumfees += $ilconfig['productupsell_highlightfee'];
                                        }
                                        if ($ilconfig['productupsell_highlightactive'])
                                        {
                                                $options['highlite'] = 1;
                                        }
                                }
                        }
                        
                        // #### featured #######################################
                        if (isset($enhancement) AND $enhancement == 'featured')
                        {
                                $htmlenhancements .= $phrase['_featured_homepage'] . ' - ' . $ilconfig['serviceupsell_featuredlength'] . ' ' . $phrase['_days'] . ', ';
                                $options['featured'] = 0;
                                
                                if ($cattype == 'service')
                                {
                                        if ($ilconfig['serviceupsell_featuredfees'])
                                        {
                                                $sumfees += $ilconfig['serviceupsell_featuredfee'];
                                        }
                                        if ($ilconfig['serviceupsell_featuredactive'])
                                        {
                                                $options['featured'] = 1;
                                        }
                                }
                                else if ($cattype == 'product')
                                {
                                        if ($ilconfig['productupsell_featuredfees'])
                                        {
                                                $sumfees += $ilconfig['productupsell_featuredfee'];
                                        }
                                        if ($ilconfig['productupsell_featuredactive'])
                                        {
                                                $options['featured'] = 1;
                                        }
                                }
                        }
                        
                        // #### auto-relist ####################################
                        if (isset($enhancement) AND $enhancement == 'autorelist')
                        {
                                $htmlenhancements .= $phrase['_autorelist'] . ', ';
                                $options['autorelist'] = 0;
                                
                                if ($cattype == 'service')
                                {
                                        if ($ilconfig['serviceupsell_autorelistfees'])
                                        {
                                                $sumfees += $ilconfig['serviceupsell_autorelistfee'];
                                        }
                                        if ($ilconfig['serviceupsell_autorelistactive'])
                                        {
                                                $options['autorelist'] = 1;
                                        }
                                }
                                else if ($cattype == 'product')
                                {
                                        if ($ilconfig['productupsell_autorelistfees'])
                                        {
                                                $sumfees += $ilconfig['productupsell_autorelistfee'];
                                        }
                                        if ($ilconfig['productupsell_autorelistactive'])
                                        {
                                                $options['autorelist'] = 1;
                                        }
                                }
                        }
                        // #### buy now price (product) ########################
                        if (isset($enhancement) AND $enhancement == 'buynow')
                        {
                                $htmlenhancements .= $phrase['_buy_now_price'] . ', ';
                                $options['buynow'] = 0;
                                
                                if ($cattype == 'product')
                                {
                                        if ($ilconfig['productupsell_buynowcost'] > 0)
                                        {
                                                $sumfees += $ilconfig['productupsell_buynowcost'];
                                                $options['buynow'] = 1;
                                        }
                                }
                        }
                        // #### reserve price (product) ########################
                        if (isset($enhancement) AND $enhancement == 'reserve')
                        {
                                $htmlenhancements .= $phrase['_reserve_price'] . ', ';
                                $options['reserve'] = 0;
                                
                                if ($cattype == 'product')
                                {
                                        if ($ilconfig['productupsell_reservepricecost'] > 0)
                                        {
                                                $sumfees += $ilconfig['productupsell_reservepricecost'];
                                                $options['reserve'] = 1;
                                        }
                                }
                        }
                }
                
                // determine how many additional slideshow pictures were uploaded
                $pictures = 0;
                if ($ilconfig['productupsell_slideshowcost'] > 0 AND $cattype == 'product')
                {
                        $sql = $ilance->db->query("
                                SELECT COUNT(*) AS pictures
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = 'slideshow'
                                        AND project_id = '" . intval($rfpid) . "'
                                        AND invoiceid = '0'
                        ");
                        $res = $ilance->db->fetch_array($sql);
                        $pictures = $res['pictures'];
                        if ($pictures > 0)
                        {
                                $sumfees += ($ilconfig['productupsell_slideshowcost'] * $pictures);
                                $htmlenhancements .= $phrase['_photo_slideshow_media'] . ' (' . $pictures . ' ' . $phrase['_pictures_lower'] . '), ';
                        }
                }
                
                if (($ilconfig['serviceupsell_videodescriptioncost'] > 0 OR $ilconfig['productupsell_videodescriptioncost'] > 0) AND (isset($ilance->GPC['old']['description_videourl']) AND !empty($ilance->GPC['old']['description_videourl']) OR isset($ilance->GPC['description_videourl']) AND !empty($ilance->GPC['description_videourl'])))
                {
                        $htmlenhancements .= $phrase['_video'] . ', ';
                        
                        if ($cattype == 'service')
                        {
                                if ($ilconfig['serviceupsell_videodescriptioncost'] > 0)
                                {
                                        $sumfees += $ilconfig['serviceupsell_videodescriptioncost'];
                                }
                        }
                        else if ($cattype == 'product')
                        {
                                if ($ilconfig['productupsell_videodescriptioncost'] > 0)
                                {
                                        $sumfees += $ilconfig['productupsell_videodescriptioncost'];
                                }
                        }
                }
                
                if (!empty($htmlenhancements))
                {
                        $htmlenhancements = mb_substr($htmlenhancements, 0, -2);
                }
                                           
                if ($sumfees > 0)
                {
                        // does owner have sufficient funds?
                        $sql = $ilance->db->query("
                                SELECT available_balance, total_balance, autopayment
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $resaccount = $ilance->db->fetch_array($sql);
                                
                                $ilance->accounting = construct_object('api.accounting');
                                $ilance->tax = construct_object('api.tax');
                                
                                // #### CREATE PAID TRANSACTION ################
                                if ($resaccount['available_balance'] >= $sumfees AND $resaccount['autopayment'])
                                {
                                        $transactionid = construct_transaction_id();
					
                                        $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                intval($rfpid),
                                                0,
                                                intval($userid),
                                                0,
                                                0,
                                                0,
                                                $phrase['_auction'] . ' #' . intval($rfpid) . ' : ' . $htmlenhancements,
                                                sprintf("%01.2f", $sumfees),
                                                sprintf("%01.2f", $sumfees),
                                                'paid',
                                                'debit',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                DATETIME24H,
                                                '',
                                                0,
                                                0,
                                                1,
                                                $transactionid,
                                                0,
                                                0
                                        );
                                        
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET isenhancementfee = '1' , statement_date= '" . fetch_auction('date_end',$rfpid) . "'
                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        if ($pictures > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "attachment
                                                        SET invoiceid = '" . intval($invoiceid) . "'
                                                        WHERE attachtype = 'slideshow'
                                                                AND project_id = '" . intval($rfpid) . "'
                                                                AND invoiceid = '0'
                                                ", 0, null, __FILE__, __LINE__);        
                                        }
                                        
                                        // track spending habits
                                        insert_income_spent(intval($userid), sprintf("%01.2f", $sumfees), 'credit');
                                        
                                        // track this user as paid for enhancements so his/her referral can see this from their my cp
                                        update_referral_action('enhancements', intval($userid));
                                        
                                        if ($ilance->tax->is_taxable($userid, 'enhancements') AND $sumfees > 0)
                                        {
                                                // fetch tax amount to charge for this invoice type
                                                $taxamount = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 0);
                                                
                                                // fetch total amount to hold within the "totalamount" field
                                                $totalamount = ($sumfees + $taxamount);
                                                
                                                // fetch tax bit to display when outputing tax infos
                                                $taxinfo = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 1);
                                                
                                                // member is taxable for this invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET istaxable = '1',
                                                        totalamount = '" . sprintf("%01.2f", $totalamount) . "',
                                                        taxamount = '" . sprintf("%01.2f", $taxamount) . "',
                                                        taxinfo = '" . $ilance->db->escape_string($taxinfo) . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // debit funds from online account balance
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = available_balance - $totalamount,
                                                        total_balance = total_balance - $totalamount
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else
                                        {
                                                // debit funds from online account balance
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = available_balance - " . sprintf("%01.2f", $sumfees) . ",
                                                        total_balance = total_balance - " . sprintf("%01.2f", $sumfees) . "
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                                
                                // #### CREATE UNPAID TRANSACTION ##############
                                else
                                {
                                        $transactionid = construct_transaction_id();
					
                                        $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                intval($rfpid),
                                                0,
                                                intval($userid),
                                                0,
                                                0,
                                                0,
                                                $phrase['_auction'] . ' #' . intval($rfpid) . ' : ' . $htmlenhancements,
                                                sprintf("%01.2f", $sumfees),
                                                0,
                                                'unpaid',
                                                'debit',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                '',
                                                '',
                                                0,
                                                0,
                                                1,
                                                $transactionid,
                                                0,
                                                0
                                        );
                                        
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET isenhancementfee = '1' , statement_date= '" . fetch_auction('date_end',$rfpid) . "'
                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        if ($pictures > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "attachment
                                                        SET invoiceid = '" . intval($invoiceid) . "'
                                                        WHERE attachtype = 'slideshow'
                                                                AND project_id = '" . intval($rfpid) . "'
                                                                AND invoiceid = '0'
                                                ", 0, null, __FILE__, __LINE__);        
                                        }
                                        
                                        if ($ilance->tax->is_taxable($userid, 'enhancements') AND $sumfees > 0)
                                        {
                                                // fetch tax amount to charge for this invoice type
                                                $taxamount = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 0);
                                                
                                                // fetch total amount to hold within the "totalamount" field
                                                $totalamount = ($sumfees + $taxamount);
                                                
                                                // fetch tax bit to display when outputing tax infos
                                                $taxinfo = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 1);
                                                
                                                // member is taxable for this invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET istaxable = '1',
                                                        totalamount = '" . sprintf("%01.2f", $totalamount) . "',
                                                        taxamount = '" . sprintf("%01.2f", $taxamount) . "',
                                                        taxinfo = '" . $ilance->db->escape_string($taxinfo) . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }        
                                }
                        }
                }
                
                return $options;
        }
        
        /**
        * Function to insert a new user into the auction invitation table.
        *
        * @param       integer      owner id
        * @param       integer      user id
        * @param       integer      project id
        * @param       bool         no email flag (true or false)
        * @param       string       invitation type (service or product)
        *
        * @return      nothing
        */
        function insert_auction_invitation($ownerid = 0, $userid = 0, $projectid = 0, $noemail = 0, $invitetype = 'service')
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilpage, $ilconfig, $tstart, $finaltime;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                if ($invitetype == 'service')
                {
                        $field1 = 'buyer_user_id';
                        $field2 = 'seller_user_id';
                }
                else
                {
                        $field1 = 'seller_user_id';
                        $field2 = 'buyer_user_id';
                }
                
                if ($ownerid > 0 AND $userid > 0 AND $projectid > 0)
                {
                        $presql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "project_invitations
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND $field1 = '" . intval($ownerid) . "'
                                    AND $field2 = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($presql) == 0)
                        {
                                $sqlauction = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects
                                        WHERE project_id = '" . intval($projectid) . "'
                                            AND (status = 'draft' OR status = 'open')
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlauction) > 0)
                                {
                                        // invite member
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "project_invitations
                                                (id, project_id, $field1, $field2, date_of_invite, bid_placed)
                                                VALUES(
                                                NULL,
                                                '" . intval($projectid) . "',
                                                '" . intval($ownerid) . "',
                                                '" . intval($userid) . "',
                                                '".DATETIME24H."',
                                                'no')
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($noemail == 0)
                                        {
                                                $sql_project = $ilance->db->query("
                                                        SELECT cid, filtered_budgetid, project_title, bids, description, project_id
                                                        FROM " . DB_PREFIX . "projects
                                                        WHERE project_id = '" . intval($projectid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_project) > 0)
                                                {
                                                        $project = $ilance->db->fetch_array($sql_project);
                                                        $budget = $this->construct_budget_overview($project['cid'], $project['filtered_budgetid']);
                                                        
                                                        // email potential service provider
                                                        $ilance->email->mail = fetch_user('email', $userid);
                                                        $ilance->email->slng = fetch_user_slng($userid);
                                        
                                                        $ilance->email->get('invite_message_from_buyer');		
                                                        $ilance->email->set(array(
                                                                '{{provider}}' => fetch_user('username', $userid),
                                                                '{{buyer}}' => fetch_user('username', $ownerid),
                                                                '{{project_title}}' => strip_vulgar_words(stripslashes($project['project_title'])),
                                                                '{{bids}}' => $project['bids'],
                                                                '{{project_budget}}' => $budget,
                                                                '{{project_description}}' => strip_vulgar_words(stripslashes($project['description'])),
                                                                '{{p_id}}' => $project['project_id'],
                                                                
                                                        ));
                                                        
                                                        $ilance->email->send();
                                                        
                                                        // email admin
                                                        $ilance->email->mail = SITE_EMAIL;
                                                        $ilance->email->slng = fetch_site_slng();
                                                        
                                                        $ilance->email->get('invite_message_from_buyer_admin');		
                                                        $ilance->email->set(array(
                                                                '{{provider}}' => fetch_user('username', $userid),
                                                                '{{buyer}}' => fetch_user('username', $ownerid),
                                                                '{{project_title}}' => strip_vulgar_words(stripslashes($project['project_title'])),
                                                                '{{bids}}' => $project['bids'],
                                                                '{{project_budget}}' => $budget,
                                                                '{{project_description}}' => strip_vulgar_words(stripslashes($project['description'])),
                                                                '{{p_id}}' => $project['project_id'],
                                                                
                                                        ));
                                                        
                                                        $ilance->email->send();
                                                }                                                
                                        }
                                }
                        }    
                }
        }
        
        /**
        * Function to fetch total number of invited users to a particular auction
        *
        * @param       integer       category id
        *
        * @return      integer       Returns the number of invitees
        */
        function fetch_invited_users_count($projectid = 0)
        {
                global $ilance;
                
                $count = 0;
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $count = $res['count'];
                }
                                
                return $count;
        }
        
        /**
        * Function to fetch the the budgetgroup name for a particular category
        *
        * @param       integer       category id
        *
        * @return      string        Budget group name
        */
        function fetch_category_budgetgroup($cid = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return $res['budgetgroup'];
                }
                else
                {
                        return 'default';
                }
        }
        
        /**
        * Function for creating a new insertion fee transaction which is usually executed during the initial posting
        * of a service or product auction.  This function will attempt to debit the amount owing from the user's
        * account balance (if funds available) otherwise it will create an unpaid transaction and force the auction to be
        * hidden until payment is completed.  This function takes into consideration a user with insertion fees exemption.
        *
        * @param       integer      category id
        * @param       string       category type (service or product) default is service
        * @param       string       amount to process
        * @param       integer      project id
        * @param       integer      user id
        * @param       bool         is a budget range type insertion group (true or false)
        * @param       integer      budget range id that is selected
        */
        function calculate_insertion_fee($cid = 0, $cattype = 'service', $amount = 0, $pid = 0, $userid = 0, $isbudgetrange = 0, $filtered_budgetid = 0)
        {
                global $ilance, $myapi, $phrase;
                
                $ilance->accounting = construct_object('api.accounting');
                $ilance->subscription = construct_object('api.subscription');
                
                $fee = $fee2 = 0;
                
                // #### PRODUCT INSERTION FEE ##################################
                if ($cattype == 'product')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '" . $ilance->db->escape_string($ilance->categories->insertiongroup($cid)) . "'
                                    AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $found = 0;
                                while ($rows = $ilance->db->fetch_array($sql))
                                {
                                        if ($rows['insertion_to'] == '-1')
                                        {
                                                if ($amount >= $rows['insertion_from'] AND $rows['insertion_to'] == '-1')
                                                {
                                                        $found = 1;
                                                        $fee += $rows['amount'];
                                                }
                                        }
                                        else
                                        {
                                                if ($amount >= $rows['insertion_from'] AND $amount <= $rows['insertion_to'])
                                                {
                                                        $found = 1;
                                                        $fee += $rows['amount'];
                                                }
                                        }
                                }
                                if ($found == 0)
                                {
                                        $fee = 0;
                                }           
                        }
                        else
                        {
                                $fee = 0;
                        }
                }
                
                // #### SERVICE INSERTION FEE ##################################
                else if ($cattype == 'service')
                {
                        // #### BUDGET RANGE INSERTION FEES ####################
                        if ($isbudgetrange AND $filtered_budgetid > 0)
                        {
                                $insertiongroup = $ilance->db->fetch_field(DB_PREFIX . "budget", "budgetid = '" . intval($filtered_budgetid) . "'", "insertiongroup");
                                
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "insertion_fees
                                        WHERE groupname = '" . $ilance->db->escape_string($insertiongroup) . "'
                                            AND state = '" . $ilance->db->escape_string($cattype) . "'
                                        ORDER BY sort ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        // our budget range has some insertion group defined ..
                                        while ($rows = $ilance->db->fetch_array($sql))
                                        {
                                                $fee += $rows['amount'];
                                        }
                                }
                        }
                        else
                        {
                                // buyer decides to set project as budget non-disclosed (does not select a pre-defined budget range)
                                // is admin charging fees in this category for non-disclosed auctions?
                                $ndfee = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "nondisclosefeeamount");
                                if ($ndfee > 0)
                                {
                                        $fee = $ndfee;
                                }
                                unset($ndfee);
                        }
                        
                        // #### CATEGORY BASE INSERTION FEES ###################
			$insertiongr = $ilance->categories->insertiongroup($cid);
                        if (!empty($insertiongr))
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "insertion_fees
                                        WHERE groupname = '" . $ilance->db->escape_string($insertiongr) . "'
                                            AND state = '" . $ilance->db->escape_string($cattype) . "'
                                        ORDER BY sort ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        while ($rows = $ilance->db->fetch_array($sql))
                                        {
                                                $fee += $rows['amount'];
                                                $fee2 += $rows['amount'];
                                        }
                                }
                        }
			
			unset($insertiongr, $fee2);
                }
                
                // check if we're exempt from insertion fees
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
                {
                        $fee = 0;
                }
                
                return $fee;
        }
        
        /**
        * Function for creating a new insertion fee transaction which is usually executed during the initial posting
        * of a service or product auction.  This function will attempt to debit the amount owing from the user's
        * account balance (if funds available) otherwise it will create an unpaid transaction and force the auction to be
        * hidden until payment is completed.  This function takes into consideration a user with insertion fees exemption.
        *
        * @param       integer      category id
        * @param       string       category type (service or product) default is service
        * @param       string       amount to charge
        * @param       integer      project id
        * @param       integer      user id
        * @param       bool         is a budget range type insertion group (true or false)
        * @param       integer      budget range id that is selected
        * @param       bool         is being called via bulk upload (default false)
        * @param       array        bulk uploaded items listings ids
        */
        function process_insertion_fee_transaction($cid = 0, $cattype = 'service', $amount = 0, $pid = 0, $userid = 0, $isbudgetrange = 0, $filtered_budgetid = 0, $isbulkupload = false, $pids = array())
        {
                global $ilance, $ilpage, $phrase, $ilconfig;
                
                $ilance->accounting = construct_object('api.accounting');
                $ilance->subscription = construct_object('api.subscription');
                
                $fee = $fee2 = 0;
                $feetitle = '';
                
		// #### process single fee transaction #########################
                if ($isbulkupload == false)
                {
                        // #### PRODUCT INSERTION FEE ##################################
                        if ($cattype == 'product')
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "insertion_fees
                                        WHERE groupname = '" . $ilance->db->escape_string($ilance->categories->insertiongroup($cid)) . "'
                                            AND state = '" . $ilance->db->escape_string($cattype) . "'
                                        ORDER BY sort ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $found = 0;
                                        while ($rows = $ilance->db->fetch_array($sql))
                                        {
                                                if ($rows['insertion_to'] == '-1')
                                                {
                                                        if ($amount >= $rows['insertion_from'] AND $rows['insertion_to'] == '-1')
                                                        {
                                                                $found = 1;
                                                                $fee += $rows['amount'];
                                                        }
                                                }
                                                else
                                                {
                                                        if ($amount >= $rows['insertion_from'] AND $amount <= $rows['insertion_to'])
                                                        {
                                                                $found = 1;
                                                                $fee += $rows['amount'];
                                                        }
                                                }
                                        }
                                        if ($found == 0)
                                        {
                                                $fee = 0;
                                        }           
                                }
                                else
                                {
                                        $fee = 0;
                                }
                        }
                        
                        // #### SERVICE INSERTION FEE ##################################
                        else if ($cattype == 'service')
                        {
                                // #### BUDGET RANGE INSERTION FEES ####################
                                if ($isbudgetrange AND $filtered_budgetid > 0)
                                {
                                        $insertiongroup = $ilance->db->fetch_field(DB_PREFIX . "budget", "budgetid = '" . intval($filtered_budgetid) . "'", "insertiongroup");
                                        
                                        $sql = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "insertion_fees
                                                WHERE groupname = '" . $ilance->db->escape_string($insertiongroup) . "'
                                                    AND state = '" . $ilance->db->escape_string($cattype) . "'
                                                ORDER BY sort ASC
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql) > 0)
                                        {
                                                // our budget range has some insertion group defined ..
                                                while ($rows = $ilance->db->fetch_array($sql))
                                                {
                                                        $fee += $rows['amount'];
                                                }
                                                
                                                $feetitle .= $phrase['_budget_range'] . ': ' . $this->fetch_rfp_budget($pid, $showicon = false) . ' - ' . $phrase['_insertion_fee'] . ': ' . $ilance->currency->format($fee) . ', ';
                                        }
                                }
                                else
                                {
                                        // buyer decides to set project as budget non-disclosed (does not select a pre-defined budget range)
                                        // is admin charging fees in this category for non-disclosed auctions?
                                        $ndfee = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "nondisclosefeeamount");
                                        if ($ndfee > 0)
                                        {
                                                $fee = $ndfee;
                                                $feetitle .= $phrase['_budget_range'] . ': ' . $phrase['_non_disclosed'] . ' - ' . $phrase['_insertion_fee'] . ': ' . $ilance->currency->format($fee) . ', ';
                                        }
                                        unset($ndfee);
                                }
                                
                                // #### CATEGORY BASE INSERTION FEES ###################
				$insertiongr = $ilance->categories->insertiongroup($cid);
                                if (!empty($insertiongr))
                                {
                                        $sql = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "insertion_fees
                                                WHERE groupname = '" . $ilance->db->escape_string($insertiongr) . "'
                                                    AND state = '" . $ilance->db->escape_string($cattype) . "'
                                                ORDER BY sort ASC
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql) > 0)
                                        {
                                                while ($rows = $ilance->db->fetch_array($sql))
                                                {
                                                        $fee += $rows['amount'];
                                                        $fee2 += $rows['amount'];
                                                }
						
                                                $feetitle .= $phrase['_category'] . ': ' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cattype, $cid) . ' - ' . $phrase['_insertion_fee'] . ': ' . $ilance->currency->format($fee2) . ', ';
                                        }
                                }
				
				unset($insertiongr, $fee2);
                        }
                        
                        // chop trailing ", " from the ending of the generated fee title
                        if (!empty($feetitle))
                        {
                                $feetitle = mb_substr($feetitle, 0, -2);
                        }
                        else if (empty($feetitle))
                        {
                                $feetitle = $phrase['_insertion_fee'];
                        }               
                        
                        // check if we're exempt from insertion fees
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
                        {
                                $fee = 0;
                        }
                        
                        // try to debit the account of this user
                        if ($fee > 0)
                        {
				// #### taxes on insertion fees ################
				$ilance->tax = construct_object('api.tax');
				$extrainvoicesql = '';
				if ($ilance->tax->is_taxable(intval($userid), 'insertionfee'))
				{
					// #### fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'insertionfee', 0);
					
					// #### fetch total amount to hold within the "totalamount" field
					$totalamount = ($fee + $taxamount);
					
					// #### fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($userid), $fee, 'insertionfee', 1);
					
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
				}				
				
                                // does owner have sufficient funds?
                                $sqlaccount = $ilance->db->query("
                                        SELECT available_balance, autopayment
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlaccount) > 0)
                                {
                                        $resaccount = $ilance->db->fetch_array($sqlaccount);
                                        if ($resaccount['available_balance'] >= $fee AND $resaccount['autopayment'])
                                        {
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        intval($pid),
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_insertion_fee'] . ' #' . intval($pid) . ' : ' . $feetitle,
                                                        sprintf("%01.2f", $fee),
                                                        sprintf("%01.2f", $fee),
                                                        'paid',
                                                        'debit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                
                                                // update invoice mark as insertion fee invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET $extrainvoicesql
							isif = '1'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // update auction with insertion fee
                                                // set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET insertionfee = '" . sprintf("%01.2f", $fee) . "',
                                                        isifpaid = '1',
                                                        ifinvoiceid = '" . intval($invoiceid) . "'
                                                        WHERE project_id = '" . intval($pid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // adjust account balance
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = available_balance - $fee,
                                                        total_balance = total_balance - $fee
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // track spending habits
                                                insert_income_spent(intval($userid), sprintf("%01.2f", $fee), 'credit');
                                                
                                                // #### REFERRAL SYSTEM TRACKER ############################
                                                update_referral_action('ins', intval($userid));
                                        }
                                        else
                                        {
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        intval($pid),
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_auction'] . ' #' . intval($pid) . ' : ' . $feetitle,
                                                        sprintf("%01.2f", $fee),
                                                        '',
                                                        'unpaid',
                                                        'debit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        '',
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                
                                                // update invoice mark as insertion fee invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET $extrainvoicesql
							isif = '1'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // update auction with insertion fee
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET insertionfee = '" . sprintf("%01.2f", $fee) . "',
                                                        isifpaid = '0',
                                                        ifinvoiceid = '" . intval($invoiceid) . "'
                                                        WHERE project_id = '" . intval($pid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                        }                        
                }
                
		// #### process bulk upload fee transaction ####################
		else
                {
                        // #### try to debit the account of this user
                        if ($amount > 0)
                        {
                                // #### does owner have sufficient funds?
                                $sqlaccount = $ilance->db->query("
                                        SELECT available_balance, autopayment
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlaccount) > 0)
                                {
                                        $resaccount = $ilance->db->fetch_array($sqlaccount);
					$comments = '';
					
                                        if ($resaccount['available_balance'] >= $amount AND $resaccount['autopayment'])
                                        {
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        0,
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_bulk_upload_fee'],
                                                        sprintf("%01.2f", $amount),
                                                        sprintf("%01.2f", $amount),
                                                        'paid',
                                                        'debit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                
                                                // update invoice mark as insertion fee invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET isif = '1'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                if (isset($pids) AND is_array($pids) AND count($pids) > 0)
                                                {
                                                        foreach ($pids AS $pid)
                                                        {
                                                                if (isset($pid) AND $pid > 0)
                                                                {
									$amountsplit = ($amount / count($pids));
									$comments .= "<hr size=\"1\" width=\"100%\" style=\"color:#cccccc\" /><div style=\"padding-top:3px\" class=\"blue\">" . $phrase['_item'] . " # $pid: <a href=\"" . HTTP_SERVER . $ilpage['merch'] . "?id=" . $pid . "\">" . fetch_auction('project_title', $pid) . "</a> (" . $ilance->currency->format($amountsplit) . ")</div>";
									
                                                                        // update auction with insertion fee
                                                                        // set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET insertionfee = '" . sprintf("%01.2f", $amountsplit) . "',
                                                                                isifpaid = '1',
                                                                                ifinvoiceid = '" . intval($invoiceid) . "'
                                                                                WHERE project_id = '" . intval($pid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                }
                                                        }
                                                }
                                                
                                                // adjust account balance
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = available_balance - $amount,
                                                        total_balance = total_balance - $amount
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // track spending habits
                                                insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
                                                
                                                // #### REFERRAL SYSTEM TRACKER ############################
                                                update_referral_action('ins', intval($userid));
                                        }
                                        else
                                        {
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        0,
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_bulk_upload_fee'],
                                                        sprintf("%01.2f", $amount),
                                                        '',
                                                        'unpaid',
                                                        'debit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        '',
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                
                                                // update invoice mark as insertion fee invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET isif = '1'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                if (isset($pids) AND is_array($pids) AND count($pids) > 0)
                                                {
                                                        foreach ($pids AS $pid)
                                                        {
                                                                if (isset($pid) AND $pid > 0)
                                                                {
                                                                        $amountsplit = ($amount / count($pids));
									$comments .= "<hr size=\"1\" width=\"100%\" style=\"color:#cccccc\" /><div style=\"padding-top:3px\" class=\"blue\">" . $phrase['_item'] . " # $pid: <a href=\"" . HTTP_SERVER . $ilpage['merch'] . "?id=" . $pid . "\">" . fetch_auction('project_title', $pid) . "</a> (" . $ilance->currency->format($amountsplit) . ")</div>";
                                                                        
                                                                        // update auction with insertion fee
                                                                        // set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET insertionfee = '" . sprintf("%01.2f", $amountsplit) . "',
                                                                                isifpaid = '0',
                                                                                ifinvoiceid = '" . intval($invoiceid) . "'
                                                                                WHERE project_id = '" . intval($pid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                }
                                                        }
                                                }
                                        }
					
					// #### update transaction showing split payment details in comment area
					if (!empty($comments))
					{
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET custommessage = '" . $ilance->db->escape_string($comments) . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
					}
                                }
                        }        
                }
        }
        
        /**
        * Function to print inline all invited users for a particular service auction
        *
        * @param       integer       project id
        * @param       integer       owner id
        * @param       string        bid privacy details (open, sealed, blind, full)
        *
        * @return      string        Returns HTML formatted invited users list
        */
        function print_invited_users($projectid = 0, $ownerid = 0, $bid_details)
        {
                global $ilance, $ilconfig, $phrase;
                
                $invite_list = '';
                
                $externalbidders = $registeredbidders = 0;
                
                $sql = $ilance->db->query("
                        SELECT seller_user_id, bid_placed
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if ($res['seller_user_id'] != '-1')
                                {
                                        // inviting registered members only
                                        $sqlvendor = $ilance->db->query("
                                                SELECT user_id
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res['seller_user_id'] . "'
                                                    AND status = 'active'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sqlvendor) > 0)
                                        {
                                                $resvendor = $ilance->db->fetch_array($sqlvendor);
                                                $invite_list .= ($res['bid_placed'] == '0') ? '<span class="blue">' . fetch_user('username', $resvendor['user_id']) . '</span> <span class="smaller gray">[ <em>' . $phrase['_not_placed'] . '</em> ]</span>, ' : '<span class="blue">' . fetch_user('username', $resvendor['user_id']) . '</span> <span class="smaller gray">[ <strong>' . $phrase['_placed'] . '</strong> ]</span>, ';
                                                $registeredbidders++;
                                        }        
                                }
                                else
                                {
                                        // this bidder appears to be an external bidder
                                        // so we only have their email address to work with...
                                        $externalbidders++;
                                }
                        }        
                }

                if (!empty($invite_list))
                {
                        $invite_list = mb_substr($invite_list, 0, -2);
                }

                if ($externalbidders > 0 OR $registeredbidders > 0)
                {
                        // viewing as admin
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                        {
                                // formatted invited users list display
                                $invite_list = $invite_list . '<ul style="margin:18px; padding:0px;"><li>' . $externalbidders . ' ' . $phrase['_bidders_invited_via_email'] . '</li><li>' . $registeredbidders . ' ' . $phrase['_registered_members_invited'] . '</li></ul>';
                        }
                        // viewing as owner
                        else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $ownerid)
                        {
                                // formatted invited users list display
                                $invite_list = $invite_list . '<ul style="margin:18px; padding:0px;"><li>' . $externalbidders . ' ' . $phrase['_bidders_invited_via_email'] . '</li><li>' . $registeredbidders . ' ' . $phrase['_registered_members_invited'] . '</li></ul>';
                        }
                        // viewing as guest
                        else if (empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $invite_list = '= ' . $phrase['_sealed'] . ' =';
                        }
                        else if (!empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                // viewing as member
                                $invite_list = '= ' . $phrase['_sealed'] . ' =';        
                        }
                }
                else
                {
                        $invite_list = $phrase['_none'];		
                }
                
                return $invite_list;
        }

        /**
        * Function to print inline all invited users for a particular service auction
        *
        * @param       string        profile answer
        * @param       integer       project id
        *
        * @return      nothing
        */        
        function insert_profile_answers($profile_ans, $projectid)
        {
                global $ilance, $ilconfig, $phrase;
                
                $answeredarray = array();
                
                foreach ($profile_ans as $type => $answerarray)
                {
                        if ($type == 'range')
                        {
                                foreach ($answerarray as $questionid => $answers)
                                {
                                        if (!empty($answers) AND is_array($answers) AND $questionid > 0)
                                        {
                                                foreach ($answers as $key => $value)
                                                {
                                                        if (!empty($key) AND !empty($value) AND $value > 0)
                                                        {
                                                                $answeredarray[$questionid][$key] = $value;        
                                                        }
                                                }
                                        }
                                        
                                        if (!empty($answeredarray) AND is_array($answeredarray) AND $questionid > 0 AND !empty($answeredarray[$questionid]['from']) AND !empty($answeredarray[$questionid]['to']))
                                        {
                                                $sqlfield = $ilance->db->query("
                                                        SELECT questionid, project_id, answer
                                                        FROM " . DB_PREFIX . "profile_filter_auction_answers
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND project_id = '" . intval($projectid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlfield) > 0)
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "profile_filter_auction_answers
                                                                SET answer = '" . $ilance->db->escape_string($answeredarray[$questionid]['from'] . '|' . $answeredarray[$questionid]['to']) . "'
                                                                WHERE questionid = '" . intval($questionid) . "'
                                                                AND project_id = '" . intval($projectid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                else
                                                {
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "profile_filter_auction_answers
                                                                (answerid, questionid, project_id, user_id, answer, filtertype, date, visible)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($questionid) . "',
                                                                '" . intval($projectid) . "',
                                                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                                                '" . intval($answeredarray[$questionid]['from']) . '|' . intval($answeredarray[$questionid]['to']) . "',
                                                                'range',
                                                                '" . DATETIME24H . "',
                                                                '1')
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                        }
                                }
                        }
                        else if (mb_ereg('choice_', $type))
                        {
                                foreach ($answerarray as $questionid => $answers)
                                {
                                        if (!empty($answers) AND is_array($answers) AND $questionid > 0)
                                        {
                                                foreach ($answers as $key => $value)
                                                {
                                                        if (!empty($key) AND !empty($value))
                                                        {
                                                                $answeredarray[$questionid][$key] = $value;
                                                        }
                                                }
                                                
                                                if (!empty($answeredarray) AND is_array($answeredarray) AND $questionid > 0)
                                                {
                                                        $sqlfield = $ilance->db->query("
                                                                SELECT questionid, project_id, answer
                                                                FROM " . DB_PREFIX . "profile_filter_auction_answers
                                                                WHERE questionid = '" . intval($questionid) . "'
                                                                    AND project_id = '" . intval($projectid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sqlfield) > 0)
                                                        {
                                                                $res = $ilance->db->fetch_array($sqlfield);
                                                                
                                                                $custom = '';
                                                                if (!empty($res['answer']))
                                                                {
                                                                        $currentanswers = explode('|', $res['answer']);
                                                                        if (!in_array($answeredarray[$questionid]['custom'], $currentanswers))
                                                                        {
                                                                                $custom = $res['answer'] . '|' . $answeredarray[$questionid]['custom'];
                                                                        }
                                                                        else
                                                                        {
                                                                                $custom = $res['answer'];        
                                                                        }
                                                                }
                                                                
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "profile_filter_auction_answers
                                                                        SET answer = '" . $ilance->db->escape_string($custom) . "'
                                                                        WHERE questionid = '" . intval($questionid) . "'
                                                                        AND project_id = '" . intval($projectid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "profile_filter_auction_answers
                                                                        (answerid, questionid, project_id, user_id, answer, filtertype, date, visible)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($questionid) . "',
                                                                        '" . intval($projectid) . "',
                                                                        '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                                                        '" . $ilance->db->escape_string($answeredarray[$questionid]['custom']) . "',
                                                                        'checkbox',
                                                                        '" . DATETIME24H . "',
                                                                        '1')
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }        
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>