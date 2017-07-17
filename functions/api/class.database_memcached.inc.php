<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1314
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
* ILance memcached class to perform the majority of database caching in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class database_memcached
{
	var $memcache = null;
	var $memcache_set = true;
	var $memcache_connected = false;
	var $store_result = false;

	/**
	* Constructor
	*/
	function database_memcached(&$registry, $cachetimeout = 1, $cachetodatabase = true)
	{
		$this->registry =& $registry;
		if (!class_exists('Memcache'))
		{
			echo 'Memcache is not installed';
			exit();
		}

		$this->memcache = new Memcache;
	}

	function connect()
	{
		if (!$this->memcache_connected)
		{
			if (!$this->memcache->connect(DB_MEMCACHE_SERVER, DB_MEMCACHE_PORT, DB_MEMCACHE_TIMEOUT))
			{
				echo 'Unable to connect to memcache server';
				exit();
			}
			
			$this->memcache_connected = true;
			
			return 1;
		}
		
		return 0;
	}

	function close()
	{
		if ($this->memcache_connected)
		{
			$this->memcache->close();
			$this->memcache_connected = false;
		}
	}

	function fetch($itemarray)
	{
		$this->connect();

		$this->memcache_set = false;

		if (is_array($itemarray))
		{
			foreach ($itemarray AS $item)
			{
				$this->do_fetch($item);
			}
		}

		$this->store_result = true;
		$this->memcache_set = true;

		$this->close();
	}

	function do_fetch($title)
	{
		$ptitle = $title;

		if (($data = $this->memcache->get($ptitle)) === false)
		{
			return false;
		}
		
		$this->register($title, $data);
		
		return true;
	}

	function register($title, $data)
	{
		if ($this->store_result === true)
		{
			$this->build($title, $data);
		}
	}
	
	function build($title, $data)
	{
		$ptitle = $title;
		$check = $this->connect();

		if ($this->memcache_set)
		{
			$this->memcache->set($ptitle, $data, MEMCACHE_COMPRESSED);
		}
		else
		{
			$this->memcache->add($ptitle, $data, MEMCACHE_COMPRESSED);
		}
		
		// if we caused the connection above, then close it
		if ($check == 1)
		{
			$this->close();
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>