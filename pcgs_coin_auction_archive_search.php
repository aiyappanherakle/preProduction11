<?php
/*Tamil For Bug 2695 * Starts*/
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
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
	);
// #### load required javascript ###############################################
	$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
        'modal',
	'flashfix'
);
// #### define top header nav ##################################################
	$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
	define('LOCATION', 'merch');
// #### require backend ########################################################
	require_once('./functions/config.php');
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_search_prefs.php');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
	$ilance->bid_proxy = construct_object('api.bid_proxy');
// #### require shipping backend #######################
	require_once(DIR_CORE . 'functions_shipping.php');
	
	$page_title = SITE_NAME . ' - ' . 'PCGS Coin Auction Archive Search';
	$navcrumb = array("pcgs_coin_auction_archive_search.php" => "PCGS Coin Auction Archive Search" );
	$show['widescreen']=true;
	
	
	if(isset($ilance->GPC['sort']) OR isset($ilance->GPC['action']))	
	{
		$scriptpageprevnext = 'pcgs_coin_auction_archive_search.php?&sort='.$ilance->GPC['sort'].'&action='.$ilance->GPC['action'];
	}	
	else
	{
		$scriptpageprevnext = 'pcgs_coin_auction_archive_search.php?';
	}
	
	
	if(isset($ilance->GPC['pcgs']) OR isset($ilance->GPC['ngc']))
	{
		$grading_service1='<input type="checkbox" name="pcgs" id="pcgs" value="PCGS" checked>PCGS';
		$grading_service2='<input type="checkbox" name="ngc" id="ngc" value="NGC" checked>NGC';
	}
	else
	{
		$grading_service1='<input type="checkbox" name="pcgs" id="pcgs" value="PCGS" checked>PCGS';
		$grading_service2='<input type="checkbox" name="ngc" id="ngc" value="NGC" checked>NGC';
	}
	
	
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='pcgs_coin_search')
	{
		$lowest_grade=($ilance->GPC['grade_range1'] < $ilance->GPC['grade_range2'])? $ilance->GPC['grade_range1'] : $ilance->GPC['grade_range2'];
		$highest_grade=($ilance->GPC['grade_range1'] > $ilance->GPC['grade_range2'])? $ilance->GPC['grade_range1'] : $ilance->GPC['grade_range2'];
		
		if(!empty($ilance->GPC['pcgs_no']))
		{
			$pcgs_grading=($lowest_grade==$highest_grade)? " AND pcgs=".$ilance->GPC['pcgs_no']." AND Grade=".$lowest_grade." " : " AND pcgs=".$ilance->GPC['pcgs_no']." AND (Grade BETWEEN ".$lowest_grade." AND ".$highest_grade." )";
			$pcgs_number=$ilance->GPC['pcgs_no'];
		}
		else
		{
			$pcgs_grading=($lowest_grade==$highest_grade)? " AND Grade=".$lowest_grade." " : " AND (Grade BETWEEN ".$lowest_grade." AND ".$highest_grade." )";			
			$pcgs_number='';
		}
				
		if(isset($ilance->GPC['pcgs']) AND isset($ilance->GPC['ngc']))
		{
			$grading_service=" AND (Grading_Service='PCGS' OR Grading_Service='NGC') ";
			$scriptpageprevnext = 'pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort='.$ilance->GPC['sort'].'&action='.$ilance->GPC['action'];
			$switch_variable=$ilance->GPC['cmd'].'|'.$ilance->GPC['action'].'|'.$ilance->GPC['pcgs'].'|'.$ilance->GPC['ngc'];
			$grading_service1='<input type="checkbox" name="pcgs" id="pcgs" value="PCGS" checked>PCGS';
			$grading_service2='<input type="checkbox" name="ngc" id="ngc" value="NGC" checked>NGC';
		}	
		else if(isset($ilance->GPC['ngc']) AND !isset($ilance->GPC['pcgs']))
		{
			$grading_service=" AND Grading_Service='NGC' ";
			$scriptpageprevnext = 'pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort='.$ilance->GPC['sort'].'&action='.$ilance->GPC['action'];
			$switch_variable=$ilance->GPC['cmd'].'|'.$ilance->GPC['action'].'|'.$ilance->GPC['ngc'];
			$grading_service1='<input type="checkbox" name="pcgs" id="pcgs" value="PCGS">PCGS';
			$grading_service2='<input type="checkbox" name="ngc" id="ngc" value="NGC" checked>NGC';
		}
		else if(!isset($ilance->GPC['ngc']) AND isset($ilance->GPC['pcgs']))
		{
			$grading_service=" AND Grading_Service='PCGS' ";
			$switch_variable=$ilance->GPC['cmd'].'|'.$ilance->GPC['action'].'|'.$ilance->GPC['pcgs'];
			$scriptpageprevnext = 'pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort='.$ilance->GPC['sort'].'&action='.$ilance->GPC['action'];
			$grading_service1='<input type="checkbox" name="pcgs" id="pcgs" value="PCGS" checked>PCGS';
			$grading_service2='<input type="checkbox" name="ngc" id="ngc" value="NGC" >NGC';		
		}
		
		
	}
	else
	{
		$pcgs_grading='';
		$grading_service='';
		$switch_variable=$ilance->GPC['action'];
		$grading_service1='<input type="checkbox" name="pcgs" id="pcgs" value="PCGS" checked>PCGS';
		$grading_service2='<input type="checkbox" name="ngc" id="ngc" value="NGC" checked>NGC';
	}
	
	
	
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

	

	$scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	


	switch($switch_variable)
	{
		case 'price':
		{
			
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|price':
		{
			
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|price|PCGS':
		{
			
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|price|NGC':
		{
			
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|price|PCGS|NGC':
		{
		
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search||PCGS':
		{
		
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search||NGC':
		{
		
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search||PCGS|NGC':
		{
		
			if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$ilance->GPC['pcgs_no'].'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
				';
			}  

			break;
		}
		
		case 'type':
		{
			
			if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?sort=22&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?sort=21&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand.gif"></a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|type':
		{
		
			if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=22&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=21&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand.gif"></a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|type|PCGS':
		{
		
			if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=22&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&sort=21&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand.gif"></a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|type|NGC':
		{
		
			if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=22&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&ngc='.$ilance->GPC['ngc'].'&sort=21&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand.gif"></a></td>
				';
			}  

			break;
		}
		
		case 'pcgs_coin_search|type|PCGS|NGC':
		{
		
			if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=22&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
				';
			}

			else
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=11&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td> 
				<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&pcgs='.$ilance->GPC['pcgs'].'&ngc='.$ilance->GPC['ngc'].'&sort=21&action=type" title="Sort by Type" style="text-decoration:underline">Type<img alt="desc" src="images/default/expand.gif"></a></td>
				';
			}  

			break;
		}
		
		default:
		{
		
		
			if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='pcgs_coin_search')
			{
				$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td>								
			<td><a href="pcgs_coin_auction_archive_search.php?cmd=pcgs_coin_search&pcgs_no='.$pcgs_number.'&grade_range1='.$ilance->GPC['grade_range1'].'&grade_range2='.$ilance->GPC['grade_range2'].'&sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
			';
			}
			else
			{
			$listing =  '<td><a href="pcgs_coin_auction_archive_search.php?sort=12&action=price" title="Sort by Price" style="text-decoration:underline">Price</a></td>								
			<td><a href="pcgs_coin_auction_archive_search.php?sort=12&action=type" title="Sort by Type" style="text-decoration:underline">Type</a></td>
			';
			}
		}	
	
	}
	
	if ($ilance->GPC['sort']=='21') 
	{
		$orderby ="ORDER BY c.bids ASC";
	}
	else if ($ilance->GPC['sort']=='22') 
	{
		$orderby ="ORDER BY c.bids DESC";
	}
	else if ($ilance->GPC['sort']=='01') 
	{
		$orderby ="ORDER BY c.date_starts ASC";
	}
	else if ($ilance->GPC['sort']=='02') 
	{
		$orderby ="ORDER BY c.date_starts DESC";
	}
	else if ($ilance->GPC['sort']=='11') 
	{
		$orderby ="ORDER BY c.currentprice ASC";
	}
	else if ($ilance->GPC['sort']=='12') 
	{
		$orderby ="ORDER BY c.currentprice DESC";
	}
	else
	{
		$orderby ="ORDER BY c.haswinner DESC,c.date_end DESC";
	}

	
	$surfing_user_id=isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0?$_SESSION['ilancedata']['user']['userid']:0;
	
	
	
	
	$select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.description
											from " . DB_PREFIX . "projects c												
											where (c.status = 'expired' or c.status = 'closed')											
											AND c.visible = '1'										
											$pcgs_grading
											$grading_service
											group by c.project_id
											$orderby LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
											");
					   
			
	$select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.description
												from " . DB_PREFIX . "projects c							
												where (c.status = 'expired' or c.status = 'closed')
												AND c.visible = '1'
												$pcgs_grading
												$grading_service
												group by c.project_id
												$orderby ");
					   
	$total_num1=$ilance->db->num_rows($select_featurednew12);
	
	if($total_num1)
	{
		$total_num=true;
	}
	else{
		$total_num=false;
	}
	if($ilance->db->num_rows( $select_featurednew12) > 0)
	{
		$number = (int)$ilance->db->num_rows( $select_featurednew12);

		while($det=$ilance->db->fetch_array($select_featurednew))
		{
			$projectid=$det['project_id'];
			
			
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{

				$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($det['project_id'], $_SESSION['ilancedata']['user']['userid']);

				if ($pbit > 0)
				{										
					$highbidderidtest = $ilance->bid->fetch_highest_bidder($det['project_id']);
					
					
					if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
					$listpageg['proxybit'] = '<div class="smaller green" style="padding-top:4px"><span>You won this item</span><br>' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
					else
					$listpageg['proxybit'] ='<div class="smaller red" style="padding-top:4px"><span>You were outbid</span><br>' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
					';                                          
					$show['proxy']=true;
				
				}
				
				else
				{
					$listpageg['proxybit'] ='';
				}
				
			}
			
			

			if ($ilconfig['globalauctionsettings_seourls'])	
			$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
			else
			$listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';

			$listpageg['description']=$det['description'];

			$listpageg['date_end']='<strong>' . date("F d,Y",strtotime($det['date_end'])) . '</strong>';
			$buynow=$det['buynow'];

			
			$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
			$respjt = $ilance->db->fetch_array($selectpjt);
			$hammer_price = $ilance->currency->format($respjt['currentprice']);
			if($respjt['haswinner']=='1')
			{
				$sol='Sold';
				$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
			}
			else
			{
				$sol='Unsold';
				$hammer = '';
			}  
			if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
			{
				if ($respjt['filtered_auctiontype'] == 'regular')
				{

					if ($respjt['bids'] > 0)
					{
						$listpageg['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($respjt['currentprice']+$respjt['buyer_fee']). '</strong>';
					}
					else
					{
						$listpageg['currentprice'] ='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($respjt['currentprice']+$respjt['buyer_fee']). '</strong>';
						$listpageg['currentprice'] .='' .'<br>Buy Now'.'&nbsp;'.$ilance->currency->format($respjt['buynow_price']). '';
					}
					$listpageg['bids'] = ($respjt['bids'] > 0)
					? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
					: '<span class="blue"> Bid </span>';

				}
				else if ($respjt['filtered_auctiontype'] == 'fixed')
				{
					$listpageg['currentprice'] ='<strong>' .'Buy Now'.'&nbsp;'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
					$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
				}

			}
			else
			{
				$listpageg['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($respjt['currentprice']+$respjt['buyer_fee']). '</strong>'.$hammer;
				$listpageg['bids'] = ($respjt['bids'] > 0)
				? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
				: '<span class="blue"> Bid </span>';
			}
		

			$sql_attya = $ilance->db->query("
			SELECT * FROM
			" . DB_PREFIX . "attachment
			WHERE visible='1' 
			AND project_id = '".$det['project_id']."'
			AND attachtype='itemphoto' order by attachid desc

			");
			$fetch_newa=$ilance->db->fetch_array($sql_attya);

			if($ilance->db->num_rows($sql_attya) > 0)
			{

			$count_attachtype= $ilance->db->query("SELECT  count(attachtype) as imag FROM
												" . DB_PREFIX . "attachment
												WHERE visible='1' 
												AND project_id = '".$det['project_id']."'
												AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') order by attachid desc
												");
			$display_count=$ilance->db->fetch_array($count_attachtype);
			$uselistra = HTTPS_SERVER.$ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
			if ($ilconfig['globalauctionsettings_seourls'])
			$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
									<div class="gallery-thumbs-entry">
									<div class="gallery-thumbs-main-entry">
									<div class="gallery-thumbs-wide-wrapper">
									<div class="gallery-thumbs-wide-inner-wrapper">
									<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
									<div class="gallery-thumbs-corner-text"><span>' . ($display_count['imag']) . ' photos</span></div>
									</div>
									</div>
									</div>
									</div>
									</div>';
			else
			$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
									<div class="gallery-thumbs-entry">
									<div class="gallery-thumbs-main-entry">
									<div class="gallery-thumbs-wide-wrapper">
									<div class="gallery-thumbs-wide-inner-wrapper"><a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a> 
									</div>
									</div>
									</div>
									</div>
									</div>';


			}
			if($ilance->db->num_rows($sql_attya) == 0)
			{
				$uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
				if ($ilconfig['globalauctionsettings_seourls'])
				$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
				else
				$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
			}				

			$listpage[]=	$listpageg;
		}
	}
	
		
	$count=$number;

	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
	$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
	$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);	

	

	
	$grade_range1='<select name="grade_range1" id="grade_range1">';
	$grade_range2='<select name="grade_range2" id="grade_range2">';
	
	$grade_range_sql= $ilance->db->query("select *
											from " . DB_PREFIX . "coin_proof 
											");
	
	while($grade_range_res=$ilance->db->fetch_array($grade_range_sql))
	{
		if(isset($ilance->GPC['grade_range1']) AND isset($ilance->GPC['grade_range2']))
		{
			if($grade_range_res['value']==$ilance->GPC['grade_range1'])
				$grade_range1.="<option value=".$grade_range_res['value']." selected>".$grade_range_res['value']."</option>";
			else
				$grade_range1.="<option value=".$grade_range_res['value']." >".$grade_range_res['value']."</option>";
			if($grade_range_res['value']==$ilance->GPC['grade_range2'])
				$grade_range2.="<option value=".$grade_range_res['value']." selected>".$grade_range_res['value']."</option>";
			else
				$grade_range2.="<option value=".$grade_range_res['value']." >".$grade_range_res['value']."</option>";
		}		
		else
		{			
			if($grade_range_res['value']==69)
				$grade_range1.="<option value=".$grade_range_res['value']." selected>".$grade_range_res['value']."</option>";
			else
				$grade_range1.="<option value=".$grade_range_res['value']." >".$grade_range_res['value']."</option>";
			if($grade_range_res['value']==70)
				$grade_range2.="<option value=".$grade_range_res['value']." selected>".$grade_range_res['value']."</option>";
			else
				$grade_range2.="<option value=".$grade_range_res['value'].">".$grade_range_res['value']."</option>";
		}
		
	}
	
	$grade_range1.='</select>';
	$grade_range2.='</select>';
	$pcgs_no=(isset($ilance->GPC['pcgs_no'])) ? $ilance->GPC['pcgs_no'] : '';
	
	
	
	
	$pprint_array = array('grading_service1','grading_service2','listing','pcgs_no','prof','count','grade_range1','grade_range2','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'pcgs_coin_auction_archive_search.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
	$ilance->template->parse_loop('main', 'listpage');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	

/*Tamil For Bug 2695 * Ends*/

?>