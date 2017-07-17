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
define('DB_ASSOC', 1);
define('DB_NUM', 2);
define('DB_BOTH', 3);
/**
* ILance database class to perform the majority of database caching in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance_database
{
	/**
	* The ILance registry object
	*
	* @var	    $registry
	*/
        var $registry = null;
        
        /**
	* Debug Mode
	*
	* @var	    $debug
	*/
	var $debug = false;
        
        /**
	* Email Error Reporting
	*
	* @var	    $email_reporting
	*/
        var $error_reporting = true;
	var $email_reporting = true;
        
        /**
	* Timer Variables
	*
	* @var	    $start
	* @var      $end
	* @var      $totaltime
	* @var      $formatted
	*/
	var $start = null;
	var $end = null;
	var $totaltime = null;
	var $formatted = null;
        
        /**
	* Database Connection Parameters
	*
	* @var	    $multiserver
	* @var      $database
	* @var      $explain
	* @var      $querylist
	* @var      $query_count
	* @var      $connection_write
	* @var      $connection_read
	* @var      $connection_link
	*/
        var $multiserver = false;
	var $database = null;
        var $explain = null;
        var $querylist = array();
        var $query_count = 0;
	var $connection_write = null;
	var $connection_read = null;
	var $connection_link = null;
	var $error = '';
	var $errno = '';
	/**
	* Constructor
	*/
	function ilance_database()
	{
		// #### prepare our default dbms escape string #################
                if (isset($this->functions) AND function_exists($this->functions['real_escape_string']))
		{
			$this->functions['escape_string'] = $this->functions['real_escape_string'];
		}
	}
	
	/**
	* Initialize database connection
	*
	* Connects to a database server
	* 
	* @return	boolean
	*/
	function connect()
	{
		$this->connection_write = $this->db_connect();
		$this->multiserver = false;
		$this->connection_read =& $this->connection_write;
		$this->database = DB_DATABASE;
		if ($this->connection_write)
		{
			$this->select_db($this->database);
		}
	}
	
	/**
	* Selects a database for usage
	*
	* @param	string	 name of the database to use
	*
	* @return	boolean
	*/
        function select_db($database = '')
	{
		if ($database != '')
		{
			$this->database = $database;
		}
		if ($check_write = @$this->select_db_wrapper($this->database, $this->connection_write))
		{
			$this->connection_link =& $this->connection_write;
			return true;
		}
		else
		{
			$this->connection_link =& $this->connection_write;
			$this->dberror('Cannot select database ' . $this->database . ' for usage');
			return false;
		}
	}
	/**
	* Function to perform a database explain query
	* 
	* @param       string        sql code
	* @param       integer       sql query time
	* @param       string        script name
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function explain_query($string = '', $qtime = '', $script = '', $line = '')
	{
		if (defined('DB_EXPLAIN') AND DB_EXPLAIN)
		{
			if (mb_stristr($string, 'SELECT'))
			{
				$query = $this->functions['query']("EXPLAIN $string", $this->connection_link);
				
				$this->explain .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\">\n".
				"<tr>\n".
				"<td colspan=\"8\" bgcolor=\"orange\"><strong>#".$this->query_count." - Select Query</strong></td>\n".
				"</tr>\n".
				"<tr>\n".
				"<td colspan=\"8\" bgcolor=\"#fefefe\"><span style=\"font-family: Courier; font-size: 14px;\">Script: ".$script.", Line: ".$line."</span></td>\n".
				"</tr>\n".
				"<tr>\n".
				"<td colspan=\"8\" bgcolor=\"#fefefe\"><span style=\"font-family: Courier; font-size: 14px;\">".$string."</span></td>\n".
				"</tr>\n".
				"<tr bgcolor=\"#efefef\">\n".
				"<td><strong>table</strong></td>\n".
				"<td><strong>type</strong></td>\n".
				"<td><strong>possible_keys</strong></td>\n".
				"<td><strong>key</strong></td>\n".
				"<td><strong>key_len</strong></td>\n".
				"<td><strong>ref</strong></td>\n".
				"<td><strong>rows</strong></td>\n".
				"<td><strong>Extra</strong></td>\n".
				"</tr>\n";
	
				while ($table = $this->functions['fetch_array']($query))
				{
					$this->explain .=
					"<tr bgcolor=\"#ffffff\">\n".
					"<td>".$table['table']."</td>\n".
					"<td>".$table['type']."</td>\n".
					"<td>".$table['possible_keys']."</td>\n".
					"<td>".$table['key']."</td>\n".
					"<td>".$table['key_len']."</td>\n".
					"<td>".$table['ref']."</td>\n".
					"<td>".$table['rows']."</td>\n".
					"<td>".$table['Extra']."</td>\n".
					"</tr>\n";
				}
				
				$this->explain .=
				"<tr>\n".
				"<td colspan=\"8\" bgcolor=\"#ffffff\">Query Time: ".$qtime."</td>\n".
				"</tr>\n".
				"</table>\n".
				"<br />\n";
			}
			else if (mb_stristr($string, 'DELETE'))
			{
				$this->explain .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\">\n".
				"<tr>\n".
				"<td bgcolor=\"#ff0000\"><font color=\"#ffffff\"><strong>#".$this->query_count." - Delete Query</strong></font></td>\n".
				"</tr>\n".
				"<tr bgcolor=\"#fefefe\">\n".
				"<td><span style=\"font-family: Courier; font-size: 14px;\">Script: ".$script.", Line: ".$line."</span></td>\n".
				"</tr>\n".
				"<tr bgcolor=\"#fefefe\">\n".
				"<td><span style=\"font-family: Courier; font-size: 14px;\">".$string."</span></td>\n".
				"</tr>\n".
				"<tr>\n".
				"<td bgcolor=\"#ffffff\">Query Time: ".$qtime."</td>\n".
				"</tr>\n".
				"</table>\n".
				"</table>\n".
				"<br />\n";
			}
			else
			{
				$this->explain .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\">\n".
				"<tr>\n".
				"<td bgcolor=\"#ffee00\"><strong>#".$this->query_count." - Write Query</strong></td>\n".
				"</tr>\n".
				"<tr bgcolor=\"#fefefe\">\n".
				"<td><span style=\"font-family: Courier; font-size: 14px;\">Script: ".$script.", Line: ".$line."</span></td>\n".
				"</tr>\n".
				"<tr bgcolor=\"#fefefe\">\n".
				"<td><span style=\"font-family: Courier; font-size: 14px;\">".$string."</span></td>\n".
				"</tr>\n".
				"<tr>\n".
				"<td bgcolor=\"#ffffff\">Query Time: ".$qtime."</td>\n".
				"</tr>\n".
				"</table>\n".
				"</table>\n".
				"<br />\n";
			}
			
			$this->querylist[$this->query_count]['query'] = $string;
			$this->querylist[$this->query_count]['time'] = $qtime;	
		}
	}
	/**
	* Function to perform database error handling
	* 
        * @return      nothing
	*/
	function dberror($string = '',$script = '', $line = '')
	{
		define('NO_DB', true);
                
		global $ilance, $message, $ilconfig, $site_email, $site_name, $myapi, $phrase, $ilpage, $headinclude;
		
		
		if ($this->error_reporting)
		{
		$name =($_SESSION['ilancedata']['user']['userid']!=0)?fetch_user("username",$_SESSION['ilancedata']['user']['userid']):'Guest';
		$error_msg.='The User having database error'."\r\n\r\n";
		$error_msg.='Username:'.$name."\r\n\r\n";
		$error_msg.='Referrer:http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . print_hidden_fields(true, array(), true, '', '', true, false)."\r\n\r\n";
		$error_msg.='File Details:'.$script.",".$line."\r\n\r\n";
		$error_msg.='Query:'.$string."\r\n\r\n";
                $this->log_or_email_error($error_msg,$_SERVER['REQUEST_URI']);	
                $message = "We're currently rebooting our database. We'll be back in a minute. If you are continuing to have trouble accessing our website, please call us at        1.800.442.6467.";
                
                print_notice("Our Database is Rebooting - We'll be Back in a Minute!",handle_input_keywords($message), $ilpage['main'], $phrase['_main_menu'],'','1');
						
						
}
	}
function log_or_email_error($error_msg,$script)
{
    global $ilance,$ilconfig;
$ilance->db->query("INSERT INTO  ".DB_PREFIX."error_dump (referrer , error_msg,error_date,error_ipaddress) VALUES ('".$script."',  '".$ilance->db->escape_string($error_msg)."',  '".DATETIME24H."',  '".IPADDRESS."')");
    $query1="SELECT * FROM ".DB_PREFIX."error_dump WHERE  date(error_date) = '".DATETODAY."' AND referrer = '".$script."'";
    $emailerror=$ilance->db->query($query1);
    if($emailerr=$ilance->db->num_rows($emailerror)<=25)
    {
		/*$ilance->email = construct_dm_object('email', $ilance);
		$ilance->email->mail = $ilconfig['globalserversettings_iremail'];
		$ilance->email->get('database_error');
		$ilance->email->set(array('{{database}}' => $error_msg,));
		$ilance->email->send();*/
		
		$ilance->email = construct_dm_object('email', $ilance);
		$ilance->email->mail = $ilconfig['globalserversettings_testemail'];
		$ilance->email->get('database_error');
		$ilance->email->set(array('{{database}}' => $error_msg,));
		$ilance->email->send();
           
            
    }
}
	/**
	* Function to determine if a field within a table exists
	* 
	* @param       string       field name
	* @param       string       table name
	*
        * @return      boolean      Returns false on no field existing, true on field existing
	*/
        function field_exists($field = '', $table = '')
        {
                $exists = false;
                $columns = $this->query("SHOW COLUMNS FROM $table");
                while ($c = $this->fetch_assoc($columns))
                {
                        if ($c['Field'] == $field)
                        {
                                $exists = true;
                                break;
                        }
                }
                
                return $exists;
        }
	
	/**
	* Function to determine if a database table exists based on the currently selected database
	* 
	* @param       string       table name
	*
	* @return      boolean      Returns false on no table existing, true on table existing
	*/
	function table_exists($table = '')
	{		
		$res = $this->query("SHOW TABLES LIKE '$table'");
		if ($this->num_rows($res) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
        /**
        * Function to determine if a field within a table exists, and if not, to automatically add the necessary field column details
        * 
        * @param       string       database table name
        * @param       string       table field name to add (if does not already exist)
        * @param       string       table field attributes to process (ie: VARCHAR(250) NOT NULL)
        * @param       string       table field name that we'll add our new field name after (ie: AFTER `title`)
        *
        * @return      boolean      Returns valid sql string if added, blank string if already exists
        */
        function add_field_if_not_exist($table = '', $column = '', $attributes = '', $addaftercolumn = '', $doquery = true)
        {
                $exists = false;
                $sql = '';
                
                $columns = $this->query("SHOW COLUMNS FROM $table");
                while ($c = $this->fetch_assoc($columns))
                {
                        if (isset($c['Field']) AND !empty($c['Field']) AND $c['Field'] == $column)
                        {
                                $exists = true;
                                break;
                        }
                }
                
                if ($exists == false)
                {
                        if ($doquery)
                        {
                                $sql = "ALTER TABLE `$table` ADD `$column` $attributes $addaftercolumn";
                                $this->query($sql);
                        }
                        else
                        {
                                $sql = "ALTER TABLE `$table` ADD `$column` $attributes $addaftercolumn";
                        }
                }
                
                return $sql;
        }
	/**
	* Timer function
	* 
        * @return      nothing
	*/
	function timer()
	{
		$this->add();
	}
	/**
	* Timer add function
	* 
        * @return      nothing
	*/
	function add()
	{
		if (!$this->start) 
		{
			$mtime1 = explode(" ", microtime());
			$this->start = $mtime1[1] + $mtime1[0];
		}
	}
	/**
	* Get Time from timer() function
	* 
        * @return      nothing
	*/
	function gettime()
	{
		if ($this->end)
		{ // timer has been stopped
			return $this->totaltime;
		}
		else if ($this->start AND !$this->end)
		{ // timer is still going
			$mtime2 = explode(" ", microtime());
			$currenttime = $mtime2[1] + $mtime2[0];
			$totaltime = $currenttime - $this->start;
			return $this->format($totaltime);
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Stop time from timer() function
	* 
        * @return      nothing
	*/
	function stop()
	{
		if ($this->start)
		{
			$mtime2 = explode(" ", microtime());
			$this->end = $mtime2[1] + $mtime2[0];
			$totaltime = $this->end - $this->start;
			$this->totaltime = $totaltime;
			$this->formatted = $this->format($totaltime);
			return $this->formatted;
		}
	}
	
	/**
	* Remove time from timer() function
	* 
        * @return      nothing
	*/
	function remove()
	{
		$this->name = $this->start = $this->end = $this->totaltime = $this->formatted = '';
	}
	
	/**
	* Format time from timer() function
	* 
        * @return      nothing
	*/
	function format($string = '')
	{
		return number_format($string, 7);
	}
	
	function query_cache($sql, $linkidentifier, $timeout = 60)
	{
		global $ilance;
		
		$cache = $ilance->cache->fetch($sql);
		if ($cache == false)
		{
			$result = ($linkidentifier != false)
				? $this->functions['query']($linkidentifier, $sql, MYSQLI_STORE_RESULT)
				: $this->functions['query']($sql);
				
			if ($ilance->db->num_rows($result) > 0)
			{
				while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
				{
					$cache[] = $res;
				}
				
				$ilance->cache->store($sql, $cache, $timeout);
			}
		}
		
		return $cache;
	}
}
class ilance_nocache extends ilance_database
{
	/**
	* Fetch items from cache
	* 
        * @return      
	*/
	function fetch($key)
	{
		return false;
	}
	
	/**
	* Store items in cache
	* 
        * @return      
	*/
	function store($key, $data, $ttl = 60)
	{
		return false;
	}
	
	/**
	* Delete items in cache
	* 
        * @return      
	*/
	function delete($key)
	{
		return false;
	}
};
/**
* ILance file system cache class to perform the majority of database caching in ILance
*
* @package      iLance Filecache
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance_filecache extends ilance_database
{
	/**
	* Fetch items from cache
	* 
        * @return      
	*/
	function fetch($key)
	{
		$filename = $this->getfilename(md5(DB_CACHE_PREFIX . $key));
		
		if (!file_exists($filename))
		{
			return false;
		}
		
		$h = fopen($filename, 'r');
		if (!$h)
		{
			return false;
		}
		
		// setting a shared lock
		flock($h, LOCK_SH);
		$data = file_get_contents($filename);
		fclose($h);
		
		$data = unserialize($data);
		if (!$data)
		{
			// if unserializing somehow didn't work out, we'll delete the file
			unlink($filename);
			return false;
		}
		
		if (time() > $data[0])
		{
			// unlinking when the file was expired
			unlink($filename);
			return false;
		}
		
		if (is_serialized($data[1]))
		{
			return unserialize($data[1]);
		}
		
		return $data[1];
	}
	
	/**
	* Store items in cache
	* 
        * @return      
	*/
	function store($key, $data, $ttl = 60)
	{
		// opening the file in read/write mode
		$h = fopen($this->getfilename(md5(DB_CACHE_PREFIX . $key)), 'a+');
		if (!$h)
		{
			throw new Exception('Could not write to cache');
		}
		
		// exclusive lock, will get released when the file is closed
		flock($h, LOCK_EX);
		
		// go to the start of the file
		fseek($h, 0);
		
		// truncate the file
		ftruncate($h, 0);
		
		// serializing along with the TTL
		//$data = serialize(array(time() + $ttl, $data));
		$data = serialize(array(time() + $ttl, serialize($data)));
		
		if (fwrite($h, $data) === false)
		{
			throw new Exception('Could not write to cache');
		}
		
		fclose($h);
	}
	
	/**
	* Delete items in cache
	* 
        * @return      
	*/
	function delete($key)
	{
		$filename = $this->getfilename(md5(DB_CACHE_PREFIX . $key));
		
		if (file_exists($filename))
		{
			return unlink($filename);
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Get local cache filename on server
	* 
        * @return      
	*/
	function getfilename($key)
	{
		return DIR_SERVER_ROOT . DIR_TMP_NAME . '/datastore/filecache_' . $key;
	}
}
/**
* ILance APC class to perform the majority of database caching in ILance
*
* @package      iLance APC Cache
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance_apc extends ilance_database
{
	/**
	* Fetch items from cache
	* 
        * @return      
	*/
	function fetch($key)
	{
		return apc_fetch(DB_CACHE_PREFIX . $key);
	}
	
	/**
	* Store items in cache
	* 
        * @return      
	*/
	function store($key, $data, $ttl = 60)
	{
		return apc_store(DB_CACHE_PREFIX . $key, $data, $ttl);
	}
	
	/**
	* Delete items in cache
	* 
        * @return      
	*/
	function delete($key)
	{
		return apc_delete(DB_CACHE_PREFIX . $key);
	}
}
	
/**
* ILance Memcached class to perform the majority of database memory caching in ILance
*
* @package      iLance Memcached
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance_memcached extends ilance_database
{
	public $connection;
	/**
	* Constructor
	*/
	function __construct()
	{
		global $memcacheserver;
		
		$this->connection = new MemCache;
		
		foreach ($memcacheserver AS $servernumber => $serverinfo)
		{
			$this->connection->addServer(
				$serverinfo['server'],
				$serverinfo['port'],
				$serverinfo['persistent'],
				$serverinfo['weight'],
				$serverinfo['timeout'],
				$serverinfo['retry']
			);
		}
		
		$this->memcache_connected = true;
	}
	/**
	* Fetch items from cache
	* 
        * @return      
	*/
	function fetch($key,$reload=false)
	{
		global $ilance;
		if($this->connection->get(DB_CACHE_PREFIX . $key)==false or $reload==true)
		{
			$sql="SELECT value,TIMESTAMPDIFF(second,'".DATETIME24H."',expires_on) as ttl FROM " . DB_PREFIX . "memcached WHERE variable = '" . DB_CACHE_PREFIX . $key . "' and expires_on >= '".DATETIME24H."'";
			$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($result)>0)
			{
				while($line= $ilance->db->fetch_array($result))
				{
					$this->connection->set(DB_CACHE_PREFIX . $key, $line['value'], 0, $line['ttl']);
					return $line['value'];
				}
			}
			
		}else
		{
			return $this->connection->get(DB_CACHE_PREFIX . $key);
		}
		return false;

	}

	/*
	fetches the last time the variable is save to the database
	*/
	function fetch_saved_time($key)
	{
		global $ilance;
		$sql="SELECT DATE_FORMAT(update_on,'%D %M %Y %H:%i:%s') as update_on_formatted FROM " . DB_PREFIX . "memcached WHERE variable = '" . DB_CACHE_PREFIX . $key . "'";
			$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($result)>0)
			{
				while($line= $ilance->db->fetch_array($result))
				{
					return $line['update_on_formatted'];
				}
			}
		return null;

	}
	
	/**
	* Store items in cache
	* 
        * @return      
	*/
	function store($key, $data, $ttl = 60)
	{
		global $ilance;
		
		if($ttl==0)
			$sqlttl=365*24*60*60;
		else
			$sqlttl=$ttl;
		$sql="insert into " . DB_PREFIX . "memcached  (variable,value,expires_on,update_on) values ('".DB_CACHE_PREFIX . $key."','".$ilance->db->escape_string($data)."',date_add('".DATETIME24H."', INTERVAL '".$sqlttl."' second ),'".DATETIME24H."')
		ON DUPLICATE KEY UPDATE value='".$ilance->db->escape_string($data)."',expires_on=date_add('".DATETIME24H."', INTERVAL '".$sqlttl."' second ),update_on='".DATETIME24H."'";
		$ilance->db->query($sql);

		$this->connection->set(DB_CACHE_PREFIX . $key, $data, 0, $ttl);
	}
	
	/**
	* Delete items in cache
	* 
        * @return      
	*/
	function delete($key)
	{
		return $this->connection->delete(DB_CACHE_PREFIX . $key);
	}
	
	/**
	* Close the memcache connection
	* 
        * @return      
	*/
	function close()
	{
		if ($this->memcache_connected)
		{
			$this->connection->close();
			$this->memcache_connected = false;
		}
	}
	
	/**
	* Print the memcache server status and statistics
	* 
	* Sample usage:
	* $memcache = new Memcache;
	* $memcache->addServer('memcache_host', 11211);
	* $this->stats($memcache->getStats());
	* 
        * @return      
	*/
	function stats($status)
	{
		$html = "<table border=\"1\">";
		$html .= "<tr><td>Memcache Server version:</td><td>$status[version]</td></tr>";
		$html .= "<tr><td>Process id of this server process </td><td>$status[pid]</td></tr>";
		$html .= "<tr><td>Number of seconds this server has been running </td><td>$status[uptime]</td></tr>";
		$html .= "<tr><td>Accumulated user time for this process </td><td>$status[rusage_user] seconds</td></tr>";
		$html .= "<tr><td>Accumulated system time for this process </td><td>$status[rusage_system] seconds</td></tr>";
		$html .= "<tr><td>Total number of items stored by this server ever since it started </td><td>$status[total_items]</td></tr>";
		$html .= "<tr><td>Number of open connections </td><td>$status[curr_connections]</td></tr>";
		$html .= "<tr><td>Total number of connections opened since the server started running </td><td>$status[total_connections]</td></tr>";
		$html .= "<tr><td>Number of connection structures allocated by the server </td><td>$status[connection_structures]</td></tr>";
		$html .= "<tr><td>Cumulative number of retrieval requests </td><td>$status[cmd_get]</td></tr>";
		$html .= "<tr><td> Cumulative number of storage requests </td><td>$status[cmd_set]</td></tr>";
	
		$percCacheHit = ((real)$status["get_hits"] / (real)$status["cmd_get"] * 100);
		$percCacheHit = round($percCacheHit, 3);
		$percCacheMiss = 100 - $percCacheHit;
	
		$html .= "<tr><td>Number of keys that have been requested and found present </td><td>$status[get_hits] ($percCacheHit%)</td></tr>";
		$html .= "<tr><td>Number of items that have been requested and not found </td><td>$status[get_misses] ($percCacheMiss%)</td></tr>";
	
		$MBRead = (real)$status["bytes_read"] / (1024 * 1024);
		$html .= "<tr><td>Total number of bytes read by this server from network </td><td>$MBRead Mega Bytes</td></tr>";
		
		$MBWrite = (real)$status["bytes_written"] / (1024 * 1024);
		$html .= "<tr><td>Total number of bytes sent by this server to network </td><td>$MBWrite Mega Bytes</td></tr>";
		
		$MBSize = (real)$status["limit_maxbytes"] / (1024 * 1024);
		$html .= "<tr><td>Number of bytes this server is allowed to use for storage.</td><td>$MBSize Mega Bytes</td></tr>";
		$html .= "<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>$status[evictions]</td></tr>";
		$html .= "</table>";
		
		return $html;
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>