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

/**
* Core Cron Job and automated task functions for ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/**
* Function to log actions created by specific cron job tasks in the system
*
* @param	string		description of task
* @param	array		array holding the next cron job item details
*
* @return	nothing
*/
function log_cron_action($description, $nextitem)
{
	global $ilance, $myapi;
	
	if ($nextitem['loglevel'])
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "cronlog
			(varname, dateline, description)
			VALUES(
			'" . $ilance->db->escape_string($nextitem['varname']) . "',
			" . time() . ",
			'" . $ilance->db->escape_string($description) . "')
		");
	}
}

/**
* Fetches the next run time for a particular cron job
*
* @param	string		date array
* @param	integer		hour
* @param	integer		minute
*
* @return	array		Return single date array
*/
function fetch_cron_next_run($data, $hour = -2, $minute = -2)
{
	if ($hour == -2)
	{
		$hour = intval(date('H', TIMESTAMPNOW));
	}
	if ($minute == -2)
	{
		$minute = intval(date('i', TIMESTAMPNOW));
	}
	
	$data['minute'] = unserialize($data['minute']);
	if ($data['hour'] == -1 AND $data['minute'][0] == -1)
	{
		$newdata['hour'] = $hour;
		$newdata['minute'] = $minute + 1;
	}
	else if ($data['hour'] == -1 AND $data['minute'][0] != -1)
	{
		$newdata['hour'] = $hour;
		$nextminute = fetch_cron_next_minute($data['minute'], $minute);
		if ($nextminute === false)
		{
			++$newdata['hour'];
			$nextminute = $data['minute'][0];
		}
		$newdata['minute'] = $nextminute;
	}
	else if ($data['hour'] != -1 AND $data['minute'][0] == -1)
	{
		if ($data['hour'] < $hour)
		{
			$newdata['hour'] = -1;
			$newdata['minute'] = -1;
		}
		else if ($data['hour'] == $hour)
		{
			$newdata['hour'] = $data['hour'];
			$newdata['minute'] = $minute + 1;
		}
		else
		{
			$newdata['hour'] = $data['hour'];
			$newdata['minute'] = 0;
		}
	}
	else if ($data['hour'] != -1 AND $data['minute'][0] != -1)
	{
		$nextminute = fetch_cron_next_minute($data['minute'], $minute);
		if ($data['hour'] < $hour OR ($data['hour'] == $hour AND $nextminute === false))
		{
			$newdata['hour'] = -1;
			$newdata['minute'] = -1;
		}
		else
		{
			$newdata['hour'] = $data['hour'];
			$newdata['minute'] = $nextminute;
		}
	}
	
	return $newdata;
}

/**
* Fetches the next minute for a particular cron job
*
* @param	array		minute array
* @param	integer		minute
*
* @return	boolean
*/
function fetch_cron_next_minute($minutedata, $minute)
{
	foreach ($minutedata AS $nextminute)
	{
		if ($nextminute > $minute)
		{
			return $nextminute;
		}
	}
	
	return false;
}

/**
* Function to determine the next run time for a particular task within the ILance automation system
*
* @param	integer         cron id
* @param        array           cron data array
*
* @return	integer         returns next run time (or 0)
*/
function construct_cron_item($cronid, $data = '')
{
	global $ilance, $myapi;
	
	if (!is_array($data))
	{
		$data = $ilance->db->query_fetch("
			SELECT *
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . intval($cronid) . "'
		");
	}
	
	$minutenow = intval(date('i', TIMESTAMPNOW));
	$hournow = intval(date('H', TIMESTAMPNOW));
	$daynow = intval(date('d', TIMESTAMPNOW));
	$monthnow = intval(date('m', TIMESTAMPNOW));
	$yearnow = intval(date('Y', TIMESTAMPNOW));
	$weekdaynow = intval(date('w', TIMESTAMPNOW));
	if ($data['weekday'] == -1)
	{
		if ($data['day'] == -1)
		{
			$firstday = $daynow;
			$secondday = $daynow + 1;
		}
		else
		{
			$firstday = $data['day'];
			$secondday = $data['day'] + date('t', TIMESTAMPNOW);
		}
	}
	else
	{
		$firstday = $daynow + ($data['weekday'] - $weekdaynow);
		$secondday = $firstday + 7;
	}
	if ($firstday < $daynow)
	{
		$firstday = $secondday;
	}
	if ($firstday == $daynow)
	{
		$todaytime = fetch_cron_next_run($data);
		if ($todaytime['hour'] == -1 AND $todaytime['minute'] == -1)
		{
			$data['day'] = $secondday;
			$newtime = fetch_cron_next_run($data, 0, -1);
			$data['hour'] = $newtime['hour'];
			$data['minute'] = $newtime['minute'];
		}
		else
		{
			$data['day'] = $firstday;
			$data['hour'] = $todaytime['hour'];
			$data['minute'] = $todaytime['minute'];
		}
	}
	else
	{
		$data['day'] = $firstday;
		$newtime = fetch_cron_next_run($data, 0, -1);
		$data['hour'] = $newtime['hour'];
		$data['minute'] = $newtime['minute'];
	}
	$nextrun = mktime($data['hour'], $data['minute'], 0, $monthnow, $data['day'], $yearnow);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "cron
		SET nextrun = " . $nextrun . "
		WHERE cronid = " . intval($cronid) . "
			AND nextrun = " . $data['nextrun'] . "
	");
	$norun = ($ilance->db->affected_rows() > 0);
	build_cron_next_runtime($nextrun);
	
	return iif($norun, $nextrun, 0);
}

/**
* Function to build a cron job's next execution time
*
* @param	integer         cron id
*
* @return	array           array with next run information
*/
function build_cron_next_runtime($nextrun = '')
{
	global $ilance, $myapi;
	
	if (!$nextcron = $ilance->db->query_fetch("SELECT MIN(nextrun) AS nextrun FROM " . DB_PREFIX . "cron AS cron"))
	{
		$nextcron['nextrun'] = TIMESTAMPNOW + 60 * 60;
	}
	
	return $nextrun;
}

/**
* Function to execute a task within the cron job system
*
* @param	integer         cron id (default null)
*
* @return	nothing
*/
function execute_task($cronid = null)
{
	global $ilance, $myapi, $phrase, $ilconfig, $show;
	
	if ($cronid = intval($cronid))
	{
		$nextitem = $ilance->db->query_fetch("
			SELECT *
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . $cronid . "'
		");
	}
	else
	{
		$nextitems = $ilance->db->query("
			SELECT cron.*
			FROM " . DB_PREFIX . "cron AS cron
			WHERE cron.nextrun <= " . TIMESTAMPNOW . " AND cron.active = 1
			ORDER BY cron.nextrun
		");
	}
	if (isset($nextitem))
	{
		if ($nextrun = construct_cron_item($nextitem['cronid'], $nextitem))
		{
			if (!empty($nextitem['filename']) AND file_exists(DIR_CRON . $nextitem['filename']))
			{
				include_once(DIR_CRON . $nextitem['filename']);
			}
		}	
	}
	else if (isset($nextitems))
	{
		while ($nextitem = $ilance->db->fetch_array($nextitems))
		{
			if ($nextrun = construct_cron_item($nextitem['cronid'], $nextitem))
			{
				if (!empty($nextitem['filename']) AND file_exists(DIR_CRON . $nextitem['filename']))
				{
					include_once(DIR_CRON . $nextitem['filename']);
				}
			}	
		}
	}
	else
	{
		build_cron_next_runtime();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>