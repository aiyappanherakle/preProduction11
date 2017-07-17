<?PHP
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
$sel = $ilance->db->query("SELECT coin_id,consignid,user_id FROM ".DB_PREFIX."coins WHERE coin_id IN (28583,27490,27476,34464,27497,28544,28582,27498,27507,27475,34660,27479,27500,34788,28573,27502,34791,27499,28587,34686,28592,27488,27491,34465,28585,28594,23129,27501,28586,34792,27487,34796,34797,28580,28564,34682,34687,30064,34780,23900,28535,27478,28565,28570,27504,23101,27489,28576,30056,34689,34771,34692,28572,28591,30069,20774,30057,28588,30054,30055,30996,28590,31002,30067,30989,34795,23899,30979,30997,30972,26758,28531,28533,28584,28593,34659,34675,34690,26757,26761,27484,30068,26760,30060,30065,34677,34767,30059,30066,30977,34676,15876,28537,28538,28579,28589,30061,34657,35282,28545,28548,30072,34769,27495,30965,30976,30074,23902,27505,20123,27492,30070,30966,34775,28532,30062,31968,31974,20701,27481,28529,34678,30058,15889,19014,34768,27506,35264,16594,16593,34684,35277,34661,35268,10758,23144,34798,19012,28536,34784,18988,34654,34668,34790,14282,18973,17578,23119,30063,34655,17594,18396,23142,28549,34673,19000,30071,30073,16636,16639,18398,18408,20676,23120,14194,19042,16595,16606,18413,18999,20121,27486,28551,28567,34709,17595,19017,13703,19034,28539,16592,20753,16630,15888,16629,16590,28542,16589,16581,20740,18401,19040,23111,23133,28541,34653,16601,16608,20707,20722,25331,28547,28550,28553,28575,34772,34777,34779,18418,34691,12642,16600,18404,18986,28561,18972,16598,18991,18990,31973,34665,26763,28546,31967,26721,28578,34702,26745,28543,28571,28574,35274,35286,18996,26719,35279,14180,15891,20113,20719,23115,28554,28555,28557,28562,28563,34685,18979,26751,28552,35276,19043,26720,26718,26716,28559,26728,26729,18980,20703,28560,17602,14218,15880,15913,20759,27474,35281,20705,26749,35266,15878,14239)");

while($res = $ilance->db->fetch_array($sel))
{
	$ilance->accounting = construct_object('api.accounting');
						
					//seller invoice create 
					$invoiceid = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						$res['user_id'],
						0,
						0,
						0,
						'Your requested  for return coin',
						sprintf("%01.2f", 0),
						sprintf("%01.2f", 0),
						'paid',
						'debit',
						'account',
						DATETIME24H,
						DATETIME24H,
						DATETIME24H,
						'Return coin',
						0,
						0,
						1,
						'',
						0,
						0
					);
					 
					$invoiceid = $invoiceid;
					
					
					$con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_return
					 (coin_id, consign_id, user_id, shipper_id, shipping_fees, charges, return_date, invoiceid,return_opt)
					 VALUES (
						   '".$res['coin_id']."',
						   '".$res['consignid']."',
						   '".$res['user_id']."',
						   '26',
						   '0',
						   '0',
						   '".DATETODAY."',
						   '".$invoiceid."',
						   'show'
					  
					)");
					$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned SELECT * FROM ilance_coins where coin_id='".$res['coin_id']."'");
					$con_insert3 = $ilance->db->query("delete FROM ilance_projects where project_id='".$res['coin_id']."'");
					$con_insert3 = $ilance->db->query("delete FROM ilance_watchlist where watching_project_id='".$res['coin_id']."'");
					$con_insert2 = $ilance->db->query("delete FROM ilance_coins where coin_id='".$res['coin_id']."'");
					
					
}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>
