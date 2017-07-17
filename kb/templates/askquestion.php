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
<li title="" class=""><a href="' . HTTP_KB . 'top-10-emailed.html">' . $phrase['_top_10_articles_emailed'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . 'top-10-printed.html">' . $phrase['_top_10_articles_printed'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . 'top-10-saved.html">' . $phrase['_top_10_articles_saved'] . '</a></li>';   
}
else
{
    $kboptions = '
<li title="" class=""><a href="' . HTTP_KB . '">' . $phrase['_recent_articles'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=1">' . $phrase['_top_10_articles_viewed'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=2">' . $phrase['_top_10_articles_emailed'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=4">' . $phrase['_top_10_articles_printed'] . '</a></li>
<li title="" class=""><a href="' . HTTP_KB . '?cmd=3&amp;pop=3">' . $phrase['_top_10_articles_saved'] . '</a></li>';
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

<div style="font-size:18px; padding-bottom:12px"><?php echo $phrase['_ask_a_question']; ?></div>

<form action="<?php echo HTTP_KB; ?>" method="post" name="ilform" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="5" />
<input type="hidden" name="id" value="<?php (isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0) ?>" />
<input name="name" value="<?php echo $_SESSION['ilancedata']['user']['username']; ?>" type="hidden" />
<input name="email" value="<?php echo $_SESSION['ilancedata']['user']['email']; ?>" type="hidden" />
<table border="0" width="100%" cellpadding="9" cellspacing="1">
<tr class="alt1"> 
    <td height="29"><span class="gray"><?php echo $phrase['_category']; ?></span></td>
    <td height="19"><select name="catid" id="catid" class="pulldown" style="font-family: verdana"><?php $appendstr = ''; $ilance->lancekb->print_category_pulldown(0, '', 0); echo $appendstr; ?></select></td>
</tr>
<tr class="alt1">
    <td valign="top"><span class="gray"><?php echo $phrase['_question']; ?></span></td>
    <td height="19"><textarea  name="comments" cols="40" rows="10" style="width: 400px; height: 84px; font-family: Verdana" wrap="physical" id="comments"></textarea></td>
</tr>
<tr> 
    <td height="30">&nbsp;</td>
    <td height="30" align="left"><input type="submit" value="<?php echo $phrase['_continue']; ?>" style="font-size:15px" class="buttons" /></td>
</tr>
</table>
</form>