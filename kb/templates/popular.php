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

if (!empty($ilance->GPC['pop']))
{
	switch ($ilance->GPC['pop'])
	{
		case 1:
		{
			$sql = "SELECT * FROM " . DB_PREFIX . "kbposts WHERE approved = '1' ORDER BY numviews DESC, moddate LIMIT 10";
			if ($ilance->lancekb->config['enableseo'])
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class="on"><a href="javascript:void(0)">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';   
			}
			else
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class="on"><a href="javascript:void(0)">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';
			}
			$headertitle = $phrase['_top_10_articles_viewed'];
			break;
		}
		case 2:
		{
			$sql = "SELECT * FROM " . DB_PREFIX . "kbposts WHERE approved = '1' ORDER BY numemails DESC, moddate LIMIT 10";
			if ($ilance->lancekb->config['enableseo'])
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class=""><a href="' . HTTP_KB . 'top-10-viewed.html">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';   
			}
			else
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=1">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';
			}
			$headertitle = $phrase['_top_10_articles_emailed'];
			break;
		}
		case 4:
		{
			$sql = "SELECT * FROM " . DB_PREFIX . "kbposts WHERE approved = '1' ORDER BY numprints DESC, moddate LIMIT 10";
			if ($ilance->lancekb->config['enableseo'])
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class=""><a href="' . HTTP_KB . 'top-10-viewed.html">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';   
			}
			else
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=1">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';
			}
			$headertitle = $phrase['_top_10_articles_printed'];
			break;
		}
		case 3:
		{
			$sql = "SELECT * FROM " . DB_PREFIX . "kbposts where approved = '1' ORDER BY numsaves DESC, moddate LIMIT 10";
			if ($ilance->lancekb->config['enableseo'])
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class=""><a href="' . HTTP_KB . 'top-10-viewed.html">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';   
			}
			else
			{
				$popularoptions = '
				<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
				<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=1">' . $phrase['_top_10_articles_viewed'] . '</a></li>
				';
			}
			$headertitle = $phrase['_top_10_articles_saved'];
			break;
		}
	}
}
?>
<div class="bigtabs" style="padding-bottom:15px; padding-top:0px">
<div class="bigtabsheader">
<ul>
	<?php echo $popularoptions; ?>
</ul>
</div>
</div>
<div style="clear:both;"></div>

<div class="block-wrapper">
<div class="block5">

                <div class="block5-top">
                                <div class="block5-right">
                                                <div class="block5-left"></div>
                                </div>
                </div>
                
                <div class="block5-header"><?php echo $headertitle; ?></div>
                <!--<div class="block5-content-gray" style="padding:9px"><div class="smaller"><strong><?php echo $headertitle; ?></strong></div></div>-->
                <div class="block5-content" style="padding:0px">
                        
                        <table border="0" width="100%" height="100%" cellpadding="9" cellspacing="1">
			<?php echo $ilance->lancekb->fetch_rows($sql); ?>
			<tr>
				<td><span class="gray"><?php echo $phrase['_you_are_currently_viewing_top_10_stats']; ?></span></td>
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