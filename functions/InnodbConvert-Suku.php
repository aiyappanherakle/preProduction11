<?php
require_once('./config.php');

error_reporting(E_ALL);
ini_set('display_errors', 'stdout');
restore_error_handler();
mysqli_report(MYSQLI_REPORT_ERROR);  // MYSQLI_REPORT_STRICT is a little too strict for iLance, MYSQLI_REPORT_ALL also fails on warnings, like that an index isn't available to optimize a query

// $patch_file="../cache/InnodbConvert-Suku.php"; unlink($patch_file);
echo '<pre>';
$sql="SHOW INDEX from ".DB_PREFIX."categories where key_name='sx_categories_sets'";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
	while($line= $ilance->db->fetch_array($result))
	{
		add_patch_sql("ALTER TABLE `".DB_PREFIX."categories` DROP INDEX sx_categories_sets;");
	}
}

$sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE engine = 'MyISAM' and TABLE_SCHEMA='".DATABASE."'";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
	$sql='';
	while($line= $ilance->db->fetch_array($result))
	{
		// echo '<pre>'; print_r($line); '</pre>'; exit;
		$sql="ALTER TABLE `".$line['TABLE_NAME']."` ENGINE=INNODB".";";		// why was this: $line['Tables_in_gc_inno']
		add_patch_sql($sql);
	}
	
}
add_patch_sql("ALTER TABLE `".DB_PREFIX."projects` ENABLE KEYS;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."projects` ADD FULLTEXT (`project_title`);");
add_patch_sql("ALTER TABLE `".DB_PREFIX."projects` ADD FULLTEXT (`description`);");
add_patch_sql("ALTER TABLE `".DB_PREFIX."projects` ADD FULLTEXT `p.project_title,p.description` (`project_title`, `description`);");
add_patch_sql("ALTER TABLE `".DB_PREFIX."projects` CHANGE `hotlists` `hotlists` INT( 1 ) NOT NULL DEFAULT '0';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."coins` CHANGE `ebay_Categories_id` `ebay_Categories_id` INT( 11 ) NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."coins` CHANGE `ebay_Categories_id` `ebay_Categories_id` INT( 11 ) NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."coins` CHANGE `ebay_subtitle` `ebay_subtitle` VARCHAR( 55 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."coin_relist` CHANGE `daily_deal_enddate` `daily_deal_enddate` DATETIME NULL DEFAULT '0000-00-00 00:00:00' ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."invoices` CHANGE `miscamount` `miscamount` FLOAT( 10, 2 ) NOT NULL DEFAULT '0';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."invoices` CHANGE `scheduled_date` `scheduled_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."invoices` CHANGE `misc_date` `misc_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."invoices` CHANGE `statement_date` `statement_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."invoices` CHANGE `combine_project` `combine_project` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."dailydeal` CHANGE `project_id` `project_id` INT( 11 ) NOT NULL DEFAULT '0';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."daily_report_split` CHANGE `total_payments` `total_payments` FLOAT( 10, 2 ) NOT NULL DEFAULT '0', CHANGE `total_amount` `total_amount` FLOAT( 10, 2 ) NULL DEFAULT '0', CHANGE `total_shipping_cost` `total_shipping_cost` FLOAT( 10, 2 ) NOT NULL DEFAULT '0';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."sessions` CHANGE `url` `url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."sessions` CHANGE `title` `title` VARCHAR( 1000 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."users` CHANGE `Check_Payable` `Check_Payable` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."users` CHANGE `bidslifetime` `bidslifetime` INT( 10 ) NOT NULL DEFAULT '0';");
add_patch_sql("ALTER TABLE `".DB_PREFIX."users` CHANGE `referal_id` `referal_id` INT( 11 ) NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."users` CHANGE `access_bb` `access_bb` INT( 1 ) NULL DEFAULT NULL , CHANGE `enable_batch_bid` `enable_batch_bid` INT( 1 ) NULL DEFAULT NULL , CHANGE `is_auto_lower_min_bid` `is_auto_lower_min_bid` BINARY( 1 ) NULL DEFAULT NULL ,CHANGE `auto_min_bid_lower_prec` `auto_min_bid_lower_prec` INT( 2 ) NULL DEFAULT NULL ;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."invoices` CHANGE `combine_project` `combine_project` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."projects` ADD INDEX `archive_coins` (`project_id`, `haswinner`, `status`, `coin_series_denomination_no`) USING BTREE;");
add_patch_sql("ALTER TABLE `".DB_PREFIX."project_bids` ADD INDEX (`user_id`, `project_id`);");

echo 'Execute:  mysql --user=root --password=PPP gc_ilance < ToInnoDB.sql';

function add_patch_sql($sql)
{
	echo $sql.'<br />';
	flush();
	set_time_limit(300);
	global $ilance,$patch_file;
	// $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	/*
	$fp=fopen($patch_file, "a+");
	fwrite($fp, $sql."\n");
	fclose($fp);*/
}
?>
