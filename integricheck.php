<?php
/*
integricheck.php - Integrity Checking script, server-side
*/
require_once('./functions/config.php');

// $output = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5);
$output = array('time' => time());
// print_r($output); exit;
// header('Content-Type: application/json'); echo json_encode($output); exit;

/*
From iLance setup environment:
define('DB_DATABASE', 'gc_ilance');
define('DB_SERVER', 'localhost');
define('DB_SERVER_PORT', '3306');
define('DB_SERVER_USERNAME', 'gc_root');
define('DB_SERVER_PASSWORD', 'kennedy1/2');
define('DB_PERSISTANT_MASTER', 1);
*/
$mysqli = new mysqli(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, DB_SERVER_PORT);
	// open a connection to the database
	
if ($mysqli->connect_errno)
{
	$output['sql'] = 'connect error: '.$mysqli->connect_error;
} else {
	if ($result = $mysqli->query('SELECT MAX(id) AS maxid FROM ilance_projects'))
	{
		$row = $result->fetch_object();
		if ($row->maxid > 0)
			$output['sql'] = 'ok';
		else
			$output['sql'] = 'id error';
		$result->close();
	} else
		$output['sql'] = 'query error: '.$mysqli->error;
	$mysqli->close();
}

$ctx = stream_context_create(array('http'=>
	array(
		'timeout' => 5,	// 5 second timeout
		/* 'ignore_errors' => true */
	)
));
$pageErr = '';
try {
	// $page = file_get_contents('http://www.greatcollections.com/saddfssdfdfssfd', false, $ctx);
	// $page = file_get_contents('http://192.168.1.200:2101/', false, $ctx);
	$page = file_get_contents('http://www.greatcollections.com/Denomination/32/Books-and-Accessories', false, $ctx);
}
catch (Exception $e) {
	$pageErr = ', error: '.$e->getMessage();
}
// echo $page; echo "<BR><BR><B><FONT SIZE=5>$pageErr</FONT></B>"; exit;
	// get contents of book list
// >Showing results <strong>1</strong> to <strong>0</strong> of <strong> 0</strong>
// >No results found<

if (preg_match("#>Showing results <strong>[0-9]+</strong> to <strong>[0-9]+</strong> of <strong> [0-9]+</strong>#", $page))
	$output['page'] = 'ok';
else
	$output['page'] = 'fail'.$pageErr;

header('Content-Type: application/json');
echo json_encode($output);

?>