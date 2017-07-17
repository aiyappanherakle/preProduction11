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

/**
* Core Tab functions for ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/*
* ...
*
* @param       
*
* @return      
*/
function print_buying_activity_tab_options($button = '', $cattype = '', $userid = 0, $extra = '')
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_tabs = construct_object('api.bid_tabs');
	
	switch ($cattype)
	{
		// #### SERVICE AUCTIONS BUYING ACTIVITY TAB COUNT #############
		case 'service':
		{
			$activerfps = $ilance->bid_tabs->fetch_service_bidtab_sql('active', 'count', $userid, $extra);
			$endedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('expired', 'count', $userid, $extra);
			$awardedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('awarded', 'count', $userid, $extra);
			$archivedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('archived', 'count', $userid, $extra);
			$delistedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('delisted', 'count', $userid, $extra);
			$draftrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('drafts', 'count', $userid, $extra);
			$pendingrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('pending', 'count', $userid, $extra);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$extra1 = str_replace('p.date_added', 'e.date_awarded', $extra);
				$serviceescrow = $ilance->bid_tabs->fetch_service_bidtab_sql('serviceescrow', 'count', $userid, $extra1);
			}
			unset($extra1);
			
			// display period (if applicable)
			$periodbit = (isset($ilance->GPC['period']) AND $ilance->GPC['period'] > 0) ? '&amp;period=' . intval($ilance->GPC['period']) : '';
			
			// display order (if applicable)
			$displayorderbit = (isset($ilance->GPC['displayorder']) AND !empty($ilance->GPC['displayorder'])) ? '&amp;displayorder=' . ilance_htmlentities($ilance->GPC['displayorder']) : '';
			
			switch ($button)
			{
				case 'active':
				{
					$html = '
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
				case 'ended':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
				case 'awarded':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
				case 'archived':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'delisted':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'drafts':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'rfp-pending':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'rfp-escrow':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activerfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $endedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_awarded'] . ' (' . $awardedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archivedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delistedrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftrfps . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
			}
			break;
		}
		
		// #### PRODUCT AUCTION BUYING ACTIVITY TAB COUNT ##############
		case 'product':
		{
			$activebids = $ilance->bid_tabs->fetch_product_bidtab_sql('active', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$awardedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$invitedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$expiredbids = $ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$retractedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			
			// buynow and escrow
			$extra1 = str_replace('p.date_added', 'b.orderdate', $extra);
			$extra2 = '';
			$buynowproductescrow = $ilance->bid_tabs->fetch_product_bidtab_sql('buynowproductescrow', 'count', '', '', '', $userid, $extra1);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$extra2 = str_replace('p.date_added', 'e.date_awarded', $extra);
				$productescrow = $ilance->bid_tabs->fetch_product_bidtab_sql('productescrow', 'count', '', '', '', $userid, $extra2);
			}
			unset($extra1, $extra2);
			
			// display period (if applicable)
			$periodbit = (isset($ilance->GPC['period2']) AND $ilance->GPC['period2'] > 0) ? '&amp;period2=' . intval($ilance->GPC['period2']) : '';
			
			// display order (if applicable)
			$displayorderbit = (isset($ilance->GPC['displayorder2']) AND !empty($ilance->GPC['displayorder2'])) ? '&amp;displayorder2=' . ilance_htmlentities($ilance->GPC['displayorder2']) : '';
			
			switch ($button)
			{
				case 'active':
				{
					$html = '
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				case 'awarded':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . '  (' . $expiredbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				/*case 'invited':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}	*/		    
				case 'expired':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>-->
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				/*case 'retracted':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}	*/		    
				case 'buynow-escrow':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>-->
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}
				case 'product-escrow':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>
					<!--<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>-->
					<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_buy_now'] . ' (' . $buynowproductescrow . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}
			}
			break;
		}
		
		case 'unique':
		{
			$activebids = $ilance->bid_tabs->fetch_product_bidtab_sql('active_unique', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$awardedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded_unique', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$expiredbids = $ilance->bid_tabs->fetch_product_bidtab_sql('expired_unique', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			
			// display period (if applicable)
			$periodbit = '';
			if (isset($ilance->GPC['period2']) AND $ilance->GPC['period2'] > 0)
			{
				$periodbit = '&amp;period2=' . intval($ilance->GPC['period2']);	
			}
			
			// display order (if applicable)
			$displayorderbit = '';
			if (isset($ilance->GPC['displayorder2']) AND !empty($ilance->GPC['displayorder2']))
			{
				//$displayorderbit = '&amp;displayorder2=' . htmlentities($ilance->GPC['displayorder2'], ENT_QUOTES);
				$displayorderbit = '&amp;displayorder2=' . ilance_htmlentities($ilance->GPC['displayorder2']);	
			}
			
			switch ($button)
			{
				case 'active':
				{
					$html = '
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>';
					break;
				}			    
				case 'awarded':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_i_lost'] . '  (' . $expiredbids . ')</a></li>';
					break;
				}			    
				case 'expired':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub' . $periodbit . $displayorderbit . '">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">' . $phrase['_i_won'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_i_lost'] . ' (' . $expiredbids . ')</a></li>';
					break;
				}			    
			}
			break;
		}
	}
	
	return $html;
}
    
/*
* ...
*
* @param       
*
* @return      
*/
function print_selling_activity_tab_options($button = '', $cattype = '', $userid = 0, $extra = '')
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_tabs = construct_object('api.bid_tabs');
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_tabs = construct_object('api.auction_tabs');
	
	switch ($cattype)
	{
		// #### SERVICE AUCTIONS SELLING ACTIVITY TAB COUNT ############
		case 'service':
		{
			$activebids = $ilance->bid_tabs->fetch_bidtab_sql('active', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$awardedbids = $ilance->bid_tabs->fetch_bidtab_sql('awarded', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$archivedbids = $ilance->bid_tabs->fetch_bidtab_sql('archived', 'count', '', '', '', $userid, $extra);
			$declinedbids = $ilance->bid_tabs->fetch_bidtab_sql('delisted', 'count', '', '', '', $userid, $extra);
			$retractedbids = $ilance->bid_tabs->fetch_bidtab_sql('retracted', 'count', '', '', '', $userid, $extra);
			$invitedbids = $ilance->bid_tabs->fetch_bidtab_sql('invited', 'count', '', '', '', $userid, $extra);
			$expiredbids = $ilance->bid_tabs->fetch_bidtab_sql('expired', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			if ($ilconfig['escrowsystem_enabled'])
			{
			       $serviceescrow = $ilance->bid_tabs->fetch_bidtab_sql('serviceescrow', 'count', '', '', '', $userid, $extra);
			}
			
			// display period (if applicable)
			$periodbit = (isset($ilance->GPC['period2']) AND $ilance->GPC['period2'] > 0) ? '&amp;period2=' . intval($ilance->GPC['period2']) : '';
			
			// display order (if applicable)
			$displayorderbit = (isset($ilance->GPC['displayorder2']) AND !empty($ilance->GPC['displayorder2'])) ? '&amp;displayorder2=' . ilance_htmlentities($ilance->GPC['displayorder2']) : '';
			
			switch ($button)
			{
				case 'active':
				{
					$html = '
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'awarded':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'archived':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'delisted':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' ('.$archivedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'invited':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'expired':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'retracted':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $serviceescrow.')</a></li>';
					}
					break;
				}			    
				case 'escrow':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_active'] . ' (' . $activebids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_awarded'] . ' (' . $awardedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_archived'] . ' (' . $archivedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_declined'] . ' (' . $declinedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_retracted'] . ' (' . $retractedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_invited'] . ' (' . $invitedbids . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">' . $phrase['_expired'] . ' (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_escrow'] . ' (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
			}
			break;
		}
		// #### PRODUCT AUCTION SELLING ACTIVITY TAB COUNT #############
		case 'product':
		{
			$activeitems = $ilance->auction_tabs->product_auction_tab_sql('active', 'count', $userid, $extra);
			$archiveditems = $ilance->auction_tabs->product_auction_tab_sql('archived', 'count', $userid, $extra);
			$delisteditems = $ilance->auction_tabs->product_auction_tab_sql('delisted', 'count', $userid, $extra);
			$expireditems = $ilance->auction_tabs->product_auction_tab_sql('expired', 'count', $userid, $extra);
			$pendingitems = $ilance->auction_tabs->product_auction_tab_sql('pending', 'count', $userid, $extra);
			$draftitems = $ilance->auction_tabs->product_auction_tab_sql('drafts', 'count', $userid, $extra);
			$solditems = $ilance->auction_tabs->product_auction_tab_sql('sold', 'count', $userid, $extra);
			if ($ilconfig['escrowsystem_enabled'])
			{
			       $productescrow = $ilance->auction_tabs->product_auction_tab_sql('productescrow', 'count', $userid, $extra);
			}
			
			// display period (if applicable)
			$periodbit = '';
			if (isset($ilance->GPC['period']) AND $ilance->GPC['period'] > 0)
			{
				$periodbit = '&amp;period=' . intval($ilance->GPC['period']);	
			}
			
			// display order (if applicable)
			$displayorderbit = '';
			if (isset($ilance->GPC['displayorder']) AND !empty($ilance->GPC['displayorder']))
			{
				//$displayorderbit = '&amp;displayorder=' . htmlentities($ilance->GPC['displayorder'], ENT_QUOTES);
				$displayorderbit = '&amp;displayorder=' . ilance_htmlentities($ilance->GPC['displayorder']);	
			}
			
			switch ($button)
			{
				case 'active':
				{
					$html = '
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}
				case 'sold':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}
				case 'archived':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				case 'delisted':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived"' . $periodbit . $displayorderbit . '>' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired"' . $periodbit . $displayorderbit . '>' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending"' . $periodbit . $displayorderbit . '>' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				case 'expired':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}
				case 'drafts':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				case 'pending':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="highlight"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}			    
				case 'escrow':
				{
					$html = '
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">' . $phrase['_im_selling'] . ' (' . $activeitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">' . $phrase['_ive_sold'] . ' (' . $solditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">' . $phrase['_archived'] . ' (' . $archiveditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">' . $phrase['_delisted'] . ' (' . $delisteditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">' . $phrase['_ended'] . ' (' . $expireditems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">' . $phrase['_draft'] . ' (' . $draftitems . ')</a></li>
					<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">' . $phrase['_pending'] . ' (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">' . $phrase['_escrow'] . ' (' . $productescrow . ')</a></li>';
					}
					break;
				}
			}
			break;
		}
	}
	
	return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_buying_activity_tabs($tab = '', $cattype = '', $userid = 0, $extra = '')
{
	$html = '
	<div class="bigtabs" style="padding-bottom:9px">
		<div class="bigtabsheader">
			<ul>' . print_buying_activity_tab_options($tab, $cattype, $userid, $extra) . '</ul>
		</div>
	</div>
	<div style="clear:both;"></div>';
	
	return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_selling_activity_tabs($tab = '', $cattype = '', $userid = 0, $extra = '')
{
	$html = '
	<div class="bigtabs" style="padding-bottom:9px">
		<div class="bigtabsheader">
			<ul>' . print_selling_activity_tab_options($tab, $cattype, $userid, $extra) . '</ul>
		</div>
	</div>
	<div style="clear:both;"></div>';
	
	return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>