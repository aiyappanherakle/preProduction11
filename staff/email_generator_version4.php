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
	/* 'ajax', */
	'inline',
	/* 'jquery', */
	'cron',
	'tabfx',
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
$mailtype = $_GET['mailtype'];	// none=preauction, postauction
switch ($mailtype)
{
case 'postauction':
	$mailtypeFieldSuf = '_post';
	$mailtypeDesc = 'Post-Auction';
	break;
default:
	$mailtypeFieldSuf = '';
	$mailtype = 'preauction';
	$mailtypeDesc = 'Pre-Auction';
}
$mailtypeDesc = ($mailtype=='postauction'?'Post-Auction':'Pre-Auction');
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
  $ilance->subscription = construct_object('api.subscription');

error_reporting(E_ALL); ini_set('display_errors', 1);

define('TEXTLENGTH', 58);	// plain text email line wrapping length
define('TEXTLENGTH_CCE', 67);	// plain text email line wrapping length, for CCE post

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

	if (isset($_GET['autogenlist']))	// if auto generate coin list...
	{
		echo "Featured Coin\n\n";
		$sql = "SELECT * FROM ".DB_PREFIX."projects p LEFT JOIN ".
			DB_PREFIX."catalog_coin cc ON cc.PCGS=p.cid ".
			"WHERE p.reserve=0 AND p.currentprice>500 AND p.status='open' AND p.project_state='product' AND p.visible='1' ".
			"ORDER BY p.Orderno LIMIT 100;";
		// echo 'SQL = '.$sql; exit;
		$select = $ilance->db->query($sql);
		$cnt = 0;
		while ($row = $ilance->db->fetch_array($select))	// for matching coins found in database
		{
			echo $row['project_id'].' ';
			if ((++$cnt % 4) == 0)
				echo "\n";
		}
		unset($select);		// close database recordset
		exit;
	}
	if (!isset($_POST['issubmit']))	// if first level of page access, show form
	{
		if (True || isset($_GET['load']))	// ALWAYS DO UPON LOAD... if a previous mailing is being referenced
		{
			// get the last variables from a JSON file in the same directory as this PHP script
			clearstatcache(); 
			//$savedTime = @filemtime($dataFile);	// get file modification time of JSON data store
			//$formvars = @json_decode(@file_get_contents($dataFile), true);
			$savedTime=$ilance->cache->fetch_saved_time('_email_generator_bottombody'.$mailtypeFieldSuf);
			$formvars['title']=$ilance->cache->fetch('_email_generator_title'.$mailtypeFieldSuf);
			$formvars['topbody']=$ilance->cache->fetch('_email_generator_topbody'.$mailtypeFieldSuf);
			$formvars['coinlisttitle']=$ilance->cache->fetch('_email_generator_coinlisttitle'.$mailtypeFieldSuf);
			$formvars['coinlist']=$ilance->cache->fetch('_email_generator_coinlist'.$mailtypeFieldSuf);
			$formvars['bottomtitle']=$ilance->cache->fetch('_email_generator_bottomtitle'.$mailtypeFieldSuf);
			$formvars['bottombody']=$ilance->cache->fetch('_email_generator_bottombody'.$mailtypeFieldSuf);
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
				<title>Email Generator 4.0 - <?php echo $mailtypeDesc; ?></title>
				<script src="../functions/javascript/jquery-3.2.0.min.js"></script>
				<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
				<script>
					tinymce.init({
						selector: 'textarea#topbody',
						plugins: 'link, code, textcolor, lists',
						convert_urls: false, /* don't convert links to relative! */
						toolbar: 'undo | redo | formatselect | sizeselect | fontselect | fontsizeselect | bold | italic | forecolor | alignleft | aligncenter | alignright | alignjustify | bullist | numlist | outdent | indent | link | code',
						fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
						/* textcolor_cols: '4',
						textcolor_rows: '10', */
						textcolor_map: [
							'000000', 'Black',
							'993300', 'Burnt orange',
							'333300', 'Dark olive',
							'003300', 'Dark green',
							'003366', 'Dark azure',
							'000080', 'Navy Blue',
							'333399', 'Indigo',
							'333333', 'Very dark gray',
							'800000', 'Maroon',
							'FF6600', 'Orange',
							'808000', 'Olive',
							'008000', 'Green',
							'008080', 'Teal',
							'0000FF', 'Blue',
							'666699', 'Grayish blue',
							'808080', 'Gray',
							'FF0000', 'Red',
							'FF9900', 'Amber',
							'99CC00', 'Yellow green',
							'339966', 'Sea green',
							'33CCCC', 'Turquoise',
							'3366FF', 'Royal blue',
							'800080', 'Purple',
							'999999', 'Medium gray',
							'FF00FF', 'Magenta',
							'FFCC00', 'Gold',
							'FFFF00', 'Yellow',
							'00FF00', 'Lime',
							'00FFFF', 'Aqua',
							'00CCFF', 'Sky blue',
							'993366', 'Red violet',
							'FFFFFF', 'White',
							'EEEEEE', 'Near white',
							'FF99CC', 'Pink',
							'FFCC99', 'Peach',
							'FFFF99', 'Light yellow',
							'CCFFCC', 'Pale green',
							'CCFFFF', 'Pale cyan',
							'99CCFF', 'Light sky blue',
							'CC99FF', 'Plum'
							]
					});
					tinymce.init({
						selector: 'textarea#bottombody',
						plugins: 'link, code, textcolor, paste',
						convert_urls: false, /* don't convert links to relative! */
						toolbar: 'undo | redo | formatselect | sizeselect | fontselect | fontsizeselect | bold | italic | forecolor | alignleft | aligncenter | alignright | alignjustify | bullist | numlist | outdent | indent | link | code',
						fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
						valid_elements: 'a[href|target=_blank|title],strong/b,em,i,div[align],p[align],br'
					});
					function submitForm() {
						document.getElementById('setupform').savedata.value='1';	// make sure we'll be saving these settings
						document.getElementById('setupform').submit();				// submit form
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
		<form id="setupform" action="<?php echo $_SERVER['PHP_SELF']; ?>?mailtype=<?php echo $mailtype; ?>" method="post" target="results_frame">
		<input type="hidden" name="savedata" value="0"><?php // default to NOT save data at the target of this form submission ?>
		<table style="font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 12px;"><tr>
		<td valign="top" style="padding-right: 8px;">
			<h2>Email Generator 4.0 - <?php echo $mailtypeDesc; ?> &nbsp; <span id="formdatasource" style="font-size: 10px; font-weight: normal;">Settings <?php echo (is_null($savedTime)?'are the <span style="color: #900;">hardcoded defaults</span>':'from '.date('M j, g:ia',strtotime($savedTime)).' PT'); ?></span></h2>

			<h3>Top Section: &nbsp; &nbsp; &nbsp; <span style="font-size: 10px; font-weight: normal; float: right;">
				<strong>Output:</strong>
				<label style="cursor: pointer;"><input type="radio" name="outtype" value="html" style="vertical-align: bottom;" onChange="submitForm();" checked>HTML Email &nbsp;</label>
				<label style="cursor: pointer;"><input type="radio" name="outtype" value="text" style="vertical-align: bottom;" onChange="submitForm();">Plain Text &nbsp;</label>
				<label style="cursor: pointer;"><input type="radio" name="outtype" value="cce" style="vertical-align: bottom;" onChange="submitForm();">CCE Text &nbsp;</label>
				<label style="cursor: pointer;"><input type="radio" name="outtype" value="cu" style="vertical-align: bottom;" onChange="submitForm();">CU HTML &nbsp;</label>
			</span></h3>
			<?php
			if ($mailtype == 'postauction')
			{
				echo '<div style="font-size: 10px; margin-top: -5px; margin-bottom: 5px;"><strong>(a row of underscores denotes the start of an optional second column)</strong></div>';
			}
			?>			<input type="hidden" name="issubmit" value="1">
			<input type="text" name="title" value="<?php echo htmlentities($formvars['title']); ?>" size="<?php echo floor($formWidthChar/1.15); ?>" maxlength="255" style="<?php echo $formFont; ?> font-weight: bold;"><br />
			<div style="width: <?php echo $formWidth; ?>px;">
				<textarea id="topbody" name="topbody" rows=20 cols=<?php echo $formWidthChar; ?> style="<?php echo $formFont; ?>"><?php echo htmlentities($formvars['topbody']); ?></textarea>
			</div>
			<br />
			<h3>Coin List: <?php
			if ($mailtype == 'preauction')
			{
				echo '<span style="font-size: 10px;">(1st one is used for the larger featured coin above) &nbsp; <a href="JavaScript:autoCoinList();">Auto-Generate</a></span>';
			}
			?>
			</h3>
			<input type="text" name="coinlisttitle" value="<?php echo htmlentities($formvars['coinlisttitle']); ?>" size="<?php echo floor($formWidthChar/1.15); ?>" maxlength="255" style="<?php echo $formFont; ?> font-weight: bold;"><br />
			<textarea id="coinlist" name="coinlist" rows=20 cols=<?php echo $formWidthChar; ?> style="width: <?php echo $formWidth; ?>px;"><?php echo htmlentities($formvars['coinlist']); ?></textarea><br />
			<br />
			<h3>Bottom Section: <span style="font-size: 10px;">(Leave title blank to skip this section)</span><div style="font-size: 10px;"><i>Also immediately appears on the home page, just above the footer</i></div></h3>
			<input type="text" name="bottomtitle" value="<?php echo htmlentities($formvars['bottomtitle']); ?>" size="<?php echo floor($formWidthChar/1.15); ?>" maxlength="255" style="<?php echo $formFont; ?> font-weight: bold;"><br />
			<div style="width: <?php echo $formWidth; ?>px;">
				<textarea id="bottombody" name="bottombody" rows=20 cols=<?php echo $formWidthChar; ?> style="<?php echo $formFont; ?>"><?php echo htmlentities($formvars['bottombody']); ?></textarea>
			</div>
			<!--<input type="submit" name="submit" value="Submit"><br />-->
		</td>
		<td class="clickbar" valign="top" title="Update generated results on the right, and save the settings" onClick="{document.getElementById('formdatasource').innerHTML='Settings are new and have been saved'; submitForm();}">&nbsp;
			<!--
			<div style="color: #EEE; font-size: 20px; font-weight: bold;">
				S<br />u<br />b<br />m<br />i<br />t<br /> <br />C<br />o<br />l<br />u<br />m<br />n<br />
			</div>
			-->
		</td>
		<td valign="top" style="width: 690px;">
			<iframe name="results_frame" style="width: 100%; height: 100%; border: 0px" onload="{resizeIframe(this); backPlug(this)};"></iframe>
			<script>
				function resizeIframe(obj) {	// resize the height of the iframe to match the dynamic content
					obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
				}
				function backPlug(obj) {		// backplug possible item titles into the "coinlist" <textarea>
					var backplugs = obj.contentDocument.getElementById('backplug').value;		// get backplug text loaded into <iframe>'s hidden field
					// alert(backplugs);
					var coinlist = document.getElementById('coinlist').value;		// get form field contents
					// alert(coinlist);
					if (coinlist != null && backplugs != null)						// if there's anything in the user's coinlist form, or the backplug string
					{
						backplugs = backplugs.split("\n");							// split backplug into array of items
						// alert(backplugs.length);
						for (var i = 0, len = backplugs.length; i < len; i++)		// for every backplug item
						{
							backplugs[i] = backplugs[i].split("\t");				// split backplug item into array of item #, item desc
							// alert(backplugs[i].length);
							var itemID = backplugs[i][0], desc = backplugs[i][1];	// get number and description
							// alert('itemID = '+itemID+', desc = '+desc);
							if (itemID > 0)											// if a valid number
							{
								var re = new RegExp("^[\t ]*"+itemID+"[\t ]*$", "m");	// set up regex search pattern for item alone on a line
								coinlist = coinlist.replace(re, itemID+" - "+desc); 	// replace with item plus title
							}
						}						
						document.getElementById('coinlist').value = coinlist;		// write back updated form field contents
						// if (!preg_match_all("#^([0-9]+)[- \t,;:]*(.*)\n(.+\n)?#im",$coinlist."\n",$coinlist,PREG_SET_ORDER))
					}
				}
				function autoCoinList()		// auto-generate pre-auction coin list
				{
					jQuery.ajax({
						url: "<?php echo $_SERVER['PHP_SELF']; ?>?autogenlist=1",
						cache: false
					})
					.done(function(coinlist) {
						document.getElementById('coinlist').value = coinlist;		// write  updated form field contents
					});
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
		$ilance->cache->store('_email_generator_title'.$mailtypeFieldSuf,$formvars['title'],0);
		$ilance->cache->store('_email_generator_topbody'.$mailtypeFieldSuf,$formvars['topbody'],0);
		$ilance->cache->store('_email_generator_coinlisttitle'.$mailtypeFieldSuf,$formvars['coinlisttitle'],0);
		$ilance->cache->store('_email_generator_coinlist'.$mailtypeFieldSuf,$formvars['coinlist'],0);
		$ilance->cache->store('_email_generator_bottomtitle'.$mailtypeFieldSuf,$formvars['bottomtitle'],0);
		$ilance->cache->store('_email_generator_bottombody'.$mailtypeFieldSuf,$formvars['bottombody'],0);

		//file_put_contents($dataFile, json_encode($formvars));
	}
	$outtype = $_POST['outtype'];	// output format type: html, text, cce, cu

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

	if ($mailtype == 'preauction')
	{
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
		if ($outtype == 'cu')	// if CU special HTML
			$itemHTML .= '<table width="100%"><tr>';				// start the table and a new line
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
						if ($outtype == 'html')		// if Email HTML and some coins already displayed
						{
							$itemHTML .= 
	($itemHTML?'<br /><br />':'').'
	<b style="font-size: 15px;">'.$coinlist[1][$section].'</b><br /><br />

	';
						} else {				// if CU special HTML and some coins already displayed
							// $itemHTML .= '<!--LCi='.$lineCnt.'-->';
							while (($lineCnt++ % 4) != 0)	// run out the remainder of 4 coin blocks after end,
							{
								$itemHTML .= '<td></td>';
							}
							// start a new line for the title
							$itemHTML .= '</tr><tr><td colspan="4" align="center">
	'.($itemHTML?'':'').'
	<font size="4"><b>'.$coinlist[1][$section].'</b></font><br />

	</td></tr>
	<tr>';					// end the title line
						}
					}
					$itemText .= 
	strtoupper(trim($coinlist[1][$section])).':

';

					$heading = 'SELL '.strtoupper(trim($coinlist[1][$section])).':';
					$itemCCE .=
	str_repeat('=',TEXTLENGTH_CCE).'
	'.str_repeat(' ',max(0,floor((TEXTLENGTH_CCE-strlen($heading))/2))).$heading.'
	'.str_repeat('=',TEXTLENGTH_CCE).'

	Auctioning the following lots of '.trim($coinlist[1][$section]).':

	';
					$lineCnt = 0;	// start counter of images in this group
					break;
				}
			}
			if (!$itemHTMLFeature)	// if no feature coin defined yet
				$itemHTMLFeature = showItem($row, $outtype, 200, 25);	// HTML of first, featured item, 200 pixels wide with a 25 pixel corner radius
			else {					// for the rest of the coins following the featured
				if ($outtype == 'html')	// if Email HTML, format using CSS inline styles
				{
					$itemHTML .= 
	'<div style="width: 145px; display: inline-block; padding: 4px; vertical-align: text-top;">'.showItem($row, $outtype, 140, 20).'</div>

	';
				} else {				// if special CU HTML, format using old attributes
					$itemHTML .= 
	'<td width="145" align="center" valign="top">'.showItem($row, $outtype, 140, 20).'</td>

	';
				}
				if ((++$lineCnt % 4) == 0)	// force a line break every 4 coins, not trusting the browser to wrap properly
				{
					if ($outtype == 'html')	// if Email HTML, start a new line
					{
						$itemHTML .= '<br />
	';
					} else					// if CU special HTML
						$itemHTML .= '</tr><tr>';			// start a new line using tables
				}
			}
			// echo 'Test'; exit;

			$itemText .= showItem($row, $outtype);
			$itemCCE .= showItem($row, $outtype);
		}
		if ($outtype == 'cu')				// if CU special HTML
		{
			// $itemHTML .= '<!--LC='.$lineCnt.'-->';
			while (($lineCnt++ % 4) != 0)	// run out the remainder of 4 coin blocks after end,
			{
				$itemHTML .= '<td></td>';
			}
			$itemHTML .= '</tr></table>';		// close the table
		}
		unset($select_featured);		// close database recordset
	} else if ($mailtype == 'postauction')
	{
		// if (!preg_match_all("#^([0-9]+)[- \t,;:]*(.*)\n(.+\n)?(.+\n)?#im",$coinlist."\n",$coinlist,PREG_SET_ORDER))
		// if (!preg_match_all("#^([0-9]+)[- \t,;:]*(.*)\n(.+\n)*[ \t]*\n#im",$coinlist."\n",$coinlist,PREG_SET_ORDER))
		if (!preg_match_all("#^([0-9]+)[- \t,;:]*(.*)\n(.+\n)?#im",$coinlist."\n",$coinlist,PREG_SET_ORDER))
		{
			die("Bad post-auction coin list submitted");
		}
		// echo '<pre>'; print_r($coinlist); echo '</pre>'; exit;
/*
413827 - 1847 Half Eagle PCGS AU-55 Old Green Holder CAC Gold Sticker
PCGS Price Guide at $735.
Something else.


410742 - 1914-D Lincoln Cent PCGS MS-64 RD
PCGS Price Guide $6,500.

442864 - 1798 Draped Bust Silver Dollar B-6 BB-96 Knob 9, 5 Vertical Lines PCGS XF-45


Array
(
    [0] => Array
        (
            [0] => 413827 - 1847 Half Eagle PCGS AU-55 Old Green Holder CAC Gold Sticker
PCGS Price Guide at $735.
Something else.

            [1] => 413827
            [2] => 1847 Half Eagle PCGS AU-55 Old Green Holder CAC Gold Sticker
            [3] => PCGS Price Guide at $735.

            [4] => Something else.

        )

    [1] => Array
        (
            [0] => 410742 - 1914-D Lincoln Cent PCGS MS-64 RD
PCGS Price Guide $6,500.


            [1] => 410742
            [2] => 1914-D Lincoln Cent PCGS MS-64 RD
            [3] => PCGS Price Guide $6,500.

            [4] => 

        )

    [2] => Array
        (
            [0] => 442864 - 1798 Draped Bust Silver Dollar B-6 BB-96 Knob 9, 5 Vertical Lines PCGS XF-45


            [1] => 442864
            [2] => 1798 Draped Bust Silver Dollar B-6 BB-96 Knob 9, 5 Vertical Lines PCGS XF-45
            [3] => 

        )

)

<span style="font-family:tahoma,arial,sans-serif;color:rgb(0,0,0);font-size:14px">
<a href="http://www.greatcollections.com/Coin/413827/1847-Liberty-Gold-Half-Eagle-PCGS-AU-55-CAC-Gold-Label-OGH" style="color:black;text-decoration:none" target="_blank">
<img src="http://www.greatcollections.com/image/400/268/413827-1.jpg" width="100" alt="1847 Half Eagle PCGS AU-55 Old Green Holder CAC Gold Sticker" align="left" style="padding-right: 10px;">
<strong>1847 Half Eagle PCGS AU-55 Old Green Holder CAC Gold Sticker</strong>
<br><br><br>
<span style="color:rgb(153,0,0);font-size:14px"><strong>
PCGS Price Guide at $735.<br>Sold $1,540.
</strong></span>
<br clear="all"><br>

*/
		if (count($coinlist))	// if some coins found in list
		{
			$coin_list = '';
			foreach ($coinlist as $coinIdx => $value)	// loop through sections of coin IDs
				$coin_list .= $value[1].', ';			// make a list of coin IDs
			$coin_list = substr($coin_list, 0, -2);		// rid of start
		} else
			$coin_list = '0';	// dummy list if no coins found
		// echo '<pre>'; print_r($coin_list); echo '</pre>'; exit;

		// $coin_list = '395014, 395009, 395008, 395012, 395019, 399503, 396771, 395870, 395020, 395021, 387984, 391817, 398477, 387913, 387914, 387980, 387952, 399523, 399518, 399524, 399525, 399514, 399513, 399512, 399461, 396951, 396649, 399552, 399544, 399527, 397974, 398110, 398127, 399956, 399957, 399958, 399959, 395876, 395873, 396961, 399459, 399673, 398475, 398466, 398449, 399088, 399098, 399096, 399081, 388920, 388878, 399718, 396459, 395620, 395621, 399084, 399715, 399679, 399686, 399687, 399704, 396510, 396515, 390487, 390482';	// coins to return, and in the order they're desired
		// SELECT *, CONCAT('http://www.greatcollections.com/Coin/',project_id,'/') AS 'link', CONCAT('http://www.greatcollections.com/image/400/268/',project_id,'-1.jpg') AS 'image', project_title AS 'title', currentprice, bids FROM ilance_projects WHERE project_id IN (395620)
		$sql = "SELECT project_id, project_title, currentprice, buyer_fee, bids FROM ".DB_PREFIX."projects ".
			"WHERE project_id IN ($coin_list) AND project_state='product' AND visible='1' ".
			"ORDER BY FIELD(project_id, $coin_list);";
		// echo 'SQL = '.$sql; exit;
		$select_featured = $ilance->db->query($sql);
		$itemTitles = '';	// init accumulating titles, for back-plugging to parent frame's textarea via JavaScript
		$itemHTML = '';		// init accumulating HTML
		$itemText = '';		// init accumulating plain text
		$itemCCE = '';
		$lineCnt = 0;
		$itemHTML .= '<table width="100%"><tr>';					// start the table and a new line
//		if ($outtype == 'cu')	// if CU special HTML
//			$itemHTML .= '<table width="100%"><tr>';				// start the table and a new line
		while ($row = $ilance->db->fetch_array($select_featured))	// for all coins found in database
		{
			$itemTitles .= $row['project_id']."\t".HTMLentities($row['project_title'])."\n";
			// echo '<pre>'; print_r($row); echo '</pre>'; continue;
			foreach ($coinlist as $coinIdx => $value)	// loop through sections of coin IDs
			{
				if ($row['project_id'] == intval($value[1]))	// if found this coin in submitted coin list
					break;
			}
			if (trim($coinlist[$coinIdx][2]))	// if the user has submitted a title
				$item_title = trim($coinlist[$coinIdx][2]);	// use it
			else
				$item_title = $row['project_title'];			// otherwise use title from database
			// $ilance->db->num_rows($select_featured)
			if (($lineCnt % 2) == 0)	// force a line break every 2 coins
			{
				if ($outtype == 'html')	// if Email HTML, start a new line
					$itemHTML .= '</tr><tr><td><br /></td></tr><tr>';			// start a new line using tables
			}
			$image = 'http://GreatCollections.com/image/400/268/'.$row['project_id'].'-1.jpg';
			$itemHTML .= '<td width="50%" style="font-family:tahoma,arial,sans-serif;font-size:14px;padding-right: 10px;">
<a href="http://www.greatcollections.com/Coin/'.$row['project_id'].'" style="text-decoration:none" target="_blank">
<div style="border-radius: 15px; overflow: hidden;float:left; margin-right: 10px;">
<img src="'.$image.'" width="100" alt="[Coin Photo]

" align="left" style="Xpadding-right: 10px;white-space: pre;"></div>
<strong>'.$item_title.'</strong>
<br><br><br>
<span style="color:rgb(153,0,0);font-size:14px"><strong>';
			for ($idx = 3; $idx <= 3 && trim($coinlist[$coinIdx][$idx]); $idx++)	// for all possible and assigned extra lines
				$itemHTML .= HTMLentities($coinlist[$coinIdx][$idx]).'<br>';
			$itemHTML .= 'Sold $'.number_format($row['currentprice']+$row['buyer_fee'],0).'
</strong>
</td>';
/*
			$itemHTML .= '<span style="font-family:tahoma,arial,sans-serif;color:rgb(0,0,0);font-size:14px">
<a href="http://www.greatcollections.com/Coin/'.$row['project_id'].'" style="color:black;text-decoration:none" target="_blank">
<img src="'.$image.'" width="100" alt="[Coin Photo]


" align="left" style="padding-right: 10px;Xwhite-space: pre;">
<strong>'.HTMLentities($row['project_title']).'</strong>
<br><br><br>
<span style="color:rgb(153,0,0);font-size:14px"><strong>
PCGS Price Guide at $735.<br>
Sold $'.number_format($row['currentprice'],0).'.
</strong></span>
<br clear="all"><br>';
*/
			$lineCnt++;
			continue;
			
			foreach ($coinlist as $section => $value)	// loop through sections of coin IDs
			{
				if ($outtype == 'html')	// if Email HTML, format using CSS inline styles
				{
					$itemHTML .= 
	'<div style="width: 145px; display: inline-block; padding: 4px; vertical-align: text-top;">'.showItem($row, $outtype, 140, 20).'</div>

	';
				} else {				// if special CU HTML, format using old attributes
					$itemHTML .= 
	'<td width="145" align="center" valign="top">'.showItem($row, $outtype, 140, 20).'</td>

	';
				}
				if ((++$lineCnt % 4) == 0)	// force a line break every 4 coins, not trusting the browser to wrap properly
				{
					if ($outtype == 'html')	// if Email HTML, start a new line
					{
						$itemHTML .= '<br />
	';
					} else					// if CU special HTML
						$itemHTML .= '</tr><tr>';			// start a new line using tables
				}
			}
			// echo 'Test'; exit;

			$itemText .= showItem($row, $outtype);
			$itemCCE .= showItem($row, $outtype);
		}
		// $itemHTML .= '<!--LC='.$lineCnt.'-->';
		while (($lineCnt++ % 2) != 0)	// run out the remainder of 4 coin blocks after end,
		{
			$itemHTML .= '<td></td>';
		}
		$itemHTML .= '</tr></table>';		// close the table
		unset($select_featured);		// close database recordset
	}
	if ($outtype == 'html' || $outtype == 'cu')	// if either HTML email or CU special HTML
	{
		// start of HTML page output
		?>
		<html>
		<head>
		<style type="text/css">
			body, div, td, span, font {
				font-family: sans-serif;
				font-size: 12px;"
			}
		</style>		
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
		</head>
		<body>

		<a href="#" id="copylink" style="font-family: sans-serif; font-size: 10px;" onClick="HTML2clipboard();">
		Copy this <strong><?php echo ($outtype=='html'?'HTML Email':'Collector\'s Universe Special HTML'); ?></strong> to clipboard
		</a>
		<?php if ($outtype == 'cu') { ?>
			<br />
			<span style="font-family: sans-serif; font-size: 10px;">
			The HTML sample below looks significantly different than it will look on the Collector's Universe site
			</span>
		<?php } ?>
		<textarea id="copytemp" style="width: 0px; height: 0px; border: 0px solid white;"></textarea>
		<textarea id="backplug" style="width: 0px; height: 0px; border: 0px solid white;"><?php echo @$itemTitles; ?></textarea>
		<!--<input type="text" id="copytemp" value="" style="width: 0px; border: 0px solid white;" />-->
		<br /><div id="htmlcontent"><?php
		if ($outtype == 'cu')	// if CU special HTML
		{
			// echo '<code>';	// As of 2017, CU no longer requires this
			$top = file_get_contents('http://www.greatcollections.com/email2016/cu2016top.html');	// read email top
		} else
			$top = file_get_contents('http://www.greatcollections.com/email2016/email2016top.html');	// read email top
		if ($referal_name)
			$replaceStr = '$1'.$referal_name;
		else
			$replaceStr = '';
		$top = preg_replace("#([\?&]referal_name=)#",$replaceStr,$top);
		echo $top;

		if ($outtype == 'html')	// if Email HTML
			email2016_1col_float('<span style="font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 22px;">'.$title.'</span>', $outtype);
		else					// if CU HTML
			email2016_1col_float('<font size="5">'.$title.'</font><br /><font size="1">&nbsp;</font><br />', $outtype);

		// email2016_bg_1col('More Featured Coins at <strong>Great</strong>Collections:');
			// output a single column with textured background, HTML or plain text formatted

		if ($mailtype == 'postauction')		// for post-auction mailings, split the topbody into two parts
		{
			// echo '<PRE>'; print_r($topbody); echo '<PRE>'; exit;
			$topbody = preg_split("/[_]{10,1000}/", $topbody, 2);	// split on a lone line of underscores
			// echo '<PRE>'; print_r($topbody); echo '<PRE>'; exit;
			$itemHTMLFeature = trim(@$topbody[1]);	// second half goes to column 2
			$topbody = trim($topbody[0]);				// first half stays in column 1
			// echo '<PRE>'; echo "topbody:\n"; print_r($topbody); echo "\n\nitemHTMLFeature:\n"; print_r($itemHTMLFeature); echo '<PRE>'; exit;
		}
		email2016_2col100_1(trimBottomWhiteHTML($topbody), @$itemHTMLFeature, $outtype);
			// output two columns, HTML or plain text formatted

		email2016_bg_1col($coinlisttitle, $outtype);
			// output a single column with textured background, HTML or plain text formatted

		email2016_1col_float($itemHTML, $outtype);
			// output one column, floating width, HTML or plain text formatted

		if (trim($bottomtitle))	// if there's any bottom title
		{
			email2016_bg_1col($bottomtitle, $outtype);
				// output a single column with textured background, HTML or plain text formatted
				
			email2016_1col_float('<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td align="left">'.trimBottomWhiteHTML($bottombody).'</td></tr></table>', $outtype);
				// output one column, floating width, HTML or plain text formatted. FORCE LEFT JUSTIFICATION
		}
		if ($outtype == 'cu')
		{
			include('http://www.greatcollections.com/email2016/cu2016bottom.html');
			// echo '</code>';	// As of 2017, CU no longer requires this
		} else
			include('http://www.greatcollections.com/email2016/email2016bottom.html');
		?></div>
		</body>
		</html>
		<?php
	} else if ($outtype == 'text')	// if plain text email
	{
		// echo '<div style="width: 540px; text-align: center; font-family: sans-serif; color: white; background-color: #333; border: 1px solid black;">Plain Text Email</div>';
		echo '<pre>
';
		echo 
'View this email in your browser:
[[view_this_message]]
'.str_repeat('_',TEXTLENGTH).'

'.htmlentities(strip_tags($title)).'
'.str_repeat('_',TEXTLENGTH).'

';
		echo htmlentities(HTMLtoPlain($topbody)).''.str_repeat('_',TEXTLENGTH).'

';
		echo htmlentities(wrapText($itemText));	// wrap plain text

		if (trim($bottomtitle))	// if there's any bottom title
		{
			echo str_repeat('_',TEXTLENGTH).'

'.htmlentities(strip_tags($bottomtitle)).'
'.str_repeat('_',TEXTLENGTH).'

'.htmlentities(HTMLtoPlain($bottombody)).'';
		}
		htmlentities(readfile('http://www.greatcollections.com/email2016/email2016bottom.txt'));
		echo '</pre><br />';
		exit;
	} else if ($outtype == 'cce')	// if CCE plain text
	{
		echo '<div style="width: 540px; text-align: center; font-family: sans-serif; color: white; background-color: #333; border: 1px solid black;">Certified Coin Exchange</div>';
		echo '<pre>'.htmlentities($itemCCE).'</pre><br />';
		exit;
	}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function email2016_bg_1col($body, $outtype='html')	// output a single column with textured background, HTML or plain text formatted

{
?>			<!-- Single Column, Textured Dark Background -->
<?php if ($outtype == 'html') { ?>
			<table style="width: 100%; border-top: 1px solid #252c44; text-align: center; background: #252c44 url(https://www.greatcollections.com/email2016/marble_tile.jpg) repeat;
				background-position: -100px 0px;" border="0" cellpadding="2" cellspacing="2">
				<tr>
					<td align="center" valign="top" style="padding: 10px;">
						<span style="font-family: Tahoma,Arial,sans-serif; color: white; font-size: 20px;">
<?php echo $body; ?>
						</span>
<?php } else { ?>
			<table width="100%" align="center" bgcolor="#252c44" border="0" cellpadding="2" cellspacing="2">
				<tr>
					<td align="center" valign="top">
						<font color="white" size="5">
<?php echo $body; ?>
						</span>
<?php } ?>
					</td>
				</tr>
			</table>
<?php
}
function email2016_1col_float($body, $outtype='html')	// output one column, floating width, HTML or plain text formatted
{
?>			<!-- Single Column, floating width -->
<?php if ($outtype == 'html') {		// Email HTML ?>
			<div style="text-align: center; padding: 8px;	font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 12px;">
<?php echo $body; ?>
			</div>
<?php } else {	// Collector's Universe special HTML ?>
			<center>
<?php echo $body; ?>
			</center>
<?php } ?>
<?php
	return;
}

function email2016_2col100_1($body1, $body2, $outtype='html')	// output two columns, HTML or plain text formatted
{
	global $referal_name1, $mailtype;

?>		<!-- Two Columns 100%/?% -->
<?php if ($outtype == 'html') {		// Email HTML ?>
			<table style="width: 100%; border-top: 1px solid #656c84;" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top" style="padding: 10px;">
						<span style="font-family: Tahoma,Arial,sans-serif; font-size: 14px;">
<?php echo $body1; ?>
						</span>
					</td>
					<td valign="top" <?php echo ($mailtype=='preauction'?'align="center"':(trim($body2)?'width="33%"':'')); ?> style="padding: 10px;<?php echo ($mailtype=='postauction'?' background-color: #8b8ea9; border-left: 1px solid #656c84;':''); ?>">
						<span style="font-family: Tahoma,Arial,sans-serif; font-size: 13px;">
<?php echo $body2; ?>
						</span>
<?php } else {	// Collector's Universe special HTML ?>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top">
<?php echo $body1; ?>
					</td>
					<td valign="top" align="center">
<?php echo $body2; ?>
<?php } ?>
					</td>
				</tr>
			</table>
<?php
}

function showItem($row, $outtype = 'html', $width = 140, $corners = 20)	// output a single coin, format width and corner roundness, HTML or plain text formatted
{
	global $referal_name1;
	
	$link = 'http://GreatCollections.com/Coin/'.$row['project_id'].'/'.$referal_name1;
	$image = 'http://GreatCollections.com/image/400/268/'.$row['project_id'].'-1.jpg';

	// echo '$outtype = '.$outtype.'<br />';
	if ($outtype == 'html') {
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
	} else if ($outtype == 'cu') {
		return 
'<a href="'.$link.'">
<img src="'.$image.'" width="'.$width.'" alt="[Coin Photo]


" style="white-space: pre;" /><br />
'.HTMLentities($row['project_title']).'
<br />
<i>Current Bid $'.number_format($row['currentprice'],2,'.',',').
($row['bids']>0?' <nobr>('.$row['bids'].' Bid'.($row['bids']==1?'':'s').')</nobr>':'').
'</i>
</a>';
	} else {	// if plain text email or CCE
		return
HTMLentities($row['project_title']).'
Current Bid $'.number_format($row['currentprice'],2,'.',',').
($row['bids']>0?' ('.$row['bids'].' Bid'.($row['bids']==1?'':'s').')':'').
'
'.$link.'

';
	}
}

function trimBottomWhiteHTML($html)	// strip any divs at the bottom that only produce whitespace
{
// <div style="text-align: left; margin: 10px; font-family: Tahoma,Arial,sans-serif; color: #000; font-size: 14px;">&nbsp;</div>
	// $html = preg_replace("#(<(div|p)[^>]*>((&nbsp;)|(<br ?/? *>)|([\s]+)|(<[^>]*>))+</(div|p)>[\s]*)+$#si",'', $html);
	return($html);
}

function HTMLtoPlain($html, $isCCE = False)	// convert HTML to plain text
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
	$text = wrapText($text, $isCCE);	// wrap it
	return $text;
}

function wrapText($text, $isCCE = False)	// wrap plain text
{
	$text = wordwrap($text, $isCCE?TEXTLENGTH_CCE:TEXTLENGTH);
	return $text;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>
