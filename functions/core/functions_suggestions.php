<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Core Tab functions for ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       herakle
*/

/*
* ...
*
* @param       
*
* @return      
*/

//fetch_price_population($cid,$row_info['Grade'],0,0);
function fetch_price_population_html($pid)
{
global $ilance;
$sql=$ilance->db->query("select * from  ".DB_PREFIX."pps_population where project_id='".$pid."' ");
if($ilance->db->num_rows($sql)==0)
{
fetch_price_population_save($pid);
}

$sql=$ilance->db->query("select * from  ".DB_PREFIX."pps_population where project_id='".$pid."' ");
$row=$ilance->db->fetch_array($sql);
if($row['coin_type']=='pcgs')
{
	   $price_html.='<table  border="0" cellpadding="5"><tr><td></td></tr>';
	   $price_html.= '<tr><td></td>';
	   if($row['grade1']!='')
  {
  $price_html.= '<td>'.$row['grade1'].'</td>';
	if($row['grade2']!=''||$row['grade3']!='')
	{
		$price_html.='<td>|</td>';
		}
  }
  if($row['grade2']!='')
  {
  $price_html.='<td>'.$row['grade2'].'</td>';
    if($row['grade3']!='')
	{
		$price_html.='
    <td>|</td>';
		}
  }
  
     if($row['grade3']!='')
  {
  $price_html.='<td>'.$row['grade3'].'</td></tr>';
  }
	
	  $price_html.= '<tr><td width="200">PCGS Price Guide</td>';
	  if($row['pcgs_price1']!='')
	  {
    	  $price_html.= '<td>'.$ilance->currency->format($row['pcgs_price1'], $currencyid).'</td>';
		  if($row['pcgs_price2']!=''||$row['pcgs_price3']!='')
		  {
		 $price_html.='
   <td>&nbsp;</td>';
		  }
		 else
		 {
		  $price_html.='</tr></td></tr>';
		 }
	  }
	  
	  if($row['pcgs_price2']!='')
	  {
	  $price_html.='<td>'.$ilance->currency->format($row['pcgs_price2'], $currencyid).'</td>';
		if($row['pcgs_price3']!='')
		{
			$price_html.='
   <td>&nbsp;</td>';
			}else
			{
			$price_html.='</tr></td></tr>';
			}
	  }
	   if($row['pcgs_price3']!='')
	  {
	  $price_html.='<td>'.$ilance->currency->format($row['pcgs_price3'], $currencyid).'</td></tr></td></tr>';
	   }
	  $price_html.= '<tr><td width="200">NGC Price Guide</td>';
	   if($row['ngc_price1']!='')
	  {
	  
	  $price_html.= '<td>'.$row['ngc_price1'].'</td>';
		if($row['ngc_price2']!=''||$row['ngc_price3']!='')
		{
			$price_html.='
   <td>&nbsp;</td>';
			}
			else
			{
			$price_html.='</tr></td></tr></table>';
			}
	  }
	  
	  if($row['ngc_price2']!='')
	  {
		 $price_html.='<td>'.$row['ngc_price2'].'</td>';
		  if($row['ngc_price3']!='')
		  {
			$price_html.='
   <td>&nbsp;</td>';
		   }
			else
			{
			$price_html.='</tr></td></tr></table>';
			}
	  }
	   if($row['ngc_price3']!='')
	  {
	  $price_html.='<td>'.$row['ngc_price3'].'</td></tr></td></tr></table>';
	   }
	$pop_html='<table  border="0" cellpadding="5"><tr><td></td></tr>';
	 $pop_html.= '<tr><td></td>';
	
	 if($row['grade1']!='')
  {
  $pop_html.= '<td>'.$row['grade1'].'</td>';
	if($row['grade2']!=''||$row['grade3']!='')
	{
		$pop_html.='<td>|</td>';
		}
  }
  if($row['grade2']!='')
  {
  $pop_html.='<td>'.$row['grade2'].'</td>';
    if($row['grade3']!='')
	{
		$pop_html.='
    <td>|</td>';
		}
  }
  
     if($row['grade3']!='')
  {
  $pop_html.='<td>'.$row['grade3'].'</td></tr>';
  }
	  $pop_html.= '<tr><td width="200">PCGS</td>';
	  if($row['pcgs_population1']!='')
	  {
	 $pop_html.= '<td>'.$row['pcgs_population1'].'</td>';
		if($row['pcgs_population2']!=''||$row['pcgs_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr>';
			}
	  }
	  if($row['pcgs_population2']!='')
	  {
	  $pop_html.='<td>'.$row['pcgs_population2'].'</td>';
		if($row['pcgs_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr>';
			}
	  }
	  
		
	  
			 if($row['pcgs_population3']!='')
	  {
	  $pop_html.='<td>'.$row['pcgs_population3'].'</td></tr></td></tr>';
	   }
	   $pop_html.= '<tr><td width="200">NGC</td>';
		if($row['ngc_population1']!='')
	  {
	 $pop_html.= '<td>'.$row['ngc_population1'].'</td>';
		if($row['ngc_population2']!=''||$row['ngc_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr></table>';
			}
	  }
	  if($row['ngc_population2']!='' && $row['pcgs_population2']!='')
	  {
	  $pop_html.='<td>'.$row['ngc_population2'].'</td>';
		if($row['ngc_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr></table>';
			}
	  }
	   else
	   {
	   $pop_html.='</tr></td></tr></table>';
	   }
	  
		
	  
			 if($row['ngc_population3']!='' && $row['pcgs_population3']!='')
	  {
	  $pop_html.='<td>'.$row['ngc_population3'].'</td></tr></td></tr></table>';
	   }
	   else
	   {
	   $pop_html.='</tr></td></tr></table>';
	   }
	   $html['value']='true';
}

else if($row['coin_type']=='ngc')
{
	   $price_html.='<table  border="0" cellpadding="5"><tr><td></td></tr>';
	   $price_html.= '<tr><td></td>';
	    if($row['grade1']!='')
  {
  $price_html.= '<td>'.$row['grade1'].'</td>';
	if($row['grade2']!=''||$row['grade3']!='')
	{
		$price_html.='<td>|</td>';
		}
  }
  if($row['grade2']!='')
  {
  $price_html.='<td>'.$row['grade2'].'</td>';
    if($row['grade3']!='')
	{
		$price_html.='
    <td>|</td>';
		}
  }
  
     if($row['grade3']!='')
  {
  $price_html.='<td>'.$row['grade3'].'</td></tr>';
  }
	
	
	  $price_html.= '<tr><td width="200">PCGS Price Guide</td>';
	  if($row['pcgs_price1']!='')
	  {
    	  $price_html.= '<td>'.$row['pcgs_price1'].'</td>';
		  if($row['pcgs_price2']!=''||$row['pcgs_price3']!='')
		  {
		   $price_html.='
   <td>&nbsp;</td>';
		  }
		 else
		 {
		  $price_html.='</tr></td></tr>';
		 }
	  }
	  
	  if($row['pcgs_price2']!='')
	  {
	  $price_html.='<td>'.$row['pcgs_price2'].'</td>';
		if($row['pcgs_price3']!='')
		{
			 $price_html.='
   <td>&nbsp;</td>';
			}else
			{
			$price_html.='</tr></td></tr>';
			}
	  }
	   if($row['pcgs_price3']!='')
	  {
	  $price_html.='<td>'.$row['pcgs_price3'].'</td></tr></td></tr>';
	   }
	  $price_html.= '<tr><td width="200">NGC Price Guide</td>';
	   if($row['ngc_price1']!='')
	  {
	  
	  $price_html.= '<td>'.$ilance->currency->format($row['ngc_price1'], $currencyid).'</td>';
		if($row['ngc_price2']!=''||$row['ngc_price3']!='')
		{
			 $price_html.='
   <td>&nbsp;</td>';
			}
			else
			{
			$price_html.='</tr></td></tr></table>';
			}
	  }
	  
	  if($row['ngc_price2']!='')
	  {
		 $price_html.='<td>'.$ilance->currency->format($row['ngc_price2'], $currencyid).'</td>';
		  if($row['ngc_price3']!='')
		  {
			 $price_html.='
   <td>&nbsp;</td>';
		   }
			else
			{
			$price_html.='</tr></td></tr></table>';
			}
	  }
	   if($row['ngc_price3']!='')
	  {
	  $price_html.='<td>'.$ilance->currency->format($row['ngc_price3'], $currencyid).'</td></tr></td></tr></table>';
	   }
	$pop_html='<table  border="0" cellpadding="5"><tr><td></td></tr>';
	 $pop_html.= '<tr><td></td>';
	
	  if($row['grade1']!='')
  {
  $pop_html.= '<td>'.$row['grade1'].'</td>';
	if($row['grade2']!=''||$row['grade3']!='')
	{
		$pop_html.='<td>|</td>';
		}
  }
  if($row['grade2']!='')
  {
  $pop_html.='<td>'.$row['grade2'].'</td>';
    if($row['grade3']!='')
	{
		$pop_html.='
    <td>|</td>';
		}
  }
  
     if($row['grade3']!='')
  {
  $pop_html.='<td>'.$row['grade3'].'</td></tr>';
  }
	  $pop_html.= '<tr><td width="200">PCGS</td>';
	  if($row['pcgs_population1']!='')
	  {
	 $pop_html.= '<td>'.$row['pcgs_population1'].'</td>';
		if($row['pcgs_population2']!=''||$row['pcgs_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr>';
			}
	  }
	  if($row['pcgs_population2']!='')
	  {
	  $pop_html.='<td>'.$row['pcgs_population2'].'</td>';
		if($row['pcgs_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr>';
			}
	  }
	  
		
	  
			 if($row['pcgs_population3']!='')
	  {
	  $pop_html.='<td>'.$row['pcgs_population3'].'</td></tr></td></tr>';
	   }
	   $pop_html.= '<tr><td width="200">NGC</td>';
		if($row['ngc_population1']!='')
	  {
	 $pop_html.= '<td>'.$row['ngc_population1'].'</td>';
		if($row['ngc_population2']!=''||$row['ngc_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr></table>';
			}
	  }
	  if($row['ngc_population2']!='' && $row['pcgs_population2']!='')
	  {
	  $pop_html.='<td>'.$row['ngc_population2'].'</td>';
		if($row['ngc_population3']!='')
		{
			$pop_html.= '<td>&nbsp;</td>';
			}
			else
			{
			$pop_html.='</tr></td></tr></table>';
			}
	  }
	   else
	   {
	   $pop_html.='</tr></td></tr></table>';
	   }
	  
		
	  
			 if($row['ngc_population3']!='' && $row['pcgs_population3']!='')
	  {
	  $pop_html.='<td>'.$row['ngc_population3'].'</td></tr></td></tr></table>';
	   }
	   else
	   {
	   $pop_html.='</tr></td></tr></table>';
	   }
	   $html['value']='true';
}
$html['pop_html']=$pop_html;
$html['price_html']=$price_html;

return $html;
}


function fetch_price_population_save($pid)
{
global $ilance;

$sql=$ilance->db->query("select pcgs,Grading_Service,Grade,Star,Plus from  ".DB_PREFIX."coins where project_id='".$pid."'");
$row=$ilance->db->fetch_array($sql);
//$population_and_prices=fetch_price_population($row['pcgs'],$row['Grade'],$row['Plus'],$row['Star']);
if($row['Grading_Service']=='PCGS')
{
	$sql1=$ilance->db->query("select grade from  ".DB_PREFIX."pps_pcgs_price where plus='".$row['Plus']."' and pcgs='".$row['pcgs']."'");
	$cou=$ilance->db->num_rows($sql1);
	if($ilance->db->num_rows($sql1)>0)
	{
	while($row1=$ilance->db->fetch_array($sql1))
	{
	$list.=$row1['grade'];
	$list.=",";
	}
	$grade_list=explode(",",$list);
    $grd1=$grade_list;
	}
	else
	{
	}
	//$row['Grade']=66;
	for($i=0;$i<$cou;$i++)
	{
	  if($grd1[$i]==$row['Grade'])
	  {
		 $pcgs_gd1=$grd1[$i];
		 $pcgs_gd2=$grd1[$i+1];
		 $pcgs_gd3=$grd1[$i+2];
	  }
	}
	$grd1=array($pcgs_gd1,$pcgs_gd2,$pcgs_gd3);
	$population_and_prices=fetch_price_population($row['pcgs'],$row['Grade'],$row['Plus'],$row['Star'],$grd1,$row['Grading_Service']);
	$price_pop_details=$population_and_prices['pcgs'];
	$price_pop_details1=$population_and_prices['ngc'];
	$sub_sql="insert into ".DB_PREFIX.   "pps_population(ondate,grade1,grade2,grade3,pcgs_price1,pcgs_price2,pcgs_price3,ngc_price1,ngc_price2,ngc_price3,pcgs_population1,pcgs_population2,pcgs_population3,ngc_population1,ngc_population2,ngc_population3,project_id,cid,coin_type)
			 values(
			'".DATETIME24H."','".$pcgs_gd1."',
			'".$pcgs_gd2."','".$pcgs_gd3."','".$price_pop_details['price'][0]."','".$price_pop_details['price'][1]."','".$price_pop_details['price'][2]."','".$price_pop_details1['price'][0]."','".$price_pop_details1['price'][1]."','".$price_pop_details1['price'][2]."',
			'".$price_pop_details['population'][0]."','".$price_pop_details['population'][1]."','".$price_pop_details['population'][2]."','".$price_pop_details1['population'][0]."','".$price_pop_details1['population'][1]."','".$price_pop_details1['population'][2]."','".$pid."','".$row['pcgs']."','".strtolower($row['Grading_Service'])."') ";
	$ilance->db->query($sub_sql);
}

else
{
	$sql1=$ilance->db->query("select proof,value from  ".DB_PREFIX."coin_proof where value='".$row['Grade']."'");
	if($ilance->db->num_rows($sql1)>0)
	{
	        $row1_new=$ilance->db->fetch_array($sql1);
	        $row2=$row1_new['proof'].$row1_new['value'];
			$sql2=$ilance->db->query("show columns from  ".DB_PREFIX."pps_ngc_price");
			$cou=$ilance->db->num_rows($sql2);
			if($ilance->db->num_rows($sql2)>0)
			{
			while($row1=$ilance->db->fetch_array($sql2))
			{
			$list.=$row1['Field'];
	        $list.=",";
			}
			$grade_list=explode(",",$list);
            $grd1=$grade_list;
			}
			else
			{
			}
			for($i=0;$i<$cou;$i++)
			{
			  if($grd1[$i]==$row2)
			  {
			    
				 $ngc_gd1=$grd1[$i];
				 $ngc_gd2=$grd1[$i+1];
				 $ngc_gd3=$grd1[$i+2];
				
			  }
			}
			$grd1=array($ngc_gd1,$ngc_gd2,$ngc_gd3);
			$population_and_prices=fetch_price_population($row['pcgs'],$row['Grade'],$row['Plus'],$row['Star'],$grd1,$row['Grading_Service']);
			$price_pop_details=$population_and_prices['ngc'];
			$price_pop_details1=$population_and_prices['pcgs'];
			$sub_sql="insert into ".DB_PREFIX.   "pps_population(ondate,grade1,grade2,grade3,pcgs_price1,pcgs_price2,pcgs_price3,ngc_price1,ngc_price2,ngc_price3,pcgs_population1,pcgs_population2,pcgs_population3,ngc_population1,ngc_population2,ngc_population3,project_id,cid,coin_type)
			 values(
			'".DATETIME24H."','".$ngc_gd1."',
			'".$ngc_gd2."','".$ngc_gd3."','".$price_pop_details1['price'][0]."','".$price_pop_details1['price'][1]."','".$price_pop_details1['price'][2]."','".$price_pop_details['price'][0]."','".$price_pop_details['price'][1]."','".$price_pop_details['price'][2]."',
			'".$price_pop_details1['population'][0]."','".$price_pop_details1['population'][1]."','".$price_pop_details1['population'][2]."','".$price_pop_details['population'][0]."','".$price_pop_details['population'][1]."','".$price_pop_details['population'][2]."','".$pid."','".$row['pcgs']."','".strtolower($row['Grading_Service'])."') ";
			$ilance->db->query($sub_sql);
   }
}
}
function  fetch_price_population($pcgs=0,$grade=0,$plus=0,$star=0,$grd1=0,$grading_service='')
{
global $ilance;
//pcgs price 	coinpricesplus.csv 	pcgs_price

//$grd2=$this->fetch_price_population_save($grd1);

//(grade='".$grade."' or grade='".$next_grade."')

//PCGS grading service

if($grading_service=='PCGS')
{
	for($i=0; $i<count($grd1);$i++)
	{
		if($grd1[$i]=='')
		{
			$prices[$i]='';
			$all['pcgs']['price']=$prices;
			
		 }
		 else
		 {
			$column_name=$grd1[$i];
			
				$sql=$ilance->db->query("select * from ".DB_PREFIX."pps_pcgs_price where pcgs='".$pcgs."' and grade='".$column_name."' and plus='".$plus."'");
				if($ilance->db->num_rows($sql))
				{
					$result=$ilance->db->fetch_array($sql);
					$prices[$i]=$result['pcgspriceguidevalue'];
					$price1[$i]='-';
					$all['pcgs']['price']=$prices;
					$all['ngc']['price']=$price1;
				}
			
		  }
	 }
	//pcgs populations
	
	for($i=0; $i<count($grd1);$i++)
	{
		if($grd1[$i]=='')
		{
			$population[$i]='';
			$all['pcgs']['population']=$population;
			
		}
		else
		{
		   if($plus>0)
		   {
			  $column_name='grade'.$grd1[$i].'plus';
		   }
		   else
		   {
			  $column_name='grade'.$grd1[$i];
		   }
			
			$pcgs_query1=$ilance->db->query("show columns from  ".DB_PREFIX."pps_pcgs_population");
			$pcgs_fetch1=$ilance->db->fetch_array($pcgs_query1);
			$list_pcgs=$pcgs_fetch1['Field'];
			if($column_name==$list_pcgs)
			{
				$sql=$ilance->db->query("select ".$column_name." from ".DB_PREFIX."pps_pcgs_population where spec='".$pcgs."'");
				if($ilance->db->num_rows($sql))
				{
					while($result=$ilance->db->fetch_array($sql))
					{
					$population[$i]=$result[$column_name];
					//$i++;
					}
				   $all['pcgs']['population']=$population;
				}
				else
				{
				  $population[$i]='-';
				  $all['pcgs']['population']=$population;
				}
			}
			else
			{
			  $population[$i]='-';
			  $all['pcgs']['population']=$population;
			}	
		
		 }
	  }
	//ngc population
		$sql1=$ilance->db->query("select value from  ".DB_PREFIX."coin_proof");
		$cou1=$ilance->db->num_rows($sql1);
		if($ilance->db->num_rows($sql1)>0)
		{
			while($row1=$ilance->db->fetch_array($sql1))
				{
					$list.=$row1['value'];
					$list.=",";
				}
				$grade_list=explode(",",$list);
				$value_list=$grade_list;
		}
		for($i=0;$i<$cou1;$i++)
		{
			  if($value_list[$i]==$grade)
			  {
				 $ngc_val1=$value_list[$i];
				 $ngc_val2=$value_list[$i+1];
				 $ngc_val3=$value_list[$i+2];
			  }
		}
		$ngc_list=array($ngc_val1,$ngc_val2,$ngc_val3);
		for($i=0;$i<count($ngc_list);$i++)
		{
			if($ngc_list[$i]=='')
		    {
				$population[$i]='';
				$all['ngc']['population']=$population;
		    }
			else
	       {
				if($plus>0 and $star>0)
				{
				   $column_name_str=$ngc_list[$i].'+*';
				}
				if($plus>0)
			    {
				   $column_name_str=$ngc_list[$i].'+';
				}
				elseif($star>0)
				{
					 $column_name_str=$ngc_list[$i].'*';
				}
				else
				{
				   $column_name_str=$ngc_list[$i];
				}
				$ngc_query1=$ilance->db->query("show columns from  ".DB_PREFIX."pps_ngc_population");
				$ngc_fetch1=$ilance->db->fetch_array($ngc_query1);
				$list_ngc=$ngc_fetch1['Field'];
			   if($column_name_str==$list_ngc)
			   {
					 $sql=$ilance->db->query("select `".$column_name_str."` from ".DB_PREFIX."pps_ngc_population where PrintOrder='".$pcgs."'"); 
					 if($ilance->db->num_rows($sql)>0)
					 {
					   $result=$ilance->db->fetch_array($sql);
					   $population[$i]=$result[$column_name_str];
						$all['ngc']['population']=$population;
					  }
					  else
					  {
					  $population[$i]='-';
					  $all['ngc']['population']=$population;
					  }
			  }	  
			  else
			  {
				  $population[$i]='-';
				  $all['ngc']['population']=$population;
			  }
	     }  
      }
	
}
// end PCGS grading service


// NGC grading service
else
{
    //ngc price
	for($i=0; $i<count($grd1);$i++)
	{
		if($grd1[$i]=='')
		{
			$prices[$i]='';
			$all['ngc']['price']=$prices;
			
		 }
		 else
		{
			$column_name=$grd1[$i];
			$ngc_query2=$ilance->db->query("show columns from  ".DB_PREFIX."pps_ngc_price");
			$ngc_fetch2=$ilance->db->fetch_array($ngc_query2);
			$list_ngc1=$ngc_fetch2['Field'];
			if($column_name==$list_ngc1)
			{
				$sql=$ilance->db->query("select ".$column_name." from ".DB_PREFIX."pps_ngc_price where pcgs='".$pcgs."'");
				if($ilance->db->num_rows($sql))
				{
					$result=$ilance->db->fetch_array($sql);
					//$grades[$i]=$result['grade'];
					$price[$i]='-';
					$prices[$i]=$result[$column_name];
					//$all['pcgs']['grade']=$grades;
					$all['pcgs']['price']=$price;
					$all['ngc']['price']=$prices;
					
				}
			 }
			 else
			 {
			        $price[$i]='-';
					$prices[$i]='-';
					//$all['pcgs']['grade']=$grades;
					$all['pcgs']['price']=$price;
					$all['ngc']['price']=$prices;
			 }	
		}
	 }
	//pcgs populations
	
	$sql1=$ilance->db->query("select grade from  ".DB_PREFIX."pps_pcgs_price where plus='".$plus."' and pcgs='".$pcgs."'");
	$cou=$ilance->db->num_rows($sql1);
	if($ilance->db->num_rows($sql1)>0)
	{
		while($row1=$ilance->db->fetch_array($sql1))
		{
			$list.=$row1['grade'];
			$list.=",";
		}
	    $grade_list=explode(",",$list);
        $grd1=$grade_list;
	}
	else
	{
	}
	
	for($i=0;$i<$cou;$i++)
	{
	  if($grd1[$i]==$grade)
	  {
		 $pcgs_gd1=$grd1[$i];
		 $pcgs_gd2=$grd1[$i+1];
		 $pcgs_gd3=$grd1[$i+2];
	  }
	}
	$grd2=array($pcgs_gd1,$pcgs_gd2,$pcgs_gd3);
	for($i=0; $i<count($grd2);$i++)
	{
		if($grd2[$i]=='')
		{
			$population[$i]='';
			$all['pcgs']['population']=$population;
			
		 }
	    else
	    {
		   if($plus>0)
			{
			 $column_name='grade'.$grd2[$i].'plus';
			}
			else
			{
			$column_name='grade'.$grd2[$i];
			}
		$query1=$ilance->db->query("show columns from  ".DB_PREFIX."pps_pcgs_population");
	    $fetch1=$ilance->db->fetch_array($query1);
		$list_field=$fetch1['Field'];
		if($column_name==$list_field)
		{
			$sql=$ilance->db->query("select ".$column_name." from ".DB_PREFIX."pps_pcgs_population where spec='".$pcgs."'");
			if($ilance->db->num_rows($sql))
			{
				while($result=$ilance->db->fetch_array($sql))
				{
     				$population[$i]=$result[$column_name];
				}
			   $all['pcgs']['population']=$population;
		    }
			else
			{
			  $population[$i]='-';
			  $all['pcgs']['population']=$population;
			}
		}
		else
		{
		  $population[$i]='-';
		  $all['pcgs']['population']=$population;
		}	
	
	   }
	}
	//ngc population
	$sql3=$ilance->db->query("select value from  ".DB_PREFIX."coin_proof");
	$cou1=$ilance->db->num_rows($sql3);
	if($ilance->db->num_rows($sql3)>0)
	{
		while($row1=$ilance->db->fetch_array($sql3))
			{
				$list.=$row1['value'];
				$list.=",";
			}
			$grade_list=explode(",",$list);
            $value_list=$grade_list;
	}
		for($i=0;$i<$cou1;$i++)
	   {
		  if($value_list[$i]==$grade)
		  {
			 $ngc_val1=$value_list[$i];
			 $ngc_val2=$value_list[$i+1];
			 $ngc_val3=$value_list[$i+2];
		  }
	  }
		$ngc_list=array($ngc_val1,$ngc_val2,$ngc_val3);
		for($i=0;$i<count($ngc_list);$i++)
		{
		   if($ngc_list[$i]=='')
           {
			$population[$i]='';
			$all['ngc']['population']=$population;
			
	       }
		   else
		   {
					if($plus>0 and $star>0)
					{
					   $column_name_str=$ngc_list[$i].'+*';
					}
					if($plus>0)
				   {
					   $column_name_str=$ngc_list[$i].'+';
					}
					elseif($star>0)
					{
						 $column_name_str=$ngc_list[$i].'*';
					}
					else
					{
					   $column_name_str=$ngc_list[$i];
					 }
					 
					$ngc_query1=$ilance->db->query("show columns from  ".DB_PREFIX."pps_ngc_population");
					$ngc_fetch1=$ilance->db->fetch_array($ngc_query1);
					$list_ngc=$ngc_fetch1['Field'];
				    if($column_name_str==$list_ngc)
				    {
						 $sql=$ilance->db->query("select `".$column_name_str."` from ".DB_PREFIX."pps_ngc_population where PrintOrder='".$pcgs."'"); 
						 if($ilance->db->num_rows($sql)>0)
						 {
						   $result=$ilance->db->fetch_array($sql);
						   $population[$i]=$result[$column_name_str];
							$all['ngc']['population']=$population;
						  }
						  else
						  {
							  $population[$i]='-';
							  $all['ngc']['population']=$population;
						  }
				   }	  
				   else
				   {
					  $population[$i]='-';
					  $all['ngc']['population']=$population;
				   }
			 }  
		  }
	 
}

// end NGC grading service
return $all;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>