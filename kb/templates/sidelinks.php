<table width="95%" border="0" cellspacing="0" cellpadding="0">
<tr> 
	<td align="left">
            
        </td>
</tr>
<tr> 
	<td>
		<?php
		if ($ilance->lancekb->config['enableseo']) 
		{ 
		?>
                    <div class="bigtabs" style="padding-bottom:10px; padding-top:0px">
                    <div class="bigtabsheader">
                    <ul>
                            <li title="" class="<?php if (isset($cmd) AND $cmd == '1') { echo "on"; } ?>"><a href="<?php echo HTTP_KB; ?>"><?php echo $phrase['_main']; ?></a></li>
                            <li title="" class="<?php if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '14') { echo "on"; } ?>"><a href="<?php echo HTTP_KB; ?>saved-articles-14.html"><?php echo $phrase['_saved_articles']; ?></a></li>
							
                           <?php /*?> <li title="" class="<?php if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '5') { echo "on"; } ?>"><a href="<?php echo HTTP_KB; ?>ask-a-question-5.html"><?php echo $phrase['_ask_a_question']; ?></a></li><?php */?>
                    </ul>
                    </div>
                    </div>
                    <div style="clear:both;"></div>
		<?php 
		} 
		else 
		{
		?>
                    <div class="bigtabs" style="padding-bottom:10px; padding-top:0px">
                    <div class="bigtabsheader">
                    <ul>
                            <li title="" class="<?php if (isset($cmd) AND $cmd == '1') { echo "on"; } ?>"><a href="<?php echo HTTP_KB; ?>"><?php echo $phrase['_main']; ?></a></li>
                            <li title="" class="<?php if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '14') { echo "on"; } ?>"><a href="<?php echo HTTP_KB; ?>?cmd=14"><?php echo $phrase['_saved_articles']; ?></a></li>
                            <li title="" class="<?php if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '5') { echo "on"; } ?>"><a href="<?php echo HTTP_KB; ?>?cmd=5"><?php echo $phrase['_ask_a_question']; ?></a></li>
                    </ul>
                    </div>
                    </div>
                    <div style="clear:both;"></div>
		<?php 
		}
		?>
	</td>
</tr>
</table>

<form method="get" action="<?php echo HTTP_KB; ?>" name="search" accept-charset="UTF-8" style="margin: 0px; padding-top:5px; padding-bottom:5px;">
<input type="hidden" name="cmd" value="6" />
<table width="1%" border="0" cellspacing="0" cellpadding="3">
<tr> 
        <td width="1%"><strong><?php echo $phrase['_keywords']; ?></strong>&nbsp;</td>
        <td width="1%" nowrap="nowrap" class="smaller"><input type="text" id="keyword" name="keyword" value="" maxlength="50" class="input" /></td>
        <td width="1%" nowrap="nowrap" width="6%">&nbsp; <input type="submit" class="buttons" value="<?php echo $phrase['_search']; ?>" /></td>
</tr>
</table>
</form>

<div style="padding-bottom:2px"></div>

<table width="90%" align="center" cellpadding="1" cellspacing="0">
<?php

$parentid = 0;
if (isset($ilance->GPC['catid']) AND $ilance->GPC['catid'] > 0)
{
        $parentid = $ilance->lancekb->parentid($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['catid']);
}

$result = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "kbcategory
        WHERE parent = '" . intval($parentid) . "'
        ORDER BY sort ASC
");
if ($ilance->db->num_rows($result) > 0)
{
        $perm = '';
	$rows = $ilance->db->num_rows($result);
	$count = 0;
	while ($obj = $ilance->db->fetch_object($result)) 
	{
		if ($obj->adminaccess == 'N')
		{
			$perm = '';
		}
		else 
		{
                        if (empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $perm = '<img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/limited.gif" alt="' . $phrase['_subscribers_only'] . '" />';
                        }
		}
		
		$numquestions = $ilance->lancekb->print_article_category_count($obj->categoryid);
                
                if (isset($ilance->GPC['catid']) AND $ilance->GPC['catid'] == $obj->categoryid)
                {
			$class = 'alt3';
                        $cat = '<strong>' . stripslashes($obj->catname) . '</strong>&nbsp;<span class="smaller gray">(' . $numquestions . ')</span>';    
                }
                else
                {
			if ($rows === $count)
			{
				$class = 'alt1';
			}
			else
			{
				$class = 'alt1';
			}
				
                        $cat = ($ilance->lancekb->config['enableseo'])
				? '<span class="blue"><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($obj->catname)) . '-' . $obj->categoryid . '-2.html">' . stripslashes($obj->catname) . '</a></span>&nbsp;<span class="smaller gray">(' . $numquestions . ')</span>'
				: '<span class="blue"><a href="' . HTTP_KB . '?cmd=2&amp;catid=' . $obj->categoryid . '">' . stripslashes($obj->catname) . '</a></span>&nbsp;<span class="smaller gray">(' . $numquestions . ')</span>';
                }
		$count++;
		
                
		$catdesc = stripslashes($obj->description);
?>
<tr class="<?php echo $class; ?>"> 
	<td align="left" valign="top"><div style="padding-left:9px; padding-top:9px"><?php echo '<span style="font-size:14px">' . $cat . '</span>&nbsp;' . $perm; ?></div><div class="smaller" style="padding-left:9px; padding-top:6px; padding-bottom:9px"><?php echo stripslashes(handle_input_keywords($catdesc)); ?></div></td>
</tr>
<?php
		
	}
}
?>
</table>