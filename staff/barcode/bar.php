<?php
require_once('../../functions/config.php');

//tags
$invoicetag='inv';
$itemtag='item';
$resettag='RESET';
$removenexttag='REMOVENEXT';
$removelasttag='REMOVELAST';
$columns=3;
$i=0;
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='invoice')
	{
		$sql="SELECT project_id,qty  FROM " . DB_PREFIX . "invoice_projects WHERE final_invoice_id = '".$ilance->GPC['id']."'";
		
		$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res)>0)
		{
		$html='<h4>Invoice - '.$ilance->GPC['id'].' <input type="hidden" name ="invoiceid" value="'.$ilance->GPC['id'].'"></h4>';
		$js_bit="<script> var items = [\n";
		$html.='<table border=1 width="100%"><tr>';
		$i=0;
			while($line=$ilance->db->fetch_array($res))
			{
			
			if($i%$columns==0)
			$html.='</tr><tr>';
			
				$html.='<td width="'.(100/$columns).'%"><div id="div_'.$i.'">'.$line['project_id'].'</div></td>';
				$js_bit.="[".$line['project_id'].",".(($line['qty']>0)?$line['qty']:1).",".(($line['qty']>0)?$line['qty']:1)."],\n";
				$i++;
			}
			$html.='</tr></table>';
			$js_bit.="];</script>";
			echo $js_bit;
			echo $html;
		}
		exit;
	}
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='log')
	{
	
	if(stristr($ilance->GPC['id'],$invoicetag))
	{
	$id=str_ireplace($invoicetag,'',$ilance->GPC['id']);
	$tag=$invoicetag;
	}else if(stristr($ilance->GPC['id'],$itemtag))
	{
	$id=str_ireplace($itemtag,'',$ilance->GPC['id']);
	$tag=$itemtag;
	}else
	{
	$id=0;
	$tag=$ilance->GPC['id'];
	}
	
		echo $sql="insert into " . DB_PREFIX . "scan_log (tag,read_id) value ('".$tag."','".$id."')";
		$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		exit;
	}
}else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*
sample invoice = inv415028
<br>
sample item= item114216
<br>

*/
?>

<script src="http://www.greatcollections.com/functions/javascript/functions_jquery.js"></script>
<script src="jquery.cookie.js"></script>
<script src="all_js.js"></script>
<script>


function fill_pending()
{
html="<table>";
if(typeof items !== "undefined" && items)
{
for(var i=0;i<items.length;i++)
{
	 html+="<tr><td>"+items[i][0]+"</td><td>"+items[i][1]+"</td></tr>";
}
html+="</table>";

	$('#div_error').html(html);
}
}


$(document).ready(function(){
  $("#reader").keyup(function(event){
   delay(function(){
      readerval=$("#reader").val();
	  
	  log(readerval);
	  if(readerval.indexOf('<?php echo $invoicetag;?>')==0 || readerval.indexOf('<?php echo strtoupper($invoicetag);?>')==0)
	  {
	  var invoiceid=readerval.substr(3);
	  $.cookie("invoiceid", invoiceid);
	  $("#div1").load("bar.php?cmd=invoice&id="+invoiceid);
	  readerval='';
	  
	  }else if(readerval.indexOf('<?php echo $itemtag;?>')==0 || readerval.indexOf('<?php echo strtoupper($itemtag);?>')==0)
	  {
	  //trigger item check
	  itemid=readerval.substr(4);
		  if($.cookie("removenext")==1)
		  {
			idandqty=addindexval(itemid);
			update_cellcolor();
			$.removeCookie("removenext");
			$('#reader').attr('value', ''); 
			fill_pending();
			return true;
		  }
	  idandqty=getindexval(itemid);
	  itemindexid=idandqty[0];
	  itemqty=idandqty[1];
	  itemoriginalqty=idandqty[1];
	  update_cellcolor();
	  
	  }else if(readerval=='<?php echo $resettag;?>' || readerval=='<?php echo strtoupper($resettag);?>')
	  {
		  $.removeCookie("invoiceid");
		  $.removeCookie("itemid");
		  $.removeCookie("removenext");
		  location.reload();
	  }else if(readerval=='<?php echo $removenexttag;?>' || readerval=='<?php echo strtoupper($removenexttag);?>')
	  {
		$.cookie("removenext",1);
	  }else if(readerval=='<?php echo $removelasttag;?>' || readerval=='<?php echo strtoupper($removelasttag);?>')
	  {
		  idandqty=addindexval($.cookie("itemid"));
		  update_cellcolor();
	  }
	  $('#reader').attr('value', ''); 
	  fill_pending();
	  
    }, 50 );
	
  });
});


function addindexval(itemid)
{
for(var i=0;i<items.length;i++)
{
	if(items[i][0]==itemid && items[i][1]<items[i][2] )
	{
	items[i][1]=items[i][1]+1;
	return [i,items[i][1]];
	}
}

}

function getindexval(itemid)
{

	for(var i=0;i<items.length;i++)
	{
		if(items[i][0]==itemid)
		{
		$.cookie("itemid", itemid);
		items[i][1]=items[i][1]-1;
		return [i,items[i][1]+1,items[i][2]];
		}
	}

	for(var i=0;i<items.length;i++)
	{
		if(items[i][0]==itemid)
		{
		return [i,items[i][1]];
		}
	}

}


function update_cellcolor()
{
for(var i=0;i<items.length;i++)
{
	if(items[i][1]==items[i][2])
	{
	$("#div_"+i).css("background-color","rgb(255, 255, 255)"); 
	}
	if(items[i][1]!=items[i][2])
	{
	$("#div_"+i).css("background-color","rgb(255, 255, 0)"); 
	}
	if(items[i][1]<0)
	{
	$("#div_"+i).css("background-color","rgb(255, 0, 0)"); 
	}
}
}
</script>
</head>
<body onload="">
<div id="div1"></div>
<input id="reader" name="reader" type="test" autofocus ><div id="div_error" >ON</div>

</body>
<script>
$('#div_error').text($.cookie("<?php echo $removenexttag;?>"));
</script>
</html>