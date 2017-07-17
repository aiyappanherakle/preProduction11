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

if ($ilance->lancekb->config['enableseo'])
{
    $kboptions = '
<li title="" class="on"><a href="javascript:void(0)">' . $phrase['_recent_articles'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . 'top-10-viewed.html">' . $phrase['_top_10_articles_viewed'] . '</a></li>
';   
}
else
{
    $kboptions = '
<li title="" class="on"><a href="javascript:void(0)">' . $phrase['_recent_articles'] . '</a></li>
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
			
			<div class="block5-header"><?php echo $phrase['_recent_articles']; ?></div>
			<!--<div class="block5-content-gray" style="padding:9px"><div class="smaller">View recent articles posted within the community</div></div>-->
			<div class="block5-content" style="padding:0px">
				
                                <table border="0" cellpadding="9" cellspacing="1" width="100%">
                                <?php
                                echo $ilance->lancekb->fetch_rows("
                                        SELECT *
                                        FROM " . DB_PREFIX . "kbposts
                                        WHERE approved = '1'
					    AND answer != ''
                                        ORDER BY postsid DESC
                                        LIMIT " . $ilance->lancekb->config['articleslimit'] . "
                                ");
                                ?>
                                <tr>
                                    <td><span class="gray">You are currently viewing recently posted articles</span></td>
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