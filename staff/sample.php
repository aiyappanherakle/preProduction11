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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration','accounting'
);
error_reporting(E_ALL);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';

//error_reporting(E_ALL);


// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');
$ilance->subscription = construct_object('api.subscription');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[invoicepayment]" => $ilcrumbs["$ilpage[invoicepayment]"]);
// #### build our encrypted array for decoding purposes
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
//error_reporting(E_ALL);
if($_SESSION['ilancedata']['user']['isadmin'] == '1')
{
($apihook = $ilance->api('cron_unpaid_start')) ? eval($apihook) : false;
$res['user_id']='82';

				$sqlinv = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
						WHERE status = 'unpaid'
						AND isfvf != 1
						AND isif != 1 
						AND isenhancementfee != 1
						AND isbuyerfee != 1 
						AND Site_Id !=1
						AND not combine_project						
						AND user_id = '".$res['user_id']."'								   
				", 0, null, __FILE__, __LINE__);
				//$buyerfee1 = 0;
				while($resinv = $ilance->db->fetch_array($sqlinv))
				{
				   
				   if(fetch_auction('filtered_auctiontype',$resinv['projectid']) == 'regular')
				    {
					
					
						$buyfee_inv = $ilance->db->query("SELECT SUM(amount) AS buyeramount FROM ".DB_PREFIX."invoices 
												WHERE projectid = '".$resinv['projectid']."'
												AND user_id = '".$res['user_id']."'
												AND Site_Id !=1
												AND isbuyerfee = '1'");
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						//echo '<br>'.$resinv['projectid'].'--'.$res['user_id'].'---'.$res_buyfee['buyeramount'];
						$buyerfee1[] = $res_buyfee['buyeramount'];
					}
					else
					{
						//$buyerfee1[] = 0;
					}
					
					
						$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
													WHERE invoiceid = '".$resinv['invoiceid']."'
													AND buyer_id = '".$res['user_id']."'");
						if($ilance->db->num_rows($buy)>0)
						{
							$resbuy = $ilance->db->fetch_array($buy);
							$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
							WHERE coin_id = '".$resinv['projectid']."'");
							$temp=$ilance->db->fetch_array($bids);						

							$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
							$res_regardlist['qty'] = $resbuy['qty'];
							$totqty[] = $res_regardlist['qty']*$coin_no_in_set;
						}
						else
						{
							//check 	nocoin  in ilance_coins for each coins
							$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
							WHERE coin_id = '".$resinv['projectid']."'");
							$temp=$ilance->db->fetch_array($bids);		

							$res_regardlist['qty'] = 1;

							$totqty[] = empty($temp['nocoin'])?1:intval($temp['nocoin']);
						}
						
				   
			   }
				$newbuyer = array_sum($buyerfee1);
				unset($buyerfee1);
				
			   $sqlamt = $ilance->db->query("
						SELECT SUM(amount) AS amount,project_id
						FROM " . DB_PREFIX . "invoices
						WHERE status = 'unpaid'
						AND isfvf != 1
						AND isif != 1 
						AND isenhancementfee != 1
						AND isbuyerfee != 1 
						AND Site_Id !=1
						AND not combine_project						
						AND user_id = '".$res['user_id']."'            
						", 0, null, __FILE__, __LINE__);
						
						$resamt = $ilance->db->fetch_array($sqlamt);
						
						$totalamount = 	$resamt['amount'] + $newbuyer;

						$shippment_nethod_pulldown = print_shippment_nethod_pulldown($resamt['project_id'],$selected=0,'shipper_id','return change_shipper();',array_sum($totqty));
						
						
						
								//suku
							$headinclude.='<script>
							function change_shipper()
							{

							var shippers_base_cost=new Array(); 
							var shippers_added_cost=new Array();
							var international_extra_morethen_n_coins=0;

							'.$shippment_nethod_pulldown['script'].'
							var shipper=document.getElementById("shipper_id").value;

							// karthik start apr 16
							var taxamt = document.getElementById("taxhidden").value;


							var taxpresent = document.getElementById("taxhiddenyes").value;


							var taxinfonew = document.getElementById("taxinfonew").value;



							if(shipper == 26 && shippers_base_cost[shipper] == 0)

							{
							document.getElementById("free_announce").innerHTML ="<span class=\"green\">Standard shipping is free for your first auction purchase (U.S. only)</span>";

							}

							else

							{



							}

							//end
							//var totalproject = document.getElementById("total_val").value;
							if(shipper>0)
							{
							//document.getElementById("sub").disabled = false;

							invhidden=document.getElementById("invhidden").value;
							qtyhiddennew=document.getElementById("qtyhidden").value;
							projectlist=invhidden.split(",");

							//var txt = parseFloat(projectlist.length) - parseFloat(totalproject);
							var txt = parseFloat(projectlist.length);
							// muruagn changes on apr 17 for qty
							//var no_item=txt;
							var no_item=qtyhiddennew;

							if(projectlist.length > 0)
							{
							var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
							}
							else
							{

							}
							var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];

							shipping_total=shipping_total+international_extra_morethen_n_coins;
							shipping_cost=shipping_total.toFixed(2);
							//new change calculating  tax amount for shipping

							var taxcount = (taxinfonew *  shipping_cost) / 100;

							document.getElementById("taxshipcal").value = taxcount;

							var taxadd = parseFloat(document.getElementById("taxhidden1").value) + parseFloat(taxcount);

							newtaxadd = taxadd.toFixed(2);

							document.getElementById("taxhidden").value = newtaxadd;

							//end
							document.getElementById("shipping_cost").value=shipping_cost;
							calculate_total();
							}else
							{

							//document.getElementById("sub").disabled = true;
							document.getElementById("shipping_cost").value="0";
							calculate_total();
							}
							return false;
							}

							function promocodecheck(val,user_id)
							{
							if (window.XMLHttpRequest) { // Mozilla & other compliant browsers
							request = new XMLHttpRequest();
							} else if (window.ActiveXObject) { // Internet Explorer
							request = new ActiveXObject("Microsoft.XMLHTTP");
							}

							request.onreadystatechange = function ajaxResponse(){
							if (request.readyState==4){
							returned=request.responseText;
							result=returned.split("|");
							if(result[1]=="$" || result[1]=="%")
							{
							var discount=parseFloat(result[0]);
							var temp22=discount.toFixed(2);
							document.getElementById("disount_val").value=temp22;
							if(result[1]=="$")
							discount_str="US$"+temp22+" from total amount";
							if(result[1]=="%")
							discount_str=discount+" % from total amount";
							document.getElementById("promodiv").innerHTML= "You have saved "+discount_str;
							calculate_total();
							}else
							{
							document.getElementById("promodiv").innerHTML= returned;
							document.getElementById("disount_val").value=0;
							calculate_total();
							}
							}else
							{
							document.getElementById("promodiv").innerHTML= "<img src=\"images/default/working.gif\"/>";	
							}
							}
							url ="ajax.php?promocodeauction=" +val+"&projectid="+user_id;
							request.open("GET", url,true);
							request.send(null);
							}
							function calculate_total()
							{
							totalhidden_base=parseFloat(document.getElementById("totalhidden_base").value);
							disount_val=parseFloat(document.getElementById("disount_val").value);
							shipping_cost=parseFloat(document.getElementById("shipping_cost").value);
							//new changes apr22
							tax_cost=parseFloat(document.getElementById("taxhidden").value);
							tax_cost_inship = parseFloat(document.getElementById("taxshipcal").value);
							totalhidden=totalhidden_base-disount_val+shipping_cost+tax_cost_inship;
							document.getElementById("totalhidden").value=totalhidden;
							disount_val_text=disount_val.toFixed(2);
							shipping_cost_text=shipping_cost.toFixed(2);
							totalhidden_text=totalhidden.toFixed(2);
							//apr22
							document.getElementById("sales_tax_div").innerHTML="US$"+tax_cost.toFixed(2)+"";
							document.getElementById("dicount_amount_div").innerHTML="(US$"+disount_val_text+")";
							document.getElementById("ship_cost_div").innerHTML="US$"+shipping_cost_text;
							document.getElementById("totalamount_area").innerHTML="US$"+totalhidden_text;
							//oct-31

							document.getElementById("totalamt_area").innerHTML="US$"+totalhidden_text;
							}
							</script>
							';
			
			
				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->logtype = 'unpaidinvoice_vj';

				$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
				$ilance->email->slng = fetch_site_slng();	

				$ilance->email->get('unpaid_invoice_notification');		
				$ilance->email->set(array(
				'{{customer}}' =>fetch_user('username',$res['user_id']),
				'{{amount}}' => $ilance->currency->format($totalamount),
				));

				$ilance->email->send();
			
		
}

function print_shippment_nethod_pulldown($projects,$selected,$name,$onchage_script,$totqty=0)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $_SESSION['ilancedata']['user']['countryid']==500 and !$only_buynow)
	{
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$_SESSION['ilancedata']['user']['userid']."' AND status='paid'");
	if($ilance->db->num_rows($sql)==0)
	{
	$first_shipment=true;
	}
	}
	
	//karthik start on Apr 12
	
	//shipping for INTERNATIONAL CLIENTS 
				
	if($_SESSION['ilancedata']['user']['countryid']!=500)
	{			
	///invoice  over $10,000	
	if( $_SESSION['ilancedata']['user']['totalamount'] >= '10000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
		if($ilance->db->num_rows($sql))
		{
		$html='';
		$script='';
		$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
		while($line=$ilance->db->fetch_array($sql))
		{
	      /* if($line['shipperid']==$selected)
	     {*/
			if($totqty>$line['maxitem_count'])
			{
				$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
				$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
			}
			$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';'; 
			/*}
			else
			{
			
			$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}*/
	      }
		  $html.='</select>';
		 }
	}
		 
	else
	{	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
	if($ilance->db->num_rows($sql))
	{
	$html='';
	$script='';
	$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
	while($line=$ilance->db->fetch_array($sql))
	{
	if($line['domestic'] == 1)
	{
		//echo $test = $line['title'];
	}
	if($line['international'] == 1)
	{
		//echo 'inter';
		//echo $raga = $line['title'];
	}
    // oct-31
	$selected='22';
	if($line['shipperid']==$selected)
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
	}
	$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
	
	}
	else
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
		
	}
	$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';

	
	}
	}
	}
	}
	$html.='</select>';
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $_SESSION['ilancedata']['user']['totalamount'] > '10000.00')
	   {
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql))
		  {
				$html='';
				$script='';
				$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
				while($line=$ilance->db->fetch_array($sql))
				{
				   
					
					//$selected_text='';
					//if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment)
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
	             }
		        $html.='</select>';
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $_SESSION['ilancedata']['user']['totalamount'] > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			    $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql))
				{
					$html='';
					$script='';
					$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
					while($line=$ilance->db->fetch_array($sql))
					{
					   
					   
						$selected_text='';
                       
					    //oct-31
						$selected='27';
						if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment and $line['shipperid']=='27')
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
							
							
					  }
				     $html.='</select>';
				  }
		       }  
		
		else
		{	
            //new change apr19  order by carrier to basefee asc
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			$html='';
			$script='';
			$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
			while($line=$ilance->db->fetch_array($sql))
			{
			
			//	Shipping is free for your first auction purchase (U.S. only)
           //oct-31
			$selected='26';
			if($line['shipperid']==$selected)
		      $selected_text='selected="selected"';
			 else
			    $selected_text='';
	      if($first_shipment AND $line['shipperid']=='26' AND !$only_buynow)
	      {
		  
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']=0;';
		     $script.='shippers_added_cost['.$line['shipperid'].']=0;';		
			}
						
			else
			{
			 
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}
				
			}
			
			}
			}
			if($first_shipment)
			$free_announce='<div id="free_announce"><span class="green">Standard shipping is free for your first auction purchase (U.S. only)</span></div>';
			$html.='</select>';
	}

$html.=$free_announce;
	$pulldown['html']=$html;
	$pulldown['script']=$script;
	
	return $pulldown;
	}

?>