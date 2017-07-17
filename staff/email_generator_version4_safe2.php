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

error_reporting(E_ALL); ini_set('display_errors', 1);

define('TEXTLENGTH', 67);	// plain text email line wrapping length

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	// if logged-in as admin

	$dataFile = pathinfo($_SERVER['SCRIPT_FILENAME']);	// get path and file name info about this script
	$dataFile = $dataFile['dirname'].'/'.$dataFile['filename'].'.json';	// establish path and file name to JSON data storage file
	// echo $dataFile; exit;

	$referal_name = '';	// 'referal_name=email04' referral for click tracking, or blank to suppress
	if ($referal_name)
	{
		$referal_name1 = '?'.$referal_name;
		$referal_name2 = '&'.$referal_name;
	} else {
		$referal_name1 = '';
		$referal_name2 = '';
	}
	
	if (False)		// NOT DONE, DO WE EVEN WANT? // if first level of page access, use iLance template to launch initial page
	{
		$pprint_array = array('couo','myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','latestviews','list','myfeat','subheading','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'email_generator_version4.html',2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val','series_list');
		$ilance->template->parse_loop('main', 'info_feat');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	if (!isset($_POST['issubmit']))	// if first level of page access, show form
	{
		if (True || isset($_GET['load']))	// ALWAYS DO UPON LOAD... if a previous mailing is being referenced
		{
			// get the last variables from a JSON file in the same directory as this PHP script
			clearstatcache(); $savedTime = @filemtime($dataFile);	// get file modification time of JSON data store
			$formvars = @json_decode(@file_get_contents($dataFile), true);
				// try to get the last form variables from the JSON file
			// echo '<PRE>'; print_r($formvars); echo '</PRE>'; exit;
		}
		if (!isset($formvars) || empty($formvars))	// if there is still no initial form variables defined, then ard code default them
		{
			$savedTime = null;		// no last form file loaded

			$formvars['title'] = 'This Week at <strong>Great</strong>Collections';

			$formvars['topbody'] = 'A selection of key date CAC coins, a rarely seen 1992 Close AM Lincoln, a collection of better Indian cents, a Hitlist VAM and some very nice British gold coins.<br />
<br />
<strong>
<a href="https://www.greatcollections.com/'.$referal_name1.'" style="color: #33C;">Browse Over {totalcoins} Coin Auctions</a><br />
<br />
No Hidden Reserves - Items Bid from $1 to $25,000+<br />
</strong>
<br />
<strong>All first-time U.S. auction winners receive free shipping.</strong><br />
<br />
<strong><a href="https://www.greatcollections.com/Register'.$referal_name1.'" style="color: #33C;">
Register to Bid... and Be Part of the Action
</a></strong><br />
It\'s fast and free, and gives you instant access to bidding, our auction archives, your personal watchlist, and more.<br />
<br />
Your Team,<br />
<i>Ian Russell, Raeleen Endo, and everyone at <strong>Great</strong>Collections</i>
';

			$formvars['coinlisttitle'] = 'More Featured Coins at <strong>Great</strong>Collections';

			$formvars['coinlist'] = '
Our Featured Coin
399367

Unreserved U.S.
400143 400755 400761 399953
399973 399985 376401 400745

Rare U.S.
400725 400729 400724 400446

Unreserved U.S.
400043 400045 399674 399675
388056 388065 394092 400017

Modern Coins
401527 401489 401504 401165
401166 397303 401090 401111
400341 400349 400386 400391

Rare U.S.
397059 388003 401142 401162

Modern Coins
401507 401534 376391 396489
396553 396554 398463 401371

Currency
400392 400395 389330

World Coins
399637 399634 399645 399646
326777 390479 396519 400851
';	// coins to return, and in the order they're desired

			$formvars['bottomtitle'] = 'Coming Up at <strong>Great</strong>Collections:';
		
			$formvars['bottombody'] = '<div style="text-align: left; margin: 10px; font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 14px;">
<a href="http://www.greatcollections.com/Coin/395621/'.$referal_name1.'" style="color: #33C;">
A rare British 1902 Proof Set graded by PCGS.
</a>
<br /><br />
<a href="http://www.greatcollections.com/search.php?q=Abbey&mode=product&sort=01'.$referal_name2.'" style="color: #33C;">
The Abbey Collection of Three-Dollar Gold Pieces.
</a>
<br /><br />
<a href="http://www.greatcollections.com/Coin/399091/'.$referal_name1.'" style="color: #33C;">
1995-W $1 Silver Eagle PCGS Proof-70 DCAM - Key to Series.
</a>
<br /><br />
Scarce and high grade $10 Indians (Listing Soon).
<br /><br />
An extremely rare 1891 Proof Half Eagle PCGS Proof-65+ DCAM (Listing Soon).
<br /><br />
A large collection of modern world gold coins graded by NGC.
<br /><br />
An extensive collection of CAC approved coins.
<br /><br />
</div>';
		}
		$formWidth = 500;
		$formWidthChar = 90;
		$formFont = 'font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 11px;';
		$clickBarSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="100px" height="500px" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0  100 500" preserveAspectRatio="xMidYMid meet" ><defs id="svgEditorDefs"><polygon id="svgEditorShapeDefs" fill="khaki" stroke="black" style="vector-effect: non-scaling-stroke; stroke-width: 1px;"/></defs><rect id="svgEditorBackground" x="0" y="0" width="100" height="500" style="fill: none; stroke: none;"/><path d="M0.0352828,-1.57447 l-1.875,-4.375 v1.875 h-3.75 v5 h3.75 v1.875 z M-6.21472,-4.07447 h-1.25 v5 h1.25 z M-8.08972,-4.07447 h-0.625 v5 h0.625 z" style="vector-effect: non-scaling-stroke; stroke-width: 1px;" stroke="black" id="e1_shape" transform="matrix(8.89188, 0, 0, 8.89188, 87.5815, 64)" fill="khaki"/></svg>';
		$clickBarSVGenc = 'data:image/svg+xml;charset=UTF-8,'.str_replace('"',"'",$clickBarSVG);
		// TinyMCE: https://www.tinymce.com/download/
		?>
		<html>
			<head>
				<title>Email Generator 4.0</title>
				<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
				<script>
					tinymce.init({
						selector: 'textarea#topbody, textarea#bottombody',
						plugins: 'link',
						convert_urls: false, /* don't convert links to relative! */
						toolbar: 'undo | redo | formatselect | sizeselect | fontselect | fontsizeselect | bold | italic | alignleft | aligncenter | alignright | alignjustify | bullist | numlist | outdent | indent | link',
						/* insert_toolbar: 'sizeselect | fontselect | fontsizeselect | link', */
						fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt'
					});
					function submitForm(e) {
						if (e.preventDefault) {
							e.preventDefault();
						}

						this.target = "email_iframe";
						// this.destination
						// this.submit();

						var cce = this.cloneNode();
						secondForm.target = "second_iframe";
						secondForm.submit();
					}
				</script>
				<style type="text/css">
					td.clickbar {
						width: 40px;
						min-width: 40px;
						height: 100%;
						background-color: #FFC;
						background-image: url("<?php echo $clickBarSVGenc; ?>");
						background-position: 2px 100px;
						background-repeat: repeat-y;
						background-size: 40px;
						cursor: pointer;
						
						border: 1px solid #999;
						border-radius: 10px;
						overflow: hidden;
						
						box-shadow: 3px 3px 3px grey;
					}
					td.clickbar:active {
						box-shadow: 0px 0px 0px white;
						position: relative;
						top: 3px;
						left: 3px;
					}
				</style>
			</head>
		<body onLoad="{document.getElementById('setupform').savedata.value='0'; document.getElementById('setupform').submit();}">
		<?php // echo $clickBarSVG; ?>
		<form id="setupform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" target="results_frame">
		<input type="hidden" name="savedata" value="0"><?php // default to NOT save data at the target of this form submission ?>
		<table style="font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 12px;"><tr>
		<td valign="top" style="padding-right: 8px;">
			<h2>Email Generator 4.0 &nbsp; &nbsp; &nbsp; <span id="formdatasource" style="font-size: 10px; font-weight: normal;">Email settings below are <?php echo (is_null($savedTime)?'the <span style="color: #900;">hardcoded defaults</span>':'from '.date('D M jS \a\t g:ia',$savedTime).' PT'); ?></span></h2>

			<h3>Top Section:</h3>
			<input type="hidden" name="issubmit" value="1">
			<input type="text" name="title" value="<?php echo htmlentities($formvars['title']); ?>" size="<?php echo floor($formWidthChar/1.15); ?>" maxlength="80" style="<?php echo $formFont; ?> font-weight: bold;"><br />
			<div style="width: <?php echo $formWidth; ?>px;">
				<textarea id="topbody" name="topbody" rows=20 cols=<?php echo $formWidthChar; ?> style="<?php echo $formFont; ?>"><?php echo htmlentities($formvars['topbody']); ?></textarea>
			</div>
			<br />
			<h3>Coin List: <span style="font-size: 10px;">(1st one is used for the larger featured coin above)</span></h3>
			<input type="text" name="coinlisttitle" value="<?php echo htmlentities($formvars['coinlisttitle']); ?>" size="<?php echo floor($formWidthChar/1.15); ?>" maxlength="80" style="<?php echo $formFont; ?> font-weight: bold;"><br />
			<textarea name="coinlist" rows=20 cols=<?php echo $formWidthChar; ?> style="width: <?php echo $formWidth; ?>px;"><?php echo htmlentities($formvars['coinlist']); ?></textarea><br />
			<br />
			<h3>Bottom Section: <span style="font-size: 10px;">(Leave title blank to skip this section)</span></h3>
			<input type="text" name="bottomtitle" value="<?php echo htmlentities($formvars['bottomtitle']); ?>" size="<?php echo floor($formWidthChar/1.15); ?>" maxlength="80" style="<?php echo $formFont; ?> font-weight: bold;"><br />
			<div style="width: <?php echo $formWidth; ?>px;">
				<textarea id="bottombody" name="bottombody" rows=20 cols=<?php echo $formWidthChar; ?> style="<?php echo $formFont; ?>"><?php echo htmlentities($formvars['bottombody']); ?></textarea>
			</div>
			<!--<input type="submit" name="submit" value="Submit"><br />-->
		</td>
		<td class="clickbar" valign="top" title="Update generated results on the right, and save the settings" onClick="{document.getElementById('formdatasource').innerHTML='Email settings below are new and have been saved'; document.getElementById('setupform').savedata.value='1'; document.getElementById('setupform').submit();}">&nbsp;
			<!--
			<div style="color: #EEE; font-size: 20px; font-weight: bold;">
				S<br />u<br />b<br />m<br />i<br />t<br /> <br />C<br />o<br />l<br />u<br />m<br />n<br />
			</div>
			-->
		</td>
		<td valign="top" style="width: 690px;">
			<iframe name="results_frame" style="width: 100%; height: 100%; border: 0px" onload="resizeIframe(this);"></iframe>
			<script>
				function resizeIframe(obj) {	// resize the height of the iframe to match the dynamic content
					obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
				}
			</script>
		</td>
		</tr></table>
		</form>
		</body>
		</html>
		<?php
		exit();
	}
	$title = $_POST['title'];
	$topbody = $_POST['topbody'];
	$coinlisttitle = $_POST['coinlisttitle'];
	$coinlist = $_POST['coinlist'];
	$bottomtitle = $_POST['bottomtitle'];
	$bottombody = $_POST['bottombody'];

	if ($_POST['savedata'])	// if form data is to be saved
	{
		$formvars['title'] = $title;
		$formvars['topbody'] = $topbody;
		$formvars['coinlisttitle'] = $coinlisttitle;
		$formvars['coinlist'] = $coinlist;
		$formvars['bottomtitle'] = $bottomtitle;
		$formvars['bottombody'] = $bottombody;
		// save the last variables to a JSON file in the same directory as this PHP script
		file_put_contents($dataFile, json_encode($formvars));
	}

	$select_featured = $ilance->db->query("SELECT COUNT(*) AS totalcoins FROM ".DB_PREFIX."projects ".
		"WHERE project_state='product' AND visible='1' AND status='open';");
	if ($row = $ilance->db->fetch_array($select_featured))
		$totalcoins = $row['totalcoins'];
	else
		$totalcoins = 0;
	unset($select_featured);
	// echo $totalcoins; exit;

	// substitute for dynamic variables
	$topbody = str_replace('{totalcoins}',number_format($totalcoins,0,'.',','),$topbody);
	$bottombody = str_replace('{totalcoins}',number_format($totalcoins,0,'.',','),$bottombody);

	if (!preg_match_all("#^(.*[a-z]+.*)\n([0-9\s,]+)$#im",$coinlist,$coinlist,PREG_PATTERN_ORDER))
	{
		die("Bad coin list submitted");
	}
	// echo '<pre>'; print_r($coinlist); echo '</pre>'; exit;
/*
Array
(
    [0] => Array
        (
            [0] => Featured
395014
            [1] => CAC Approved

395009 395008 395012 395019

            [2] => Rare U.S.

399503 396771 395870 395020
395021 387984 391817 398477
            [3] => Indian Cents
387913 387914 387980 387952
            [4] => Unreserved U.S.
399523 399518 399524 399525
399514 399513 399512 399461
396951 396649 399552 399544
399527 397974 398110 398127
            [5] => CAC Approved
399956 399957 399958 399959
395876 395873 396961 399459
            [6] => Modern Coins
399673 398475 398466 398449
399088 399098 399096 399081
388920 388878 399718 396459
            [7] => World Coins
395620 395621 399084 399715
399679 399686 399687 399704
396510 396515 390487 390482	

        )

    [1] => Array
        (
            [0] => Featured
            [1] => CAC Approved
            [2] => Rare U.S.
            [3] => Indian Cents
            [4] => Unreserved U.S.
            [5] => CAC Approved
            [6] => Modern Coins
            [7] => World Coins
        )

    [2] => Array
        (
            [0] => 395014
            [1] => 
395009 395008 395012 395019

            [2] => 
399503 396771 395870 395020
395021 387984 391817 398477
            [3] => 387913 387914 387980 387952
            [4] => 399523 399518 399524 399525
399514 399513 399512 399461
396951 396649 399552 399544
399527 397974 398110 398127
            [5] => 399956 399957 399958 399959
395876 395873 396961 399459
            [6] => 399673 398475 398466 398449
399088 399098 399096 399081
388920 388878 399718 396459
            [7] => 395620 395621 399084 399715
399679 399686 399687 399704
396510 396515 390487 390482	

        )
)
*/
	foreach ($coinlist[2] as $section => $value)	// loop through sections of coin IDs
		$coinlist[2][$section] = trim($value).' ';	// and rid of extra spaces at start, leave one trailing at end for matching
	$coin_list = implode(' ', $coinlist[2]);	// combine all sections into a single string
	$coin_list = preg_replace("#[^0-9]+#ms",' ',$coin_list);	// all non-number character(s) between numbers become single spaces
	$coin_list = preg_replace("#\s+#", ',', trim($coin_list));	// eliminate any trailing space, and convert all spaces and newlines to commas
	// echo '<pre>'; print_r($coin_list); echo '</pre>'; exit;

	// $coin_list = '395014, 395009, 395008, 395012, 395019, 399503, 396771, 395870, 395020, 395021, 387984, 391817, 398477, 387913, 387914, 387980, 387952, 399523, 399518, 399524, 399525, 399514, 399513, 399512, 399461, 396951, 396649, 399552, 399544, 399527, 397974, 398110, 398127, 399956, 399957, 399958, 399959, 395876, 395873, 396961, 399459, 399673, 398475, 398466, 398449, 399088, 399098, 399096, 399081, 388920, 388878, 399718, 396459, 395620, 395621, 399084, 399715, 399679, 399686, 399687, 399704, 396510, 396515, 390487, 390482';	// coins to return, and in the order they're desired
	// SELECT *, CONCAT('http://www.greatcollections.com/Coin/',project_id,'/') AS 'link', CONCAT('http://www.greatcollections.com/image/400/268/',project_id,'-1.jpg') AS 'image', project_title AS 'title', currentprice, bids FROM ilance_projects WHERE project_id IN (395620)
	$select_featured = $ilance->db->query("SELECT project_id, project_title, currentprice, bids FROM ".DB_PREFIX."projects ".
		"WHERE project_id IN ($coin_list) AND project_state='product' AND visible='1' AND status='open' ".
		"ORDER BY FIELD(project_id, $coin_list);");
	$itemHTMLFeature = '';	// init to no feature coin defined yet
	$itemHTML = '';		// init accumulating HTML
	$itemText = '';		// init accumulating plain text
	$itemCCE = '';
	$lineCnt = 0;
	while ($row = $ilance->db->fetch_array($select_featured))	// for all coins found in database
	{
		// echo '<pre>'; print_r($row); echo '</pre>'; exit;
		
		foreach ($coinlist[2] as $section => $value)	// loop through sections of coin IDs
		{
			// echo '<pre>project_id='.$row['project_id'].', section='.$section.', value='.$value.'<br />';
			if (preg_match("#^".$row['project_id']."[^0-9]#",$value))	// if current coin ID matches one at start of coin list for this section
			{
				if ($itemHTMLFeature)	// and if a feature coin is defined yet
				{
					$itemHTML .= 
($itemHTML?'<br /><br />':'').'
<b style="font-size: 15px;">'.$coinlist[1][$section].'</b><br /><br />

';
				}
				$itemText .= 
strtoupper(trim($coinlist[1][$section])).':

';

				$heading = 'SELL '.strtoupper(trim($coinlist[1][$section])).':';
				$itemCCE .=
str_repeat('=',TEXTLENGTH).'
'.str_repeat(' ',max(0,floor((TEXTLENGTH-strlen($heading))/2))).$heading.'
'.str_repeat('=',TEXTLENGTH).'

Auctioning the following lots of '.trim($coinlist[1][$section]).':

';
				break;
			}
		}
		if (!$itemHTMLFeature)	// if no feature coin defined yet
			$itemHTMLFeature = showItem($row, False, 200, 25);	// HTML of first, featured item, 200 pixels wide with a 25 pixel corner radius
		else {					// for the rest of the coins following the featured
			$itemHTML .= 
'<div style="width: 145px; display: inline-block; padding: 4px; vertical-align: text-top;">'.showItem($row, False, 140, 20).'</div>

';
			if ($lineCnt++ >= 4)	// force a line break every 4 coins, not trusting the browser to wrap properly
				$itemHTML .= '<br />
';
		}
		// echo 'Test'; exit;

		$itemText .= showItem($row, True);
		$itemCCE .= showItem($row, True);
/*
=========================================================================
           SELL $10 INDIAN HEAD & LIBERTY HEAD GOLD COINS
=========================================================================

Selling the following lot of $10 Indian Head and Liberty Head gold coins:

(5) XF $10 Liberty Heads @ $640

(2) AU/BU $10 Liberty Heads @ *SOLD*

(3) BU $10 Liberty Heads @ $655/ea *SOLD*

(2) BU $10 Indian Heads @ $680/ea *SOLD*

Please send a direct or text/call 770-843-6012, if interested.

Thanks,

Tony
*/
		$lineCnt = 0;
	}
	unset($select_featured);		// close database recordset

	// start of HTML page output
	?>
	<script language="JavaScript">
		function HTML2clipboard()	// copy generated HTML to clipboard
		{
			// document.getElementById('copylink').style.backgroundColor = '#F99';
			document.getElementById('copytemp').value = document.getElementById('htmlcontent').innerHTML;
			document.querySelector('#copytemp').select();
			document.execCommand('copy');
			document.getElementById('copylink').style.backgroundColor = '#9F9';
			setTimeout(function myFunction() {
				document.getElementById('copylink').style.backgroundColor = 'white';
			}, 1000);
		}
	</script>

	<a href="#" id="copylink" style="font-family: sans-serif; font-size: 10px;" onClick="HTML2clipboard();">
	Copy HTML to clipboard
	</a>
	<textarea id="copytemp" style="width: 0px; height: 0px; border: 0px solid white;"></textarea>
	<!--<input type="text" id="copytemp" value="" style="width: 0px; border: 0px solid white;" />-->
	<br /><div id="htmlcontent"><?php
	$top = file_get_contents('http://www.greatcollections.com/email2016/email2016top.html');	// read email top
	if ($referal_name)
		$replaceStr = '$1'.$referal_name;
	else
		$replaceStr = '';
	$top = preg_replace("#([\?&]referal_name=)#",$replaceStr,$top);
	echo $top;

	email2016_1col_float('<span style="font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 22px;">'.$title.'</span>');

	// email2016_bg_1col('More Featured Coins at <strong>Great</strong>Collections:');
		// output a single column with textured background, HTML or plain text formatted

	email2016_2col100_1($topbody, $itemHTMLFeature);
		// output two columns, HTML or plain text formatted

	email2016_bg_1col($coinlisttitle);
		// output a single column with textured background, HTML or plain text formatted

	email2016_1col_float($itemHTML);
		// output one column, floating width, HTML or plain text formatted

	if (trim($bottomtitle))	// if there's any bottom title
	{
		email2016_bg_1col($bottomtitle);
			// output a single column with textured background, HTML or plain text formatted
			
		email2016_1col_float($bottombody);
			// output one column, floating width, HTML or plain text formatted
	}
	include('http://www.greatcollections.com/email2016/email2016bottom.html');
	?></div>
	<?php
	echo '<br /><br /><pre>'.str_repeat('*',TEXTLENGTH).'
'.str_repeat(' ',max(0,floor((TEXTLENGTH-17)/2))).'Plain Text Email:
'.str_repeat('*',TEXTLENGTH).'

';
	echo strip_tags($title).'
'.str_repeat('_',TEXTLENGTH).'

';
	echo HTMLtoPlain($topbody).''.str_repeat('_',TEXTLENGTH).'

';
	echo HTMLEntities(wrapText($itemText));	// wrap plain text

	if (trim($bottomtitle))	// if there's any bottom title
	{
		echo str_repeat('_',TEXTLENGTH).'

'.strip_tags($bottomtitle).'
'.str_repeat('_',TEXTLENGTH).'

'.HTMLtoPlain($bottombody).'';
	}
	include('http://www.greatcollections.com/email2016/email2016bottom.txt');
	echo '


'.str_repeat('*',TEXTLENGTH).'
'.str_repeat(' ',max(0,floor((TEXTLENGTH-23)/2))).'Certified Coin Exchange:
'.str_repeat('*',TEXTLENGTH).'

'.HTMLEntities($itemCCE).'</pre><br /><br />';
	exit;

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function email2016_bg_1col($body)	// output a single column with textured background, HTML or plain text formatted

{
?>			<!-- Single Column, Textured Dark Background -->
			<table style="width: 100%; border-top: 1px solid #252c44; text-align: center; background: #252c44 url(https://www.greatcollections.com/email2016/marble_tile.jpg) repeat;
				background-position: -100px 0px;" border="0" cellpadding="2" cellspacing="2">
				<tr>
					<td align="center" valign="top" style="padding: 10px;">
						<span style="font-family: Tahoma,Arial,sans-serif; color: white; font-size: 20px;">
<?php echo $body; ?>
						</span>
					</td>
				</tr>
			</table>
<?php
}
function email2016_1col_float($body)	// output one column, floating width, HTML or plain text formatted
{
?>			<!-- Single Column, floating width -->
			<div style="text-align: center; padding: 8px;	font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 12px;">
			<?php echo $body; ?>
			</div>
<?php
			return;
?>			<!-- Single Column, floating width -->
			<center>
			<div style="padding: 8px;	font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 12px;">
			<?php echo $body; ?>
			</div>
			</center>
<?php
			return;
?>			<!-- Single Column, floating width -->
			<div style="width: 100%; text-align: center; padding: 8px;	font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 12px;">
			<?php echo $body; ?>
			</div>
<?php
}

function email2016_2col100_1($body1, $body2)	// output two columns, HTML or plain text formatted
{
	global $referal_name1;

?>		<!-- Two Columns 100%/?% -->
			<table style="width: 100%; border-top: 1px solid #656c84;" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="100%" valign="top" style="padding: 10px;">
						<span style="font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 14px;">
<?php echo $body1; ?>
						</span>
					</td>
					<td width="1%" valign="top" align="center" style="padding: 10px; Xbackground-color: #8b8ea9; color: white; border-left: 0px solid #656c84;">
						<span style="font-family: Tahoma,Arial,sans-serif; color: white; font-size: 13px;">

<?php echo $body2; ?>

						</span>
					</td>
				</tr>
			</table>
<?php
}

function showItem($row, $isPlain = False, $width = 140, $corners = 20)	// output a single coin, format width and corner roundness, HTML or plain text formatted
{
	global $referal_name1;
	
	$link = 'http://GreatCollections.com/Coin/'.$row['project_id'].'/'.$referal_name1;
	$image = 'http://GreatCollections.com/image/400/268/'.$row['project_id'].'-1.jpg';

	if (!$isPlain) {
		return 
'<a href="'.$link.'" style="color: #33C;">
<div style="border-radius: '.$corners.'px; overflow: hidden;">
<img src="'.$image.'" width="'.$width.'" alt="[Coin Photo]


" style="white-space: pre;" />
</div>
'.HTMLentities($row['project_title']).'
<br style="margin-bottom: 10px;" />
<i>Current Bid $'.number_format($row['currentprice'],2,'.',',').
($row['bids']>0?' <nobr>('.$row['bids'].' Bid'.($row['bids']==1?'':'s').')</nobr>':'').
'</i>
</a>';
	} else {
		return
HTMLentities($row['project_title']).'
Current Bid $'.number_format($row['currentprice'],2,'.',',').
($row['bids']>0?' ('.$row['bids'].' Bid'.($row['bids']==1?'':'s').')':'').
'
'.$link.'

';
	}
}

function HTMLtoPlain($html)	// convert HTML to plain text
{
	$text = str_replace('&nbsp;',' ', $html);								// convert non-breaking spaces into spaces
	$text = preg_replace("#(<br */*>)|(</p *>)|(</div *>)#i","\n",$text);	// convert <br>, </p>, and </div> to line breaks
	$text = preg_replace("#<(p|div)[^>]*>#i",'',$text);						// remove <p> and <div>
	// echo 'BEFORE: <PRE>'.htmlentities($text).'</PRE>'; 
	$text = preg_replace("#<a\s+href=\"([^\"]*)\"\s*>(.*?)</a>#msi","$2\n$1",$text);		// convert link and text to "text\nlink"
	// echo 'AFTER: <PRE>'.htmlentities($text).'</PRE>'; 
	$text = strip_tags($text);		// strip remaining tags
	$text = preg_replace("#[ \t]*\n[ \t]*#","\n",$text);		// convert any whitespace around newlines to just the newline (sort of a trim)
	$text = preg_replace("#\n{3,1000}#","\n\n",$text);	// convert more than two newlines to just two newlines
	$text = str_ireplace('https://www.greatcollections.com','https://www.GreatCollections.com',$text);	// convert to nicer looking URL start, SSL mode
	$text = preg_replace("#http://(www\.)?greatcollections\.com#i","http://GreatCollections.com",$text);	// convert to nicer looking URL start, non-SSL
	$text = wrapText($text);	// wrap it
	return $text;
}

function wrapText($text)	// wrap plain text
{
	$text = wordwrap($text, TEXTLENGTH);
	return $text;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>