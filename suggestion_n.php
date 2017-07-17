<?php

include('functions/config.php');

$searchpart=addslashes(trim($ilance->GPC['query']));
$mode=isset($ilance->GPC['mode'])?addslashes($ilance->GPC['mode']):'product';
if(strlen($searchpart)>0)
{

if($mode=='product')
{
	echo "SELECT project_id,project_title FROM " . DB_PREFIX . "projects WHERE (project_title LIKE '%$searchpart%' OR project_id = '".$searchpart."' ) AND status = 'open' AND visible = 1 LIMIT 50";
	exit;
	
	$result=$ilance->db->query("SELECT project_id,project_title FROM " . DB_PREFIX . "projects WHERE (project_title LIKE '%$searchpart%' OR project_id = '".$searchpart."' ) AND status = 'open' AND visible = 1 LIMIT 50");
	$count=$ilance->db->num_rows($result);
	if($ilance->db->num_rows($result)>0)
	{
	$i=0;
	$html="{query:'".$searchpart."',suggestions:[";
		while($row=$ilance->db->fetch_assoc($result))
		{
		/*
		{
		 query:'Li',
		 suggestions:['Liberia','Libyan Arab Jamahiriya','Liechtenstein','Lithuania'],
		 data:['LR','LY','LI','LT']
		}

		*/
		//echo '<div id="link"  onclick="addText(\"'.$row['project_title'].'\");"> ' . searchThat($searchpart,$row['project_title']) .'</div>';
$suggestions[$i]="'".$row['project_title']."'";
$ids[$i]="'".$row['project_id']."'";
$i++;
		}
	$html.=implode(",",$suggestions);
	$html.="],data:[".implode(",",$ids)."]}";
		echo $html;
	}
}
}
function searchThat($parttitle,$realtitle)
{
$length = strlen($parttitle);
$length1 = strlen($realtitle);
$mainlength=$length-$length1;
$notitle=substr($realtitle,0,$length);
$availtitle=substr($realtitle,$mainlength);
return  $notitle."<strong>" . $availtitle . "</strong>";
}

?>