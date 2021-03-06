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

$canview = true;
$catid = 0;
$sql = $ilance->db->query("
        SELECT adminaccess, catid
        FROM " . DB_PREFIX . "kbposts
        WHERE postsid = '" . intval($ilance->GPC['id']) . "'
");
if ($ilance->db->num_rows($sql) > 0)
{
        while ($res = $ilance->db->fetch_array($sql)) 
        {
                if ($res['adminaccess'] == 'Y')
                {
                        if (empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $canview = false;
                        }  
                }
                
                $catid = $res['catid'];
        }   
}

$ilance->GPC['catid'] = $catid;

if ($canview == false)
{
        $area_title = $phrase['_access_denied'];
        $page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
        
        echo $phrase['_sorry_member_only_article_register_signin'];
        exit();
}

// #### update view counter ####################################################
$ilance->db->query("
        UPDATE " . DB_PREFIX . "kbposts
        SET numviews = numviews + 1
        WHERE postsid = '" . intval($ilance->GPC['id']) . "'
        LIMIT 1
");
?>

<div class="bigtabs" style="padding-bottom:10px; padding-top:0px">
<div class="bigtabsheader">
<ul>
        <li title="" class="on"><a href="javascript:void(0)"><?php echo $phrase['_article']; ?></a></li>
        <li title="" class=""><a href="<?php if ($ilance->lancekb->config['enableseo']){?><?php echo HTTP_KB; ?>print-article-t<?php echo intval($ilance->GPC['id']); ?>.html<?php }else {?><?php echo HTTP_KB; ?>printarticle<?php echo $ilconfig['globalsecurity_extensionmime']; ?>?id=<?php echo intval($ilance->GPC['id']); ?><?php }?>"><?php echo $phrase['_print_article']; ?></a></li>
        <li title="" class=""><a href="<?php if ($ilance->lancekb->config['enableseo']){?><?php echo HTTP_KB; ?>save-article-t<?php echo intval($ilance->GPC['id']); ?>-11.html<?php }else {?><?php echo HTTP_KB; ?>?cmd=11&amp;id=<?php print intval($ilance->GPC['id']); ?><?php }?>"><?php echo $phrase['_save_article']; ?></a></li>
        <li title="" class=""><a href="<?php if ($ilance->lancekb->config['enableseo']){?><?php echo HTTP_KB; ?>email-article-t<?php echo intval($ilance->GPC['id']); ?>-12.html<?php }else {?><?php echo HTTP_KB; ?>?cmd=12&amp;id=<?php echo intval($ilance->GPC['id']); ?><?php }?>"><?php echo $phrase['_email_article']; ?></a></li>
</ul>
</div>
</div>
<div style="clear:both;"></div>

<div><?php echo $ilance->lancekb->fetch_article(intval($ilance->GPC['id']), $se = ''); ?></div>
<div style="clear:both; padding-top:12px"></div>
<!-- karthik on may23 for hiding stars on comment -->
<?php /*?><table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td align="left" colspan="2"><?php echo '<div style="float:right">' . $ilance->lancekb->fetch_ratings(intval($ilance->GPC['id'])) . '<div style="padding-top:3px" class="smaller gray">' . $phrase['_article_rating'] . '</div></div><div><img id="star1" class="star" onMouseOut="starover(0)" onMouseOver="starover(1)" onMouseDown="stardown(1)" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif" border="0" /><img id="star2" class="star" onMouseOut="starover(0)" onMouseOver="starover(2)" onMouseDown="stardown(2)" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif" border="0" /><img id="star3" class="star" onMouseOut="starover(0)" onMouseOver="starover(3)" onMouseDown="stardown(3)" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif" border="0" /><img id="star4" class="star" onMouseOut="starover(0)" onMouseOver="starover(4)" onMouseDown="stardown(4)" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif" border="0" /><img id="star5" class="star" onMouseOut="starover(0)" onMouseOver="starover(5)" onMouseDown="stardown(5)" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif" border="0" /></div>'; ?></td>
</tr>
</table><?php */?>

<?php
//if ($ilance->lancekb->config['enablecomments'] AND $ilance->lancekb->is_user_logged_in())
if ($ilance->lancekb->config['enablecomments'])
{
?>
<div style="font-size:15px; padding-bottom:4px; padding-top:9px"><strong><?php echo $phrase['_comments']; ?></strong></div>
<table width="100%" border="0" cellpadding="12" cellspacing="0">
<?php
echo $ilance->lancekb->fetch_article_comments("
        SELECT *
        FROM " . DB_PREFIX . "kbcomments 
        WHERE approved = '1'
            AND postsid = '" . intval($ilance->GPC['id']) . "'
        ORDER BY insdate", intval($ilance->GPC['id']));
?>
</table>
<div style="padding-bottom:12px"></div>
<?php
}
?>

<!-- karthik on may26 for hiding  comment -->

<?php /*?><div style="font-size:15px; padding-bottom:4px; padding-top:6px"><strong><?php echo $phrase['_discuss_article']; ?></strong></div><?php */?>

<?php
if ($ilance->lancekb->config['moderation'])
{
?>

<!-- karthik on may26 for hiding  comment -->

<?php /*?><div class="smaller gray" style="padding-bottom:12px"><?php echo $phrase['_your_comments_in_regards_to_this_article_have_been_saved_and_will_be_posted_after_moderation_approval']; ?></div><?php */?>
<?php
}
?>
<?php
if ($ilance->lancekb->is_user_logged_in())
{
?>

<?php
}
else
{
?>
<!-- karthik on may26 for hiding  comment -->

<?php /*?><div class="gray"><?php echo $phrase['_please_signin_to_leave_a_comment']; ?></div><?php */?>
<?php
}

$kbuserid = !empty($_SESSION['ilancedata']['user']['userid']) ? intval($_SESSION['ilancedata']['user']['userid']) : 0;
$sql = $ilance->db->query("
        SELECT rating
        FROM " . DB_PREFIX . "kbratings
        WHERE user_id = '" . intval($kbuserid) . "'
            AND postsid = '" . intval($ilance->GPC['id']) . "'
        LIMIT 1
");
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
?>
<script type="text/javascript">starover(<?php echo $res['rating']; ?>); enablerating=0;</script>
<?php
}
?>

<?php
// fetch related articles
$related = $ilance->lancekb->fetch_related_articles(intval($ilance->GPC['id']));
if (!empty($related))
{
?>
<div class="bigtabs" style="padding-bottom:5px; padding-top:12px">
<div class="bigtabsheader">
<ul>
        <li title="" class="on"><a href="javascript:void(0)"><?php echo $phrase['_related_articles']; ?></a></li>
</ul>
</div>
</div>
<div style="clear:both;"></div>

<table width="100%" border="0" cellpadding="3" cellspacing="0">
<?php echo $related; ?> 
</table>
<br />
<?php
}
?>