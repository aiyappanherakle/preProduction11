<?php 
include('../functions/config.php');
error_reporting(E_ALL);
$v=' through ' ;
$count=0;
$to_bid_list=array();
$incompatible_user_list=array();
$only_incoin_table=array();
if(isset($_POST['submit']))
{
$t=$_POST['p'];

$t_list=explode('<br />',nl2br($t));
foreach($t_list as $row_list)
{
	$row=explode($v,$row_list);
	asort($row);
	if(isset($row[1]) and $row[1]>1)
	{
	$remembered_user_id=0;
	for($i=$row[0];$i<=$row[1];$i++)
	{
		
		$query="SELECT user_id FROM " . DB_PREFIX . "projects WHERE project_id = '".$i."' and status='open'";
		$result=$ilance->db->query($query);
		if($ilance->db->num_rows($result))
		{
			
			while($line=$ilance->db->fetch_array($result))
			{
				//echo $i.'|'.$line['user_id'].'|'.$remembered_user_id.'<br>';
				if($remembered_user_id!=0 and $remembered_user_id!=$line['user_id'])
				{
					echo $remembered_user_id.'|'.$line['user_id'].'|'.$i.'<br>';
					$incompatible_user_id['check_user_id']=$remembered_user_id;
					$incompatible_user_id['actual_user_id']=$line['user_id'];
					$incompatible_user_id['project_id']=$i;
					$incompatible_user_list[$count]=$incompatible_user_id;
					unset($incompatible_user_id);
					$count++;
				}else
				{
				$remembered_user_id=$line['user_id'];
				$to_bid_list[]=$i;
				}
			
			}
			
		}else
		{
		$only_incoin_table[]=$i;
		}
 	
	}
	unset($remembered_user_id);
	}else
	{
	$to_bid_list[]=$row[0];
	}

}

foreach($to_bid_list as $id)
{
echo '<a href="http://www.greatcollections.com/Coin/'.$id.'">"http://www.greatcollections.com/Coin/'.$id.'"</a><br>';
}
echo 'incompatible<pre>';
print_r($incompatible_user_list);

echo 'only coin table<pre>';
print_r($only_incoin_table);
}
?>
<form action="" method="post">
<textarea name="p"><?php echo $_POST['p']?></textarea>
<input type="submit" name="submit" value="submit">
</form>