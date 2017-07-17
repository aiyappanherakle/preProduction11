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
        'watchlist',
        'registration',
        'search',
        'stores',
        'wantads',
        'subscription',
        'preferences',
        'buying',
        'selling',
        'rfp',
        'javascript'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
        'tabfx',
        'jquery',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION','ajax');
define('SKIP_SESSION', true);
// #### require backend ########################################################
require_once('./functions/config.php');
($apihook = $ilance->api('ajax_start')) ? eval($apihook) : false;
// #### INLINE TEXT INPUT EDITOR ###############################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'inlineedit' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        if (isset($ilance->GPC['action']))
        {
                switch ($ilance->GPC['action'])
                {
                        // #### subscription permissions title #################
                        case 'permission_accesstext':
                        {
                                break;
                        }                
                        // #### subscription permissions description ###########
                        case 'permission_description':
                        {
                                break;
                        }
                        
                        // #### favorite search title ##########################
                        case 'favsearchtitle':
                        {
                                $ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
                                $ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "search_favorites
                                        SET title = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'
                                        WHERE searchid = '" . intval($ilance->GPC['id']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                echo $ilance->GPC['text'];
                                break;
                        }
                        
                        // #### portfolio title ################################
                        case 'portfolio':
                        {
                                $ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
                                $ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
                                
                                $setwhat = "caption = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'";
                                if (stristr($ilance->GPC['id'], '_title'))
                                {
                                        $setwhat = "caption = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'";
                                        $id = explode('_', $ilance->GPC['id']);
                                }
                                else if (stristr($ilance->GPC['id'], '_description'))
                                {
                                        $setwhat = "description = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'";
                                        $id = explode('_', $ilance->GPC['id']);
                                }
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "portfolio
                                        SET $setwhat
                                        WHERE portfolio_id = '" . intval($id[0]) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                echo $ilance->GPC['text'];
                                break;
                        }
                        
                        // #### seller updating shipment tracking number #######
                        case 'sellershiptracking':
                        {
                                $ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
                                $ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
                                
                                echo $ilance->GPC['text'];
                                break;
                        }
                }
                exit();
        }
}
//mycollection VENKAT
if (isset($ilance->GPC['col1']))
{
$pcg=$ilance->GPC['col1'];
$user_id=$_SESSION['ilancedata']['user']['userid'];
$sel1=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "myfav WHERE pcgs=".$pcg." AND user_id=".$user_id."");
$sel2=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "myfav WHERE user_id=".$user_id."");   
$num_fav=$ilance->db->num_rows($sel1);
$num_tot=$ilance->db->num_rows($sel2);
$max=6;
if(!$num_fav)
{
if($num_tot<$max)
{
 //$num_tot1=$ilance->db->num_rows($des);
$sql123=$ilance->db->query("INSERT INTO " . DB_PREFIX . "myfav(pcgs,user_id) VALUES(".$pcg.",".$user_id.")");
 
}
else if($num_tot==$max)
{
//echo "error";
}
} 
else
{
$del=$ilance->db->query("DELETE FROM " . DB_PREFIX . "myfav WHERE pcgs=".$pcg." AND user_id=".$user_id."");
}
header('location:mycollection.php');
}
//VENKAT
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'myself')
{
  refresh(HTTPS_SERVER .'buyer_invoice.php');
exit();
  } 
// #### WATCHLIST ##############################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'watchlist' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_watchlist_start')) ? eval($apihook) : false;        
        
	// #### SAVE WATCHLIST ITEM FOR USER ###################################
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'savewatchlist')
	{		
	}
	
	// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
        if (!empty($ilance->GPC['value']) AND !empty($ilance->GPC['type']))
        {
                $ilance->GPC['value'] = ($ilance->GPC['value'] == 'on' ? 1 : 0);
                $ilance->GPC['type'] = $ilance->GPC['type'];
                switch ($ilance->GPC['type'])
                {
                        case 'lasthour':
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET hourleftnotify = '".intval($ilance->GPC['value'])."', subscribed = '1'
                                        WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->GPC['value'])
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "watchlist
                                                SET subscribed = '1'
                                                WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                        ", 0, null, __FILE__, __LINE__);
                                        $ilance->xml->add_tag('status', 'on');
                                }
                                else
                                {
                                        $ilance->xml->add_tag('status', 'off');
                                }
                                break;
                        }                
                        case 'lowbid':
                        {
                                $sql = $ilance->db->query("
                                        SELECT watching_project_id
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql);
                                        // did this user already place a bid?
                                        $sql2 = $ilance->db->query("
                                                SELECT bidamount
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE user_id = '".intval($_SESSION['ilancedata']['user']['userid'])."'
                                                        AND project_id = '".$res['watching_project_id']."'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "watchlist
                                                        SET lowbidnotify = '".intval($ilance->GPC['value'])."', subscribed = '1' WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->GPC['value'])
                                                {
                                                        $ilance->db->query("UPDATE " . DB_PREFIX . "watchlist SET subscribed = '1' WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'", 0, null, __FILE__, __LINE__);
                                                        $ilance->xml->add_tag('status', 'on');
                                                }
                                                else
                                                {
                                                        $ilance->xml->add_tag('status', 'off');
                                                }
                                        }
                                        else
                                        {
                                                $ilance->xml->add_tag('status', $phrase['_sorry_to_track_lower_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first']);
                                        }
                                }
                                else
                                {
                                        $ilance->xml->add_tag('status', $phrase['_sorry_to_track_lower_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first']);
                                }
                                break;
                        }                
                        case 'highbid':
                        {
                                $sql = $ilance->db->query("
                                        SELECT watching_project_id
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql);
                                        
                                        $sql2 = $ilance->db->query("
                                                    SELECT bidamount
                                                    FROM " . DB_PREFIX . "project_bids
                                                    WHERE user_id = '".intval($_SESSION['ilancedata']['user']['userid'])."'
                                                            AND project_id = '".$res['watching_project_id']."'
                                                    LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "watchlist
                                                        SET highbidnotify = '".intval($ilance->GPC['value'])."',
                                                        subscribed = '1'
                                                        WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->GPC['value'])
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "watchlist
                                                                SET subscribed = '1'
                                                                WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $ilance->xml->add_tag('status', 'on');
                                                }
                                                else
                                                {
                                                        $ilance->xml->add_tag('status', 'off');
                                                }
                                        }
                                        else
                                        {
                                                $ilance->xml->add_tag('status', $phrase['_sorry_to_track_higher_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first']);
                                        }
                                }
                                else
                                {
                                        $ilance->xml->add_tag('status', $phrase['_sorry_to_track_higher_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first']);
                                }
                                break;
                        }
                        case 'subscribed':
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET subscribed = '".intval($ilance->GPC['value'])."'
                                        WHERE watchlistid = '".intval($ilance->GPC['watchlistid'])."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->GPC['value'])
                                {
                                        $ilance->xml->add_tag('status', 'on');
                                }
                                else
                                {
                                        $ilance->xml->add_tag('status', 'off');
                                }
                                break;
                        }
                }
                
                $ilance->xml->print_xml();
                exit();
        }
}
// #### ADD LISTING TO WATCHLIST AND SAVE SELLER AS FAVORTE HANDLER ############
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'addwatchlist' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_addwatchlist_start')) ? eval($apihook) : false;        
        
	// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
        if (isset($ilance->GPC['userid']) AND !empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0)
        {
                $ilance->watchlist = construct_object('api.watchlist');
                
                if (isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
                {
				//suku
				if(!empty($ilance->GPC['comment']))
				{
				$comment=$ilance->GPC['comment'];
				}else
				{

				$comment=$phrase['_added_from_listing_page'];
				}
                        $success = $ilance->watchlist->insert_item(intval($ilance->GPC['userid']), intval($ilance->GPC['projectid']), 'auction', $comment, 0, 0, 0, 0);
                        if ($success)
                        {
                                $ilance->xml->add_tag('status', 'addeditem');
                        }
                        else
                        {
                                $ilance->xml->add_tag('status', 'alreadyaddeditem');        
                        }
                }
                else if (isset($ilance->GPC['sellerid']) AND $ilance->GPC['sellerid'] > 0)
                {
                        $success = $ilance->watchlist->insert_item(intval($ilance->GPC['userid']), intval($ilance->GPC['sellerid']), 'mprovider', $phrase['_added_from_listing_page'], 0, 0, 0, 0);
                        if ($success)
                        {
                                $ilance->xml->add_tag('status', 'addedseller');
                        }
                        else
                        {
                                $ilance->xml->add_tag('status', 'alreadyaddedseller');        
                        }
                }
                else
                {
                        $ilance->xml->add_tag('status', 'error');
                }
                
                $ilance->xml->print_xml();
                exit();
        }
}
// #### Edit WATCHLIST AND SAVE SELLER AS FAVORTE HANDLER ############
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'editwatchlist' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_addwatchlist_start')) ? eval($apihook) : false;        
        
	// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
        if (isset($ilance->GPC['userid']) AND !empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0)
        {
                $ilance->watchlist = construct_object('api.watchlist');
                
                if (isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
                {
				//suku
				if(!empty($ilance->GPC['comment']))
				{
				$comment=$ilance->GPC['comment'];
				}else
				{

				$comment=$phrase['_added_from_listing_page'];
				}
                        $success = $ilance->watchlist->update_item(intval($ilance->GPC['userid']), intval($ilance->GPC['projectid']), 'auction', $comment, 0, 0, 0, 0);
                        if ($success)
                        {
                                $ilance->xml->add_tag('status', 'addeditem');
                        }
                        else
                        {
                                $ilance->xml->add_tag('status', 'alreadyaddeditem');        
                        }
                }
                else if (isset($ilance->GPC['sellerid']) AND $ilance->GPC['sellerid'] > 0)
                {
                        $success = $ilance->watchlist->update_item(intval($ilance->GPC['userid']), intval($ilance->GPC['sellerid']), 'mprovider', $phrase['_added_from_listing_page'], 0, 0, 0, 0);
                        if ($success)
                        {
                                $ilance->xml->add_tag('status', 'addedseller');
                        }
                        else
                        {
                                $ilance->xml->add_tag('status', 'alreadyaddedseller');        
                        }
                }
                else
                {
                        $ilance->xml->add_tag('status', 'error');
                }
                
                $ilance->xml->print_xml();
                exit();
        }
}
//###### Ask your question #################////
//herakle start feb 23 2011 //
//work by karthik//
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'askquestion' )
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_addwatchlist_start')) ? eval($apihook) : false;          
        
	// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
       /*  if (isset($ilance->GPC['userid']) AND !empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0)
        {*/
                //$ilance->watchlist = construct_object('api.watchlist');
                
                if (isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
                {
				//suku
				if(!empty($ilance->GPC['comment']))
				{
				$comment=$ilance->GPC['comment'];
				}else
				{

				$comment=$phrase['_added_from_listing_page'];
				}
				
				if($_SESSION['ilancedata']['user']['userid'] > 0)
				{
				$useremail = fetch_user('email',$_SESSION['ilancedata']['user']['userid']);
				$username = fetch_user('username',$_SESSION['ilancedata']['user']['userid']);
				}
				else
				{
				$useremail = $ilance->GPC['askmail'];
				$username = $ilance->GPC['askname'];
				}
				$date_end=fetch_auction('date_end',$ilance->GPC['projectid']);
				$to = $ilconfig['globalserversettings_siteemail'];

				
				$subject = "GreatCollections Coin Auctions - Item # ".$ilance->GPC['projectid'];
				$Item_type=fetch_auction('filtered_auctiontype', $ilance->GPC['projectid']);
				if($Item_type=='fixed')
				{
				$txt .= '<b>Item ID: </b>'.$ilance->GPC['projectid']. '<br>' .'<b>Buy Now Price: </b>'.$ilance->currency->format(fetch_auction('buynow_price',$ilance->GPC['projectid'])). '' . "\r\n";
				}
                else
				{
				$txt .= '<b>Item ID: </b>'.$ilance->GPC['projectid']. '<br>' .'<b>Item Current Bid: </b>'.$ilance->currency->format(fetch_auction('currentprice',$ilance->GPC['projectid'])). '' . "\r\n";
				}
				$txt .= '<br><b>Item Title:</b> <a href="'.$_SERVER['SERVER_NAME'].'/Coin/'.$ilance->GPC['projectid'].'/'.construct_seo_url_name(fetch_auction('project_title',$ilance->GPC['projectid'])).'">'.fetch_auction('project_title',$ilance->GPC['projectid']).'</a><br>';
				
				if($Item_type=='fixed')
				{
				$txt .= "<b>End Date: </b>" . date("l, F d, Y h:i:s A",strtotime($date_end)) . " (Pacific Time)"."\n\n"; 
				}
				else
				{
				$txt .= "<b>Bidding Ends: </b>" . date("l, F d, Y h:i:s A",strtotime($date_end)) . " (Pacific Time)"."\n\n"; 
				}
				$txt .= '<br><b>Username: </b>'.$username. '<br>' .'<b>Question: </b>'.$comment. '' . "\r\n";						
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$headers .= 'From: '.$useremail.'' . "\r\n" ;
				
				
				
				$success = mail($to,$subject,$txt,$headers);	
				//$success = $ilance->watchlist->insert_item(intval($ilance->GPC['userid']), intval($ilance->GPC['projectid']), 'auction', $comment, 0, 0, 0, 0);
				if ($success == '1')
				{
						$ilance->xml->add_tag('status', 'ask_w');
				}
				else
				{
						$ilance->xml->add_tag('status', 'ask_w1');        
				}
				
                }
                
                else
                {
                        $ilance->xml->add_tag('status', 'error');
                }
                
                $ilance->xml->print_xml();
                exit();
       /* }*/
}
//##########Teller Friend ##################/////
/*if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'tellfriend' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{*/
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'tellfriend')
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_addwatchlist_start')) ? eval($apihook) : false;          
        
	// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
         /*if (isset($ilance->GPC['userid']) AND !empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0)
        {*/
                               
                if (isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
                {
				$friend=$ilance->GPC['to'];	
				$sub=$ilance->GPC['subject'];			
				
				if(isset($ilance->GPC['message']))
				{
				$message=$ilance->GPC['message'];
				}
				else
				{
				$message=$phrase['_added_from_listing_page'];
				}
				if (isset($ilance->GPC['userid']) AND !empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0)
				{
				$useremail = fetch_user('email',$ilance->GPC['userid']);
				$username = fetch_user('username',$ilance->GPC['userid']);
				$project = '<a href="'.$_SERVER['SERVER_NAME'].'/Coin/'.$ilance->GPC['projectid'].'/'.construct_seo_url_name(fetch_auction('project_title',$ilance->GPC['projectid'])).'">'.fetch_auction('project_title',$ilance->GPC['projectid']).'</a>';
				$projectid=$ilance->GPC['projectid'];
				$path = $_SERVER['SERVER_NAME'];
			    }
				else
				{
				$useremail =$ilance->GPC['frm'];
				$username = $ilance->GPC['frm'];
				$project = '<a href="'.$_SERVER['SERVER_NAME'].'/Coin/'.$ilance->GPC['projectid'].'/'.construct_seo_url_name(fetch_auction('project_title',$ilance->GPC['projectid'])).'">'.fetch_auction('project_title',$ilance->GPC['projectid']).'</a>';
				$projectid=$ilance->GPC['projectid'];
				$path = $_SERVER['SERVER_NAME'];
				}			
				
				$to=$friend;
				$bcc2 = $ilconfig['globalserversettings_adminemail'];				
				$subject = 'Check out this coin from GreatCollections';
				$txt .= 'A GreatCollections member wants to show you this item.<br><br>
						<table broder="1"><tr><td><b>From Member: </b>'.$username. '<br></td></tr>'
						.'<tr><td><b>Auction / Buy Now Title: </b>'.$project. '<br><td></tr>'
						.'<tr><td><b>Item ID: </b>'.$projectid. '<br><td></tr>'
						.'<tr><td><b>Message from Member: </b>'.$message. '<br><td></tr>'
						.'<tr><td><b>GreatCollections Website: </b>http://'.$path.'/'.'</td></tr></table>'
						.'<br><b>About GreatCollections</b><br>GreatCollections is a trusted auction and direct sale venue for PCGS, NGC and ANACS certified coins ranging in price from $25 up to $10,000s.  All coins have been professionally listed and imaged by GreatCollections expert staff and most are certified by the leading third-party grading companies.<br><br>GreatCollections<br>Tel: 1.800.44.COINS (+1-949-679-4180)<br>E-mail: info@greatcollections.com<br>Address:  2030 Main Street, Suite 620, Irvine CA 92614'						 
						. "\r\n";
				//$txt .= '<b>Username: </b>'.$username. '<br>' .'<b>Message: </b>'.$message. '' . "\r\n";				
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$headers .= 'From: '.$ilconfig['globalserversettings_siteemail'].'' . "\r\n" ;
				$headers .= 'bcc: '.$ilconfig['globalserversettings_siteemail'].'' . "\r\n" ;
				$headers .= 'bcc: '.$bcc2.'' . "\r\n" ;
				
				
				
				
				$success=mail($to,$subject,$txt,$headers);	
				if ($success == '1')
				{
						$ilance->xml->add_tag('status', 'fri');
				}
				else
				{
						$ilance->xml->add_tag('status', 'friend');        
				}
				
                }
                
                else
                {
                        $ilance->xml->add_tag('status', 'error');
                }
                
                $ilance->xml->print_xml();
                exit();
       /* }*/
		
}
//make kannan
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'makeadmin')
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_addwatchlist_start')) ? eval($apihook) : false;          
        
	// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
        
                $ilance->auction = construct_object('api.auction');
				$ilance->escrow = construct_object('api.escrow');
				               
                if (isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
                {
				
				$subj=$ilance->GPC['subj'];
				if(isset($ilance->GPC['des']))
				{
				$des=$ilance->GPC['des'];
				}
				else
				{
				$des=$phrase['_added_from_listing_page'];
				}
				if(isset($ilance->GPC['mail']))
				{
				$mail=$ilance->GPC['mail'];
				}
				
				$kkk = fetch_auction('user_id',$ilance->GPC['projectid']);;
				
				$useremail = fetch_user('email',$kkk);
				$username = fetch_user('username',$kkk);
				$address = fetch_user('address',$kkk);
				$city = fetch_user('city',$kkk);
				$project = fetch_auction('project_title',$ilance->GPC['projectid']);
				$projectid=$ilance->GPC['projectid'];
				$proj_id = fetch_auction('user_id',$ilance->GPC['projectid']);
			 	//$userinfo = print_shipping_address_text(fetch_user('user_id', $username));
				$pcgs_id = fetch_auction('cid',$ilance->GPC['projectid']);	
				
				$user_project = fetch_user('username',$proj_id);
				$user_email = fetch_user('email',$proj_id);
				$user_phone = fetch_user('phone',$proj_id);
				
				
				//$to="info@greatcollections.com";
				$to = $ilconfig['globalserversettings_siteemail'];
				$subject = 'Make An Offer:';
				$txt .= '<table broder="1"><tr><td>
						<b>Amount of the Offer: </b>'.$subj. '<br></td></tr>'						
						.'<tr><td><b>Buyer/Offerer e-mail: </b>'.$mail. '<br><br><td></tr>'
						
						
						
						.'<tr><td><b>Item ID: </b>'.$projectid. '<br><td></tr>'
						.'<tr><td><b>Item Title: </b>'.$project. '<br><br><td></tr>' 	
						
						.'<tr><td><b>PCGS: </b>'.$pcgs_id. '<br><td></tr>'					
						.'<tr><td><b>Consignor Name: </b>'.$user_project. '<br><td></tr>'
						.'<tr><td><b>Consignor E-mail: </b>'.$user_email. '<br><td></tr>' 
						.'<tr><td><b>Consignor Phone: </b>'.$user_phone. '<br><br><td></tr>' 
						
						.'<tr><td><b>Message: </b>'.$des. '</td></tr></table>'						 
						. "\r\n";	
									
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$headers .= 'From: '.$mail.'' . "\r\n" ;
				$success=mail($to,$subject,$txt,$headers);	
				if ($success == '1')
				{
						$ilance->xml->add_tag('status', 'make');
				}
				else
				{
						$ilance->xml->add_tag('status', 'make_offer');        
				}
				
                }
                
                else
                {
                        $ilance->xml->add_tag('status', 'error');
                }
                
                $ilance->xml->print_xml();
                exit();
        
}
// #### SEARCH FAVORITES #######################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'searchfavorites' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_searchfavorites_start')) ? eval($apihook) : false;
        
        $ilance->GPC['searchid'] = intval($ilance->GPC['searchid']);
        $ilance->GPC['value'] = ($ilance->GPC['value'] == 'on' ? 1 : 0);
        
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "search_favorites
                SET subscribed = '" . intval($ilance->GPC['value']) . "',
                added = '" . DATETIME24H . "'
                WHERE searchid = '" . intval($ilance->GPC['searchid']) . "'
                        AND user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->GPC['value'])
        {
                $ilance->xml->add_tag('status', 'on');
        }
        else
        {
                $ilance->xml->add_tag('status', 'off');
        }
        
        $ilance->xml->print_xml();
        exit();
}
// #### ACP AJAX ENHNACEMENTS ##################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'acpenhancements' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
{
        $ilance->xml = construct_object('api.xml');
        
        ($apihook = $ilance->api('ajax_acpenhancements_start')) ? eval($apihook) : false;
        
        $ilance->GPC['id'] = intval($ilance->GPC['id']);
        $ilance->GPC['value'] = ($ilance->GPC['value'] == 'on' ? 1 : 0);
        $ilance->GPC['type'] = strip_tags($ilance->GPC['type']);
        
        switch ($ilance->GPC['type'])
        {
                case 'featured':
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET featured = '" . intval($ilance->GPC['value']) . "'
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        break;
                }
                case 'bold':
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET bold = '" . intval($ilance->GPC['value']) . "'
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        break;
                }
                case 'highlite':
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET highlite = '" . intval($ilance->GPC['value']) . "'
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        break;
                }
                case 'autorelist':
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET autorelist = '" . intval($ilance->GPC['value']) . "'
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        break;
                }
        }
        if ($ilance->GPC['value'])
        {
                $ilance->xml->add_tag('status', 'on');
        }
        else
        {
                $ilance->xml->add_tag('status', 'off');
        }
        
        $ilance->xml->print_xml();
        exit();
}
// #### FLASH GALLERY APPLET ###################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'flashgallery')
{
        ($apihook = $ilance->api('ajax_flashgallery_start')) ? eval($apihook) : false;
        
        switch ($ilance->GPC['config'])
        {
                case 'portfolio':
                {
                        $xml = '<?xml version="1.0"?>
<gallery>
<config>
<big_thumb type="number">80</big_thumb>
<inc_koef type="number">1.1</inc_koef>
<dec_koef type="number">0.9</dec_koef>
<interval_delay type="number">20</interval_delay>
<fade_in_delay type="number">20</fade_in_delay>
<fade_in_step type="number">5</fade_in_step>
<speed_increment type="number">1</speed_increment>
<speed_up_part type="number">0.2</speed_up_part>
<speed_decrement type="number">1</speed_decrement>
<speed_down_part type="number">0.8</speed_down_part>
<speed_delay type="number">7</speed_delay>
<pager_scroll_alpha type="number">4</pager_scroll_alpha>
<show_thumb_after_scroll_delay type="number">25</show_thumb_after_scroll_delay>
<show_thumb_after_scroll_alpha_step type="number">5</show_thumb_after_scroll_alpha_step>
<pager_controls_alpha type="number">30</pager_controls_alpha>
</config>
<items>
' . fetch_flash_gallery_xml_items($ilance->GPC['config']) . '
</items>
</gallery>';
                        break;
                }        
                case 'favoriteseller':
                {
                        $xml = '<?xml version="1.0"?>
<gallery>
<config>
<big_thumb type="number">80</big_thumb>
<inc_koef type="number">1.1</inc_koef>
<dec_koef type="number">0.9</dec_koef>
<interval_delay type="number">20</interval_delay>
<fade_in_delay type="number">20</fade_in_delay>
<fade_in_step type="number">5</fade_in_step>
<speed_increment type="number">1</speed_increment>
<speed_up_part type="number">0.2</speed_up_part>
<speed_decrement type="number">1</speed_decrement>
<speed_down_part type="number">0.8</speed_down_part>
<speed_delay type="number">7</speed_delay>
<pager_scroll_alpha type="number">4</pager_scroll_alpha>
<show_thumb_after_scroll_delay type="number">25</show_thumb_after_scroll_delay>
<show_thumb_after_scroll_alpha_step type="number">5</show_thumb_after_scroll_alpha_step>
<pager_controls_alpha type="number">30</pager_controls_alpha>
</config>
<items>
' . fetch_flash_gallery_xml_items($ilance->GPC['config'], $ilance->GPC['userid']) . '
</items>
</gallery>';
                        break;
                }        
                case 'recentlyviewed':
                {
                        $xml = '<?xml version="1.0"?>
<gallery>
<config>
<big_thumb type="number">80</big_thumb>
<inc_koef type="number">1.1</inc_koef>
<dec_koef type="number">0.9</dec_koef>
<interval_delay type="number">20</interval_delay>
<fade_in_delay type="number">20</fade_in_delay>
<fade_in_step type="number">5</fade_in_step>
<speed_increment type="number">1</speed_increment>
<speed_up_part type="number">0.2</speed_up_part>
<speed_decrement type="number">1</speed_decrement>
<speed_down_part type="number">0.8</speed_down_part>
<speed_delay type="number">7</speed_delay>
<pager_scroll_alpha type="number">4</pager_scroll_alpha>
<show_thumb_after_scroll_delay type="number">25</show_thumb_after_scroll_delay>
<show_thumb_after_scroll_alpha_step type="number">5</show_thumb_after_scroll_alpha_step>
<pager_controls_alpha type="number">30</pager_controls_alpha>
</config>
<items>
' . fetch_flash_gallery_xml_items($ilance->GPC['config']) . '
</items>
</gallery>';
                        break;
                }
        }
        
        echo $xml;
        exit();
}
// #### STATS GALLERY APPLET ###################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'stats' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($ilance->GPC['config']) AND !empty($ilance->GPC['config']))
{
        $xml = '<?xml version="1.0"?>
<chart>
<config>
<axises_space_left type="number">0</axises_space_left>
<axises_space_top type="number">20</axises_space_top>
<axises_space_right type="number">0</axises_space_right>
<axises_space_bottom type="number">45</axises_space_bottom>
<axises_line_thickness type="number">2</axises_line_thickness>
<axises_marks_thickness type="number">1</axises_marks_thickness>
<axises_marks_length type="number">10</axises_marks_length>
<axises_labels_space type="number">2</axises_labels_space>
<axises_labels_font_size type="number">11</axises_labels_font_size>
<axises_color type="hex">0x000000</axises_color>
<axises_bg_grid_color type="hex">0xCCCCCC</axises_bg_grid_color>
<mouse_pointer_thickness type="number">1</mouse_pointer_thickness>
<mouse_pointer_color type="hex">0x0066ff</mouse_pointer_color>
<date_mouse_pointer_color type="hex">0x0066ff</date_mouse_pointer_color>
<date_mouse_pointer_alpha type="number">100</date_mouse_pointer_alpha>
<date_mouse_pointer_distance_axis type="number">2</date_mouse_pointer_distance_axis>
<date_mouse_pointer_distance_x_arrow type="number">7</date_mouse_pointer_distance_x_arrow>
<date_mouse_pointer_distance_y_arrow type="number">7</date_mouse_pointer_distance_y_arrow>
<date_mouse_pointer_label_dx type="number">1</date_mouse_pointer_label_dx>
<date_mouse_pointer_label_dy type="number">10</date_mouse_pointer_label_dy>
<date_mouse_pointer_bg_dx type="number">10</date_mouse_pointer_bg_dx>
<date_mouse_pointer_bg_dy type="number">1</date_mouse_pointer_bg_dy>
<date_mouse_pointer_date_label_color type="hex">0xFFFFFF</date_mouse_pointer_date_label_color>
<value_mouse_pointer_label_color type="hex">0xFFFFFF</value_mouse_pointer_label_color>
<value_mouse_pointer_distance_x_arrow type="number">7</value_mouse_pointer_distance_x_arrow>
<value_mouse_pointer_distance_y_arrow type="number">7</value_mouse_pointer_distance_y_arrow>
<show_all_btn_dx type="number">100</show_all_btn_dx>
<show_all_btn_dy type="number">30</show_all_btn_dy>
<scroll_bg_color type="hex">0x009966</scroll_bg_color>
<scroll_color type="hex">0x3366ff</scroll_color>
<scroll_height type="number">15</scroll_height>
<scroll_space_bottom type="number">20</scroll_space_bottom>
<pin_bg_color type="hex">0x000000</pin_bg_color>
<pin_text_color type="hex">0xFFFFFF</pin_text_color>
<pin_bg_alpha type="number">100</pin_bg_alpha>
<show_dots_after type="number">50</show_dots_after>
<dots_radius type="number">3</dots_radius>
';
        $xml .= fetch_flash_stats_xml_items($ilance->GPC['config']);
        
        echo $xml;
        exit();
}
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'stats2' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        switch ($ilance->GPC['config'])
        {
                case 'connections':
                {
                        $xml = '<?xml version="1.0"?>
<chart>
<config>
<axises_space_left type="number">0</axises_space_left>
<axises_space_top type="number">15</axises_space_top>
<axises_space_right type="number">0</axises_space_right>
<axises_space_bottom type="number">25</axises_space_bottom>
<axises_font_size type="number">10</axises_font_size>
<axises_font_face type="string">Tahoma</axises_font_face>
<axises_font_bold type="boolean">true</axises_font_bold>
<axises_color type="hex">0x444444</axises_color>
<axises_bg_grid_color type="hex">0xCCCCCC</axises_bg_grid_color>
<axises_line_thickness type="number">2</axises_line_thickness>
<axises_marks_thickness type="number">1</axises_marks_thickness>
<axises_marks_length type="number">8</axises_marks_length>
<axises_marks_font_size type="number">10</axises_marks_font_size>
<axises_marks_font_face type="string">Tahoma</axises_marks_font_face>
<axises_marks_font_bold type="boolean">false</axises_marks_font_bold>
<axises_marks_font_color type="hex">0x333333</axises_marks_font_color>
<axises_labels_space type="number">5</axises_labels_space>
<axises_labels_font_size type="number">10</axises_labels_font_size>
<axises_labels_font_face type="string">Verdana</axises_labels_font_face>
<axises_labels_font_bold type="boolean">false</axises_labels_font_bold>
<value_mouse_pointer_label_color type="hex">0xFFFFFF</value_mouse_pointer_label_color>
<value_mouse_pointer_bg_color type="hex">0x004B95</value_mouse_pointer_bg_color>
<value_mouse_pointer_bg_alpha type="number">100</value_mouse_pointer_bg_alpha>
<value_mouse_pointer_distance_x_arrow type="number">7</value_mouse_pointer_distance_x_arrow>
<value_mouse_pointer_distance_y_arrow type="number">7</value_mouse_pointer_distance_y_arrow>
<value_mouse_pointer_space_left type="number">10</value_mouse_pointer_space_left>
<value_mouse_pointer_space_top type="number">10</value_mouse_pointer_space_top>
<value_bar_color1 type="hex">0x99BBDB</value_bar_color1> 
<value_bar_color2 type="hex">0x4A6E7D</value_bar_color2>
<value_bar_alpha1 type="number">100</value_bar_alpha1>
<value_bar_alpha2 type="number">100</value_bar_alpha2>
<value_bar_gradient_spread1 type="number">0</value_bar_gradient_spread1>
<value_bar_gradient_spread2 type="number">255</value_bar_gradient_spread2>
<value_bar_height type="number">10</value_bar_height>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
' . fetch_flash_stats_xml_items($ilance->GPC['config']) . '
</items>
</chart>';
                        break;
                }
        }
        
        echo $xml;
        exit();
}
// #### AJAX CATEGORY SELECTOR #################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'categories' AND isset($ilance->GPC['mode']))
{
        $modetypes = array('service','product');
        if (!in_array($ilance->GPC['mode'], $modetypes))
        {
                return '';
        }
        
        // #### determine if we're displaying rss feeds to hide cats that admin prefers not to include
        $rssquery = "";
        $rss = isset($ilance->GPC['rss']) ? intval($ilance->GPC['rss']) : 0;
        if ($rss)
        {
                $rssquery = "AND xml = '1' ";
        }
        
        // #### determine if we're displaying category notifications to hide cats that admin prefers not to include
        $newsquery = "";
        $news = isset($ilance->GPC['newsletter']) ? intval($ilance->GPC['newsletter']) : 0;
        if ($news)
        {
                $newsquery = "AND newsletter = '1' ";
        }
        
        // #### show categories for the first box ##############################
        $getcats = $ilance->db->query("
                SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
                FROM " . DB_PREFIX . "categories
                WHERE parentid = '0'
                        AND cattype = '" . $ilance->db->escape_string($ilance->GPC['mode']) . "'
                        AND visible = '1'
                        $rssquery
                        $newsquery
                ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
        ", 0, null, __FILE__, __LINE__);
        
        require_once(DIR_API . 'class.xajax.inc.php');
        
        $xajax = new xajax();        
        $xajax->registerFunction('print_next_category');
        $xajax->processRequests();
        
        $cidfield = isset($ilance->GPC['cidfield']) ? $ilance->GPC['cidfield'] : 'cid';
        $showcontinue = isset($ilance->GPC['showcontinue']) ? intval($ilance->GPC['showcontinue']) : 1;
        $showthumb = isset($ilance->GPC['showthumb']) ? intval($ilance->GPC['showthumb']) : 1;
        $showcidbox = isset($ilance->GPC['showcidbox']) ? intval($ilance->GPC['showcidbox']) : 1;
        $showyouselectedstring = isset($ilance->GPC['showyouselectedstring']) ? intval($ilance->GPC['showyouselectedstring']) : 1;
        $readonly = isset($ilance->GPC['readonly']) ? intval($ilance->GPC['readonly']) : 0;
        $showcheckmarkafterstring = isset($ilance->GPC['showcheckmarkafterstring']) ? intval($ilance->GPC['showcheckmarkafterstring']) : 1;
        $categoryfinderjs = isset($ilance->GPC['categoryfinderjs']) ? intval($ilance->GPC['categoryfinderjs']) : 0;
        $id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
        $cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
        $rootcid = 0;
        $assigntoall = 0;
        $showaddanother = isset($ilance->GPC['showaddanother']) ? intval($ilance->GPC['showaddanother']) : 0;
        if ($showaddanother AND isset($ilance->GPC['mode']))
        {
                $cmd = $ilance->GPC['mode'];
        }
        
        if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
        {
                $assigntoall = isset($ilance->GPC['assigntoall']) ? intval($ilance->GPC['assigntoall']) : 0;
                $rootcid = isset($ilance->GPC['rootcid']) ? intval($ilance->GPC['rootcid']) : 0;
        }
        
        $footerscript = '';
        if (isset($ilance->GPC["$cidfield"]) AND $ilance->GPC["$cidfield"] > 0)
        {
               $footerscript = '
<script type="text/javascript">
<!--
' . fetch_recursive_category_ids_js(intval($ilance->GPC["$cidfield"]), $ilance->GPC['mode'], $_SESSION['ilancedata']['user']['slng'], $cidfield, $showcontinue, $showthumb, $showcidbox, $showyouselectedstring, $readonly, $showcheckmarkafterstring, $categoryfinderjs, $id, $cmd, $rss, $news, $showaddanother) . '
//--></script>
';
        }
?>
<html><head><script language="javascript" type="text/javascript" src="<?php echo $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . '/javascript/functions.js'; ?>"></script>
<?php $xajax->printJavascript(); ?></head>
<body bgcolor="#ffffff" style="margin:0px; padding:0px" onLoad="">  <table cellpadding="0" cellspacing="0" border="0" dir="<?php echo $ilconfig['template_textdirection']; ?>"><tr valign="top">
<td id="catbox_1"><select id="catbox_1_list" name="catbox_1" onChange="xajax_print_next_category(this[this.selectedIndex].value, 'catbox_1', '<?php echo $cidfield; ?>', '<?php echo $showcontinue; ?>', '<?php echo $showthumb; ?>', '<?php echo $showcidbox; ?>', '<?php echo $showyouselectedstring; ?>', '<?php echo $readonly; ?>', '<?php echo $showcheckmarkafterstring; ?>', '<?php echo $categoryfinderjs; ?>', '<?php echo $id; ?>', '<?php echo $cmd; ?>', '<?php echo $rss; ?>', '<?php echo $news; ?>', '<?php echo $showaddanother; ?>')" style="position:relative; width:230px; height:212px; font-family:verdana" size="13">
<?php
if ($rootcid)
{
        echo '<option value="0">' . $phrase['_no_parent_category'] . '</option>';
}
if ($assigntoall)
{
        echo '<option value="-1" style="background-color:#ff9900; color:#fff">' . $phrase['_assign_to_all_categories'] . '</option>';
}
while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
{
        echo '<option value="' . $res['cid'] . '">' . $res['title'] . '' . (is_last_category($res['cid']) ? '' : ' &gt;') . '</option>';
}
?>
</select></td><td id="catbox_2" style="padding-left:10px"></td><td id="catbox_3" style="padding-left:10px"></td><td id="catbox_4" style="padding-left:10px"></td><td id="catbox_5" style="padding-left:10px"></td><td id="catbox_6" style="padding-left:10px"></td><td id="catbox_7" style="padding-left:10px"></td><td id="catbox_8" style="padding-left:10px"></td><td id="catbox_9" style="padding-left:10px"></td><td id="catbox_10" style="padding-left:10px"></td><td id="catbox_11" style="padding-left:10px"></td><td id="catbox_12" style="padding-left:10px"></td><td id="catbox_13" style="padding-left:10px"></td><td id="catbox_14" style="padding-left:10px"></td><td id="catbox_15" style="padding-left:10px"></td></tr></table>
<?php
echo $footerscript;
if ($rootcid AND $ilance->GPC["$cidfield"] == 0)
{
       echo "<script type=\"text/javascript\">window.setTimeout(function(){xajax_print_next_category('0','catbox_1','pid','0','0','0','0','0','0','0','0','','0','0','0');},1200);</script>";
}
?>
</body></html>
<?php
}
// ##### COUNTRY CHECKBOXES ####################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'cbcountries' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $sql = $ilance->db->query("
                SELECT locationid, location_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
                FROM " . DB_PREFIX . "locations
                ORDER BY location_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $rc = 0;
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $res['class'] = ($rc % 2) ? 'alt2' : 'alt1';
                        $res['cb'] = '<input type="checkbox" name="locationid[]" value="' . $res['locationid'] . '" />';
                        $res['title'] = stripslashes($res['title']);
                        $countries[] = $res;
                        $rc++;
                }
                
                $ilance->template->load_popup('head', 'popup_header.html');
                $ilance->template->load_popup('main', 'ajax_countries.html');
                $ilance->template->load_popup('foot', 'popup_footer.html');
                $ilance->template->parse_loop('main', 'countries');
                $ilance->template->parse_if_blocks('head');
                $ilance->template->parse_if_blocks('main');
                $ilance->template->parse_if_blocks('foot');
                $ilance->template->pprint('head', array('headinclude','onbeforeunload','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time') );
                $ilance->template->pprint('main', array('headerstyle','bidamounttype','bidamounttype_pulldown','type','amount','fvf','ins','esc','final_conversion','category_pulldown','category_pulldown2','cid','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
                $ilance->template->pprint('foot', array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));
                exit();
        }
        else
        {
                echo 'Could not fetch country list at this time.';
        }
        
        exit();
}
// #### SKILLS CHECKBOXES ######################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'cbskills')
{
        $ilance->categories_skills = construct_object('api.categories_skills');
        
        $headinclude .= '
<script language="javascript" type="text/javascript">
<!--
var newArray = new Array();
var selectedskillbit;
var selectedskills;
var skillhiddenfields;
var skillshidden;
window.top.document.getElementById(\'selectedskills\').innerHTML = \'\';
window.top.document.getElementById(\'skillhiddenfields\').innerHTML = \'\';
function add_skill(cid, title)
{
        selectedskillbit = \'<div style="padding-bottom:3px"><span style="float:right; padding-right:10px; padding-top:8px"><input type="submit" value=" ' . $phrase['_search'] . ' " class="buttons" style="font-size:15px" /></span><strong>' . $phrase['_you_have_selected_the_following_skills'] . '</strong></div>\';
        selectedskills = \'\';
        skillhiddenfields = \'\';
        skillshidden = \'\';
        if (newArray[cid] != title)
        {
                newArray[cid] = title;
        }
        else if (newArray[cid] == title)
        {
                newArray[cid] = \'\';
        }
        for (i = 0; i <= newArray.length; i++)
        {
                skillshidden = \'\';
                if (newArray[i] != undefined && newArray[i] != \'\')
                {
                        if (selectedskills != \'\')
                        {
                                selectedskills = selectedskills + \', <span class="gray">\' + newArray[i] + \'</span>\';
                                skillshidden = \'<input type="hidden" name="sid[\' + i + \']" value="true" />\';
                                skillhiddenfields = skillhiddenfields + skillshidden;
                        }
                        else
                        {
                                selectedskills = \'<span class="gray">\' + newArray[i] + \'</span>\';
                                skillhiddenfields = \'<input type="hidden" name="sid[\' + i + \']" value="true" />\';
                        }
                }
        }
        if (selectedskills == \'\')
        {
                window.top.document.getElementById(\'selectedskills\').innerHTML = \'\';
                window.top.document.getElementById(\'skillhiddenfields\').innerHTML = \'\';
        }
        else
        {
                window.top.document.getElementById(\'selectedskills\').innerHTML = selectedskillbit + " " + selectedskills;
                window.top.document.getElementById(\'skillhiddenfields\').innerHTML = skillhiddenfields;
        }
}
//-->
</script>';
        $cbskills = $ilance->categories_skills->print_skills_columns($_SESSION['ilancedata']['user']['slng'], 0, false, 4, true);
        
        $ilance->template->load_popup('head', 'popup_header.html');
        $ilance->template->load_popup('main', 'ajax_skills.html');
        $ilance->template->load_popup('foot', 'popup_footer.html');
        $ilance->template->parse_if_blocks('head');
        $ilance->template->parse_if_blocks('foot');
        $ilance->template->pprint('head', array('headinclude','onbeforeunload','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time') );
        $ilance->template->pprint('main', array('cbskills','headerstyle','bidamounttype','bidamounttype_pulldown','type','amount','fvf','ins','esc','final_conversion','category_pulldown','category_pulldown2','cid','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
        $ilance->template->pprint('foot', array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));
        exit();
}
// #### QUICK REGISTRATION #####################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'quickregister' AND isset($ilance->GPC['qusername']) AND isset($ilance->GPC['qpassword']) AND isset($ilance->GPC['qemail']))
{
        // some check ups
        $unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['qusername']);
        $unicode_email = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['qemail']);
                
        // username ban checkup
        if ($ilance->common->is_username_banned($ilance->GPC['qusername']) OR $ilance->common->is_username_banned($unicode_name))
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_problem'] . '</strong>: ' . $phrase['_that_username_is_currently_banned'];
                exit();
        }
        
        // username check
        $sqlusercheck = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "users
                WHERE username IN ('" . addslashes(htmlspecialchars_uni($ilance->GPC['qusername'])) . "', '" . addslashes(htmlspecialchars_uni($unicode_name)) . "')
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlusercheck) > 0)
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_problem'] . '</strong>: ' . $phrase['_that_username_already_exists_in_our_system'];
                exit();
        }
        
        // email address check
        $sqlemailcheck = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "users
                WHERE email IN ('" . addslashes(htmlspecialchars_uni($ilance->GPC['qemail'])) . "', '" . addslashes(htmlspecialchars_uni($unicode_email)) . "')
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlemailcheck) > 0)
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_problem'] . '</strong>: ' . $phrase['_that_email_address_already_exists_in_our_system'];
                exit();
        }
        
        if (isset($ilance->GPC['qemail']) AND isset($ilance->GPC['qemail2']) AND $ilance->GPC['qemail'] != $ilance->GPC['qemail2'])
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_problem'] . '</strong>: ' . $phrase['_email_addresses_do_not_match'];
                exit();
        }
        
        // final email checks (check mx record, check list of banned free emails, etc)
        if ($ilance->common->is_email_banned(trim($ilance->GPC['qemail'])) OR $ilance->common->is_email_valid(trim($ilance->GPC['qemail'])) == false)
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_problem'] . '</strong>: ' . $phrase['_it_appears_this_email_address_is_banned_from_the_marketplace_please_try_another_email_address'];
                exit();
        }
        
        // email is good check if it's duplicate
        $sqlemailcheck = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "users
                WHERE email = '" . $ilance->db->escape_string($ilance->GPC['qemail']) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlemailcheck) > 0)
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_problem'] . '</strong>: ' . $phrase['_that_email_address_already_exists_in_our_system'];
                exit();
        }
        
        // set new member defaults
        $user = array();
        $subscription = array();
        $preferences = '';
        
        $user['roleid'] = '-1';
        $user['username'] = trim($ilance->GPC['qusername']);
        $user['password'] = $ilance->GPC['qpassword'];
        $user['secretquestion'] = $phrase['_what_is_my_email_address'];
        $user['secretanswer'] = md5($ilance->GPC['qemail']);
        $user['email'] = $ilance->GPC['qemail'];
        $user['firstname'] = $phrase['_unknown'];
        $user['lastname'] = $phrase['_unknown'];
        $user['address'] = $phrase['_unknown'];
        $user['city'] = $phrase['_unknown'];
        $user['state'] = $phrase['_unknown'];
        $user['zipcode'] = $phrase['_unknown'];
        $user['phone'] = '+0';
        $user['countryid'] = '500';
        $user['styleid'] = $_SESSION['ilancedata']['user']['styleid'];
        
        // we must tell the registration system what plan to set as default!
        $subscription['subscriptionid'] = (isset($ilance->GPC['subscriptionid'])) ? intval($ilance->GPC['subscriptionid']) : '1';
        $subscription['subscriptionpaymethod'] = (isset($ilance->GPC['subscriptionpaymethod'])) ? $ilance->GPC['subscriptionpaymethod'] : 'account';        
        $subscription['promocode'] = '';
    
        // construct registration system
        $ilance->registration = construct_object('api.registration');
        
        $questions = array();
        
        $final = $ilance->registration->build_user_datastore($user, $preferences, $subscription, $questions, 'return_userarray');
        if (!empty($final))
        {
                // set new members cookies
                set_cookie('userid', $ilance->crypt->three_layer_encrypt($final['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
                set_cookie('username', $ilance->crypt->three_layer_encrypt($final['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
                set_cookie('password', $ilance->crypt->three_layer_encrypt($final['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
                set_cookie('lastvisit', DATETIME24H, true);
                set_cookie('lastactivity', DATETIME24H, true);
                
                switch ($final['status'])
                {
                        case 'active':
                        {
                                // make sure we have a valid password session
                                if (!empty($_SESSION['ilancedata']['user']['password_md5']))
                                {
                                        $_SESSION['ilancedata']['user']['password'] = $_SESSION['ilancedata']['user']['password_md5'];
                                        session_unregister($_SESSION['ilancedata']['user']['password_md5']);
                                }
                                
                                // display final registration information
                                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_complete'] . '</strong>';
                                exit();
                                break;        
                        }
                        case 'unverified':
                        {
                                // display email link code information
                                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' . $phrase['_registration_not_completed'] . '</strong><div style="padding-top:4px">' . $phrase['_thank_you_for_registering_an_email_has_been_dispatched_to_you'] . '</div>';
                                exit();
                                break;
                        }
                }
        }
        else
        {
                echo '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>' .  $phrase['_registration_problem'] . '</strong>: ' . $phrase['_sorry_there_was_a_problem_completing_your_registration_we_apologize'];
                exit();
        }
}
// #### SEARCH PAGE PROFILE FILTER CATEGORY RESULTS ############################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'profilefilters')
{
        global $phrase;
        
        $ilance->auction = construct_object('api.auction');
        $ilance->auction_post = construct_object('api.auction_post');
        
        $cid = 0;
        if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
        {
                $cid = intval($ilance->GPC['cid']);
        }
        
        echo '<div id="profile_filters_text">' . $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service') . '</div>';
        exit();
}
// #### AUTOCOMPLETE SEARCH BAR ################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'autocomplete')
{
        //return '';
        global $ilance, $phrase;
        
        function arrfilter(&$item)
        {
                global $ilance;
                
                return preg_match('/^' . utf8_decode($ilance->GPC['q']) . '/', $item);
        }
        
        $xmlDoc  = '<?xml version="1.0" encoding="utf-8"?>';
        $xmlDoc .= '<root>';
        
        if (isset($ilance->GPC['q']) AND !empty($ilance->GPC['q']))
        {
                $available = array();
                
                $sql = $ilance->db->query("
                        SELECT keyword
                        FROM " . DB_PREFIX . "search
                        WHERE count > 10
                                AND keyword LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%'
                        GROUP BY keyword
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $available[] = $res['keyword'];
                        }
                }
                
                $available = array_unique($available);
                $results = array_filter($available, 'arrfilter');
                
                $i = 0;
                foreach ($results AS $key => $label)
                {
                        if ($i <= 10)
                        {
                                $labelformatted = str_replace($ilance->GPC['q'], '<span class="blue"><strong>' . $ilance->GPC['q'] . '</strong></span>', $label);
                                $labelformatted = '<div style="width:353px; padding-top:2px; padding-bottom:2px; padding-left:2px; cursor: hand">' . $labelformatted . '</div>';
                                $xmlDoc .= '<item id="' . $i . '" label="' . ilance_htmlentities($labelformatted) . '" text="' . ilance_htmlentities($label) . '"></item>';
                        }
                        $i++;
                }
        }
        
        $xmlDoc .= '</root>';
        
        header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
        echo $xmlDoc;
        exit();
}
// #### REFRESH PROJECT DETAIL PAGE ############################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'refreshprojectdetails' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
        $ilance->auction = construct_object('api.auction');
        $ilance->auction_expiry = construct_object('api.auction_expiry');
        $ilance->bid = construct_object('api.bid');
        $ilance->subscription = construct_object('api.subscription');
        
        $ilance->GPC['id'] = intval($ilance->GPC['id']);
        
        // run listing expiry logic
        $ilance->auction_expiry->listings();
        
        $timeleft = $ilance->auction->auction_timeleft($ilance->GPC['id'], 'alt1', 'center', $timeintext = 0, $showlivebids = 0, $forcenoflash = 1, $showfullformat = 1);
        $cid = fetch_auction('cid', $ilance->GPC['id']);
        $userid = fetch_auction('user_id', $ilance->GPC['id']);
        $status = fetch_auction('status', $ilance->GPC['id']);
        $bid_details = fetch_auction('bid_details', $ilance->GPC['id']);
        $winner_user_id = fetch_auction('winner_user_id', $ilance->GPC['id']);
        $bids = fetch_auction('bids', $ilance->GPC['id']);
        $declinedbids = $ilance->bid->fetch_declined_bids($ilance->GPC['id']);
        $retractedbids = $ilance->bid->fetch_retracted_bids($ilance->GPC['id']);
        
        $fetchbidstuff = $ilance->bid->fetch_average_lowest_highest_bid_amounts($bid_details, $ilance->GPC['id'], $userid);
        $bidprivacy = $fetchbidstuff['bidprivacy'];
        $average = $ilance->bid->fetch_average_bid($ilance->GPC['id'], false, $bid_details, false);
        $lowest = $fetchbidstuff['lowest'];
        $highest = $fetchbidstuff['highest'];
        unset($fetchbidstuff);
        
        $showplacebidrow = 1;
        $showawardedbidderrow = 0;
        $showendedrow = 0;
        $showlowestactivebidder = 0;
        
        if ($bids > 0)
        {
                // fetch lowest bidder details (will populate $show['lowbidder_active'] also)..
                $lowbidtemp = $ilance->bid->fetch_lowest_bidder_info($ilance->GPC['id'], $userid, $bid_details);
                $lowestbiddertext = $lowbidtemp['lowbidder'];
                if ($show['lowbidder_active'])
                {
                        $showlowestactivebidder = 1;       
                }
                unset($lowbidtemp);
                                
                // awarded bidder username
                $awardedbiddertext = '';
                if ($winner_user_id > 0)
                {
                        $showawardedbidderrow = 1;
                        $awardedbiddertext = fetch_user('username', $winner_user_id);
                        if ($bid_details == 'blind' OR $bid_details == 'full')
                        {
                                $awardedbiddertext = '= ' . $phrase['_blind_bidder'] . ' =';        
                        }
                }
                
                // highest bidder username
                $highestbiddertext = '';
                $highbidderuserid = $ilance->bid->fetch_highest_bidder($ilance->GPC['id']);
                if ($highbidderuserid > 0)
                {
                        $highestbiddertext = fetch_user('username', $highbidderuserid);
                        if ($bid_details == 'blind' OR $bid_details == 'full')
                        {
                                $highestbiddertext = '= ' . $phrase['_blind_bidder'] . ' =';        
                        }
                }
                unset($highbidderuserid);
                
                if ($bid_details == 'sealed' OR $bid_details == 'full')
                {
                        $lowest = '= ' . $phrase['_sealed'] . ' =';
                        $highest = '= ' . $phrase['_sealed'] . ' =';
                }
        }
        else
        {
                $lowestbiddertext = $highestbiddertext = $awardedbiddertext = '';
        }
        
        if ($status != 'open')
        {
                $showplacebidrow = 0;
                $showendedrow = 1;
                $timeleft = $phrase['_ended'];
                
                $date_end = fetch_auction('date_end', $ilance->GPC['id']);
                
                switch ($status)
                {
                        case 'closed':
                        {
                                $projectstatus = $phrase['_closed_since'] . ' ' . print_date($date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                break;
                        }				
                        case 'expired':
                        {
                                $projectstatus = $phrase['_expired_since'] . ' ' . print_date($date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                break;
                        }				
                        case 'delisted':
                        {
                                $projectstatus = $phrase['_delisted_since'] . ' ' . print_date($date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                break;
                        }				
                        case 'approval_accepted':
                        {
                                $projectstatus = $phrase['_vendor_awarded_bidding_for_event_closed'];
                                break;
                        }                        
                        case 'wait_approval':
                        {
                                // fetch days since the provider has been awarded giving more direction to the viewer
                                $close_date = fetch_auction('close_date', $ilance->GPC['id']);
                                $date1split = explode(' ', $close_date);
                                $date2split = explode('-', $date1split[0]);
                                $days = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
                                if ($days == 0)
                                {
                                        $days = 1;
                                }
                                
                                $projectstatus = $phrase['_waiting_for_awarded_provider_to_accept_the_project'] . ' <span class="gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</span>';
                                break;
                        }				
                        case 'frozen':
                        {
                                $projectstatus = $phrase['_frozen_event_temporarily_closed'];
                                break;
                        }
                        case 'finished':
                        {
                                $projectstatus = $phrase['_vendor_awarded_event_is_finished'];
                                break;
                        }				
                        case 'archived':
                        {
                                $projectstatus = $phrase['_archived_event'];
                                break;
                        }				
                        case 'draft':
                        {
                                $projectstatus = $phrase['_draft_mode_pending_post_by_owner'];
                                break;
                        }
                }
        }
        else
        {
                $projectstatus = $phrase['_event_open_for_bids'];        
        }
        
        // myString[0]  = timeleft text
        // myString[1]  = bids text
        // myString[2]  = lowest bidder name
        // myString[3]  = highest bidder name
        // myString[4]  = awarded bidder name
        // myString[5]  = average bid amount
        // myString[6]  = project status text
        // myString[7]  = number of declined bids
        // myString[8]  = number of retracted bids
        // myString[9]  = SHOW place a bid row?
        // myString[10] = SHOW awarded bidder row?
        // myString[11] = SHOW block header as ended listing?
        // myString[12] = lowest bid amount
        // myString[13] = highest bid amount
        // myString[14] = SHOW lowest active bidder row?
        
        //   |timeleft    |bids         |ILance                   |mom                       |ILance                    |S$ 18.50        |Ended                 |0                    |0                     |1                       |0                            |0                    |$100.00        |$500.00         |1
        echo $timeleft . '|' . $bids . '|' . $lowestbiddertext . '|' . $highestbiddertext . '|' . $awardedbiddertext . '|' . $average . '|' . $projectstatus . '|' . $declinedbids . '|' . $retractedbids . '|' . $showplacebidrow . '|' . $showawardedbidderrow . '|' . $showendedrow . '|' . $lowest . '|' . $highest . '|' . $showlowestactivebidder;
        exit();
}
// #### REFRESH ITEM DETAIL PAGE ###############################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'refreshitemdetails' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['type']) AND !empty($ilance->GPC['type']))
{
      
	    $ilance->auction = construct_object('api.auction');
        $ilance->auction_expiry = construct_object('api.auction_expiry');
        $ilance->bid = construct_object('api.bid');
        $ilance->bid_proxy = construct_object('api.bid_proxy');
        $ilance->subscription = construct_object('api.subscription');
        
        //$ilance->auction_expiry->listings();
        
        $timeleft = $ilance->auction->auction_timeleft(intval($ilance->GPC['id']), 'alt1', 'center', 0, 0, 1, 1);
        
        $sql = $ilance->db->query("
                SELECT p.cid, p.user_id, p.status, p.date_starts, p.bids, p.startprice, p.currentprice, p.reserve, p.reserve_price, p.buynow, p.buynow_price, p.buynow_qty, p.date_end, p.close_date, p.currencyid, s.ship_method, p.buyer_fee
                FROM " . DB_PREFIX . "projects p
                LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
                WHERE p.project_id = '" . intval($ilance->GPC['id']) . "'
        ");
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        
        $cid = $res['cid'];
        $userid = $res['user_id'];
        $purchases = fetch_buynow_ordercount(intval($ilance->GPC['id']));
        $currencyid = $res['currencyid'];
        $endstext = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
        
        if ($res['ship_method'] == 'localpickup')
        {
                $showshippingrow = 1;
        }
        else
        {
                $showshippingrow = (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $ilance->GPC['id']])) ? 1 : 0;
        }
        
        switch ($ilance->GPC['type'])
        {
                case 'regular':
                {
                        $winningbidder = $winningbid = $refreshbidders = $reservetext = $highest_amount = '';
                        $showwinningbidderrow = $hidebuynowrow = $hideplacebidrow = $showblockheaderended = $hidebuynowactionrow = '0';
                        
                        $status = $res['status'];
                        if ($status != 'open')
                        {
                                $showblockheaderended = $hideplacebidrow = $hidebuynowactionrow = '1';
                                $timeleft = '<span class="black" style="font-size:13px; font-weight:bold">' . $phrase['_ended'] . '</span>';
                                
                                if ($ilance->bid->has_winning_bidder(intval($ilance->GPC['id'])))
                                {
                                        $winningbidder = '';
                                        $winningbidderid = $ilance->bid->fetch_highest_bidder(intval($ilance->GPC['id']));
                                        if ($winningbidderid > 0)
                                        {
                                                $showwinningbidderrow = '1';
                                                //$winningbidder = print_username($winningbidderid, 'href', 0, '', '');
                                                $winningbidder = fetch_user('username', $winningbidderid);
                                                $winningbid = $ilance->bid->fetch_awarded_bid_amount(intval($ilance->GPC['id']));
                                                //$winningbid = $ilance->currency->format($winningbid, $currencyid);
												
												//new change on Dec-04
												
												$buyer_fee = $winningbid+$res['buyer_fee'];
                                                $winningbid = $ilance->currency->format($winningbid, $currencyid)." (".$ilance->currency->format($buyer_fee, $currencyid)." with Buyer's Fee)";
                                        }
                                }
                        }
                        
						//new change
						if (!empty($_SESSION['ilancedata']['user']['userid']))
				        { 
							$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($ilance->GPC['id'], $_SESSION['ilancedata']['user']['userid']);
							if ($pbit > 0)
							{
								
							$highbidderid_my = $ilance->bid->fetch_highest_bidder($ilance->GPC['id']);	
																						
							if($highbidderid_my == $_SESSION['ilancedata']['user']['userid'])
							{
							$proxy_bit_my = '<span class="green">'.$phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';                   
							$winner_replace = '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 0px 2px 15px 0px;"><tbody><tr><td><div class="grayborder"><div class="n"><div class="e"><div class="w"></div></div></div><div><table cellspacing="0" cellpadding="0" border="0"><tbody><tr><td valign="top"></td><td><img height="1" border="0" width="5" id="" alt="" src="'. $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'].'spacer.gif"></td><td style="padding-right: 5px; padding-left: 3px;"><div><img border="0" id="" alt="" src="'. $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'].'icons/checkmark.gif"><strong>Congratulations! You are currently the high bidder for this auction!</strong></div><div style="padding-top: 4px;" class="black">However, another bidder might place a higher bid.  Please check your 
Watchlist regularly.  This item has been added to your Watchlist.</div></td></tr></tbody></table></div><div class="s"><div class="e"><div class="w"></div></div></div></div></td></tr></tbody></table>';
                           
							}
							else
							{
							$proxy_bit_my = '<span class="red">'.$phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';                           
							$outbid_replace = '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 0px 2px 15px 0px;"><tbody><tr><td><div class="grayborder"><div class="n"><div class="e"><div class="w"></div></div></div><div><table cellspacing="0" cellpadding="0" border="0"><tbody><tr><td valign="top"></td><td><img height="1" border="0" width="5" alt="" id="" src="'. $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'].'spacer.gif"></td><td style="padding-right: 5px; padding-left: 3px;"><div><strong>You were outbid by another bidder! Place a new maximum bid.</strong></div><div style="padding-top: 4px;" class="black">It appears another bidder has placed a higher bid.  Please bid again by 
entering the maximum amount you are willing to spend on this item (which is not necessarily the amount you will end up paying... our system will only bid up to your maximum bid should there be other competition for this item).</div></td></tr></tbody></table></div><div class="s"><div class="e"><div class="w"></div></div></div></div></td></tr></tbody></table>';
							
							}
							}
						}
						
						else
						{
						 $proxy_bit_my = '';
						 $winner_replace = '';
						 $outbid_replace = '';
						 
						}	
                      
                                                                                
																				
																				
                        $date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        $bids = $res['bids'];
                        $startprice = $res['startprice'];
                        $startprice_temp = $startprice;
                        $currentbid = $res['currentprice'];
                        $isreserve = $res['reserve'];
                        $reserve_price = $res['reserve_price'];                        
                        $buynow = $res['buynow'];
                        $buynow_price = $res['buynow_price'];
                        $buynow_qty = $res['buynow_qty'];
                        
                        if ($bids > 0 AND $currentbid > $startprice)
                        {
                                $startprice = '';
                                $currentbid = $ilance->currency->format($currentbid, $currencyid);
                        }
                        else if ($bids > 0 AND $currentbid == $startprice)
                        {
                                $startprice = '';
                                $currentbid = $ilance->currency->format($currentbid, $currencyid);        
                        }
                        else
                        {
                                $startprice = $ilance->currency->format($startprice, $currencyid);
                                $currentbid = '';        
                        }
                        
                        // fetch highest bidder username
                        $highbidderid = $ilance->bid->fetch_highest_bidder(intval($ilance->GPC['id']));
                        /*$highbidder = ($highbidderid > 0)
                                ? print_username($highbidderid, 'href', 0, '', '')
                                : '';*/
                        $highbidder = ($highbidderid > 0)
                                ? fetch_user('username', $highbidderid)
                                : '';
				
                        if ($isreserve)
                        {
                                $sql = $ilance->db->query("
                                        SELECT MAX(bidamount) AS highest
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                        ORDER BY highest
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $resbids = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        $reservetext = ($resbids['highest'] >= $reserve_price)
                                                ? '<span class="blueonly"> ' . $phrase['_yes_reserve_price_met'] . ' <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" /></span>'
                                                : '<span style="color:#ff6600">' . $phrase['_no_reserve_price_not_met'] . '</span>';
                                }
                                else
                                {
                                        $reservetext = '<span style="color:#ff6600">' . $phrase['_no_reserve_price_not_met'] . '</span>';
                                }
                        }
                        
                        if ($buynow == 0 OR $buynow_qty <= 0)
                        {
                                $hidebuynowrow = '1';
                        }
                        
                        // #### bid increments in this category ################
                        $increment = '';
                        $cbid = $ilance->bid->fetch_current_bid(intval($ilance->GPC['id']), $noformat = 1);
                        $slng = fetch_user_slng($userid);
                        
                        //$ilance->categories->build_array('product', $slng, 0, true);                        
                        $incrementgroup = $ilance->categories->incrementgroup($cid);
                        $sqlincrements = $ilance->db->query("
                                SELECT amount
                                FROM " . DB_PREFIX . "increments
                                WHERE ((increment_from <= $cbid
                                        AND increment_to >= $cbid)
                                                OR (increment_from < $cbid
                                        AND increment_to < $cbid))
                                        AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                                ORDER BY amount DESC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlincrements) > 0)
                        {
                                $resincrement = $ilance->db->fetch_array($sqlincrements, DB_ASSOC);
                        }
                        
                        $min_bidamount = sprintf("%.02f", '0.01');
                        $min_bidamountformatted = $ilance->currency->format('0.01', $currencyid);
                        $highestbid = 0;
                                        
                        if ($bids <= 0)
                        {
                                // do we have starting price?
                                if ($startprice_temp > 0)
                                {
                                        $min_bidamount = sprintf("%.02f", $startprice_temp);
                                        $min_bidamountformatted = $ilance->currency->format($startprice_temp, $currencyid);
                                }
                        }
                        else if ($bids > 0)
                        {
                                // highest bid amount placed for this auction
                                $highbid = $ilance->db->query("
                                        SELECT MAX(bidamount) AS maxbidamount
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($highbid) > 0)
                                {
                                        $reshighbid = $ilance->db->fetch_array($highbid, DB_ASSOC);
                                        $highestbid = sprintf("%.02f", $reshighbid['maxbidamount']);
                                }
                                
                                // if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
                                if (isset($resincrement['amount']) AND !empty($resincrement['amount']) AND $resincrement['amount'] > 0)
                                {
                                        $min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
                                        $min_bidamountformatted = $ilance->currency->format($highestbid + $resincrement['amount'], $currencyid);
                                }
                                else
                                {
                                        $min_bidamount = sprintf("%.02f", $highestbid);
                                        $min_bidamountformatted = $ilance->currency->format($highestbid, $currencyid);
                                }
                        }
                        
                        // adjust proxy details for logged in bidder if they already have a max proxy bid placed
                        if (!empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($ilance->GPC['id'], $_SESSION['ilancedata']['user']['userid']);
                                if ($pbit > 0)
                                {
                                        if ($pbit > $min_bidamount)
                                        {
                                                $min_bidamount = sprintf("%.02f", $pbit) + 0.01;
                                                $min_bidamountformatted = $ilance->currency->format($min_bidamount, $currencyid);
                                        }
                                }
                        }
                        
                        if ($res['close_date'] != '0000-00-00 00:00:00')
                        {
                                if ($res['close_date'] < $res['date_end'])
                                {
                                        $ends = print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $timeleft = '<span class="blue" style="font-size:13px">' . $phrase['_ended_early'] . '</span>';
                                }
                        }
                        
                        // #### realtime bidder refresh list ###########
                        $refreshbidders = '
                        <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '">
                        <tr class="alt2">
                              <td width="43%" nowrap="nowrap"><strong>' . $phrase['_bidder'] . '</strong></td>
                              <td width="33%" nowrap="nowrap"><strong>' . $phrase['_bid_amount'] . '</strong></td>
                              <td nowrap="nowrap"><strong>' . $phrase['_bid_placed'] . '</strong></td>
                        </tr>';
                        
						$result = $ilance->db->query("
                                SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added AS bidadded, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, p.project_id, p.escrow_id, p.cid, p.description, p.date_added, p.buynow_qty, p.date_end, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
                                FROM " . DB_PREFIX . "project_bids AS b,
                                " . DB_PREFIX . "projects AS p,
                                " . DB_PREFIX . "users AS u
                                WHERE b.project_id = '" . intval($ilance->GPC['id']) . "'
                                    AND b.project_id = p.project_id
                                    AND u.user_id = b.user_id
                                    AND b.bidstatus != 'declined'
                                    AND b.bidstate != 'retracted'
                                ORDER by b.bidamount DESC, b.bid_id DESC", 0, null, __FILE__, __LINE__);
							$result=$result;	 
                        if ($ilance->db->num_rows($result) > 0)
                        {
								$row_count = 0;
                                while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
								$list_bidderid[$row_count]=$resbids['user_id'];
								$row_count++;
								}
								$bidderid_list=array_reverse(array_unique($list_bidderid));
								$counter=0;
								foreach($bidderid_list as $bidders)
								{
									$seq=$counter+1; 
									$bidder_name_list[$bidders]="Bidder ".$seq;
									$counter++;
								} 
						}
                        $result = $ilance->db->query("
                                SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added AS bidadded, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, p.project_id, p.escrow_id, p.cid, p.description, p.date_added, p.buynow_qty, p.date_end, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
                                FROM " . DB_PREFIX . "project_bids AS b,
                                " . DB_PREFIX . "projects AS p,
                                " . DB_PREFIX . "users AS u
                                WHERE b.project_id = '" . intval($ilance->GPC['id']) . "'
                                    AND b.project_id = p.project_id
                                    AND u.user_id = b.user_id
                                    AND b.bidstatus != 'declined'
                                    AND b.bidstate != 'retracted'
                                ORDER by b.bidamount DESC, b.bid_id DESC", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($result) > 0)
                        {
                                $row_count = 0;
                                
                                while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
								if($row_count==0 )
								{
								// murugan changes on jan 31 for bidder name display
								if($resbids['user_id']==$_SESSION['ilancedata']['user']['userid'])
									$highbidder = fetch_user('username', $resbids['user_id']);
								else
									//$resbids['provider'] = $bidder_name_list[$resbids['user_id']];
									$highbidder=$bidder_name_list[$resbids['user_id']];
								}
								$resbids['bid_datetime'] = print_date($resbids['bidadded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
								//$resbids['provider'] = fetch_user('username', $resbids['user_id']);
								if($resbids['user_id']==$_SESSION['ilancedata']['user']['userid'])
									$resbids['provider'] = fetch_user('username', $resbids['user_id']);
								else
									$resbids['provider'] = $bidder_name_list[$resbids['user_id']];
								
                                        if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'])
                                        {
                                                $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
                                        }
                                        else if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
                                        {
                                                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
                                                {
                                                        $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
                                                }
                                                else
                                                {
                                                        $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $currencyid);
                                                }
                                        }
                                        else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'] AND $_SESSION['ilancedata']['user']['userid'] != $resbids['project_user_id'])
                                        {
                                                $resbids['bidamount'] = '= ' . $phrase['_sealed'] . ' =';
                                        }
                                        else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
                                        {
                                                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
                                                {
                                                        $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
                                                }
                                                else
                                                {
                                                        $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $currencyid);
                                                }
                                        }
                                        else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['project_user_id'])
                                        {
                                                $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
                                        }
                                        else
                                        {
                                                $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $currencyid);
                                        }
                                        
                                        if ($resbids['bidstatus'] == 'awarded')
                                        {
                                                $resbids['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" />';
                                                $awarded_vendor = stripslashes($resbids['username']);
                                                $resbids['bidamount'] = '<span style="font-size:15px"><strong>' . $resbids['bidamount'] . '</strong></span>';
                                        }
                                        else
                                        {
                                                $resbids['award'] = '';
                                        }
                                        
                                        if (!empty($resbids['proposal']))
                                        {
                                                // proxy bid
                                                $resbids['class'] = 'featured_highlight';
                                                $resbids['provider'] = $resbids['provider'];
                                                $resbids['bidamount'] = $resbids['bidamount'];
                                                $resbids['bid_datetime'] = $resbids['bid_datetime'];
                                        }
                                        else
                                        {
                                                // user bid
                                                $resbids['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
                                        }
                                        
                                        $row_count++;
                                        
                                        $refreshbidders .= '
                                        <tr class="' . $resbids['class'] . '" valign="top"> 
                                              <td nowrap="nowrap"><span style="float:right">' . $resbids['award'] . '</span><div><span style="font-family: arial; font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '; font-size:' . ($row_count == 1 ? '13px' : '13px') . '"" class="blue">' . $resbids['provider'] . '</span></div></td>
                                              <td nowrap="nowrap"><div style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '; font-size:' . ($row_count == 1 ? '14px' : '13px') . '">' . $resbids['bidamount'] . '</div></td>
                                              <td nowrap="nowrap"><div style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '; font-size:' . ($row_count == 1 ? '13px' : '13px') . '">' . $resbids['bid_datetime'] . '</div></td>
                                        </tr>';
                                        
                                        $bid_results_rows[] = $resbids;
                                        
                                }
                        }
                        
                        $refreshbidders .= '<tr>
                              <td valign="middle" nowrap="nowrap"><div><span style="font-family: arial; font-size:13px"><span class="gray">' . $phrase['_starting_bid'] . '</span></span></div></td>
                              <td valign="middle" nowrap="nowrap"><span class="gray">' . $ilance->currency->format($startprice_temp, $currencyid) . '</span></td>
                              <td valign="middle" nowrap="nowrap"><span class="gray">' . $date_starts . '</span></td>
                        </tr>
                        </table>';
                        
                        // timeleft       |bids         |US$ 5.00           |US$ 15.00          |Reserve Price Met   |US$ 18.50                      |18.50                 |purchases         |highest bidder     |1 or 0                |1 or 0                      |Thu, Apr 08, 2010 10:04 PM|   1 or 0      |<html>                 |1 or 0                       |0|1 or 0                       |US$10.00           |1 or 0
						
						//new change last variable  $winner_replace = '';
						
                        echo $timeleft . '|' . $bids . '|' . $startprice . '|' . $currentbid . '|' . $reservetext . '|' . $min_bidamountformatted . '|' . $min_bidamount . '|' . $purchases . '|' . $highbidder . '|' . $hidebuynowrow . '|' . $hidebuynowactionrow . '|' . $endstext . '|' . $hideplacebidrow . '|' . $refreshbidders . '|' . $showwinningbidderrow . '|0|' . $showblockheaderended . '|' . $winningbid . '|' . $showshippingrow . '|' . $proxy_bit_my . '|' . $winner_replace . '|' . $outbid_replace;
                        break;
                }        
                case 'fixed':
                {
                        $status = fetch_auction('status', intval($ilance->GPC['id']));
                        $hidebuynowrow = $showblockheaderended = 0;
                        if ($status != 'open')
                        {
                                $timeleft = '<span class="black" style="font-size:13px; font-weight:bold">' . $phrase['_ended'] . '</span>';
                                $hidebuynowrow = $showblockheaderended = '1';                                
                        }
                        
                        echo $timeleft . '|' . $purchases . '|' . $hidebuynowrow . '|' . $showblockheaderended . '|' . $showshippingrow . '|' . $endstext;
                        break;
                }        
                case 'unique':
                {
                        $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                        
                        $status = fetch_auction('status', intval($ilance->GPC['id']));
                        
                        $retailpriceraw = fetch_auction('retailprice', intval($ilance->GPC['id']));
                        $retailprice = $ilance->currency->format($retailpriceraw, $currencyid);
                        $bids = $ilance->bid_lowest_unique->fetch_bid_count(intval($ilance->GPC['id']));
                        $bidsuntilend = fetch_auction('uniquebidcount', intval($ilance->GPC['id']));
                        $bidsleft = $row_count = 0;
                        if ($bids > 0 AND $bidsuntilend > 0)
                        {
                                $bidsleft = $bidsuntilend - $bids;
                                if ($bidsleft < 0)
                                {
                                        $bidsleft = 0;
                                }
                        }
                        
                        $uniquewinner = $winningbid = '';
                        $showblockheaderended = $showwinningbidderrow = '0';
                        if (($status != 'open') OR ($bidsuntilend > 0 AND $bids > 0 AND $bids == $bidsleft))
                        {
                                $showblockheaderended = '1';
                                $timeleft = '<span class="black" style="font-size:13px; font-weight:bold">' . $phrase['_ended'] . '</span>';
                                if ($ilance->bid_lowest_unique->has_unique_bid_winner(intval($ilance->GPC['id'])))
                                {
                                        $savings = fetch_savings_total($retailpriceraw, $ilance->bid_lowest_unique->fetch_lowest_unique_bid_winner_amount(intval($ilance->GPC['id']), false));
                                        $showwinningbidderrow = '1';
                                        $uniquewinner = $ilance->bid_lowest_unique->fetch_lowest_unique_bid_winner(intval($ilance->GPC['id']));
                                        $winningbid = $ilance->bid_lowest_unique->fetch_lowest_unique_bid_winner_amount(intval($ilance->GPC['id']), true) .
                                                '<div style="padding-top:3px"><span class="red">(<span style="font-size:15px">' . $savings['savingspercentage'] . '%</span> ' . $phrase['_savings_lower'] . ')</span></div>';
                                                
                                        unset($savings);
                                }
                        }
                        
                        $refreshbidders = '';
                        $userid = (empty($_SESSION['ilancedata']['user']['userid'])) ? 0 : $_SESSION['ilancedata']['user']['userid'];
                        
                        // #### is user sorting by status? #####################
                        $ilance->GPC['sort'] = (isset($ilance->GPC['sortby']) AND $ilance->GPC['sortby'] == 'status' AND isset($ilance->GPC['sort']))
                                ? mb_strtoupper($ilance->GPC['sort'])
                                : 'DESC';
                        
                        $refreshbidders .= '
                        <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '">
                        <tr class="alt2">
                                <td width="6%" nowrap="nowrap"><strong>' . $phrase['_bid_id'] . '</strong></td>
                                <td width="40%" nowrap="nowrap"><strong>' . $phrase['_response'] . '</strong></td>
                                <td width="17%" nowrap="nowrap"><strong>' . $phrase['_status'] . '</strong></td>
                                <td width="9%" nowrap="nowrap"><strong>' . $phrase['_bids'] . '</strong></td>
                                <td width="11%" nowrap="nowrap"><strong>' . $phrase['_bid_amount'] . '</strong></td>
                                <td width="12%" nowrap="nowrap"><strong>' . $phrase['_bid_placed'] . '</strong></td>
                                <td width="5%" nowrap="nowrap"><strong>' . $phrase['_status'] . '</strong></td>
                        </tr>';
                        
                        // #### unique bidders query ###################
                        $result = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "projects_uniquebids
                                WHERE user_id = '" . $userid . "'
                                    AND project_id = '" . intval($ilance->GPC['id']) . "'
                                ORDER BY status " . $ilance->db->escape_string($ilance->GPC['sort']) . "
                                LIMIT 5
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($result) > 0)
                        {
                                while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
                                        $resbids['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
                                        
                                        // date of placed bid
                                        $resbids['date'] = print_date($resbids['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $resbids['award'] = ($resbids['status'] == 'lowestunique' AND $res['status'] != 'open') ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" />' : '';
                                        
                                        if ($resbids['status'] == 'unique')
                                        {
                                                $resbids['amountstatus'] = '<strong>' . $phrase['_unique_bid'] . '</strong>';
                                                $resbids['response'] = '<span class="black">' . $phrase['_your_bid'] . ': ' . $ilance->currency->format($resbids['uniquebid'], $currencyid) . ' ' . $phrase['_is_unique_but_there_is_currently'] . ' <strong>' . $ilance->bid_lowest_unique->fetch_lower_unique_bids($resbids['user_id'], $resbids['project_id'], $resbids['uniquebid']) . '</strong> ' . $phrase['_lower_unique_bids_placed_than_yours'] . '</span>';
                                                $resbids['uniquebid'] = '<span class="black">' . $ilance->currency->format($resbids['uniquebid'], $currencyid) . '</span>';
                                                $resbids['date'] = '<span class="black">' . $resbids['date'] . '</span>';
                                        }
                                        else if ($resbids['status'] == 'nonunique')
                                        {
                                                $resbids['amountstatus'] = '<span class="gray">' . $phrase['_non_unique'] . '</span>';
                                                $resbids['response'] = '<span class="gray">' . $phrase['_your_bid'] . ': ' . $ilance->currency->format($resbids['uniquebid'], $currencyid) . ' is <strong>not-unique</strong> because there is currently <strong>' . $resbids['totalbids'] . '</strong> other bids with the same amount placed.</span>';
                                                $resbids['totalbids'] = '<span class="gray">' . $resbids['totalbids'] . '</span>';
                                                $resbids['uniquebid'] = '<span class="gray">' . $ilance->currency->format($resbids['uniquebid'], $currencyid) . '</span>';
                                                $resbids['date'] = '<span class="gray">' . $resbids['date'] . '</span>';
                                        }
                                        else if ($resbids['status'] == 'lowestunique')
                                        {
                                                $resbids['uid'] = '<span style="font-size:14px"><strong>' . $resbids['uid'] . '</strong></span>';
                                                $resbids['amountstatus'] = '<span style="font-weight:bold" class="black"><strong>'.$phrase['_lowest_unique'].'</strong></span>';
                                                $resbids['response'] = '<span class="black" style="font-weight:bold">' . $phrase['_your_bid'] . ': ' . $ilance->currency->format($resbids['uniquebid'], $currencyid) . ' ' . $phrase['_is_currently_the_lowest_unique_bid_placed'] . '</span>';
                                                $resbids['totalbids'] = '<span style="font-weight:bold" class="black">' . $resbids['totalbids'] . '</span>';
                                                $resbids['uniquebid'] = '<span class="black" style="font-weight:bold">' . $ilance->currency->format($resbids['uniquebid'], $currencyid) . '</span>';
                                                $resbids['date'] = '<span class="black" style="font-weight:bold">' . $resbids['date'] . '</span>';
                                        }
                                        
                                        $row_count++;
                                        
                                        $refreshbidders .= '
                                        <tr class="' . $resbids['class'] . '" valign="top">
                                                <td nowrap="nowrap" align="center">' . $resbids['uid'] . '</td> 
                                                <td>' . $resbids['response'] . '</td>
                                                <td nowrap="nowrap">' . $resbids['amountstatus'] . '</td>
                                                <td nowrap="nowrap">' . $resbids['totalbids'] . '</td>
                                                <td nowrap="nowrap">' . $resbids['uniquebid'] . '</td>
                                                <td nowrap="nowrap">' . $resbids['date'] . '</td>
                                                <td nowrap="nowrap" align="center">' . $resbids['award'] . '</td>
                                        </tr>';
                                        
                                        $unique_bid_results_rows[] = $resbids;
                                }
                                
                                $refreshbidders .= '</table>';
                        }
                        
                        echo $timeleft . '|' . $bids . '|' . $retailprice . '|' . $bidsleft . '|' . $showblockheaderended . '|' . $showwinningbidderrow . '|' . $uniquewinner . '|' . $winningbid . '|' . $refreshbidders . '|' . $showshippingrow . '|' . $endstext;
                        break;
                }
        }
        exit();
}
// #### SHOW STATES BASED ON COUNTRIES #########################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'showstates' AND isset($ilance->GPC['countryname']) AND !empty($ilance->GPC['countryname']) AND isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
{
        global $phrase;
        
	$locationid = fetch_country_id($ilance->GPC['countryname'], $_SESSION['ilancedata']['user']['slng']);
        $html = construct_state_pulldown($locationid, '', $ilance->GPC['fieldname']);
        
        ($apihook = $ilance->api('ajax_do_showstates_end')) ? eval($apihook) : false;
	
        echo $html;
        exit();
}
// #### SHOW SHIPPING SERVICES BASED ON SHIP TO OPTION SELECTED ################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'showshippers' AND isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
{
        global $phrase;
        
        $ilance->auction = construct_object('api.auction');
        $ilance->auction_post = construct_object('api.auction_post');
        
	$ilance->GPC['domestic'] = isset($ilance->GPC['domestic']) ? $ilance->GPC['domestic'] : 'false';
        $ilance->GPC['international'] = isset($ilance->GPC['international']) ? $ilance->GPC['international'] : 'false';
        $ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
        
        $html = $ilance->auction_post->print_shipping_partners($ilance->GPC['fieldname'], false, $ilance->GPC['domestic'], $ilance->GPC['international'], $ilance->GPC['shipperid']);
        
        ($apihook = $ilance->api('ajax_do_showshippers_end')) ? eval($apihook) : false;
	
        echo $html;
        exit();
}
// #### SHOW SHIPPING SERVICES BASED ON SHIP TO OPTION SELECTED ################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'shipcalculator' AND isset($ilance->GPC['modal_shipperid']) AND isset($ilance->GPC['weight']) AND isset($ilance->GPC['modal_country_from']) AND isset($ilance->GPC['modal_zipcode_from']) AND isset($ilance->GPC['modal_country_to']) AND isset($ilance->GPC['modal_zipcode_to']))
{
        $ilance->GPC['modal_carrier'] = $ilance->db->fetch_field(DB_PREFIX . "shippers", "shipperid = '" . intval($ilance->GPC['modal_shipperid']) . "'", "carrier");
        $ilance->GPC['modal_shipcode'] = $ilance->db->fetch_field(DB_PREFIX . "shippers", "shipperid = '" . intval($ilance->GPC['modal_shipperid']) . "'", "shipcode");
        $carriers[$ilance->GPC['modal_carrier']] = true;
        
        $ilance->shipcalculator = construct_object('api.shipcalculator');
        $test = $ilance->shipcalculator->get_rates($ilance->GPC['weight'], $ilance->GPC['modal_zipcode_to'], $ilance->GPC['modal_country_to'], $ilance->GPC['modal_zipcode_from'], $ilance->GPC['modal_country_from'], $carriers, $ilance->GPC['modal_shipcode']);
        if (isset($test['price'][0]))
        {
                //echo handle_input_keywords($test['name'][0]) . ': <span class="blue">$ ' . sprintf("%01.2f", $test['price'][0]) . '</span>';
                echo '<span class="blue">$ ' . sprintf("%01.2f", $test['price'][0]) . '</span>';
        }
        else
        {
                echo $phrase['_out_of_region_try_again'];
        }
        
        ($apihook = $ilance->api('ajax_do_shipcalculator_end')) ? eval($apihook) : false;
        
	unset($test);
        exit();
}
// #### SHOW SHIPPING SERVICE ROWS BASED ON SHIP TO COUNTRY OPTION SELECTED ####
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'showshipservicerows' AND isset($ilance->GPC['countryid']) AND isset($ilance->GPC['pid']))
{
        // #### require shipping backend #######################################
        require_once(DIR_CORE . 'functions_shipping.php');
        
        $output = '';
        $rows = 0;
        
        $ilance->GPC['qty'] = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
        $ilance->GPC['radiuszip'] = isset($ilance->GPC['radiuszip']) ? $ilance->GPC['radiuszip'] : '';
               
        $result = $ilance->db->query("
                SELECT p.row, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS countrytitle, l.region
                FROM " . DB_PREFIX . "projects_shipping_regions p
                LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
                WHERE p.project_id = '" . intval($ilance->GPC['pid']) . "'
                    AND p.countryid = '" . intval($ilance->GPC['countryid']) . "'
                ORDER BY p.row ASC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($result) > 0)
        {
                while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
                {
                        $output .= '|' . fetch_ajax_ship_service_row($res['row'], $ilance->GPC['pid'], $res['countrytitle'], $res['region'], $ilance->GPC['qty']); // returns: |ship cost~~~~ship to country title~~~~ship service title~~~~est delivery info
                        $rows++;
                }
        }
        
        // fetch fixed region from supplied country id
        $region = fetch_region_by_countryid($ilance->GPC['countryid']);
        if (!empty($region) AND !empty($ilance->GPC['radiuszip']))
        {
                $ilance->GPC['region'] = $region . '.' . $ilance->GPC['countryid'];
        }
        
        // check if a region along with a country id is selected: example: europe.219
	if (!empty($ilance->GPC['region']) AND strrchr($ilance->GPC['region'], '.'))
	{
		set_cookie('region', handle_input_keywords($ilance->GPC['region']), true);
	}
	
	// check if user supplied us with a zip code
	if (!empty($ilance->GPC['radiuszip']))
	{
		set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['radiuszip'])), true);
	}
        
        echo $rows . $output;
        
        ($apihook = $ilance->api('ajax_do_showshipservicerows_end')) ? eval($apihook) : false;
        
        exit();
}
// ##### Murugan Script For Promo Code Inventory System . ####
// ##### Below This Code is Used to Calculate the Reduction Amount From the Promo Code ####
// ##### This Code only for BIN sales Type . BIN - Buy Now ####
// ##### Here we check various Condition Which are all included in Promo Code. ####
if(isset($ilance->GPC['promocode']))
{
    //echo $ilance->GPC['promocode'];
	//echo $ilance->GPC['projectid'];
	
	$selectproject= $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = '".$ilance->GPC['projectid']."'");
	while($pjtres = $ilance->db->fetch_array($selectproject))
	{
	   $catid = $pjtres['cid'];
	   $buynow = $pjtres['buynow_price'];
	}
	
	$selectpromo= $ilance->db->query("SELECT * FROM " . DB_PREFIX . "promo_inventory WHERE promoCode = '".$ilance->GPC['promocode']."'");
	if ($ilance->db->num_rows($selectpromo) > 0)
        {
                
				while ($res = $ilance->db->fetch_array($selectpromo, DB_ASSOC))
                {
				
				  $explode = explode(',',$res['categoryID']);			
				
				   if($res['validDate'] >= date('Y-m-d') || $res['validDate'] == '0000-00-00')
				   {
				   	   if($res['userID'] == $_SESSION['ilancedata']['user']['userid'] || $res['userID'] == '' )
					   {
					   	   if($res['salesType'] == 'Both' || $res['salesType'] == 'Bin')
						   {
						     
					   			if($res['categoryID'] == '' || in_array($catid,$explode))
								{
									if($res['conditionAmt'] <= $buynow)
									{
										 if($res['offerType'] == 'dollar')
										 {
											echo $res['offerAmt'].' $ &nbsp;';
										 }
										 if($res['offerType'] == 'percentage')
										 {
											echo $res['offerAmt'] .'&nbsp; %';
										 }
									}
									else
									{
									   echo 'This Buy Now Amount Not Reach The Condidtion Amount';
									}
								}
								else
								{
								   echo 'This Promo Code Not Applicable For This Coin Category';
								}
							}
							else
							{
							   echo 'This Promo Code is not applicable for this Sales Type';
							}
					    }
						else
						{
						   echo 'This Promo Code not available For You';
						}
					
				   }
				   else
				   {
				      echo 'Promo Code Expired';
				   }			   
				 
				}
		}
		else
		{
		   echo 'Promo Code Not Matching';
		}
  
}
// OCT 12 Murugan For Promo Code Auction
if(isset($ilance->GPC['promocodeauction']))
{
   // echo $ilance->GPC['promocodeauction'];
	//echo $ilance->GPC['projectid'];
	
	
	$sql_regardlist = $ilance->db->query("
			SELECT SUM(amount) AS amt
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid']."'
			AND status = 'unpaid'	and not combine_project		
		");
	$ress = $ilance->db->fetch_array($sql_regardlist);
		$buynow = $ress['amt'];
	/*$selectbid= $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE project_id = '".$ilance->GPC['projectid']."' AND bidstatus='awarded'");
	$selectproject= $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = '".$ilance->GPC['projectid']."' ");
	while($bidres = $ilance->db->fetch_array($selectbid))	{
	   
	   $buynow = $bidres['bidamount'];
	}
	while($pjtres = $ilance->db->fetch_array($selectproject))	{
	   
	   $cid = $pjtres['cid'];
	}*/
	
	$selectpromo= $ilance->db->query("SELECT * FROM " . DB_PREFIX . "promo_inventory WHERE promoCode = '".$ilance->GPC['promocodeauction']."'");
	if ($ilance->db->num_rows($selectpromo) > 0)
        {
                
				while ($res = $ilance->db->fetch_array($selectpromo, DB_ASSOC))
                {
				   if($res['validDate'] >= date('Y-m-d') || $res['validDate'] == '0000-00-00')
				   {
				   	   if($res['userID'] == $_SESSION['ilancedata']['user']['userid'] || $res['userID'] == '' )
					   {
					   	   if($res['salesType'] == 'Both' || $res['salesType'] == 'Auction')
						   {
						     
									if($res['conditionAmt'] <= $buynow)
									{
										 if($res['offerType'] == 'dollar')
										 {
											echo $res['offerAmt'].'|$';
										 }
										 if($res['offerType'] == 'percentage')
										 {
											$cal = $buynow * ($res['offerAmt'] / 100 ) ;
											echo $cal .'|$';
										 }
									}
									else
									{
									   echo 'This Buy Now Amount Not Reach The Condidtion Amount';
									}
								
							}
							else
							{
							   echo 'This Promo Code is not applicable for this Sales Type';
							}
					    }
						else
						{
						   echo 'This Promo Code not available For You';
						}
					
				   }
				   else
				   {
				      echo 'Promo Code Expired';
				   }			   
				 
				}
		}
		else
		{
		   echo 'Promo Code Not Matching';
		}
  
}




//seller pending lightbox
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'lighrbox_get' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	if(isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql_atty = $ilance->db->query("
                       SELECT * FROM
                       " . DB_PREFIX . "attachment
                       WHERE visible='1'
                                               AND project_id = '".$ilance->GPC['id']."'
                                               AND attachtype IN ('itemphoto','slideshow')
                                               
                       ");
                
					   if($ilance->db->num_rows($sql_atty) > 0)
					   {
							
							$i = 0;
							
							while ($res_coin = $ilance->db->fetch_array($sql_atty))
							{
							   $img_hash[$i]['img_hash'] = $res_coin['filehash'];
							   $testing = "http://www.greatcollections.com/image.php?cmd=thumb&subcmd=itemphoto&id=".$res_coin['filehash']."";
				
								list($width, $height, $type, $attr) = getimagesize($testing);
								
									$img_hash[$i]['w'] = ($width*60)/100;
									$img_hash[$i]['h'] = ($height*60)/100;
									
								
								
								
								$i++;
							}     
					   }
			
				
			
		echo json_encode($img_hash);
	}
	
	exit;
}



($apihook = $ilance->api('ajax_end')) ? eval($apihook) : false;
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>