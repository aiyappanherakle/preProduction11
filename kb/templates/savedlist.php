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

<div style="font-size:18px; padding-bottom:12px"><?php echo $phrase['_saved_articles']; ?></div>

<div><?php $_COOKIE[COOKIE_PREFIX . 'savedarticles'] = !empty($_COOKIE[COOKIE_PREFIX . 'savedarticles']) ? $_COOKIE[COOKIE_PREFIX . 'savedarticles'] : ''; echo $ilance->lancekb->fetch_saved_list($_COOKIE[COOKIE_PREFIX . 'savedarticles']);?></div>
