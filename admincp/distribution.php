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
        'administration',
        'accounting',
        'buying',
        'selling',
        'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
        'tabfx',
        'countries',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_attachment.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['distribution'] => $ilcrumbs["$ilpage[distribution]"]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['distribution']);

if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
        exit();
}

$ilance->auction = construct_object('api.auction');

($apihook = $ilance->api('admincp_distribution_start')) ? eval($apihook) : false;

// #### CATEGORY MANAGEMENT ####################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'categories')
{
	// #### UPDATE QUESTIONS CATEGORY SORTING ##############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-category-questions-sort')
	{
		$table = ((isset($ilance->GPC['type']) AND $ilance->GPC['type'] == 'service')
			? DB_PREFIX . 'project_questions'
			: DB_PREFIX . 'product_questions');
		
		if (isset($ilance->GPC['sort']))
		{
			foreach ($ilance->GPC['sort'] AS $key => $value)
			{
				$ilance->db->query("
					UPDATE $table
					SET sort = '" . intval($value) . "'
					WHERE questionid = '" . intval($key) . "'
					LIMIT 1
				");
			}
					
			print_action_success($phrase['_question_sort_display_order_was_successfully_saved'], $ilance->GPC['return']);
			exit();
		}
	}
	// #### UPDATE SERVICE AUCTION CATEGORIES HANDLER ######################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-service-category')
	{
		$query1 = $query2 = $query3 = '';
		if (!empty($ilance->GPC['title']))
		{
			foreach ($ilance->GPC['title'] AS $slng => $value)
			{
				$query1 .= "title_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		if (!empty($ilance->GPC['description']))
		{
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$bidamounttypes = isset($ilance->GPC['bidamounttypes']) ? $ilance->GPC['bidamounttypes'] : '';
		$bidtypes = '';
		if (!empty($bidamounttypes))
		{
			$bidtypes = serialize($bidamounttypes);
		}
		
		$bidfieldtypes = isset($ilance->GPC['bidfieldtypes']) ? $ilance->GPC['bidfieldtypes'] : '';
		$bidfields = '';
		if (!empty($bidfieldtypes))
		{
			$bidfields = serialize($bidfieldtypes);
		}
		
		$usefixedfees = 0;
		if (isset($ilance->GPC['usefixedfees']) AND $ilance->GPC['usefixedfees'])
		{
			$usefixedfees = 1;
		}
		
		$fixedfeeamount = 0;
		if (isset($ilance->GPC['fixedfeeamount']) AND $ilance->GPC['fixedfeeamount'] > 0)
		{
			$fixedfeeamount = $ilance->GPC['fixedfeeamount'];
		}
		
		$nondisclosefeeamount = 0;
		if (isset($ilance->GPC['nondisclosefeeamount']) AND $ilance->GPC['nondisclosefeeamount'] > 0)
		{
			$nondisclosefeeamount = $ilance->GPC['nondisclosefeeamount'];
		}
		
		$multipleaward = 0;
		if (isset($ilance->GPC['multipleaward']) AND $ilance->GPC['multipleaward'])
		{
			$multipleaward = 1;
		}
		
		$bidgrouping = 0;
		if (isset($ilance->GPC['bidgrouping']) AND $ilance->GPC['bidgrouping'])
		{
			$bidgrouping = 1;
		}
		
		$bidgroupdisplay = 0;
		if (isset($ilance->GPC['bidgroupdisplay']))
		{
			$bidgroupdisplay = $ilance->GPC['bidgroupdisplay'];
		}
		
		$ilance->GPC['catimage'] = isset($ilance->GPC['catimage']) ? $ilance->GPC['catimage'] : '';
		
		// #### final parent category pulldown checkup to be safe
		if (isset($ilance->GPC['cid']) AND isset($ilance->GPC['pid']) AND $ilance->GPC['cid'] == $ilance->GPC['pid'])
		{
			print_action_failed($phrase['_the_category_you_are_trying_to_save_cannot_be_the_same_category'], 'javascript:history.back(1);');
			exit();
		}
		
		$sql = $ilance->db->query("SELECT cid FROM " . DB_PREFIX . "categories WHERE parentid='".$ilance->GPC['cid']."'");
		if($ilance->db->num_rows($sql))
		{
			$sql_old = $ilance->db->query("SELECT parentid FROM " . DB_PREFIX . "categories WHERE cid='".$ilance->GPC['cid']."'");
			$res_old = $ilance->db->fetch_array($sql_old);
			while($res = $ilance->db->fetch_array($sql))
			{
				$ilance->db->query("UPDATE " . DB_PREFIX . "categories SET parentid = '".$res_old['parentid']."' WHERE cid='".$res['cid']."'");
			}
		}
		
		($apihook = $ilance->api('admincp_update_service_category_end')) ? eval($apihook) : false;
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET parentid = '" . intval($ilance->GPC['pid']) . "',
			$query1
			$query2
			$query3
			canpost = '" . intval($ilance->GPC['canpost']) . "',
			xml = '" . intval($ilance->GPC['xml']) . "',
			portfolio = '" . intval($ilance->GPC['portfolio']) . "',
			newsletter = '" . intval($ilance->GPC['newsletter']) . "',
			budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['budgetgroup']) . "',
			insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['insertiongroup']) . "',
			finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['finalvaluegroup']) . "',
			cattype = 'service',
			bidamounttypes = '" . $ilance->db->escape_string($bidtypes) . "',
			bidfields = '" . $ilance->db->escape_string($bidfields) . "',
			usefixedfees = '" . intval($usefixedfees) . "',
			fixedfeeamount = '" . $ilance->db->escape_string($fixedfeeamount) . "',
			nondisclosefeeamount = '" . $ilance->db->escape_string($nondisclosefeeamount) . "',
			multipleaward = '" . intval($multipleaward) . "',
			bidgrouping = '" . intval($bidgrouping) . "',
			bidgroupdisplay = '" . $ilance->db->escape_string($bidgroupdisplay) . "',
			catimage = '" . $ilance->db->escape_string($ilance->GPC['catimage']) . "',
			keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
			visible = '" . intval($ilance->GPC['visible']) . "'
			WHERE cid = '" . intval($ilance->GPC['cid']) . "'
		", 0, null, __FILE__, __LINE__);
		
		// #### update the new level bit for the category tree structure
		$ilance->categories->set_levels();
		$ilance->categories->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories->rebuild_category_geometry();
		
		refresh($ilance->GPC['return']);
		exit();
	}
    
	// #### UPDATE PRODUCT AUCTION CATEGORIES HANDLER ######################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-product-category')
	{
		$query1 = $query2 = $query3 = '';
		if (!empty($ilance->GPC['title']))
		{
			foreach ($ilance->GPC['title'] AS $slng => $value)
			{
				$query1 .= "title_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value)."',";
			}
		}
		if (!empty($ilance->GPC['description']))
		{
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$xml = isset($ilance->GPC['xml']) ? intval($ilance->GPC['xml']) : 0;
		$portfolio = isset($ilance->GPC['portfolio']) ? intval($ilance->GPC['portfolio']) : 0;
		$newsletter = isset($ilance->GPC['newsletter']) ? intval($ilance->GPC['newsletter']) : 0;
		
		$ilance->GPC['catimage'] = isset($ilance->GPC['catimage']) ? $ilance->GPC['catimage'] : '';
		$ilance->GPC['budgetgroup'] = isset($ilance->GPC['budgetgroup']) ? $ilance->GPC['budgetgroup'] : '';
		$ilance->GPC['finalvaluegroup'] = isset($ilance->GPC['finalvaluegroup']) ? $ilance->GPC['finalvaluegroup'] : '';
		$ilance->GPC['incrementgroup'] = isset($ilance->GPC['incrementgroup']) ? $ilance->GPC['incrementgroup'] : '';
		$useproxybid = isset($ilance->GPC['useproxybid']) ? intval($ilance->GPC['useproxybid']) : 0;
		$usereserveprice = isset($ilance->GPC['usereserveprice']) ? intval($ilance->GPC['usereserveprice']) : 0;
		$usehidebuynow = isset($ilance->GPC['usehidebuynow']) ? intval($ilance->GPC['usehidebuynow']) : 0;
		$useantisnipe = isset($ilance->GPC['useantisnipe']) ? intval($ilance->GPC['useantisnipe']) : 0;
		
		// #### final parent category pulldown checkup to be safe
		if (isset($ilance->GPC['cid']) AND isset($ilance->GPC['pid']) AND $ilance->GPC['cid'] == $ilance->GPC['pid'])
		{
			print_action_failed($phrase['_the_category_you_are_trying_to_save_cannot_be_the_same_category'], 'javascript:history.back(1);');
			exit();
		}
		
		$sql = $ilance->db->query("SELECT cid FROM " . DB_PREFIX . "categories WHERE parentid='".$ilance->GPC['cid']."'");
		if($ilance->db->num_rows($sql))
		{
			$sql_old = $ilance->db->query("SELECT parentid FROM " . DB_PREFIX . "categories WHERE cid='".$ilance->GPC['cid']."'");
			$res_old = $ilance->db->fetch_array($sql_old);
			while($res = $ilance->db->fetch_array($sql))
			{
				$ilance->db->query("UPDATE " . DB_PREFIX . "categories SET parentid = '".$res_old['parentid']."' WHERE cid='".$res['cid']."'");
			}
		}
		
		($apihook = $ilance->api('admincp_update_product_category_end')) ? eval($apihook) : false;
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET parentid = '" . intval($ilance->GPC['pid']) . "',
			$query1
			$query2
			$query3
			canpost = '" . intval($ilance->GPC['canpost']) . "',
			xml = '" . $xml . "',
			portfolio = '" . $portfolio . "',
			newsletter = '" . $newsletter . "',
			budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['budgetgroup']) . "',
			insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['insertiongroup']) . "',
			finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['finalvaluegroup']) . "',
			incrementgroup = '" . $ilance->db->escape_string($ilance->GPC['incrementgroup']) . "',
			cattype = 'product',
			catimage = '" . $ilance->db->escape_string($ilance->GPC['catimage']) . "',
			keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
			useproxybid = '" . $useproxybid . "',
			usereserveprice = '" . $usereserveprice . "',
			useantisnipe = '" . $useantisnipe . "',
			hidebuynow = '" . $usehidebuynow . "',
			visible = '" . intval($ilance->GPC['visible']) . "'
			WHERE cid = '" . intval($ilance->GPC['cid']) . "'
		", 0, null, __FILE__, __LINE__);
		
		// #### update the new level bit for the category structure system.
		$ilance->categories->set_levels();
		$ilance->categories->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories->rebuild_category_geometry();
		
		refresh($ilance->GPC['return']);
		exit();
	}
	
	// #### CREATE NEW SERVICE CATEGORY HANDLER ############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_insert-service-category')
	{
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		//$pid = isset($ilance->GPC['pid']) ? intval($ilance->GPC['pid']) : 0;
		$pid = $cid;
		$customfields1 = $customfields2 = $customfields3 = $customfieldvalues1 = $customfieldvalues2 = $customfieldvalues3 = '';
		
		// #### handle multilanguage titles ####################
		$titleerror = 0;
		if (!empty($ilance->GPC['title']))
		{
			$titlefields = $titlevalues = '';
			foreach ($ilance->GPC['title'] AS $slng => $title)
			{
				if (!empty($slng) AND !empty($title))
				{
					$titlefields .= 'title_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
					$titlevalues .= "'" . $ilance->db->escape_string($title) . "',";
				}
				else 
				{
					$titleerror = 1;
				}
			}
			
			if ($titleerror)
			{
				print_action_failed($phrase['_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system'], $ilpage['distribution'] . '?cmd=categories&subcmd=addservicecat&cid=' . $cid);
				exit();	
			}
		}
		else 
		{
			print_action_failed($phrase['_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system'], $ilpage['distribution'] . '?cmd=categories&subcmd=addservicecat&cid=' . $cid);
			exit();		
		}
		
		// #### handle multilanguage descriptions ##############
		$descriptionerror = false;
		if (!empty($ilance->GPC['description']))
		{
			$descriptionfields = $descriptionvalues = '';
			foreach ($ilance->GPC['description'] AS $slng => $title)
			{
				if (!empty($slng) AND !empty($title))
				{
					$descriptionfields .= 'description_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
					$descriptionvalues .= "'" . $ilance->db->escape_string($title) . "',";
				}
				else 
				{
					$descriptionerror = true;
				}
			}
			
			if ($descriptionerror)
			{
				//print_action_failed("Sorry, to create a new category you must define a description all available languages in your system.", $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
				//exit();	
			}
		}
		else 
		{
			//print_action_failed("Sorry, to create a new category you must define a description for all available languages in your system.", $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
			//exit();		
		}
		    
		$canpost = 0;
		if (isset($ilance->GPC['canpost']) AND $ilance->GPC['canpost'] == 1)
		{
			$canpost = 1;
		}
		
		$xml = 0;
		if (isset($ilance->GPC['xml']) AND $ilance->GPC['xml'] == 1)
		{
			$xml = 1;
		}
		
		$portfolio = 0;
		if (isset($ilance->GPC['portfolio']) AND $ilance->GPC['portfolio'] == 1)
		{
			$portfolio = 1;
		}
		
		$newsletter = 0;
		if (isset($ilance->GPC['newsletter']) AND $ilance->GPC['newsletter'] == 1)
		{
			$newsletter = 1;
		}
		
		$visible = 0;
		if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'] == 1)
		{
			$visible = 1;
		}
		
		$budgetgroup = isset($ilance->GPC['budgetgroup']) ? $ilance->GPC['budgetgroup'] : '';
		$insertiongroup = isset($ilance->GPC['insertiongroup']) ? $ilance->GPC['insertiongroup'] : '';
		$finalvaluegroup = isset($ilance->GPC['finalvaluegroup']) ? $ilance->GPC['finalvaluegroup'] : '';
		$cattype = isset($ilance->GPC['cattype']) ? $ilance->GPC['cattype'] : '';
		$bidamounttypes = isset($ilance->GPC['bidamounttypes']) ? $ilance->GPC['bidamounttypes'] : '';
		$bidtypes = '';
		if (!empty($bidamounttypes))
		{
			$bidtypes = serialize($bidamounttypes);
		}
		
		$bidfieldtypes = isset($ilance->GPC['bidfieldtypes']) ? $ilance->GPC['bidfieldtypes'] : '';
		$bidfields = '';
		if (!empty($bidfieldtypes))
		{
			$bidfields = serialize($bidfieldtypes);
		}
		
		$usefixedfees = 0;
		if (isset($ilance->GPC['usefixedfees']) AND $ilance->GPC['usefixedfees'] == 1)
		{
			$usefixedfees = 1;
		}
		
		$fixedfeeamount = 0;
		if (isset($ilance->GPC['fixedfeeamount']) AND $ilance->GPC['fixedfeeamount'] > 0)
		{
			$fixedfeeamount = $ilance->GPC['fixedfeeamount'];
		}
		
		$nondisclosefeeamount = 0;
		if (isset($ilance->GPC['nondisclosefeeamount']) AND $ilance->GPC['nondisclosefeeamount'] > 0)
		{
			$nondisclosefeeamount = $ilance->GPC['nondisclosefeeamount'];
		}
		
		$multipleaward = 0;
		if (isset($ilance->GPC['multipleaward']) AND $ilance->GPC['multipleaward'] == 1)
		{
			$multipleaward = 1;
		}
		
		$bidgrouping = 0;
		if (isset($ilance->GPC['bidgrouping']) AND $ilance->GPC['bidgrouping'] == 1)
		{
			$bidgrouping = 1;
		}
		
		$bidgroupdisplay = 'lowest';
		if (isset($ilance->GPC['bidgroupdisplay']))
		{
			$bidgroupdisplay = $ilance->GPC['bidgroupdisplay'];
		}
		
		$catimage = isset($ilance->GPC['catimage']) ? $ilance->db->escape_string($ilance->GPC['catimage']) : '';
		$keywords = isset($ilance->GPC['keywords']) ? $ilance->db->escape_string($ilance->GPC['keywords']) : '';
		
		($apihook = $ilance->api('admincp_insert_service_category_end')) ? eval($apihook) : false;
		
		// #### lock the category table ########################
		//$ilance->db->query("LOCK TABLE " . DB_PREFIX . "categories WRITE", 0, null, __FILE__, __LINE__);
		  
		// #### get the parent record ##########################
		$sql = $ilance->db->query("
			SELECT rgt
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($cid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$parent = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			if ($parent['rgt'] > 0)
			{
				// #### prepare the table for the insert
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET rgt = rgt + 2 
					WHERE rgt > " . intval($parent['rgt']) . "
						AND cattype = 'service'
				", 0, null, __FILE__, __LINE__);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET lft = lft + 2
					WHERE lft > " . intval($parent['rgt']) . "
						AND cattype = 'service'
				", 0, null, __FILE__, __LINE__);
			}
		}
		else
		{
			$parent['rgt'] = 0; 
		}
		
		$ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
		
		// #### insert the record ##############################
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "categories
			(parentid, $titlefields $descriptionfields $customfields1 $customfields2 $customfields3 canpost, xml, portfolio, newsletter, budgetgroup, insertiongroup, finalvaluegroup, cattype, bidamounttypes, bidfields, usefixedfees, fixedfeeamount, nondisclosefeeamount, multipleaward, bidgrouping, bidgroupdisplay, catimage, keywords, visible, lft, rgt)
			VALUES(
			'" . $pid . "',
			$titlevalues
			$descriptionvalues
			$customfieldvalues1
			$customfieldvalues2
			$customfieldvalues3
			'" . $canpost . "',
			'" . $xml . "',
			'" . $portfolio . "',
			'" . $newsletter . "',
			'" . $budgetgroup . "',
			'" . $insertiongroup . "',
			'" . $finalvaluegroup . "',
			'service',
			'" . $bidtypes . "',
			'" . $bidfields . "',
			'" . $usefixedfees . "',
			'" . $fixedfeeamount . "',
			'" . $nondisclosefeeamount . "',
			'" . $multipleaward . "',
			'" . $bidgrouping . "',
			'" . $bidgroupdisplay . "',
			'" . $catimage . "',
			'" . $keywords . "',
			'" . $visible . "',
			'" . ($parent['rgt'] + 1) . "',
			'" . ($parent['rgt'] + 2). "')
		", 0, null, __FILE__, __LINE__);
		
		$ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
		
		// #### update the new level bit for the category tree structure
		$ilance->categories->set_levels();
		$ilance->categories->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories->rebuild_category_geometry();
		
		print_action_success($phrase['_new_category_was_added'], $ilance->GPC['return']);
		exit();
	}
		
	// #### CREATE NEW PRODUCT CATEGORY HANDLER ############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_insert-product-category')
	{
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		//$pid = isset($ilance->GPC['pid']) ? intval($ilance->GPC['pid']) : 0;
		$pid = $cid;
		
		$customfields1 = $customfields2 = $customfields3 = $customfieldvalues1 = $customfieldvalues2 = $customfieldvalues3 = '';
		$canpost = $xml = $newsletter = $visible = 0;
		$titleerror = $descriptionerror = false;
		
		// #### handle multilanguage titles ####################
		if (!empty($ilance->GPC['title']))
		{
			$titlefields = $titlevalues = '';
			foreach ($ilance->GPC['title'] AS $slng => $title)
			{
				if (!empty($slng) AND !empty($title))
				{
					$titlefields .= 'title_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
					$titlevalues .= "'" . $ilance->db->escape_string($title) . "',";
				}
				else 
				{
					$titleerror = true;
				}
			}
			
			if ($titleerror)
			{
				print_action_failed($phrase['_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system'], $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
				exit();	
			}
		}
		else 
		{
			print_action_failed($phrase['_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system'], $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
			exit();		
		}
		
		// #### handle multilanguage descriptions ##############
		if (!empty($ilance->GPC['description']))
		{
			$descriptionfields = $descriptionvalues = '';
			foreach ($ilance->GPC['description'] AS $slng => $title)
			{
				if (!empty($slng) AND !empty($title))
				{
					$descriptionfields .= 'description_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
					$descriptionvalues .= "'" . $ilance->db->escape_string($title) . "',";
				}
				else 
				{
					$descriptionerror = true;
				}
			}
			
			if ($descriptionerror)
			{
				//print_action_failed("Sorry, to create a new category you must define a description all available languages in your system.", $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
				//exit();	
			}
		}
		else 
		{
			//print_action_failed("Sorry, to create a new category you must define a description for all available languages in your system.", $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
			//exit();		
		}
		    
		
		if (isset($ilance->GPC['canpost']) AND $ilance->GPC['canpost'])
		{
			$canpost = 1;
		}
		if (isset($ilance->GPC['xml']) AND $ilance->GPC['xml'])
		{
			$xml = 1;
		}
		if (isset($ilance->GPC['newsletter']) AND $ilance->GPC['newsletter'])
		{
			$newsletter = 1;
		}
		if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'])
		{
			$visible = 1;
		}
		
		$useproxybid = isset($ilance->GPC['useproxybid']) ? intval($ilance->GPC['useproxybid']) : 0;
		$usereserveprice = isset($ilance->GPC['usereserveprice']) ? intval($ilance->GPC['usereserveprice']) : 0;
		$useantisnipe = isset($ilance->GPC['useantisnipe']) ? intval($ilance->GPC['useantisnipe']) : 0;
		$insertiongroup = isset($ilance->GPC['insertiongroup']) ? $ilance->GPC['insertiongroup'] : '';
		$finalvaluegroup = isset($ilance->GPC['finalvaluegroup']) ? $ilance->GPC['finalvaluegroup'] : '';
		$incrementgroup = isset($ilance->GPC['incrementgroup']) ? $ilance->GPC['incrementgroup'] : '';
		$cattype = isset($ilance->GPC['cattype']) ? $ilance->GPC['cattype'] : '';
		$bidamounttypes = isset($ilance->GPC['bidamounttypes']) ? $ilance->GPC['bidamounttypes'] : '';
		$bidtypes = !empty($bidamounttypes) ? serialize($bidamounttypes) : '';                        
		$catimage = isset($ilance->GPC['catimage']) ? $ilance->db->escape_string($ilance->GPC['catimage']) : '';                        
		$keywords = isset($ilance->GPC['keywords']) ? $ilance->db->escape_string($ilance->GPC['keywords']) : '';
		
		($apihook = $ilance->api('admincp_insert_product_category_end')) ? eval($apihook) : false;
		
		// #### get the parent record ##################################
		$sql = $ilance->db->query("
			SELECT rgt
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($cid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$parent = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			if ($parent['rgt'] > 0)
			{
				// #### prepare the table for the insert
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET rgt = rgt + 2 
					WHERE rgt > " . intval($parent['rgt']) . "
						AND cattype = 'product'
				", 0, null, __FILE__, __LINE__);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET lft = lft + 2
					WHERE lft > " . intval($parent['rgt']) . "
						AND cattype = 'product'
				", 0, null, __FILE__, __LINE__);
			}
		}
		else
		{
			$parent['rgt'] = 0; 
		}
		
		$ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
		
		// #### insert the record ######################################
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "categories
			(parentid, $titlefields $descriptionfields $customfields1 $customfields2 $customfields3 canpost, xml, newsletter, insertiongroup, finalvaluegroup, incrementgroup, cattype, bidamounttypes, useproxybid, usereserveprice, useantisnipe, catimage, keywords, visible, lft, rgt)
			VALUES(
			'" . $pid . "',
			$titlevalues
			$descriptionvalues
			$customfieldvalues1
			$customfieldvalues2
			$customfieldvalues3
			'" . $canpost . "',
			'" . $xml . "',
			'" . $newsletter . "',
			'" . $insertiongroup . "',
			'" . $finalvaluegroup . "',
			'" . $incrementgroup . "',
			'product',
			'" . $bidtypes . "',
			'" . $useproxybid . "',
			'" . $usereserveprice . "',
			'" . $useantisnipe . "',
			'" . $catimage . "',
			'" . $keywords . "',
			'" . $visible . "',
			'" . ($parent['rgt'] + 1) . "',
			'" . ($parent['rgt'] + 2) . "')
		", 0, null, __FILE__, __LINE__);
		  
		$ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
		
		// #### update the new level bit for the category tree structure
		$ilance->categories->set_levels();
		$ilance->categories->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories->rebuild_category_geometry();
		
		print_action_success($phrase['_new_category_was_added'], $ilance->GPC['return']);
		exit();
	}
		
	// #### REMOVE SERVICE CATEGORY ########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removeservicecat' AND isset($ilance->GPC['cid']))
	{
		$ilance->admincp = construct_object('api.admincp');
		$ilance->admincp_category = construct_object('api.admincp_category');
		
		if ($ilance->admincp_category->can_remove_categories())
		{
			$ilance->admincp_category->remove_category_recursive(intval($ilance->GPC['cid']), 'service');
			
			// update the new level bit for the category structure system.
			$ilance->categories->set_levels();
			$ilance->categories->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
			$ilance->categories->rebuild_category_geometry();
		
			print_action_success($phrase['_category_was_removed_from_the_service_category_system_please_note'], $ilpage['distribution'] . "?cmd=categories");
			exit();
		}
		
		print_action_failed($phrase['_sorry_you_must_have_at_least_1_category_in_the_system_please_update'], $ilpage['distribution'] . '?cmd=categories');
		exit();	
	}
	
	// #### REMOVE MULTIPLE SERVICE CATEGORY ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removeservicecats' AND isset($ilance->GPC['cids']) AND is_array($ilance->GPC['cids']))
	{
		$ilance->admincp = construct_object('api.admincp');
		$ilance->admincp_category = construct_object('api.admincp_category');
		
		if ($ilance->admincp_category->can_remove_categories())
		{
			foreach ($ilance->GPC['cids'] AS $catid)
			{
				$ilance->admincp_category->remove_category_recursive(intval($catid), 'service');
			}
			
			// update the new level bit for the category structure system.
			$ilance->categories->set_levels();
			$ilance->categories->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
			$ilance->categories->rebuild_category_geometry();
		
			print_action_success($phrase['_category_was_removed_from_the_service_category_system_please_note'], $ilpage['distribution'] . "?cmd=categories");
			exit();
		}
		
		print_action_failed($phrase['_sorry_you_must_have_at_least_1_category_in_the_system_please_update'], $ilpage['distribution'] . '?cmd=categories');
		exit();	
	}
	
	// #### REMOVE PRODUCT CATEGORY ########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removeproductcat' AND isset($ilance->GPC['cid']))
	{
		$ilance->admincp = construct_object('api.admincp');
		$ilance->admincp_category = construct_object('api.admincp_category');
		
		if ($ilance->admincp_category->can_remove_categories())
		{
			$ilance->admincp_category->remove_category_recursive(intval($ilance->GPC['cid']), 'product');
			
			// update the new level bit for the category structure system.
			$ilance->categories->set_levels();
			$ilance->categories->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
			$ilance->categories->rebuild_category_geometry();
			
			print_action_success($phrase['_category_was_removed_from_the_product_category_system_please'], $ilpage['distribution'] . '?cmd=categories');
			exit();
		}
		
		print_action_failed($phrase['_sorry_you_must_have_at_least_1_category_in_the_system_please_update'], $ilpage['distribution'] . '?cmd=categories');
		exit();	
	}
	
	// #### REMOVE MULTIPLE PRODUCT CATEGORIES #############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removeproductcats' AND isset($ilance->GPC['cids']) AND is_array($ilance->GPC['cids']))
	{
		$ilance->admincp = construct_object('api.admincp');
		$ilance->admincp_category = construct_object('api.admincp_category');
		
		if ($ilance->admincp_category->can_remove_categories())
		{
			foreach ($ilance->GPC['cids'] AS $catid)
			{
				$ilance->admincp_category->remove_category_recursive(intval($catid), 'product');
			}
			
			// update the new level bit for the category structure system.
			$ilance->categories->set_levels();
			$ilance->categories->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
			$ilance->categories->rebuild_category_geometry();
			
			print_action_success($phrase['_category_was_removed_from_the_product_category_system_please'], $ilpage['distribution'] . '?cmd=categories');
			exit();
		}
		
		print_action_failed($phrase['_sorry_you_must_have_at_least_1_category_in_the_system_please_update'], $ilpage['distribution'] . '?cmd=categories');
		exit();	
	}
	
	// #### EDIT SERVICE CATEGORY DETAILS ##################################
	else if (isset($ilance->GPC['subcmd']) AND ($ilance->GPC['subcmd'] == 'editservicecat' OR $ilance->GPC['subcmd'] == 'addservicecat'))
	{
		$area_title = $phrase['_add_update_category'];
		$page_title = SITE_NAME . ' - ' . $phrase['_add_update_category'];
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
		
		$cid = intval($ilance->GPC['cid']);
		$slng = fetch_site_slng();
		    
		$ilance->bid = construct_object('api.bid');
		$ilance->bid_fields = construct_object('api.bid_fields');
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);

		if ($ilance->GPC['subcmd'] == 'editservicecat')
		{
			$cidfield = 'pid';
			
			// #### we are editing a particular question
			$submit = ($show['ADMINCP_TEST_MODE'])
				? '<input type="submit" value="' . $phrase['_save'] . '" class="buttons" style="font-size:15px" disabled="disabled" />'
				: '<input type="submit" value="' . $phrase['_save'] . '" class="buttons" style="font-size:15px" />';
				
			$subcmd = '_update-service-category';
	
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($ilance->GPC['cid']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$pid = $res['parentid'];
					
					// #### BEGIN YES/NO ###################################
					$res['checked_canpost_1'] = '';
					$res['checked_canpost_0'] = 'checked="checked"';
					if ($res['canpost'])
					{
						$res['checked_canpost_1'] = 'checked="checked"';
						$res['checked_canpost_0'] = '';	
					}
					
					$res['checked_xml_1'] = '';
					$res['checked_xml_0'] = 'checked="checked"';
					if ($res['xml'])
					{
						$res['checked_xml_1'] = 'checked="checked"';
						$res['checked_xml_0'] = '';	
					}
					
					$res['checked_portfolio_1'] = '';
					$res['checked_portfolio_0'] = 'checked="checked"';
					if ($res['portfolio'])
					{
						$res['checked_portfolio_1'] = 'checked="checked"';
						$res['checked_portfolio_0'] = '';	
					}
					
					$res['checked_newsletter_1'] = '';
					$res['checked_newsletter_0'] = 'checked="checked"';
					if ($res['newsletter'])
					{
						$res['checked_newsletter_1'] = 'checked="checked"';
						$res['checked_newsletter_0'] = '';	
					}
					
					$res['checked_visible_1'] = '';
					$res['checked_visible_0'] = 'checked="checked"';
					if ($res['visible'])
					{
						$res['checked_visible_1'] = 'checked="checked"';
						$res['checked_visible_0'] = '';	
					}
					
					########################################################
					$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown($res['insertiongroup'], 'service');
					$res['budgetgrouppulldown'] = $ilance->admincp->construct_budget_group_pulldown($res['budgetgroup'], 'service');
					$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown($res['finalvaluegroup'], 'service');
					$res['bidtypes'] = $ilance->admincp->construct_bidamounttypes(intval($ilance->GPC['cid']), 0);
					$res['bidfields'] = $ilance->bid_fields->print_bid_field_checkboxes(intval($ilance->GPC['cid']), $_SESSION['ilancedata']['user']['slng']);
					########################################################
					
					$res['checked_usefixedfees_1'] = '';
					$res['checked_usefixedfees_0'] = 'checked="checked"';
					if ($res['usefixedfees'])
					{
						$res['checked_usefixedfees_1'] = 'checked="checked"';
						$res['checked_usefixedfees_0'] = '';	
					}
					
					$res['checked_multipleaward_1'] = '';
					$res['checked_multipleaward_0'] = 'checked="checked"';
					if ($res['multipleaward'])
					{
						$res['checked_multipleaward_1'] = 'checked="checked"';
						$res['checked_multipleaward_0'] = '';       
					}
					
					$res['checked_bidgrouping_1'] = '';
					$res['checked_bidgrouping_0'] = 'checked="checked"';
					if ($res['bidgrouping'])
					{
						$res['checked_bidgrouping_1'] = 'checked="checked"';
						$res['checked_bidgrouping_0'] = '';
					}
					
					$res['checked_bidgroupdisplay_1'] = 'checked="checked"';
					$res['checked_bidgroupdisplay_0'] = '';
					if ($res['bidgroupdisplay'] == 'highest')
					{
						$res['checked_bidgroupdisplay_1'] = '';
						$res['checked_bidgroupdisplay_0'] = 'checked="checked"';
					}
					
					$res['nondisclosefee'] = '<input type="text" name="nondisclosefeeamount" value="' . $res['nondisclosefeeamount'] . '" class="input" size="5" />';
					$servicecategory[] = $res;
				}
			}
			
			// multilanguage question and description
			$row_count = 0;
			$languages = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
			");
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				
				// fetch title in this language
				$sql = $ilance->db->query("
					SELECT title_$language[slng] AS title, description_$language[slng] AS description
					FROM " . DB_PREFIX . "categories
					WHERE cid = '" . intval($cid) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$language['title'] = $res['title'];
						$language['description'] = $res['description'];
					}
				}
				$servicelanguages[] = $language;
				$row_count++;
			}
		}
		
		// #### adding new service cat #########################
		else if ($ilance->GPC['subcmd'] == 'addservicecat')
		{
			$cidfield = 'cid';
			$cid = intval($ilance->GPC['cid']);
			
			// we are editing a particular question
			$submit = ($show['ADMINCP_TEST_MODE'])
				? '<input type="submit" style="font-size:15px" value=" ' . $phrase['_save'] . ' " class="buttons" disabled="disabled" />'
				: '<input type="submit" style="font-size:15px" value=" ' . $phrase['_save'] . ' " class="buttons" />';
				
			$subcmd = '_insert-service-category';
	
			$res = array();
			// #### BEGIN YES/NO ###########################################
			$res['checked_canpost_1'] = '';
			$res['checked_canpost_0'] = 'checked="checked"';
			$res['checked_xml_1'] = '';
			$res['checked_xml_0'] = 'checked="checked"';
			$res['checked_portfolio_1'] = '';
			$res['checked_portfolio_0'] = 'checked="checked"';
			$res['checked_newsletter_1'] = '';
			$res['checked_newsletter_0'] = 'checked="checked"';
			$res['checked_visible_1'] = 'checked="checked"';
			$res['checked_visible_0'] = '';
			################################################################
			$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown('', 'service');
			$res['budgetgrouppulldown'] = $ilance->admincp->construct_budget_group_pulldown('', 'service');
			$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown('', 'service');
			$res['bidtypes'] = $ilance->admincp->construct_bidamounttypes('', 1);
			$res['bidfields'] = $ilance->bid_fields->print_bid_field_checkboxes('', $_SESSION['ilancedata']['user']['slng']);
			################################################################
			$res['checked_usefixedfees_1'] = '';
			$res['checked_usefixedfees_0'] = 'checked="checked"';
			$res['fixedfeeamount'] = '0';
			$res['checked_multipleaward_1'] = '';
			$res['checked_multipleaward_0'] = 'checked="checked"';
			$res['checked_bidgrouping_1'] = '';
			$res['checked_bidgrouping_0'] = 'checked="checked"';
			$res['checked_bidgroupdisplay_1'] = 'checked="checked"';
			$res['checked_bidgroupdisplay_0'] = '';
			################################################################
			$res['catimage'] = '';
			$res['keywords'] = '';
			$res['title'] ='';
			$res['nondisclosefee'] = '<input type="text" name="nondisclosefeeamount" value="" class="input" size="5" />';
			$servicecategory[] = $res;
			
			// multilanguage question and description
			$row_count = 0;
			$languages = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
			");
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$language['title'] = '';	
				$servicelanguages[] = $language;
				$row_count++;
			}
		}
		
		$pprint_array = array('cidfield','pid','buildversion','ilanceversion','login_include_admin','submit','subcmd','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_service_category_details')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'categories_edit.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','servicecategory','servicelanguages'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
	
	// #### EDIT PRODUCT CATEGORY DETAILS ##################################
	else if (isset($ilance->GPC['subcmd']) AND ($ilance->GPC['subcmd'] == 'editproductcat' OR $ilance->GPC['subcmd'] == 'addproductcat'))
	{
		$area_title = $phrase['_add_update_category'];
		$page_title = SITE_NAME . ' - ' . $phrase['_add_update_category'];;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
 
 		$cid = intval($ilance->GPC['cid']);
		$slng = fetch_site_slng();
		$ilance->GPC['page'] = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
		$pagebit = isset($ilance->GPC['page2']) ? '&amp;page2=' . intval($ilance->GPC['page2']) : '&amp;page=' . intval($ilance->GPC['page']);
		$return = $ilpage['distribution'] . '?cmd=categories' . $pagebit;
		$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);

		// #### adding a new category ##########################
		if ($ilance->GPC['subcmd'] == 'addproductcat')
		{
			$cidfield = 'cid';
			
			// we are editing a particular question
			$submit = ($show['ADMINCP_TEST_MODE'])
				? '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" disabled="disabled" />'
				: '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
				
			$subcmd = '_insert-product-category';
	
			// #### BEGIN YES/NO ###########################################
			$res = array();
			$res['checked_canpost_1'] = '';
			$res['checked_canpost_0'] = 'checked="checked"';
			$res['checked_xml_1'] = '';
			$res['checked_xml_0'] = 'checked="checked"';
			$res['checked_portfolio_1'] = '';
			$res['checked_portfolio_0'] = 'checked="checked"';
			$res['checked_newsletter_1'] = '';
			$res['checked_newsletter_0'] = 'checked="checked"';
			$res['checked_visible_1'] = 'checked="checked"';
			$res['checked_visible_0'] = '';
			$res['checked_useproxybid_1'] = '';
			$res['checked_useproxybid_0'] = 'checked="checked"';
			$res['checked_usereserveprice_1'] = 'checked="checked"';
			$res['checked_usereserveprice_0'] = '';
			$res['checked_useantisnipe_1'] = '';
			$res['checked_useantisnipe_0'] = 'checked="checked"';
			################################################
			$res['catimage'] = '';
			$res['keywords'] = '';
			$res['sort'] = '';
			################################################
			$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown('', 'product');
			$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown('', 'product');
			$res['incrementpulldown'] = $ilance->admincp->construct_increment_group_pulldown('', 'product');
			$res['title'] = '';
			
			($apihook = $ilance->api('admincp_product_add_category_end')) ? eval($apihook) : false;
			
			$productcategory[] = $res;
			    
			// multilanguage question and description
			$row_count = 0;
			$languages = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
			");
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$language['title'] = $res['title'];	
				$productlanguages[] = $language;
				$row_count++;
			}
		}
		
		// #### editing the category details ###################
		else 
		{
			$cidfield = 'pid';
			if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
			{
				$submit = ($show['ADMINCP_TEST_MODE'])
					? '<input type="submit" style="font-size:15px" value="'  . $phrase['_save'] . ' " class="buttons" disabled="disabled" />'
					: '<input type="submit" style="font-size:15px" value="'  . $phrase['_save'] . ' " class="buttons" />';
					
				$subcmd = '_update-product-category';
		
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "categories
					WHERE cid = '" . intval($ilance->GPC['cid']) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$pid = $res['parentid'];
						$categoryname = $res["title_$slng"];
						
						// #### BEGIN YES/NO
						$res['checked_canpost_1'] = '';
						$res['checked_canpost_0'] = 'checked="checked"';
						if ($res['canpost'])
						{
							$res['checked_canpost_1'] = 'checked="checked"';
							$res['checked_canpost_0'] = '';	
						}
						
						$res['checked_xml_1'] = '';
						$res['checked_xml_0'] = 'checked="checked"';
						if ($res['xml'])
						{
							$res['checked_xml_1'] = 'checked="checked"';
							$res['checked_xml_0'] = '';	
						}
						
						$res['checked_portfolio_1'] = '';
						$res['checked_portfolio_0'] = 'checked="checked"';
						if ($res['portfolio'])
						{
							$res['checked_portfolio_1'] = 'checked="checked"';
							$res['checked_portfolio_0'] = '';	
						}
						
						$res['checked_newsletter_1'] = '';
						$res['checked_newsletter_0'] = 'checked="checked"';
						if ($res['newsletter'])
						{
							$res['checked_newsletter_1'] = 'checked="checked"';
							$res['checked_newsletter_0'] = '';	
						}
						
						$res['checked_visible_1'] = '';
						$res['checked_visible_0'] = 'checked="checked"';
						if ($res['visible'])
						{
							$res['checked_visible_1'] = 'checked="checked"';
							$res['checked_visible_0'] = '';	
						}
						
						$res['checked_useproxybid_1'] = '';
						$res['checked_useproxybid_0'] = 'checked="checked"';
						if ($res['useproxybid'])
						{
							$res['checked_useproxybid_1'] = 'checked="checked"';
							$res['checked_useproxybid_0'] = '';	
						}
						
						$res['checked_usereserveprice_1'] = '';
						$res['checked_usereserveprice_0'] = 'checked="checked"';
						if ($res['usereserveprice'])
						{
							$res['checked_usereserveprice_1'] = 'checked="checked"';
							$res['checked_usereserveprice_0'] = '';	
						}
						
						$res['checked_useantisnipe_1'] = '';
						$res['checked_useantisnipe_0'] = 'checked="checked"';
						if ($res['useantisnipe'])
						{
							$res['checked_useantisnipe_1'] = 'checked="checked"';
							$res['checked_useantisnipe_0'] = '';	
						}
						
						$res['checked_usehidebuynow_1'] = '';
						$res['checked_usehidebuynow_0'] = 'checked="checked"';
						if ($res['hidebuynow'])
						{
							$res['checked_usehidebuynow_1'] = 'checked="checked"';
							$res['checked_usehidebuynow_0'] = '';	
						}


						$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown($res['insertiongroup'], 'product');
						$res['budgetgrouppulldown'] = $ilance->admincp->construct_budget_group_pulldown($res['budgetgroup'], 'product');
						$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown($res['finalvaluegroup'], 'product');
						$res['incrementpulldown'] = $ilance->admincp->construct_increment_group_pulldown($res['incrementgroup'], 'product');
						
						($apihook = $ilance->api('admincp_product_edit_category_end')) ? eval($apihook) : false;
						
						$productcategory[] = $res;
					}
				}
				    
				// #### multilanguage question and description
				$row_count = 0;
				$languages = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
				");
				while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
				{
					$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
					$language['language'] = $language['title'];
					
					// fetch title in this language
					$sql = $ilance->db->query("
						SELECT title_" . $language['slng'] . " AS title,
						description_" . $language['slng'] . " AS description
						FROM " . DB_PREFIX . "categories
						WHERE cid = '" . intval($cid) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$language['title'] = $res['title'];
							$language['description'] = $res['description'];
						}
					}
					$productlanguages[] = $language;
					$row_count++;
				}
			}
		}
		
		// custom product bid increment logic
		$sqlincrements = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "increments
			WHERE (cid = '" . intval($cid) . "')
			ORDER BY incrementid ASC
		");
		if ($ilance->db->num_rows($sqlincrements) > 0)
		{
			$row_count = 0;
			$show['no_increments'] = false;
			while ($rows = $ilance->db->fetch_array($sqlincrements, DB_ASSOC))
			{
				$rows['from'] = $ilance->currency->format($rows['increment_from']);
				$rows['to'] = $ilance->currency->format($rows['increment_to']);
				$rows['amount'] = $ilance->currency->format($rows['amount']);
				$rows['actions'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $cid . '&amp;do=editincrement&amp;id='.$rows['incrementid'].'#edit"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a> &nbsp; <a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=_remove-increment&amp;id='.$rows['incrementid'].'&amp;cid=' . $cid . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$increments[] = $rows;
				$row_count++;
			}
		}
		else
		{
			$show['no_increments'] = true;
		}
		
		// #### UPDATE BID INCREMENT SORTING ###################
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-increment-sort')
		{
			if (!empty($ilance->GPC['sort']))
			{
				foreach ($ilance->GPC['sort'] AS $incrementid => $sortvalue)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "increments
						SET sort = '" . intval($sortvalue) . "'
						WHERE incrementid = '" . intval($incrementid) . "'
						LIMIT 1
					");
				}
				
				refresh($ilpage['distribution'] . '?cmd=categories');
				exit();
			}
		}
		
		$pprint_array = array('return','cidfield','pid','buildversion','ilanceversion','login_include_admin','inchidden','incform','incsubmit','inshidden','insform','inssubmit','incamount','incto','incfrom','insamount','insto','insfrom','submit','subcmd','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_product_category_details')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'categories_edit.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','productcategory','productlanguages','increments'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
	
	// #### DELETE SERVICE CATEGORY QUESTION ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-service-question')
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "project_questions
			WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_category_question_was_removed_from_the_system'], $ilpage['distribution'] . '?cmd=categories&amp;subcmd=servicequestions&amp;cid='.$ilance->GPC['cid']);
		exit();
	}
	// #### REMOVE PRODUCT CATEGORY QUESTIONS ##############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-product-question')
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "product_answers
			WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		", 0, null, __FILE__, __LINE__);
		
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "product_questions
			WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		", 0, null, __FILE__, __LINE__);
		
		print_action_success($phrase['_category_question_was_removed_from_the_system'], $ilpage['distribution'] . '?cmd=categories&amp;subcmd=productquestions&amp;cid=' . $ilance->GPC['cid']);
		exit();
	}
		
	// #### CREATE NEW SERVICE CATEGORY QUESTION ###########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_insert-service-question')
	{
		$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : '0';
		$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : '0';
		$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : '0';
		$recursive = isset($ilance->GPC['recursive']) ? intval($ilance->GPC['recursive']) : '0';
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '0';
		$formname = construct_form_name(14);
		    
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "project_questions
			(cid, formname, formdefault, inputtype, multiplechoice, sort, visible, required, cansearch, canremove, recursive)
			VALUES(
			'" . intval($ilance->GPC['cid']) . "',
			'" . $ilance->db->escape_string($formname) . "',
			'" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			'" . $sort . "',
			'" . $visible . "',
			'" . $required . "',
			'" . $cansearch . "',
			'" . $recursive . "',
			'1')
		");
		
		$insid = $ilance->db->insert_id();
		
		$query1 = $query2 = '';
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] AS $slng => $value)
			{
				$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_questions
			SET
			$query1
			$query2
			`canremove` = '1'
			WHERE `questionid` = '" . $insid . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_new_category_question_was_added'], $ilance->GPC['return']);
		exit();
	}
		
	// #### CREATE PRODUCT CATEGORY QUESTION ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_insert-product-question')
	{
		$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : '0';
		$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : '0';
		$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : '0';
		$recursive = isset($ilance->GPC['recursive']) ? intval($ilance->GPC['recursive']) : '0';
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '0';
		$formname = construct_form_name(14);
		    
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "product_questions
			(cid, formname, formdefault, inputtype, multiplechoice, sort, visible, required, cansearch, canremove, recursive)
			VALUES(
			'" . intval($ilance->GPC['cid']) . "',
			'" . $ilance->db->escape_string($formname) . "',
			'" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			'" . $sort . "',
			'" . $visible . "',
			'" . $required . "',
			'" . $cansearch . "',
			'" . $recursive . "',
			'1')
		");
		$insid = $ilance->db->insert_id();
		
		$query1 = $query2 = '';
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] AS $slng => $value)
			{
				$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "product_questions
			SET $query1
			$query2
			canremove = '1'
			WHERE questionid = '" . $insid . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_new_category_question_was_added'], $ilance->GPC['return']);
		exit();
	}
	
	// #### UPDATE SERVICE CATEGORY QUESTION ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-service-question')
	{
		$required = $visible = $cansearch = $recursive = 0;
		if (isset($ilance->GPC['required']) AND $ilance->GPC['required'] > 0)
		{
			$required = 1;
		}
		if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'] > 0)
		{
			$visible = 1;
		}
		if (isset($ilance->GPC['cansearch']) AND $ilance->GPC['cansearch'] > 0)
		{
			$cansearch = 1;
		}
		if (isset($ilance->GPC['recursive']) AND $ilance->GPC['recursive'] > 0)
		{
			$recursive = 1;
		}
		
		// handle multilanguage question and description
		$query1 = $query2 = '';
		
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] AS $slng => $value)
			{
				$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		if (!isset($ilance->GPC['formname']) OR empty($ilance->GPC['formname']))
		{
			$ilance->GPC['formname'] = construct_form_name(14);        
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_questions
			SET `cid` = '" . intval($ilance->GPC['cid']) . "',
			$query1
			$query2
			`formname` = '" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
			`formdefault` = '" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
			`inputtype` = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			`multiplechoice` = '" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			`sort` = '" . intval($ilance->GPC['sort']) . "',
			`visible` = '" . $visible . "',
			`required` = '" . $required . "',
			`cansearch` = '" . $cansearch . "',
			`recursive` = '" . $recursive . "',
			`canremove` = '1'
			WHERE `questionid` = '".intval($ilance->GPC['qid'])."'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		print_action_success($phrase['_new_category_question_details_was_updated_for_the_selected_question'], $ilance->GPC['return']);
		exit();
	}
		
	// #### UPDATE PRODUCT CATEGORY QUESTION ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-product-question')
	{
		$required = $visible = $cansearch = $recursive = 0;
		if (isset($ilance->GPC['required']) AND $ilance->GPC['required'] > 0)
		{
			$required = 1;
		}
		if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'] > 0)
		{
			$visible = 1;
		}
		if (isset($ilance->GPC['cansearch']) AND $ilance->GPC['cansearch'] > 0)
		{
			$cansearch = 1;
		}
		if (isset($ilance->GPC['recursive']) AND $ilance->GPC['recursive'] > 0)
		{
			$recursive = 1;
		}
		
		// handle multilanguage question and description
		$query1 = $query2 = '';
		
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] AS $slng => $value)
			{
				$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		if (!isset($ilance->GPC['formname']) OR empty($ilance->GPC['formname']))
		{
			$ilance->GPC['formname'] = construct_form_name(14);        
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "product_questions
			SET `cid` = '" . intval($ilance->GPC['cid']) . "',
			$query1
			$query2
			`formname` = '" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
			`formdefault` = '" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
			`inputtype` = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			`multiplechoice` = '" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			`sort` = '" . intval($ilance->GPC['sort']) . "',
			`visible` = '" . $visible . "',
			`required` = '" . $required . "',
			`cansearch` = '" . $cansearch . "',
			`recursive` = '" . $recursive . "',
			`canremove` = '1'
			WHERE `questionid` = '" . intval($ilance->GPC['qid']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		print_action_success($phrase['_new_category_question_details_was_updated_for_the_selected_question'], $ilance->GPC['return']);
		exit();
	}
	// #### EDIT SERVICE CATEGORY QUESTIONS ################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'servicequestions')
	{
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
		    
		//$ilance->categories_pulldown = construct_object('api.categories_pulldown');
		//$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);
		
		$cid = intval($ilance->GPC['cid']);
		$categoryname = $ilance->categories->title(fetch_user_slng($_SESSION['ilancedata']['user']['userid']), 'service', $cid);
		$slng = fetch_site_slng();
		
		$area_title = $phrase['_create_or_update_category_question'] . ' ' . $phrase['_in'] . ' ' . $categoryname;
		$page_title = SITE_NAME . ' - ' . $phrase['_create_or_update_category_question'] . ' ' . $phrase['_in'] . ' ' . $categoryname;
		
		$questionid = 0;
		$question_subcmd = '_insert-service-question';
		
		$submit_category_question = ($show['ADMINCP_TEST_MODE'])
			? '<input type="submit" style="font-size:15px" value=" ' . $phrase['_save'] . ' " class="buttons" disabled="disabled" />'
			: '<input type="submit" style="font-size:15px" value=" ' . $phrase['_save'] . ' " class="buttons" />';
		
		$question = $description = $formname = $formdefault = $multiplechoice = $sort = $checked_question_active = $checked_question_required = $checked_question_cansearch = '';
		
		$question_inputtype_pulldown = '<select name="inputtype" id="inputtype" style="font-family: verdana" onchange="javascript:
		if (document.ilform.inputtype[5].selected == true)
		{ // multiple choice items
			toggle_show(\'displayvalues\');
			toggle_hide(\'defaultdisplayvalue\');
			toggle_show(\'searchablecb_service\');
		}
		else if (document.ilform.inputtype[6].selected == true)
		{ // pulldown menu items
			toggle_paid(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_show(\'searchablecb_service\');
		}
		else if (document.ilform.inputtype[1].selected == true)
		{ // integer field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_service\');
		}
		else if (document.ilform.inputtype[2].selected == true)
		{ // text area field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_service\');
		}
		else if (document.ilform.inputtype[2].selected == true)
		{ // input text field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_service\');
		}
		else if (document.ilform.inputtype[3].selected == true)
		{ // url single line field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_service\');
		}
		else if (document.ilform.inputtype[0].selected == true)
		{ // radio yes/no field
			toggle_hide(\'displayvalues\');
			toggle_hide(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_service\');
		}
		else
		{
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_service\');
		}
		">';
		
		$question_inputtype_pulldown .= '<option value="yesno">' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$question_inputtype_pulldown .= '<option value="int">' . $phrase['_integer_field_numbers_only'] . '</option>';
		$question_inputtype_pulldown .= '<option value="textarea">Text Area Field (multi-line)</option>';
		$question_inputtype_pulldown .= '<option value="text">' . $phrase['_input_text_field_singleline'] . '</option>';
		$question_inputtype_pulldown .= '<option value="url">' . $phrase['_url_singleline'] . '</option>';
		$question_inputtype_pulldown .= '<option value="multiplechoice">' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$question_inputtype_pulldown .= '<option value="pulldown">' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
		$question_inputtype_pulldown .= '</select>';
    
		$var = $ilance->categories->fetch_children_ids($cid, 'service');
                $extracids = "AND (FIND_IN_SET(cid, '$cid,$var') OR cid = '-1')";
                unset($explode, $var);
    
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_questions
			WHERE questionid > 0
			$extracids
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$show['noservicequestions'] = false;
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['active'] = ($res['visible'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['cansearch'] = ($res['cansearch'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['sort'] = '<input type="text" name="sort[' . $res['questionid'] . ']" value="' . $res['sort'] . '" class="input" style="text-align:center" size="3" />';
				$res['question'] = '<strong>'.$res['question_'.fetch_site_slng()].'</strong><div class="smaller gray" style="padding-top:3px">'.$res['description_'.fetch_site_slng()].'</div>';
				$res['inputtype'] = $res['inputtype'];
				$res['fieldname'] = $res['formname'];
				$res['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=servicequestions&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$res['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=_remove-service-question&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '" onClick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$res['isrequired'] = ($res['required'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['recursive'] = ($res['recursive'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$servicequestions[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['noservicequestions'] = true;
		}
    
		if (isset($ilance->GPC['qid']) AND $ilance->GPC['qid'] > 0)
		{
			// we are editing a particular question
			$submit_category_question = ($show['ADMINCP_TEST_MODE'])
				? '<input type="submit" style="font-size:15px" value=" ' . $phrase['_save'] . ' " class="buttons" disabled="disabled" />'
				: '<input type="submit" style="font-size:15px" value=" ' . $phrase['_save'] . ' " class="buttons" />';
					
			$questionid = intval($ilance->GPC['qid']);
			$question_subcmd = '_update-service-question';
	
			$var = $ilance->categories->fetch_children_ids($cid, 'service');
			$extracids = "AND (FIND_IN_SET(cid, '$cid,$var') OR cid = '-1')";
			unset($explode, $var);
	
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_questions 
				WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				
				$question = stripslashes($res['question_' . fetch_site_slng()]);
				$description = stripslashes($res['description_' . fetch_site_slng()]);
				$formname = $res['formname'];
				$formdefault = $res['formdefault'];
				$multiplechoice = $res['multiplechoice'];
				$sort = $res['sort'];
				
				$checked_question_active = ($res['visible']) ? 'checked="checked"' : '';
				$checked_question_required = ($res['required']) ? 'checked="checked"' : '';
				$checked_question_cansearch = ($res['cansearch']) ? 'checked="checked"' : '';
				$checked_question_recursive = ($res['recursive']) ? 'checked="checked"' : '';
	    
				//$question_inputtype_pulldown = '<select name="inputtype" style="font-family: verdana">';
				$question_inputtype_pulldown = '<select name="inputtype" id="inputtype" style="font-family: verdana" onchange="javascript:
				if (document.ilform.inputtype[5].selected == true)
				{ // multiple choice items
					toggle_show(\'displayvalues\');
					toggle_hide(\'defaultdisplayvalue\');
					toggle_show(\'searchablecb_service\');
				}
				else if (document.ilform.inputtype[6].selected == true)
				{ // pulldown menu items
					toggle_show(\'displayvalues\');
					toggle_hide(\'defaultdisplayvalue\');
					toggle_show(\'searchablecb_service\');
				}
				else if (document.ilform.inputtype[1].selected == true)
				{ // integer field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_service\');
				}
				else if (document.ilform.inputtype[2].selected == true)
				{ // text area field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_service\');
				}
				else if (document.ilform.inputtype[2].selected == true)
				{ // input text field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_service\');
				}
				else if (document.ilform.inputtype[3].selected == true)
				{ // url single line field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_service\');
				}
				else if (document.ilform.inputtype[0].selected == true)
				{ // radio yes/no field
					toggle_hide(\'displayvalues\');
					toggle_hide(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_service\');
				}
				else
				{
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_service\');
				}
				">';
				
				$question_inputtype_pulldown .= '<option value="">' . $phrase['_please_select'] . '</option>';				
				$question_inputtype_pulldown .= '<option value="yesno"'; if ($res['inputtype'] == "yesno") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
				$question_inputtype_pulldown .= '<option value="int"'; if ($res['inputtype'] == "int") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_integer_field_numbers_only'] . '</option>';
				$question_inputtype_pulldown .= '<option value="textarea"'; if ($res['inputtype'] == "textarea") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>Text Area Field (multi-line)</option>';
				$question_inputtype_pulldown .= '<option value="text"'; if ($res['inputtype'] == "text") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_input_text_field_singleline'] . '</option>';
				$question_inputtype_pulldown .= '<option value="url"'; if ($res['inputtype'] == "url") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_url_singleline'] . '</option>';
				$question_inputtype_pulldown .= '<option value="multiplechoice"'; if ($res['inputtype'] == "multiplechoice") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
				$question_inputtype_pulldown .= '<option value="pulldown"'; if ($res['inputtype'] == "pulldown") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
				$question_inputtype_pulldown .= '</select>';
			}
			
			// multilanguage question and description
			$row_count = 0;
			$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					    
				// fetch english question and description values
				$sql = $ilance->db->query("
					SELECT question_$language[slng] AS question, description_$language[slng] AS description
					FROM " . DB_PREFIX . "project_questions
					WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$language['question'] = $res['question'];	
						$language['description'] = $res['description'];	
					}
				}
				
				$servicelanguages[] = $language;
				$row_count++;
			}
		}
		else 
		{
			// multilanguage question and description form fields (blank)
			$row_count = 0;
			$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				
				// fetch english question and description values
				$sql = $ilance->db->query("
					SELECT question_$language[slng] AS question, description_$language[slng] AS description
					FROM " . DB_PREFIX . "project_questions
					WHERE cid = '" . intval($cid) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$row_count = 0;
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$language['question'] = '';	
						$language['description'] = '';	
					}
				}
				$servicelanguages[] = $language;
				$row_count++;
			}
		}
		
		$pprint_array = array('checked_question_recursive','buildversion','ilanceversion','login_include_admin','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_categories_questions_service_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'categories_questions.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','servicequestions','servicelanguages'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
	// #### EDIT PRODUCT CATEGORY QUESTIONS ################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'productquestions')
	{
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
		    
		$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);
		
		$cid = intval($ilance->GPC['cid']);
		$slng = fetch_site_slng();
		$categoryname = $ilance->categories->title($slng, 'product', $cid);
		
		$area_title = $phrase['_create_or_update_category_question'] . ' ' . $phrase['_in'] . ' ' . $categoryname;
		$page_title = SITE_NAME . ' - ' . $phrase['_create_or_update_category_question'] . ' ' . $phrase['_in'] . ' ' . $categoryname;
		
		$questionid = 0;
		$question_subcmd = '_insert-product-question';
		
		$submit_category_question = ($show['ADMINCP_TEST_MODE'])
			? '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" disabled="disabled" />'
			: '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
			
		$question = $description = $formname = $formdefault = $multiplechoice = $sort = $checked_question_active = $checked_question_required = $checked_question_cansearch = '';
		
		$question_inputtype_pulldown = '<select name="inputtype" id="inputtype" style="font-family: verdana" onchange="javascript:
		if (document.ilform.inputtype[5].selected == true)
		{ // multiple choice items
			toggle_show(\'displayvalues\');
			toggle_hide(\'defaultdisplayvalue\');
			toggle_show(\'searchablecb_product\');
		}
		else if (document.ilform.inputtype[6].selected == true)
		{ // pulldown menu items
			toggle_show(\'displayvalues\');
			toggle_hide(\'defaultdisplayvalue\');
			toggle_show(\'searchablecb_product\');
		}
		else if (document.ilform.inputtype[1].selected == true)
		{ // integer field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_product\');
		}
		else if (document.ilform.inputtype[2].selected == true)
		{ // text area field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_product\');
		}
		else if (document.ilform.inputtype[2].selected == true)
		{ // input text field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_product\');
		}
		else if (document.ilform.inputtype[3].selected == true)
		{ // url single line field
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_product\');
		}
		else if (document.ilform.inputtype[0].selected == true)
		{ // radio yes/no field
			toggle_hide(\'displayvalues\');
			toggle_hide(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_product\');
		}
		else
		{
			toggle_hide(\'displayvalues\');
			toggle_show(\'defaultdisplayvalue\');
			toggle_hide(\'searchablecb_product\');
		}
		">';
		$question_inputtype_pulldown .= '<option value="yesno">' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$question_inputtype_pulldown .= '<option value="int">' . $phrase['_integer_field_numbers_only'] . '</option>';
		$question_inputtype_pulldown .= '<option value="textarea">' . $phrase['_text_area_field_multiline'] . '</option>';
		$question_inputtype_pulldown .= '<option value="text">' . $phrase['_input_text_field_singleline'] . '</option>';
		$question_inputtype_pulldown .= '<option value="url">' . $phrase['_url_singleline'] . '</option>';
		$question_inputtype_pulldown .= '<option value="multiplechoice">' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$question_inputtype_pulldown .= '<option value="pulldown">' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
		$question_inputtype_pulldown .= '</select>';
    
                $var = $ilance->categories->fetch_children_ids($cid, 'product');
		$var2 = $ilance->categories->fetch_parent_ids($cid);
                $extracids = "AND (FIND_IN_SET(cid, '$cid,$var,$var2') OR cid = '-1')";
                unset($explode, $var);
    
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "product_questions
			WHERE questionid > 0
			$extracids
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$show['noproductquestions'] = false;
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['sort'] = '<input type="text" name="sort[' . $res['questionid'] . ']" value="' . $res['sort'] . '" class="input" style="text-align:center" size="3" />';
				$res['question'] = '<strong>' . $res['question_' . fetch_site_slng()] . '</strong><div class="smaller gray" style="padding-top:3px">' . $res['description_' . fetch_site_slng()] . '</div>';
				$res['inputtype'] = $res['inputtype'];
				$res['fieldname'] = $res['formname'];
				if ($res['cid'] == '-1')
				{
					$res['category'] = 'Assigned to all categories';
				}
				else
				{
					$res['category'] = $ilance->categories->recursive($res['cid'], 'product', $_SESSION['ilancedata']['user']['slng'], 1, '', 0);
				}
				
				$res['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=productquestions&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$res['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=_remove-product-question&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '" onClick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$res['active'] = ($res['visible'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['cansearch'] = ($res['cansearch'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['isrequired'] = ($res['required'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['recursive'] = ($res['recursive'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$productquestions[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['noproductquestions'] = true;
		}
    
		if (isset($ilance->GPC['qid']) AND $ilance->GPC['qid'] > 0)
		{
			// we are editing a particular question
			$submit_category_question = ($show['ADMINCP_TEST_MODE'])
				? '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" disabled="disabled" />'
				: '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" />';
			
			$questionid = intval($ilance->GPC['qid']);
			$question_subcmd = '_update-product-question';
	
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "product_questions 
				WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				
				$question = stripslashes($res['question_'.fetch_site_slng()]);
				$description = stripslashes($res['description_'.fetch_site_slng()]);
				$formname = $res['formname'];
				$formdefault = $res['formdefault'];
				$multiplechoice = $res['multiplechoice'];
				$sort = $res['sort'];
	    
				$checked_question_active = ($res['visible']) ? 'checked="checked"' : '';
				$checked_question_required = ($res['required']) ? 'checked="checked"' : '';	    
				$checked_question_cansearch = ($res['cansearch']) ? 'checked="checked"' : '';
				$checked_question_recursive = ($res['recursive']) ? 'checked="checked"' : '';
	    
				$question_inputtype_pulldown = '<select name="inputtype" id="inputtype" style="font-family: verdana" onchange="javascript:
				if (document.ilform.inputtype[5].selected == true)
				{ // multiple choice items
					toggle_show(\'displayvalues\');
					toggle_hide(\'defaultdisplayvalue\');
					toggle_show(\'searchablecb_product\');
				}
				else if (document.ilform.inputtype[6].selected == true)
				{ // pulldown menu items
					toggle_show(\'displayvalues\');
					toggle_hide(\'defaultdisplayvalue\');
					toggle_show(\'searchablecb_product\');
				}
				else if (document.ilform.inputtype[1].selected == true)
				{ // integer field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_product\');
				}
				else if (document.ilform.inputtype[2].selected == true)
				{ // text area field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_product\');
				}
				else if (document.ilform.inputtype[2].selected == true)
				{ // input text field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_product\');
				}
				else if (document.ilform.inputtype[3].selected == true)
				{ // url single line field
					toggle_hide(\'displayvalues\');
					toggle_show(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_product\');
				}
				else if (document.ilform.inputtype[0].selected == true)
				{ // radio yes/no field
					toggle_hide(\'displayvalues\');
					toggle_hide(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_product\');
				}
				else
				{
					toggle_hide(\'displayvalues\');
					toggle_paid(\'defaultdisplayvalue\');
					toggle_hide(\'searchablecb_product\');
				}
				">';

				$question_inputtype_pulldown .= '<option value="yesno"'; if ($res['inputtype'] == "yesno") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
				$question_inputtype_pulldown .= '<option value="int"'; if ($res['inputtype'] == "int") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_integer_field_numbers_only'] . '</option>';
				$question_inputtype_pulldown .= '<option value="textarea"'; if ($res['inputtype'] == "textarea") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_text_area_field_multiline'] . '</option>';
				$question_inputtype_pulldown .= '<option value="text"'; if ($res['inputtype'] == "text") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_input_text_field_singleline'] . '</option>';
				$question_inputtype_pulldown .= '<option value="url"'; if ($res['inputtype'] == "url") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_url_singleline'] . '</option>';
				$question_inputtype_pulldown .= '<option value="multiplechoice"'; if ($res['inputtype'] == "multiplechoice") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
				$question_inputtype_pulldown .= '<option value="pulldown"'; if ($res['inputtype'] == "pulldown") { $question_inputtype_pulldown .= ' selected="selected"'; } $question_inputtype_pulldown .= '>' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
				$question_inputtype_pulldown .= '</select>';
			}
			
			// multilanguage question and description
			$row_count = 0;
			$languages = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
			", 0, null, __FILE__, __LINE__);
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				
				// fetch english question and description values
				$sql = $ilance->db->query("
					SELECT question_$language[slng] AS question, description_$language[slng] AS description
					FROM " . DB_PREFIX . "product_questions
					WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$language['question'] = $res['question'];	
						$language['description'] = $res['description'];	
					}
				}
				
				$productlanguages[] = $language;
				$row_count++;
			}
		}
		else 
		{
			// multilanguage question and description form fields (blank)
			$row_count = 0;
			$languages = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
			", 0, null, __FILE__, __LINE__);
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				
				// fetch english question and description values
				$sql = $ilance->db->query("
					SELECT question_$language[slng] AS question, description_$language[slng] AS description
					FROM " . DB_PREFIX . "product_questions
					WHERE cid = '" . intval($cid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$row_count = 0;
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$language['question'] = '';	
						$language['description'] = '';	
					}
				}
				
				$productlanguages[] = $language;
				$row_count++;
			}
		}
		
		$pprint_array = array('checked_question_recursive','buildversion','ilanceversion','login_include_admin','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_categories_questions_product_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'categories_questions.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','productquestions','productlanguages'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
	
	$area_title = $phrase['_category_manager'];
	$page_title = SITE_NAME . ' - ' . $phrase['_category_manager'];
	
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_fields = construct_object('api.bid_fields');
	$ilance->admincp_category = construct_object('api.admincp_category');

	($apihook = $ilance->api('admincp_category_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
	$prevnext = $prevnext2 = '';
	
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
	$pp = $ilance->GPC['pp'];
	
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
		$ilance->GPC['level'] = (!isset($ilance->GPC['level']) OR isset($ilance->GPC['level']) AND $ilance->GPC['level'] <= 0) ? 10 : intval($ilance->GPC['level']);
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		$ilance->GPC['title'] = !empty($ilance->GPC['title']) ? $ilance->GPC['title'] : '';
		$ilance->GPC['visible'] = !empty($ilance->GPC['visible']) ? $ilance->GPC['visible'] : '';
		$ilance->GPC['cid'] = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		
		$cid = ($ilance->GPC['cid'] > 0) ? $ilance->GPC['cid'] : '';
		$page = $ilance->GPC['page'];
		$title = handle_input_keywords($ilance->GPC['title']);
		$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
		
		$count = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "categories
			WHERE cattype = 'service'
				AND level <= '" . intval($ilance->GPC['level']) . "'
				" . ((isset($ilance->GPC['title']) AND !empty($ilance->GPC['title'])) ? "AND title_" . $_SESSION['ilancedata']['user']['slng'] . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['title']) . "%'" : "") . "
				" . ((isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0) ? "AND cid = '" . intval($ilance->GPC['cid']) . "'" : "") . "
		");
		$count = $ilance->db->fetch_array($count, DB_ASSOC);
		$count = $count['count'];
		$row_count = 0;
		
		$urlbit = '&amp;level=' . $ilance->GPC['level'];
		if (!empty($ilance->GPC['title']))
		{
			$urlbit .= '&amp;title=' . $ilance->GPC['title'];
		}
		if (isset($ilance->GPC['visible']) AND !empty($ilance->GPC['visible']))
		{
			$urlbit .= '&amp;visible=' . $ilance->GPC['visible'];
		}
		
		$ilance->categories->cats = array();
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', $counter, $ilance->GPC['pp'], $ilance->GPC['level'], $ilance->GPC['cid'], $ilance->GPC['title']);
		
		for ($i = 0; $i < $ilance->GPC['pp']; $i++)
		{
			if (isset($ilance->categories->cats[$i]['cid']) AND !empty($ilance->categories->cats[$i]['cid']))
			{
				$ilance->categories->cats[$i]['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				if ($ilance->categories->cats[$i]['level'] == 1)
				{
					$ilance->categories->cats[$i]['title'] = ($ilance->categories->cats[$i]['canpost'] == 0) ? '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '"><strong>' . $ilance->categories->cats[$i]['title'] . '</strong></span></a>' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '">' . $ilance->categories->cats[$i]['title'] . '</span></a>';
				}
				else if ($ilance->categories->cats[$i]['level'] > 1)
				{
					$ilance->categories->cats[$i]['title'] = ($ilance->categories->cats[$i]['canpost'] == 0) ? str_repeat('<span class="gray">--</span> ', $ilance->categories->cats[$i]['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '"><strong>' . $ilance->categories->cats[$i]['title'] . '</strong></span></a>' : str_repeat('<span class="gray">--</span> ', $ilance->categories->cats[$i]['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '">' . $ilance->categories->cats[$i]['title'] . '</span></a>';
				}
				if ($ilance->categories->cats[$i]['insertiongroup'] == '0' OR $ilance->categories->cats[$i]['insertiongroup'] == '')
				{
					$ilance->categories->cats[$i]['insertiongroup'] = '-';
				}
				if ($ilance->categories->cats[$i]['finalvaluegroup'] == '0' OR $ilance->categories->cats[$i]['finalvaluegroup'] == '')
				{
					$ilance->categories->cats[$i]['finalvaluegroup'] = '-';
				}
				if ($ilance->categories->cats[$i]['budgetgroup'] == '0' OR $ilance->categories->cats[$i]['budgetgroup'] == '')
				{
					$ilance->categories->cats[$i]['budgetgroup'] = '-';
				}
				$ilance->categories->cats[$i]['questions'] = $ilance->admincp_category->fetch_category_listing_question_count($ilance->categories->cats[$i]['cid'], 'service');
				if ($ilance->categories->cats[$i]['questions'] == 0)
				{
					$ilance->categories->cats[$i]['questions'] = '-';
				}
				$ilance->categories->cats[$i]['bfields'] = $ilance->bid_fields->print_bid_field_count_in_category($ilance->categories->cats[$i]['cid']);
				if ($ilance->categories->cats[$i]['bfields'] == 0)
				{
					$ilance->categories->cats[$i]['bfields'] = '-';
				}
				
				($apihook = $ilance->api('admincp_service_category_loop_end')) ? eval($apihook) : false;
				
				$servicecategories[] = $ilance->categories->cats[$i];
				$row_count++;
			}
		}
		
		$prevnext = print_pagnation($count, $ilance->GPC['pp'], $ilance->GPC['page'], $counter, $ilpage['distribution'] . '?cmd=categories' . $urlbit);
	}
	
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		$ilance->GPC['level2'] = (!isset($ilance->GPC['level2']) OR isset($ilance->GPC['level2']) AND $ilance->GPC['level2'] <= 0) ? 10 : intval($ilance->GPC['level2']);
		$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
		$ilance->GPC['title2'] = !empty($ilance->GPC['title2']) ? $ilance->GPC['title2'] : '';
		$ilance->GPC['visible2'] = !empty($ilance->GPC['visible2']) ? intval($ilance->GPC['visible2']) : '';
		$ilance->GPC['cid2'] = !empty($ilance->GPC['cid2']) ? intval($ilance->GPC['cid2']) : 0;
		
		$cid2 = ($ilance->GPC['cid2'] > 0) ? $ilance->GPC['cid2'] : '';
		$page2 = $ilance->GPC['page2'];
		$title2 = handle_input_keywords($ilance->GPC['title2']);
		$counter = ($ilance->GPC['page2'] - 1) * $ilance->GPC['pp'];
		
		$count = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "categories
			WHERE cattype = 'product'
				AND level <= '" . intval($ilance->GPC['level2']) . "'
				" . ((isset($ilance->GPC['title2']) AND !empty($ilance->GPC['title2'])) ? "AND title_" . $_SESSION['ilancedata']['user']['slng'] . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['title2']) . "%'" : "") . "
				" . ((isset($ilance->GPC['cid2']) AND $ilance->GPC['cid2'] > 0) ? "AND cid = '" . intval($ilance->GPC['cid2']) . "'" : "") . "
		");
		$count = $ilance->db->fetch_array($count, DB_ASSOC);
		$count = $count['count'];
		$row_count = 0;
		
		$urlbit = '&amp;level2=' . $ilance->GPC['level2'];
		if (!empty($ilance->GPC['title2']))
		{
			$urlbit .= '&amp;title2=' . $ilance->GPC['title2'];
		}
		if (isset($ilance->GPC['visible2']) AND !empty($ilance->GPC['visible2']))
		{
			$urlbit .= '&amp;visible2=' . $ilance->GPC['visible2'];
		}
		
		$ilance->categories->cats = array();
		$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', $counter, $ilance->GPC['pp'], $ilance->GPC['level2'], $ilance->GPC['cid2'], $ilance->GPC['title2']);
		
		//print_r($ilance->categories->cats);
		
		for ($i = 0; $i < $ilance->GPC['pp']; $i++)
		{
			if (isset($ilance->categories->cats[$i]['cid']) AND $ilance->categories->cats[$i]['cid'] > 0)
			{
				$ilance->categories->cats[$i]['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				if ($ilance->categories->cats[$i]['level'] == 1)
				{
					$ilance->categories->cats[$i]['title'] = ($ilance->categories->cats[$i]['canpost'] == 0) ? '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '"><strong>' . $ilance->categories->cats[$i]['title'] . '</strong></span></a>' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '">' . $ilance->categories->cats[$i]['title'] . '</span></a>';
				}
				else if ($ilance->categories->cats[$i]['level'] > 1)
				{
					$ilance->categories->cats[$i]['title'] = ($ilance->categories->cats[$i]['canpost'] == 0) ? str_repeat('<span class="gray">--</span> ', $ilance->categories->cats[$i]['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '"><strong>' . $ilance->categories->cats[$i]['title'] . '</strong></span></a>' : str_repeat('<span class="gray">--</span> ', $ilance->categories->cats[$i]['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '&amp;pid=' . $ilance->categories->cats[$i]['parentid'] . '&amp;level=' . $ilance->categories->cats[$i]['level'] . '&amp;lft=' . $ilance->categories->cats[$i]['lft'] . '&amp;rgt=' . $ilance->categories->cats[$i]['rgt'] . '" title="' . $ilance->categories->cats[$i]['description'] . '"><span style="' . (($ilance->categories->cats[$i]['visible']) ? '' : 'color:red') . '">' . $ilance->categories->cats[$i]['title'] . '</span></a>';
				}
				if ($ilance->categories->cats[$i]['insertiongroup'] == '0' OR $ilance->categories->cats[$i]['insertiongroup'] == '')
				{
					$ilance->categories->cats[$i]['insertiongroup'] = '-';
				}
				if ($ilance->categories->cats[$i]['finalvaluegroup'] == '0' OR $ilance->categories->cats[$i]['finalvaluegroup'] == '')
				{
					$ilance->categories->cats[$i]['finalvaluegroup'] = '-';
				}
				if ($ilance->categories->cats[$i]['incrementgroup'] == '0' OR $ilance->categories->cats[$i]['incrementgroup'] == '')
				{
					$ilance->categories->cats[$i]['incrementgroup'] = '-';
				}
				$ilance->categories->cats[$i]['useproxybid'] = ($ilance->categories->cats[$i]['useproxybid']) ? '<img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'proxy_gray.gif" border="0" alt="' . $phrase['_proxy_bidding_enabled_for_this_category'] . '" />' : '';
				$ilance->categories->cats[$i]['usereserveprice'] = ($ilance->categories->cats[$i]['usereserveprice']) ? '<img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'reserve_gray.gif" border="0" alt="' . $phrase['_reserve_price_is_available_for_usage_in_this_category'] . '" />' : '';
				$ilance->categories->cats[$i]['useantisnipe'] = ($ilance->categories->cats[$i]['useantisnipe']) ? '<img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/antisnipe.gif" border="0" alt="' . $phrase['_antibid_sniping_enabled_for_this_category'] . '" />' : '';
				$ilance->categories->cats[$i]['questions'] = $ilance->admincp_category->fetch_category_listing_question_count($ilance->categories->cats[$i]['cid'], 'product');
				if ($ilance->categories->cats[$i]['questions'] == 0)
				{
					$ilance->categories->cats[$i]['questions'] = '-';
				}
				
				($apihook = $ilance->api('admincp_product_category_loop_end')) ? eval($apihook) : false;
				
				$productcategories[] = $ilance->categories->cats[$i];
				$row_count++;
			}
		}
		
		$prevnext2 = print_pagnation($count, $ilance->GPC['pp'], $ilance->GPC['page2'], $counter, $ilpage['distribution'] . '?cmd=categories' . $urlbit, 'page2');
	}

	// #### head javascript include ################################
	$headinclude .= $ilance->categories->print_category_jump_js('ilform', 'ilform2', 'cid');
	
	$global_categoryoptions = $ilance->admincp->construct_admin_input('globalcategorysettings', $ilpage['distribution'] . '?cmd=categories');
	
	$pprint_array = array('cid','cid2','global_categoryoptions','title','title2','page','page2','pp','prevnext','prevnext2','buildversion','ilanceversion','login_include_admin','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_categories_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'categories.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','servicecategories','productcategories'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}
    
// #### CURRENCY MANAGEMENT ############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'currencies')
{
	$area_title = $phrase['_currency_distribution'];
	$page_title = SITE_NAME . ' - ' . $phrase['_currency_distribution'];
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=currencies', $_SESSION['ilancedata']['user']['slng']);
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','id','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_currencies_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'currencies.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','escrows'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
    
// #### BULK EMAIL MANAGER #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bulkemail')
{
	// #### EMAIL EXPORT ###########################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'email-export')
	{
		if (isset($ilance->GPC['method']))
		{
			switch ($ilance->GPC['method'])
			{
				case 'newline':
				{
					$sql = $ilance->db->query("
						SELECT email
						FROM " . DB_PREFIX . "users
						ORDER BY user_id ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$txt = '';
						while ($emails = $ilance->db->fetch_array($sql))
						{
							$txt .= trim($emails['email']) . LINEBREAK;
						}
					}
					$ext = '.txt';
					$mime = 'text/plain';
					break;
				}                                    
				case 'csv':
				{
					$sql = $ilance->db->query("
						SELECT email
						FROM " . DB_PREFIX . "users
						ORDER BY user_id ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$txt = '';
						while ($emails = $ilance->db->fetch_array($sql))
						{
							$txt .= trim($emails['email']) . ",";
						}
						$txt = mb_substr($txt, 0, -1);
					}
					$ext = '.csv';
					$mime = 'text/x-csv';
					break;
				}                                    
				case 'csvnewline':
				{
					$sql = $ilance->db->query("
						SELECT email
						FROM " . DB_PREFIX . "users
						ORDER BY user_id ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$txt = '';
						while ($emails = $ilance->db->fetch_array($sql))
						{
							$txt .= trim($emails['email']) . "," . LINEBREAK;
						}
					}
					$ext = '.csv';
					$mime = 'text/x-csv';
					break;
				}
			}
			
			$ilance->common->download_file($txt, "email-list".$ext, $mime);
		}
	}
	
	// count total emails in system
	$emailcount = 0;
	$ec = $ilance->db->query("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "users
	");
	if ($ilance->db->num_rows($ec) > 0)
	{
		$rs = $ilance->db->fetch_array($ec);
		$emailcount = (int)$rs['count'];
	}
	
	// email export options
	$emailmethod_pulldown  = '<select name="method" style="font-family: verdana">';
	$emailmethod_pulldown .= '<option value="newline">' . $phrase['_each_email_address_per_line'] . '</option>';
	$emailmethod_pulldown .= '<option value="csv">' . $phrase['_comma_seperated_values'] . '</option>';
	$emailmethod_pulldown .= '<option value="csvnewline">' . $phrase['_comma_seperated_values_with_newlines'] . '</option>';
	$emailmethod_pulldown .= '</select>';
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_send-bulk-email')
	{
		$area_title = $phrase['_sending_bulk_email'];
		$page_title = SITE_NAME . ' - ' . $phrase['_sending_bulk_email'];
		
		$batch = isset($ilance->GPC['batch']) ? intval($ilance->GPC['batch']) : 0;
		$from = trim($ilance->GPC['from']);
		$subject = un_htmlspecialchars($ilance->GPC['subject']);
		
		if (isset($ilance->GPC['sendashtml']) AND $ilance->GPC['sendashtml'] == '1')
		{
			// admin sending bulk email as HTML
			$ilance->bbcode = construct_object('api.bbcode');
			
			if (!empty($ilance->GPC['description']))
			{
				$message = $ilance->GPC['description'];
				$message = stripslashes($message);
				$message = $ilance->bbcode->bbcode_to_html($message);
				$message = $ilance->bbcode->prepare_special_codes('PHP', $message);
				$message = $ilance->bbcode->prepare_special_codes('HTML', $message);
				$message = $ilance->bbcode->prepare_special_codes('CODE', $message);
				$message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
				$message = $ilance->bbcode->strip_bb_tags($message);
				$message = html_entity_decode($message);
			}
		}
		else
		{
			// admin sending bulk email as plain text
			$ilance->bbcode = construct_object('api.bbcode');
			if (!empty($ilance->GPC['description']))
			{
				$message = $ilance->GPC['description'];
				$message = $ilance->bbcode->prepare_special_codes('PHP', $message);
				$message = $ilance->bbcode->prepare_special_codes('HTML', $message);
				$message = $ilance->bbcode->prepare_special_codes('CODE', $message);
				$message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
				$message = $ilance->bbcode->strip_bb_tags($message);
				$message = html_entity_decode($message);
			} 
		}
		
		if (isset($ilance->GPC['users']) AND !empty($ilance->GPC['users']))
		{
			$plan = false;
			$users = explode('|', $ilance->GPC['users']);
			if (count($users) > 0)
			{
				$customwhere = '';
				foreach ($users AS $username)
				{
					$customwhere .= "username = '" . $ilance->db->escape_string($username) . "' OR ";
				}
				$customwhere = mb_substr($customwhere, 0, -4);
			}
		}
		else if ($ilance->GPC['subscriptionid'] != '0')
		{
			$plan = true;
			$subscriptionid = $ilance->GPC['subscriptionid'];
		}
		else
		{
			$plan = false;
		}
		
		if (isset($ilance->GPC['testmode']) AND $ilance->GPC['testmode'])
		{
			// admin sending test bulk email
			$area_title = $phrase['_bulk_email_test_message'];
			$page_title = SITE_NAME . ' - ' . $phrase['_bulk_email_test_message'];
			
			$ilance->subscription = construct_object('api.subscription');
			
			$wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
			
			$message = str_replace("{{username}}", $_SESSION['ilancedata']['user']['username'], $message);
			if (isset($ilance->GPC['sendashtml']) AND $ilance->GPC['sendashtml'] == '1')
			{
				send_email(SITE_EMAIL, $subject, $message, $from, '', 1);
			}
			else
			{
				send_email(SITE_EMAIL, $subject, $message, $from);
			}
			
			$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=bulkemail', $_SESSION['ilancedata']['user']['slng']);
			$site_email = $from;
			
			$subscription_pulldown = $ilance->subscription->pulldown();
			
			$pprint_array = array('buildversion','ilanceversion','login_include_admin','wysiwyg_area','description','emailcount','emailmethod_pulldown','subject','message','subscription_pulldown','site_email','numberpaid','numberunpaid','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			($apihook = $ilance->api('admincp_bulkemail_testmode_end')) ? eval($apihook) : false;
			
			$ilance->template->fetch('main', 'bulkemail.html', 1);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			// admin dispatching bulk newsletter
			
			if ($plan == true)
			{
				$sql = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "subscription_user
					WHERE subscriptionid = '" . intval($subscriptionid) . "'
					    AND active = 'yes'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql))
					{
						$sql2 = $ilance->db->query("
							SELECT username, email, first_name, last_name
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $res['user_id'] . "'
							    AND status = 'active'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							while ($res2 = $ilance->db->fetch_array($sql2))
							{
								unset($premessage);
								
								$premessage = str_replace("{{username}}", $res2['username'], $message);
								$premessage = str_replace("{{firstname}}", ucfirst($res2['first_name']), $premessage);
								$premessage = str_replace("{{lastname}}", ucfirst($res2['last_name']), $premessage);
								if (isset($ilance->GPC['sendashtml']) AND $ilance->GPC['sendashtml'] == '1')
								{
									send_email($res2['email'], $subject, $premessage, $from, '', 1);
								}
								else
								{
									send_email($res2['email'], $subject, $premessage, $from);
								}
							}
						}
					}
					
					print_action_success($phrase['_bulk_email_was_sent_to_subscribers_within_the_selected_subscription_plan'], $ilance->GPC['return']);
					exit();
				}
				else
				{
					print_action_failed($phrase['_bulk_email_could_not_be_sent_to_any_subscribers_within_the_selected_subscription_plan'], $ilance->GPC['return']);
					exit();
				}
			}
			else
			{
				if (empty($customwhere))
				{
					// sending to all active members
					$sql = $ilance->db->query("
						SELECT username, email, first_name, last_name
						FROM " . DB_PREFIX . "users
						WHERE status = 'active'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql))
						{
							unset($premessage);
							
							$premessage = str_replace("{{username}}", $res['username'], $message);
							$premessage = str_replace("{{firstname}}", ucfirst($res['first_name']), $premessage);
							$premessage = str_replace("{{lastname}}", ucfirst($res['last_name']), $premessage);
							
							if (isset($ilance->GPC['sendashtml']) AND $ilance->GPC['sendashtml'] == '1')
							{
								send_email($res['email'], $subject, $premessage, $from, '', 1);
							}
							else
							{
								send_email($res['email'], $subject, $premessage, $from);
							}
						}
						
						print_action_success($phrase['_bulk_email_was_sent_to_all_active_subscribers'], $ilance->GPC['return']);
						exit();
					}
			       }
			       else
			       {
					// send to only selected usernames admin has defined
					$sql = $ilance->db->query("
						SELECT username, email, first_name, last_name
						FROM " . DB_PREFIX . "users
						WHERE $customwhere
						    AND status = 'active'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql))
						{
							unset($premessage);
							
							$premessage = str_replace("{{username}}", $res['username'], $message);
							$premessage = str_replace("{{firstname}}", ucfirst($res['first_name']), $premessage);
							$premessage = str_replace("{{lastname}}", ucfirst($res['last_name']), $premessage);
							
							if (isset($ilance->GPC['sendashtml']) AND $ilance->GPC['sendashtml'] == '1')
							{
								send_email($res['email'], $subject, $premessage, $from, '', 1);
							}
							else
							{
								send_email($res['email'], $subject, $premessage, $from);
							}
						}
						print_action_success($phrase['_bulk_email_was_sent_to_selected_subscribers'], $ilance->GPC['return']);
						exit();
					}
				}
			}
		}
	}
	else
	{
		$area_title = $phrase['_bulk_email_manager'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bulk_email_manager'];
		
		($apihook = $ilance->api('admincp_bulkemail_management')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=bulkemail', $_SESSION['ilancedata']['user']['slng']);
		
		$ilance->subscription = construct_object('api.subscription');
		
		$site_email = SITE_EMAIL;
		$subscription_pulldown = $ilance->subscription->pulldown();
		$wysiwyg_area = print_wysiwyg_editor('description', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','wysiwyg_area','emailcount','emailmethod_pulldown','subject','message','subscription_pulldown','site_email','numberpaid','numberunpaid','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_bulkemail_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'bulkemail.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

// #### RSS FEEDS MANAGER ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rssfeeds')
{
	// #### UPDATE RSS FEED SORTING ########################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-sort')
	{
		foreach ($ilance->GPC['sort'] AS $rssid => $sort)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "rssfeeds
				SET sort = '" . intval($sort) . "'
				WHERE rssid = '" . intval($rssid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
	}
	
	// #### INSERT NEW RSS FEED RESOURCE ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-feed')
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "rssfeeds
			(rssid, rssname, rssurl, sort)
			VALUES (
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['rssname']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['rssurl']) . "',
			'" . intval($ilance->GPC['sort']) . "')
		", 0, null, __FILE__, __LINE__);
	}
	
	// #### REMOVE RSS FEED RESOURCE #######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-feed')
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "rssfeeds
			WHERE rssid = '" . intval($ilance->GPC['rssid']) . "'
		", 0, null, __FILE__, __LINE__);
	}
	
	// #### RSS FEEDS MANAGER ##############################################
	$area_title = $phrase['_rss_feeds'];
	$page_title = SITE_NAME . ' - ' . $phrase['_rss_feeds_manager'];
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=rssfeeds', $_SESSION['ilancedata']['user']['slng']);
	
	($apihook = $ilance->api('admincp_rssfeed_management')) ? eval($apihook) : false;
		
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "rssfeeds
		ORDER BY sort ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($feeds = $ilance->db->fetch_array($sql))
		{
			if ($show['ADMINCP_TEST_MODE'])
			{
				$feeds['remove'] = '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" alt="" /></div>';
			}
			else
			{
				$feeds['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=rssfeeds&amp;subcmd=remove-feed&amp;rssid=' . $feeds['rssid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\');"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="' . $phrase['_remove'] . '" /></a>';
			}
			$rssfeeds[] = $feeds;
		}
	}
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','emailcount','emailmethod_pulldown','subject','message','subscription_pulldown','site_email','numberpaid','numberunpaid','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_rssfeeds_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'rssfeeds.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','rssfeeds'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}    

// #### ATTACHMENT MANAGER #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'attachments')
{
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'attachment-manage-storagetype')
	{
		$area_title = $phrase['_managing_attachment_storage_type'];
		$page_title  = SITE_NAME . ' - ' . $phrase['_managing_attachment_storage_type'];
		
		require_once(DIR_CORE . 'functions_attachment.php');
    
		// #### MOVE ATTACHMENTS FROM DATABASE TO FILE SYSTEM ##############
		if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'movetofilepath')
		{
			$notice = move_attachments_to_filepath();
			if (!empty($notice))
			{
				print_action_success($phrase['_the_following_attachments_within_the_database_were_moved_to_the_file_system'] . "<br /><br />" . $notice, $ilance->GPC['return']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_there_was_an_error_no_attachments_were_found_in_the_database_to_move'], $ilance->GPC['return']);
				exit();
			}
		}
		
		// #### MOVE ATTACHMENTS FROM THE FILE SYSTEM TO THE DATABASE ######
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'movetodatabase')
		{
			$notice = move_attachments_to_database();
			if (!empty($notice))
			{
				print_action_success($phrase['_the_following_attachments_were_moved_into_the_database'] . " " . $notice, $ilance->GPC['return']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_there_was_an_error_no_attachments_were_found_in_the_filesystem'], $ilance->GPC['return']);
				exit();
			}
		}
	}
	
	// #### MANAGING ATTACHMENTS ###########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'attachment-manage')
	{
		$area_title = $phrase['_managing_attachments'];
		$page_title = SITE_NAME . ' - ' . $phrase['_managing_attachments'];
		
		if (isset($ilance->GPC['attachid']) AND !empty($ilance->GPC['attachid']))
		{
			foreach ($ilance->GPC['attachid'] AS $value)
			{
				if (!empty($value))
				{
					$ilance->db->query("
					    DELETE FROM " . DB_PREFIX . "attachment
					    WHERE attachid = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
			
			print_action_success($phrase['_selected_attachments_have_been_removed_from_the_marketplace'], $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_there_was_an_error_no_attachments_have_been_selected_please_retry_your_actions'], $ilance->GPC['return']);
			exit();
		}
	}
	
	// #### MODERATING ATTACHMENTS #########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'attachment-moderate-manage')
	{
		$area_title = $phrase['_moderating_attachments'];
		$page_title = SITE_NAME . ' - ' . $phrase['_moderating_attachments'];
		
		if (isset($ilance->GPC['attachid']) AND !empty($ilance->GPC['attachid']))
		{
			foreach ($ilance->GPC['attachid'] AS $value)
			{
				if (!empty($value))
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "attachment
						SET visible = '1'
						WHERE attachid = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
			
			print_action_success($phrase['_selected_attachments_have_been_moderated_and_verified_to_the_public_marketplace'], $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_there_was_an_error_no_attachments_have_been_selected_for_moderation'], $ilance->GPC['return']);
			exit();
		}
	}
	
	// #### ATTACHMENT MANAGER AREA ########################################
	else
	{
		$area_title = $phrase['_attachments_manager'];
		$page_title = SITE_NAME . ' - ' . $phrase['_attachments_manager'];
		
		($apihook = $ilance->api('admincp_attachment_management')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=attachments', $_SESSION['ilancedata']['user']['slng']);
    
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
		{
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
			{
				$ilance->GPC['page'] = 1;
			}
			else
			{
				$ilance->GPC['page'] = intval($ilance->GPC['page']);
			}
			
			$limit = ' ORDER BY attachid ' . $ilance->db->escape_string($ilance->GPC['orderby']) . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		}
		else
		{
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
			{
				$ilance->GPC['page'] = 1;
			}
			else
			{
				$ilance->GPC['page'] = intval($ilance->GPC['page']);
			}
			
			$limit = ' ORDER BY attachid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		}
    
		$filtersql = '';
		if (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby']))
		{
			$filtersql = " AND `" . $ilance->db->escape_string($ilance->GPC['filterby']) . "` = '" . $ilance->db->escape_string($ilance->GPC['filtervalue']) . "'";
		}
		
		// moderate attachments
		$sql = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE visible = '0'
			$filtersql
			$limit
		", 0, null, __FILE__, __LINE__);
    
		// moderate attachments
		$sqltmp = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE visible = '0'
			$filtersql
		", 0, null, __FILE__, __LINE__);
		
		$totalcount = $ilance->db->num_rows($sqltmp);
		$counter = ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'];
		if ($ilance->db->num_rows($sql) > 0)
		{
			$show['no_moderateattachments'] = false;
			
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql))
			{
				$res['subscriber'] = fetch_user('username', $res['user_id']);
				$res['filesize'] = print_filesize($res['filesize']);
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$moderateattachments[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['no_moderateattachments'] = true;
		}
    
		$prevnext = print_pagnation($totalcount, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['distribution'] . '?cmd=attachments&amp;subcmd=moderate');
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == "search" )
		{
			if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
			{
				$ilance->GPC['page2'] = 1;
			}
			else
			{
				$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
			}
			
			$limit2 = ' ORDER BY attachid '.$ilance->db->escape_string($ilance->GPC['orderby']).' LIMIT '.(($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
		}
		else
		{
			if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
			{
				$ilance->GPC['page2'] = 1;
			}
			else
			{
				$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
			}
			
			$limit2 = ' ORDER BY attachid DESC LIMIT '.(($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
		}
    
		$filtersql = '';
		if (isset($ilance->GPC['filterby']) AND $ilance->GPC['filterby'] != "")
		{
			$filtersql = " AND `".$ilance->db->escape_string($ilance->GPC['filterby'])."` = '".$ilance->db->escape_string($ilance->GPC['filtervalue'])."'";
		}
		
		// manage attachments
		$sql2 = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE visible = '1'
			$filtersql
			$limit2
		", 0, null, __FILE__, __LINE__);
    
		// manage attachments
		$sql2tmp = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE visible = '1'
			$filtersql
		", 0, null, __FILE__, __LINE__);
    
		$totalcount2 = $ilance->db->num_rows($sql2tmp);
		$counter2 = ($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay'];
		if ($ilance->db->num_rows($sql2) > 0)
		{
			$show['no_attachments'] = false;
			
			$row_count2 = 0;
			while ($res2 = $ilance->db->fetch_array($sql2))
			{
				$res2['subscriber'] = fetch_user('username', $res2['user_id']);
				$res2['filesize'] = print_filesize($res2['filesize']);
				$res2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$attachextension = fetch_extension($res2['filename']) . '.gif';
				if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
				{
					$attachextension = fetch_extension($res2['filename']) . '.gif';
				}
				else
				{
					$attachextension = 'attach.gif';
				}
				
				$res2['attachextension'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" border="0" alt="" />';
				$attachments[] = $res2;
				$row_count2++;
			}
		}
		else
		{
			$show['no_attachments'] = true;
		}
		if (empty($ilance->GPC['filterby']))
		{
			$ilance->GPC['filterby'] = '';
		}
		if (empty($ilance->GPC['filtervalue']))
		{
			$ilance->GPC['filtervalue'] = '';
		}
		if (empty($ilance->GPC['orderby']))
		{
			$ilance->GPC['orderby'] = '';
		}
		$prevnext2 = print_pagnation($totalcount2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $ilpage['distribution'] . '?cmd=attachments&amp;filterby='.$ilance->GPC['filterby'].'&amp;filtervalue='.$ilance->GPC['filtervalue'].'&amp;orderby='.$ilance->GPC['orderby'], 'page2');
    
		// construct attachment system
		$ilance->attachment = construct_object('api.attachment');
		$totalattachments = $ilance->attachment->totalattachments();
		$totaldiskspace = $ilance->attachment->totaldiskspace();
		$totaldownloads = $ilance->attachment->totaldownloads();
		$storagetype = $ilance->attachment->storagetype('type');
		$storagetypeaction = $ilance->attachment->storagetype('formaction');
    
		$configuration_attachmentsettings = $ilance->admincp->construct_admin_input('attachmentsystem', $ilpage['distribution'] . '?cmd=attachments');
		$configuration_attachmentmoderation = $ilance->admincp->construct_admin_input('attachmentmoderation', $ilpage['distribution'] . '?cmd=attachments');
		$configuration_attachmentlimits = $ilance->admincp->construct_admin_input('attachmentlimit', $ilpage['distribution'] . '?cmd=attachments');
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','configuration_attachmentsettings','configuration_attachmentmoderation','configuration_attachmentlimits','totalattachments','totaldiskspace','storagetype','totaldownloads','storagetypeaction','prevnext','prevnext2','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		    
		($apihook = $ilance->api('admincp_attachments_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'attachments.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','moderateattachments','attachments'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
    
// #### PORTFOLIO LISTINGS #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'portfolios')
{
	$area_title = $phrase['_portfolio_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_portfolio_management'];
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=portfolios', $_SESSION['ilancedata']['user']['slng']);
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','numberpaid','numberunpaid','paidprevnext','unpaidprevnext','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_portfolios_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'portfolios.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','escrows'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
    
// #### CREDENTIAL VERIFICATION SETTINGS ###############################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'verifications')
{
	$area_title = $phrase['_credential_verification_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_credential_verification_management'];
	
	($apihook = $ilance->api('admincp_verification_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=verifications', $_SESSION['ilancedata']['user']['slng']);
	
	// #### VERIFICATION MANAGE ############################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'verification-manage' AND !empty($ilance->GPC['answerid']))
	{
		foreach ($ilance->GPC['answerid'] as $value)
		{
			if (isset($ilance->GPC['delete']) AND !empty($value))
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "profile_answers
					WHERE answerid = '" . intval($value) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
		}
		
		print_action_success($phrase['_credential_verifications_selected_were_successfully_deleted_from_the_datastore'], $ilance->GPC['return']);
		exit();
	}
	
	// #### VERIFY CREDENTIAL FOR X DAYS ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'verify-credential' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$expiry = gmdate('Y-m-d H:i:s', mktime(gmdate('H', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('i', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('s', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('m', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('d', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))+$ilconfig['verificationlength'], gmdate('Y', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))));
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "profile_answers
			SET isverified = '1',
			visible = '1',
			verifyexpiry = '" . $expiry . "'
			WHERE answerid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		print_action_success($phrase['_credential_verification_was_successfully_verified'], $ilpage['distribution'] . '?cmd=verifications&amp;page='.intval($ilance->GPC['page']));
		exit();
	}
	
	// #### UN VERIFY CREDENTIAL ###########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unverify-credential' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "profile_answers
			SET isverified = '0',
			verifyexpiry = '0000-00-00 00:00:00'
			WHERE answerid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		print_action_success($phrase['_credential_verification_details_were_successfully_unverified_an_icon'], $ilpage['distribution'] . '?cmd=verifications&amp;page='.(int)$ilance->GPC['page']);
		exit();
	}
	
	$sql1 = $sql2 = $sql3 = $isverified1 = $isverified2 = $isverified3 = $answerid = $user_id = $orderby1 = $orderby2 = '';
	$number = $row_count = 0;
	
	$status = 'total';
	$sqlorderby = 'ORDER BY answers.answerid DESC';
	$orderby2 = 'checked="checked"';
	
	$show['no_verifications'] = true;
	
	$ilance->GPC['isverified'] = isset($ilance->GPC['isverified']) ? intval($ilance->GPC['isverified']) : 0;
	if ($ilance->GPC['isverified'] == '0')
	{
		$sql3 = "AND answers.isverified = '0'";
	}
	else if ($ilance->GPC['isverified'] == '1')
	{
		$sql3 = "AND answers.isverified = '1'";
	}
	
	if ($ilance->GPC['isverified'] == '-1')
	{
		$isverified1 = 'selected="selected"';
	}
	else if ($ilance->GPC['isverified'] == '1')
	{
		$isverified2 = 'selected="selected"';
		$status = 'paid';
	}
	else if ($ilance->GPC['isverified'] == '0')
	{
		$isverified3 = 'selected="selected"';
		$status = 'pending';
	}
	
	// #### SEARCH MODE ####################################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
		if (isset($ilance->GPC['answerid']) AND $ilance->GPC['answerid'] > 0)
		{
			$sql1 = "AND answers.answerid = '" . intval($ilance->GPC['answerid']) . "'";
			$answerid = intval($ilance->GPC['answerid']);
		}
		if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
		{
			$sql2 = "AND answers.user_id = '" . intval($ilance->GPC['user_id']) . "'";
			$user_id = intval($ilance->GPC['user_id']);
		}
	}
	
	$ilance->GPC['orderby'] = isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : 'DESC';
	
	if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']))
	{
		if ($ilance->GPC['orderby'] == 'DESC')
		{
			$sqlorderby = 'ORDER BY answers.answerid DESC';
			$orderby1 = '';
			$orderby2 = 'checked="checked"';
		}
		else
		{
			$sqlorderby = 'ORDER BY answers.answerid ASC';
			$orderby1 = 'checked="checked"';
			$orderby2 = '';
		}
	}
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	$sql = $ilance->db->query("
		SELECT answers.answerid, answers.questionid, answers.user_id, answers.answer, answers.date, answers.visible, answers.isverified, answers.invoiceid, answers.contactname, answers.contactnumber, answers.contactnotes, answers.verifyexpiry, questions.question, questions.verifycost
		FROM " . DB_PREFIX . "profile_answers AS answers,
		" . DB_PREFIX . "profile_questions AS questions
		WHERE answers.questionid = questions.questionid
		    AND answers.answer != ''
		    AND answers.contactname != ''
		    AND answers.contactnumber != ''
		    AND answers.contactnotes != ''
		    $sql1
		    $sql2
		    $sql3
		    $sqlorderby
		    $limit
	", 0, null, __FILE__, __LINE__);
	
	$sqltemp = $ilance->db->query("
		SELECT answers.answerid, answers.questionid, answers.user_id, answers.answer, answers.date, answers.visible, answers.isverified, answers.invoiceid, answers.contactname, answers.contactnumber, answers.contactnotes, answers.verifyexpiry, questions.question, questions.verifycost
		FROM " . DB_PREFIX . "profile_answers AS answers,
		" . DB_PREFIX . "profile_questions AS questions
		WHERE answers.questionid = questions.questionid
		    AND answers.answer != ''
		    AND answers.contactname != ''
		    AND answers.contactnumber != ''
		    AND answers.contactnotes != ''
		    $sql1
		    $sql2
		    $sql3
		    $sqlorderby
	", 0, null, __FILE__, __LINE__);
	
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['no_verifications'] = false;
		
		$number = $ilance->db->num_rows($sqltemp);
		while ($res = $ilance->db->fetch_array($sql))
		{
			$res['username'] = fetch_user('username', $res['user_id']);
			$res['email'] = fetch_user('email', $res['user_id']);
			switch ($res['isverified'])
			{
				case '0':
				{
					$res['verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="' . $phrase['_not_verified'] . '" border="0" />';
					$res['actions'] = '<span class="blue"><a href="' . $ilpage['distribution'] . '?cmd=verifications&amp;subcmd=verify-credential&amp;id='.$res['answerid'].'&amp;page='.intval($ilance->GPC['page']).'" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">' . $phrase['_verify'] . '</a></span>';
					break;
				}                                    
				case '1':
				{
					$res['verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="' . $phrase['_verified'] . '" border="0" />';
					$res['actions'] = '<span class="red"><a href="' . $ilpage['distribution'] . '?cmd=verifications&amp;subcmd=unverify-credential&amp;id='.$res['answerid'].'&amp;page='.intval($ilance->GPC['page']).'" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><span style="color:red">' . $phrase['_unverify'] . '</span></a></span>';
					break;
				}
			}
			
			$sqlinv = $ilance->db->query("
				SELECT status
				FROM " . DB_PREFIX . "invoices
				WHERE invoiceid = '" . $res['invoiceid'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlinv) > 0)
			{
				$resinv = $ilance->db->fetch_array($sqlinv);
				if ($resinv['status'] == 'paid')
				{
					$res['payout'] = $phrase['_yes'];
				}
				else
				{
					$res['payout'] = $phrase['_no'];
				}
			}
			else
			{
				$res['payout'] = '-';
			}
			
			if ($res['verifyexpiry'] == '0000-00-00 00:00:00')
			{
				$res['verifyexpiry'] = '-';
			}
			
			$res['answer'] = stripslashes(nl2br($res['answer']));
			$res['contactname'] = stripslashes($res['contactname']);
			$res['contactnumber'] = stripslashes($res['contactnumber']);
			$res['contactnotes'] = stripslashes(nl2br($res['contactnotes']));
			$res['verifycost'] = $ilance->currency->format($res['verifycost']);
			$res['invoiceid'] = '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $res['invoiceid'] . '&amp;pp=10">' . $res['invoiceid'] . '</a></span>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$row_count++;
			$verifications[] = $res;
		}
	}
		
	$querybit = '';
	foreach ($ilance->GPC as $key => $value)
	{
		if ($key != 'submit' AND $key != 'cmd' AND $key != 'subcmd')
		{
			$querybit .= '&amp;' . $key . '=' . $value;
		}
	}
	
	$scriptpage = $ilpage['distribution'] . '?cmd=verifications&amp;subcmd=search' . $querybit;
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $scriptpage);
	
	// #### VERIFICATION SETTINGS TAB ######################################
	$configuration_verificationsettings = $ilance->admincp->construct_admin_input('verificationsystem', $ilpage['distribution'] . '?cmd=verifications');
	
	$pprint_array = array('orderby1','orderby2','buildversion','ilanceversion','login_include_admin','configuration_verificationsettings','isverified1','isverified2','isverified3','answerid','user_id','number','status','prevnext','numberpaid','numberunpaid','paidprevnext','unpaidprevnext','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_verifications_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'verifications.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','verifications'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
    
// #### REFERRAL LISTINGS ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'referrals')
{
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'referral-manage')
	{
		if (isset($ilance->GPC['delete']) AND !empty($ilance->GPC['delete']))
		{
			foreach ($ilance->GPC['id'] as $value)
			{
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "referral_data WHERE id = '" . intval($value) . "' LIMIT 1");
			}
			
			print_action_success($phrase['_the_selected_referral_entries_have_been_removed_from_the_datastore'], $ilance->GPC['return']);
			exit();
		}
		else
		{
			if (isset($ilance->GPC['payout']) AND !empty($ilance->GPC['payout']))
			{
				foreach ($ilance->GPC['payout'] as $key => $value)
				{
					$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "referral_data WHERE id = '".intval($key)."'", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						$sql2 = $ilance->db->query("SELECT available_balance, total_balance FROM " . DB_PREFIX . "users WHERE user_id = '".$res['referred_by']."'", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							$res2 = $ilance->db->fetch_array($sql2);
							$new_credit_amount = trim($ilance->GPC['amount']);
							
							$total_now = $res2['total_balance'];
							$avail_now = $res2['available_balance'];
							
							$new_total_now = ($total_now+$new_credit_amount);
							$new_avail_now = ($avail_now+$new_credit_amount);
							
							$ilance->db->query("UPDATE " . DB_PREFIX . "users SET total_balance = '".$new_total_now."', available_balance = '".$new_avail_now."' WHERE user_id = '".$res['referred_by']."'", 0, null, __FILE__, __LINE__);
			
							// adjust members total amount received for referral payments from admin
							insert_income_reported($res['referred_by'], sprintf("%01.2f", $new_credit_amount), 'credit');
			
							$ilance->accounting = construct_object('api.accounting');
							$ilance->accounting->insert_transaction(
							0,
							0,
							0,
							$res['referred_by'],
							0,
							0,
							0,
							$phrase['_referral_account_bonus'],
							sprintf("%01.2f", $new_credit_amount),
							sprintf("%01.2f", $new_credit_amount),
							'paid',
							'credit',
							'account',
							DATETIME24H,
							DATEINVOICEDUE,
							DATETIME24H,
							'',
							0,
							0,
							0);
								
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "referral_data
								SET paidout = '1'
								WHERE id = '".intval($key)."'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							
							$sqlemail = $ilance->db->query("
								SELECT email, username, first_name, last_name
								FROM " . DB_PREFIX . "users
								WHERE user_id = '" . $res['referred_by'] . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sqlemail) > 0)
							{
								$resemail = $ilance->db->fetch_array($sqlemail, DB_ASSOC);
								
								$ilance->email = construct_dm_object('email', $ilance);
	
								$ilance->email->mail = $resemail['email'];
								$ilance->email->slng = fetch_user_slng($res['referred_by']);
								
								$ilance->email->get('referral_account_credit');		
								$ilance->email->set(array(
									'{{customer}}' => stripslashes($resemail['username']),
									'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
									'{{datetime}}' => DATETODAY." ".TIMENOW,
								));
								
								$ilance->email->send();
			    
								$ilance->email->mail = SITE_EMAIL;
								$ilance->email->slng = fetch_site_slng();
								
								$ilance->email->get('referral_account_credit_admin');		
								$ilance->email->set(array(
									'{{customer}}' => stripslashes($resemail['username']),
									'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
									'{{datetime}}' => DATETODAY . " " . TIMENOW,
								));
								
								$ilance->email->send();
							}
						}
					}
					
					print_action_success($phrase['_the_selected_referring_user_was_credited_funds_to_their_online_account'], $ilance->GPC['return']);
					exit();
				}
			}
		}
	}
	
	// #### REFERRAL MANAGEMENT ####################################
	else
	{
		$area_title = $phrase['_referral_management'];
		$page_title  = SITE_NAME . ' - ' . $phrase['_referral_management'];
		
		($apihook = $ilance->api('admincp_referral_management')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=referrals', $_SESSION['ilancedata']['user']['slng']);
			    
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		
		$limit = ' ORDER BY id DESC LIMIT '.(($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
    
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "referral_data
			$limit
		", 0, null, __FILE__, __LINE__);
		
		$sqltmp = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "referral_data
		", 0, null, __FILE__, __LINE__);
    
		$totalcount = $ilance->db->num_rows($sqltmp);
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		if ($ilance->db->num_rows($sql) > 0)
		{
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql))
			{
				$res['username'] = fetch_user('username', $res['user_id']);
				if ($res['username'] == $phrase['_unknown'])
				{
					$res['username'] = "[" . $phrase['_removed'] . "]";
					$res['email'] = "[" . $phrase['_removed'] . "]";
				}
				else
				{
					$res['username'] = '<a href="'.$ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.$res['user_id'].'">'.fetch_user('username', $res['user_id']).'</a>';
					$res['email'] = '<a href="mailto:'.fetch_user('email', $res['user_id']).'">'.fetch_user('email', $res['user_id']).'</a>';
				}
	    
				$res['referredby'] = fetch_user('username', $res['referred_by']);
				if ($res['referredby'] == $phrase['_unknown'])
				{
					$res['referredby'] = "[Removed]";
				}
				else
				{
					$res['referredby'] = '<a href="'.$ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.$res['referred_by'].'">'.fetch_user('username', $res['referred_by']).'</a>';
				}
	    
				$res['ridcode'] = fetch_user('rid', $res['referred_by']);
				$res['date'] = print_date($res['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
				$res['lastvisit'] = print_date(fetch_user('lastseen', $res['user_id']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
	    
				// posted auction?
				if ($res['postauction'] == 0)
				{
					$res['awardedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['awardedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
	    
				// awarded any auction?
				if ($res['awardauction'] == 0)
				{
					$res['postedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['postedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
		    
				// paid any subscription fee?
				if ($res['paysubscription'] == 0)
				{
					$res['invoicepaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['invoicepaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// paid any final value fee?
				if ($res['payfvf'] == 0)
				{
					$res['fvfpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['fvfpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// paid any insertion fee?
				if ($res['payins'] == 0)
				{
					$res['inspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['inspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// paid any lance ads fee?
				if ($res['paylanceads'] == 0)
				{
					$res['lanceadspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['lanceadspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// paid any portfolio upsell fee?
				if ($res['payportfolio'] == 0)
				{
					$res['portfoliopaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['portfoliopaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// paid any credential verification fee?
				if ($res['paycredentials'] == 0)
				{
					$res['credentialpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['credentialpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// paid any auction upsell enhancement fee?
				if ($res['payenhancements'] == 0)
				{
					$res['enhancementspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				else
				{
					$res['enhancementspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				
				// has already been paid out by admin?
				if ($res['paidout'])
				{
					$res['payout'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_yes'] . '" border="0" />';
				}
				else
				{
					$res['payout'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_no'] . '" border="0" />';
				}
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$referrals[] = $res;
				$row_count++;
			}
			
			$show['no_referrals'] = false;
		}
		else
		{
			$show['no_referrals'] = true;
		}
		$prevnext = print_pagnation($totalcount, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['distribution'] . '?cmd=referrals');
	}
	
	$currency = print_left_currency_symbol();
	$configuration_referalsystem = $ilance->admincp->construct_admin_input('referalsystem', $ilpage['distribution'] . '?cmd=referrals');
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','configuration_referalsystem','prevnext','currency','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_referrals_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'referrals.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','referrals'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
    
// #### BIDS MANAGER ###################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bids')
{
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-all-categories' AND isset($ilance->GPC['title']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET incrementgroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			WHERE cattype = 'product'
		");
		
		print_action_success($phrase['_all_categories_have_been_assigned_to_the_selected_bid_increment_group'], $ilpage['distribution'] . '?cmd=bids');
		exit();
	}
	
	// #### admin retracting or physically deleting bids ###################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'do-bid-action')
	{
		if (isset($ilance->GPC['bidid']) AND is_array($ilance->GPC['bidid']) AND count($ilance->GPC['bidid']) > 0)
		{
			// #### require backend ################################
			$ilance->bid = construct_object('api.bid');                        
			$ilance->bid_retract = construct_object('api.bid_retract');
			
			foreach ($ilance->GPC['bidid'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT project_id, bidstatus, user_id
					FROM " . DB_PREFIX . "project_bids
					WHERE bid_id = '" . intval($value) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					
					// #### admin retracting the bid #######
					if (isset($ilance->GPC['retract']))
					{
						$res['isawarded'] = ($res['bidstatus'] == 'awarded') ? true : false;
						$res['reason'] = (!empty($ilance->GPC['bidretractreason'])) ? ilance_htmlentities($ilance->GPC['bidretractreason']) : 'Bid retracted by admin';
						
						// re-adjust the current bid amount due to the retraction
						$ilance->bid_retract->construct_bid_retraction($res['user_id'], intval($value), $res['project_id'], $res['reason'], $res['isawarded'], false);
					}
					
					// #### admin physically deleting the bid
					else if (isset($ilance->GPC['delete']))
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "project_bids
							WHERE bid_id = '" . intval($value) . "'
							LIMIT 1
						");
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET bids = bids - 1
							WHERE project_id = '" . $res['project_id'] . "'
						");
						
						// re-adjust the current bid amount due to the retraction
					}
				}
			}
			
			print_action_success($phrase['_selected_bids_have_been_successfully_removed_from_the_auction_listing'], $ilance->GPC['return']);
			exit();
		}
	}
	
	$area_title = $phrase['_bid_manager'];
	$page_title = SITE_NAME . ' - ' . $phrase['_bid_manager'];
	
	($apihook = $ilance->api('admincp_bid_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=bids', $_SESSION['ilancedata']['user']['slng']);
	
	// #### update bid field sorting ###############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fields-sort')
	{
		foreach ($ilance->GPC['sort'] AS $key => $value)
		{
			if (!empty($key) AND !empty($value))
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "bid_fields
					SET sort = '" . $ilance->db->escape_string($value) . "'
					WHERE fieldid = '" . $ilance->db->escape_string($key) . "'
				");    
			}
		}
		
		print_action_success($phrase['_custom_bid_field_sorting_has_been_updated_and_changes_should_take_effect_immediately'], $ilance->GPC['return']);
		exit();
	}
	
	// #### update custom bid field ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatebidfield' AND isset($ilance->GPC['fieldid']) AND $ilance->GPC['fieldid'] > 0)
	{
		$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
		$query1 = $query2 = '';
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] AS $slng => $value)
			{
				$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "bid_fields
			SET 
			$query1
			$query2
			inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			multiplechoice = '" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			sort = '" . intval($ilance->GPC['sort']) . "',
			visible = '" . intval($ilance->GPC['visible']) . "'
			WHERE fieldid = '" . intval($ilance->GPC['fieldid']) . "'
		");
		
		print_action_success($phrase['_custom_bid_field_was_updated_and_changes_should_take_effect_immediately'], $ilance->GPC['return']);
		exit();
	}
	
	// #### insert custom bid field ################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'addbidfield')
	{
		$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "bid_fields
			(fieldid, inputtype, multiplechoice, sort, visible)
			VALUES (
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			'" . intval($ilance->GPC['sort']) . "',
			'" . intval($ilance->GPC['visible']) . "')
		");
		
		$insid = $ilance->db->insert_id();
		
		$query1 = $query2 = '';
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] AS $slng => $value)
			{
				$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] AS $slng => $value)
			{
				$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "bid_fields
			SET
			$query1
			$query2
			visible = '" . intval($ilance->GPC['visible']) . "'
			WHERE fieldid = '" . $insid . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_custom_bid_field_was_added_and_changes_should_take_effect_immediately'], $ilance->GPC['return']);
		exit();
	}

	// #### remove custom bid field ################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-bid-field')
	{
		
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "bid_fields
			WHERE fieldid = '" . intval($ilance->GPC['id']) . "'
		");
	}

	if (!isset($ilance->GPC['subcmd']))
	{
		// multilanguage bid fields
		$row_count = 0;
		$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
		while ($language = $ilance->db->fetch_array($languages))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$servicelanguages[] = $language;
			$row_count++;
		}
	}
	
	// requesting normal mode or edit/update mode?
	$submit_field = $question = $question_description = $multiplechoicefield = $sort = $checked_active = $hiddenfield = $checked_active = '';
	$fieldid = 0;
	$field_inputtype_pulldown = '<select name="inputtype" style="font-family: verdana">';
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-bid-field')
	{
		// multilanguage question and description
		$row_count = 0;
		$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
		while ($language = $ilance->db->fetch_array($languages))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				    
			// fetch english question and description values
			$sql = $ilance->db->query("
				SELECT question_$language[slng] AS question, description_$language[slng] AS question_description
				FROM " . DB_PREFIX . "bid_fields
				WHERE fieldid = '" . intval($ilance->GPC['id']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql))
				{
					$language['question'] = $res['question'];	
					$language['question_description'] = $res['question_description'];	
				}
			}
			
			$servicelanguages[] = $language;
			$row_count++;
		}
		
		
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "bid_fields
			WHERE fieldid = '" . intval($ilance->GPC['id']) . "'
		");
		$res = $ilance->db->fetch_array($sql);
		
		if ($show['ADMINCP_TEST_MODE'])
		{
			$submit_field = '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" disabled="disabled" />';
		}
		else
		{
			$submit_field = '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" />';
		}
		
		$subcmd = 'updatebidfield';
		
		$hiddenfield = '<input type="hidden" value="' . $res['fieldid'] . '" name="fieldid" />';
		$question = $res['question_' . $_SESSION['ilancedata']['user']['slng']];
		$question_description = $res['description_' . $_SESSION['ilancedata']['user']['slng']];
		$multiplechoicefield = $res['multiplechoice'];
		$sort = $res['sort'];
		if ($res['visible'])
		{
			$checked_active = 'checked="checked"';
		}
		
		$field_inputtype_pulldown .= '<option value="yesno"'; if ($res['inputtype'] == "yesno") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$field_inputtype_pulldown .= '<option value="int"'; if ($res['inputtype'] == "int") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_integer_field_numbers_only'] . '</option>';
		$field_inputtype_pulldown .= '<option value="textarea"'; if ($res['inputtype'] == "textarea") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_textarea_field_multiline'] . '</option>';
		$field_inputtype_pulldown .= '<option value="text"'; if ($res['inputtype'] == "text") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_input_text_field_singleline'] . '</option>';
		$field_inputtype_pulldown .= '<option value="multiplechoice"'; if ($res['inputtype'] == "multiplechoice") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$field_inputtype_pulldown .= '<option value="pulldown"'; if ($res['inputtype'] == "pulldown") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
		$field_inputtype_pulldown .= '<option value="date"'; if ($res['inputtype'] == "date") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . $phrase['_date_input_field'] . '</option>';
	}
	else
	{
		$submit_field = ($show['ADMINCP_TEST_MODE'])
			? '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" disabled="disabled" />'
			: '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" />';
		
		$subcmd = 'addbidfield';
		
		$field_inputtype_pulldown .= '<option value="yesno">' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$field_inputtype_pulldown .= '<option value="int">' . $phrase['_integer_field_numbers_only'] . '</option>';
		$field_inputtype_pulldown .= '<option value="textarea">' . $phrase['_textarea_field_multiline'] . '</option>';
		$field_inputtype_pulldown .= '<option value="text">' . $phrase['_input_text_field_singleline'] . '</option>';
		$field_inputtype_pulldown .= '<option value="multiplechoice">' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$field_inputtype_pulldown .= '<option value="pulldown">' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
		$field_inputtype_pulldown .= '<option value="date">' . $phrase['_date_input_field'] . '</option>';
	}
	$field_inputtype_pulldown .= '</select>';

	// #### select existing bid fields #############################
	$no_bidfields = true;
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "bid_fields
		ORDER BY sort ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$no_bidfields = false;
		$row_count = 0;
		
		$ilance->bid = construct_object('api.bid');
		$ilance->bid_fields = construct_object('api.bid_fields');
		
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['sortinput'] = '<input type="text" name="sort[' . $res['fieldid'] . ']" value="' . $res['sort'] . '" class="input" size="3" style="text-align:center" />';
			$res['question_active'] = ($res['visible'])
				? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />'
				: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			
			$res['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-bid-field&amp;id=' . $res['fieldid'] . '#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$res['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_remove-bid-field&amp;id=' . $res['fieldid'] . '" onclick="return confirm_js(\'' . $phrase['_removing_this_bid_field_will_additionally_remove_all_associated'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$res['catcount'] = $ilance->bid_fields->fetch_categories_assigned($res['fieldid'], true);
			$res['answercount'] = $ilance->bid_fields->fetch_answer_count_submitted($res['fieldid']);
			$bidfields[] = $res;
			$row_count++;
		}
	}
	
	$acceptedgroupby = array('project_id', 'bidstatus');

	if (!isset($ilance->GPC['subcmd']))
	{
		$ilance->GPC['subcmd'] = '';
	}
	
	$orderby = 'DESC';
	if (isset($ilance->GPC['orderby']))
	{
		$orderby = $ilance->GPC['orderby'];
	}
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$limit = 'ORDER BY b.bid_id ' . $orderby . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$addquery = $addquery2 = $addquery3 = $addquery4 = $project_id = $user_id = $bidstatus = $bid_id = '';
	
	// #### searching by listing id number #################################
	if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
	{
		$project_id = intval($ilance->GPC['project_id']);
		$addquery = "AND p.project_id = '" . $project_id . "'";
	}
	
	// #### searching by user id number ####################################
	if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
	{
		$user_id = intval($ilance->GPC['user_id']);
		$addquery2 = "AND u.user_id = '" . $user_id . "'";
	}
	
	// #### searching by bid status ########################################
	if (isset($ilance->GPC['bidstatus']) AND $ilance->GPC['bidstatus'] != '')
	{
		$bidstatus = $ilance->GPC['bidstatus'];
		$addquery3 = "AND b.bidstatus = '" . $ilance->db->escape_string($bidstatus) . "'";
	}
	
	// #### searching by bid id number #####################################
	if (isset($ilance->GPC['bid_id']) AND $ilance->GPC['bid_id'] > 0)
	{
		$bid_id = intval($ilance->GPC['bid_id']);
		$addquery4 = "AND b.bid_id = '" . $bid_id . "'";
	}
	
	$resultbids = $ilance->db->query("
		SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.fvf, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
		FROM " . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u
		WHERE b.project_id = p.project_id
		    AND p.project_state = 'service'
		    AND u.user_id = b.user_id
		    $addquery
		    $addquery2
		    $addquery3
		    $addquery4
		    $limit
	");

	$numberrows = $ilance->db->query("
		SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days,b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.fvf, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
		FROM " . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u
		WHERE b.project_id = p.project_id
		    AND p.project_state = 'service'
		    AND u.user_id = b.user_id
		    $addquery
		    $addquery2
		    $addquery3
		    $addquery4
	");
	$number = $ilance->db->num_rows($numberrows);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	    
	if ($ilance->db->num_rows($resultbids) > 0)
	{
		$show['no_servicebids'] = false;
		$row_count = 0;
		
		$ilance->feedback = construct_object('api.feedback');
		$ilance->subscription = construct_object('api.subscription');
		
		while ($bidrows = $ilance->db->fetch_array($resultbids, DB_ASSOC))
		{
			$sql_user_results = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $bidrows['project_user_id'] . "'
			");
			$res_project_user = $ilance->db->fetch_array($sql_user_results, DB_ASSOC);
	
			$bidrows['fvf'] = ($bidrows['fvf'] > 0)
				? $ilance->currency->format($bidrows['fvf'])
				: $phrase['_none'];
				
			$bidrows['bid_datetime'] = print_date($bidrows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$bidrows['bidamount'] = $ilance->currency->format($bidrows['bidamount'], $bidrows['currencyid']);
			$bidrows['delivery'] = ($bidrows['estimate_days'] <= 1) ? $bidrows['estimate_days'] . ' ' . $phrase['_day'] : $bidrows['estimate_days'] . ' ' . $phrase['_days'];
			$bidrows['proposal'] = stripslashes($bidrows['proposal']);
			$bidrows['isonline'] = print_online_status($bidrows['user_id']);
			$bidrows['provider'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $bidrows['user_id'] . '">' . stripslashes($bidrows['username']) . '</a>';
			$bidrows['level'] = $ilance->subscription->print_subscription_icon($bidrows['user_id']);
			$bidrows['city'] = ucfirst($bidrows['city']);
			$bidrows['state'] = ucfirst($bidrows['state']);
			$bidrows['zip'] = trim(mb_strtoupper($bidrows['zip_code']));
			$bidrows['location'] = $bidrows['state'].' &gt; '.print_user_country($bidrows['user_id'], fetch_site_slng());
			$bidrows['title'] = fetch_auction('project_title', $bidrows['project_id']);
	
			$sqlattachments = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE attachtype = 'bid'
				    AND project_id = '" . $bidrows['project_id'] . "'
				    AND user_id = '" . $bidrows['user_id'] . "'
				    AND visible = '1'
			");
			if ($ilance->db->num_rows($sqlattachments) > 0)
			{
				$bidrows['bidattach'] = '';
				while ($resattach = $ilance->db->fetch_array($sqlattachments, DB_ASSOC))
				{
					$bidrows['bidattach'] .= '<tr>';
					$bidrows['bidattach'] .= '<td align="left" class="smaller"><strong>' . $phrase['_attachments'] . '</strong><br />';
					$bidrows['bidattach'] .= '<span class="smaller" title="' . $resattach['filename'] . '" style="word-spacing:-6px"><font color="888888">';
					$tempvariable_underscore = str_replace("_", "_ ", $resattach['filename']);
					$tempvariable = str_replace("-", "- ", $tempvariable_underscore);
					$bidrows['bidattach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="" id="" /><a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $resattach['filehash'] . '" target="_blank">' . $tempvariable . '</a></font></span><br />';
					$bidrows['bidattach'] .= '</td>';
					$bidrows['bidattach'] .= '</tr>';
				}
			}
			else
			{
				$bidrows['bidattach'] = '';
			}
	
			$bidrows['award'] = '[' . $bidrows['bidstatus'] . ']';
			$bidrows['delete'] = '<input type="checkbox" name="bidid[]" value="' . $bidrows['bid_id'] . '" />';
			$bidrows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$servicebids[] = $bidrows;
			$row_count++;
		}
	}
	else
	{
		$show['no_servicebids'] = true;
	}

	if (!isset($ilance->GPC['project_id']))
	{
		$ilance->GPC['project_id'] = 0;
	}
	if (!isset($ilance->GPC['user_id']))
	{
		$ilance->GPC['user_id'] = 0;
	}
	if (!isset($ilance->GPC['orderby']))
	{
		$ilance->GPC['orderby'] = '';
	}
	if (!isset($ilance->GPC['bidstatus']))
	{
		$ilance->GPC['bidstatus'] = '';
	}
	if (!isset($ilance->GPC['groupby']))
	{
		$ilance->GPC['groupby'] = '';
	}
	// murugan changes on apr 15
	$ilconfig['globalfilters_maxrowsdisplay'] = 50;
	$scriptpage = $ilpage['distribution'] . '?cmd=bids&amp;subcmd=' . $ilance->GPC['subcmd'] . '&amp;project_id=' . $ilance->GPC['project_id'] . '&amp;user_id=' . $ilance->GPC['user_id'] . '&amp;bidstatus=' . $ilance->GPC['bidstatus'] . '&amp;orderby=' . $ilance->GPC['orderby'];
	$serviceprevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);
	
	$orderby = 'DESC';
	if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']))
	{
		$orderby = $ilance->GPC['orderby'];
	}
	
	$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
	$limit2 = 'ORDER BY b.bid_id ' . $orderby . ' LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$addquery = $addquery2 = $addquery3 = $addquery4 = $bid_id = $bidstatus = $user_id = $project_id = "";
	
	if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
	{
		$project_id = intval($ilance->GPC['project_id']);
		$addquery = "AND p.project_id = '" . $project_id . "'";
	}
	
	if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
	{
		$user_id = intval($ilance->GPC['user_id']);
		$addquery2 = "AND u.user_id = '" . $user_id . "'";
	}
	
	if (isset($ilance->GPC['bidstatus']) AND $ilance->GPC['bidstatus'] != '')
	{
		$bidstatus = $ilance->GPC['bidstatus'];
		$addquery3 = "AND b.bidstatus = '" . $ilance->db->escape_string($bidstatus) . "'";
	}
	
	if (isset($ilance->GPC['bid_id']) AND $ilance->GPC['bid_id'] > 0)
	{
		$bid_id = intval($ilance->GPC['bid_id']);
		$addquery4 = "AND b.bid_id = '" . $bid_id . "'";
	}
	
	$resultbids2 = $ilance->db->query("
		SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.isproxybid, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
		FROM " . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u
		WHERE b.project_id = p.project_id
		    AND p.project_state = 'product'
		    AND u.user_id = b.user_id
		    $addquery
		    $addquery2
		    $addquery3
		    $addquery4
		    $limit2
	");
	
	$numberrows2 = $ilance->db->query("
		SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.isproxybid, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
		FROM " . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u
		WHERE b.project_id = p.project_id
		    AND p.project_state = 'product'
		    AND u.user_id = b.user_id
		    $addquery
		    $addquery2
		    $addquery3
		    $addquery4
	");
	$number2 = $ilance->db->num_rows($numberrows2);
	
	
	$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];

	if ($ilance->db->num_rows($resultbids2) > 0)
	{
		$row_count = 0;
		while ($bidrows = $ilance->db->fetch_array($resultbids2, DB_ASSOC))
		{
			$bidrows['bid_datetime'] = print_date($bidrows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$sql_user_results = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE user_id = '" . $bidrows['project_user_id'] . "'");
			$res_project_user = $ilance->db->fetch_array($sql_user_results);
			
			$rowbeforeexchange = $bidrows['bidamount'];
			$bidrows['bidamount'] = $ilance->currency->format($rowbeforeexchange, $bidrows['currencyid']);
			$bidrows['isonline'] = print_online_status($bidrows['user_id']);
			$bidrows['provider'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $bidrows['user_id'] . '">' . stripslashes($bidrows['username']) . '</a>';
			// murugan changes on apr 15
			$bidrows['consignor'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $bidrows['project_user_id'] . '">' . fetch_user('username',$bidrows['project_user_id']) . '</a>';
			$bidrows['currbid'] = fetch_auction('currentprice', $bidrows['project_id']);
			$bidrows['city'] = ucfirst($bidrows['city']);
			$bidrows['state'] = ucfirst($bidrows['state']);
			$bidrows['zip'] = trim(mb_strtoupper($bidrows['zip_code']));
			$bidrows['location'] = $bidrows['state'] . ' &gt; ' . print_user_country($bidrows['user_id'], fetch_site_slng());
			$bidrows['title'] = fetch_auction('project_title', $bidrows['project_id']);
			if ($bidrows['bidstatus'] == 'awarded')
			{
				$bidrows['award'] = '[' . $phrase['_winner_lower'] . ']';
			}
			else
			{
				$bidrows['award'] = '[' . $bidrows['bidstatus'] . ']';
			}
			
			if ($bidrows['isproxybid'])
			{
				$bidrows['proxy'] = '<span style="color:#ff6600">[' . $phrase['_proxy_bid_lc'] . ']</span>';
				$proxysel = $ilance->db->query("SELECT maxamount FROM " . DB_PREFIX . "proxybid
												WHERE project_id = '".$bidrows['project_id']."'
												AND user_id = '".$bidrows['user_id']."' ");
				$resproxy = $ilance->db->fetch_array($proxysel);
				$bidrows['proxy'] = 'YES';
				$bidrows['bidamount'] = $ilance->currency->format($resproxy['maxamount'], $bidrows['currencyid']);
			}
			else
			{
				$bidrows['proxy'] = '-';
				$bidrows['bidamount'] = $ilance->currency->format($rowbeforeexchange, $bidrows['currencyid']);
			}
			$bidrows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$bidrows['delete'] = '<input type="checkbox" name="bidid[]" value="' . $bidrows['bid_id'] . '" />';
			$productbids[] = $bidrows;
			$row_count++;
		}
	}
	else
	{
		$show['no_productbids'] = true;
	}
	if (empty($ilance->GPC['project_id']))
	{
		$ilance->GPC['project_id'] = 0;
	}
	if (empty($ilance->GPC['user_id']))
	{
		$ilance->GPC['user_id'] = 0;
	}
	if (empty($ilance->GPC['bidstatus']))
	{
		$ilance->GPC['bidstatus'] = '';
	}
	if (empty($ilance->GPC['orderby']))
	{
		$ilance->GPC['orderby'] = 'DESC';
	}
	if (!isset($show['no_productbids']))
	{
		$productprevnext = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $ilpage['distribution'] . '?cmd=bids&amp;subcmd=' . $ilance->GPC['subcmd'] . '&amp;project_id='.$ilance->GPC['project_id'].'&amp;user_id='.$ilance->GPC['user_id'].'&amp;bidstatus='.$ilance->GPC['bidstatus'].'&amp;orderby='.$ilance->GPC['orderby'], 'page2');
	}
	
	// #### BID INCREMENT GROUPS ###################################
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "increments_groups
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			// fetch increment values in this group
			$sqlfees = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "increments
				WHERE groupname = '" . $row['groupname'] . "'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlfees) > 0)
			{
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlfees, DB_ASSOC))
				{
					$rows['from'] = $ilance->currency->format($rows['increment_from']);
					$rows['to'] = ($rows['increment_to'] != '-1') ? $ilance->currency->format($rows['increment_to']) : $phrase['_or_more'];
					$rows['amount'] = $ilance->currency->format($rows['amount']);
					$rows['actions'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-increment&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['incrementid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=_remove-increment&amp;groupid='.$row['groupid'].'&amp;id='.$rows['incrementid'].'" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$GLOBALS['increments' . $row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS['no_increments' . $row['groupid']][] = 1;	
			}
			
			$row['remove_group'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_remove-increment-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-increment-group&amp;groupid=' . $row['groupid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$row['groupcount'] = $ilance->admincp->fetch_increment_catcount($row['groupname']);
			$row['groupnameplain'] = $row['groupname'];
			$row['groupname'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-increment-group&amp;groupid=' . $row['groupid'] . '#editgroup">' . $row['groupname'] . '</a>';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$increment_groups[] = $row;
			$increment_groups2[] = $row;
			$row_count++;
		}
		
		$show['no_increment_groups'] = false;
	}
	else
	{
		$show['no_increment_groups'] = true;
	}
	
	// #### INSERT BID INCREMENT HANDLER ###########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-increment' AND isset($ilance->GPC['increment_from']) AND isset($ilance->GPC['increment_to']) AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$ilance->GPC['groupname'] = $ilance->db->fetch_field(DB_PREFIX . "increments_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
		$ilance->GPC['cid'] = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		
		$ilance->admincp->insert_bid_increment($ilance->GPC['increment_from'], $ilance->GPC['increment_to'], $ilance->GPC['amount'], $ilance->GPC['cid'], $ilance->GPC['sort'], $ilance->GPC['groupname']);
		
		print_action_success($phrase['_new_product_bid_increment_range_was_successfully_added'], $ilance->GPC['return']);
		exit();
	}
	
	// #### UPDATE CATEGORY INCREMENT ##############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-increment' AND isset($ilance->GPC['increment_from']) AND isset($ilance->GPC['increment_to']) AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$ilance->GPC['groupname'] = $ilance->db->fetch_field(DB_PREFIX . "increments_groups", "groupid = '".intval($ilance->GPC['groupid'])."'", "groupname");
		$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
		$ilance->admincp->update_bid_increment($ilance->GPC['id'], $ilance->GPC['increment_from'], $ilance->GPC['increment_to'], $ilance->GPC['amount'], 0, $ilance->GPC['sort'], $ilance->GPC['groupname']);
		
		print_action_success($phrase['_bid_increment_range_was_successfully_updated'], $ilance->GPC['return']);
		exit();
	}
	
	// #### REMOVE CATEGORY INCREMENT ##############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-increment' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
	{
		$ilance->admincp->remove_bid_increment($ilance->GPC['id']);
		
		print_action_success($phrase['_bid_increment_range_was_removed'], $ilpage['distribution'] . '?cmd=bids');
		exit();
	}
	
	// #### INSERT INCREMENT GROUP HANDLER #########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-increment-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
	{
		$ilance->admincp->insert_increment_group($ilance->GPC['groupname'], $ilance->GPC['description']);
		
		print_action_success($phrase['_new_product_bid_increment_group_created'], $ilpage['distribution'] . '?cmd=bids');
		exit();
	}
	
	// #### UPDATE INCREMENT GROUP HANDLER #########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-increment-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
	{
		$ilance->admincp->update_increment_group($ilance->GPC['groupid'], $ilance->GPC['groupname'], $ilance->GPC['description']);
		
		print_action_success($phrase['_bid_increment_group_was_updated'], $ilpage['distribution'] . '?cmd=bids');
		exit();
	}
	
	// #### REMOVE INCREMENT FEE GROUP HANDLER #####################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-increment-group' AND isset($ilance->GPC['groupid']))
	{
		$ilance->admincp->remove_increment_group($ilance->GPC['groupid']);
		
		print_action_success($phrase['_bid_increment_group_was_removed'], $ilpage['distribution'] . '?cmd=bids');
		exit();
	}
	
	// #### REMOVE INCREMENT HANDLER ###############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-increment' AND isset($ilance->GPC['groupid']) AND isset($ilance->GPC['id']))
	{
		$ilance->admincp->remove_bid_increment($ilance->GPC['id']);
		
		print_action_success($phrase['_bid_increment_range_was_removed'], $ilpage['distribution'] . '?cmd=bids');
		exit();
	}
	
	// #### CALLED WHEN ADMIN CLICKS EDIT INCREMENT FEE GROUP PENCIL ICON #####
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-increment-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "increments_groups
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
	    
		$incrementgroupname = $res['groupname'];
		$incrementgroupdescription = $res['description'];
		
		if ($show['ADMINCP_TEST_MODE'])
		{
			$submitincrement = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" disabled="disabled" />';
		}
		else
		{
			$submitincrement = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
		}
		
		$subcmdincrementgroup = '_update-increment-group';
		$hiddenincrementgroupid2 = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
	}
	else 
	{
		$incrementgroupname = $incrementgroupdescription = $hiddenincrementgroupid2 = '';
		if ($show['ADMINCP_TEST_MODE'])
		{
			$submitincrement = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" disabled="disabled" />';
		}
		else
		{
			$submitincrement = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
		}
		
		$subcmdincrementgroup = 'insert-increment-group';
	}
	
	// does admin edit specific increment?
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-increment' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$incrementgrouppulldown = $ilance->admincp->print_increment_group_pulldown(intval($ilance->GPC['groupid']), 1, 'product');
		
		$sqlincrements = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "increments
			WHERE incrementid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sqlincrements) > 0)
		{
			while ($rows = $ilance->db->fetch_array($sqlincrements))
			{	
				$incfrom = $rows['increment_from'];
				$incto = $rows['increment_to'];
				$incamount = $rows['amount'];
				$incsort = $rows['sort'];
				
				if ($show['ADMINCP_TEST_MODE'])
				{
					$incsubmit = '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" disabled="disabled" />';
				}
				else
				{
					$incsubmit = '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" />';
				}
				
				$incform = 'update-increment';
				$inchidden = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
			}
		}
	}
	else 
	{
		$incrementgrouppulldown = $ilance->admincp->print_increment_group_pulldown('', 1, 'product');
		$incfrom = $incto = $incamount = '0.00';
		$incsort = '10';
		
		if ($show['ADMINCP_TEST_MODE'])
		{
			$incsubmit = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" disabled="disabled" />';
		}
		else
		{
			$incsubmit = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
		}
		
		$incform = 'insert-increment';
		$inchidden = '';
	}
	
	// #### bid configuration ##############################################
	$configuration_productbid = $ilance->admincp->construct_admin_input('productbid', $ilpage['distribution'] . '?cmd=bids');
	
	$pprint_array = array('configuration_productbid','bid_id','user_id','project_id','incrementgrouppulldown','incsort','inchidden','incform','incsubmit','incamount','incfrom','incto','hiddenincrementgroupid2','incrementgroupname','incrementgroupdescription','submitincrement','subcmdincrementgroup','buildversion','ilanceversion','login_include_admin','checked_active','submit_field','hiddenfield','multiplechoicefield','sort','question','question_description','subcmd','field_inputtype_pulldown','projectid','serviceprevnext','productprevnext','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_bids_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'bids.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','servicebids','productbids','bidfields','servicelanguages','increment_groups2','increment_groups'));
	if (!isset($increment_groups))
	{
		$increment_groups = array();
	}
	@reset($increment_groups);
	while ($i = @each($increment_groups))
	{
		$ilance->template->parse_loop('main', 'increments' . $i['value']['groupid']);
	}
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
    
// #### AUCTIONS DISTRIBUTION ##########################################
else
{
	$area_title = $phrase['_auctions_distribution'];
	$page_title = SITE_NAME . ' - ' . $phrase['_auctions_distribution'];
	
	($apihook = $ilance->api('admincp_auction_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'], $_SESSION['ilancedata']['user']['slng']);
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'auctions' OR empty($ilance->GPC['cmd']))
	{
		if (isset($ilance->GPC['pagetype']))
		{
			$pagetype = $ilance->GPC['pagetype'];
			$page = intval($ilance->GPC['page']);
			$viewtype = $ilance->GPC['viewtype'];
		}
		
		// #### UPDATE AUCTION HANDLER #################################
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_update-auction')
		{
			$cid = intval($ilance->GPC['acpcid']);
			$visible = intval($ilance->GPC['visible']);
			
			$query = $ilance->db->query("
				SELECT cid, status
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
			");
			if ($ilance->db->num_rows($query) > 0)
			{
				$qres = $ilance->db->fetch_array($query, DB_ASSOC);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "',
					project_state = '" . $ilance->db->escape_string($ilance->GPC['project_state']) . "',
					project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "',
					cid = '" . intval($cid) . "',
					date_added = '" . $ilance->db->escape_string($ilance->GPC['date_added']) . "',
					date_starts = '" . $ilance->db->escape_string($ilance->GPC['date_starts']) . "',
					date_end = '" . $ilance->db->escape_string($ilance->GPC['date_end']) . "',
					visible = '" . $visible . "'
					WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
				");
					
				// is the admin changing the category for this listing?
				// if so, we must remove all answers based on this category..
				move_listing_category_from_to($ilance->GPC['project_id'], $qres['cid'], $cid, $ilance->GPC['project_state'], $qres['status'], $ilance->GPC['status']);
			}
			
			print_action_success($phrase['_listing_id_was_updated_no_email_was_dispatched_to_the_member'], $ilance->GPC['return']);
			exit();
		}
		
		// #### AUCTION MODERATION CONTROLS ############################
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'moderate-action')
		{
			// #### VALIDATE MULTIPLE AUCTIONS #####################
			if (isset($ilance->GPC['validate']))
			{
				// default email template to parse when sending out verified listing emails
				$emailtemplate = 'moderate_auction_verified';				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_moderate_action_validate_start')) ? eval($apihook) : false;
				
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, project_state, project_details, date_starts, date_end, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						// seconds that have past since the listing was posted
						$secondspast = $res['seconds'];
						// fetch the new future date end based on elapsed seconds
						$sqltime = $ilance->db->query("
							SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
						");
						$restime = $ilance->db->fetch_array($sqltime, DB_ASSOC);
						
						// new date end 
						$new_date_end = $restime['new_date_end'];
						$datenow = DATETIME24H;
						
						if ($res['project_details'] == 'realtime')
						{
							if ($datenow > $res['date_starts'])
							{
								$new_date_start = $datenow;
							}
							else
							{
								$new_date_start = $res['date_starts'];	
							}
						}
						else
						{
							$new_date_start = DATETIME24H;
						}
						
						// add seconds that have past back to the listings date_end
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET date_starts = '" . $ilance->db->escape_string($new_date_start) . "',
							date_end = '" . $ilance->db->escape_string($new_date_end) . "',
							visible = '1'
							WHERE project_id = '" . intval($value) . "'
						");
						
						($apihook = $ilance->api('admincp_moderate_action_validate_foreach')) ? eval($apihook) : false;
						
						// rebuild category count
						build_category_count($res['cid'], 'add', "admin validating moderated listing from admincp: adding increment count category id $res[cid]");
						
						// dispatch email to user and admin
						$ilance->email->mail = array(fetch_user('email', $res['user_id']), SITE_EMAIL);
						$ilance->email->slng = fetch_user_slng($res['user_id']);
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
							'{{new_date_end}}' => print_date($new_date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0),
							'{{new_date_start}}' => print_date($new_date_start, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0),
						));
						
						$ilance->email->send();                                                
						
						// #### REFERRAL SYSTEM TRACKER ############################
						update_referral_action('postauction', $res['user_id']);
					}
				}
				
				print_action_success($phrase['_the_selected_listings_have_been_verified_an_email_was_also_dispatched'], $ilance->GPC['return']);
				exit();
			}
			
			// #### REMOVE MULTIPLE AUCTIONS PENDING MODERATION
			else if (isset($ilance->GPC['remove']))
			{
				$ilance->email = construct_dm_object('email', $ilance);
				$emailnotice = '';
				
				($apihook = $ilance->api('admincp_moderate_action_remove_start')) ? eval($apihook) : false;
				
				if (isset($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
				{
					$count = 1;
					foreach ($ilance->GPC['project_id'] AS $value)
					{
						$sql = $ilance->db->query("
							SELECT user_id, cid, project_state, project_title
							FROM " . DB_PREFIX . "projects
							WHERE project_id = '" . intval($value) . "'
						");
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql, DB_ASSOC);
							
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_changelog WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "product_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_realtimebids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_uniquebids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE project_id = '" . intval($value) . "'");                                                                
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb_alerts WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE watching_project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_filter_auction_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "bid_fields_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_trackbacks WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_destinations WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_regions WHERE project_id = '" . intval($value) . "'");
							
							if ($res['project_state'] == 'product')
							{
								$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
							}
							else if ($res['project_state'] == 'service')
							{
								$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
							}
							
							$emailnotice .= "$count. #$value - $res[project_title] ($url)";
							
							($apihook = $ilance->api('admincp_moderate_action_remove_foreach')) ? eval($apihook) : false;
							
							$count++;
						}
					}
					
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					
					$ilance->email->get('moderate_auction_unverified');		
					$ilance->email->set(array(
						'{{listingsremoved}}' => $emailnotice,
					));
					
					$ilance->email->send();
				}
			}
		}
		
		// #### REGULAR AUCTION CONTROLS ###############################
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'auction-action')
		{
			// #### CLOSE MULTIPLE AUCTIONS ################
			if (isset($ilance->GPC['close']))
			{
				$emailtemplate = 'moderate_auction_closed';
				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_action_close_start')) ? eval($apihook) : false;
				
				$notice = '';
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'closed',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
						");
						
						if ($res['status'] == 'open')
						{
							build_category_count($res['cid'], 'subtract', "admin closing multiple listings from admincp: subtracting increment count category id $res[cid]");
						}
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						($apihook = $ilance->api('admincp_action_close_foreach')) ? eval($apihook) : false;
						
						$ilance->email->mail = array(fetch_user('email', $res['user_id']), SITE_EMAIL);
						$ilance->email->slng = fetch_user_slng($res['user_id']);
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
						));
						
						$ilance->email->send();
					}
				}
				
				print_action_success($phrase['_the_selected_listings_were_closed_early'], $ilance->GPC['return']);
				exit();
			}
			
			// #### DELIST MULTIPLE AUCTIONS ###############
			else if (isset($ilance->GPC['delist']))
			{
				$emailtemplate = 'moderate_auction_delist';
				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_action_delist_start')) ? eval($apihook) : false;
				
				$notice = '';
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						
						if ($res['status'] == 'open')
						{
							build_category_count($res['cid'], 'subtract', "admin delisting multiple listings from admincp: subtracting increment count category id $res[cid]");
						}
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'delisted',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
						");
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						($apihook = $ilance->api('admincp_action_delist_foreach')) ? eval($apihook) : false;
						
						$ilance->email->mail = array(fetch_user('email', $res['user_id']), SITE_EMAIL);
						$ilance->email->slng = fetch_user_slng($res['user_id']);
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
						));
						
						$ilance->email->send();
					}
				}
				
				print_action_success($phrase['_the_selected_listings_were_delisted_closed'], $ilance->GPC['return']);
				exit();
			}
			
			// #### ARCHIVE MULTIPLE AUCTIONS ##############
			else if (isset($ilance->GPC['archive']))
			{
				$emailtemplate = 'moderate_auction_archive';
				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_action_archive_start')) ? eval($apihook) : false;
				
				$notice = '';
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'archived',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
						");
						
						if ($res['status'] == 'open')
						{
							build_category_count($res['cid'], 'subtract', "admin archiving multiple listings from admincp: subtracting increment count category id $res[cid]");
						}
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						($apihook = $ilance->api('admincp_action_archive_foreach')) ? eval($apihook) : false;
						
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
						));
						
						$ilance->email->send();
					}
				}
				
				print_action_success($phrase['_the_selected_listings_were_archived'], $ilance->GPC['return']);
				exit();
			}
			
			// #### REMOVE MULTIPLE AUCTIONS ###############
			else if (isset($ilance->GPC['remove']))
			{
				$ilance->email = construct_dm_object('email', $ilance);
				$notice = $emailnotice = '';
				$count = 1;
				
				($apihook = $ilance->api('admincp_action_remove_start')) ? eval($apihook) : false;
				
				if (isset($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
				{
					foreach ($ilance->GPC['project_id'] AS $value)
					{
						$sql = $ilance->db->query("
							SELECT user_id, cid, status, project_state, project_title
							FROM " . DB_PREFIX . "projects
							WHERE project_id = '" . intval($value) . "'
						");
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql, DB_ASSOC);
							
							if ($res['status'] == 'open')
							{
								build_category_count($res['cid'], 'subtract', "admin removing multiple listings from admincp: subtracting increment count category id $res[cid]");
							}
							
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_changelog WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "product_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_realtimebids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_uniquebids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE project_id = '" . intval($value) . "'");                                                                
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb_alerts WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE watching_project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_filter_auction_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "bid_fields_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_trackbacks WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_destinations WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_regions WHERE project_id = '" . intval($value) . "'");
							
							if ($res['project_state'] == 'product')
							{
								$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
							}
							else if ($res['project_state'] == 'service')
							{
								$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
							}
							
							$emailnotice .= "$count. #$value - $res[project_title] ($url)";
							
							($apihook = $ilance->api('admincp_action_remove_foreach')) ? eval($apihook) : false;
							
							$count++;
						}
					}
					
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					
					$ilance->email->get('moderate_auction_unverified');		
					$ilance->email->set(array(
						'{{listingsremoved}}' => $emailnotice,
					));
					
					$ilance->email->send();
					
					$notice .= $phrase['_the_selected_listings_were_removed'];
					
					print_action_success($notice, $ilance->GPC['return']);
					exit();	
				}
			}
		}
		else if (!isset($ilance->GPC['subcmd']) OR isset($ilance->GPC['do']) AND $ilance->GPC['do'] != '_update-auction')
		{
			$show['update_auction'] = false;
			$show['no_update_auction'] = true;
			$dosql = $dosql2 = '';
			
			if (isset($ilance->GPC['viewtype']) AND $ilance->GPC['viewtype'] != '')
			{
				$viewtype = $ilance->GPC['viewtype'];
			}
			
			if (isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] > 0)
			{
				$pagetype = 'page3';
				$page = intval($ilance->GPC['page3']);
			}
			else if (isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] > 0)
			{
				$pagetype = 'page2';
				$page = intval($ilance->GPC['page2']);
			}
			else if (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 0)
			{
				$pagetype = 'page';
				$page = intval($ilance->GPC['page']);
			}
			else
			{
				$pagetype = 'page';
				$page = 1;
			}
	
			if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
			{
				$ilance->GPC['page3'] = 1;
			}
			else
			{
				$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
			}
	
			if (isset($ilance->GPC['orderby']) AND $ilance->GPC['orderby'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$ordersort = strip_tags($ilance->GPC['orderby']);
			}
			else
			{
				$ordersort = 'DESC';
			}
			$orderlimit3 = ' ORDER BY id '.$ordersort.' LIMIT '.(($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
	
			// filtering search via project id
			if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql = " AND project_id = '".intval($ilance->GPC['project_id'])."' ";
			}
			
			// filtering search via user id
			if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND user_id = '".intval($ilance->GPC['user_id'])."' ";
			}
			
			// filtering search via auction type
			if (isset($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND project_details = '".$ilance->db->escape_string($ilance->GPC['project_details'])."' ";
			}
			
			// filtering search via auction title
			if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
			}
			
			// filtering search via auction status
			if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND status = '".$ilance->db->escape_string($ilance->GPC['status'])."' ";
			}
			
			if (empty($dosql))
			{
				$dosql = '';
			}
			
			if (empty($ilance->GPC['status']))
			{
				$ilance->GPC['status'] = '';
			}
	
			$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '0' ".$dosql." ".$orderlimit3);
			$sql2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '0' ".$dosql);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$show['no_moderateauctions'] = false;
				
				$ilance->escrow = construct_object('api.escrow');
				
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sql))
				{
					if (isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] != '')
					{
						$res['pagetype'] = 'page3';
						$res['page'] = intval($ilance->GPC['page3']);
					}
					$res['project_title'] = stripslashes($res['project_title']);
					$res['r3'] = '<input type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
					$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
					$res['owner'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id='.$res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
					$res['category'] = '<strong>' . $ilance->categories->title(fetch_site_slng(), 'service', $res['cid']) . '</strong>';
					$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
					$res['escrow'] = $ilance->escrow->status($res['project_id']);
					$res['auctiontype'] = ucfirst($res['project_details']);
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$res['type'] = $res['project_state'];
					$res['insertionfee'] = ($res['insertionfee'] > 0)
						? $ilance->currency->format($res['insertionfee'])
						: '-';
						
					$moderateauctions[] = $res;
					$row_count++;
				}
				$numbermoderation = $ilance->db->num_rows($sql2);
			}
			else
			{
				$numbermoderation = 0;
				$show['no_moderateauctions'] = true;
			}
			
			$moderateprevnext = '';
			if ($show['no_moderateauctions'] == false)
			{
				$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : 0;
				$ilance->GPC['user_id'] = isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : 0;
				$ilance->GPC['project_details'] = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : '';
				$ilance->GPC['orderby'] = isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : '';
				
				$moderateprevnext = print_pagnation($numbermoderation, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=moderate&amp;project_id=".intval($ilance->GPC['project_id'])."&amp;user_id=".(int)$ilance->GPC['user_id']."&amp;project_details=".$ilance->GPC['project_details']."&amp;orderby=".$ilance->GPC['orderby'], 'page3');
			}
			
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
			{
				$ilance->GPC['page'] = 1;
			}
			else
			{
				$ilance->GPC['page'] = intval($ilance->GPC['page']);
			}
	
			// filtering search via ascending / decending
			$ordersort = 'DESC';
			if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']) AND $ilance->GPC['viewtype'] == 'service')
			{
				$ordersort = strip_tags($ilance->GPC['orderby']);
			}
			$orderlimit = ' ORDER BY id ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
			// filtering search via project id
			if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
			}
	
			// filtering search via user id
			if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
			}
	
			// filtering search via auction type
			if (isset($ilance->GPC['project_details']) AND !empty($ilance->GPC['project_details']) AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
				$ilance->GPC['auctiontype'] = $ilance->GPC['project_details'];
			}
			
			// filtering search via auction title
			if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
			}
	
			// filtering search via auction status
			if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']) AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			}
			
			if (!isset($dosql2))
			{
				$dosql2 = '';
			}
			
			if (empty($ilance->GPC['status']))
			{
				$ilance->GPC['status'] = '';
			}
	
			$sqlservice = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE visible = '1'
					AND project_state = 'service'
					$dosql2
					$orderlimit
			");
			
			$sqlservice2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE visible = '1'
					AND project_state = 'service'
					$dosql2
			");
			if ($ilance->db->num_rows($sqlservice) > 0)
			{
				$show['no_serviceauctions'] = false;
				
				$ilance->escrow = construct_object('api.escrow');
				
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sqlservice, DB_ASSOC))
				{
					$res['r1'] = '<input type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
					$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
					$res['owner'] = '<a href="'.$ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"">' . fetch_user('username', $res['user_id']) . '</a>';
					$res['project_title'] = stripslashes($res['project_title']);
					$res['awarded'] = $ilance->auction->fetch_auction_winner($res['project_id']);
					$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
					$res['escrow'] = $ilance->escrow->status($res['project_id']);                        
					$res['auctiontype'] = ucfirst($res['project_details']);
					
					if ($res['status'] == 'wait_approval')
					{
						$res['status'] = $phrase['_pending_acceptance'];
					}
					else if ($res['status'] == 'approval_accepted')
					{
						$res['status'] = $phrase['_accepted'];
					}
					else
					{
						$res['status'] = ucwords($res['status']);
					}
					
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$res['timeleft'] = $ilance->auction->auction_timeleft($res['project_id'], $res['class']);
					
					if ($res['insertionfee'] > 0)
					{
						$res['insertionfee'] = $ilance->currency->format($res['insertionfee']);
					}
					else
					{
						$res['insertionfee'] = '-';
					}
					if ($res['fvf'] > 0)
					{
						$res['fvf'] = $ilance->currency->format($res['fvf']);
					}
					else
					{
						$res['fvf'] = '-';
					}
					
					if ($res['bids'] == 0)
					{
						$res['bids'] = '-';
					}
					
					$serviceauctions[] = $res;
					$row_count++;
				}
				
				$numberservice = $ilance->db->num_rows($sqlservice2);
			}
			else
			{
				$numberservice = '0';
				$show['no_serviceauctions'] = true;
			}
			
			if ($show['no_serviceauctions'] == false)
			{
				if (!isset($ilance->GPC['project_id']))
				{
					$ilance->GPC['project_id'] = 0;
				}
				if (!isset($ilance->GPC['project_details']))
				{
					$ilance->GPC['project_details'] = '';
				}
				if (!isset($ilance->GPC['user_id']))
				{
					$ilance->GPC['user_id'] = 0;
				}
				if (!isset($ilance->GPC['orderby']))
				{
					$ilance->GPC['orderby'] = '';
				}
				$serviceprevnext = print_pagnation($numberservice, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=service&amp;project_id=".intval($ilance->GPC['project_id'])."&amp;user_id=".intval($ilance->GPC['user_id'])."&amp;project_details=".$ilance->GPC['project_details']."&amp;orderby=".$ilance->GPC['orderby']."&amp;status=" . $ilance->GPC['status'] . "");
			}
			else
			{
				$serviceprevnext = '';
			}
			
			if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
			{
				$ilance->GPC['page2'] = 1;
			}
			else
			{
				$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
			}
	
			// filtering search via ascending / descending
			$ordersort = 'DESC';
			if (isset($ilance->GPC['orderby']) AND $ilance->GPC['orderby'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$ordersort = strip_tags($ilance->GPC['orderby']);
			}
			$orderlimit = ' ORDER BY id ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
			$dosql3 = '';
			
			// filtering search via project id
			if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
			}
	
			// filtering search via user id
			if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
			}
	
			// filtering search via auction type
			if (isset($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
			}
			
			// filtering by product type regular/fixed
			if (isset($ilance->GPC['project_details2']) AND $ilance->GPC['project_details2'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND filtered_auctiontype = '" . $ilance->db->escape_string($ilance->GPC['project_details2']) . "' ";
			}
			
			// filtering search via auction title
			if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
			}
	
			// filtering search via auction status
			if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			}
			
			if (!isset($dosql3))
			{
				$dosql3 = '';
			}
	
			$sqlproduct = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '1' AND project_state = 'product' ".$dosql3." ".$orderlimit);
			$sqlproduct2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '1' AND project_state = 'product' ".$dosql3);
			if ($ilance->db->num_rows($sqlproduct) > 0)
			{
				$show['no_productauctions'] = false;
				
				$ilance->escrow = construct_object('api.escrow');
				
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sqlproduct))
				{
					$res['r2'] = '<input type="checkbox" name="project_id[]" value="'.$res['project_id'].'" />';
					$res['merchant'] = '<a href="'.$ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.$res['user_id'].'"">'.fetch_user('username', $res['user_id']).'</a>';
					$res['status'] = ucfirst($res['status']);
					$res['project_title'] = stripslashes($res['project_title']);
					$res['winner'] = $ilance->auction->fetch_auction_winner($res['project_id']);
					//$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
					$sold = fetch_buynow_ordercount($res['project_id']);
					if ($sold > 0)
					{
						$res['sales'] = $sold;
					}
					else
					{
						$res['sales'] = '-';
					}
					$res['escrow'] = $ilance->escrow->status($res['project_id']);
					$res['auctiontype'] = ucfirst($res['project_details']);
					$res['auctiontype2'] = ucfirst($res['filtered_auctiontype']);
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$res['timeleft'] = $ilance->auction->auction_timeleft($res['project_id'], $res['class']);
					if ($res['insertionfee'] > 0)
					{
						$res['insertionfee'] = $ilance->currency->format($res['insertionfee']);
					}
					else
					{
						$res['insertionfee'] = '-';
					}
					if ($res['fvf'] > 0)
					{
						$res['fvf'] = $ilance->currency->format($res['fvf']);
					}
					else
					{
						$res['fvf'] = '-';
					}
					if ($res['bids'] == 0)
					{
						$res['bids'] = '-';
					}
					$productauctions[] = $res;
					$row_count++;
				}
				$numberproduct = $ilance->db->num_rows($sqlproduct2);
			}
			else
			{
				$numberproduct = 0;
				$show['no_productauctions'] = true;
			}
	
			$productprevnext = '';
			if ($show['no_productauctions'] == false)
			{
				$productprevnext = print_pagnation($numberproduct, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], ($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=product&amp;project_id=" . (isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : 0) . "&amp;user_id=" . (isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : 0) . "&amp;project_details=".(isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : '') . "&amp;orderby=" . (isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : '') . "&amp;status=" . $ilance->GPC['status'] . "", 'page2');
			}
				
			$ilance->GPC['auctiontype'] = isset($ilance->GPC['auctiontype']) ? $ilance->GPC['auctiontype'] : '';
			$ilance->GPC['project_details2'] = isset($ilance->GPC['project_details2']) ? $ilance->GPC['project_details2'] : 'regular';
			
			$auction_type_pulldown = $ilance->admincp->auction_details_pulldown($ilance->GPC['auctiontype'], 1, 'service');
			$auction_type_pulldown2 = $ilance->admincp->auction_details_pulldown($ilance->GPC['auctiontype'], 1, 'product');
			$auctiontype_pulldown2 = $ilance->admincp->auction_details_pulldown2($ilance->GPC['project_details2'], 1, 'product');
			
			// auction status pulldown
			$ilance->GPC['status'] = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : 0;
			$auction_status_pulldown = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], 1, 'service');
			$auction_status_pulldown2 = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], 1, 'product');
		}
		else
		{
			header("Location: " . HTTPS_SERVER . (($ilance->GPC['viewtype'] == 'service') ? $ilpage['buying'] . "?cmd=rfp-management&id=" . intval($ilance->GPC['id']) . "&admincp=1" : $ilpage['selling'] . "?cmd=product-management&id=" . intval($ilance->GPC['id']) . "&admincp=1"));
			exit();
		}
	}
	$id = 0;
	if (isset($ilance->GPC['id']))
	{
		$id = intval($ilance->GPC['id']);
	}
	
	$project_id = '';
	if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
	{
		$project_id = intval($ilance->GPC['project_id']);
	}
	
	$user_id = '';
	if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
	{
		$user_id = intval($ilance->GPC['user_id']);
	}
	
	$project_title = '';
	if (isset($ilance->GPC['project_title']) AND !empty($ilance->GPC['project_title']))
	{
		$project_title = $ilance->GPC['project_title'];
	}
	
	// #### AUCTION SETTINGS TAB ###################################
	$global_auctionoptions = $ilance->admincp->construct_admin_input('globalauctionsettings', $ilpage['distribution']);
	
	$pprint_array = array('auctiontype_pulldown2','project_title','user_id','project_id','buildversion','ilanceversion','login_include_admin','auction_status_pulldown2','auction_type_pulldown2','wysiwyg_area','global_auctionoptions','configuration_moderationsystem','project_questions','auction_status_pulldown','productprevnext','numberproduct','pagetype','page','viewtype','id','bidapplet','auction_type_pulldown','numbermoderation','serviceprevnext','moderateprevnext','numberservice','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_auctions_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'auctions.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','bid_results_rows','serviceescrows','productescrows','moderateauctions','serviceauctions','productauctions','updateserviceauction'));
	if (!isset($updateserviceauction))
	{
		$updateserviceauction = array();
	}
	@reset($updateserviceauction);
	while ($i = @each($updateserviceauction))
	{
		$ilance->template->parse_loop('main', 'purchase_now_activity' . $i['value']['project_id']);
	}
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>