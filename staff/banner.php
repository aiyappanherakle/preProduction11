<?php 
/* Tamil */
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
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');
error_reporting(E_ALL);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	
	if(isset($ilance->GPC['image_form_submit']))
	{	 
			$images_arr = array();
			$unique_list_banners = array();
			$row_con_list=0;
 			$items  = array();
			foreach($_FILES['files']['name'] as $key=>$val)
			{
				$image_name = $_FILES['files']['name'][$key];
				$tmp_name 	= $_FILES['files']['tmp_name'][$key];
				$size 		= $_FILES['files']['size'][$key];
				$type 		= $_FILES['files']['type'][$key];
				$error 		= $_FILES['files']['error'][$key];
				if($size =='')
				{
					print_action_failed("We're sorry. Please Upload valid image files details", $_SERVER['PHP_SELF']);
			        exit();
				}
				if ($type != "image/jpeg")
				{
					print_action_failed("We're sorry. Please Upload valid .jpg extension image files", $_SERVER['PHP_SELF']);
			        exit();
				}
 				$string = implode("_",array_reverse(explode("_",$image_name)));                
                $result= explode("_", $string);
                $sqlquery = $ilance->db->query("select * from ".DB_PREFIX."coins where coin_id='".$result[2]."'");
				$project_res_seller = $ilance->db->fetch_array($sqlquery); 
				if($project_res_seller['coin_id'] == '')
				{
					$items[] = $image_name;
				}
				$images_arr[$tmp_name] = $image_name;
				$unique_list_banners[] = $result[2];
				$row_con_list++;
 			}
 			$unique_elements = array_unique($unique_list_banners);
			$totalUniqueElements = count($unique_elements); 
 			if ($row_con_list != $totalUniqueElements){
			print_action_failed("Please Upload valid Unique Banners file details", $_SERVER['PHP_SELF']);
			exit;
			}
			$Check_coin       = implode("<br>",$items);
		 	$tot_Check_coins  = count($items);
			if($tot_Check_coins > 0)
			{		
			print_action_failed("Please Check the following files coins Id in Live GC:<br> ".$Check_coin."", $_SERVER['PHP_SELF']);
			exit;  	 
		 	}		
 	
 			$sucess = array();
			$fail = array();
			foreach($_FILES['files']['name'] as $key=>$val)
		    {
		    	$res_image_name = $_FILES['files']['name'][$key];
				$res_tmp_name 	= $_FILES['files']['tmp_name'][$key];
				$res_size 		= $_FILES['files']['size'][$key];
				$res_type		= $_FILES['files']['type'][$key];
				$res_error 		= $_FILES['files']['error'][$key];
				$hash=md5(microtime());
		    	$coinid = implode("_",array_reverse(explode("_",$res_image_name)));                
                $coinid_res= explode("_", $coinid);  
		    	$uploads_dir =DIR_SERVER_ROOT."banner/images/";
		        if(move_uploaded_file($_FILES['files']['tmp_name'][$key], $uploads_dir.$_FILES['files']['name'][$key]))
        		{

        			$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."coins where coin_id='".$coinid_res[2]."' ");
						$project_res_seller = $ilance->db->fetch_array($sqlquery);
                    $text = substr($project_res_seller['Title'],0,75);
					$inner_ttt='View the '.$text.' at GC';
					$inner_link=''.HTTP_SERVER.'Coin/'.$project_res_seller['coin_id'].'/'.construct_seo_url_name($project_res_seller['Title']).'';
					$projectid=$project_res_seller['coin_id']; 
					$ilance->db->query("insert into ".DB_PREFIX."banner 
					values (0,'".$res_image_name."','".$res_type."','".$res_size."','".$hash."','".$inner_link."','".$inner_ttt."','".$inner_link."','0','0','".DATETIME24H."','','','','','','".$projectid."')");
					 $sucess[] = $res_image_name;
        		}
 		    }
 		    $sucess_coin       = implode("<br>",$sucess);
		 	$tot_sucess_coins  = count($sucess);
			if($tot_sucess_coins > 0)
			{		
			print_action_success("Listed Banner files successfully Uploaded in Live GC:<br> ".$sucess_coin."", $_SERVER['PHP_SELF']);
			exit;  	 
		 	}

	}

	$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'banner.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	 	
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

 


?>