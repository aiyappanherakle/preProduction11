<?php

$serverAddr = $_SERVER['HTTP_HOST'];

switch ($serverAddr) {
    case 'coins.greatcollections.com':
    case '173.213.224.148':
    case '10.12.179.10':
    case 'edge.greatcollections.com': 
        // echo "we're currently connected to ".$serverAddr;
        error_reporting(0);
        define('SUB_FOLDER_ROOT','');
        define('DATABASE', 'gc_ilance');
        define('SERVER', 'localhost');
        define('SERVER_USERNAME', 'gcadmin');
        define('SERVER_PASSWORD', 'voted_Signal]quicker3811');
        define('DB_PREFIX','ilance_');
        define('DIR_SERVER_ROOT',str_replace('functions/conf.php','',__FILE__));
        define('MEMCACHESERVER','127.0.0.1');
        define('MEMCACHEPORT','11211');
        define('SMTP_USERNAME',ini_get('SMTP'));
        define('SMTP_PASSWORD','password');
        define('SITE_TO_EMAIL','sukumaran.j@gmail.com');
        // echo ' '.SERVER_USERNAME;
        // echo '$serverAddr='.$serverAddr.'<br />';
        // echo 'DIR_SERVER_ROOT='.DIR_SERVER_ROOT.'<br />';
        break;
    case 'beta.greatcollections.com':
        error_reporting(0);
        define('SUB_FOLDER_ROOT','/');
        define('DATABASE', 'gcbeta_ilance');
        define('SERVER', 'localhost');
        define('SERVER_USERNAME', 'gcbeta_ilance');
        define('SERVER_PASSWORD', 'kennedy1/2');
        define('DB_PREFIX','ilance_');
        define('DIR_SERVER_ROOT',str_replace('functions/conf.php','',__FILE__));
        define('MEMCACHESERVER','127.0.0.1');
        define('MEMCACHEPORT','11211');
        define('SMTP_USERNAME',ini_get('SMTP'));
        define('SMTP_PASSWORD','password');
        define('SITE_FROM_EMAIL','sukumar@herakle.com');
        define('SITE_TO_EMAIL','theoquimby@gmail.com');
        break;
    default:
		// check for Mark's development server
		if ($_SERVER['SERVER_SOFTWARE'] == 'Microsoft-IIS/7.5' && $_SERVER['APPL_PHYSICAL_PATH'] == 'C:\\GC\\preProduction\\')
		{
			error_reporting(0);
			// error_reporting(E_ALL); ini_set('display_errors', 'stdout'); restore_error_handler(); mysqli_report(MYSQLI_REPORT_STRICT);  // MYSQLI_REPORT_ALL also fails on warnings, like that an index isn't available to optimize a query
			define('SUB_FOLDER_ROOT','gc/');
			define('DATABASE', 'gc_ilance');
			define('SERVER', 'localhost');
			define('SERVER_USERNAME', 'root');
			define('SERVER_PASSWORD', '');
			define('DB_PREFIX','ilance_');
			define('DIR_SERVER_ROOT',str_replace('functions\\conf.php','',__FILE__));
			define('MEMCACHESERVER','127.0.0.1');
			define('MEMCACHEPORT','11211');
			define('SMTP_USERNAME',ini_get('SMTP'));
			define('SMTP_PASSWORD','password');
			define('SITE_FROM_EMAIL','markerb@gmail.com');
			define('SITE_TO_EMAIL','markerb@gmail.com');
			break;
		}

        error_reporting(0);
        define('SUB_FOLDER_ROOT','preProduction11/');
        define('DATABASE', 'gc');
        define('SERVER', 'localhost');
        define('SERVER_USERNAME', 'root');
        define('SERVER_PASSWORD', '');
        define('DB_PREFIX','ilance_');
        define('DIR_SERVER_ROOT',str_replace('functions/conf.php','',__FILE__).'/');
        define('MEMCACHESERVER','127.0.0.1');
        define('MEMCACHEPORT','11211');
        define('SMTP_USERNAME',ini_get('SMTP'));
        define('SMTP_PASSWORD','password');
        define('SITE_FROM_EMAIL','aiyappan@herakle.com');
        define('SITE_TO_EMAIL','aiyappan.herakle@gmail.com');
}



?>
