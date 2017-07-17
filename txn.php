<?php
function construct_random_value($num)
	{
		switch($num)
		{
			case "1":
			$rand = "A";
			break;
			case "2":
			$rand = "B";
			break;
			case "3":
			$rand = "C";
			break;
			case "4":
			$rand = "D";
			break;
			case "5":
			$rand = "E";
			break;
			case "6":
			$rand = "F";
			break;
			case "7":
			$rand = "G";
			break;
			case "8":
			$rand = "H";
			break;
			case "9":
			$rand = "I";
			break;
			case "10":
			$rand = "J";
			break;
			case "11":
			$rand = "K";
			break;
			case "12":
			$rand = "L";
			break;
			case "13":
			$rand = "M";
			break;
			case "14":
			$rand = "N";
			break;
			case "15":
			$rand = "O";
			break;
			case "16":
			$rand = "P";
			break;
			case "17":
			$rand = "Q";
			break;
			case "18":
			$rand = "R";
			break;
			case "19":
			$rand = "S";
			break;
			case "20":
			$rand = "T";
			break;
			case "21":
			$rand = "U";
			break;
			case "22":
			$rand = "V";
			break;
			case "23":
			$rand = "W";
			break;
			case "24":
			$rand = "X";
			break;
			case "25":
			$rand = "Y";
			break;
			case "26":
			$rand = "Z";
			break;
			case "27":
			$rand = "0";
			break;
			case "28":
			$rand = "1";
			break;
			case "29":
			$rand = "2";
			break;
			case "30":
			$rand = "3";
			break;
			case "31":
			$rand = "4";
			break;
			case "32":
			$rand = "5";
			break;
			case "33":
			$rand = "6";
			break;
			case "34":
			$rand = "7";
			break;
			case "35":
			$rand = "8";
			break;
			case "36":
			$rand = "9";
			break;
		}
                
		return $rand;
	}

$tid = '';
                for ($i = 1; $i <= 17; $i++)
                {
                        mt_srand((double)microtime() * 1000000);
                        $num = mt_rand(1, 36);
                        $tid .= construct_random_value($num);
                }

echo $tid;
?>