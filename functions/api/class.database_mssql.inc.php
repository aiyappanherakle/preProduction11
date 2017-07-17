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
* MS-SQL database class to perform the majority of database related functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance_mssql extends ilance_database
{
        /*
	* MS-SQL Database Array Resource Types
	*
	* @var	    $types
	*/
        var $types = array(
		DB_NUM => MSSQL_NUM,
		DB_ASSOC => MSSQL_ASSOC,
		DB_BOTH => MSSQL_BOTH
	);
	
	/*
	* MS-SQL Database Interface Functions
	*
	* @var	    $functions
	*/
	var $functions = array(
		'select_db' => 'mssql_select_db',
		'pconnect' => 'mssql_pconnect',
		'connect' => 'mssql_connect',
		'query' => 'mssql_query',
		'query_unbuffered' => 'mssql_query',
		'fetch_row' => 'mssql_fetch_row',
		'fetch_object' => 'mssql_fetch_object',
		'fetch_array' => 'mssql_fetch_array',
		'fetch_field' => 'mssql_fetch_field',
		'free_result' => 'mssql_free_result',
		'data_seek' => 'mssql_data_seek',
		'error' => 'mssql_get_last_message',
		'errno' => 'mssql_get_last_message',
		'affected_rows' => 'mssql_rows_affected',
		'num_rows' => 'mssql_num_rows',
		'num_fields' => 'mssql_num_fields',
		'field_name' => 'mssql_field_name',
		'insert_id' => 'mssql_insert_id',
		'close' => 'mssql_close'
	);
	
        /*
	* Constructor
	*
	* @param	object	        ilance registry object
	* @param        integer         cache time out
	* @param        bool            cache results to database within cache table?
	*/
	function ilance_mssql(&$registry, $cachetimeout = 1, $cachetodatabase = true)
	{
                $this->registry =& $registry;
		parent::ilance_database($cachetimeout, $cachetodatabase);
		
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
		// Server in the this format: <computer>\<instance name> or <server>,<port> when using a non default port number
		$hostname = DB_SERVER;
                $username = DB_SERVER_USERNAME;
                $password = DB_SERVER_PASSWORD;
		
		$link = $this->functions['connect']($hostname, $username, $password);
                
		return (!$link) ? false : $link;
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
		return $this->functions['select_db']($database, $link);
	}
	
        /*
	* Function to perform a database specific query
	* 
	* @param       string        sql code
	* @param       bool          hide database errors? default false
	* @param       string        cache to filesystem filename
	* @param       string        script name
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function query($string = '', $hideerr = 0, $cache_to_file = null, $script = '', $line = '', $buffered = true)
	{
		global $pagestarttime, $querytime;
		
		$this->query_count++;
                
		$dblink = preg_match('#(^|\s)SELECT\s#s', $string) ? 'connection_read' : 'connection_write';
                $qtimer = $this->timer();
                
		$query = $this->functions['query']($string, $this->connection_link);
		if (!$this->connection_link)
		{
			 $this->dberror($string);
			 exit;
		}
                
		$qtime = $this->stop();
		if ($this->debug)
		{
			$this->explain_query($string, $qtime, $script, $line);
		}
                
		$querytime += $this->totaltime;
		$this->remove();
		
		return $query;
	}
	
        
        /*
	* Function to perform a database specific query and immediately returns the associated array/results
	* 
	* @param       string        sql code
	* @param       bool          hide database errors? default false
	* @param       string        cache to filesystem filename
	* @param       string        script name
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function query_fetch($string, $hideerrors = 0, $cache_to_file = null, $script = '', $line = '', $buffered = true)
	{
		global $pagestarttime, $querytime;
		
		$qtimer = $this->timer();
		$query = $this->functions['query']($string, $this->connection_link);
		if (!$this->connection_link)
		{
			 $this->dberror($string);
			 exit;
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
		return $this->functions['num_rows']($query);
	}
	
	/**
	* Function to fetch the last insert id for the database connection
	* 
        * @return      nothing
	*/
	function insert_id()
	{
		$query = $this->functions['query']("SELECT @@IDENTITY AS last_insert_id", $this->connection_link);
		$res = $this->fetch_array($query);
		
		return $res['last_insert_id'];
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
		$this->error = ($this->connection_link === null) ? '' : $this->functions['error']();
		return $this->error;
	}
	
	/**
	* Function to mimic database error number handling
	* 
        * @return      nothing
	*/
	function errno()
	{
		$this->errno = ($this->connection_link === null) ? '' : $this->functions['errno']();
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
		return $query;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>