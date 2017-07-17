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
        'buying',
        'selling',
        'rfp',
        'search',
        'feedback',
        'accounting',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete'
        
	
);

// #### setup script location ##################################################


// #### require backend ########################################################
require_once('./functions/config.php');


			
			
$date=str_replace(' ','T',DATETIME24H).'-07:00';
header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>
			<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

			
			//gc
			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER;
			echo '</loc>';
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////********************** MAIN  ***************************////

			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['main'];
			echo '</loc>';
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			

			///////********************** MAIN-***  ***************************////
			
			
			$accepted = print_accepted_array();
			

			for($i=1 ; $i<= count($accepted) ; $i++)
			{
				echo '<url>';
				echo '<loc>';
				echo HTTP_SERVER .'main-'.$accepted[$i];
				echo '</loc>';
				echo '<lastmod>'.$date.'</lastmod>';
				echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
				echo '</url>';			
				


			}	
			
			
			///////**********************Coin Shows**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-shows';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';				


			///////********************** LOGIN  ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['login'];
			echo '</loc>';
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';		
			
			
			
			///////////****REGISTRATION****/////////////////////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['registration'];
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			///////**********************AUCTION  1-250   ***************************////
			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['search'].'?mode=product&amp;sort=01&amp;auction=1&amp;fromprice=1.00&amp;toprice=250.00';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			///////********************** AUCTION 250 >  ***************************////
			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['search'].'?mode=product&amp;sort=01&amp;auction=1&amp;fromprice=250.00&amp;toprice=100000.00';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			///////********************** BUY NOW  ***************************////			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['search'].'?mode=product&amp;sort=01&amp;buynow=1';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			
			///////********************** MULTI COINS ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER .$ilpage['search'].'?mode=product&amp;q=Coins)&amp;series=&amp;sort=01';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			///////**********************DAILY DEALS ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'DailyDeal';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			///////********************** AUCTION-ARCHIVE ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'CoinPrices';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////////****CATEGORIES****/////////////////////

			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'Categories';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////////****LISTINGS****/////////////////////
			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'Listings';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


			///////////****DENOMINATION****/////////////////////
						
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'Denominations';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


						
			
			//////////************** COIN DENOMINATION LIST **********************//////////
			
			$result=$ilance->db->query("select * from " . DB_PREFIX . "catalog_toplevel ".$subquery." order by denomination_sort");

			if($ilance->db->num_rows($result) > 0 )
			{
				
				while($row=$ilance->db->fetch_array($result))	
				{
					echo '<url>';
					echo '<loc>';
					echo HTTP_SERVER.'Denomination/'.$row['id'].'/'.construct_seo_url_name($row['denomination_long']);
					echo '</loc>';
					echo '<lastmod>'.$date.'</lastmod>';
					echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
					echo '</url>';
				
				}
			}
			
			//////////************** COIN SERIES **********************//////////
			

			$result1=$ilance->db->query("SELECT coin_series_unique_no,coin_series_name FROM ".DB_PREFIX."catalog_second_level ");

			if($ilance->db->num_rows($result1) > 0 )
			{
				while($row=$ilance->db->fetch_array($result1))
				{
				
					echo '<url>';
					echo '<loc>';
					echo HTTP_SERVER.'Series/'.$row['coin_series_unique_no'].'/'.construct_seo_url_name($row['coin_series_name']);
					echo '</loc>';			
					echo '<lastmod>'.$date.'</lastmod>';
					echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
					echo '</url>';

				}
			}			


			///////////****PRINT****/////////////////////	
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB .'print-article-t185.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';			


			////////////////////****This item qualifies for the GC Return Policy*****////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['main'] .'?cmd=return';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


			//////////////////***** All items are sold by GreatCollections*****//////////////////////



			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['main'] .'?cmd=allitem';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


			////////////********  Selling Coins******////////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['main'] .'?cmd=sell';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


			////////////////////*****  Request a Coin Estimate ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['coin_appraisal'];
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


			////////////////////*****  ConsignmentForm ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'forms/'.$ilpage['consignment_form'];
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';


			////////////////////***** RAW ConsignmentForm ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'forms/'.$ilpage['raw_coins'];
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			////////////////////***** TOP 10 VIEWED ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'top-10-viewed.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';



			////////////////////***** PRINT ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'print-article-t86.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';



			////////////////////***** SAVE ARTICLE ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'save-article-t86-11.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			////////////////////***** EMAIL ARTICLE ******////////////


			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'email-article-t86-12.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			

			
			
			///////********************** KB VARIABLE  ***************************////
			
			echo '<url>';
			echo '<loc>';
			$ilance->lancekb = construct_object('api.lancekb');
			echo HTTP_KB . '?cmd=2&amp;amp;catid=18' . $ilance->lancekb->fetch_variables();	
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			///////********************** KB ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB;	
			echo '</loc>';
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			///////********************** KB NEWS ***************************////;



			$result=$ilance->db->query("SELECT subject,postsid FROM ".DB_PREFIX."kbposts ");
			if($ilance->db->num_rows($result))
			{
			while($row=$ilance->db->fetch_array($result))
			{
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.construct_seo_url_name($row['subject']).'-t'.$row['postsid'].'-4.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			}
			}

			
			///////********************** KB NEWS static ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'/GreatCollections-News-18-2.html'; 
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			
			
			///////********************** TESTIMONIALS***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'testimonials.php';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';		
			
			
			///////********************** Main-Terms***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-terms';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';		
			
			///////********************** Main-About***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-about';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';		
			
			///////********************** Why GreatCollections***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-why-greatcollections';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';	
			
			///////********************** Through***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-through';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';				
			
			
			///////**********************Promise***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-promise';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';	
			
			///////**********************Paymonth***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-paymonth';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';	
			
			///////********************** SHIPPING INFORMATION***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'Shipping-Fees-and-Methods-t86-4.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			///////********************** NEW MEMBER INFORMATION***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'Useful-Information-For-New-Members-17-2.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			///////********************** ***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'Notification-Payment-Shipping-9-2.html';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			///////********************** Free Coin Appraisal***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'coin_appraisal.php';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************Grade and Auction Program***************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_KB.'Grade-and-Auction-Program-for-Raw-Coins-t152-4.html?se=Grade and Auction Program';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			
			
			
			
			///////**********************Featured Coin Auctions***************************////
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'featured-coin-auction.php';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************Random Coin Auction**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'randomauction.php';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************Rare Coin Auctions**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['search'].'?mode=product&amp;sort=01&amp;auction=1&amp;fromprice=500.00&amp;toprice=100000.00&amp;fromprice=500.00&amp;toprice=100000.00';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************Toned Coins**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['search'].'?q=Toned&amp;mode=product&amp;sort=01';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************CAC Coins**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['search'].'?q=CAC&amp;mode=product&amp;sort=01';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************Chinese Coins **************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['search'].'?q=China&amp;mode=product&amp;sort=01';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			

			///////**********************Record Coin Prices**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'record_coin_prices.php';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';

			
			///////**********************greatcollections-vs-ebay**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'greatcollections-vs-ebay';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////**********************Coins Graded PCGS MS-70/Proof-70 Coins**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'pcgs-70-coins';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////**********************Coins Graded NGC MS-70/Proof-70**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'ngc-70-coins';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////**********************MS-67 Coins Certified by PCGS and NGC**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'MS-67-coins';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
				///////**********************The Young Collection of Silver Commemoratives**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'young-collection';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
				///////**********************The Amadeus Collection**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'amadeus';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
				///////**********************Main grading**************************////
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main-grading';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			///////********************** search***************************////
			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['search'].'?q=&amp;mode=product&amp;sort=011';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.$ilpage['search'];
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';		
			
			
			
			//footer privacy
			
			  
			echo '<url>';
			echo '<loc>';
			echo HTTP_SERVER.'main.php?cmd=privacy';
			echo '</loc>';			
			echo '<lastmod>'.$date.'</lastmod>';
			echo '<changefreq>weekly</changefreq><priority>0.8</priority>';
			echo '</url>';
			
			
			
			echo '</urlset>';

			?>