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
	'administration'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

require_once(DIR_CORE . 'functions_attachment.php');
$ilance->cache = new ilance_memcached();
//print_r($_SESSION);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
  $ilance->subscription = construct_object('api.subscription');

error_reporting(E_ALL);
ini_set('display_errors', 'stdout');
restore_error_handler();
mysqli_report(MYSQLI_REPORT_STRICT);

define('DEFER_UPDATE',True);		// if updates should be deferred
$destCCtable = 'mark_catalog_coin';	// destination TEST table
// $destCCtable = DB_PREFIX.'catalog_coin';	// destination ACTUAL table

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	// if logged-in as admin
	switch (@$_REQUEST['mode'])	{
	case 'sort':
		?>
		<html>
			<head>
				<title>PCGS Merge processing</title>
				<style type="text/css">
				</style>
			</head>
		<body>
		Assign GC sort to PCGS table.<br />
		Clearing GC sort field... 
		<?php
		flush();
		$sql = "UPDATE mark_from_pcgs SET mp_gcsort='';";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);
		unset($sql_results);
		?>
		Done.<br />
		Setting hingepoints on GC sort field... 
		<?php
		flush();
		$sql = "UPDATE mark_from_pcgs SET mp_gcsort = (SELECT LPAD(CAST(Orderno AS CHAR(8)), 8, '0') FROM ".DB_PREFIX."catalog_coin WHERE ".DB_PREFIX."catalog_coin.PCGS=mp_SpecNo) WHERE EXISTS (SELECT Orderno FROM ".DB_PREFIX."catalog_coin WHERE ".DB_PREFIX."catalog_coin.PCGS=mp_SpecNo);";
		// echo '<br />'.$sql; exit;
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);
		// With decimal points: LPAD(CAST(CAST(Orderno AS DECIMAL(20,5)) AS VARCHAR, 20, '0')
		unset($sql_results);
		?>
		Done.<br />
		Looping through entire PCGS import, setting new items based on hingepoint... 
		<?php
		flush();
		$sql = "SELECT mp_id, mp_SortOrder, mp_gcsort FROM mark_from_pcgs ORDER BY mp_SortOrder, mp_gcsort;";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);
			// get all iLance-existing PCGS numbers
		$hinge = '00000000';
		$sqls = array();					// container for deferred updates
		while ($row = $ilance->db->fetch_array($sql_results))	// for each record found in database
		{
			if ($row['mp_gcsort'])								// if a new hingepin
			{
				$hinge = $row['mp_gcsort'];						// remember it
			} else {											// if not a hingpin, fill empty with hingepin combined with PCGS sort order
				$sql = "UPDATE mark_from_pcgs SET mp_gcsort='".$ilance->db->escape_string($hinge.'.'.sprintf('%015.5f',$row['mp_SortOrder']))."' WHERE mp_id=".$row['mp_id'].";";
				if (DEFER_UPDATE)
					array_push($sqls, $sql);
				else {
					$sql_holefill_results = mysqli_query($ilance->db->connection_link,$sql);
					// $sql_holefill_results = $ilance->db->query($sql);
					unset($sql_holefill_results);
				}
			}
			set_time_limit(30);		// extend execution time
		}
		?>
		Done.<br />
		<?php
		unset($sql_results);		// close database recordset
		if (DEFER_UPDATE)			// if we're in deferred update mode
		{
			?>
			Looping through <?php echo count($sqls); ?> deferred UPDATEs... 
			<?php
			flush();
			foreach ($sqls as $sql)	// loop through deferred SQL commands
			{
				$sql_holefill_results = mysqli_query($ilance->db->connection_link,$sql);
				// $sql_holefill_results = $ilance->db->query($sql);
				unset($sql_holefill_results);
				set_time_limit(30);		// extend execution time
			}
		}
		?>
		Done.<br />
		Looping through similar items, re-ordering certain suffices... 
		<?php
		flush();
		$sql = "
SELECT mp_YearIssued, mp_Denomination, mp_MajorVariety, mp_DieVariety, mp_Heading, mp_Prefix, COUNT( mp_id ) AS qty, GROUP_CONCAT( mp_id, ',', mp_Suffix, ',', mp_gcsort
ORDER BY IF(mp_Suffix='',99,LOCATE(mp_Suffix,'DC,CA,RD,RB,BN,FB,FH,FL,FS'))
SEPARATOR ';' ) AS variations
FROM mark_from_pcgs
WHERE mp_Suffix
IN (
'','DC','CA','RD','RB','BN','FB','FH','FL','FS'
)
GROUP BY mp_YearIssued, mp_Denomination, mp_MajorVariety, mp_DieVariety, mp_Heading, mp_Prefix
HAVING qty >1
ORDER BY mp_SortOrder, mp_gcsort;
";
	
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);
			// get all iLance-existing PCGS numbers
		$sqls = array();					// container for deferred updates
		while ($row = $ilance->db->fetch_array($sql_results))	// for each mult-toned record found in database
		{
			// echo $row['variations']."<BR>\n";
			$variations = preg_split("#;#",$row['variations']);	// get an array of matching rows
			if (count($variations) < 9)	// only if there aren't too many
			{
				foreach ($variations as $idx => $variation)			// loop through variations, note that they're already in re-sort order due to GROUP_CONCAT(ORDER BY)
				{
					// echo " ".$variation."<BR>\n";
					list($mp_id, $mp_Suffix, $mp_gcsort) = preg_split("#,#", $variation);	// split out fields
					if ($idx == 0)					// if it's the first one
					{
						$mp_gcsort1 = $mp_gcsort;	// remember its sort order
					} else {						// only update if it's not the first one
						$sql = "UPDATE mark_from_pcgs SET mp_gcsort='".$mp_gcsort1.'.'.$idx."' WHERE mp_id=".$mp_id.";";
							// use first variation's sort order as base, append ordering suffix of index
						if (DEFER_UPDATE)
							array_push($sqls, $sql);
						else {
							$sql_results2 = mysqli_query($ilance->db->connection_link,$sql);
							// $sql_results2 = $ilance->db->query($sql);
							unset($sql_results2);
						}
					}
				}
			}
			set_time_limit(30);		// extend execution time
		}
		?>
		Done.<br />
		<?php
		unset($sql_results);		// close database recordset
		if (DEFER_UPDATE)			// if we're in deferred update mode
		{
			?>
			Looping through <?php echo count($sqls); ?> deferred UPDATEs... 
			<?php
			flush();
			foreach ($sqls as $sql)	// loop through deferred SQL commands
			{
				// echo $sql."<BR />\n";
				$sql_results = mysqli_query($ilance->db->connection_link,$sql);
				// $sql_results = $ilance->db->query($sql);
				unset($sql_results);
				set_time_limit(30);		// extend execution time
			}
		}
		?>
		<strong>All tasks complete.</strong><br />
		</body>
		</html>
		<?php
		break;
	case 'output';
		$sql = 
"(SELECT 
'GC' AS 'Source',
CONCAT(LPAD(CAST(Orderno AS CHAR(8)), 8, '0'),'.000000000.00000') AS Orderno,
PCGS,
(SELECT coin_series_name FROM ilance_catalog_second_level WHERE ilance_catalog_second_level.coin_series_unique_no=ilance_catalog_coin.coin_series_unique_no LIMIT 1) AS unique_no,
(SELECT denomination_short FROM ilance_catalog_toplevel WHERE denomination_unique_no=coin_series_denomination_no LIMIT 1) AS denomination_no,
coin_detail_year AS year,
coin_detail_mintmark AS mintmark,
coin_detail_coin_series AS coin_series,
coin_detail_denom_long AS denom_long,
coin_detail_denom_short AS denom_short,
coin_detail_proof AS proof,
coin_detail_suffix AS suffix,
coin_detail_major_variety AS major_variety,
coin_detail_die_variety AS die_variety,
coin_detail_key_date AS key_date,
coin_detail_mintage AS mintage,
coin_detail_low_mintage AS low_mintage,
coin_detail_weight AS weight,
coin_detail_composition AS composition,
coin_detail_diameter AS diameter,
coin_detail_designer AS designer,
coin_detail_description_long AS description_long,
coin_detail_description_short AS description_short,
coin_detail_notes AS notes,
coin_detail_ngc_no AS ngc_no,
coin_detail_ebay_heading AS ebay_heading,
coin_detail_ebay_category AS ebay_category,
coin_detail_related_coins AS related_coins,
coin_detail_meta_description AS meta_description,
coin_detail_meta_title AS meta_title,
coin_detail_image AS image,
coin_detail_image_alt AS image_alt,
coin_detail_sort AS sort,
coin_detail_coin_series_no AS coin_series_no,
nmcode
FROM ilance_catalog_coin)

UNION

(SELECT 
'' AS 'Source',
mp_gcsort AS Orderno,
mp_SpecNo AS PCGS,
'' AS unique_no,
LOWER(mp_Denomination) AS denomination_no,
mp_YearIssued AS year,
'' AS mintmark,
mp_Heading AS coin_series,
'' AS denom_long,
LOWER(mp_Denomination) AS denom_short,
IF(mp_Prefix='PR' OR mp_Suffix IN ('DC','DM'),'y','') AS proof,
mp_Suffix AS suffix,
mp_MajorVariety AS major_variety,
mp_DieVariety AS die_variety,
'' AS key_date,
'' AS mintage,
'' AS low_mintage,
'' AS weight,
'' AS composition,
'' AS diameter,
'' AS designer,
'' AS description_long,
'' AS description_short,
mp_CoinDescription AS notes,
'' AS ngc_no,
'' AS ebay_heading,
'' AS ebay_category,
'' AS related_coins,
'' AS meta_description,
'' AS meta_title,
'' AS image,
'' AS image_alt,
'' AS sort,
'' AS coin_series_no,
'' AS nmcode
FROM mark_from_pcgs WHERE NOT EXISTS (SELECT PCGS FROM ilance_catalog_coin WHERE ilance_catalog_coin.PCGS = mp_SpecNo))
ORDER BY Orderno;";

		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);
		// header('Content-type: text/csv');
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename=pcgs_merge.xls');
		header('Pragma: no-cache');
		header('Expires: 0');
		$finfo = $sql_results->fetch_fields();
		$fields = array();
		foreach ($finfo as $idx => $val)
		{
			array_push($fields, $val->name);
			echo $val->name;
			if ($idx+1 < count($finfo))					// delimiter for all but the last field
				echo "\t";
			/*
			printf("Name:      %s\n",   $val->name);
			printf("Table:     %s\n",   $val->table);
			printf("Max. Len:  %d\n",   $val->max_length);
			printf("Length:    %d\n",   $val->length);
			printf("charsetnr: %d\n",   $val->charsetnr);
			printf("Flags:     %d\n",   $val->flags);
			printf("Type:      %d\n\n", $val->type);
			*/
		}
		echo "\n";
		$lastPCGS = 0;	// init last PCGS #
		while ($row = $ilance->db->fetch_array($sql_results))	// for each record found in database
		{
			if ($lastPCGS == $row['PCGS'])						// if the last PCGS is the same as the current
				continue;										// skip this record, using a cheap and dirty dedup (assumes consecutive)
			$lastPCGS = $row['PCGS'];							// remember last PCGS
			foreach ($fields as $idx => $field)					// loop through all fields
			{
				if ($field=='key_date' && $row[$field] == 0)
					echo '';
				elseif ($field=='suffix' && $row[$field] == 'CA')
					echo 'CAMEO';
				elseif ($field=='suffix' && $row[$field] == 'DC')
					echo 'DCAM';
				elseif ($field=='suffix' && $row[$field] == 'DM')
					echo 'DMPL';
				else
					echo $row[$field];
				if ($idx+1 < count($fields))					// delimiter for all but the last field
					echo "\t";
			}
			echo "\n";
			set_time_limit(30);		// extend execution time
		}
		exit;
	case 'ian_import_form':
		?>
		<html>
			<head>
				<title>Ian's spreadsheet import</title>
				<style type="text/css">
				</style>
			</head>
		<body>
		Copy and paste entire spreadsheet from Ian:<br />
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method=POST>
			<input type="hidden" name="source" value="field" />
			<input type="hidden" name="mode" value="ian_import_post" />
			<textarea name="data" rows="10" cols="50"></textarea><br />
			<input type="submit" name="submit" value="Submit" />
		</form>
		<br />
		... or...<br />
		<br />
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method=GET>
			<input type="hidden" name="source" value="file" />
			<input type="hidden" name="mode" value="ian_import_post" />
			Enter location of spreadsheet file from Ian:
			<input type="text" name="file" value="/home/gc/public_html/staff/pcgs_merge4.txt" />
			<input type="submit" name="submit" value="Submit" />
		</form>
		</body>
		</html>
		<?php
		break;
	case 'ian_import_post':
		?>
		<html>
			<head>
				<title>Ian's spreadsheet importing</title>
				<style type="text/css">
				</style>
			</head>
		<body>
		<?php
		flush();
		$errCnt = 0;	// init count of errors
		if ($_REQUEST['source'] == 'file')
		{
			?>
			Importing spreadsheet file at "<?php echo $_REQUEST['file']; ?>".<br />
			<?php
			$data = file_get_contents($_REQUEST['file']);
		} else {
			?>
			Importing pasted spreadsheet.<br />
			<?php
			$data = trim($_POST['data']);
		}
		$data = preg_split("#\r?\n#",$data);	// split on line termination
		if (count($data) <= 1)
		{
			?>
			<div style="color: red;">Only found <?php echo count($data); ?> line(s).</div>
			<?php
			$errCnt++;
		} else {
			?>
			Truncating table "mark_pcgs_to"...
			<?php
			flush();
			$sql = "TRUNCATE TABLE mark_pcgs_to;";
			$sql_results = mysqli_query($ilance->db->connection_link,$sql);
			// $sql_results = $ilance->db->query("TRUNCATE TABLE mark_pcgs_to;");
			unset($sql_results);
			?>
			Done.<br />
			<?php
			flush();
			$expectedHeader = 'Source	Orderno	PCGS	unique_no	denomination_no	year	mintmark	coin_series	denom_long	denom_short	proof	suffix	major_variety	die_variety	key_date	mintage	low_mintage	weight	composition	diameter	designer	description_long	description_short	notes	ngc_no	ebay_heading	ebay_category	related_coins	meta_description	meta_title	image	image_alt	sort	coin_series_no	nmcode';
			$inputFieldCnt = count(preg_split("#\t#",$expectedHeader));	// get number of fields in import
			foreach ($data as $lineIdx=>$line)
			{
				if ($lineIdx == 0)
				{
					?>
					Checking first header line...
					<?php
					flush();
					if ($line != $expectedHeader)
					{
						?>
						<div style="color: red;">First, header line was not as expected. Found:<br /> <?php echo $line; ?><br />Expected:<br /> <?php echo $expectedHeader; ?></div>
						<?php
						$errCnt++;
						break;
					}
				} else {
					$line = preg_split("#\t#",$line);	// split on field separator
					$sql = "INSERT INTO mark_pcgs_to VALUES (0, ";	// init insert statement, including autoincrement "id" field
					foreach ($line as $fieldIdx=>$field)	// for each field
					{
						if ($fieldIdx != 0)				// skip "Source" field
							$sql .= "'".$ilance->db->escape_string($field)."', ";		// append value to SQL INSERT statement
					}
					while (++$fieldIdx < $inputFieldCnt)	// if not quite enough fields (sometimes Excel or copy/paste or POST trims the end of the line)
						$sql .= "'', ";						// append empty field value until we get the right field count
					$sql = substr($sql, 0, -2).");";			// lopping off last ", " and close SQL INSERT statement
					// echo "<br />".$sql;
					try {
						$sql_results = mysqli_query($ilance->db->connection_link,$sql);
						// throw new Exception('Simulate MySQL error');
						unset($sql_results);
					} catch (Exception $e) {
						echo '<div style="color: red;">Import insert error: ',$e->getMessage(),'</div>';
						$errCnt++;
					}
					echo '.';
					if (($lineIdx % 200) == 0)
						echo '<br />';
					flush();
				}
				set_time_limit(30);		// extend execution time after each row updated
			}
		}
		if ($errCnt)	// if errors
		{
			?>
			<br />
			<strong style="color: red;"><?php echo $errCnt; ?> error(s) found in import of spreadsheet.</strong><br />
			<?php
		} else {	// if no errors
			?>
			<br />
			<strong>Import of spreadsheet complete.</strong><br />
			<?php
		}
		?>
		</body>
		</html>
		<?php
		break;
	case 'ian_import_check':
		?>
		<html>
			<head>
				<title>Ian's spreadsheet error check</title>
				<style type="text/css">
				</style>
			</head>
		<body>
		Checking imported spreadsheet.<br />
		Checking against current "catalog_coin" for missing PCGS numbers...
		<?php
		flush();
		// echo '<PRE>'; print_r($ilance->db); echo '</PRE>'; exit;
		$sql = "SELECT PCGS FROM ".DB_PREFIX."catalog_coin AS cc WHERE NOT EXISTS(SELECT id FROM mark_pcgs_to AS mp WHERE mp.PCGS=cc.PCGS);";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);

		if (!mysqli_num_rows($sql_results))						// if no rows returned
		{
			?>
			<span style="color: green;">None missing</span><br />
			<?php
		} else {
			?>
			<span style="color: red;">PCGS number(s) missing:</span><br />
			<?php
			while ($row = $ilance->db->fetch_array($sql_results))	// for each missing PCGS
			{
				echo $row['PCGS'].' ';
				flush();
			}
			echo '<br />';
		}
		unset($sql_results);
		?>
		Checking for unknown top level denomination categories...
		<?php
		flush();
		$sql = "SELECT PCGS, coin_series_denomination_no FROM mark_pcgs_to AS mp WHERE NOT EXISTS(SELECT id FROM ".DB_PREFIX."catalog_toplevel AS c1 WHERE c1.denomination_short=mp.coin_series_denomination_no) ORDER BY id;";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);

		if (!mysqli_num_rows($sql_results))						// if no rows returned
		{
			?>
			<span style="color: green;">All okay.</span><br />
			<?php
		} else {
			?>
			<span style="color: red;">These top level denomination(s) not found (by PCGS #):</span><br />
			<?php
			while ($row = $ilance->db->fetch_array($sql_results))	// for each missing PCGS
			{
				echo $row['PCGS'].' -> '.$row['coin_series_denomination_no'].'<br />';
				flush();
			}
		}
		unset($sql_results);
		?>
		Checking for unknown second level coin series categories...
		<?php
		flush();
		$sql = "SELECT PCGS, coin_series_unique_no FROM mark_pcgs_to AS mp WHERE NOT EXISTS(SELECT id FROM ".DB_PREFIX."catalog_second_level AS c2 WHERE c2.coin_series_name=mp.coin_series_unique_no) ORDER BY id;";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);

		if (!mysqli_num_rows($sql_results))						// if no rows returned
		{
			?>
			<span style="color: green;">All okay.</span><br />
			<?php
		} else {
			?>
			<span style="color: red;">These second level coin series not found (by PCGS #):</span><br />
			<?php
			while ($row = $ilance->db->fetch_array($sql_results))	// for each missing PCGS
			{
				echo $row['PCGS'].' -> '.$row['coin_series_unique_no'].'<br />';
				flush();
			}
		}
		unset($sql_results);
		?>
		Checking for mismatched second level coin series categories...
		<?php
		flush();
		$sql = "SELECT * FROM mark_pcgs_to AS mp LEFT JOIN ilance_catalog_second_level AS c2 ON (SELECT c2.coin_series_unique_no FROM ilance_catalog_second_level AS c2 WHERE c2.coin_series_name=mp.coin_series_unique_no)=mp.coin_series_unique_no WHERE c2.coin_series_denomination_no<>mp.coin_series_denomination_no ORDER BY mp.id;";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);

		if (!mysqli_num_rows($sql_results))						// if no rows returned
		{
			?>
			<span style="color: green;">All okay.</span><br />
			<?php
		} else {
			?>
			<span style="color: red;">These second level coin series not found (by PCGS #):</span><br />
			<?php
			while ($row = $ilance->db->fetch_array($sql_results))	// for each missing PCGS
			{
				echo $row['PCGS'].' -> '.$row['coin_series_unique_no'].'<br />';
				flush();
			}
		}
		unset($sql_results);
		?>
		Checking for certain changed fields in original "catalog_coin" table...
		<?php
		flush();
		// select fields to check between incoming catalog items numbers and existing ones
		$compFields = array(
			'coin_detail_year',
			'coin_detail_mintmark',
			'coin_detail_coin_series',
			'coin_detail_denom_long',
			'coin_detail_denom_short',
			'coin_detail_proof',
			'coin_detail_suffix',
			'coin_detail_major_variety',
			'coin_detail_die_variety'
		);
		// ALL mark_pcgs_to FIELDS: id 	Orderno 	PCGS 	coin_series_unique_no 	coin_series_denomination_no 	coin_detail_year 	coin_detail_mintmark 	coin_detail_coin_series 	coin_detail_denom_long 	coin_detail_denom_short 	coin_detail_proof 	coin_detail_suffix 	coin_detail_major_variety 	coin_detail_die_variety 	coin_detail_key_date 	coin_detail_mintage 	coin_detail_low_mintage 	coin_detail_weight 	coin_detail_composition 	coin_detail_diameter 	coin_detail_designer 	coin_detail_description_long 	coin_detail_description_short 	coin_detail_notes 	coin_detail_ngc_no 	coin_detail_ebay_heading 	coin_detail_ebay_category 	coin_detail_related_coins 	coin_detail_meta_description 	coin_detail_meta_title 	coin_detail_image 	coin_detail_image_alt 	coin_detail_sort 	coin_detail_coin_series_no 	nmcode

		$sql = "SELECT mp.PCGS";
		foreach ($compFields as $field)	// loop through fields to check
			$sql .= ", cc.$field AS cc_$field, mp.$field AS mp_$field";
		$sql .= " FROM mark_pcgs_to AS mp LEFT JOIN ".DB_PREFIX."catalog_coin AS cc ON cc.PCGS=mp.PCGS WHERE\n";
		foreach ($compFields as $field)	// loop through fields to check
			$sql .= "	cc.$field <> mp.$field OR ";
		$sql = substr($sql, 0, -4)." ORDER BY mp.id;";		// rid of last " OR " and append sorting clause
		// echo $sql; // exit;
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);

		if (!mysqli_num_rows($sql_results))						// if no rows returned
		{
			?>
			<span style="color: green;">All okay.</span><br />
			<?php
		} else {
			?>
			<span style="color: red;">Non-matching fields found (by PCGS # -> field_name: old value <span style="color: #909; font-weight: bold;">/</span> new value):</span><br />
			<?php
			while ($row = $ilance->db->fetch_array($sql_results))	// for each mismatched PCGS
			{
				foreach ($compFields as $field)	// loop through fields to check
				{
					if ($row['cc_'.$field] <> $row['mp_'.$field])
					{
						echo $row['PCGS'].' -> ';
						echo $field.': '.ilance_htmlentities($row['cc_'.$field]).' <span style="color: #909; font-weight: bold;">/</span> '.ilance_htmlentities($row['mp_'.$field]);
						echo '<br />';
					}
				}
				flush();
			}
		}
		unset($sql_results);
		?>
		<br />
		<strong>Checking done.</strong><br />
		</body>
		</html>
		<?php
		break;
	case 'copyback':
		?>
		<html>
			<head>
				<title>Imported spreadsheet copy back</title>
				<style type="text/css">
				</style>
			</head>
		<body>
		Imported spreadsheet copy back to "<?php echo $destCCtable; ?>" table.<br />
		<?php
		flush();
		?>
		Truncating table "<?php echo $destCCtable; ?>"...
		<?php
		flush();
		$sql = "TRUNCATE TABLE $destCCtable;";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query("TRUNCATE TABLE mark_pcgs_to;");
		unset($sql_results);
		?>
		Copying all records...<?php
		flush();

		$sql = "SELECT *,
			(SELECT denomination_unique_no FROM ".DB_PREFIX."catalog_toplevel AS c1 WHERE c1.denomination_short=mp.coin_series_denomination_no LIMIT 1) AS c1_coin_series_denomination_no,
			(SELECT coin_series_unique_no FROM ".DB_PREFIX."catalog_second_level AS c2 WHERE c2.coin_series_name=mp.coin_series_unique_no LIMIT 1) AS c2_coin_series_unique_no
			FROM mark_pcgs_to AS mp 
			ORDER BY id;";
			// get all records, JOINed with top and second level table IDs.
		 echo $sql."<br />\n";
		$sql_results = mysqli_query($ilance->db->connection_link,$sql);
		// $sql_results = $ilance->db->query($sql);
		$orderNum = 100;
		$fields = mysqli_fetch_fields($sql_results);			// get all fields into an array
		$lineIdx = 0;
		while ($row = $ilance->db->fetch_array($sql_results))	// for each source record
		{
			$sql = "INSERT INTO $destCCtable VALUES (0, ";	// init insert statement, including autoincrement "id" field
			foreach ($fields as $field)						// loop through fields
			{
				if ($field->name=='id' || $field->name=='coin_series_denomination_no' || $field->name=='coin_series_unique_no')
					;	// skip some fields
				elseif ($field->name=='Orderno')
				{
					$sql .= "'".$orderNum."', ";		// append value to SQL INSERT statement
					$sql .= "'".$ilance->db->escape_string($row['c1_coin_series_denomination_no'])."', ";		// append value to SQL INSERT statement
					$sql .= "'".$ilance->db->escape_string($row['c2_coin_series_unique_no'])."', ";				// append value to SQL INSERT statement
				} else {
					$sql .= "'".$ilance->db->escape_string($row[$field->name])."', ";		// append value to SQL INSERT statement
				}
			}
			$sql = substr($sql, 0, -2).");";			// lopping off last ", " and close SQL INSERT statement
			// echo $sql."<br />\n";
			$sql_results2 = mysqli_query($ilance->db->connection_link,$sql);
			// $sql_results2 = $ilance->db->query($sql);
			echo '.';
			if (($lineIdx++ % 200) == 0)
				echo '<br />';
			$orderNum += 100;
			flush();
			set_time_limit(30);		// extend execution time after each row updated
		}
		unset($sql_results);
		?>
		<br />
		<strong>Checking done.</strong><br />
		</body>
		</html>
		<?php
		break;
	}
	?>
	<html>
		<head>
			<title>PCGS Merge Menu</title>
			<style type="text/css">
			</style>
		</head>
	<body>
	Choose a function:
	<ul>
	<li><a href="?mode=sort">Sort PCGS import database</a>
	<li><a href="?mode=output" target="_blank">Output merged PCGS spreadsheet</a>
	<li><a href="?mode=ian_import_form">Ian's spreadsheet import</a>
	<li><a href="?mode=ian_import_check">Ian's spreadsheet error check</a>
	<li><a href="?mode=copyback" onClick="return confirm('Copy imported spreadsheet back into <?php echo $destCCtable; ?>?');">Copy imported spreadsheet back into "<?php echo $destCCtable; ?>"</a>
	</ul>
	<?php
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>

