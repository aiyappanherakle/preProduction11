<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base1.0.8 Build 85
|| # -------------------------------------------------------------------- # ||
|| # Customer License # KapIxNXTSUYf3LjCGHiWk1XevwZ-ISZStLboZ-ErQdU-pATvJ3
|| # -------------------------------------------------------------------- # ||
|| # Copyright �2000-2011 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

function getSCategories($catid, $level)
{
	global $ilance, $appendstr;	
	$catid = intval($catid);
	$result = $ilance->db->query("SELECT * FROM ".DB_PREFIX."kbcategory 
	WHERE categoryid = '".$catid."'");
	if ($obj = $ilance->db->fetch_object($result)) 
	{
		$appendstr = $appendstr . "<option value='".$catid."'>";
		$spacer = "";
		for ($i=0; $i<$level; $i++)
		$spacer = $spacer." -- ";
		$appendstr = $appendstr . $spacer . $obj->catname."</option>\n";
	}
	$result = $ilance->db->query("SELECT * FROM ".DB_PREFIX."kbcategory 
	WHERE parent = '".$catid."'");
	while ($obj = $ilance->db->fetch_object($result))
	{
		$catid = $obj->categoryid;
		getSCategories($catid, ($level+1));
	}
}

if (isset($ilance->GPC['keyword']))
{
	$keyword = $ilance->db->escape_string(strip_tags(trim($ilance->GPC['keyword'])));
}

if (isset($ilance->GPC['scatid']))
{
	$scatid = intval($ilance->GPC['scatid']);
}

if (isset($ilance->GPC['match']))
{
	$match = intval($ilance->GPC['match']);
}

$sql = "";

// search all categories
if (!isset($scatid) AND isset($keyword)) 
{
	$sql = "SELECT * FROM ".DB_PREFIX."kbposts WHERE approved = '1' 
	AND (keywords LIKE '%".$keyword."%' OR subject LIKE '%".$keyword."%' OR answer LIKE '%".$keyword."%')";
}
else if (isset($scatid)) 
{
	// search specific categories
	if (!isset($match)) 
	{
		$sql = "SELECT * FROM ".DB_PREFIX."kbposts WHERE approved = '1' 
		AND (keywords LIKE '%".$keyword."%' OR subject LIKE '%".$keyword."%' OR answer LIKE '%".$keyword."%')";
	}
	else 
	{
		$sql = "SELECT * FROM ".DB_PREFIX."kbposts WHERE approved = '1' 
		AND (keywords REGEXP '[[:<:]]".$keyword."[[:>:]]' OR subject REGEXP '[[:<:]]".$keyword."[[:>:]]' OR answer REGEXP '[[:<:]]".$keyword."[[:>:]]')";
	}
	
	if ($scatid != '-1')
	{
		$sql = $sql. " AND catid = '".$scatid."'";
	}
}
?>

<?php
if (!empty($sql))	
{
		if ($ilance->lancekb->config['enableseo'])
	{
	    $kboptions = '
	<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
	<li title="" class=""><a href="' . HTTP_KB . 'top-10-viewed.html">' . $phrase['_top_10_articles_viewed'] . '</a></li>
	';   
	}
	else
	{
	    $kboptions = '
	<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
	<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=1">' . $phrase['_top_10_articles_viewed'] . '</a></li>
';
	}
?>

<div class="bigtabs" style="padding-bottom:10px; padding-top:0px">
<div class="bigtabsheader">
<ul>
        <?php echo $kboptions; ?>
</ul>
</div>
</div>

<div style="clear:both;"></div>
<div style="padding-bottom:5px"></div>

<div class="block-wrapper">
	<div class="block5">
	
			<div class="block5-top">
					<div class="block5-right">
							<div class="block5-left"></div>
					</div>
			</div>
			
			<div class="block5-header"><?php echo $phrase['_search_results']; ?></div>
			<!--<div class="block5-content-gray" style="padding:9px"><div class="smaller">Please find articles below relating to your keyword search</div></div>-->
			<div class="block5-content" style="padding:0px">
				
				<table border="0" width="100%" cellpadding="12" cellspacing="0">
				<tr class="alt2">
					<td>
						
					
					
					<form name="searchkb" action="<?php echo HTTP_KB; ?>" method="get" accept-charset="UTF-8" style="margin: 0px;">
					<table border="0" width="100%" cellpadding="0" cellspacing="6">
					<tr> 
						<td width="14%" ><?php echo $phrase['_keyword']; ?></td>
						<td width="86%"><input value="<?php if (isset($ilance->GPC['keyword'])) { echo $ilance->GPC['keyword']; } ?>" class="input" name="keyword" type="text" id="keyword"> <label for="match"><input name="match" type="checkbox" id="match" value="1" />
						<?php echo $phrase['_match_whole_word_only']; ?></label></td>
					</tr>
					<tr> 
						<td height="19"><?php echo $phrase['_category']; ?></td>
						<td height="19">
							<select name="scatid" id="scatid" style="font-family: verdana">
							<option value="-1"><?php echo $phrase['_all_categories']; ?></option>
							<option value="0"><?php echo $phrase['_main_category']; ?></option>
							<?php $appendstr = '';  getSCategories(0, 0); echo $appendstr; ?>
							</select>
						</td>
					</tr>
					<tr> 
						<td colspan="2" style="padding-top:6px"><input type="submit" value=" <?php echo $phrase['_search']; ?> " style="font-size:15px" class="buttons" /></td>
					</tr>
					</table>
					<input type="hidden" name="cmd" value="6" />
					</form>	
						
					</td>				
				</tr>
				<?php echo $ilance->lancekb->fetch_rows($sql, $keyword); ?>
				<tr>
                                    <td><span class="gray">You are currently viewing articles related to your search terms</span></td>
                                </tr>
				</table>
				
			</div>
			
			<div class="block5-footer">
					<div class="block5-right">
							<div class="block5-left"></div>
					</div>
			</div>
			
	</div>
</div>
<?php
}
?>