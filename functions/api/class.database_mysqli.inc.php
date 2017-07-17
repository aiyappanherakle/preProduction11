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

if (!class_exists('ilance_database'))
{
	echo 'Could not find database backend.';
	exit;
}

/**
* MySQLi database class to perform the majority of database related functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance_mysqli extends ilance_database
{
        /*
	* MySQLi Database Array Resource Types
	*
	* @var	    $types
	*/
        var $types = array(
		DB_NUM => MYSQLI_NUM,
		DB_ASSOC => MYSQLI_ASSOC,
		DB_BOTH => MYSQLI_BOTH
	);
	
	/*
	* MySQLi Database Interface Functions
	*
	* @var	    $functions
	*/
	var $functions = array(
		'select_db' => 'mysqli_select_db',
		'pconnect' => 'mysqli_real_connect',
		'connect' => 'mysqli_real_connect',
		'query' => 'mysqli_query',
		'query_unbuffered' => 'mysqli_unbuffered_query',
		'fetch_row' => 'mysqli_fetch_row',
		'fetch_object' => 'mysqli_fetch_object',
		'fetch_array' => 'mysqli_fetch_array',
		'fetch_field' => 'mysqli_fetch_field',
		'free_result' => 'mysqli_free_result',
		'data_seek' => 'mysqli_data_seek',
		'error' => 'mysqli_error',
		'errno' => 'mysqli_errno',
		'affected_rows' => 'mysqli_affected_rows',
		'num_rows' => 'mysqli_num_rows',
		'num_fields' => 'mysqli_num_fields',
		'field_name' => 'mysqli_field_name',
		'insert_id' => 'mysqli_insert_id',
		'list_tables' => 'mysqli_list_tables',
                'list_fields' => 'mysqli_list_fields',
		'escape_string' => 'mysqli_real_escape_string',
		'real_escape_string' => 'mysqli_real_escape_string',
		'close' => 'mysqli_close',
		'client_encoding' => 'mysqli_client_encoding',
		'create_db' => 'mysqli_create_db',
		'ping' => 'mysqli_ping'
	);
	
        /*
	* Constructor
	*
	* @param	object	        ilance registry object
	* @param        integer         cache time out
	* @param        bool            cache results to database within cache table?
	*/
	function ilance_mysqli(&$registry, $cachetimeout = 1, $cachetodatabase = true)
	{
                $this->registry =& $registry;
		parent::ilance_database();
		
                $this->connect();
	}
	
        /*
	* Connect to the database and return the connection link resource
	*
	* Connects to a database server and physically returns the connection link identifier
	* 
	* @return	boolean
	*/
        function db_connect()
	{
                $hostname = DB_SERVER;
                $username = DB_SERVER_USERNAME;
                $password = DB_SERVER_PASSWORD;
		$dbcharset = DB_CHARSET;
		$dbcollate = DB_COLLATE;
                $port = DB_SERVER_PORT;
		$port = $port ? $port : 3306;
		
 		
		
		$link = mysqli_init();
		
		if (!empty($configfile))
		{
			mysqli_options($link, MYSQLI_READ_DEFAULT_FILE, $configfile);
		}

		$connect = $this->functions['connect']($link, $hostname, $username, $password, '', $port);
		   if($connect === false)     
	       {
	       	if(ENVIROMNENT=='production')
	       	 {
	       	 	require_once(DIR_FUNCTIONS .'api'. '/class.database.inc.php');
				$ilance->cache = new ilance_memcached();
				$notification_flag=false;
				$notification_flag=$ilance->cache->fetch('notification_flag');
				if($notification_flag!=true)
				{
					$ilance->cache->store('notification_flag',true,300);
					$msg = "Database ";
					$msg = wordwrap($msg,70);
					//mail("sukumar@herakle.com,mark@greatcollections.com,ian@greatcollections.com,shreeram@herakle.com","Alert:MYSQL server gone away @".date("F j, Y, g:i a"),$msg);
				}
	       	 }
		 
		   	 echo file_get_contents(DIR_SERVER_ROOT.'templates/gc/' . 'error.html');
		 exit; 
	       }   
		if (!empty($dbcharset) AND !empty($dbcollate))
		{
			$this->functions['query']($link, "SET CHARACTER SET $dbcharset");
			$this->functions['query']($link, "SET NAMES $dbcharset");
                        $this->functions['query']($link, "SET COLLATION_DATABASE $dbcollate");
                        $this->functions['query']($link, "SET COLLATION_CONNECTION $dbcollate");
			$this->functions['query']($link, "SET character_set_results = '$dbcharset', character_set_client = '$dbcharset', character_set_connection = '$dbcharset', character_set_database = '$dbcharset', character_set_server = '$dbcharset', character_set_system = '$dbcharset'");
		}

		return (!$connect) ? false : $link;
	}
	
        /*
	* Function to select the database with an associated mysql link identifier
	* 
	* @param       string        database name
	* @param       object        database link
	* 
        * @return      nothing
	*/
	function select_db_wrapper($database = '', $link = null)
	{
		return $this->functions['select_db']($link, $database);
	}
	
        /*
	* Function to perform a database specific query
	* 
	* @param       string        sql code
	* @param       bool          hide database errors? default false
	* @param       string        cache to filesystem filename
	* @param       string        script filename
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function query($string = '', $hideerrors = 0, $enablecache = null, $script = '', $line = '', $buffered = true)
	{
		
		global $pagestarttime, $querytime,$new_page;
		$string.=' /*'.$_SERVER['REQUEST_URI'].'*/';
		$this->enablecache = $enablecache;
		$this->query_count++;
		$dblink = preg_match('#(^|\s)SELECT\s#s', $string) ? 'connection_read' : 'connection_write';
                $qtimer = $this->timer();
		
		if(isset($_GET['log']))
		{
			$log_file="../cache/sqllog_pdf.txt";
			if(is_file($log_file) and $new_page==1)
			{
				unlink($log_file);
				$new_page++;
			}
                $qtimer = $this->timer(); 		 		
				$fp=fopen($log_file,"a+");		
				fwrite($fp,trim(preg_replace('/\s\s+/', ' ', $string))."\n");		
				fwrite($fp,$script."\n");		
				fwrite($fp,"Line number : ".$line."\n");
				$start = microtime(true);
		}
		
		if ($enablecache == null)
		{
			$query = $this->functions['query']($this->connection_link, $string, ($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT));
		}
		else
		{
			$query = $this->query_cache($string, $this->connection_link);
		}
		
		if ($this->errno() AND !$hideerrors)
		{
			 $this->dberror($string);
			 exit();
		}

		$qtime = $this->stop();
		$this->explain_query($string, $qtime, $script, $line);
		$querytime = $this->totaltime;
		if(isset($_GET['log']))
		{
			$end = microtime(true);
			fwrite($fp,"Time taken : ".round($end - $start,3)." Seconds \n\n");	 
		}
		$this->remove();
		
		return $query;
	
	}
	
        
        /*
	* Function to perform a database specific query and immediately returns the associated array/results
	* 
	* @param       string        sql code
	* @param       bool          hide database errors? default false
	* @param       string        cache to filesystem filename
	* @param       string        script filename
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function query_fetch($string, $hideerrors = 0, $enablecache = null, $script = '', $line = '', $buffered = true)
	{
		global $pagestarttime, $querytime;
		
		$qtimer = $this->timer();
		$query = $this->functions['query']($this->connection_link, $string, ($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT));
		
		if ($this->errno() AND !$hideerrors)
		{
			 $this->dberror($string);
			 exit();
		}
                
		$qtime = $this->stop();
		$this->explain_query($string, $qtime, $script, $line);
		$querytime += $this->totaltime;
		$this->remove();
		$this->query_count++;
		
		return $this->fetch_array($query);
	}

	/*
	* Function to perform a database fetch array
	* 
	* @param       string        sql code
	* @param       string        sql result type
	* 
        * @return      nothing
	*/
        function fetch_array(&$query, $type = DB_BOTH)
	{
		return @$this->functions['fetch_array']($query, $this->types[$type]);
	}
	
        /**
	* Function to perform a database fetch object
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
	function fetch_object(&$query)
        {
                return $this->functions['fetch_object']($query);
        }
	
	/*
	* Function to perform a database fetch associative array
	* 
	* @param       string        sql code
	* @param       string        sql result type
	* 
        * @return      nothing
	*/
	function fetch_assoc(&$query, $type = DB_ASSOC)
	{
		return @$this->functions['fetch_array']($query, $this->types[$type]);
	}

        /*
	* Function to perform a database fetch row
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
	function fetch_row(&$query)
	{
		return @$this->functions['fetch_row']($query);
	}
	
	/**
	* Function to fetch the total number of affected rows for the connection
	* 
        * @return      nothing
	*/
	function affected_rows()
	{
		return $this->functions['affected_rows']($this->connection_link);
	}
        
        /*
	* Function to fetch a field value result from a table
	* 
	* @param       string       table name
	* @param       string       sql condition code
	* @param       string       field name
	*
        * @return      nothing
	*/
	function fetch_field($tbl = '', $condition = '', $field = '')
	{
		$result = $this->query("
                        SELECT " . $this->escape_string($field) . "
                        FROM " . $this->escape_string($tbl) . "
                        WHERE " . $condition);
		$row = ($this->fetch_array($result));
		
		return $row["$field"];
	}
        
        /*
	* Function to perform a database num rows
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
        function num_rows($query = '')
	{
		if ($this->enablecache)
		{
			return count($query);
		}
		else
		{
			return $this->functions['num_rows']($query);
		}
			
	}
	
	/*
	* Function to perform a database num fields
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
        function num_fields($query = '')
	{
		return $this->functions['num_fields']($query);
	}
	
	/*
	* Function to perform a database field name
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
        function field_name($query = '')
	{
		return $this->functions['field_name']($query);
	}
	
	/**
	* Function to fetch the last insert id for the database connection
	* 
        * @return      nothing
	*/
	function insert_id()
	{
		return $this->functions['insert_id']($this->connection_link);
	}
	
	/**
	* Function to close the database connection
	* 
        * @return      nothing
	*/
	function close()
	{
		@$this->functions['close']($this->connection_link);
	}
	
	/**
	* Function to mimic database error handling
	* 
        * @return      nothing
	*/
	function error()
	{
		$this->error = ($this->connection_link === null) ? '' : $this->functions['error']($this->connection_link);
		return $this->error;
	}
	
	/**
	* Function to mimic database error number handling
	* 
        * @return      nothing
	*/
	function errno()
	{
		$this->errno = ($this->connection_link === null) ? 0 : $this->functions['errno']($this->connection_link);
		return $this->errno;	
	}
        
        /**
	* Function to execute xxxx_real_escape_string()
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
	function escape_string($query = '')
	{
		return $this->functions['real_escape_string']($this->connection_write, $query);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
