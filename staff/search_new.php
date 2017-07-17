<?php
$phrase['groups'] = array(
        'search',
        'stores',
        'wantads',
        'subscription',
        'preferences',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'search',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'modal',
	'yahoo-jar',
	'flashfix'
);

// #### define top header nav ##################
$topnavlink = array(
        'main_listings'
);

// #### setup script location ##################################################
define('LOCATION', 'search');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[search]" => $ilcrumbs["$ilpage[search]"]);
$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

$old=$ilance->GPC['q'];
if(isset($ilance->GPC['q']))
{
echo '<form action="" method="post"><input type="text" name="q" value="'.$ilance->GPC['q'].'"><input type="submit" name="submit" value="search"></form>';
}
else
{
echo '<form action="" method="post"><input type="text" name="q"><input type="submit" name="submit" value="search"></form>';
}
if(!$ilance->GPC['q'])
{
$ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 ", 0, null, __FILE__, __LINE__);
}
else
{

/*###########################   conditions*/

$keyword_text=$ilance->GPC['q'];



$year=explode("-",$keyword_text);

 $diff_year = $year['1'] - $year['0'];
$yearfirst = trim($year['0']);
$yearsecond = trim($year['1']);


if(is_numeric($yearfirst) && is_numeric($yearsecond))
{
$tot=0;
for($i=0;$i<=$diff_year;$i++)
{
	$totalyear =$yearfirst+$i;

 $yrtitle=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$totalyear
								 ."%'", 0, null, __FILE__, __LINE__);
								 
								 
						if ($ilance->db->num_rows($yrtitle) > 0)
						{
						
						   while($res_val = $ilance->db->fetch_array($yrtitle))
							{
							 $tot = $tot+count($res_val['project_title']);
							  
							  $result_one.= $res_val['project_title'];
							  
							   $result_one.= '</br>';
							}
						}	
	
	
	
	
}

                        if($tot)
						{
							echo "results found for this search <b>".$ilance->GPC['q']."</b> is <b>".$tot."</b><br><br>";
							echo $result_one;
							}
							else
							{
							echo "no results found for the key word <b>".$ilance->GPC['q']."</b><br><br>";
							}
		
exit();						
					
}
 $var = str_replace('*', '' , $keyword_text);
if(is_numeric($var)) 
{

$keyword_text = str_replace('*','0',$keyword_text);
$der = '100';
$zer='000';
$twoone =abs($var).$der;
$twoo= abs($var).$zer;

$wild=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$twoo
								 ."%' || '%".$twoone
								 ."%'", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($wild) > 0)
						{
                           while($card_wild = $ilance->db->fetch_array($wild))
							{
							echo $card_wild['project_title'];
							echo '<br/>';
                             }
						}	 
exit();
}

echo '</br>';


if(strstr($keyword_text,'*'))
						{
							$keyword_text = str_replace('*','★',$keyword_text);
						}
						if($keyword_text == '$' || $keyword_text == '$1')
						{
							$keyword_text = 'Dollar';
						}
						if($keyword_text == 'Doller' || $keyword_text == 'Doller')
						{
							$keyword_text = 'Dollar';
						}
						if(strtoupper($keyword_text) == 'PENNY' || strtoupper($keyword_text) == 'CENT')
						{
							$keyword_text = 'Cent';
						}
						if(strtoupper($keyword_text) == 'EAGLE' || strtoupper($keyword_text) == 'EAGEL')
						{
							$keyword_text = 'Eagle';
						}
						if(strtoupper($keyword_text) == 'UHR')
						{
							$keyword_text = 'Ultra High Relief';
						}
						if(strtoupper($keyword_text) == 'SILVER EAGLE' || strtoupper($keyword_text) == 'EAGLE SILVER' || strtoupper($keyword_text) == 'SILVER EAGEL' || strtoupper($keyword_text) == 'EAGEL SILVER')
						{
							$keyword_text = 'Silver Eagle';
							//$keyword_text = '\"'.$keyword_text.'\"';
						}
						
					 if($keyword_text)
						{
						    $keyword_text = str_replace('10c','Dime',$keyword_text);
						    $keyword_text = str_replace('25c','Quarter',$keyword_text);
							$keyword_text = str_replace('50c','Half Dollar',$keyword_text);
							$keyword_text = str_replace('1c','cent',$keyword_text);
							$keyword_text = str_replace('2c','Two Cent',$keyword_text);
							$keyword_text = str_replace('$1','Dollar',$keyword_text);
						}
						
						
						$removes=str_split($keyword_text);
						$ref="";
						
						if($removes[count($removes)-1]=='s')
						{
						for($i=0;$i<(count($removes)-1);$i++)
						{
						$ref.=$removes[$i];
						}
						$keyword_text=$ref;
						}
						
						
								$coinpf = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "coin_proof                                         
                                ", 0, null, __FILE__, __LINE__);
								$r=0;
						if ($ilance->db->num_rows($coinpf) > 0)
						{
							while($res = $ilance->db->fetch_array($coinpf))
							{
								$first = $res['proof'].''.$res['value'];
								$second = $res['proof'].'-'.$res['value'];
								$third = $res['proof'].' '.$res['value'];
								//venkat
								if($r)
								{
								if(!in_array($res['proof'],$prrr))
								{
								$prrr[]=$res['proof'];
								}
								}
								else
								{
								$prrr[]=$res['proof'];
								}
								$r++;
								
								//venkat
								/*if(strtoupper($keyword_text) == strtoupper($first) || strtoupper($keyword_text) == strtoupper($second) || strtoupper($keyword_text) == strtoupper($third))
								{								
									//$keyword_text = '\"'.$second.'\"';
									$keyword_text = $second;
								}	
								*/
								
								$ccc=array($first,$second,$third);
								if(strstr(strtoupper($keyword_text),strtoupper($first))!=false || strstr(strtoupper($keyword_text),strtoupper($second))!=false ||strstr(strtoupper($keyword_text),strtoupper($third))!=false)
								{
								
							/*	foreach($ccc as $vall)
								{
								if(strstr(strtoupper($keyword_text),strtoupper($vall)))
								{
								echo $keyword_text=str_replace(strstr($keyword_text,$vall),strtoupper($second),$keyword_text);
								}
								}*/
								$keyword_text = $second;
								
								}
							}
						}
						for($i = 40;$i<75;$i++)
						{
							$first ='pf'.$i;
							$second = 'pf-'.$i;
							$third = 'pf '.$i;
							$fourth = 'proof-'.$i;
							$fifth = 'proof'.$i;
							$sixth = 'proof '.$i;
							$seven = 'pr'.$i;
							$eight = 'pr-'.$i;
							$nine = 'pr '.$i;
							$tenth = $i;
							
							$chk_arr=array($first,$second,$third,$fourth,$fifth,$sixth,$seven,$eight,$nine,$tenth);
							
							/*if(strtoupper($keyword_text) == strtoupper($first) || strtoupper($keyword_text) == strtoupper($second) || strtoupper($keyword_text) == strtoupper($third)|| strtoupper($keyword_text) == strtoupper($fourth) || strtoupper($keyword_text) == strtoupper($fifth) || strtoupper($keyword_text) == strtoupper($sixth) || strtoupper($keyword_text) == strtoupper($seven) || strtoupper($keyword_text) == strtoupper($eight) || strtoupper($keyword_text) == strtoupper($nine) ||(strtoupper($keyword_text) == $tenth))
							{*/
							
							
							if((strstr(strtoupper($keyword_text),strtoupper($first))!=false) || (strstr(strtoupper($keyword_text),strtoupper($second))!=false) || (strstr(strtoupper($keyword_text),strtoupper($third))!=false) || (strstr(strtoupper($keyword_text),strtoupper($fourth))!=false) || (strstr(strtoupper($keyword_text),strtoupper($fifth))!=false) || (strstr(strtoupper($keyword_text),strtoupper($sixth))!=false) || (strstr(strtoupper($keyword_text),strtoupper($seven))!=false) || (strstr(strtoupper($keyword_text),strtoupper($eight))!=false) || (strstr(strtoupper($keyword_text),strtoupper($nine))!=false) || (strstr(strtoupper($keyword_text),strtoupper($tenth))!=false) )
							{
							
							
								if(strtoupper($keyword_text) == $tenth)
								{
								$change_query=1;
								
								foreach($prrr as $value)
								{
								$chqu[]=$value."-".$i;
							    }
								}
								else
								{
								//$keyword_text = $fourth;
								foreach($chk_arr as $vall)
								{
								if(strstr($keyword_text,$vall))
								{
								$keyword_text=str_replace(strstr($keyword_text,$vall),ucfirst($fourth),$keyword_text);
								}
								}
							
								}
							
							}
								//$keyword_text ='\"'.$fourth.'\"';
								
						/*}*/
						
						
						}
						
						
$ilance->GPC['q']=$keyword_text;
/*###########################   conditions*/


if($change_query)
{

$tot=0;
foreach($chqu as $val)
{

/*$ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where ".$kk."", 0, null, __FILE__, __LINE__);*/
								 $ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$val
								 ."%'", 0, null, __FILE__, __LINE__);
								 if ($ilance->db->num_rows($ccc) > 0)
						{
						
						$tot+=$ilance->db->num_rows($ccc);
						
						/*$ff=$ilance->GPC['q']?"<b>".$ilance->GPC['q']." </b>is":"this search is";
						echo "results found for ". $ff."  <b>".$ilance->db->num_rows($ccc)."</b><br><br>";*/
						while($res = $ilance->db->fetch_array($ccc))
							{
						$resul_chang_query.=$res["project_title"];
						$resul_chang_query.="<br>";
						}
						
						}
								 
}

if($tot)
{
$ff=$old?"<b>".$old." </b>is":"this search is";
						echo "results found for ". $ff."  <b>".$tot."</b><br><br>";
						
						echo $resul_chang_query;
}
else
{
echo "no results found for the key word <b>".$old."</b><br><br>";
}
}
else
{
if(strstr($ilance->GPC['q']," "))
{
$secon=explode(" ",$ilance->GPC['q']);

if(count($secon)==2)
{
$ilance->GPC['q']=$secon[0];
$two_word=$secon[1];
$check=1;
}
else
{
$two_word="";
}
}
else
{
$two_word="";
}
$ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$ilance->GPC['q']
								 ."%'", 0, null, __FILE__, __LINE__);
								 
					if ($ilance->db->num_rows($ccc) == 0)
					{
					$ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".ucfirst($ilance->GPC['q'])
								 ."%'", 0, null, __FILE__, __LINE__);
					}			 
					if ($ilance->db->num_rows($ccc) == 0)
					{
					$ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".strtoupper($ilance->GPC['q'])
								 ."%'", 0, null, __FILE__, __LINE__);
					}	
					if ($ilance->db->num_rows($ccc) == 0)
					{
					$ccc=$ilance->db->query("SELECT project_title FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".strtolower($ilance->GPC['q'])
								 ."%'", 0, null, __FILE__, __LINE__);
					}			 
								 }
								 }
								 
						if(!$change_query)
						{		
						$two_search=0;
						$flag=0;
						 
						if ($ilance->db->num_rows($ccc) > 0)
						{
						
						
						
						
						
							while($res = $ilance->db->fetch_array($ccc))
							{
							
							if($two_word)
							{
							if(strstr($res["project_title"],$two_word))
							{
							$result_one.=$res["project_title"];
							$result_one.="<br>";
							$flag=1;
							$two_search++;
							}
							}
							else
							{
							$result_one.=$res["project_title"];
							$result_one.="<br>";
							}
							
							}
							
							
							$tww=$check?" ".$secon[1]:"";
						
						$tt_row=$two_word?$two_search:$ilance->db->num_rows($ccc);
						
						$ff=$old?"<b>".$old." </b>is":"this search is";
						if($tt_row)
						{
							echo "results found for ". $ff."  <b>".$tt_row."</b><br><br>";
							echo $result_one;
							}
							else
							{
							echo "no results found for the key word <b>".$old."</b><br><br>";
							}
							
							
							}
							else
							{
						
								echo "no results found for the key word <b>".$ilance->GPC['q']."</b><br><br>";
							
							$vvv=str_split($ilance->GPC['q']);
							$vvv_len=strlen($ilance->GPC['q']);
							$ff=count($vvv)-1;
							$final="";
							while($ff)
							{
							for($i=0;$i<$ff;$i++)
							{
							
							
							$final.=$vvv[$i];
							}
							$ff--;
							
							
							$ccc1=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$final
								 ."%'", 0, null, __FILE__, __LINE__);
								
							if ($ilance->db->num_rows($ccc1) > 0)
						{
						
						
						$ff1=$ilance->db->fetch_array($ccc1);
						
						$new_title=$ff1["project_title"];
						
						$rf=explode(" ",$new_title);
						
						$last="";
						
						$final_text="";
						/*##############search 1*/
						for($j=0;$j<count($rf);$j++)
						{
						if(strstr($rf[$j],$final))
						{
						$final_text="Do yo mean <a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						$last=$rf[$j];
						break;
						}
						elseif(strstr($rf[$j],ucfirst($final)))
						{
						$final_text="Do yo mean <a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						$last=$rf[$j];
						break;
						}
						elseif(strstr($rf[$j],strtoupper($final)))
						{
						$final_text="Do yo mean <a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
						$last=$rf[$j];
						break;
						}
						elseif(strstr($rf[$j],strtolower($final)))
						{
						$final_text="Do yo mean <a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						$last=$rf[$j];
						break;
						}
						
						
						}
						echo "<br>";
						/*##############search 2*/
						
						
						/*if(strstr($new_title,$final))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,ucfirst($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,strtoupper($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,strtolower($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						
						echo "<br>";*/
						break; 
						
						}
							
							
							unset($final);
							
							}
							
							
							
							
							
							
							
							/*###############reverse search */
							
							$gg=1;
							while($gg<=($vvv_len-1))
							{
							
							for($i=$gg;$i<$vvv_len;$i++)
							{
							
							
							$final.=$vvv[$i];
							}
							$gg++;
							
							
							$ccc1=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$final
								 ."%'", 0, null, __FILE__, __LINE__);
								
							if ($ilance->db->num_rows($ccc1) > 0)
						{
						
						
						$ff1=$ilance->db->fetch_array($ccc1);
						
						$new_title=$ff1["project_title"];
						
						$rf= explode(" ",$new_title);
						
						$last1="";
						/*##############search 1*/
						for($j=0;$j<count($rf);$j++)
						{
						if(strstr($rf[$j],$final))
						{
						$last1=$rf[$j];
						if($last!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
						$last_1=$rf[$j];
						
						break;
						}
						}
						elseif(strstr($rf[$j],ucfirst($final)))
						{
						$last1=$rf[$j];
						if($last!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
						$last_1=$rf[$j];
						
						break;
						}
						}
						elseif(strstr($rf[$j],strtoupper($final)))
						{
						$last1=$rf[$j];
						if($last!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						$last_1=$rf[$j];
						
						
						break;
						}
						}
						elseif(strstr($rf[$j],strtolower($final)))
						{
						$last1=$rf[$j];
						if($last!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						$last_1=$rf[$j];
						
						
						break;
						}
						}
						
						
						}
						
						/*##############search 2*/
						
						
						/*if(strstr($new_title,$final))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,ucfirst($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,strtoupper($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,strtolower($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						
						echo "<br>";*/
						break; 
						
						}
							
						
							unset($final);
							
							}
							
							
							
							
							
							
							
							/*##########search3*/
							$aa=1;
							$jj=$vvv_len-1;
							while($aa<=$jj)
							{
							
							for($i=$aa;$i<$jj;$i++)
							{
							
							
							$final.=$vvv[$i];
							}
							$gg++;
							$jj--;
							
							
							$ccc1=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects                                         
                                 where project_title like'%".$final
								 ."%'", 0, null, __FILE__, __LINE__);
								
							if ($ilance->db->num_rows($ccc1) > 0)
						{
						
						
						$ff1=$ilance->db->fetch_array($ccc1);
						
						$new_title=$ff1["project_title"];
						
						$rf= explode(" ",$new_title);
						
						$last1="";
						/*##############search 1*/
						for($j=0;$j<count($rf);$j++)
						{
						if(strstr($rf[$j],$final))
						{
						$last1=$rf[$j];
						if($last_2!=$last1 && $last1!=$last1 )
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
						
						
						break;
						}
						}
						elseif(strstr($rf[$j],ucfirst($final)))
						{
						$last1=$rf[$j];
						if($last_2!=$last1 && $last1!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
					
						
						break;
						}
						}
						elseif(strstr($rf[$j],strtoupper($final)))
						{
						$last1=$rf[$j];
						if($last_2!=$last1 && $last1!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
						
						
						break;
						}
						}
						elseif(strstr($rf[$j],strtolower($final)))
						{
						$last1=$rf[$j];
						if($last_2!=$last1 && $last1!=$last1)
						{
						$final_text.=",<a href='search_new.php?q=".$rf[$j]."'><b>".$rf[$j]."</b></a>";
						
						
						
						break;
						}
						}
						
						
						}
						
						/*##############search 2*/
						
						
						/*if(strstr($new_title,$final))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,ucfirst($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,strtoupper($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						elseif(strstr($new_title,strtolower($final)))
						{
						echo "Do yo mean <a href='search_new.php?q=".strstr($new_title,$final)."'><b>".strstr($new_title,$final)."</b></a>";
						
						}
						
						echo "<br>";*/
						break; 
						
						}
							
						
							unset($final);
							
							}
							
								if($final_text)
						{
						echo $final_text;
						}
							
							}
							
							
							}
							
							
		}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}					

?>