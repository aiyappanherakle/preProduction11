<?php
error_reporting(E_ALL);
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
define('pop_files_upload_dir',DIR_SERVER_ROOT.'upload_pop/');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{

   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'upload')
   {
	switch($ilance->GPC['subcmd'])
	{
	case 'pcgs_population':
	   $pcgs_pop_filename=fetch_pps('filename',1);
   	   $pcgs_pop_tablename=fetch_pps('table_name',1);
	   
		$check1=$ilance->db->query("TRUNCATE TABLE " . DB_PREFIX . $pcgs_pop_tablename);
		if (($handle = fopen(pop_files_upload_dir.$pcgs_pop_filename, "r")) !== FALSE) 
		{
		while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
		$i=0;
		foreach($line as $temp)
		{
		$line[$i]=mysql_escape_string($temp);
		$i++;
		}
			$rows[$row]=$line;
			$row++;
		}
		}
		$i=1;
		$skip_rows=1;
		foreach($rows as $new_csv1)
		{
		if($i>$skip_rows)
		{
			$val=$ilance->db->query("insert into " . DB_PREFIX .$pcgs_pop_tablename. "(spec,year,denom,variety,prefix,suffix,country,pcgsunused,desc1,grade1,grade2,grade3,grade4,grade6,grade8,grade10,grade12,grade15,grade20,grade25,grade30,grade35,grade40,grade45,grade45plus,grade50,grade50plus,grade53,grade53plus,grade55,grade55plus,grade58,grade58plus,grade60,grade61,grade62,grade62plus,grade63,grade63plus,grade64,grade64plus,grade65,grade65plus,grade66,grade66plus,grade67,grade67plus,grade68,grade68plus,grade69,grade70,total,modifieddate) values('".$new_csv1[0]."','".$new_csv1[1]."','".$new_csv1[2]."','".$new_csv1[3]."','".$new_csv1[4]."','".$new_csv1[5]."','".$new_csv1[6]."','".$new_csv1[7]."','".$new_csv1[8]."','".$new_csv1[9]."','".$new_csv1[10]."','".$new_csv1[11]."','".$new_csv1[12]."','".$new_csv1[13]."','".$new_csv1[14]."','".$new_csv1[15]."','".$new_csv1[16]."','".$new_csv1[17]."','".$new_csv1[18]."','".$new_csv1[19]."','".$new_csv1[20]."','".$new_csv1[21]."','".$new_csv1[22]."','".$new_csv1[23]."','".$new_csv1[24]."','".$new_csv1[25]."','".$new_csv1[26]."','".$new_csv1[27]."','".$new_csv1[28]."','".$new_csv1[29]."','".$new_csv1[30]."','".$new_csv1[31]."','".$new_csv1[32]."','".$new_csv1[33]."','".$new_csv1[34]."','".$new_csv1[35]."','".$new_csv1[36]."','".$new_csv1[37]."','".$new_csv1[38]."','".$new_csv1[39]."','".$new_csv1[40]."','".$new_csv1[41]."','".$new_csv1[42]."','".$new_csv1[43]."','".$new_csv1[44]."','".$new_csv1[45]."','".$new_csv1[46]."','".$new_csv1[47]."','".$new_csv1[48]."','".$new_csv1[49]."','".$new_csv1[50]."','".$new_csv1[51]."','".DATETIME24H."')");
		}		
		$i++;
		}		
		$ilance->db->query("update " . DB_PREFIX ."pps_conf set uploadeddate='".DATETIME24H."' where id=1");
		fclose($handle);	
	break;
	case 'ngc_population':
		$ngc_pop_filename=fetch_pps('filename',2);
   	   	$ngc_pop_tablename=fetch_pps('table_name',2);
	   
		$check1=$ilance->db->query("TRUNCATE TABLE " . DB_PREFIX . $ngc_pop_tablename);
		if (($handle = fopen(pop_files_upload_dir.$ngc_pop_filename, "r")) !== FALSE) 
		{
		while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
		$i=0;
		foreach($line as $temp)
		{
		$line[$i]=mysql_escape_string($temp);
		$i++;
		}
			$rows[$row]=$line;
			$row++;
		}
		}
		$i=1;
		$skip_rows=3;
		foreach($rows as $new_csv)
		{
		if($i>$skip_rows)
		{
			 $coininsert=$ilance->db->query("INSERT INTO " . DB_PREFIX . $ngc_pop_tablename ."(`IsSummary`,`PrintOrder`,`MajorCategory`,`CatDesc`,`Coin`,`SumNum`,`Type`,`Date`,`Mint`,`Denom`,`Variety`,`Des`,`BarCode`,`Graded`,`PrAg`,`PrAg*`,`1`,`1*`,`2`,`2*`,`3`,`3*`,`G`,`G*`,`4`,`4*`,`6`,`6*`,`VG`,`VG*`,`8`,`8*`,`10`,`10*`,`F`,`F*`,`12`,`12*`,`15`,`15*`,`VF`,`VF*`,`20`,`20*`,`25`,`25*`,`30`,`30*`,`35`,`35*`,`40`,`40*`,`45`,`45*`,`45+`,`45+*`,`50`,`50*`,`50+`,`50+*`,`53`,`53*`,`53+`,`53+*`,`55`,`55*`,`55+`,`55+*`,`58`,`58*`,`58+`,`58+*`,`60`,`60*`,`60+`,`60+*`,`61`,`61*`,`61+`,`61+*`,`62`,`62*`,`62+`,`62+*`,`63`,`63*`,`63+`,`63+*`,`64`,`64*`,`64+`,`64+*`,`65`,`65*`,`65+`,`65+*`,`66`,`66*`,`66+`,`66+*`,`67`,`67*`,`67+`,`67+*`,`68`,`68*`,`68+`,`68+*`,`69`,`69*`,`70`,`70*`,`date_modified`)
	VALUES(
	'".$new_csv[0]."',
'".$new_csv[ 1]."',
'".$new_csv[ 2]."',
'".$new_csv[ 3]."',
'".$new_csv[ 4]."',
'".$new_csv[ 5]."',
'".$new_csv[ 6]."',
'".$new_csv[ 7]."',
'".$new_csv[ 8]."',
'".$new_csv[ 9]."',
'".$new_csv[ 10]."',
'".$new_csv[ 11]."',
'".$new_csv[ 12]."',
'".$new_csv[ 13]."',
'".$new_csv[ 14]."',
'".$new_csv[ 15]."',
'".$new_csv[ 16]."',
'".$new_csv[ 17]."',
'".$new_csv[ 18]."',
'".$new_csv[ 19]."',
'".$new_csv[ 20]."',
'".$new_csv[ 21]."',
'".$new_csv[ 22]."',
'".$new_csv[ 23]."',
'".$new_csv[ 24]."',
'".$new_csv[ 25]."',
'".$new_csv[ 26]."',
'".$new_csv[ 27]."',
'".$new_csv[ 28]."',
'".$new_csv[ 29]."',
'".$new_csv[ 30]."',
'".$new_csv[ 31]."',
'".$new_csv[ 32]."',
'".$new_csv[ 33]."',
'".$new_csv[ 34]."',
'".$new_csv[ 35]."',
'".$new_csv[ 36]."',
'".$new_csv[ 37]."',
'".$new_csv[ 38]."',
'".$new_csv[ 39]."',
'".$new_csv[ 40]."',
'".$new_csv[ 41]."',
'".$new_csv[ 42]."',
'".$new_csv[ 43]."',
'".$new_csv[ 44]."',
'".$new_csv[ 45]."',
'".$new_csv[ 46]."',
'".$new_csv[ 47]."',
'".$new_csv[ 48]."',
'".$new_csv[ 49]."',
'".$new_csv[ 50]."',
'".$new_csv[ 51]."',
'".$new_csv[ 52]."',
'".$new_csv[ 53]."',
'".$new_csv[ 54]."',
'".$new_csv[ 55]."',
'".$new_csv[ 56]."',
'".$new_csv[ 57]."',
'".$new_csv[ 58]."',
'".$new_csv[ 59]."',
'".$new_csv[ 60]."',
'".$new_csv[ 61]."',
'".$new_csv[ 62]."',
'".$new_csv[ 63]."',
'".$new_csv[ 64]."',
'".$new_csv[ 65]."',
'".$new_csv[ 66]."',
'".$new_csv[ 67]."',
'".$new_csv[ 68]."',
'".$new_csv[ 69]."',
'".$new_csv[ 70]."',
'".$new_csv[ 71]."',
'".$new_csv[ 72]."',
'".$new_csv[ 73]."',
'".$new_csv[ 74]."',
'".$new_csv[ 75]."',
'".$new_csv[ 76]."',
'".$new_csv[ 77]."',
'".$new_csv[ 78]."',
'".$new_csv[ 79]."',
'".$new_csv[ 80]."',
'".$new_csv[ 81]."',
'".$new_csv[ 82]."',
'".$new_csv[ 83]."',
'".$new_csv[ 84]."',
'".$new_csv[ 85]."',
'".$new_csv[ 86]."',
'".$new_csv[ 87]."',
'".$new_csv[ 88]."',
'".$new_csv[ 89]."',
'".$new_csv[ 90]."',
'".$new_csv[ 91]."',
'".$new_csv[ 92]."',
'".$new_csv[ 93]."',
'".$new_csv[ 94]."',
'".$new_csv[ 95]."',
'".$new_csv[ 96]."',
'".$new_csv[ 97]."',
'".$new_csv[ 98]."',
'".$new_csv[ 99]."',
'".$new_csv[ 100]."',
'".$new_csv[ 101]."',
'".$new_csv[ 102]."',
'".$new_csv[ 103]."',
'".$new_csv[ 104]."',
'".$new_csv[ 105]."',
'".$new_csv[ 106]."',
'".$new_csv[ 107]."',
'".$new_csv[ 108]."',
'".$new_csv[ 109]."',
'".$new_csv[ 110]."',
'".$new_csv[ 111]."','".$modified."')") or die(mysql_error());	
		}		
		$i++;
		}		
		$ilance->db->query("update " . DB_PREFIX ."pps_conf set uploadeddate='".DATETIME24H."' where id=2");
		fclose($handle);
	break;
	case 'pcgs_price':
	   $pcgs_price_filename=fetch_pps('filename',3);
   	   $pcgs_price_tablename=fetch_pps('table_name',3);
	   
		$check1=$ilance->db->query("TRUNCATE TABLE " . DB_PREFIX . $pcgs_price_tablename);
		if (($handle = fopen(pop_files_upload_dir.$pcgs_price_filename, "r")) !== FALSE) 
		{
		while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
		$i=0;
		foreach($line as $temp)
		{
		$line[$i]=mysql_escape_string($temp);
		$i++;
		}
				$rows[$row]=$line;
			$row++;
		}
		}
		$i=1;
		$skip_rows=2;
		foreach($rows as $data)
		{
		if($i>$skip_rows)
		$coininsert=$ilance->db->query("INSERT INTO " . DB_PREFIX . $pcgs_price_tablename."(pcgs, grade, pcgspriceguidevalue, plus, datetime)
				VALUES('".$data[0]."','".$data[1]."','".$data[2]."','".$data[3]."','".DATETIME24H."')") or die(mysql_error());		
				$i++;
		}		
		$ilance->db->query("update " . DB_PREFIX ."pps_conf set uploadeddate='".DATETIME24H."' where id=3");
		fclose($handle);
	break;
	
	case 'ngc_price':
 		$ngc_price_filename=fetch_pps('filename',4);
   	   	$ngc_price_tablename=fetch_pps('table_name',4);
	   
		$check1=$ilance->db->query("TRUNCATE TABLE " . DB_PREFIX . $ngc_price_tablename);
		if (($handle = fopen(pop_files_upload_dir.$ngc_price_filename, "r")) !== FALSE) 
		{
		while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
		$i=0;
		foreach($line as $temp)
		{
		$line[$i]=mysql_escape_string($temp);
		$i++;
		}
				$rows[$row]=$line;
			$row++;
		}
		}
		$i=1;
		$skip_rows=4;
		foreach($rows as $new_csv)
		{
		if($i>$skip_rows)
		$val=$ilance->db->query("insert into " . DB_PREFIX . $ngc_price_tablename."(NMCode,PCGS,Description,G4,VG8,F12,VF20,XF40,AU50,AU53,AU55,AU58,MS60,MS61,MS62,MS63,MS64,MS65,MS66,MS67,MS68,MS69,MS70,pricecode,NGCCode,NMcode1,PR,end,modifieddate) values('".$new_csv[0]."','".$new_csv[1]."','".$new_csv[2]."','".$new_csv[3]."','".$new_csv[4]."','".$new_csv[5]."','".$new_csv[6]."','".$new_csv[7]."','".$new_csv[8]."','".$new_csv[9]."','".$new_csv[10]."','".$new_csv[11]."','".$new_csv[12]."','".$new_csv[13]."','".$new_csv[14]."','".$new_csv[15]."','".$new_csv[16]."','".$new_csv[17]."','".$new_csv[18]."','".$new_csv[19]."','".$new_csv[20]."','".$new_csv[21]."','".$new_csv[22]."','".$new_csv[23]."','".$new_csv[24]."','".$new_csv[25]."','".$new_csv[26]."','".$new_csv[27]."','".$datemodified1."')");
						
				
				$i++;
		}		
		$ilance->db->query("update " . DB_PREFIX ."pps_conf set uploadeddate='".DATETIME24H."' where id=4");
		fclose($handle);
	break;
	}
 
   }else
   {
   
   $pcgs_pop_filename=fetch_pps('filename',1);
   $pcgs_pop_moddate=modified_date(pop_files_upload_dir.$pcgs_pop_filename);
   $pcgs_pop_update=fetch_pps('uploadeddate',1);
   
   $ngc_pop_filename=fetch_pps('filename',2);
   $ngc_pop_moddate=modified_date(pop_files_upload_dir.$ngc_pop_filename);
   $ngc_pop_update=fetch_pps('uploadeddate',2);
   
   $pcgs_price_filename=fetch_pps('filename',3);
   $pcgs_price_moddate=modified_date(pop_files_upload_dir.$pcgs_price_filename);
   $pcgs_price_last_update=fetch_pps('uploadeddate',3);
   
   $ngc_price_filename=fetch_pps('filename',4);
   $ngc_price_moddate=modified_date(pop_files_upload_dir.$ngc_price_filename);
   $ngc_price_last_update=fetch_pps('uploadeddate',4);
  
   $pop_upload_dir=pop_files_upload_dir;
   	$pprint_array = array('pop_upload_dir','pcgs_pop_filename','pcgs_pop_moddate','pcgs_pop_update','ngc_pop_filename','ngc_pop_moddate','ngc_pop_update','pcgs_price_filename','pcgs_price_moddate','pcgs_price_last_update','ngc_price_filename','ngc_price_moddate','ngc_price_last_update','site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'population_area.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('holding_area_list'));
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

function modified_date($file)
{
$modified_date="File not found";
if(is_file($file))
{
 $modified_date=date ("y/m/d h:i:s", filemtime($file));
}
return $modified_date;
}

function fetch_pps($column_name="title",$id=0)
{
global $ilance;
$query="select ".$column_name." as column_name from ".DB_PREFIX."pps_conf where id=".$id;
$result=$ilance->db->query($query);
$value=0;
if($ilance->db->num_rows($result))
{
$line=$ilance->db->fetch_array($result);
$value=$line['column_name'];
}
return $value;
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>