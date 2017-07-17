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
	'autocomplete',
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

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{


	if(isset($ilance->GPC['var']))
	{
	
	$file = HTTP_SERVER.'email_template/UHR_template_'.$_SESSION['ilancedata']['time'].'.html';
	
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
	header("Content-Type: application/force-download");
	header( "Content-Disposition: attachment; filename=".basename($file));
	
	header( "Content-Description: File Transfer");
	@readfile($file);
	exit();
	}
	
if(isset($ilance->GPC['you']) and $ilance->GPC['you'] == 'uio')
{

//echo 'dddd'.$ilance->GPC['user'];
$head = '
<div align="center">

<div style="width:630px; background-color:#FFF;"> 
	
    <div style="background-color:#f9f9ed; height:70px;"> 
		<div style="float:left; width:400px; color:#999; font-size:11px; text-align:left; padding:15px 10px 0px 10px;"> 
        	Skip the e-mail! Just go straight to GreatCollections.com.<br /><br />
			Having problems viewing this email? View it in your browser.
        </div>
        
    	<div style="float:right; width:150px; color:#999; font-size:11px; text-align:right; padding:15px 10px 0px 10px; "> 
        	{VR_SOCIAL_SHARING}<br />
			<a href="#">Forward to a Friend </a></div>
		</div>
    
    <div style="padding:15px;">
    
        <div style="height:115px;background:url(http://www.greatcollections.com/images/gc/box_blue.jpg);">  
        	<div style="float:left;padding:0px 0px 0px 0px;"><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none;"><img src="http://www.greatcollections.com/images/gc/uhr1.png" height="110" width="110" border="0" /></a></div>
        	<div style="color:#2d5b8e;  padding:50px 20px 0px 0px; text-align:center;">
            	<span style="font-size:18px;">2009 Ultra High Relief at GreatCollections</span><br />  
				<div style="height:30px;margin-top:13px;margin-left:-12px;"> 
        <table width="78%" border="0" cellspacing="0" cellpadding="0" align="center" valign="middle" style="background-color:#2C5987; background:url(http://www.greatcollections.com/images/gc/nav-bg.jpg) center left repeat-x #2C5987;">
          <tr height="30">
  <td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/merch.php?cmd=listings&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-align:center; text-decoration:none; text-transform:none;">View Coins</a></td>
  <td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?q=ultra+high+relief&mode=product&sort=01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none; text-align:center;">UHRs</a></td>
  <td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/main-promise" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none; text-align:center;">Trust GC</a></td>
  <td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/main-about" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none; text-align:center;">About GC</a></td>
  <td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/main-contact" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none; text-align:center;">Contact US</a></td>

</tr>

        </table>
        </div>
            </div>
        </div>
        
        
      ';
	  
	  $body = '<br><table width="594" border="0" cellpadding="1" cellspacing="1">
  <tr valign="top">
    <td width="180" height="450">
	
  <table width="183" height="250" border="0" cellpadding="1" cellspacing="3" style="border:1px solid #B0C4CF;">
  <tr >
    <td height="32" colspan="2" style="background-image:url(http://www.greatcollections.com/images/gc/box_blue_01.jpg);background-color:#607F9E;color:#FFFFFF;" ><b>Current Inventory </b></td>
  </tr>
  <tr >
    <td colspan="2" >View Current Inventory </td>
  </tr>
  <tr style="background:url(http://www.greatcollections.com/images/gc/box_blue_02.jpg); background-color:#B4D1F1;">
    <td colspan="2" >PCGS</td>
  </tr>
  <tr>
    <td width="77" bgcolor="#ededed"><a href="http://www.greatcollections.com/search.php?q=PCGS+MS-70+pl&mode=product&sort=01">MS-70 PL </a></td>
    <td width="99" bgcolor="#ededed"><a href="http://www.greatcollections.com/search.php?q=PCGS+MS-69+pl&mode=product&sort=01">MS-69 PL </a></td>
  </tr>
  <tr>
    <td bgcolor="#ededed"><a href="http://www.greatcollections.com/search.php?q=PCGS+MS-70+fs&mode=product&sort=01">MS-70 FS</a> </td>
    <td bgcolor="#ededed"><a href="http://www.greatcollections.com/search.php?q=PCGS+MS-69+fs&mode=product&sort=01">MS-69 FS</a> </td>
  </tr>
  <tr>
    <td bgcolor="#ededed"><a href="http://www.greatcollections.com/search.php?q=PCGS+MS-70&mode=product&sort=01">MS-70</a></td>
    <td bgcolor="#ededed"><a href="http://www.greatcollections.com/search.php?q=PCGS+MS-69&mode=product&sort=01">MS-69</a></td>
  </tr>

  <tr style="background:url(http://www.greatcollections.com/images/gc/box_blue_02.jpg);background-color:#B4D1F1;">
    <td colspan="2">NGC</td>
  </tr>
  <tr>
    <td bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?q=NGC+MS-70+pl&mode=product&sort=01">MS-70 PL </a></td>
    <td bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?q=NGC+MS-69+pl&mode=product&sort=01">MS-69 PL</a></td>
  </tr>
  <tr style="border-bottom:1px solid #B0C4CF;">
    <td bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?q=NGC+MS-70&mode=product&sort=01">MS-70</a></td>
    <td bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?q=NGC+MS-69&mode=product&sort=01">MS-69</a></td>
  </tr>
  </table><br>
  <table width="183" border="0" cellpadding="1" cellspacing="3" style="border:1px solid #B0C4CF;">
  <tr style="background:url(http://www.greatcollections.com/images/gc/box_blue_01.jpg);background-color:#607F9E;">
    <td height="32" colspan="2" style="color:#FFFFFF;"><b>Wanted to Buy </b></td>
  </tr>
  <tr>
    <td height="25" colspan="2" bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?mode=product&q=PCGS+MS-70+PL+FS&series=&sort=01">PCGS MS-70 PL FS</a> </td>
  </tr>
  <tr>
    <td height="25" colspan="2" bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?mode=product&q=PCGS+MS-70+PL+FS&series=&sort=01">PCGS MS-70 PL FS </a></td>
  </tr>
  <tr>
    <td height="25" colspan="2" bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?mode=product&q=NGC+MS-70+PL&series=&sort=01">NGC MS-70 PL </a></td>
  </tr>
  <tr>
    <td height="25" colspan="2" bgcolor="#EDEDED"><a href="http://www.greatcollections.com/search.php?q=NGC+MS-70&mode=product&sort=01">NGC MS-70 </a></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  
</table></td>
    <td width="319"><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and   typesetting industry. Lorem Ipsum has been the industrys standard dummy   text ever since the 1500s, when an unknown printer took a galley of   type and scrambled it to make a type specimen book. It has survived not   only five centuries, but also the leap into electronic typesetting,   remaining essentially unchanged. It was popularised in the 1960s with   the release of Letraset sheets containing Lorem Ipsum passages, and more   recently with desktop publishing software like Aldus PageMaker   including versions of Lorem Ipsum.</p>
	<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
   </td>
  </tr>
</table><br>';
	
$foot = '<div> 
          	<div style="height:80px; background:url(http://www.greatcollections.com/images/gc/footer_blue.jpg) repeat-x top;background-color:#2D598A;">	
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr><td>&nbsp;</td></tr>
		    
			<tr>
  <td align="center" style="border-collapse:collapse;text-align:center;"><a href="https://www.greatcollections.com/main.php?cmd=cp&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">MyGC</a></td>
  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.greatcollections.com/watchlist.php?referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Watchlist</a></td>

  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.greatcollections.com/main.php?cmd=about&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">About Us</a></td>
  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.greatcollections.com/kb/" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Help</a></td>
  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.greatcollections.com/main.php?cmd=contact&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Contact Us</a></td>
  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.greatcollections.com/main.php?cmd=promise&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Trust</a></td>
  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.greatcollections.com/main.php?cmd=shows&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Coin Shows</a></td>
</tr>
 <tr><td>&nbsp;</td></tr>
 
      </table> 
	  
	 	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
          
			<tr>
  <td align="center" style="border-collapse:collapse;text-align:center;"><a href="https://www.greatcollections.com/main.php?cmd=through&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Bidding/Buying Coins Through GreatCollections</a></td>
  
  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://www.facebook.com/GreatCollections" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Facebook</a></td>

  <td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;text-align:center;"><a href="http://twitter.com/greatcollection" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#fff; text-decoration:none; font-weight:bold;">Follow Us On Twitter</a></td>
  
 
</tr>
 
      </table> 
			
			</div>
	  
	  
          	<div style="height:34px; background:url(http://www.greatcollections.com/images/gc/footer_green.jpg) repeat-x top;background-color:#7A9102; color: #fff; font-size: 10px; margin: 0; padding: 10px 0;text-align:center"> 
            	This message was sent to {EMAIL_ADDRESS}.<br />
				Please add <a href="mailto:info@greatcollections.com" style="font-family:verdana,arial; font-size:10px; color:#336699; color:#336699; text-decoration:none;">info@greatcollections.com</a> to your address book to ensure our emails reach your inbox!
            </div>
          
			<div style="height:70px; background-color: #f9f9ed; color: #555555; font-size: 10px; margin: 0; padding: 10px 0; text-align:center">&#169; 2010 - '.CURRENTYEAR.'  GreatCollections.com, LLC<br />
							2030 Main Street, Suite 620, Irvine, CA 92614<br />
							Tel: 1.800.44.COINS (+1.949.679.4180) 
		  	</div>
        </div></div></div>';
	 
	
	// $all = $head.''.$ilance->GPC['user'].''.$foot;
$_SESSION['ilancedata']['time'] = date('Y-m-d-h-i-s');	
 $all = $head.''.$body.''.$foot;
 //$all=$head.''.$featured.''.$_SESSION['ilancedata']['user']['item'].''.$foot;
$handle = $all;
$f=@fopen(DIR_SERVER_ROOT."email_template/UHR_template_".$_SESSION['ilancedata']['time'].".html","w");
fwrite($f,$handle);
fclose($f);

exit();

}


        $pprint_array = array('couo','myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','latestviews','list','myfeat','subheading','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

         ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	    $ilance->template->fetch('main', 'uhr_template.html',2);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val','series_list');
		$ilance->template->parse_loop('main', 'info_feat');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
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