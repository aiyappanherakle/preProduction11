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
	'autocomplete',
        'countries',
        'tabfx',
        'inline_edit',
	'jquery',
        'modal',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'knowledge');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');

date_default_timezone_set('America/Los_Angeles');

if (True)	// debugging mode for PHP and MySQL
{
	error_reporting(E_ALL);
	ini_set('display_errors', 'stdout');
	restore_error_handler();
	mysqli_report(MYSQLI_REPORT_STRICT);  // MYSQLI_REPORT_ALL also fails on warnings, like that an index isn't available to optimize a query
}
define('THIS_SCRIPT',HTTP_SERVER.'/kb.php');
define('KB_TITLE','Knowledge');
define('ISMARK',($_SERVER['SERVER_SOFTWARE'] == 'Microsoft-IIS/7.5' && $_SERVER['APPL_PHYSICAL_PATH'] == 'C:\\GC\\preProduction\\'));	// set if Mark devel server

// list of all universe IDs and descriptions
$uvList = array(
	1=>'US Coin Prices',
	2=>'World Coin Prices',
	3=>'US and World Currency Prices'
);
// mapping of denomination IDs to universe IDs, or default to "1" (US Coins)
$uvListMap = array(
	30=>2, /* World and Ancient Coins=>World Coin Prices */
	34=>3, /* Currency=>US and World Currency Prices */
);

$navcrumb = array(nameURLencode(KB_TITLE).'/' => KB_TITLE);		// start knowledgebase breadcrumb

// echo '<PRE>'; print_r($_GET); echo '</PRE>'; exit;

$isSearch = isset($_GET['search']);		// set if search called
$query = @$_GET['query'];

$id = intval(@$_GET['id']);

$page_title = SITE_NAME.' Knowledgebase';	// default page title
$head_title = $page_title;					// and page header title

if ($id)	// if a specific article given
{
	// $descr = @$_GET['description'];	// we don't care about the SEO URL title

	$sql = "SELECT * FROM ".DB_PREFIX."kb2 WHERE kb_id=$id;";
	// echo 'SQL = '.$sql; exit;

	$select = $ilance->db->query($sql);		// execute query
	// echo 'Num rows = '.$ilance->db->num_rows($select); exit;

	$row = $ilance->db->fetch_array($select);
	unset($select);
	if ($row)		// if a KB record found
	{
		// echo '<PRE>'; print_r($row); echo '</PRE>'; exit;
		$title = $row['kb_title'];	// get title from database
		$subTitle = $row['kb_subtitle'];	// get title from database
		$navcrumb = array_merge($navcrumb, array(nameURLencode(KB_TITLE).'/'.$id.'/'.construct_seo_url_name(trim($title.' '.$subTitle)) => $title.($subTitle?': '.$subTitle:'')));
			// add title to knowledgebase breadcrumb
		$head_title = $title.($subTitle?': '.$subTitle:'');
		$page_title = $head_title.' - '.SITE_NAME.' Coin and Currency Knowledgebase';
	}
}

// #### setup default breadcrumb ###############################################
$area_title = 'Area Title';

$headinclude .= '
<!--
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="functions/javascript/jquery-3.2.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="functions/javascript/jquery.cookie.js"></script>
<script src="functions/javascript/jstree/jstree.min.js"></script>
-->
<script src="functions/javascript/jquery.columnizer.js"></script>
<!--
<link rel="stylesheet" href="functions/javascript/jstree/themes/default/style.min.css" />
<style type="text/css">
.tree li a ins {
	display:none !important;
} 
</style>
-->
';

$pprint_array = array('page_title','headinclude');
$ilance->template->construct_header('main');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);

?>
<style type="text/css">
div.sc_menu_wrapper {
  position: relative;   
  height: 533px;
  width: 195px;
  margin-top: 0px;
  overflow: auto;
}
div.sc_menu {
  padding: 15px 0;
  width: 195px;
}
#open
{
background:none;
color:#E16E19;
font-size:14px;
text-decoration:none;
font-weight:bold;
font-family:Tahoma, Arial, "Times New Roman", Verdana;
text-transform:capitalize;
}
#imgs
{
background-image: url(images/gc/w2.jpg);
width:198px;
height:151px;
}
#textim
{
background-image: url(images/gc/coin_bg.jpg);
width:198px;
height:160px;

}
#amo
{
background:none;
color:#E16E19;
font-size:14px;
text-decoration:none;
font-weight:bold;
font-family:Tahoma, Arial, "Times New Roman", Verdana;
text-transform:capitalize;
}
#fetit
{
background:none;
color:#303030;
font-size:12px;
text-decoration:none;
font-weight:bold;
font-family:Tahoma, Arial, "Times New Roman", Verdana;
text-transform:capitalize;
}		
#titl
{
background:none;
color:#5B6C7E;
font-size:12px;
text-decoration:none;
font-weight:bold;
font-family:Tahoma, Arial, "Times New Roman", Verdana;
text-transform:capitalize;
}
		
		a:focus { outline:none }
		
		img { border: 0 }
		
	
	  
		.slider-wrap { /* This div isn't entirely necessary but good for getting the side arrows vertically centered */
			
			position: relative;
			width: 100%;
		}



		.stripViewer { /* This is the viewing window */
			position: relative;
			overflow: hidden; 
			/*border: 5px solid #000;*/ /* this is the border. should have the same value for the links */
			margin: auto;
			
		}
		
		.stripViewer .panelContainer { /* This is the big long container used to house your end-to-end divs. Width is calculated and specified by the JS  */
			position: relative;
			left: 0; top: 0;
			
			list-style-type: none;
			/* -moz-user-select: none; // This breaks CSS validation but stops accidental (and intentional - beware) panel highlighting in Firefox. Some people might find this useful, crazy fools. */
		}
		
		.stripViewer .panelContainer .panel { /* Each panel is arranged end-to-end */
			float:left;
			height: 100%;
			position: relative;
			width: 700px; /* Also specified in  .stripViewer  above */
		}
		
		.stripViewer .panelContainer .panel .wrapper { /* Wrapper to give some padding in the panels, without messing with existing panel width */
			padding: 10px;
		}
		
		
		
		.stripNavL, .stripNavR { /* The left and right arrows */
			position: absolute;
			top: 80px;
			text-indent: -9000em;
		}
		
		.stripNavL a, .stripNavR a {
			display: block;
			height: 40px;
			width: 40px;
		}
		
		.stripNavL {
			left: 0;
		}
		
		.stripNavR {
			right: 0;
		}
		
		.stripNavL {
			background: url("images/arrow-left.gif") no-repeat center;
		}
		
		.stripNavR {
			background: url("images/arrow-right.gif") no-repeat center;
		}
		 .stripNav ul
		{
		display:none;
		} 
	</style>
<div class="block-wrapper">                
	<div class="block">                            
		<div class="block-top">
			<div class="block-right">
				<div class="block-left"></div>
			</div>
		</div>                                    
		<div class="block-header"><?php echo ilance_htmlentities($head_title); ?></div>
		<div class="block-content" style="padding: 6px 0px 0px 6px">
		<?php
		if ($id)	// if a specific article given
		{
			?>
			<?php
			$article = $row['kb_article'];		// get article from database
			$article = str_replace("\n","<br />\n",$article);
			// {img,"item":239023,"align":"right"}
			// {img,"src":"http://www.greatcollections.com/image/400/268/470412-1.jpg","caption":"This is a caption","link":"http://www.greatcollections.com/Coin/470412"}
			$article = preg_replace_callback('#\{ *img *,([^}]*)\}#i', 'replace_image', $article);	// if found {img,...}
			echo $article;
		}
		?>
		</div>                                    
		<div class="block-footer">
			<div class="block-right">
				<div class="block-left"></div>
			</div>
		</div>                                    
		</div>
	</div>
</div>
<?php
exit;
function replace_image($match)		// callback function to replace an image code with HTML in a description field
{
	global $ilance;

	// echo '<PRE>$match='; print_r($match); echo '</PRE>'; exit;
	// echo '<PRE>JSON='.'{'.$match[1].'}'.'</PRE>'; exit;
	$params = json_decode('{'.$match[1].'}', true);		// decode as JSON to array
	if (is_null($params))	// if bad formatting, skip with error
		return '{img} FORMAT ERROR';
	$src = '';
	$caption = '';
	$link = '';
	if (isset($params['item']))	// if source given as an item number
	{
		$id = intval($params['item']);	// clean project ID, just in case it's hacked
		$sql = "SELECT date_end, project_title, status, buynow, buynow_price, currentprice, buyer_fee FROM ".DB_PREFIX."projects WHERE project_id=$id;";
		// echo 'SQL = '.$sql; exit;

		$select = $ilance->db->query($sql);		// execute query
		// echo 'Num rows = '.$ilance->db->num_rows($select); exit;

		$row = $ilance->db->fetch_array($select);
		unset($select);
		if (!$row)		// if a project record found
			return '{img} ITEM #'.$id.' NOT FOUND';
		else {
			// echo '<PRE>'; print_r($row); echo '</PRE>'; exit;
			$title = $row['project_title'];		// get title from database
			$status = $row['status'];			// get status from database, "open", "closed"=buynow sold, "expired"=auction sold
			$src = HTTP_SERVER.'/image/400/268/'.$id.'-1.jpg';
			$link = HTTP_SERVER.'Coin/'.$id.'/'.construct_seo_url_name($title);
			
			// from Suku 6/15/2017: status 'closed' is for buynow items that are sold, successfully sold auction coins are "expired"
			if ($row['buynow'])					// format pricing based on buy vs auction item
			{
				if ($status == 'expired')
					$caption = ilance_htmlentities($title).'<br />Bought for '.$ilance->currency->format($row['buynow_price']);
				else
					$caption = ilance_htmlentities($title).'<br />Offered at '.$ilance->currency->format($row['buynow_price']);
			} else {
				if ($status == 'open')
					$caption = ilance_htmlentities($title).'<br />Current bid at '.$ilance->currency->format($row['currentprice']);
				else
					$caption = ilance_htmlentities($title).'<br />Sold for '.$ilance->currency->format($row['currentprice']+$row['buyer_fee']);
			}
		}
	}
	if (isset($params['src']))				// if various paramaters given, override defaults (or DB-loaded ones)
		$src = $params['src'];
	if (isset($params['caption']))
		$caption = $params['caption'];
	if (isset($params['link']))
		$link = $params['link'];
	if (isset($params['align']))
		$align = $params['align'];
	else
		$align = 'right';
	if (!$src)								// if no source, return with error
		return '{img} NO "SRC" defined';
	$res = '<div style="width: 200px; margin-left: 10px; border-left: 5px; padding: 6px; float: '.$align.'; clear: both; text-align: center;">';
	if ($link)
		$res .= '<a href="'.$link.'" title="'.ilance_htmlentities($caption).'">';
	$res .= '<div style="border-radius: 15px; overflow: hidden;">';
	$res .= '<img src="'.$src.'" width="200" alt="'.ilance_htmlentities($caption).'" title="'.ilance_htmlentities($caption).'" />';
	$res .= '</div>';
	if ($caption)
		$res .= $caption;
	if ($link)
		$res .= '</a>';
	$res .= '</div>';
	return $res;
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
?>
