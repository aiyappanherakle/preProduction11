<script language="JavaScript">
<!--
function trim(text)
{
	var str=text;
	var ln=str.length;
	if (ln==0){return str;}
	while(str.charAt(0)==" ")
	{
		str=str.substr(1);
	}
	ln=str.length;
	while(str.charAt(ln-1)==" ")
	{
		str=str.substring(0,ln-1);
		ln=str.length;
	}
	return str;
}
function chkspaces(chkStr,fieldname)
{
	if (chkStr.indexOf(" ")>=0)
	{
		alert("The "+fieldname+" has spaces in it.");
		return false;
	}
	else
	{
		return true;
	}
}
function emailCheck() {
var emailStr = document.getElementById("femail").value
var fname = document.getElementById("fname").value
var emailPat=/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/
var specialChars="\\(\\)<>@,;:\\\\\\\"\\.\\[\\]"
var validChars="\[^\\s" + specialChars + "\]"
var quotedUser="(\"[^\"]*\")"
var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/
var atom=validChars + '+'
var word="(" + atom + "|" + quotedUser + ")"
var userPat=new RegExp("^" + word + "(\\." + word + ")*$")
var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$")
var matchArray=emailStr.match(emailPat)
if(fname == '' )
{
   alert("Enter your Friend Name")
}
if (matchArray == null) {
	alert("Email address seems incorrect (check @ and .'s)")
	return false
}
var user=matchArray[1]
var domain=matchArray[2]
if (user.match(userPat)==null) {
    alert("The username on email doesn't seem to be valid.")
    return false
}
var IPArray=domain.match(ipDomainPat)
if (IPArray!=null) {
	  for (var i=1;i<=4;i++) {
	    if (IPArray[i]>255) {
	        alert("Email IP address is invalid!")
		return false
	    }
    }
    return true
}
var domainArray=domain.match(domainPat)
if (domainArray==null) {
	alert("Email domain name doesn't seem to be valid.")
    return false
}
var atomPat=new RegExp(atom,"g")
var domArr=domain.match(atomPat)
var len=domArr.length
if (domArr[domArr.length-1].length<2 || 
    domArr[domArr.length-1].length>4) {
   alert("Email address must end in a 3-4 letter domain, or two letter country.")
   return false
}
if (len<2) {
   var errStr="Email address is missing a hostname!"
   alert(errStr)
   return false
}
return true;
}
/*function checkifvalid()
{
	if (window.document.myform.name.value=="")
	{
		alert("Kindly enter your name");
		window.document.myform.name.focus();
		return false;
	}
	if (!emailCheck(document.myform.email.value))
	{
		window.document.myform.email.focus();
		return false;
	}
	if (window.document.myform.fname.value=="")
	{
		alert("Kindly enter your friends name");
		window.document.myform.fname.focus();
		return false;
	}
	if(!emailCheck(document.myform.femail.value))
	{
		window.document.myform.femail.focus();
		return false;
	}
	return true;
}*/
//-->
</script>
<div class="bigtabs" style="padding-bottom:10px; padding-top:0px">
<div class="bigtabsheader">
<ul>
        <li title="" class=""><a href="<?php echo HTTP_KB . '?cmd=4&amp;id=' . intval($ilance->GPC['id']) ?>"><?php echo $phrase['_article']; ?></a></li>
        <li title="" class=""><a href="<?php if ($ilance->lancekb->config['enableseo']){?><?php echo HTTP_KB; ?>print-article-t<?php echo intval($ilance->GPC['id']); ?>.html<?php }else {?><?php echo HTTP_KB; ?>printarticle<?php echo $ilconfig['globalsecurity_extensionmime']; ?>?id=<?php echo intval($ilance->GPC['id']); ?><?php }?>"><?php echo $phrase['_print_article']; ?></a></li>
        <li title="" class=""><a href="<?php if ($ilance->lancekb->config['enableseo']){?><?php echo HTTP_KB; ?>save-article-t<?php echo intval($ilance->GPC['id']); ?>-11.html<?php }else {?><?php echo HTTP_KB; ?>?cmd=11&amp;id=<?php print intval($ilance->GPC['id']); ?><?php }?>"><?php echo $phrase['_save_article']; ?></a></li>
        <li title="" class="on"><a href="javascript:void(0)"><?php echo $phrase['_email_article']; ?></a></li>
</ul>
</div>
</div>
<div style="clear:both;"></div>
<form name="myform" action="<?php echo HTTP_KB; ?>" method="post" accept-charset="UTF-8" onsubmit="javascript: return emailCheck();" style="margin: 0px;">
<input type="hidden" name="cmd" value="12" />
<input type="hidden" name="id" value="<?php echo intval($id); ?>" />
<input type="hidden" name="email" value="<?php echo !empty($_SESSION['ilancedata']['user']['email']) ? $_SESSION['ilancedata']['user']['email'] : ''; ?>" id="email" />
<div style="font-size:18px"><?php echo $phrase['_email_article']." &quot;".$ilance->lancekb->fetch_article_title($id)."&quot;"; ?></div>
<br />
<table border="0" width="100%" cellpadding="9" cellspacing="1">
<tr class="alt1"> 
<td width="20%" height="19" nowrap="nowrap"><span class="gray"><?php echo $phrase['_your_name']; ?></span></td>
<td width="70%" nowrap="nowrap"><input value="<?php echo !empty($_SESSION['ilancedata']['user']['firstname']) ? $_SESSION['ilancedata']['user']['firstname'] : ''; ?>" name="name" type="text" id="name" class="input" style="width:350px" /></td>
</tr>
<tr class="alt1"> 
<td height="19" nowrap="nowrap"><span class="gray"><?php echo $phrase['_friend_name']; ?></span></td>
<td nowrap="nowrap"><input  name="fname" type="text" id="fname" class="input" style="width:350px"></td>
</tr>
<tr class="alt1"> 
<td height="19" nowrap="nowrap"><span class="gray"><?php echo $phrase['_friend_email']; ?></span></td>
<td nowrap="nowrap"><input name="femail" type="text" id="femail" class="input" style="width:350px"> </td>
</tr>
<tr class="alt1"> 
<td height="19" valign="middle" nowrap="nowrap"><span class="gray"><?php echo $phrase['_comments']; ?></span></td>
<td><textarea name="comments" cols="35" rows="5" id="comments" style="width: 350px; height: 84px;" wrap="physical" class="textarea"></textarea></td>
</tr>
<?php /*?><?php 
if ($ilconfig['registrationdisplay_turingimage'])
{
?><?php */?>
<tr>
<td height="19" valign="middle" nowrap="nowrap">&nbsp;</td>
<td height="19">
<?php
      // captcha logic (does server support image creation)?
      if (extension_loaded('gd'))
      {
	      echo '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'].'?do=captcha" alt="" border="0" />';
      }
      else
      {
	      // captcha set? reset it!
	      $_SESSION['ilancedata']['user']['captcha'] = '';
	      // no l, i or o / O / 0
	      $src = 'abcdefghjkmnpqrstuvwxyz23456789';
	      if (mt_rand(0,1) == 0)
	      {
		      $src = mb_strtoupper($src);
	      }
	      $srclen = mb_strlen($src)-1;
	      // how long is the turing string?
	      $length = 5;
	      for ($i=0; $i<$length; $i++)
	      {
		      $char = mb_substr($src, mt_rand(0, $srclen), 1);
		      $_SESSION['ilancedata']['user']['captcha'] .= $char;
	      }
	      
	      echo '<div class="yellowhlite" style="width:95px; float:left"><div class="header" style="color:#ff6600; text-align: center;">'.$_SESSION['ilancedata']['user']['captcha'].'</div></div>';
      }
?>
</td>
</tr>
<tr class="alt1">
<td height="19" valign="middle" nowrap="nowrap"><span class="gray"><?php echo $phrase['_security_verification']; ?></span></td>
<td height="19"><input name="captcha" type="text" id="captcha" class="input" /></td>
</tr>
<?php /*?><?php
}
?><?php */?>
<tr align="center" > 
<td height="30">&nbsp;</td>
<td height="30" align="left"><input type="submit" name="submit" value="<?php echo $phrase['_continue']; ?>" style="font-size:15px" class="buttons"></td>
</tr>
</table>
</form>