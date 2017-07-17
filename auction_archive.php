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
	'rfp',
	'search',
	'accounting',
	'buying',
	'selling',
	'subscubscription',
	'feedback'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	/* 'ajax', */
	'inline',
	'cron',
	/* 'autocomplete', */
	/* 'jquery', */
	/* 'modal', */
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'main');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
$ilance->bid_proxy = construct_object('api.bid_proxy');

// #### require shipping backend #######################
require_once(DIR_CORE . 'functions_shipping.php');

date_default_timezone_set('America/Los_Angeles');

if (True)	// debugging mode for PHP and MySQL
{
	error_reporting(E_ALL);
	ini_set('display_errors', 'stdout');
	restore_error_handler();
	mysqli_report(MYSQLI_REPORT_STRICT);  // MYSQLI_REPORT_ALL also fails on warnings, like that an index isn't available to optimize a query
}
define('THIS_SCRIPT',HTTP_SERVER.'/auction_archive.php');
define('ISMARK',($_SERVER['SERVER_SOFTWARE'] == 'Microsoft-IIS/7.5' && $_SERVER['APPL_PHYSICAL_PATH'] == 'C:\\GC\\preProduction\\'));	// set if Mark devel server
define('UNGRADED','<span style="font-size: 18px;">Ungraded/Details</span>');
// define('UNGRADED','<img src="'.HTTP_SERVER.'/images/Ungraded.png" width="23" height="44" />');
// define('UNGRADED','<div style="text-align: right; position: absolute; top: 35px; left: -25px; font-size: 14px; font-weight: bold; -ms-transform: rotate(270deg); -webkit-transform: rotate(270deg); transform: rotate(270deg);">Ungraded</div>&nbsp;&nbsp;');
define('MAX_COINS_TREE', 16);			// maximum coins displayable in nav tree, after that they need to go into main body pane
define('COINS_SELECT_COLUMNS', 4);		// number of columns in the coin selector
define('TOP_COIN_HIST', 6);				// # of top coins to list in the price history area
// define('TOP_COIN_PRICE', 10);		// THIS IS NOW FIXED TO 'TOP_COIN_HIST' ... # of top coins to randomly select from, when automatically generating featured coin for any category

// list of all universe IDs and descriptions
$uvList = array(
	1=>'US Coin Prices',
	2=>'World Coin Prices',
	3=>'US and World Currency Prices'
);
// list of all universe IDs and item name
$uvListItem = array(
	1=>'Coin',
	2=>'Coin',
	3=>'Currency'
);
$uvListItemS = array(
	1=>'Coins',
	2=>'Coins',
	3=>'Currency'
);
// mapping of denomination IDs to universe IDs, or default to "1" (US Coins)
$uvListMap = array(
	30=>2, /* World and Ancient Coins=>World Coin Prices */
	34=>3, /* Currency=>US and World Currency Prices */
);

$gradeList = array(
	1=>'PO',
	2=>'FR',
	3=>'AG',
	4=>'G',
	6=>'G',
	8=>'VG',
	10=>'VG',
	12=>'F',
	15=>'F',
	20=>'VF',
	25=>'VF',
	30=>'VF',
	35=>'VF',
	40=>'XF',
	45=>'XF',
	50=>'AU',
	53=>'AU',
	55=>'AU',
	58=>'AU',
	60=>'MS/PR',
	61=>'MS/PR',
	62=>'MS/PR',
	63=>'MS/PR',
	64=>'MS/PR',
	65=>'MS/PR',
	66=>'MS/PR',
	67=>'MS/PR',
	68=>'MS/PR',
	69=>'MS/PR',
	70=>'MS/PR'
);
// echo count($gradeList); exit;

// echo '<PRE>'; print_r($_GET); echo '</PRE>'; exit;

/*
Old Auction Archive link
	// Universe: http://www.greatcollections.com/CoinPrices/
	// Denom: http://www.greatcollections.com/CoinPrices/2/Half-CentsLarge-Cents
	// Coin Series: http://www.greatcollections.com/CoinPrices/SeriesCoin/6/Classic-Half-Cents

In index.php, replace 1 "CoinPrices/SeriesCoin" near 478:

$new_list[] = array( 'url'=>'/^([Cc]+)oinPrices\/SeriesCoin\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'series'=>'$2',
		'ended'=>'1',
		'listing_type'=>'4',
		'sold'=>'1',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );

... and replace 4 "CoinPrices" near 531:

$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rices$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rices\/$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rice$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rice\/$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
WITH THIS:
$new_list[] = array( 'url'=>'#^CoinPrices?(/(SeriesCoin))?(/([0-9]+)/([^/]+))?/?$#i',
	'file'=>'auction_archive.php',
	'line'=>__LINE__,
	'parameters'=>array(
		'oldlink'=>'1',
		'seriescoin'=>'$2',
		'id'=>'$4',
		'description'=>'$5',
	));
*/

if (@$_GET['oldlink'])	// if an old Auction Archive link
{
	// echo '<PRE>'; print_r($_GET); echo '</PRE>'; // exit;
	$coinID = 0;								// never a specific PCGS number in old links
	if (empty($_GET['id']))						// if this is the top level old-school auction archive link
	{
		$coin_seriesID = 0;
		$denominationID = 0;
	}
	if (@$_GET['seriescoin'] == 'SeriesCoin')	// if this will be second level, coin series link
	{
		$coin_seriesID = intval(@$_GET['id']);
		$denominationID = cs2denom($coin_seriesID);	// get denomination ID from coin series number
		if (empty($denominationID))				// if could not determin denom ID from coin series ID
		{
			$denominationID = 0;				// nothing there
			$coin_seriesID = 0;					// and coin series is useless, too
		}
	} else {
		$denominationID = intval(@$_GET['id']);	// otherwise given ID is denom ID 
		$coin_seriesID = 0;						// and there's coin series
	}

	$universeID = denom2univ($denominationID);	// get universe ID from denomination

	// now that we've mapped old link to new link, jam the URL params back in
	// ... amounting to a silent redirect
	$_GET['universe'] = $uvList[$universeID];
	$_GET['denomination'] = $denominationID;
	$_GET['coin_series'] = $coin_seriesID;
	$_GET['coin'] = $coinID;

	// If rather than a silent redirect, we want a status code of 301:
	// header('HTTP/1.1 301 Moved Permanently');
	// header('Location: http://www.new-url.com');
}

/*
	figure out where we are in the navigation
	Sample URI: Auction-Archive/US-Coins/4/Small-Cents/17/Lincoln-Cents
	$_GET['universe'] = 'US-Coins';
	$_GET['denomination'] = '4';	// Small-Cents
	$_GET['coin_series'] = '17';	// 1909-1957 Lincoln Cents
	$_GET['coin'] = '3379';			// 1957 Lincoln Cent
	
http://127.0.0.1/gc/Auction-Archive/US-Coins/4/Small-Cents/17/1909-1957-Lincoln-Cents/3379/1957-Lincoln-Cent
http://127.0.0.1/gc/Auction-Archive/US-Coins/4/Small-Cents/15/Flying-Eagle-Cents/2016/1857-Flying-Eagle-Cent/
http://127.0.0.1/gc/Auction-Archive/US-Coins/8/Half-Dimes/27/Capped-Bust-Half-Dimes/4276/1829-Capped-Bust-Half-Dime
*/

// input and global variables, mostly from URL. These are also needed in descBody and histBody functions
$AucArchiveURI = 'Auction-Archive';
$universeDesc = nameURLdecode(@$_GET['universe']);
$universeID = array_search($universeDesc, $uvList);	// find universe ID by description match
if ($universeID == FALSE)	// if not found, default
{
	$universeID = 0;		// to no universe
	$universeDesc = '';
	/*
	$universeDesc = 'US Coin Prices';	// default to US Coins
	$universeID = 1;
	*/
}
$denominationID = intval(@$_GET['denomination']);
$denominationDesc = '';	// long denom
$coin_seriesID = intval(@$_GET['coin_series']);
$coin_seriesDesc = '';	// coin series
$coinID = intval(@$_GET['coin']);
$coinDesc = '';
$catRow = array();
$coinRow = array();

if (!$universeID)		// if no universe even given
{
} else if ($denominationID)
{
	$sqlField = "SELECT *, den.denomination_unique_no AS den_id";
	$sqlTable = "FROM ".DB_PREFIX. "catalog_toplevel den";
	$sqlWhere = "WHERE den.denomination_unique_no='$denominationID'";
	// ORDER BY den.denomination_sort"
	if ($coin_seriesID)
	{
		$sqlField .= ", cs.coin_series_unique_no AS cs_id";
		$sqlTable .= " LEFT JOIN ".DB_PREFIX. "catalog_second_level cs ON cs.coin_series_denomination_no=den.id";
		$sqlWhere .= " AND cs.coin_series_unique_no='$coin_seriesID'";
	}
	// echo 'SQL = '."$sqlField $sqlTable $sqlWhere"; exit;
	$select = $ilance->db->query("$sqlField $sqlTable $sqlWhere");
	// echo 'Num rows = '.$ilance->db->num_rows($select); exit;
	$catRow = $ilance->db->fetch_array($select);
	unset($select);		// this doesn't work: $ilance->db->free_result($select);
	// echo '<PRE>'; print_r($catRow); echo '</PRE>'; exit;
	$denominationDesc = @$catRow['denomination_long'];	// get long denom from database
	$coin_seriesDesc = @$catRow['coin_series_name'];	// get coin series from database
}
// echo 'coinID = '.$coinID; exit;
if ($coinID)
{
	$sql = "SELECT * FROM ".DB_PREFIX. "catalog_coin cc WHERE cc.PCGS='$coinID'";
	// echo 'SQL = '."$sql"; exit;
	$select = $ilance->db->query($sql);
	// echo 'Num rows = '.$ilance->db->num_rows($select); exit;
	$coinRow = $ilance->db->fetch_array($select);
	unset($select);		// this doesn't work: $ilance->db->free_result($select);
	// echo '<PRE>'; print_r($coinRow); echo '</PRE>'; exit;
	list($coinDesc) = titleFromFields($coinRow);	// get coin title from database
	// echo '<PRE>'; print_r($coinDesc); echo '</PRE>'; exit;
}

if ($nodeID = @$_GET['ajaxnode'])			// if this is a call for a new AJAX jsTree navigation node
{
	outputTreeHTML($nodeID);				// generate HTML tree code for this node
}

$page_title = SITE_NAME . ' - ' . 'PCGS Coin Auction Archive';
$meta_desc = '';
$page_head = '';
$page_desc = '';
$navcrumb = array($AucArchiveURI.'/' => nameURLdecode($AucArchiveURI));

if ($universeID)	// if URL provides the universe (US Coins, World Coins, etc)
{
	$page_title .= ' > '.$universeDesc;
	$meta_desc .= $universeDesc;
	$navcrumb[$AucArchiveURI.'/'.nameURLencode($universeDesc).'/'] = $universeDesc;
	if ($denominationDesc)	// if URL provides the denomination
	{
		$page_title .= ' > '.$denominationDesc;
		$meta_desc = $denominationDesc.' - '.$meta_desc;
		if (!$coin_seriesDesc)	// only need the denom if no coin series given in the URL
			$page_desc .= $denominationDesc.' - ';
		$navcrumb[$AucArchiveURI.'/'.nameURLencode($universeDesc).'/'.$denominationID.'/'.nameURLencode($denominationDesc).'/'] = $denominationDesc;
		// $navcrumb['Auction-Archive/'.$_GET['universe'].'/'.$_GET['denomination'].'/'] = $denominationDesc;
		if ($coin_seriesDesc)	// if URL provides the coin series
		{
			$page_title .= ' > '.$coin_seriesDesc;
			$meta_desc = $coin_seriesDesc.' - '.$meta_desc;
			if (!$coinDesc)	// only need the coin series if no final coin given in the URL
				$page_desc .= $coin_seriesDesc.' - ';
			$navcrumb[$AucArchiveURI.'/'.nameURLencode($universeDesc).'/'.$denominationID.'/'.nameURLencode($denominationDesc).'/'.$coin_seriesID.'/'.nameURLencode($coin_seriesDesc).'/'] = $coin_seriesDesc;
			// $navcrumb['Auction-Archive/'.$_GET['universe'].'/'.$_GET['denomination'].'/'.$_GET['coin_series'].'/'] = $coin_seriesDesc;
			if ($coinDesc)	// if URL provides the universe (US Coins, World Coins, etc)
			{
				$page_title .= ' > '.$coinDesc;
				$meta_desc = $coinDesc.' - '.$meta_desc;
				$page_desc .= $coinDesc.' - ';
				$navcrumb[$AucArchiveURI.'/'.nameURLencode($universeDesc).'/'.$denominationID.'/'.nameURLencode($denominationDesc).'/'.$coin_seriesID.'/'.nameURLencode($coin_seriesDesc).'/'.$coinID.'/'.nameURLencode($coinDesc).'/'] = $coinDesc;
			}
		}
	}
	// this shouldn't happen, since it should default to US Coins
	// if (!$universeDesc)
	//	$page_head = 'Auction Archive';
	// else
	$page_head = $page_desc.$universeDesc.' from our Auction Archive';
	$page_desc = substr($page_desc, 0, -3);
} else	// if nothing defined, show top header title
	$page_head = $page_desc.'Auction Archive';

if (@$_GET['ajaxdesc'])	// if this is a call for a new AJAX description body
{
	descBody();
	// now put some title information into hidden elements, so it can be extracted and placed where it is needed
	?>
	<span id="page_head_feed" style="display: none;"><?php echo $page_head; ?></span>
	<span id="page_title_feed" style="display: none;"><?php echo $page_title; ?></span>
	<?php
	exit;
}
if (@$_GET['ajaxhist'])	// if this is a call for a new AJAX history body
{
	histBody();
	exit;
}

$show['widescreen']=true;

$scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);

// $pprint_array = array('html','login_include','c'); 

$surfing_user_id=isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0?$_SESSION['ilancedata']['user']['userid']:0;

/*

$pprint_array = array('grading_service1','grading_service2','listing','pcgs_no','prof','count','grade_range1','grade_range2','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
$ilance->template->fetch('main', 'pcgs_coin_auction_archive_search.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
$ilance->template->parse_loop('main', 'listpage');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
*/
$headinclude .= '
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="functions/javascript/jquery-3.2.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="functions/javascript/jquery.cookie.js"></script>
<script src="functions/javascript/jquery.debounce-1.0.5.js"></script>
<script src="functions/javascript/jstree/jstree.min.js"></script>
<link rel="stylesheet" href="functions/javascript/jstree/themes/default/style.min.css" />
<style type="text/css">
.tree li a ins {
	display:none !important;
} 
</style>
';

// $metakeywords = stripslashes($res['project_title']) . ','.$series_details['coin_series_name'].',' . $denomination_detail['denomination_long'].',coin auctions,great collections,rare coins,greatcollections,auction coins';
// $metadescription=stripslashes($res['project_title']) . ' ' .'(' . $phrase['_item'] . ' ' . intval($ilance->GPC['id']) . ') | ' . SITE_NAME . ' Coin Auctions';

// set metatags
$meta_desc = 'What is the worth of my '.$page_desc.'? ';
if (isset($coinRow['coin_detail_description_short']))
	$meta_desc .= $coinRow['coin_detail_description_short'];
else if (isset($coinRow['coin_detail_description_long']))
	$meta_desc .= $coinRow['coin_detail_description_long'];
else if (isset($catRow['coin_series_description_short']))
	$meta_desc .= $catRow['coin_series_description_short'];
else if (isset($catRow['coin_series_description_long']))
	$meta_desc .= $catRow['coin_series_description_long'];
else if (isset($catRow['denomination_synopsis']))
	$meta_desc .= $catRow['denomination_synopsis'];
else if (isset($catRow['denomination_article']))
	$meta_desc .= $catRow['denomination_article'];
else
	$meta_desc = '';	// flag to leave default meta description

$ilconfig['template_metatitle'] = $page_title;
// $ilconfig['template_metakeywords']="test_meta_keyword";	// use default keywords
if ($meta_desc)			// only if we have something useful, do we override default site meta description
	$ilconfig['template_metadescription'] = strip_tags($meta_desc).' | '.SITE_NAME.' Coin Auctions';

$pprint_array = array('page_title','headinclude');
$ilance->template->construct_header('main');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
?>

<script type="text/javascript" language="Javascript">
	$(function () {
		ensureFilterURL();	// make sure the URL contains the filter parameters
		// hybrid static and AJAX HTML tree definition: http://stackoverflow.com/questions/30968424/jstree-html-and-json
		var static_html = $('#navtree').html();
		$('#navtree').jstree({
			'core' : {
				"check_callback" : true,
				"data" : function (node, cb) {
					if (node.id === "#") {
						// alert(node.id);
						cb(static_html);
					} else {
						// enhance the AJAX call as needed (verb, data)
						// alert("<?php echo THIS_SCRIPT; ?>?ajaxnode=" + node.id);
						$.ajax({ url : "<?php echo THIS_SCRIPT; ?>?ajaxnode=" + node.id })
							.done(function (data) {
								// alert(data);
								cb(data);
								ensureFilterURL();	// make sure the URL contains the filter parameters
							});
					}
				}
				/*
				'data' : {
					"url" : "<?php echo THIS_SCRIPT; ?>?ajaxnode=1",
					'data' : function (node) {
						// alert(node.id);
						return { 'id' : node.id };
					}
				}
				*/
			}
		});
		$('#navtree').on('select_node.jstree', function (e, data) {
			// alert(data.node.id);
			var level = nodeIDbreakout(data.node.id);	// break nodeID into a level and a database ID
			var dbID = level.dbID;
			if (level.level = 3 && (level.type == 'm1' || level.type == 'm2'))	// Is this a "...more..." nav node?  "L3m1_322"
			{
				// alert(data.node.parent);
				$('#navtree').jstree('select_node', data.node.parent);			// select parent node
				$('#navtree').jstree('deselect_node', data.node.id);			// de-select "more" node
				return;
			}
			$('#navtree').jstree('open_node', data.node.id);					// open current node tree if not already
			level = level.level;

			clearAllNavHightlights();	// clear all arrows and "current" indications

			var IDs = [];				// init IDs
			var titles = [];			// init titles
			var urls = [];				// init urls
			var parentsID = data.node.parents;	// get array of parent IDs
			parentsID.unshift(data.node.id);	// force last level to beginning of this array
			for (var idx = 0; idx < parentsID.length && parentsID[idx] != '#'; idx++)	// loop through current (0) and all parents
			{
				var level = nodeIDbreakout(parentsID[idx]);	// get level, break its ID into a level and a database ID
				var dbID = level.dbID;
				level = level.level;
				if (level < 0)			// if descended below root, stop now
					break;
				IDs[level] = dbID;
				var name = $('#L'+level+'t_'+dbID)[0].innerText;
				titles[level] = name;
				urls[level] = (level>0? dbID + '/': '') + nameURLencode(name) + '/';
				// '<?php echo $AucArchiveURI; ?>/'
				// alert(urls[level]);
			}
			/*
			IDs[0] = 0;	// TODO!  US-Coins!!!!
			titles[0] = '<?php echo nameURLdecode($AucArchiveURI); ?>';
			urls[0] = '<?php echo $AucArchiveURI; ?>/';
			*/
			// Now we have info for all levels
<?php /*
<div style="padding-top:4px; padding-bottom:12px; padding-left:10px; font-size:10px; font-family:verdana">
	<span class="blue"><a href="http://127.0.0.1/gc/" rel="nofollow">Home</a></span>
	<span class="breadcrumb">&nbsp;&gt; <span class="blue"><a href="Auction-Archive/" rel="nofollow">PCGS Coin Auction Archive</a></span></span>
	<span class="breadcrumb">&nbsp;&gt; <span class="blue"><a href="Auction-Archive/US-Coins/" rel="nofollow">US Coins</a></span></span>
	<span class="breadcrumb">&nbsp;&gt; <span class="blue"><a href="Auction-Archive/US-Coins/4/" rel="nofollow">Small Cents</a></span></span>
	<strong><span class="breadcrumb">&nbsp;&gt; <span class="blue"><a href="Auction-Archive/US-Coins/4/17/" rel="nofollow">1909-1957 Lincoln Cents</a></span></span></strong>
</div>
*/ ?>
			var crumbDiv = $('.breadcrumb')[0].parentNode;	// get <div> that encapsulates breadcrumbs
			var patt = /^([\s\S]*?)<span class="breadcrumb">[\s\S]*$/;	// regexp pattern to claim everything after "home" breadcrumb trail
			var crumbDiv2 = patt.exec(crumbDiv.innerHTML);				// get just beginning of original crumb
			crumbDiv2 = crumbDiv2[1]+'<span class="breadcrumb">&nbsp;&gt; <span class="blue"><a href="<?php echo $AucArchiveURI; ?>/" rel="nofollow"><?php echo nameURLdecode($AucArchiveURI); ?></a></span></span>';
				// store original crumb, plus base Auction Archive
			var urlAccum = '<?php echo $AucArchiveURI; ?>/';	// init full URL to new page
			for (var idx = 0; idx < IDs.length; idx++)	// loop through all levels
			{
				// alert(nodes[idx]);
				urlAccum += urls[idx];				// accumulate full URL
				crumbDiv2 += 
				'<span class="breadcrumb">&nbsp;&gt; <span class="blue"'+(idx+1 == IDs.length?' style="font-weight: bold;"':'')+'><a href="'+urlAccum+'" rel="nofollow">'+htmlEscape(titles[idx])+'</a></span></span>';
					// add breadcumb node to breadcrumb code
				// L1a_32982
				if (idx > 0)						// for all levels hiiger than first
				{
					$('#L'+idx+'s_'+IDs[idx])[0].style.color = '#600';	// text is dark red
					$('#L'+idx+'s_'+IDs[idx])[0].style.fontWeight = 'bold';	// text is bold
					if (idx+1 == IDs.length)		// only put arrow on highest level
						$('#L'+idx+'a_'+IDs[idx])[0].style.display = '';	// show arrow nodes
				}
			}
			// alert(crumbDiv2);
			crumbDiv.innerHTML = crumbDiv2;
			<?php // http://stackoverflow.com/questions/28601879/html5-refresh-page-when-popstate-is-fired ?>
			window.historyInitiated = true;
			history.pushState(null, null, urlAccum);
			window.addEventListener("popstate", function(e) {
				if (window.historyInitiated) {
					window.location.reload();
				}
			});
			$.ajax({ url : urlAccum + "?ajaxdesc=1"}) 		// AJAX call for a new descripton body
				.done(function (data) {
					ensureFilterURL();	// make sure the URL contains the filter parameters
					$('#descbody')[0].innerHTML = data;		// replace description body
					$('#page_head')[0].innerHTML = $('#page_head_feed')[0].innerText;	// updated page header title that was tucked into a hidden field of descbody
					document.title = $('#page_title_feed')[0].innerText;		// update page <title> from a hidden field of descbody
					updFeatureInHist();		// update featured coin as it appears in history body
					// alert(data);
			});
			histFresh(urlAccum);							// AJAX call for a new history body
		});
		<?php // https://stackoverflow.com/questions/25066499/pushstate-and-back-button-works-but-content-doesnt-change ?>
		$(window).bind('popstate', function(){				// force page reload for any back/forward button use
			window.location.href = window.location.href;
		});
		updFeatureInHist();		// update featured coin as it appears in history body
	});
	function histFresh(urlAccum)							// AJAX call for a new history body
	{
		if (!urlAccum)										// if no URL given, use current/default URL
		{
			urlAccum = window.location.href.split("?")[0].split("#")[0];
		}
		$.ajax({ url : urlAccum + "?ajaxhist=1"})
			.done(function (data) {
				ensureFilterURL(true);	// make sure the URL contains the filter parameters, force override of cookie values
				$('#histbody')[0].innerHTML = data;
				updateNoItemsMsg($('#closedcnt')[0].innerHTML, $('#opencnt')[0].innerHTML);
					// update messages if there are no items (pull values stored in hidden <div>s)
				histInit();		// re-init various history JavaScripts in histbody
				updFeatureInHist();		// update featured coin as it appears in history body
				// alert(data);
		});
	}
	function updateNoItemsMsg(soldCnt, upcomingCnt)	// update messages if there are no items (this won't execute from within AJAX loaded HTML)
	{
		if (soldCnt+upcomingCnt == 0)		// if no entries at all
		{									// show the messages laying-in-wait to the footer
			$('#noclosed2')[0].style.display = '';
			$('#noopen2')[0].style.display = '';
		} else if (soldCnt == 0)			// if just sold count is empty
		{									// move message in the top spot from it lies in wait in the bottom spot
			$('#noclosed')[0].innerHTML = $('#noclosed2')[0].innerHTML;
		} else if (upcomingCnt == 0)		// if just upcoming count is empty
		{									// move message in the top spot from it lies in wait in the bottom spot
			$('#noopen')[0].innerHTML = $('#noopen2')[0].innerHTML;
		}
	}
	
	$.jstree.defaults.core.themes.variant = 'small';<?php // this found method works with iLance header handling of jQuery/jsTree ?>

	/*
	<?php // this official method only works outside of iLance header handling of jQuery/jsTree ?>
	$("#navtree").jstree({
		"core" : {
			"themes" : {
				"variant" : "small"
			}
		}
	});	
	$('#navtree').jstree('hide_icons');


	$('#navtree')
	.on('changed.jstree', function (e, data) {
		alert('why?');
		var i, j, r = [];
		for (i = 0, j = data.selected.length; i < j; i++) {
			r.push(data.instance.get_node(data.selected[i]).text);
		}
		$('#event_result').html('Selected: ' + r.join(', '));
	})


	$('#navtree').jstree({ 'core' : {
		'data' : [
		   'Simple root node',
		   {
			 'text' : 'Root node 2',
			 'state' : {
			   'opened' : true,
			   'selected' : true
			 },
			 'children' : [
			   { 'text' : 'Child 1' },
			   'Child 2'
			 ]
		  }
		]
	} });
	*/
	function clearAllNavHightlights()
	{
		// L1a_32982
		var nodes = $('span:regex(id,^L[0-9]+a_[0-9]+$)');	// find all arrow nodes
		for (var i = 0; i < nodes.length; i++)
		{
			nodes[i].style.display = 'none';				// turn them off
		}
		// L1s_32982
		var nodes = $('span:regex(id,^L[0-9]+s_[0-9]+$)');	// find all nodes with nav titles, for their text styling
		for (var i = 0; i < nodes.length; i++)
		{
			nodes[i].style.color = '#000';
			nodes[i].style.fontWeight = '';
		}
	}
	function nodeIDbreakout(nodeID)		// break nodeID into a level and a database ID
	{
		var patt = /^L([0-9]+)([A-za-z]*[0-9]*)_([0-9]+)$/;	// regexp pattern to separate level and node ID numbers
		var aNode = patt.exec(nodeID);	// get level and node ID
		return {
			level: aNode[1],
			dbID: aNode[3],
			type: aNode[2]
		};
	}
	function nameURLencode(name)						// convert name to URL format
	{
		return name.trim().replace(/[ \/]/g,'-');			// convert any spaces to hyphens
	}
	<?php // https://j11y.io/javascript/regex-selector-for-jquery/ ?>
	jQuery.expr[':'].regex = function(elem, index, match) {
		var matchParams = match[3].split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels) ? 
					matchParams[0].split(':')[0] : 'attr',
			property: matchParams.shift().replace(validLabels,'')
		},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	}
	function htmlEscape(str)
	{
		return str
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}
	<?php 	// AJAX spinner from - http://blog.oio.de/2010/11/08/how-to-create-a-loading-animation-spinner-using-jquery/
			// Generate spinner at http://ajaxload.info/ ?>
	$(function(){
		var spinnerCnt = 0;
		$(document).ajaxSend(function() {
			$("#spinner").show();
			spinnerCnt++;			// count spinners open
			setTimeout(function(){
				$("#spinner").hide();	// close and reset
				spinnerCnt = 0;
			}, 5000);
		}).ajaxStop(function() {
			if (--spinnerCnt <= 0);	// dec spinners open, if last one
			{
				$("#spinner").hide();	// close and...
				spinnerCnt = 0;			// make sure we're not in fantasyland
			}
		}).ajaxError(function() {
			if (--spinnerCnt <= 0);	// dec spinners open, if last one
			{
				$("#spinner").hide();	// close and...
				spinnerCnt = 0;			// make sure we're not in fantasyland
			}
		});
		/*
		$("#spinner").bind("ajaxSend", function() {
			$(this).show();
		}).bind("ajaxStop", function() {
			$(this).hide();
		}).bind("ajaxError", function() {
			$(this).hide();
		});
		*/
	});
	
	$( function() {
		histInit();	// init various history JavaScripts in histbody
	});	
	
	<?php // Allow both clicking and hovering, albiet by using separate elements: http://jsfiddle.net/arunpjohny/aQqHz/ from https://stackoverflow.com/a/15664974/5348048 ?>
	function histInit()	// init various history JavaScripts in histbody
	{
		$( document ).tooltip({
			items: "[data-imgpopup]",
			content: function() {
				var element = $(this)[0].dataset;
				return '<div id="imgpopup" title="View Image" style="text-align: center; height: 400px;">'+
					'<a id="imgpopup_link" href="'+element.href+'">'+
					'<img id="imgpopup_img" src="<?php echo HTTP_SERVER; ?>/image/400/268/'+element.id+'-1.jpg" onError="this.src=\'images/gc/nophoto.gif\'" />'+
					'<div id="imgpopup_title">'+element.title+'</div>'+
					'</a>'+
					'</div>';
			}
		});
		/*
		var tooltips = $('.imgpopuphref').tooltip().click(function(){
			// $(this).parent().tooltip('open');
		});
		*/
	}
	<?php // API call in format: http://www.greatcollections.com/preferences.php?cmd=favorites&subcmd=save&fav=Archive&mode=product&pcgs=3922&series=23&denomination=7&q=&sort=1&ajax=1
	// This can also be a POST. Fields pcgs and series are optional. Additional usable fields are: title, grade_range_1, grade_range_2

	?>
	function addSavedAJAX(button_e, title, uv_id, den_id, cs_id, cn_id, grade_min, grade_max, ungraded)<?php // save this search ?>
	{
		var already = false;

		if (already)
			var msg = 'You have already saved this search.';
		else {
			var msg = '';
			// var msg = '<span style="color: #900; font-weight: bold;">Successfully saved to your searches.</span>';

			$.get('preferences.php?cmd=favorites&subcmd=save&fav=Archive&mode=product&title='+title+'&pcgs='+cn_id+'&series='+cs_id+'&denomination='+den_id+
			'&grade_range_1='+grade_min+'&grade_range_2='+grade_max+'&ungraded='+ungraded+'q=&sort=1&ajax=1', function() {
					msg = 'Successfully saved to your searches.';
				})
				.fail(function() {
					msg = 'There was a problem saving.';
				});
			/*
			$.ajax({ url : 'preferences.php?cmd=favorites&subcmd=save&fav=Archive&mode=product&title='+title+'&pcgs='+cn_id+'&series='+cs_id+'&denomination='+den_id+'&q=&sort=1&ajax=1'}) 		// AJAX call for a new descripton body
				.success(function (data) {
					msg = 'Saved to your searches.';
				.error(function (data) {
					msg = 'There was a problem saving.';
			});
			*/
		}
		var backuptitle = button_e.title;	// save original tool tip
		button_e.title = msg;
		$(button_e).tooltip({
			close: function( event, ui ) {
				button_e.title = backuptitle;	// restore original tool tip
			},
			/*
			content: function() {
				return msg;
			}
			*/
		});
		$(button_e).tooltip('open');
		setTimeout(function(){
			$(button_e).tooltip('close');
		}, 3000);
	}
	function ensureFilterURL(forceCookie = false)	// make sure the URL contains the filter parameters
	{
		var URLparams = window.location.search.substring(1);			// get browser URL parameters
		if (!URLparams || forceCookie)	// if no params in URL, or we're supposed to force the cookie over the URL
		{
			URLparams = $.cookie('filter');								// get cookie of parameters
			if (URLparams)												// if there are cookie params in URL
				history.pushState(null, null, window.location.href.split("?")[0].split("#")[0]+'?'+URLparams);
					// put them in the browser's URL, after stripping out any existing (?) params or anchor (#)
		}
	}
	function updFeatureInHist()		// update the featured coin if/when it appears in the history body
	{
		// id="histid_9999"
		var nodes = $('td:regex(id,^histid_[0-9]+$)');		// find all history nodes
		for (var i = 0; i < nodes.length; i++)
		{
			nodes[i].style.outline = 'none';				// turn off outlines
		}
		var id = $('#featured')								// see if there's a featured record
		if (!id.length)										// if no featured, end now
			return;
		id = id[0].getAttribute('data-id');					// get ID of featured record
		if ($('#histid_'+id).length)						// if featured ID exists in history
		{
			$('#histid_'+id)[0].style.backgroundColor = '#DDF';	// change its color
			$('#featured')[0].style.backgroundColor = '#DDF';	// change color of featured image text
		} else
			$('#featured')[0].style.backgroundColor = 'transparent';	// no color on featured image text if no matching history found
	}
	/*
	function imgPopup(id, title, link)
	{
		$('#imgpopup_link')[0].href = link;
		$('#imgpopup_title')[0].innerText = title;
		$('#imgpopup_img')[0].src = '<?php echo HTTP_SERVER; ?>/image/400/268/'+id+'-1.jpg';
		
		//When the image has loaded, display the dialog
		$('#imgpopup').dialog({
			modal: true,
			resizable: false,
			draggable: false,
			dialogClass: "no-title",
			hide: {
				effect: "scale"
			},
			show: {
				effect: "scale"
			},
			width: 'auto',
			title: title
		});
	}
	*/

</script>

<div id="spinner" class="spinner" style="display:none;">
    <img id="img-spinner" src="<?php echo HTTP_SERVER; ?>/images/ajax-loader.gif" alt="Loading"/>
</div>

<style type="text/css">
.spinner {
	position: fixed;
	top: 50%;
	left: 50%;
	margin-left: -50px; /* half width of the spinner gif */
	margin-top: -50px; /* half height of the spinner gif */
	text-align:center;
	z-index:1234;
	overflow: auto;
	width: 100px; /* width of the spinner gif */
	height: 102px; /*hight of the spinner gif +2px to fix IE8 issue */
}

a.nolink {
	text-decoration: none;
}
a.nolink:link {
    color: inherit;
}
a.nolink:visited {
    color: inherit;
}

button.button:active {
	outline: none !important;
	box-shadow: 0px 0px 0px #000 !important;
	position: relative;
	top: 1px;
	left: 1px;
}

.tree li a ins {
	display:none !important;
}<?php // this official method only works outside of iLance header handling of jQuery/jsTree ?>

i.jstree-icon.jstree-themeicon {
	display:none !important;
}<?php // this found method works with iLance header handling of jQuery/jsTree ?>

.jstree-node {
    Xfont-size: 13pt;
}
<?php // http://stackoverflow.com/questions/24746781/how-do-i-get-a-jstree-node-to-display-long-possibly-multiline-content ?>
.jstree-default a { 
    white-space:normal !important; height: auto; 
}
.jstree-anchor {
    height: auto !important;
}
.jstree-default li > ins { 
    vertical-align:top; 
}
.jstree-leaf {
    height: auto;
}
.jstree-leaf a{
    height: auto !important;
}
</style> 

<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
<td valign="top" style="width: 300px; padding: 4px;">
	<div id="navtree" style="border: 1px solid #CCC; padding-right: 20px;">
		<ul>
			<?php
			outputTreeHTML('', $universeID, $denominationID, $coin_seriesID, $coinID); 	// generate initial HTML tree code
			?>
		</ul>
	</div>
</td>
<td valign="top" style="Xwidth: 99%; padding-left: 10px; border: 0px solid black;">
	<h2 id="page_head" style="font-size: 18px; margin-top: 4px;"><?php echo $page_head; ?></h2>
	<div id="descbody"><?php descBody(); ?></div>
	<br />
	<div><?php filterSelector(); ?></div>
	<div id="histbody"><?php histBody(); ?></div>
</td>
</tr>
</table>
<?php
// echo '<PRE>'; print_r($_GET); echo '</PRE>';
$pprint_array = array();
$ilance->template->construct_footer('main');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

function outputTreeHTML($AJAXnode = '', $universeID = -1, $denominationID = -1, $coin_seriesID = -1, $coinID = -1)	// generate HTML tree code
{	// AJAXnode=0: generate whole initial tree, opened to current position. AJAXnode=Node Name: Re-generate given node, and define one level below
	global $ilance;
	global $uvList;
	global $uvListMap;

	global $AucArchiveURI;
	global $universeID;
	global $universeDesc;
	global $denominationID;
	global $denominationDesc;
	global $coin_seriesID;
	global $coin_seriesDesc;
	global $coinID;
	global $coinDesc;
	global $catRow;
	global $coinRow;

	if ($AJAXnode)
	{
		list($level, $dbID) = nodeIDbreakout($AJAXnode);					// break nodeID into a level and a database ID
		switch ($level) {
		case 0:		// universe (Should never happen)
			die('outputTreeHTML() called with specific universe');
			/*
			$universeID = $dbID;
			$denominationID = 0;
			$coin_seriesID = 0;
			$coinID = 0;
			*/
			break;
		case 1:		// denom
			$denominationID = $dbID;
			$universeID = denom2univ($denominationID);				// get universe ID from denomination
			$coin_seriesID = 0;
			$coinID = 0;
			break;
		case 2:		// series
			// We know the series number, but need to find the denomination ID above it
			$denominationID = cs2denom($dbID);					// get denomination ID from coin series number
			if (empty($denominationID))
				die('outputTreeHTML() could not determin denom ID from coin series ID');
			$universeID = denom2univ($denominationID);				// get universe ID from denomination
			$coin_seriesID = $dbID;
			$coinID = 0;
			break;
		case 3:		// specific PCGS # (Should never happen)
			die('outputTreeHTML() called with specific PCGS #');
			/*
			$universeID = 1;
			$denominationID = ;
			$coin_seriesID = $dbID;
			$coinID = 0;
			*/
			break;
		}
	} else {
		$level = -1;
		$dbID = -1;
	}

	if ($AJAXnode && $level >= 1)		// if we're getting one node, and it's at least a denomination node
		$SQLwhere = 'WHERE denomination_unique_no='.$denominationID;
	else
		$SQLwhere = '';

	$sql = denom2univSQL('denomination_unique_no');	// get SQL Where clause for universe ID, based on denomination

	$sql = "SELECT * FROM ".DB_PREFIX. "catalog_toplevel den $SQLwhere ORDER BY	$sql, denomination_sort";
		// order by most coins (1) first, then "World and Ancient Coins" (30), then Currency (34)
	// echo 'SQL = '.$sql; exit;
	$select = $ilance->db->query($sql);
	// echo 'Num rows = '.$ilance->db->num_rows($select); exit;
	$last_uv_id = 0;
	while ($denNavRow = $ilance->db->fetch_array($select))
	{
		// echo '<PRE>'; print_r($denNavRow); echo '</PRE>'; exit;
		$den_id = $denNavRow['denomination_unique_no'];	// get ID of this denom row
		$uv_id = @$uvListMap[$den_id];		// map universe from this demonination
		if (empty($uv_id))					// if none found in map
			$uv_id = 1;						// default to US Coin
		$uv_desc = $uvList[$uv_id];			// and get corresponding description
		if ($universeID)					// if the universe is known
			$onThisUVnode = ($universeID == $uv_id);	// are we currently on this Universe's node?
		else
			$onThisUVnode = True;			// if universe is unknown, force all Universe nodes to be open?
		if ($AJAXnode && $level >= 1)		// if we're getting one node, and it's deeper than the universe
			; // then we skip the actual universe output
		else {
			if ($uv_id != $last_uv_id)	// if new universe ID
			{
				if ($last_uv_id != 0)
				{
					?></ul></li><?php
				}
				?>
				<li id="L0_<?php echo $uv_id; ?>" class="<?php echo ($onThisUVnode?'jstree-open':'jstree-closed'); ?>"><span id="L0t_<?php echo $uv_id; ?>" style="font-size: 16px; font-weight: bold;"><?php echo $uv_desc; ?></span>
				<ul>
				<?php
				$last_uv_id = $uv_id;	// remember last universe ID
			}
		}
		$onThisDNnode = ($denominationID == $den_id);
		if ($AJAXnode && $level >= 2)		// if we're getting one node, and it's deeper than the denomination
			; // then we skip the actual denomination output
		else {
			?>
			<li id="L1_<?php echo $den_id; ?>" class="<?php echo ($onThisDNnode?'jstree-open':'jstree-closed'); ?>">
				<span id="L1s_<?php echo $den_id; ?>" style="<?php echo (!$AJAXnode && $onThisDNnode?'color: #600;font-weight: bold;':''); ?>">
				<span id="L1t_<?php echo $den_id; ?>"><?php
					if (!$AJAXnode)
					{
						$den_url = HTTP_SERVER.$AucArchiveURI.'/'.construct_seo_url_name($universeDesc).'/'.$den_id.'/'.construct_seo_url_name($denNavRow['denomination_long']);
						echo '<a class="nolink" href="'.$den_url.'">';
					}
					echo ilance_htmlentities($denNavRow['denomination_long']);
					if (!$AJAXnode)
						echo '</a>';
				?></span>
				(<?php echo $denNavRow['auction_count']+$denNavRow['auction_count_hist']; ?>)<?php
				?>&nbsp;<span id="L1a_<?php echo $den_id; ?>" style="<?php echo (!$AJAXnode && $onThisDNnode && !$coin_seriesID?'':'display: none;');	?>">&#10148;</span>
				</span>
			<?php
		}
		if ($onThisDNnode)	// if this is a denomination tree node that should be open (0=no denom open)
		{
			if ($AJAXnode && $level >= 2)		// if we're just getting a single node, and it's at least a coin series node
				$SQLwhere = 'cs.coin_series_unique_no='.$coin_seriesID.' AND ';
			else
				$SQLwhere = '';
			$sql = "SELECT * FROM ".DB_PREFIX. "catalog_second_level cs WHERE $SQLwhere cs.coin_series_denomination_no=$denominationID ORDER BY cs.coin_series_sort";
			// echo 'SQL = '.$sql; exit;
			$selectL2 = $ilance->db->query($sql);
			// echo 'Num rows = '.$ilance->db->num_rows($selectL2); exit;
			if ($ilance->db->num_rows($selectL2))	// if any rows
			{
				?>
				<ul>
				<?php
				while ($csNavRow = $ilance->db->fetch_array($selectL2))
				{
					$cs_id = $csNavRow['coin_series_unique_no'];	// get ID of this coin series row
					$sql = "SELECT COUNT(id) AS qty_coins FROM ".DB_PREFIX. "catalog_coin cc WHERE cc.coin_series_unique_no=$cs_id";
						// get # of coins in this coin series
					// echo 'SQL = '.$sql; exit;
					$selectL2q = $ilance->db->query($sql);
					// echo 'Num rows = '.$ilance->db->num_rows($selectL2q); exit;
					if ($res = $ilance->db->fetch_array($selectL2q))
						$qty_coins = $res['qty_coins'];		// get # of coins in this coin series
					else
						$qty_coins = 0;
					unset($selectL2q);
					$onThisCSnode = ($coin_seriesID == $cs_id);	// set if this is a coin series tree node that should be open (0=no coin series open)
					?>
					<li id="L2_<?php echo $cs_id; ?>" class="<?php echo ($onThisCSnode?'jstree-open':'jstree-closed'); ?>" style="">
						<span id="L2s_<?php echo $cs_id; ?>" style="<?php echo (!$AJAXnode && $onThisCSnode?'color: #600;font-weight: bold;':''); ?>">
						<span id="L2t_<?php echo $cs_id; ?>"><?php
							if (!$AJAXnode)
							{
								$cs_url = '/'.$cs_id.'/'.construct_seo_url_name($csNavRow['coin_series_name']);
								echo '<a class="nolink" href="'.$den_url.$cs_url.'">';
							}
							echo ilance_htmlentities($csNavRow['coin_series_name']);
							// if ($cs_id==128) { echo '<PRE>'; print_r($csNavRow); echo '</PRE>'; exit; }
							if (!$AJAXnode)
								echo '</a>';
						?></span>
						(<?php echo $csNavRow['auction_count']+$csNavRow['auction_count_hist']; ?>)<?php
						?>&nbsp;<span id="L2a_<?php echo $cs_id; ?>" style="<?php echo (!$AJAXnode && $onThisCSnode && !$coinID?'':'display: none;');	?>">&#10148;</span>
						</span>
					<?php
					// $coin_seriesDesc = @$catRow['coin_series_name'];	// get coin series from database - NOT SURE WHY THIS WAS HERE
					if ($onThisCSnode)	// if this is a coin series tree node that should be open (0=no coin series open)
					{
						$isCutCoins = ($qty_coins > MAX_COINS_TREE);	// set if too many coins to display in nav tree
						$sql = "SELECT * FROM ".DB_PREFIX. "catalog_coin c1 WHERE c1.coin_series_unique_no=$cs_id";
						if ($isCutCoins)
						{
							// get the Orderno of the center primary catalog_coin record
							$sqlCut = "SELECT Orderno FROM ".DB_PREFIX. "catalog_coin cc WHERE cc.PCGS=$coinID";
							$selectCut = $ilance->db->query($sqlCut);
							if ($rowCut = $ilance->db->fetch_array($selectCut))
								$Orderno = $rowCut['Orderno'];
							else
								$Orderno = 0;
							unset($selectCut);

							$sql = "SELECT * FROM ($sql AND c1.Orderno <= $Orderno ORDER BY c1.Orderno DESC LIMIT ".intval(MAX_COINS_TREE/2).") AS c1b UNION
								(".str_replace('c1','c2',$sql)." AND c2.Orderno > $Orderno ORDER BY c2.Orderno ASC LIMIT ".intval(MAX_COINS_TREE/2).")";

							// http://stackoverflow.com/questions/30214003/select-records-around-a-specific-record-from-database
							// SELECT * FROM (SELECT * FROM x WHERE z >= n ORDER BY z LIMIT 10) a UNION (SELECT * FROM x WHERE z < n ORDER BY z DESC) LIMIT 10 
						}
						$sql .= " ORDER BY Orderno";
						if ($isCutCoins)
							$sql .= " LIMIT ".MAX_COINS_TREE;
						// echo 'SQL = '.$sql; exit;
						$selectL3 = $ilance->db->query($sql);
						// echo 'Num rows = '.$ilance->db->num_rows($selectL3); exit;
						if ($ilance->db->num_rows($selectL3))	// if any rows
						{
							?>
							<ul>
							<?php
							if ($isCutCoins)
							{
								?>
								<li id="L3m1_<?php echo $cs_id; ?>" style="font-style: italic;">...more...</li>
								<?php
							}
							while ($cnNavRow = $ilance->db->fetch_array($selectL3))
							{
								$cn_id = $cnNavRow['PCGS'];		// get ID of this coin series row
								list($coinDescL3) = titleFromFields($cnNavRow);	// get coin title from database
								// echo '<PRE>$coinDescL3='.$coinDescL3.'<BR>'; print_r($cnNavRow); echo '</PRE>'; exit;

								$onThisCNnode = ($coinID == $cn_id);	// set if this is a coin tree node that should be selected (0=no coin open)
								?>
								<li id="L3_<?php echo $cn_id; ?>" class="<?php echo '';	// ($onThisCNnode?'jstree-open':'jstree-closed') ?>" style="">
									<span id="L3s_<?php echo $cn_id; ?>" style="<?php echo (!$AJAXnode && $onThisCNnode?'color: #600;font-weight: bold;':''); ?>">
									<span id="L3t_<?php echo $cn_id; ?>"><?php

										if (!$AJAXnode)
										{
											$cn_url = '/'.$cn_id.'/'.construct_seo_url_name($coinDescL3);
											echo '<a class="nolink" href="'.$den_url.$cs_url.$cn_url.'">';
										}
										echo ilance_htmlentities($coinDescL3);
										if (!$AJAXnode)
											echo '</a>';
									?></span><?php
									$qty = $cnNavRow['auction_count']+$cnNavRow['auction_count_hist'];
									if ($qty)
										echo ' ('.$qty.')';	// $cnNavRow['PCGS'].'-'.$cnNavRow['auction_count'].','.$qty_coins.'/'.$coinID
									?>&nbsp;<span id="L3a_<?php echo $cn_id; ?>" style="<?php echo (!$AJAXnode && $onThisCNnode?'':'display: none;');	?>">&#10148;</span>
									</span>
								</li>
								<?php
							}
							if ($isCutCoins)
							{
								?>
								<li id="L3m2_<?php echo $cs_id; ?>" style="font-style: italic;">...more...</li>
								<?php
							}
							?>
							</ul>
							<?php
						}
						unset($selectL3);
					}
					?>
					</li>
					<?php
				}
				?>
				</ul>
				<?php
			}
			unset($selectL2);
		}
		?>
		</li>
		<?php
	}
	if ($last_uv_id != -1)	// if we should some universes, then close the last universe
	{
		?></ul></li><?php
	}
	unset($select);
	return;
}

function descBody()	// display description body in right pane
{
	global $ilance;

	global $AucArchiveURI;
	global $universeID;
	global $universeDesc;
	global $denominationID;
	global $denominationDesc;
	global $coin_seriesID;
	global $coin_seriesDesc;
	global $coinID;
	global $coinDesc;
	global $catRow;
	global $coinRow;
	
	global $page_desc;
	global $uvList;
	global $uvListItem;
	global $uvListItemS;
	?>
	
	<?php
	$itemName = $uvListItem[($universeID==0?1:$universeID)];	// get singular item name, default to coin
	$itemNameS = $uvListItemS[($universeID==0?1:$universeID)];	// get plural item name
	
	if ($coinID)				// if a specific PCGS number given
	{
		$sqlJoin = "";			// no join needed
		$sqlWhere = "p.cid=$coinID AND";	// limit search to specific PCGS number
	} else if ($coin_seriesID)	// if only a coin series number given, find a random PCGS number from the series
	{
		$sqlJoin = "";			// no join needed
		$sqlWhere = "p.coin_series_unique_no=$coin_seriesID AND";				// limit results to specific coin series number
	} else if ($denominationID)	// if only a denom given, find a random PCGS number from the series
	{
		$sqlJoin = "";			// no join needed
		$sqlWhere = "p.coin_series_denomination_no=$denominationID AND";		// limit results to specific denomination number
	} else if ($universeID)		// if only the coin universe given, map universe to denom(s)
	{
		$sqlJoin = "";			// no join needed
		$sqlWhere = denom2univList('p.coin_series_denomination_no',$universeID);
			// limit results to specific denomination numbers, mapped from universe ID
	} else						// if not even the coin universe given return everything
	{
		$sqlJoin = "";			// no join needed
		$sqlWhere = "";
	}
	$sql = "SELECT p.id, p.project_id, p.currentprice, p.buyer_fee, p.project_title,
		concat( 'uploads/attachments/auctions/', (
			floor( a.project_id /100 ) ) *100, '/', a.project_id, '/', a.filehash, '.attach'
		) AS imgpath
	FROM ".DB_PREFIX."projects p LEFT JOIN ".DB_PREFIX."attachment a ON a.project_id = p.project_id
	$sqlJoin
	WHERE $sqlWhere p.visible=1 AND p.status<>'open' AND p.haswinner=1
	ORDER BY currentprice DESC
	LIMIT ".TOP_COIN_HIST;
	// get title and image path for an item, in the top TOP_COIN_HIST realized prices, those within the last 6 months 4 times as "valuable"/likely to appear
	// This appended to ORDER BY currentprice is great, but takes almost 2 seconds: * IF(p.date_end > DATE_ADD(NOW(),INTERVAL -6 MONTH),4,1)

	// status from database, "open", "closed"=buynow sold, "expired"=auction sold
	// from Suku 6/15/2017: status 'closed' is for buynow items that are sold, successfully sold auction coins are "expired"
	// Suku 6/22/2017: filtered_auctiontype = 'regular'/'fixed' is best. BuyNow unsold is "filtered_auctiontype = 'fixed' AND buynow_qty>=1"

	// echo 'SQL = '.$sql; exit;

	$select = $ilance->db->query($sql, MYSQLI_STORE_RESULT);	// execute query, make sure we're storing results so we can seek through them
	// echo 'Num rows = '.$ilance->db->num_rows($select); exit;
	mysqli_data_seek($select,rand(0,$ilance->db->num_rows($select)));	// seek to random place in recordset of up to TOP_COIN_HIST rows

	$row = $ilance->db->fetch_array($select);
	unset($select);
	if ($row)		// if a coin record found
	{
		// echo '<PRE>'; print_r($row); echo '</PRE>'; exit;
		$title = $row['project_title'];	// get coin series from database
		if (ISMARK)	// if force remote sample image
			$image = 'http://www.greatcollections.com/image/400/268/'.$row['project_id'].'-1.jpg';
		else
			$image = HTTP_SERVER.'/image/400/268/'.$row['project_id'].'-1.jpg';
		// echo '$title='.$title.'<BR>$image='.$image.'<BR>';
		?>
		<div style="width: 200px; float: right; margin-left: 10px; border-left: 5px; text-align: center;">
		<a href="<?php echo HTTP_SERVER.'Coin/'.$row['project_id'].'/'.construct_seo_url_name($title); ?>" title="<?php echo ilance_htmlentities($title); ?>">
			<div style="border-radius: 15px; overflow: hidden;">
				<img src="<?php echo $image; ?>" alt="How much is my <?php echo ilance_htmlentities($title); ?> worth?" title="Auction prices for <?php echo ilance_htmlentities($title); ?>" width="200" />
			</div>
			<div id="featured" data-id="<?php echo $row['project_id']; ?>" style="border-radius: 10px; padding: 5px;">
				<?php echo noOrphan(ilance_htmlentities($title)); ?>
				</br>Sold for <?php echo $ilance->currency->format($row['currentprice']+$row['buyer_fee']); ?>
			</div>
		</a>
		</div>
		<?php
	}

	if (!$denominationID)	// if we're at the top page of the Auction Archive, or inside a Universe
	{
		$sql = "SELECT COUNT(*) total_count FROM ".DB_PREFIX."projects p $sqlJoin
		WHERE $sqlWhere p.visible=1 AND p.status<>'open' AND (p.haswinner=1 OR p.buynow_qty>0)";
		// get item count at this level
		// echo 'SQL = '.$sql; exit;

		$select = $ilance->db->query($sql);
		if ($select)
			$row = $ilance->db->fetch_array($select);
		$total_count = $row['total_count'];
		unset($select, $row);
		?><div class="body">
<h3>Welcome to the GreatCollections Auction Archive<?php echo ($universeID?' of '.$universeDesc:''); ?></h3>

The Auction Archive is a free resource provided by GreatCollections, which you can use to research <?php echo strtolower($itemName); ?> values in the most accurate way possible... based on real transactions.
<br /><br />
Searches <?php echo number_format($total_count); ?> certified <?php echo strtolower($itemNameS); ?> sold by GreatCollections<!--, worth hundreds of millions of dollars-->.  This archive is updated immediately as more <?php echo strtolower($itemNameS); ?> are auctioned by GreatCollections each week.
<br /><br />
Many <?php echo strtolower($itemName); ?> collectors, advanced numismatists and dealers use this <?php echo strtolower($itemName); ?> price guide as a resource for determining values of <?php echo strtolower($itemNameS); ?>, but please remember that the <?php echo strtolower($itemName); ?> market changes regularly and even two <?php echo strtolower($itemNameS); ?> graded by the same company at the same grade can sell for different prices based on quality, eye appear, rarity and other factors.
<br /><br />
You can also view our <a href="record_coin_prices.php">Record Prices for Rare <?php echo $itemNameS; ?></a>, showcasing some of the highest realizations for <?php echo strtolower($itemName); ?> auctions at GreatCollections.
<br /><br />
If you have any suggestions for our auction archive, please e-mail Ian Russell at <a href="mailto:ian@greatcollections.com">ian@greatcollections.com</a>.
		</div><br /><?php
	} else {	// find most useful description
		if (isset($coinRow['coin_detail_description_long']))
			$desc = $coinRow['coin_detail_description_long'];
		else if (isset($coinRow['coin_detail_description_short']))
			$desc = $coinRow['coin_detail_description_short'];
		else if (isset($catRow['coin_series_description_long']))
			$desc = $catRow['coin_series_description_long'];
		else if (isset($catRow['coin_series_description_short']))
			$desc = $catRow['coin_series_description_short'];
		else if (isset($catRow['denomination_article']))
			$desc = $catRow['denomination_article'];
		else if (isset($catRow['denomination_synopsis']))
			$desc = $catRow['denomination_synopsis'];
		else
			$desc = '';
	}
	// denomination_synopsis, denomination_article
	// coin_series_description_short, coin_series_description_long
	// coin_detail_description_short, coin_detail_description_long
	
	if (empty($desc))
	{
		$sql = "SELECT 
			MIN(GREATEST(p.Grade,1)) AS grade_min, MAX(p.Grade) AS grade_max,
			MIN(p.currentprice+p.buyer_fee) AS price_min, MAX(p.currentprice+p.buyer_fee) AS price_max,
			MIN(p.date_end) AS date_end_min, MAX(p.date_end) AS date_end_max,
			COUNT(*) AS total_count
		FROM ".DB_PREFIX."projects p $sqlJoin
		WHERE $sqlWhere p.visible=1 AND p.status<>'open' AND (p.haswinner=1 OR p.buynow_qty>0) AND p.currentprice > 0";
		// get item aggragates at this level. Note we don't want to consider a Grade of 0 for the grade range
		// echo 'SQL = '.$sql; exit;

		$select = $ilance->db->query($sql);
		if ($select)
		{
			$row = $ilance->db->fetch_array($select);
			// echo '<PRE>'; print_r($row); echo '</PRE>'; // exit;
			// &zwnj; - zero-width non-joiner character
			$date_end_min = date('F Y',strtotime($row['date_end_min']));
			$date_end_max = date('F Y',strtotime($row['date_end_max']));
			if (!$row['total_count'])	// if nothing was ever sold
				$desc .= '.';
			else {						// if something was sold
				$desc = 'GreatCollections '.($date_end_min == $date_end_max ? '':'has ').'offered '.number_format($row['total_count']).' such '.strtolower($itemNameS).' ';
				if ($date_end_min == $date_end_max)		// if start and end dates are the same
					$desc .= 'on '.$date_end_min;
				else
					$desc .= 'in the period between '.$date_end_min.' and '.$date_end_max;
				$desc .= ', selling ';
				// currency_format_whole
				// $ilance->currency->format($row['currentprice']+$row['buyer_fee'])
				// date('M Y',strtotime($row['date_end_min']))
				$price_min = currency_format_whole($row['price_min']);
				$price_max = currency_format_whole($row['price_max']);
				if ($price_min == $price_max)		// if prices are the same
					$desc .= 'for '.$price_min;
				else
					$desc .= 'at prices from '.$price_min.' to '.$price_max;
				$grade_min = $row['grade_min'];
				$grade_max = $row['grade_max'];
				if ($grade_min == $grade_max)		// if prices are the same
					$desc .= ', in grade '.$grade_min;
				else
					$desc .= ', in grades '.$grade_min.' to '.$grade_max;
				$desc .= '.';
				echo preg_replace("#\n#",'<br />',$desc).'<br /><br />';	// show any description, convert newlines to HTML line breaks
			}
			unset($select, $row);
		}
	} else {		// if there IS a description already stored
	}
	if (!empty($catRow['coin_series_designer']))
	{
		echo 'The design was made by: '.ilance_htmlentities($catRow['coin_series_designer']).'.<br /><br />';
	}
	if (!empty($coinRow['PCGS']))
	{
		echo 'The industry catalog number is '.ilance_htmlentities($coinRow['PCGS']).'.<br /><br />';
	}
	unset($select);	// close recordset

	?>
	<br /><br />
	<?php


	// start of major PCGS list selector, if needed
	$rowCnt = 0;						// count rows, also for indicating if anything was displayed at all
	if ($coin_seriesID && !$coinID)		// if a coin series number given, but no specific PCGS number given, we might need to show a selection of PCGS coins
	{
		$sql = "SELECT COUNT(id) AS qty_coins FROM ".DB_PREFIX. "catalog_coin cc WHERE cc.coin_series_unique_no=$coin_seriesID";
			// get # of coins in this coin series
		// echo 'SQL = '.$sql; exit;
		$select = $ilance->db->query($sql);
		// echo 'Num rows = '.$ilance->db->num_rows($selectL2q); exit;
		if ($res = $ilance->db->fetch_array($select))
			$qty_coins = $res['qty_coins'];		// get # of coins in this coin series
		else
			$qty_coins = 0;
		unset($select);
		if (True || $qty_coins > MAX_COINS_TREE)		// if there were too many coins to display in the nav tree, we'll need to break them out here
		{
			$sql = "SELECT * FROM ".DB_PREFIX. "catalog_coin c1 WHERE c1.coin_series_unique_no=$coin_seriesID ORDER BY Orderno";
			// echo 'SQL = '.$sql; exit;
			$select = $ilance->db->query($sql);
			// echo 'Number of PCGS within this series = '.$ilance->db->num_rows($select).'<br />'; // exit;
			?>
			<br clear="all" />
			<div style="font-size: 16px; font-weight: bold;">Please select an item from this <?php echo ($qty_coins>200?'extensive ':''); ?>series of <?php echo $page_desc; ?>:</div>
			<div style="max-height: 500px; overflow-y: auto; border: 1px solid #CCC; padding: 10px;">
			<table cellpadding="6"><tr>
			<?php
			$rowMax = ceil($qty_coins/COINS_SELECT_COLUMNS);	// get number of rows per column
			// echo '<PRE>'; print_r($catRow); echo '</PRE>'; exit;
			$base_url = HTTP_SERVER.$AucArchiveURI.'/'.construct_seo_url_name($universeDesc).'/'.
				$denominationID.'/'.construct_seo_url_name($denominationDesc).'/'.
				$coin_seriesID.'/'.construct_seo_url_name($coin_seriesDesc);
			while ($cnNavRow = $ilance->db->fetch_array($select))
			{
				$cn_id = $cnNavRow['PCGS'];		// get ID of this coin series row
				list($coinDescL3) = titleFromFields($cnNavRow);	// get coin title from database
				// echo '<PRE>$coinDescL3='.$coinDescL3.'<BR>'; print_r($cnNavRow); echo '</PRE>'; exit;

				$cn_url = '/'.$cn_id.'/'.construct_seo_url_name($coinDescL3);
				$qty = $cnNavRow['auction_count']+$cnNavRow['auction_count_hist'];
				if (($rowCnt % $rowMax) == 0)	// if time for a new column
				{
					if ($rowCnt)
						echo '</td>';
					echo '<td valign="top">';
				}
				echo '<a class="nolink" href="'.$base_url.$cn_url.'"'.($qty?' style="font-weight: bold"':'').'>';
				$cn_url = trim($coinDescL3);
				if ($qty)
					$cn_url .= ' ('.number_format($qty).')';
				echo noOrphan(ilance_htmlentities($cn_url));	// don't orphan anything
				echo '</a>';
				echo '<br />';
				$rowCnt++;
			}
			unset($select);
			if (($rowCnt % $rowMax) > 0)	// if there's a straggler column to close
				echo '</td>';
			?>
			</tr></table>
			</div>
			<?php
		}
	}

	if (!$rowCnt)	// if nothing was displayed in the dense coin selector above
	{
		// start of subcategory list selector
		// get array of category levels
		$catList = array();
		$catListQty = array();		// also need the quantities
		if ($coinID)				// if a specific PCGS number given
		{
									// keep existing empty array
		} else if ($coin_seriesID)	// if a coin series number given, find all PCGS numbers in the series
		{
			$level = 3;				// PCGS level
			$levelDesc = $uvListItem[$universeID].' within the Coin Series of '.$coin_seriesDesc;		// PCGS level
			$sql = "SELECT * FROM ".DB_PREFIX."catalog_coin WHERE coin_series_unique_no=$coin_seriesID ORDER by Orderno";
			// echo 'SQL = '.$sql; exit;
			$select = $ilance->db->query($sql);
			// echo 'Num rows = '.$ilance->db->num_rows($select); exit;
			while ($row = $ilance->db->fetch_array($select))	// loop through PCGS numbers, putting them in an array
			{
				// echo '<PRE>'; print_r($row); echo '</PRE>'; exit;
				 list($title) = titleFromFields($row);
				 $catList[$row['PCGS']] = $title;
				 $catListQty[$row['PCGS']] = $row['auction_count']+$row['auction_count_hist'];
			}
			unset($select);
			$baseURL = HTTP_SERVER.$AucArchiveURI.'/'.construct_seo_url_name($universeDesc).'/'.$denominationID.'/'.construct_seo_url_name($denominationDesc).'/'.$coin_seriesID.'/'.construct_seo_url_name($coin_seriesDesc).'/';
		} else if ($denominationID)	// if a denom given, find all coin series of the denom
		{
			$level = 2;				// coin series level
			$levelDesc = 'Coin Series within the Denominations of '.$denominationDesc;		// Series level
			$sql = "SELECT coin_series_unique_no, coin_series_name, auction_count, auction_count_hist FROM ".DB_PREFIX."catalog_second_level WHERE coin_series_denomination_no=$denominationID ORDER by coin_series_sort";
			$select = $ilance->db->query($sql);
			while ($row = $ilance->db->fetch_array($select))	// loop through series numbers, putting them in an array
			{
				$catList[$row['coin_series_unique_no']] = $row['coin_series_name'];
				$catListQty[$row['coin_series_unique_no']] = $row['auction_count']+$row['auction_count_hist'];
			}
			unset($select);
			$baseURL = HTTP_SERVER.$AucArchiveURI.'/'.construct_seo_url_name($universeDesc).'/'.$denominationID.'/'.construct_seo_url_name($denominationDesc).'/';
		} else if ($universeID)		// if a coin universe given, shows mapped denom(s)
		{
			$level = 1;				// denom level
			$levelDesc = 'Denomination within the Universe of '.$universeDesc;		// Denom level
			$sql = "SELECT denomination_unique_no, denomination_long, auction_count, auction_count_hist FROM ".DB_PREFIX."catalog_toplevel WHERE ".denom2univList('denomination_unique_no',$universeID)." 1=1 ORDER by denomination_sort";
				// get the universe's specific denomination numbers, mapped from universe ID
			$select = $ilance->db->query($sql);
			while ($row = $ilance->db->fetch_array($select))	// loop through denom numbers, putting them in an array
			{
				$catList[$row['denomination_unique_no']] = $row['denomination_long'];
				$catListQty[$row['denomination_unique_no']] = $row['auction_count']+$row['auction_count_hist'];
			}
			unset($select);
			$baseURL = HTTP_SERVER.$AucArchiveURI.'/'.construct_seo_url_name($universeDesc).'/';
		} else						// if not even the coin universe given, return universe list
		{
			$level = 0;				// universe level
			$levelDesc = 'Numismatic Universe';		// Univ level
			$catList = $uvList;		// simple universe list
			$baseURL = HTTP_SERVER.$AucArchiveURI.'/';
		}
		// echo '<PRE>Level = '.$level.'<br />'; print_r($catList); echo '</PRE>'; // exit;

		$qty_coins = count($catList);
		if ($qty_coins)			// if something in subcategory array
		{
			?>
			<br clear="all" />
			<h3>Choose a <?php echo $levelDesc; ?>:</h3>
			<!--
			<div style="font-size: 16px; font-weight: bold;">Please select an item from this <?php echo ($qty_coins>200?'extensive ':''); ?>series of <?php echo $page_desc; ?>:</div>
			-->
			<div style="Xmax-height: 500px; Xoverflow-y: auto; border: 1px solid #CCC; padding: 10px;">
			<table width="100%" cellpadding="6"><tr>
			<?php
			$rowMax = ceil($qty_coins/COINS_SELECT_COLUMNS);	// get number of rows per column
			// echo '<PRE>'; print_r($catRow); echo '</PRE>'; exit;
			foreach ($catList as $id=>$title)		// loop through all entries in subcategory
			{
				// echo '<PRE>'; print_r($title); echo '</PRE>'; exit;
				$qty = @$catListQty[$id];
				if (($rowCnt % $rowMax) == 0)	// if time for a new column
				{
					if ($rowCnt)
						echo '</td>';
					echo '<td valign="top" style="font-size: '.($qty_coins < 10 ? 16 : 14 ).'px;">';
				}
				echo '<a href="'.$baseURL.($level==0?'':$id.'/').construct_seo_url_name($title).'" title="'.ilance_htmlentities($title).'"'.($qty || $level==0 ?' style="font-weight: bold"':'').'">';
				if (!empty($catListQty))
					$title .= ' ('.number_format($catListQty[$id]).')';
				echo noOrphan(ilance_htmlentities($title));	// don't orphan anything
				echo '</a>';
				echo '<br />';
				$rowCnt++;
			}
			unset($select);
			if (($rowCnt % $rowMax) > 0)	// if there's a straggler column to close
				echo '</td>';
			?>
			</tr></table>
			</div>

			<!--
			<div style="font-size: <?php echo (count($catList) < 10 ? 16 : 14 ); ?>px;">
			<?php
			foreach ($catList as $id=>$title)		// loop through all entries in subcategory
			{
				// echo '<PRE>'; print_r($title); echo '</PRE>'; exit;
				// echo '$level='.$level.' '; 
				echo '<a href="'.$baseURL.($level==0?'':$id.'/').construct_seo_url_name($title).'" title="'.ilance_htmlentities($title).'">';
				echo ilance_htmlentities($title).'</a><br />';
				/*
				$url.$id.'/'.construct_seo_url_name($title);
				$url = HTTP_SERVER.$AucArchiveURI.'/';
				construct_seo_url_name($universeDesc).'/'.$den_id.'/'.construct_seo_url_name($denNavRow['denomination_long']);
				$cs_url = '/'.$cs_id.'/'.construct_seo_url_name($csNavRow['coin_series_name']);
				$cn_url = '/'.$cn_id.'/'.construct_seo_url_name($coinDescL3);
				global $denominationID;
				global $denominationDesc;
				global $coinID;
				global $coinDesc;
				*/
			}
			?>
			</div>
			-->
			<?php
		}
	}
	// echo '<PRE style="white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;">$catRow=<BR>'; print_r(@$catRow); echo '</PRE>';
	// echo '<PRE style="white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;">$coinRow=<BR>'; print_r(@$coinRow); echo '</PRE>';
	?>

	<!--
	<style type="text/css">
		.aa_list ul {
			margin: 1em 0;
			padding: 0 0 0 20px;
		}

		.aa_list li {
			list-style-type: disc;
		}
	</style>
	<h3>Additional Articles related to <?php echo ilance_htmlentities($page_desc); ?></h3>
	<strong><ul class="aa_list">
		<li class="aa_list">Lincoln Cent sells for $26,000 at GreatCollections!</li>
		<li class="aa_list">Circulated pennies can be worth more than you think</li>
		<li class="aa_list">Cents in the News</li>
	</ul></strong>
	-->
	<br clear="all" />
	<?php
}
function filterSelector()
{
	global $ilance;

	global $AucArchiveURI;
	global $universeID;
	global $universeDesc;
	global $denominationID;
	global $denominationDesc;
	global $coin_seriesID;
	global $coin_seriesDesc;
	global $coinID;
	global $coinDesc;
	global $catRow;
	global $coinRow;
	
	global $page_desc;
	global $uvList;
	global $uvListItem;
	global $uvListItemS;
	
	?>
	<style type="text/css">
		.ui-slider .ui-slider-handle {
			width:1.5em;
			left:-.6em;
			font-size: 16px;
			font-weight: bold;
			text-decoration:none;
			text-align:center;
		}
	</style>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #CCC; padding: 4px; background-color: #efe; margin-bottom: 10px;">
		<!--
		<tr>
			<td colspan="2" style="text-align: center; font-size: 16px; font-weight: bold;">
				<b>Filter Setting for Archive Results</b>
				<hr style="border: 0; height: 1px; background: #CCC;"
			</td>
		</tr>
		-->
		<tr>
			<td><b>Filter</b></td>
			<td width="100%">
				<span style="float: right; white-space: nowrap;">
				Sort by <label><input id="af_c_sorthigh" type="checkbox" checked />Highest Price</label>
				<label><input id="af_c_sortdate" type="checkbox" />Recent Sales</label>
				</span>

				<span style="white-space: nowrap;">
				<label>Year from <input id="af_s_yearfrom" type="text" size="6" /></label>
				<label>to <input id="af_s_yearto" type="text"  size="6" /></label>
				</span>
				&nbsp;
				<span style="white-space: nowrap;">
				<label>Keywords <input id="af_s_keywords" type="text"  size="20" /></label>
				</span>
				&nbsp;
				<span style="white-space: nowrap;">
				<label>Industry catalog number <input id="af_s_pcgs" type="text"  size="8" /></label>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr style="border: 0; height: 1px; background: #CCC;"
			</td>
		</tr>
		<tr>
			<td style="padding-right: 6px;"><b>Grader</b></td>
			<td width="100%">
				<span style="white-space: nowrap;">
				<label><input id="af_c_pcgs" type="checkbox" checked />PCGS</label>
				<label><input id="af_c_ngc" type="checkbox" checked />NGC</label>
				<label><input id="af_c_anacs" type="checkbox" checked />ANACS</label>
				<label><input id="af_c_other" type="checkbox" checked />Other</label>
				</span>
				&nbsp;
				<span style="white-space: nowrap;">
				<label><input id="af_c_cac" type="checkbox" checked />CAC</label>
				<label><input id="af_c_noncac" type="checkbox" checked />non-CAC</label>
				&nbsp;
				<label><input id="af_c_plus" type="checkbox" checked />Plus</label>
				<label><input id="af_c_nonplus" type="checkbox" checked />non-Plus</label>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="gradeslider" style="margin-bottom: 5px; margin: 10px; margin-right: 12px;"></div>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top;"><b>Grade</b></td>
			<td Xwidth="100%" style="vertical-align: middle; position: relative;">
				<div>
					<div style="float: right; padding-right: 10px;"><?php
					showAddSaved($universeID, $denominationID, $coin_seriesID, $coinID);
					?></div>
					<nobr><label><input id="af_c_ungraded" type="checkbox" />Ungraded/Details</label></nobr>
					&nbsp;
					<span style="white-space: nowrap;">
					<label><input id="af_c_busi_strike" type="checkbox" checked />Business Strike</label>
					<label><input id="af_c_spec_proof" type="checkbox" checked />Specimen Proof</label>
				</div>
			</td>
		</tr>
	</table>
	<script type="text/javascript" language="Javascript">
		var gradeSliderMap = [<?php
			global $gradeList;
			foreach ($gradeList as $grade=>$gradeName)
				echo $grade.($grade<70?', ':'');
		?>];
		var allowChange = false;	// suppress change AJAX call during initialization
		$('#gradeslider').slider({
			range: true,
			min: 0,
			max: gradeSliderMap.length-1,
			values: [ 0, gradeSliderMap.length-1 ],
			slide: function(event, ui) {	// as slider is moved
				filterSliderUpdate(ui);		// change handle text labels for every change, send ui for its new value
				allowChange = true;			// now that there's been real user interact, allow a change AJAX call
			},
			change: function(event, ui) {	// when slider change is actually done
				filterStoreSet();			// store new filter values
				if (allowChange)			// only allow AJAX if full initialized
					histFresh();			// AJAX call for a new history body
			}
		});
		<?php /*
		http://stackoverflow.com/questions/21534628/slider-value-display-with-jquery-ui-2-handles
		http://stackoverflow.com/questions/5800714/jquery-slider-with-value-on-drag-handle-slider
		https://gist.github.com/thiyagaraj/8804699 (customer step/snap points)
		*/
		?>
		function filterSliderUpdate(ui)	// update text buttons in slider
		{
			var value;
			if (ui && ui.handleIndex == 0)	// if we're getting a specific "slide" event call, and it's for the first slider
				value = ui.value;		// get direct, upcoming slider value
			else
				value = $("#gradeslider").slider("values", 0);

			$("#gradeslider").find(".ui-slider-handle:first").text(gradeSliderMap[value]);
			if (ui && ui.handleIndex == 1)	// if we're getting a specific "slide" event call, and it's for the second slider
				value = ui.value;		// get direct, upcoming slider value
			else
				value = $("#gradeslider").slider("values", 1);
			$("#gradeslider").find(".ui-slider-handle:last").text(gradeSliderMap[value]);
		}
		$(function () {
			filterStoreGet();	// initial setting of values from a cookie

			var timersChg = {};	// mS timers objects
			var timersInp = {};

			// set change handler for all the filter fields
			$('input:regex(id,^af_.*$)').on('change', function(event) {		// set a "change" handler for each
				if (Date.now()-timersInp[this.id] < 3000)	// if input event happened recently, supress the change event happening so soon after
				{
					// alert('Suppress change');
					return;
				}
				timersChg[this.id] = Date.now();	// remember when change happened, so we can supress an input happening soon thereafter
				if (this.id == 'af_c_sorthigh')				// if what's changed is the sort high function
					$('#af_c_sortdate')[0].checked = !this.checked;	// make sort by date the opposite setting
				else if (this.id == 'af_c_sortdate')		// if what's changed is the sort high function
					$('#af_c_sorthigh')[0].checked = !this.checked;	// make sort by date the opposite setting
				filterStoreSet();
				histFresh();							// AJAX call for a new history body
			});			

			// find all text input filter storage elements (which have debouncing because "input" event happens in addition to "change" above)
			$('input:regex(id,^af_s_.*$)').bind('input',$.debounce(function(event) {
				if (Date.now()-timersChg[this.id] < 3000)	// if change happened recently, supress the input happening so soon after
				{
					// alert('Suppress keypress');
					return;
				}
				timersInp[this.id] = Date.now();			// remember when change happened, so we can supress a "change" event happening soon thereafter
				if (this.id == 'af_c_sorthigh')		// if what's changed is the sort high function
					$('#af_c_sortdate')[0].checked = !this.checked;	// make sort by date the opposite setting
				else if (this.id == 'af_c_sortdate')		// if what's changed is the sort high function
					$('#af_c_sorthigh')[0].checked = !this.checked;	// make sort by date the opposite setting
				filterStoreSet();
				histFresh();							// AJAX call for a new history body
			}, 1000));				// will only execute specified mS after the last input change
		});
		function filterStoreSet()		// store to filter store
		{
			var serialized = '';
			var nodes = $('input:regex(id,^af_c_.*$)');	// find all filter checkbox storage elements
			for (var i = 0; i < nodes.length; i++)
			{
				serialized += nodes[i].id+'='+nodes[i].checked+'&';
				// nodes[i].style.display = 'none';				// turn them off
			}
			var nodes = $('input:regex(id,^af_s_.*$)');	// find all filter text box storage elements
			for (var i = 0; i < nodes.length; i++)
			{
				serialized += nodes[i].id+'='+encodeURI(nodes[i].value.trim())+'&';
				// nodes[i].style.display = 'none';				// turn them off
			}
			serialized += 'af_gd_min='+gradeSliderMap[$("#gradeslider").slider("values", 0)]+'&';		// store min and max slider values
			serialized += 'af_gd_max='+gradeSliderMap[$("#gradeslider").slider("values", 1)]+'';
			// alert(serialized);
			$.cookie('filter', serialized, { expires: 365, path: '/' });
		}
		function filterStoreGet()		// retrieve from filter store
		{
			var serialized = window.location.search.substring(1);			// get browser URL parameters
			if (!serialized)	// if no params in URL
				serialized = $.cookie('filter');			// get cookie, lop off trailing newline
			// alert(serialized);
			if (typeof serialized === "undefined")			// if cookie undefined, mostly leaving base defaults
				serialized = "af_gd_min=0&af_gd_max="+gradeSliderMap[gradeSliderMap.length-1];
					// but need to init slider
			try {
				serialized = serialized.trim().split('&');	// split into array
				for (var i = 0; i < serialized.length; i++)	// for every parameter
				{
					var param = serialized[i].split('=');	// split into name/value parameter
					var name = param[0];					// get name
					var value = param[1];					// get value
					if (name == 'af_gd_min')				// if special grade minimum
						$("#gradeslider").slider("values", 0, gradeSliderMap.indexOf(Number(value)) );
							// take grade and find it in gradeSliderMap, return index, store in slider
					else if (name == 'af_gd_max')			// if special grade maximum
						$("#gradeslider").slider("values", 1, gradeSliderMap.indexOf(Number(value)) );
							// take grade and find it in gradeSliderMap, return index, store in slider
					else if (name.substr(0,5) == 'af_c_')	// if it's a checkbox field
						$('#'+name).prop('checked', value=='true');	// set checkbox to value
					else									// default to string field
						$('#'+name).prop('value', decodeURI(value));	// set field to value
				}
			}	catch(err) {
				// alert(err);
				// just leave things alone if there's an error
			}
			filterSliderUpdate();	// update handles with the newly set slider values
		}
	</script>
	<?php
}
function histBody()	// display history body in bottom pane
{
	global $ilance;

	global $AucArchiveURI;
	global $universeID;
	global $universeDesc;
	global $denominationID;
	global $denominationDesc;
	global $coin_seriesID;
	global $coin_seriesDesc;
	global $coinID;
	global $coinDesc;
	global $catRow;
	global $coinRow;
	
	global $page_desc;
	global $uvListItem;
	global $uvListItemS;

	global $gradeList;

	global $filterParams;
	
	$itemName = $uvListItem[($universeID==0?1:$universeID)];	// get singular item name, default to coin
	$itemNameS = $uvListItemS[($universeID==0?1:$universeID)];	// get plural item name

	filterStoreGet();		// retrieve from filter store into global $gradeParams
	$af_gd_min = intval($filterParams['af_gd_min']);	// sanitize grade range
	$af_gd_max = intval($filterParams['af_gd_max']);
	?>
	<style type="text/css">
		.hist table {
			border-collapse: collapse;
		}
		.hist tr {
			vertical-align: text-bottom;
		}
		.hist td {
			padding: 2px;
		}
		
		.hist_left {
			border-left: 1px solid #CCC;
			padding-left: 4px !important;
			border-radius: 2px; 
		}
		.hist_centerleft {
			padding-right: 4px !important;
		}
		.hist_center {
			border-left: 1px solid #CCC;
			padding-left: 4px !important;
			border-radius: 2px; 
		}
		.hist_right {
			border-right: 1px solid #CCC;
			padding-right: 4px !important;
		}

		.hist_head tr {
		}
		.hist_head td {
			padding-top: 6px;
			font-weight: bold;
			border-top: 1px solid #CCC;
		}
		
		.hist_grade {
			font-size: 20px;
			font-weight: bold;
			white-space: nowrap;
		}

		.hist_line {
			border: 0;
			height: 1px;
			background: #CCC;
		}

		.hist_foot td {
			border-bottom: 1px solid #CCC;
		}

	.cropthumb {
		/* display: block; */
		overflow: hidden;
		height: 120px;
		width: 80px;
	}

	.cropthumb img {
		margin-top: -50px;
		margin-left: -40px;
		display: inline-block; /* Otherwise it keeps some space around baseline */
		width: 200%;
		/* min-width: 100%;    Scale up to fill container width */
		/* min-height: 100%;   Scale up to fill container height */
		-ms-interpolation-mode: bicubic; /* Scaled images look a bit better in IE now */
	}
	</style>

	<?php // echo 'filter cookie: '.$_COOKIE['filter']; ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="hist">
		<tr>
			<td colspan="5" style="vertical-align: bottom;">
				<div style="font-size: 16px; font-weight: bold;">Sold <?php echo ilance_htmlentities($page_desc); ?></div>
			</td>
			<td colspan="3" style="vertical-align: bottom;">
				<div style="font-size: 16px; font-weight: bold;">Upcoming <?php echo ilance_htmlentities($page_desc); ?></div>
			</td>
		</tr>
		<tr class="hist_head">
			<!--<td><nobr>Grade (qty)</nobr></td>-->
			<td class="hist_left">Grade / Item</td>
			<td align="right"><nobr>Sold For</nobr></td>
			<td align="center"><!--Bids--></td>
			<td>Photo</td>
			<td class="hist_centerleft" align="right">Date</td>

			<td class="hist_center">Grade / Item</td>
			<td align="right">Current Price</td>
			<td class="hist_right" align="center">Photo</td>
		</tr>
		<tr>
			<td colspan="5" class="hist_left"><hr class="hist_line"></td>
			<td colspan="3" class="hist_center hist_right"><hr class="hist_line"></td>
		</tr>
		<?php
		if ($coinID)				// if a specific PCGS number given
		{
			$level = 4;				// PCGS level
			$sqlJoin = "";			// no join needed
			$sqlWhere = "p.cid=$coinID AND";	// limit search to specific PCGS number
		} else if ($coin_seriesID)	// if only a coin series number given, find a random PCGS number from the series
		{
			$level = 3;				// Coin Series level
			$sqlJoin = "";			// no join needed
			$sqlWhere = "p.coin_series_unique_no=$coin_seriesID AND";				// limit results to specific coin series number
		} else if ($denominationID)	// if only a denom given, find a random PCGS number from the series
		{
			$level = 2;				// Denomination level
			$sqlJoin = "";			// no join needed
			$sqlWhere = "p.coin_series_denomination_no=$denominationID AND";		// limit results to specific denomination number
		} else if ($universeID)		// if only the coin universe given, map universe to denom(s)
		{
			$level = 1;				// Specific Universe level
			$sqlJoin = "";			// no join needed
			$sqlWhere = denom2univList('p.coin_series_denomination_no',$universeID);
				// limit results to specific denomination numbers, mapped from universe ID
		} else						// if not even the coin universe given return everything
		{
			$level = 0;				// Global Universe level
			$sqlJoin = "";			// no join needed
			$sqlWhere = "";
		}

		// Create a SQL WHERE subclause to limit based on grading criteria. Will be used in several places
		if ($filterParams['af_c_ungraded']=='true')
			$sqlGrade = "(p.Grade BETWEEN $af_gd_min AND $af_gd_max OR p.Grade=0) AND ";	// user grade range in WHERE clause
		else
			$sqlGrade = "p.Grade BETWEEN $af_gd_min AND $af_gd_max AND ";	// user grade range in WHERE clause
		// Create a SQL WHERE subclause to limit based on other filtering criteria. Will be used in several places
		$sqlFilter = '';
		
		if ($filterParams['af_c_pcgs']=='false' || $filterParams['af_c_ngc']=='false' || $filterParams['af_c_anacs']=='false' || $filterParams['af_c_other']=='false')
		{	// if any limitations on grading organization, form an IN (list) SQL clause
			$sqlFilter .= 'p.Grading_Service IN (';
			if ($filterParams['af_c_pcgs']=='true')	// if user wants any grade, but it in the list
				$sqlFilter .= "'PCGS',";
			if ($filterParams['af_c_ngc']=='true')
				$sqlFilter .= "'NGC',";
			if ($filterParams['af_c_anacs']=='true')
				$sqlFilter .= "'ANACS',";
			if ($filterParams['af_c_other']=='true')
				$sqlFilter .= "'ICG','NCS',";
			$sqlFilter = substr($sqlFilter,0,-1);		// remove trailing comma
			$sqlFilter .= ') AND ';
		}
		if ($filterParams['af_c_cac'] != $filterParams['af_c_noncac'])	// if restrict by CAC (if both are the same, no real restriction)
			$sqlFilter .= 'p.Cac='.($filterParams['af_c_cac']=='true'?1:0).' AND ';

		// $sqlJoin = "LEFT JOIN ".DB_PREFIX."coins c2 ON c2.coin_id=p.project_id";	// join to gain access to the "Plus" field

		if ($filterParams['af_c_plus'] != $filterParams['af_c_nonplus'])	// if restrict by plus grade (if both are the same, no real restriction)
		{
			// echo '$filterParams[af_c_plus]='; print_r($filterParams['af_c_plus']); echo '<BR>$filterParams[af_c_nonplus]='; print_r($filterParams['af_c_nonplus']); echo '<BR>';
			$sqlFilter .= 'Plus='.($filterParams['af_c_plus']=='true'?1:0).' AND ';	// include the plus field
		}

		if (!$coin_seriesID)		// if a coin series number given, it's pointless to search by date range
		{
			$af_s_yearfrom = intval($filterParams['af_s_yearfrom']);	// sanitize year range
			$af_s_yearto = intval($filterParams['af_s_yearto']);
			if ($af_s_yearfrom)	// if restrict by date range start
			{
				if ($af_s_yearto)	// if also restrict by date range end, use BETWEEN
					$sqlFilter .= "p.coin_detail_year BETWEEN $af_s_yearfrom AND $af_s_yearto AND ";
				else				// otherwise just find anything starting with "from"
					$sqlFilter .= "p.coin_detail_year >= $af_s_yearfrom AND ";
			} else if ($af_s_yearto)	// if only restrict by date range end
				$sqlFilter .= "p.coin_detail_year <= $af_s_yearto AND ";
		}
		$af_s_keywords = trim($filterParams['af_s_keywords']);
		if ($af_s_keywords)		// if search by keywords
		{
			 $sqlFilter .= "MATCH (p.project_title,p.description) AGAINST ('".$ilance->db->escape_string($af_s_keywords)."' IN BOOLEAN MODE) AND ";
		}
		$af_s_pcgs = intval($filterParams['af_s_pcgs']);	// sanitize
		if ($af_s_pcgs)		// if search by PCGS number
		{
			$sqlFilter .= "p.cid=$af_s_pcgs AND ";
		}

		if ($filterParams['af_c_busi_strike'] != $filterParams['af_c_spec_proof'])	// if restrict by busi/proof (if both are the same, no real restriction)
		{
			// echo '$filterParams[af_c_busi_strike]='; print_r($filterParams['af_c_busi_strike']); echo '<BR>$filterParams[af_c_spec_proof]='; print_r($filterParams['af_c_spec_proof']); echo '<BR>';
			$sqlFilter .= 'p.project_title '.($filterParams['af_c_spec_proof']=='true'?'':'NOT ')."LIKE '%Proof-%' AND ";
		}

		// echo '$sqlFilter='.$sqlFilter.'<BR>';
		 
		$sql = "SELECT p.Grade, p.Plus, COUNT(*) AS qty_total, SUM(p.status='open') AS qty_open FROM ".DB_PREFIX."projects p
		$sqlJoin
		WHERE $sqlFilter $sqlGrade $sqlWhere p.visible=1 AND NOT ISNULL(p.Plus) GROUP BY p.Grade, p.Plus ORDER BY p.Grade DESC, p.Plus DESC";
		// for a date limit on past auctions: AND p.date_end > DATE_ADD(NOW(),INTERVAL -6 MONTH) 
		// echo 'SQL = '.$sql; // exit;

		$gradeSelect = $ilance->db->query($sql);
		// echo 'Num rows = '.$ilance->db->num_rows($gradeSelect); exit;
		$soldCnt = 0;		// init counter for both past and upcoming counter
		$upcomingCnt = 0;
		$zoomGrade = trim(@$_GET['zoomgrade']);		// get possible zoomed grade
		$zoomGradePlus = (substr($zoomGrade,-1) == '+');	// set if plus zoomed grade
		$zoomGrade = intval($zoomGrade);			// sanitize zoomed grade
		while ($gradeRow = $ilance->db->fetch_array($gradeSelect))	// for each grade found for this matching coin
		{
			// echo '<PRE>'; print_r($gradeRow); echo '</PRE>'; exit;
			if (!$gradeRow['qty_total'])	// if no records in this grade, skip entirely
				continue;
			$Grade = $gradeRow['Grade'];	// get current grade
			$Plus = $gradeRow['Plus'];		// get current Plus designation, intval() force to 0 or 1, in case JOIN to ilance_coins fails
			$isZoomedGrade = ($Grade == $zoomGrade && $Plus == $zoomGradePlus);	// set if we're on the zoomed grade
			$numRowsThisGrade = ($isZoomedGrade ? 100 : TOP_COIN_HIST);			// set max number of item rows for this grade
			// echo 'Grade = '.$Grade.'<!--, PLUS = '.$Plus.'<BR>--> Plus='; print_r($Plus); echo '<BR>';
			?>
			<tr>
			<?php
			/*
			if ($Grade)	// if there's a real grade, then generate a link to more results
			{
				$searchLink = 'search.php?fromyear=&toyear=&fromprice=&toprice=&mode=product&sort=01&series='.$coin_seriesID.'&q=&denomination%5B%5D='.$denominationID.'&frombid=0&tobid=500';
				if (!$denominationID)	// if no specific denom, then make special "all denoms" parameter
					$searchLink .= '&denom_all=1';
				// '&listing_type=1'	// for upcoming items
				// '&listing_type=4'	// for completed items
				// '&grade_range_1='.$Grade.'&grade_range_2='.$Grade
			} else 
				$searchLink = '';
			*/
			$GradeWithPlus = ($Grade?$Grade:UNGRADED).($Plus?'+':'');			// get current grade with possible plus designator
			$searchLink = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";	// get based path
			$searchLink = preg_replace("/[\?#].*$/",'',$searchLink);			// rid of any CGI parameters (filters, ajaxhist=1)
			if (!$isZoomedGrade)		// if not already showing this grade zoomed
			{
				if (strpos($searchLink, '?') === False)	// if not already ? style CGI parameters
					$searchLink .= '?';
				else
					$searchLink .= '&';
				$searchLink .= 'zoomgrade='.urlencode($GradeWithPlus);		// get possible zoomed grade
				$searchLinkText = 'Show more of this grade of '.strtolower($itemNameS);
			} else {					// if already showing this grade zoomed
				$searchLink = preg_replace("/[\?&]zoomgrade=[0-9]+\+?/",'',$searchLink);		// rid of zoomed grade in URL
				$searchLinkText = 'Restore showing just the top '.TOP_COIN_HIST.' of this grade of '.strtolower($itemNameS);
			}
			$searchLink = preg_replace("/#.*$/",'',$searchLink).'#'.urlencode($GradeWithPlus);		// add hash to this anchor, after ridding of any existing hash
			if ($gradeRow['qty_total'] > $gradeRow['qty_open'])	// if there are past records in this grade
			{
				$isPastFiller = False;				// flag this grade slot for past items as having not been filled yet
				// http://www.greatcollections.com/search.php?fromyear=&toyear=2017&grade_range_1=70&grade_range_2=70&fromprice=&toprice=&mode=product&sort=01&series=0&q=&denomination%5B%5D=2&listing_type=1&frombid=0&tobid=500
				?>
				<td colspan="5" class="hist_left hist_grade" style="padding-top: 8px;" style="position: relative;">
				<?php if ($searchLink)		// if there's a link to more items
					echo '<a name="'.urlencode($GradeWithPlus).'"><a href="'.$searchLink.'" title="'.ilance_htmlentities($searchLinkText).'">';
					// echo '<a href="'.$searchLink.'&listing_type=4&grade_range_1='.$Grade.'&grade_range_2='.$Grade.'">';
				?>
				<?php echo $GradeWithPlus; ?></span><span style="font-size: 14px; font-weight: normal;"> (<?php echo $gradeRow['qty_total']-$gradeRow['qty_open']; ?>)</span>
				<?php if ($searchLink)		// if there's a link to more items
					echo '</a></a>';
				?>
				</td>
				<?php
			} else {								// if nothing to be shown in this column
				$isPastFiller = True;				// flag this grade slot for past items as having been filled
				?>
				<td rowspan="<?php echo $numRowsThisGrade+1;	// span all the rows that might have been ?>" colspan="5" class="hist_left"><?php
				if (!$soldCnt)	// in first empty head slot, save a place a disabled "There are none" message just in case we need it
				{
					?>
					<div <?php echo (!isset($noClosedCnt)?'id="noclosed"':'id="noclosedNah"'); $noClosedCnt = True; ?>></div>
					<?php
				}
				?></td>
				<?php
			}
			if ($gradeRow['qty_open'])				// if there are future records in this grade
			{
				$isOpenFiller = False;				// flag this grade slot for open items as having not been filled yet
				?>
				<td colspan="3" class="hist_center hist_right hist_grade" style="Xposition: relative;">
				<?php if ($searchLink)		// if there's a link to more items
					echo '<a href="'.$searchLink.'" title="'.ilance_htmlentities($searchLinkText).'">';
					// echo '<a href="'.$searchLink.'&listing_type=1&grade_range_1='.$Grade.'&grade_range_2='.$Grade.'">';
				?>
				<?php echo ($Grade?$Grade:UNGRADED).($Plus?'+':''); ?><span style="font-size: 14px; font-weight: normal;"> (<?php echo $gradeRow['qty_open']; ?>)</span>
				<?php if ($searchLink)		// if there's a link to more items
					echo '</a>';
				?>
				</td>
				<?php
			} else {								// if nothing to be shown in this column
				$isOpenFiller = True;				// flag this grade slot for open items as having been filled
				?>
				<td rowspan="<?php echo $numRowsThisGrade+1;	// span all the rows that might have been ?>" colspan="3" class="hist_center hist_right"><?php
				if (!$upcomingCnt)	// in first empty head slot, save a place a disabled "There are none" message just in case we need it
				{
					// echo '<!-- $numRowsThisGrade = '.$numRowsThisGrade.', $gradeRow[qty_total] = '.$gradeRow['qty_total'].', $gradeRow[qty_open] = '.$gradeRow['qty_open'].'-->';
					?>
					<div <?php echo (!isset($noOpenCnt)?'id="noopen"':'id="noopenNah"'); $noOpenCnt = True; ?>></div>
					<?php
				}
			?></td>
				<?php
			}
			?>
			</tr>
			<?php
			if ($filterParams['af_c_sorthigh'] == 'true')	// change sort based on user selection
				$sqlOrder = 'p.currentprice';
			else
				$sqlOrder = 'p.date_end';
			// get subquery usable for both history and upcoming
			$sql = "SELECT p.* FROM ".DB_PREFIX."projects p
			$sqlJoin
			WHERE [HOLDING] $sqlWhere $sqlFilter p.Grade=$Grade AND p.Plus=$Plus AND p.visible=1 ORDER BY $sqlOrder DESC LIMIT ".$numRowsThisGrade;
				// for a date limit on past auctions: AND p.date_end > DATE_ADD(NOW(),INTERVAL -6 MONTH) 
			// echo 'SQL = '.$sql.'<BR>'; // exit;
			
			// make query UNIONing both history and upcoming subqueries, upcoming first so we can store it in an array before outputting anything
			$sql = "SELECT * FROM (".str_replace('[HOLDING]','p.status <> \'open\' AND ',$sql).") AS po UNION (".str_replace('[HOLDING]','p.status = \'open\' AND ',$sql).") ORDER BY status <> 'open'";
			// echo 'SQL = '.$sql.'<BR><BR>'; // exit;

			$histSelect = $ilance->db->query($sql);
			// echo 'Grade '.$Grade.' num rows = '.$ilance->db->num_rows($histSelect).'<br />'; // exit;
			$upcoming = array();	// init holding place for upcoming
			$rowCntGrade = 0;			// keep track of rows within this grade
			while (($histRow = $ilance->db->fetch_array($histSelect)) || !empty($upcoming) || $rowCntGrade < $numRowsThisGrade)	// return records for current grade
			{
				// echo '<PRE>Field cnt='.count($histRow).':<br>'; print_r($histRow); echo '</PRE>'; // exit;
				// echo 'status='.$histRow['status'].'<br />';
				if ($histRow['status'] == 'open')		// while upcoming lots still coming in
				{
					array_push($upcoming, $histRow);	// push upcoming record into array, saving for later
				} else {
					// echo $histRow['project_title']."\t".$histRow['currentprice']."\t".$histRow['bids']."\t".date('M Y',strtotime($histRow['date_end'])).'<br />';
					?>
					<tr>
					<?php
					if (empty($histRow))					// if nothing to be shown in this column
					{
						if (!$isPastFiller)					// if filler wasn't done yet
						{
							$isPastFiller = $numRowsThisGrade-$gradeRow['qty_total']-$gradeRow['qty_open'];	// get number of unused rows
							if ($isPastFiller)				// if any left
							{
								?>
								<td rowspan="<?php echo $isPastFiller; ?>" class="hist_left Xhist_grade"></td>
								<td rowspan="<?php echo $isPastFiller; ?>" colspan="4"></td>
								<?php
							}
							$isPastFiller = True;			// definitely flag as having been filled
						}
					} else {								// if something to be shown in this column
						$soldCnt++;							// inc counter for past
						$id = $histRow['project_id'];
						$title = $histRow['project_title'];
						$url = HTTP_SERVER.'Coin/'.$id.'/'.construct_seo_url_name($title);
						?>
						<td id="histid_<?php echo $id; ?>" class="hist_left"><a href="<?php echo $url; ?>"><?php echo noOrphan(ilance_htmlentities($title)); ?></a></td>
						<td align="right"><?php echo currency_format_whole($histRow['currentprice']+$histRow['buyer_fee']); ?></td>
						<td align="right">&nbsp;<nobr><?php
							if ($histRow['buynow'])	// if a BuyNow item
								echo '<span style="color: grey; font-size: 0.8em; font-style: italic;">Buy Now</span>';
							else		// only show bids if NOT a BuyNow item
								echo $histRow['bids'].' bid'.($histRow['bids']!=1?'s':'<span style="visibility: hidden;">s</span>');
							?></nobr></td>
						<td align="center" style="cursor: pointer;" data-imgpopup data-id="<?php echo $id; ?>" data-title="<?php
							echo ilance_htmlentities($histRow['project_title']);
						?>" data-href="<?php echo ilance_htmlentities(HTTP_SERVER.'Coin/'.$id.'/'.construct_seo_url_name($histRow['project_title'])); ?>"><a class="imgpopuphref" href="<?php echo $url; ?>" style="text-decoration: none;">&#128247;</a></td>
						<td class="hist_centerleft" align="right"><nobr><?php echo date('M Y',strtotime($histRow['date_end'])); ?></nobr></td>
						<?php
					}
					$histRow = array_shift($upcoming);		// try to get a saved upcoming lot
					if (empty($histRow))					// if nothing to be shown in this column
					{
						if (!$isOpenFiller)					// if filler wasn't done yet
						{
							$isOpenFiller = $numRowsThisGrade-$gradeRow['qty_open'];	// get number of unused rows
							if ($isOpenFiller)				// if any left
							{
								?>
								<td rowspan="<?php echo $isOpenFiller; ?>" class="hist_center Xhist_grade"></td>
								<td rowspan="<?php echo $isOpenFiller; ?>"></td>
								<td rowspan="<?php echo $isOpenFiller; ?>" class="hist_right"></td>
								<?php
							}
							$isOpenFiller = True;			// definitely flag as having been filled
						}
					} else {								// if something to be shown in this column
						$upcomingCnt++;						// inc counter for upcoming
						$id = $histRow['project_id'];
						$title = $histRow['project_title'];
						$url = HTTP_SERVER.'Coin/'.$id.'/'.construct_seo_url_name($title);
						?>
						<td id="histid_<?php echo $id; ?>" class="hist_center"><a href="<?php echo $url; ?>"><?php echo noOrphan(ilance_htmlentities($title)); ?></a></td>
						<td align="right"><?php echo currency_format_whole($histRow['currentprice']); ?></td>
						<td class="hist_right" align="center" style="cursor: pointer;" data-imgpopup data-id="<?php echo $id; ?>" data-title="<?php echo ilance_htmlentities($histRow['project_title']); ?>" data-href="<?php echo ilance_htmlentities(HTTP_SERVER.'Coin/'.$histRow['project_id'].'/'.construct_seo_url_name($histRow['project_title'])); ?>"><a href="<?php echo $url; ?>" style="text-decoration: none;">&#128247;</a></td>
						<?php
					}
					?>
					</tr>
					<?php
					$lastGrade = $histRow['Grade'];
				}
				$rowCntGrade++;
				if ($isPastFiller && $isOpenFiller)			// if all slots have been filled
					break;									// stop looping
			}
			unset($select);
			// echo '$rowCntGrade='.$rowCntGrade.' < '.$numRowsThisGrade.'<BR>';
			// if ($rowCntGrade < $numRowsThisGrade)	// if not all possible rows were displayed
			//	echo '<tr><td rowspan="'.($numRowsThisGrade-$rowCntGrade).'"></td></tr>';	// span them
			echo str_repeat('<tr></tr>',$numRowsThisGrade+1);	// needed as a sort of clear="all" for any rowspans that were defined beyond what were actually used
		}
		unset($gradeSelect);
		?>
		<tr class="hist_foot">
			<td colspan="5" class="hist_left">
				<div id="noclosed2" style="display: none;">
					<div style="text-align: center; font-size: 14px; font-weight: bold;">
						<i>There are no matching sold items</i><br /><br />
					</div>
				</div>
			</td>
			<td colspan="3" class="hist_center hist_right">
				<div id="noopen2" style="display: none;">
					<div style="text-align: center; font-size: 14px; font-weight: bold;">
						<i>There are no matching upcoming items</i><br /><br />
					</div>
					<div style="text-align: center; font-size: 14px;">
						<B style="font-size: 18px; vertical-align: middle;">Be Notified!</B>&nbsp;
						<div style="display: inline-block; vertical-align: middle;">
						<?php showAddSaved($universeID, $denominationID, $coin_seriesID, $coinID, $af_gd_min, $af_gd_max, ($filterParams['af_c_ungraded']=='true'?1:0)); ?>
						</div>
						<br />
						Add this to your saved searches and<br />we will notify you when one is available.
						<br /><br />
						<?php button('Consign Your Coins Today!','main-sell'); ?>
						<br />
						We have bidders looking for <?php
							switch ($level) {
							case 0:		// global
								echo 'coin and currency';
								break;
							case 1:		// specific universe
								echo 'these '.strtolower($itemNameS);
								break;
							case 2:		// denom
								echo 'this denomination';
								break;
							case 3:		// coin series
								echo 'this '.strtolower($itemName).' series';
								break;
							case 4:		// PCGS
								echo 'this '.strtolower($itemName);
								break;
							}
						?> in all grades.
					</div>
				</div>
			</td>
		</tr>
		<div id="closedcnt" style="display: none;"><?php echo $soldCnt; ?></div>
		<div id="opencnt" style="display: none;"><?php echo $upcomingCnt; ?></div>
		<script type="text/javascript" language="Javascript">
			$(function () {
				updateNoItemsMsg(<?php echo $soldCnt;	// xfer PHP variables to JS ?>, <?php echo $upcomingCnt; ?>);
					// update messages if there are no items (this won't execute from within AJAX loaded HTML)
			});
		</script>
	</table>
	<br />
	<?php
}
$filterParams = array();
function filterStoreGet()		// retrieve from filter store
{
	global $filterParams;

	$serialized = trim(@$_COOKIE['filter']);	// get cookie, lop off trailing newline
	// echo '$serialized='.$serialized.'<br />';
	if (!$serialized)							// return now if undefined, leaving base defaults
		return;
//	try {
		$serialized = preg_split("#&#", $serialized);	// split into array
		foreach ($serialized as $param)					// for every parameter
		{
			$param = preg_split("#=#", $param);			// split into name/value parameter
			$filterParams[$param[0]] = $param[1];		// assign to global array
		}
		// print_r($filterParams);
//	}	catch(err) {
//		// just leave things alone if there's an error
//	}
}

function showAddSaved($uv_id, $den_id, $cs_id=0, $cn_id=0, $grade_min=1, $grade_max=70, $ungraded=1)	// show a button for saving a search
{
	global $page_desc;

	button('Add this to your Saved Searches','JS:addSavedAJAX(this, \''.urlencode($page_desc).'\', '.$uv_id.', '.$den_id.', '.$cs_id.', '.$cn_id.', '.$grade_min.', '.$grade_max.', '.$ungraded.');');
}
function button($msg, $action, $tip='', $color='', $bgcolor='', $bgcolor2='')	// display an HTML button
{
	if ($color == '')			// if no font color given
		$color = 'white';
	if ($bgcolor == '')			// if no button color given
	{
		$bgcolor = '#007cc7';	// default to light blue
		$bgcolor2 = '#1c4ba7';	// default to gradient to darker blue
	}
	$style = 'background: '.$bgcolor;	// straight BG color (fallback if "linear-gradient" unsupported
	if ($bgcolor2 != '')		// if  gradient
		$style .= ' linear-gradient('.$bgcolor.', '.$bgcolor2.');';
	else
		$style .= ';';

	if (!$tip)		// if no hover tip, default to plain text of message
	{
		$tip = strip_tags(preg_replace("#(<br[^>]*>)|(\n)#i",' ',$msg));	// change <br> and newlines to spaces			
	}

	$isJS = preg_match("/^(javascript|js):(.*)$/i", $action, $res);	// check if a JS action
	if ($isJS)
		$action = $res[2];	// rid of prefix
	else
		echo '<form action="'.$action.'" method="get" style="display: inline-block">';
	echo '<button class="button" style="margin: auto; outline:0; color: '.$color.'; '.$style.' text-shadow: 2px 2px #000; font-size: 14px; font-weight: bold; padding: 6px; border: 0px solid black; border-radius: 4px; moz-border-radius: 4px; webkit-border-radius: 4px; box-shadow: 1px 1px 2px black; cursor: pointer;"';
	if ($isJS)
		echo ' onClick="'.$action.'"';
	echo ' title="'.$tip.'">';
	echo $msg;
	echo '</button>';
	if (!$isJS)
		echo '</form>';
}
function dispNavTree($universe, $level1 = '', $level2 = '')		// display a JavaScript navigation tree for this $universe,
{																// optionally opened to $level1 and maybe even $level2
	$sql = "SELECT * 
		FROM ".DB_PREFIX. "catalog_toplevel den LEFT JOIN
			".DB_PREFIX. "catalog_second_level cs ON cs.coin_series_denomination_no=den.denomination_unique_no";
	$select_tree = $ilance->db->query($sql);
}
function denom2univ($den_id)									// get universe ID from denomination
{
	global $uvListMap;

	$uv_id = @$uvListMap[$den_id];		// map universe from this demonination
	if (empty($uv_id))					// if none found in map
		$uv_id = 1;						// default to US Coin
	return $uv_id;
}
function denom2univList($denFieldName, $uv_id)	// get SQL WHERE fragment, an "IN" list of denoms, based on universe ID.
{
	global $uvListMap;

	$list = '';
	foreach ($uvListMap as $den_id=>$uv_idMap)	// loop through all mappings
	{
		if ($uv_id == $uv_idMap || $uv_id == 1)	// for any denoms that are in our target universe, or simply all non-US Coin mappings if our target is US Coins
			$list .= $den_id.',';				// add to the list
	}
	return $denFieldName.' '.($uv_id==1?'NOT ':'').'IN ('.substr($list,0,-1).') AND ';
}
function denom2univSQL($denFieldName)	// get SQL Where clause for universe ID, based on denomination
{
	global $uvListMap;

	$sql = '';
	foreach ($uvListMap as $den_id=>$uv_id)	// assemble SQL expression to test and map all denominations to universes
		$sql .= "WHEN $den_id THEN $uv_id ";
	$sql = "(CASE $denFieldName $sql ELSE 1 END)";
	return $sql;
}
function cs2denom($cs_id)				// get denomination ID from coin series number
{
	global $ilance;
	
	// We know the series number, but need to find the denomination ID above it
	$sql = "SELECT coin_series_denomination_no FROM ".DB_PREFIX. "catalog_second_level cs WHERE cs.coin_series_unique_no=$cs_id";
	// echo 'SQL = '.$sql; exit;
	$select = $ilance->db->query($sql);
	// echo 'Num rows = '.$ilance->db->num_rows($select); exit;
	if ($row = $ilance->db->fetch_array($select))
		$den_id = $row['coin_series_denomination_no'];
	else
		$den_id = FALSE;
	unset($select);
	return $den_id;
}
function nameURLencode($name)						// convert name to URL format
{
	$name = str_replace(' ','-',$name);				// convert any spaces to hyphens
	$name = str_replace('/','-',$name);				// convert any spaces to hyphens
//	$name = str_replace('-','--',$name);			// convert any hyphens to double-hyphens
	return $name;
}
function nameURLdecode($name)						// de-convert name from URL format
{
	$name = preg_replace('#-([^-])#',' $1',$name);	// convert any non-doubled hypens to spaces
//	$name = str_replace('--','-',$name);			// convert any doubled hypens to hyphens
	return $name;
}
function nodeIDbreakout($nodeID)					// break nodeID into a level and a database ID
{
	if (preg_match("/^L([0-9]+)_([0-9]+)$/", $nodeID, $res))
	{
		return array(intval($res[1]),intval($res[2]));		// return level and dbID, in their native integer form
	}
	return NULL;
}
function titleFromFields($row)						// assemble a title_engg, coin_series, desc from field list
{
	$title_engg = $row['coin_detail_year'].(!empty($row['coin_detail_mintmark'])?'-'.$row['coin_detail_mintmark']:'').' '.$row['coin_detail_coin_series'];
	$title_engg .= ' '.$row['coin_detail_suffix'].' '.$row['coin_detail_major_variety'].' '.$row['coin_detail_die_variety'].($row['coin_detail_proof']=='y'?' Proof':'');
	$coin_series = $row['coin_detail_coin_series'];
	$desc = $row['coin_detail_year'].' '.$row['coin_detail_coin_series'].' '.$row['coin_detail_suffix'].' '.$row['coin_detail_major_variety'].' '.$row['coin_detail_die_variety'];
	return array($title_engg, $coin_series, $desc);
}
function currency_format_whole($val)
{
	return '$'.number_format($val);
}
function noOrphan($str)								// make sure no single word is orphaned
{
	return preg_replace("/ ([^ ]+) ([^ ]{0,4})$/",' $1&nbsp;$2',str_replace('-','&#8209;',$str));
		// replace the space between the last two words with a non-breaking space, if the last word is 4 chars or less
		// also... replace any hyphens with non-breaking hyphens
}
?>
