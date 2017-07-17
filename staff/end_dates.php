<?php
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$p[]=131580;
$p[]=132657;
$p[]=132659;
$p[]=132677;
$p[]=128961;
$p[]=128971;
$p[]=128981;
$p[]=128988;
$p[]=128998;
$p[]=129008;
$p[]=129015;
$p[]=129019;
$p[]=129024;
$p[]=129037;
$p[]=129047;
$p[]=129052;
$p[]=129061;
$p[]=129073;
$p[]=129089;
$p[]=129721;
$p[]=129731;
$p[]=129740;
$p[]=129750;
$p[]=129753;
$p[]=129760;
$p[]=129769;
$p[]=129781;
$p[]=129795;
$p[]=132437;

foreach($p as $project)
{
$sql="SELECT *  FROM " . DB_PREFIX . "projects WHERE  project_id='".$project."'";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo $line['project_id'].'---'.$line['user_id'].'---'.$line['date_end'];
		//echo $sqll="update " . DB_PREFIX . "projects set date_end=concat('2013-08-18 ',time(date_end)) where project_id='".$project."';";
		//echo '<br>';
		//echo $sqll="update " . DB_PREFIX . "coins set End_date=concat('2013-08-18 ',time(End_date)) where coin_id='".$project."';";
		echo '<br>';
	}
}
}
}
?>