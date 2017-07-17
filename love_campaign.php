<?php

/*==========================================================================*\

|| ######################################################################## ||

|| # ILance Marketplace Software 3.2.0 Build 1575

|| # -------------------------------------------------------------------- # ||

|| # Customer License # =ryotOqStzEoc1gDhm2kyaoC2VZLPe-ZTcK=-2d-y-SXgzbKia

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

        'wantads',

        'search',

        'feedback',

        'lancebb',

        'buying',

        'selling',

        'accounting',

        'rfp'

);



// #### load required javascript ###############################################

$jsinclude = array(

	'functions',

	'ajax',

	'inline',

	'cron',

	'autocomplete',

	'flashfix',

	'jquery'

);



// #### setup script location ##################################################

define('LOCATION', 'main');



// #### require backend ########################################################

require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################

//$navcrumb = array("$ilpage[main]" => $ilcrumbs["$ilpage[main]"]);



 $area_title = 'CoinAuctions';

       $page_title = SITE_NAME . ' - ' . 'Coin Auctions and Direct Sales of Rare Coins';



$ilance->auction = construct_object('api.auction');



	//For  Featured Auctions 

		

	  $select_featurednew= $ilance->db->query("SELECT c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice 

		                                        FROM " . DB_PREFIX . "projects c

												WHERE c.featured = '1'  

												AND c.project_state = 'product'

												AND c.status = 'open'

												AND c.visible = '1' 

												GROUP BY c.project_id 

												ORDER BY RAND() ASC LIMIT 8

                                             ");

				   

				 



		if ($ilance->db->num_rows($select_featurednew) > 0)

		{   

		    $myfeature = '';

			$c = 0;

			        $itemsinrow=1;

					$$first_row='';

			while($row_pre_fea = $ilance->db->fetch_array($select_featurednew))

			{

			       $sql_attya = $ilance->db->query("SELECT filehash FROM " . DB_PREFIX . "attachment

                                                    WHERE visible='1' 

						                            AND project_id = '".$row_pre_fea['project_id']."'

						                            AND attachtype='itemphoto'

                                                  ");

				    $fetch_newa=$ilance->db->fetch_array($sql_attya);

				

					if($ilance->db->num_rows($sql_attya) == 1)

					{

						$uselistra = HTTPS_SERVER.$ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=140'; 

						if ($ilconfig['globalauctionsettings_seourls'])	

						     $htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a>';

						else

						     $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" st2yle="padding-top: 6px;"></a>';						

					}

					else

					{

					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';

						if ($ilconfig['globalauctionsettings_seourls'])	

						     $htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';

						else

					        $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';

					}

				

				if($c==3 or $c==7)

				$sep = '';

				else

				$sep = '<div id="seperator"></div>';

				

				

				 $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, 0);

				$myfeature.= '<div id="abox01">

						

						<div id="fetit">';

						$myfeature.=$ilconfig['globalauctionsettings_seourls']?

						'<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">':

						'<a href="merch.php?id='.$row_pre_fea['project_id'].'">';

						

						$myfeature.=$row_pre_fea['project_title'].'</a></div>

						<div>&nbsp;</div>

						<div id="textim"><div align="center">'.$htma.'</div></div>

						

					    <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>

						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>

						<div style="float:left;">';

						

						if($row_pre_fea['filtered_auctiontype'] == 'fixed')

						{

				  			 $image = 'buy_now_but.jpg';

						}

						else

						{

				   			$image = 'bid_now_butt.jpg';

						}

						if ($ilconfig['globalauctionsettings_seourls'])	

						$myfeature.= '<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';

						else

						$myfeature.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';

						

						 $sql_idly = $ilance->db->query("

                        SELECT * FROM

                        " . DB_PREFIX . "dailydeal

                        WHERE project_id = '".$row_pre_fea['project_id']."'

						

						

                        ");

				   

					if($ilance->db->num_rows($sql_idly) > 0)

					{

					  

					  $daily = '24-Hour Deals Starts';

					

					}

					else

					{					

					  $daily = 'Ends';

					}

						$myfeature.= '</div>

						</div>

						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>

						<div id="fetit">'.$daily.': '.$yutq.'</div>

						</div>

                        '.$sep.'';

						

						

						

						if($itemsinrow==4)

						{

						$first_row=$myfeature;

						unset($myfeature);

						}



			$c++;

			

			        $itemsinrow++;

			}

			



			$second_row=$myfeature;

			

		}

		else

		{

		  $myfeature = '<div style="margin-top: 150px;" align="center">NO RESULTS FOUND</div>';

		}

		

	

        $pprint_array = array('second_row','first_row','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','text');



        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  

	

	    $ilance->template->fetch('main', 'love_campaign.html');

        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));

		$ilance->template->parse_loop('main', 'info_val');

		$ilance->template->parse_loop('main', 'info_feat');

        ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;

        $ilance->template->parse_if_blocks('main');

        $ilance->template->pprint('main', $pprint_array);

        exit();





/*======================================================================*\

|| ####################################################################

|| # Downloaded: Fri, Sep 17th, 2010

|| ####################################################################

\*======================================================================*/

?>