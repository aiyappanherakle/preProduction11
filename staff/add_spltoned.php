<?php
 
$phrase['groups'] = array('rfp'); 
$topnavlink = array('main_listings');
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(0);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	
	
if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=="add_spltoned")
{
	
	if(strlen($ilance->GPC['coin_list'])==0)
	{
		print_action_failed("We're sorry. The textarea is empty.", $_SERVER['PHP_SELF']);exit();
	}
	
	$SQL=0;
	if(strstr($ilance->GPC['coin_list'],'to'))
	{
		if(count(explode('to',$ilance->GPC['coin_list']))==2)
		{
			$coinlist=explode('to',trim($ilance->GPC['coin_list']));
			$coin_list=array_map('trim', $coinlist);
			
			$firtno=$coin_list[0];
			$secondno=$coin_list[1];
			
				for($list = $firtno; $list <= $secondno; $list++) 
				{
					$cins_array[] = $list;
					
				}
							
			$SQL="SELECT p.project_id,p.spl_toned FROM " . DB_PREFIX . "projects p
			left join " . DB_PREFIX . "users u on p.user_id = u.user_id WHERE u.status='active' and p.status='open' and project_id between ".$coin_list[0]." and ".$coin_list[1]."";
			
		
		}else
		{
			print_action_failed("We're sorry. Only one line is allowed in the test area if you are using a range ", $_SERVER['PHP_SELF']);exit();
		}
	}
	else
	{
		
	$coin_list=$ilance->GPC['coin_list'];
	
	$cins_array = explode(",", $coin_list);
		
	$SQL="SELECT p.project_id,p.spl_toned FROM " . DB_PREFIX . "projects p
		left join " . DB_PREFIX . "users u on p.user_id = u.user_id
		WHERE p.project_id in (".$coin_list.") and u.status='active' and p.status='open'";
	}
	
	
	$result=$ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
	$hotlist_count=$ilance->db->num_rows($result);
	
	
		
	if($hotlist_count > 0)
	{
		$rowcount=0;
		while($line = $ilance->db->fetch_array($result))
		{
			
			if ($line['project_id'] > 0) 
			{		
						
			$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
			 SET spl_toned='1'	
			 WHERE  project_id = '".$line['project_id']."'");
			
			$row[]=$line['project_id'];
									
			$rowcount++;
					
			}
			
					
		}
		
		
		$cinsresult = count($cins_array);
	
		
		$notlisted=array();
		for($i=0;$i< $cinsresult;$i++)
		{	
			if (!in_array($row[$i], $cins_array)) {
			$notlisted[]= $cins_array[$i];	
			}
		}
		$notlist = implode(",",$notlisted);
		$tot=count($notlisted);
		
		if($tot > 0)
		{
			$Details="<br/><br/><div style='width:800px; word-wrap: break-word;'>Not listed:".$notlist."</div>";
		}						
		print_action_success("Task have been successfully completed.".count($row)." No of coins will be added in spl toned from now, you can check it in the user end.".$Details, $_SERVER['PHP_SELF']);exit();	
			
	}
	
	else
	{
		print_action_failed("We're sorry. The given coins not in live.", $_SERVER['PHP_SELF']);exit();
	}
	
	
}
else
{
	
	$pprint_array = array('coin_list','headinclude','form_action','buildversion','ilanceversion','login_include_admin','clientip','cmd','remember_checked','input_style','redirect','referer','rid','login','admin_cookie','login_include','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'add_spltoned.html', 2);
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
