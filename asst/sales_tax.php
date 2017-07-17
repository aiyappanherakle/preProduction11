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
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND ($_SESSION['ilancedata']['user']['isstaff'] == '2' OR $_SESSION['ilancedata']['user']['isstaff'] == '1' OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
{

		$daylist = '';
		$monthlist = '';
		$yearlist = '';
		$daylist .='<select name="day" id="day"><option value="">DATE</option>';
				$day = date('d')-1;
				for($i=1; $i<=31; $i++)
				if($day == $i)
				$daylist .= "<option value='$i' selected>$i</option>";
				else
				$daylist .= "<option value='$i'>$i</option>";

		$daylist .='</select>';
		
		$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
		$month = date('m');
				for($j=1; $j<=12; $j++)
				
				if($month == $j)
				$monthlist .= "<option value='$j' selected>$j</option>";
				else
				$monthlist .= "<option value='$j'>$j</option>";
				
				
		$monthlist .= '</select>';
		
		$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
		
		
		$year = date("Y");
		for ($k = $year+1;$k > 2008;$k--) 
		{
		$s = ($k == $year)?' selected':'';
			$yearlist .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
		}
		$yearlist .='</select>';
		// Date Month Year End
				
		$daylist1 = '';
		$monthlist1 = '';
		$yearlist1 = '';
		$daylist1 .='<select name="day1" id="day"><option value="">DATE</option>';
				$day = date('d')-1;
				for($i=1; $i<=31; $i++)
				if($day == $i)
				$daylist1 .= "<option value='$i' selected>$i</option>";
				else
				$daylist1 .= "<option value='$i'>$i</option>";

		$daylist1 .='</select>';
		
		$monthlist1 .='<select name="month1" id="month"><option value="">MONTH</option>';
		$month = date('m');
				for($j=1; $j<=12; $j++)
				
				if($month == $j)
				$monthlist1 .= "<option value='$j' selected>$j</option>";
				else
				$monthlist1 .= "<option value='$j'>$j</option>";
				
				
		$monthlist1 .= '</select>';
		
		$yearlist1 .= '<select name="year1" id="year"><option value="">YEAR</option>';
		
		
		$year = date("Y");
		for ($k = $year+1;$k > 2008;$k--) 
		{
		$s = ($k == $year)?' selected':'';
			$yearlist1 .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
		}
		$yearlist1 .='</select>';
		// Date1 Month1 Year1 End
				
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'all_sales') 
	{
	
		//start All Sales Tax CSV 
		
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'all_sales_tax')
		{
		
			$startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
			$start =  date('Y-m-d',strtotime( $startdate));
			$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
			$end =  date('Y-m-d',strtotime( $enddate));
		
			header("Location:salestax_all_csv.php?start_date=".$start."&end_date=".$end."");
		}
		
		//end All Sales Tax CSV 
		
	
		$pprint_array = array('daylist','monthlist','yearlist','daylist1','monthlist1','yearlist1','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'sales_all_tax.html', 3);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
	
		//start Sales Tax PDF 
		
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'sales_tax')
		{
		
			$startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
			$start =  date('Y-m-d',strtotime( $startdate));
			$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
			$end =  date('Y-m-d',strtotime( $enddate));
			
			header("Location:salestax_pdf.php?start_date=".$start."&end_date=".$end."");
		}
		
		//end Sales Tax PDF
	
		$pprint_array = array('daylist','monthlist','yearlist','daylist1','monthlist1','yearlist1','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'sales_tax.html', 3);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}   

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>	
	