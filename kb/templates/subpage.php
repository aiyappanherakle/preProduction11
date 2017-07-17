<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base1.0.8 Build 85
|| # -------------------------------------------------------------------- # ||
|| # Customer License # KapIxNXTSUYf3LjCGHiWk1XevwZ-ISZStLboZ-ErQdU-pATvJ3
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

$catid = 0;
$subcategoryname = '';
if (isset($ilance->GPC['catid']))
{
	$catid = intval($ilance->GPC['catid']);
	$subcategoryname = $ilance->lancekb->fetch_kbcategory_name($catid);
}

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

<?php
$rowcontent = $ilance->lancekb->fetch_kbsubcategories($catid);
if (!empty($rowcontent))
{
?>
<div class="block-wrapper">
<div class="block5">

                <div class="block5-top">
                                <div class="block5-right">
                                                <div class="block5-left"></div>
                                </div>
                </div>
                
                <div class="block5-header">More from within this category</div>
                <div class="block5-content-gray" style="padding:9px"><div class="smaller"><?php echo $phrase['_categories']; ?> <?php echo $phrase['_in']; ?> <?php echo $subcategoryname; ?></div></div>
                <div class="block5-content" style="padding:0px">
                        
                        <table border="0" width="100%" height="100%" cellpadding="9" cellspacing="1">
                        <?php echo $rowcontent; ?>
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

<?php
if (isset($catid) AND $catid > 0)
{
?>
<div class="block-wrapper">
	<div class="block5">
	
			<div class="block5-top">
					<div class="block5-right">
							<div class="block5-left"></div>
					</div>
			</div>
			
			<div class="block5-header"><?php echo $subcategoryname; ?> <?php echo $phrase['_articles']; ?></div>
			<div class="block5-content" style="padding:0px">
				
                                <table border="0" width="100%" cellpadding="9" cellspacing="1">
								
                                <?php 
								// murugan changes on feb 08 to add DESC
                                echo $ilance->lancekb->fetch_rows("
                                        SELECT *
                                        FROM " . DB_PREFIX . "kbposts 
                                        WHERE approved = '1' 
                                                AND catid = '" . intval($catid) . "' 
                                        ORDER BY moddate DESC
                                "); 
                                ?>
                                <tr>
                                        <td><div class="gray">Viewing articles posted within <?php echo $subcategoryname; ?></div></td>
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