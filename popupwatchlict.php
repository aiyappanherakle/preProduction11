<html>

<script type="text/javascript">

function showUser()
{

var com=document.getElementById("com").value;

var id=document.getElementById("id").value;


if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest();
  }
else
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
	window.refresh();
	self.close();
	
	} 
  }

   
xmlhttp.open("GET","comment.php?q="+com +"&f="+id,true);
xmlhttp.send();
}



</script>
<head>
<style type="text/css">
div.box
{
width:460px;
padding:10px;
border:1px solid gray;
margin:0px;
}
</style>
</head>

<body>

<div  class="box">
<textarea name="comment" cols="50" rows="8" id="com"></textarea>
  <br />
<input type="submit" name="save" value="Save" onClick="showUser();" />
<input type="hidden" name="id" id="id" value="<?php echo $_GET['id']; ?>">
</div>


<p> <span id="txtHint"></span></p> 
</body>
</html>