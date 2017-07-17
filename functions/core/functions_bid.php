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
* Core Bid functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/**
* Function to determine if a user id for a particular project id is awarded
*
* @param	integer         project id
* @param        integer         user id
*
* @return	boolean         Returns true if awarded, false if not
*/
function is_awarded($projectid, $userid)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("
                SELECT user_id
                FROM ".DB_PREFIX."project_bids
                WHERE project_id = '".intval($projectid)."'
                        AND user_id = '".intval($userid)."'
                        AND bidstatus = 'awarded'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return 1;
        }
        return 0;
}

/**
* Function to fetch bid information to be stored in a array for usage
*
* @param	integer         bid id
* @param        string          return what? (measure, totalamount, totalamountinput)
* @param        string          bid amount type
* @param        integer         bid amount
* @param        integer         estimate
*
* @return	boolean         Returns true if awarded, false if not
*/
function fetch_bid_info($bidid = 0, $what, $bidamounttype = 'entire', $bidamount = 0, $estimate = 0)
{
	global $ilance, $myapi;
        
	if (isset($what))
	{
		if ($what == 'measure')
		{
			$ilance->auction = construct_object('api.auction');
			
			$sql = $ilance->db->query("
				SELECT bidamounttype
				FROM ".DB_PREFIX."project_bids
				WHERE bid_id = '".intval($bidid)."'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				$measure = $ilance->auction->construct_measure($res['bidamounttype']);
				return $measure;
			}
		}
		else if ($what == 'totalamount')
		{
			$sql = $ilance->db->query("
				SELECT bidamounttype, bidamount, estimate_days
				FROM ".DB_PREFIX."project_bids
				WHERE bid_id = '".intval($bidid)."'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				if ($res['bidamounttype'] == 'entire' OR $res['bidamounttype'] == 'lot' OR $res['bidamounttype'] == 'weight')
				{
					$total = $res['bidamount'];
				}
				else
				{
					$total = ($res['bidamount']*$res['estimate_days']);
				}
				return $total;
			}
		}
		else if ($what == 'totalamountinput' AND !empty($bidamounttype) AND !empty($bidamount) AND !empty($estimate))
		{
			if ($bidamounttype == 'entire' OR $bidamounttype == 'lot' OR $bidamounttype == 'weight')
			{
				$total = $bidamount;
			}
			else
			{
				$total = ($bidamount*$estimate);
			}
			return $total;
		}
		else
		{
			$canquery = array('user_id','bidamount','estimate_days','bidstatus','bidstate','bidamounttype','date_added');
			if (in_array($what, $canquery) AND $bidid > 0)
			{
				$sql = $ilance->db->query("
					SELECT $what
					FROM ".DB_PREFIX."project_bids
					WHERE bid_id = '".intval($bidid)."'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					return $res["$what"];
				}        
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>