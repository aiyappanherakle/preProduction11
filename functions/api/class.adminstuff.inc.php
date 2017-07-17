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
* AdminCP class to perform the majority of functions within the ILance Admin Control Panel
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class adminstuff
{
        /**
        * Function for inserting a new bid increment in the database.
        *
        * @param       string       increment from
        * @param       string       increment to
        * @param       string       increment amount
        * @param       integer      category id (optional)
        * @param       integer      display sort order in the admincp
        * @param       string       bid increment group name
        */
        function insert_bid_increment($from = 0, $to = 0, $amount = 0, $cid = 0, $sort = 0, $groupname = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "increments
                        (incrementid, groupname, increment_from, increment_to, amount, sort, cid)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($from) . "',
                        '" . $ilance->db->escape_string($to) . "',
                        '" . $ilance->db->escape_string($amount) . "',
                        '" . intval($sort) . "',
                        '" . intval($cid) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for inserting a new bid increment in the database.
        *
        * @param       integer      increment id (optional)
        * @param       string       increment from
        * @param       string       increment to
        * @param       string       increment amount
        * @param       integer      category id (optional)
        * @param       integer      display sort order in the admincp
        * @param       string       bid increment group name
        */
        function update_bid_increment($incrementid = 0, $from = 0, $to = 0, $amount = 0, $cid = 0, $sort = 0, $groupname = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "increments
                        SET groupname = '" . $ilance->db->escape_string($groupname) . "',
                        increment_from = '" . $ilance->db->escape_string($from) . "',
                        increment_to = '" . $ilance->db->escape_string($to) . "',
                        amount = '" . $ilance->db->escape_string($amount) . "',
                        sort = '" . intval($sort) . "'
                        WHERE incrementid = '" . intval($incrementid) . "'
                        LIMIT 1
                ");
        }
    
        /**
        * Function for inserting a new insertion group.
        *
        * @param       string       insertion group
        * @param       string       state
        * @param       string       description
        */
        function insert_insertion_group($groupname = '', $state = '', $description = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "insertion_groups
                        (groupid, groupname, description, state)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($description) . "',
                        '" . $ilance->db->escape_string($state) . "')
                ", 0, null, __FILE__, __LINE__);
        }
    
        /**
        * Function for inserting a new final value group.
        *
        * @param       string       final value group
        * @param       string       state
        * @param       string       description
        */
        function insert_fv_group($groupname = '', $state = '', $description = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "finalvalue_groups
                        (groupid, groupname, description, state)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($description) . "',
                        '" . $ilance->db->escape_string($state) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for inserting a new insertion fee.
        *
        * @param       string       insertion fee from
        * @param       string       insertion fee to
        * @param       string       insertion fee amount
        * @param       string       insertion group name
        * @param       integer      insertion sorting display order
        * @param       string       state
        */
        function insert_insertion_fee($from = 0, $to = 0, $amount = 0, $groupname = '', $sort = 0, $state = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "insertion_fees
                        (insertionid, groupname, insertion_from, insertion_to, amount, sort, state)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($from) . "',
                        '" . $ilance->db->escape_string($to) . "',
                        '" . $ilance->db->escape_string($amount) . "',
                        '" . intval($sort) . "',
                        '" . $ilance->db->escape_string($state) . "')
                ", 0, null, __FILE__, __LINE__);
        }
    
        /**
        * Function for inserting a new insertion fee.
        *
        * @param       string       insertion fee from
        * @param       string       insertion fee to
        * @param       string       insertion fee amount
        * @param       string       insertion group name
        * @param       integer      insertion sorting display order
        * @param       string       state
        */
        function insert_budget_range($title = '', $fieldname = '', $budgetfrom = 0, $budgetto = 0, $groupname = '', $insgroupname = '', $sort = 0)
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "budget
                        (budgetid, budgetgroup, title, fieldname, budgetfrom, budgetto, insertiongroup, sort)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($title) . "',
                        '" . $ilance->db->escape_string($fieldname) . "',
                        '" . $ilance->db->escape_string($budgetfrom) . "',
                        '" . $ilance->db->escape_string($budgetto) . "',
                        '" . $ilance->db->escape_string($insgroupname) . "',
                        '" . intval($sort) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for updating an existing budget range.
        *
        * @param       integer      budget id
        * @param       string       title
        * @param       string       budget from
        * @param       string       budget to
        * @param       integer      group id
        * @param       integer      sort display order
        */
        function update_budget_range($budgetid = 0, $title = '', $budgetfrom = 0, $budgetto = 0, $groupid = 0, $insertiongroupid = '-1', $sort = 10)
        {
                global $ilance, $myapi;
                
                $groupname = $ilance->db->fetch_field(DB_PREFIX . "budget_groups", "groupid = '" . intval($groupid) . "'", "groupname");
                $insgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($insertiongroupid) . "'", "groupname");
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "budget
                        SET budgetgroup = '" . $ilance->db->escape_string($groupname) . "',
                        insertiongroup = '" . $ilance->db->escape_string($insgroupname) . "',
                        title = '" . $ilance->db->escape_string($title) . "',
                        budgetfrom = '".$ilance->db->escape_string($budgetfrom) . "',
                        budgetto = '" . $ilance->db->escape_string($budgetto) . "',
                        sort = '" . intval($sort) . "'
                        WHERE budgetid = '" . intval($budgetid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for removing an existing budget group.
        *
        * @param       integer      group id
        */
        function remove_budget_group($groupid = 0)
        {
                global $ilance, $myapi;
                
                $groupid = intval($groupid);
                $groupname = trim($ilance->db->fetch_field(DB_PREFIX . "budget_groups", "groupid = '" . intval($groupid) . "'", "groupname"));
                
                // delete budget range group
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "budget_groups
                        WHERE groupid = '" . intval($groupid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                
                // delete ranges in this group
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "budget
                        WHERE budgetgroup = '" . $ilance->db->escape_string($groupname) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for removing an existing bid increment group.  This function will additionally deassociate any categories using this group.
        *
        * @param       integer      group id
        */
        function remove_increment_group($groupid = 0)
        {
                global $ilance, $myapi;
                
                $groupname = $ilance->db->fetch_field(DB_PREFIX . "increments_groups", "groupid = '" . intval($groupid) . "'", "groupname");
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "increments_groups
                        WHERE groupid = '" . intval($groupid) . "'
                ", 0, null, __FILE__, __LINE__);
                
                // delete ranges in this group
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "increments
                        WHERE groupname = '" . $ilance->db->escape_string($groupname) . "'
                ", 0, null, __FILE__, __LINE__);
                
                // from increment range group from any categories associated
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "categories
                        SET incrementgroup = ''
                        WHERE incrementgroup = '" . $ilance->db->escape_string($groupname) . "'
                                AND cattype = 'product'
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for removing an existing budget range.
        *
        * @param       integer      budget id
        */
        function remove_budget_range($budgetid = 0)
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "budget
                        WHERE budgetid = '" . intval($budgetid) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for inserting a new budget group.
        *
        * @param       string       group name
        * @param       string       description
        */
        function insert_budget_group($groupname = '', $description = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "budget_groups
                        (groupid, groupname, description)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($description) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function for inserting a new bid increment group.  Additionally, the groupname will be replaced with underscores if the string contains spaces.
        *
        * @param       string       group name
        * @param       string       description
        */
        function insert_increment_group($groupname = '', $description = '')
        {
                global $ilance, $myapi;
                
                $groupname = str_replace(' ', '_', $groupname);
                $groupname = mb_strtolower($groupname);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "increments_groups
                        (groupid, groupname, description)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($description) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function to update an existing budget group
        *
        * @param       integer      group id
        * @param       string       group name
        * @param       string       description
        */
        function update_budget_group($groupid = 0, $groupname = '', $description = '')
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT * FROM " . DB_PREFIX . "budget_groups
                        WHERE groupid = '" . intval($groupid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        // we will need to update the categories as well
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET budgetgroup = '" . $ilance->db->escape_string($groupname) . "'
                                WHERE budgetgroup = '" . $res['groupname'] . "'
                                    AND cattype = 'service'
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "budget_groups
                                SET groupname = '" . $ilance->db->escape_string($groupname) . "',
                                description = '" . $ilance->db->escape_string($description) . "'
                                WHERE groupid = '" . intval($groupid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                }
        }
        
        /**
        * Function to update an existing increment group
        *
        * @param       integer      group id
        * @param       string       group name
        * @param       string       description
        */
        function update_increment_group($groupid = 0, $newgroupname = '', $newdescription = '')
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT groupname
                        FROM " . DB_PREFIX . "increments_groups
                        WHERE groupid = '" . intval($groupid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        // update the category association
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET incrementgroup = '" . $ilance->db->escape_string($newgroupname) . "'
                                WHERE incrementgroup = '" . $ilance->db->escape_string($res['groupname']) . "'
                                    AND cattype = 'product'
                        ", 0, null, __FILE__, __LINE__);
                        
                        // update bid increment ranges association
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "increments
                                SET groupname = '" . $ilance->db->escape_string($newgroupname) . "'
                                WHERE groupname = '" . $ilance->db->escape_string($res['groupname']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        // finally update actual increment group
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "increments_groups
                                SET groupname = '" . $ilance->db->escape_string($newgroupname) . "',
                                description = '" . $ilance->db->escape_string($newdescription) . "'
                                WHERE groupid = '" . intval($groupid) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
    
        /**
        * Function to insert a new final value fee.
        *
        * @param       string       final value from
        * @param       string       final value to
        * @param       bool         is amount fixed?
        * @param       bool         is amount percentage?
        * @param       integer      display sorting order
        * @param       string       final value group name
        * @param       string       state
        */
        function insert_fv_fee($from = 0, $to = 0, $amountfixed = 0, $amountpercent = 0, $sort = 100, $groupname = '', $state = '')
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "finalvalue
                        (tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($groupname) . "',
                        '" . $ilance->db->escape_string($from) . "',
                        '" . $ilance->db->escape_string($to) . "',
                        '" . $ilance->db->escape_string($amountfixed) . "',
                        '" . $ilance->db->escape_string($amountpercent) . "',
                        '" . $ilance->db->escape_string($state) . "',
                        '" . intval($sort) . "')
                ", 0, null, __FILE__, __LINE__);
        }
    
        /**
        * Function to update an existing insertion fee.
        *
        * @param       string       insertion fee from
        * @param       string       insertion fee to
        * @param       string       insertion fee amount
        * @param       integer      insertion id
        * @param       integer      sort display order
        */
        function update_insertion_fee($from = 0, $to = 0, $amount = 0, $groupid = 0, $insertionid = '', $sort = 0)
        {
                global $ilance, $myapi;
                
                $groupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($groupid) . "'", "groupname");
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "insertion_fees
                        SET groupname = '" . $ilance->db->escape_string($groupname) . "',
                        insertion_from = '" . $ilance->db->escape_string($from) . "',
                        insertion_to = '" . $ilance->db->escape_string($to) . "',
                        amount = '" . $ilance->db->escape_string($amount) . "',
                        sort = '" . intval($sort) . "'
                        WHERE insertionid = '" . intval($insertionid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
    
        /**
        * Function to update an existing final value fee.
        *
        * @param       string       final value fee from
        * @param       string       final value fee to
        * @param       bool         is amount fixed?
        * @param       bool         is amount percentage?
        * @param       integer      final value group id
        * @param       integer      final value tier id
        * @param       integer      sort display order
        */
        function update_fv_fee($from = 0, $to = 0, $amountfixed = 0, $amountpercent = 0, $groupid = 0, $tierid = 0, $sort = 10)
        {
                global $ilance, $myapi;
                
                $groupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($groupid) . "'", "groupname");
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "finalvalue
                        SET groupname = '" . $ilance->db->escape_string($groupname) . "',
                        finalvalue_from = '" . $ilance->db->escape_string($from) . "',
                        finalvalue_to = '" . $ilance->db->escape_string($to) . "',
                        amountfixed = '" . $ilance->db->escape_string($amountfixed) . "',
                        amountpercent = '" . $ilance->db->escape_string($amountpercent) . "',
                        sort = '" . intval($sort) . "'
                        WHERE tierid = '" . intval($tierid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
    
        /**
        * Function to remove an existing bid increment.
        *
        * @param       integer      increment id
        */
        function remove_bid_increment($id = 0)
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "increments
                        WHERE incrementid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function to remove an existing insertion fee.
        *
        * @param       integer      insertion fee id
        */
        function remove_insertion_fee($id = 0)
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "insertion_fees
                        WHERE insertionid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
    
        /**
        * Function to remove an existing final value fee.
        *
        * @param       integer      final value fee id
        */
        function remove_fv_fee($id = 0)
        {
                global $ilance, $myapi;
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "finalvalue
                        WHERE tierid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function to remove an existing insertion group.
        *
        * @param       integer      insertion group id
        */
        function remove_insertion_group($id = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT * FROM " . DB_PREFIX . "insertion_groups
                        WHERE groupid = '".intval($id)."'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '".$result['groupname']."'
                                    AND state = '".$result['state']."'
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "insertion_groups
                                WHERE groupid = '".intval($id)."'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET insertiongroup = '0'
                                WHERE insertiongroup = '".$result['groupname']."'
                                    AND cattype = '".$result['state']."'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
    
        /**
        * Function to remove an existing final value group
        *
        * @param       integer      final value group id
        */
        function remove_fv_group($id = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT * FROM " . DB_PREFIX . "finalvalue_groups
                        WHERE groupid = '".intval($id)."'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "finalvalue
                                WHERE groupname = '".$result['groupname']."'
                                    AND state = '".$result['state']."'
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "finalvalue_groups
                                WHERE groupid = '".intval($id)."'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET finalvaluegroup = '0'
                                WHERE finalvaluegroup = '".$result['groupname']."'
                                    AND cattype = '".$result['state']."'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
    
        /**
        * Function to remove a registration question from the database.
        *
        * @param       integer      registration question id
        */
        function remove_registration_question($id = 0)
        {
                global $ilance, $myapi;
                
                // remove answers for this question
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "register_answers
                        WHERE questionid = '".intval($id)."'
                ", 0, null, __FILE__, __LINE__);
                
                // remove question
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "register_questions
                        WHERE questionid = '".intval($id)."'
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function to print a pulldown with insertion group values as the options.
        *
        * @param       integer      selected insertion group id
        * @param       bool         if true, will show "please select value" option
        * @param       string       category state
        *
        * @return      string       HTML representation of the pulldown selection menu
        */
        function print_insertion_group_pulldown($selected = '', $shownoneselected = 0, $state = '', $fieldname = 'groupid')
	{
                global $ilance, $myapi, $phrase;
                
		$sqlroles = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "insertion_groups 
                        WHERE state = '" . $ilance->db->escape_string($state) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
                        $html = '<select name="' . $fieldname . '" style="font-family: verdana">';
                        
			if ($shownoneselected)
			{
                                $html .= '<option value="-1">' . $phrase['_select'] . ' &darr;</option>';
			}
			while ($roles = $ilance->db->fetch_array($sqlroles))
			{
                                if (isset($selected) AND $selected == $roles['groupid'])
				{
					$html .= '<option value="' . $roles['groupid'] . '" selected="selected">' . stripslashes($roles['groupname']) . '</option>';
				}
				else 
				{
					$html .= '<option value="' . $roles['groupid'] . '">' . stripslashes($roles['groupname']) . '</option>';
				}
			}
                        
			$html .= '</select>';
		}
		else 
		{
			$html .= $phrase['_no_groups_to_select'];	
		}
                
		return $html;
	}
	
        /**
        * Function to print a pulldown with final value group values as the options.
        *
        * @param       string       selected value
        * @param       bool         if true, will show "please select value" option
        * @param       string       category state
        *
        * @return      string       HTML representation of the pulldown selection menu
        */
	function print_fv_group_pulldown($selected = '', $shownoneselected = '', $state = '')
	{
		global $ilance, $myapi, $phrase;
                
		$sqlroles = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "finalvalue_groups 
			WHERE state = '" . $ilance->db->escape_string($state) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			$html = '<select name="groupid" style="font-family: verdana">';
                        
			if (isset($shownoneselected) AND $shownoneselected)
			{
				$html .= '<option value="-1">' . $phrase['_select'] . ' &darr;</option>';
			}
			while ($roles = $ilance->db->fetch_array($sqlroles))
			{
				if (isset($selected) AND $selected == $roles['groupid'])
				{
					$html .= '<option value="' . $roles['groupid'] . '" selected="selected">' . stripslashes($roles['groupname']) . '</option>';
				}
				else 
				{
					$html .= '<option value="' . $roles['groupid'] . '">' . stripslashes($roles['groupname']) . '</option>';
				}
			}
			$html .= '</select>';
		}
		else 
		{
			$html .= $phrase['_no_groups_to_select'];
		}
                
		return $html;
	}
        
        /**
        * Function to print a pulldown with bid increment group values as the options.
        *
        * @param       string       selected value
        * @param       bool         if true, will show "please select value" option
        * @param       string       category state
        *
        * @return      string       HTML representation of the pulldown selection menu
        */
	function print_increment_group_pulldown($selected = '', $shownoneselected = '', $state = '')
	{
		global $ilance, $myapi, $phrase;
                
		$sqlroles = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "increments_groups 
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			$html = '<select name="groupid" style="font-family: verdana">';
			if (isset($shownoneselected) AND $shownoneselected)
			{
				$html .= '<option value="-1">Please select a bid increment group:</option>';
			}
			while ($roles = $ilance->db->fetch_array($sqlroles))
			{
				if (isset($selected) AND $selected == $roles['groupid'])
				{
					$html .= '<option value="' . $roles['groupid'] . '" selected="selected">' . stripslashes($roles['groupname']) . '</option>';
				}
				else 
				{
					$html .= '<option value="' . $roles['groupid'] . '">' . stripslashes($roles['groupname']) . '</option>';
				}
			}
			$html .= '</select>';
		}
		else 
		{
			$html = $phrase['_no_groups_to_select'];
		}
                
		return $html;
	}
	
        /**
        * Function to print a pulldown with budget group values as the options.
        *
        * @param       string       selected value
        * @param       bool         if true, will show "please select value" option
        *
        * @return      string       HTML representation of the pulldown selection menu
        */
	function print_budget_group_pulldown($selected = '', $shownoneselected = '')
	{
		global $ilance, $myapi, $phrase;
                
		$sqlroles = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "budget_groups
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			$html = '<select name="groupid" style="font-family: verdana">';
                        
			if (isset($shownoneselected) AND $shownoneselected)
			{
				$html .= '<option value="-1">' . $phrase['_select'] . ' &darr;</option>';
			}
			while ($roles = $ilance->db->fetch_array($sqlroles))
			{
				if (isset($selected) AND $selected == $roles['groupid'])
				{
					$html .= '<option value="' . $roles['groupid'] . '" selected="selected">' . stripslashes($roles['groupname']) . '</option>';
				}
				else 
				{
					$html .= '<option value="' . $roles['groupid'] . '">' . stripslashes($roles['groupname']) . '</option>';
				}
			}
                        
			$html .= '</select>';
		}
		else 
		{
			$html .= $phrase['_no_groups_to_select'];	
		}
                
		return $html;
	}

        /**
        * Function to print the default payment gateway within a pulldown menu.
        *
        * @param       string       config value
        * @param       string       config key
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function default_gateway_pulldown($value = '', $key = 'use_internal_gateway')
        {
                global $ilance, $myapi, $phrase;
                
                $writepulldown = '<select name="config[' . $key . ']" style="font-family: verdana">';
                
                $sql = $ilance->db->query("
                        SELECT groupname
                        FROM " . DB_PREFIX . "payment_groups
                        WHERE moduletype = 'gateway'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT a.value, a.configgroup
                                        FROM " . DB_PREFIX . "payment_configuration as a,
                                        " . DB_PREFIX . "payment_groups as b
                                        WHERE a.name = 'paymodulename'
                                            AND a.inputname != 'defaultgateway'
                                            AND b.moduletype = 'gateway'
                                            AND a.configgroup = '" . $res['groupname'] . "'
                                        GROUP BY a.configgroup
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        while ($res2 = $ilance->db->fetch_array($sql2))
                                        {
                                                $sql3 = $ilance->db->query("
                                                        SELECT value
                                                        FROM " . DB_PREFIX . "payment_configuration
                                                        WHERE name = 'use_internal_gateway'
                                                ", 0, null, __FILE__, __LINE__);
                                                $res3 = $ilance->db->fetch_array($sql3);
                                                
                                                $writepulldown .= '<option value="' . $res2['configgroup'] . '"';
                                                if ($value == $res2['configgroup'])
                                                {
                                                        $writepulldown .= ' selected="selected"';
                                                }
                                                $writepulldown .= '>' . $res2['value'] . '</option>';
                                        }
                                }
                        }
                        
                        if ($value == 'none')
                        {
                                $writepulldown .= '<option value="none" selected="selected">' . $phrase['_disable_credit_card_support'] . '</option>';
                        }
                        else
                        {
                                $writepulldown .= '<option value="none">' . $phrase['_disable_credit_card_support'] . '</option>';
                        }
                }
                
                $writepulldown .= '</select>';
                
                return $writepulldown;
        }
    
        /**
        * Function to print the payment modules configuration input template menus.
        *
        * @param       string       config group
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_input($configgroup = '', $returnurl = '')
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilpage, $ilconfig;
                
                $html = '';
                
                $sqlgrp = $ilance->db->query("
                        SELECT groupname, description, help
                        FROM " . DB_PREFIX . "payment_groups
                        WHERE parentgroupname = '" . $ilance->db->escape_string($configgroup) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlgrp) > 0)
                {
                        while ($resgrpties = $ilance->db->fetch_array($sqlgrp))
                        {
                                $html .= '
                                <form method="post" action="' . HTTPS_SERVER_ADMIN . $ilpage['settings'] . '" name="updatesettings" accept-charset="UTF-8" style="margin: 0px;">
                                <input type="hidden" name="cmd" value="paymodulesupdate" />
                                <input type="hidden" name="subcmd" value="_update-config-settings" />
                                <input type="hidden" name="return" value="' . $returnurl . '" />
                                <div class="block-wrapper">
                                        <div class="block">
                                                        <div class="block-top">
                                                                        <div class="block-right">
                                                                                        <div class="block-left"></div>
                                                                        </div>
                                                        </div>
                                                        <div class="block-header" onclick="return toggle(\'admincp_paymodule_' . $configgroup . '\')" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'\'"><span style="float:left; padding-right:7px; padding-top:3px"><span style="padding-right:5px"><img id="collapseimg_admincp_paymodule_' . $configgroup . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand{collapse[collapseimg_admincp_paymodule_' . $configgroup . ']}.gif" border="0" alt="" /></span></span>' . stripslashes($resgrpties['description']) . '</div>
                                                        <div class="block-content" id="collapseobj_admincp_paymodule_' . $configgroup . '" style="{collapse[collapseobj_admincp_paymodule_' . $configgroup . ']} padding:0px">
                                                                
                                                                
                                <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">
                                ';
                                
                                $html .= (!empty($resgrpties['help'])) ? '<tr class="alt2"><td colspan="2">' . stripslashes($resgrpties['help']) . '</td></tr>' : '';
                                $html .= '
                                <tr class="alt2">
                                        <td>' . $phrase['_description'] . '</td>
                                        <td align="right">' . $phrase['_value'] . '</td>
                                        <td>' . $phrase['_sort'] . '</td>
                                </tr>';
                                
                                $sql = $ilance->db->query("
                                        SELECT id, inputtype, inputcode, name, value, description, help, sort
                                        FROM " . DB_PREFIX . "payment_configuration
                                        WHERE configgroup = '" . $resgrpties['groupname'] . "'
                                        ORDER BY sort ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $rowcount = 0;
                                        while ($res = $ilance->db->fetch_array($sql))
                                        {
                                                $res['class'] = ($rowcount % 2) ? 'alt1' : 'alt1';
                                                $rowcount++;
                                                
                                                if ($res['inputtype'] == 'yesno')
                                                {
                                                        $html .= $this->construct_paymodules_parent_yesno_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
                                                }
                                                else if ($res['inputtype'] == 'int')
                                                {
                                                        $html .= $this->construct_paymodules_parent_int_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
                                                }
                                                else if ($res['inputtype'] == 'text')
                                                {
                                                        $html .= $this->construct_paymodules_parent_text_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
                                                }
                                                else if ($res['inputtype'] == 'pass')
                                                {
                                                        $html .= $this->construct_paymodules_parent_pass_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
                                                }
                                                else if ($res['inputtype'] == 'textarea')
                                                {
                                                        $html .= $this->construct_paymodules_parent_textarea_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
                                                }
                                                else if ($res['inputtype'] == 'pulldown')
                                                {
                                                        $html .= $this->construct_paymodules_parent_pulldown_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['inputcode'], $res['help'], $res['sort']);
                                                }
                                        }
                                        
                                        $html .= '<tr><td colspan="2"><input type="submit" name="save" value=" ' . $phrase['_save'] . ' " onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" class="buttons" style="font-size:15px" /></td></tr></table></div>
                                                        
                                                        <div class="block-footer">
                                                                        <div class="block-right">
                                                                                        <div class="block-left"></div>
                                                                        </div>
                                                        </div>
                                                        
                                        </div>
                                </div></form><div style="padding-bottom:4px"></div>';
                                }
                        }
                }
                
                return $html;
        }

        /**
        * Function to print yes or no type radio boxes for yes / no type settings.
        *
        * @param       integer      config id
        * @param       string       value
        * @param       string       description
        * @param       string       input type
        * @param       string       css class
        * @param       string       $ilconfig[] name
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_parent_yesno_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
                global $ilance, $myapi, $ilconfig, $phrase, $ilpage;
                
                $html = '<tr valign="top" class="' . $class . '"><td align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . ucfirst($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap"><label for="rb_1[' . $id . ']"> 
                <input type="radio" name="config[' . $id . ']" value="1" id="rb_1[' . $id . ']" ';
                if ($value == '1')
                {
                        $html .= 'checked="checked" ';
                }
                $html .= '>'.$phrase['_yes'].'</label>
                <label for="rb_0[' . $id . ']">
                <input type="radio" name="config[' . $id . ']" value="0" id="rb_0[' . $id . ']" ';
                if ($value == '0')
                {
                        $html .= 'checked="checked" ';
                }
                $html .= '>' . $phrase['_no'] . '</label></span></td>
                <td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td></tr>';
                
                return $html;
	}
        
        /**
        * Function to print integer type settings.
        *
        * @param       integer      config id
        * @param       string       value
        * @param       string       description
        * @param       string       input type
        * @param       string       background color
        * @param       string       $ilconfig[] name
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_parent_int_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
                global $ilance, $myapi, $ilconfig;
                
                $html = '<tr class="' . $class . '" valign="top"><td align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap"><input type="text" name="config[' . $id . ']" value="' . $value . '" style="width:50px; font-family: Verdana" /></span></td><td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td></tr>';
                return $html;
	}
        
        /**
        * Function to print text field type settings.
        *
        * @param       integer      config id
        * @param       string       value
        * @param       string       description
        * @param       string       input type
        * @param       string       background color
        * @param       string       $ilconfig[] name
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_parent_text_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
                global $ilance, $myapi, $ilconfig;
                
                $html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap"><input type="text" name="config[' . $id . ']" style="width: 200px; font-family: Verdana" value="' . $value . '" /></span></td><td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td></tr>';
                return $html;
	}
        
        /**
        * Function to print password field type settings.
        *
        * @param       integer      config id
        * @param       string       value
        * @param       string       description
        * @param       string       input type
        * @param       string       background color
        * @param       string       $ilconfig[] name
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_parent_pass_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
                global $ilance, $myapi, $ilconfig;
                
                $html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right" valign="top"><span style="white-space:nowrap"><input type="password" name="config[' . $id . ']" style="width: 200px; font-family: Verdana" value="' . $value . '" /></span></td><td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td></tr>';
                return $html;
	}
        
        /**
        * Function to print textarea field type settings.
        *
        * @param       integer      config id
        * @param       string       value
        * @param       string       description
        * @param       string       input type
        * @param       string       background color
        * @param       string       $ilconfig[] name
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_parent_textarea_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
                global $ilance, $myapi, $ilconfig;
                
                $html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right" valign="top"><span style="white-space:nowrap"><textarea name="config[' . $id . ']" style="width: 325px; height: 84px; font-family: Verdana" wrap="physical">' . $value . '</textarea></span></td><td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td></tr>';
                return $html;
	}
        
        /**
        * Function to print pulldown selection menu field type settings.
        *
        * @param       integer      config id
        * @param       string       value
        * @param       string       description
        * @param       string       input type
        * @param       string       background color
        * @param       string       $ilconfig[] name
        * @param       string       pulldown menu input code
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_paymodules_parent_pulldown_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $inputcode = '', $help = '', $sort = 0)
	{
                global $ilance, $myapi, $ilconfig;
                
                $html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right" valign="top"><span style="white-space:nowrap">' . stripslashes($inputcode) . '</span></td><td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td></tr>';
                return $html;
	}
        
        /**
        * Function to print the products or add-ons installed pulldown menu.
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function products_pulldown($selected = 'ilance')
        {
                global $ilance, $myapi, $phrase;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "modules_group
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '<select name="product" style="font-family: verdana">';
                        $html .= '<option value="ilance">ILance</option>';
                        
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if (mb_strtolower($res['modulename']) == mb_strtolower($selected))
                                {
                                        $html .= '<option value="' . mb_strtolower(stripslashes($res['modulename'])) . '" selected="selected">' . ucfirst(stripslashes($res['modulename'])) . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . mb_strtolower(stripslashes($res['modulename'])) . '">' . ucfirst(stripslashes($res['modulename'])) . '</option>';
                                }
                                        
                        }
                        
                        $html .= '</select>';
                }
                
                if (isset($html))
                {
                        return $html;
                }
                else
                {
                        $html = '<select name="product" style="font-family: verdana">';
                        $html .= '<option value="ilance" selected="selected">ILance</option>';
                        $html .= '</select>';
                }
                
                return $html;
        }
        
        /**
        * Function to print the email departments pulldown menu.
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function email_departments_pulldown($selected = '')
        {
                global $ilance, $myapi, $phrase;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "email_departments
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '<select name="departmentid" style="font-family: verdana">';
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (mb_strtolower($res['departmentid']) == intval($selected))
                                {
                                        $html .= '<option value="' . $res['departmentid'] . '" selected="selected">' . $res['title'] . ' (' . $res['email'] . ')</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['departmentid'] . '">' . $res['title'] . ' (' . $res['email'] . ')</option>';
                                }
                                        
                        }
                        $html .= '</select>';
                }
                
                return $html;
        }
        
        /**
        * Function to print the email departments pulldown menu.
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function fetch_email_department_title($selected = '')
        {
                global $ilance, $myapi, $phrase;
                
                $html = 'Unassigned';
                
                $sql = $ilance->db->query("
                        SELECT title
                        FROM " . DB_PREFIX . "email_departments
                        WHERE departmentid = '" . intval($selected) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $html = $res['title'];
                }
                
                return $html;
        }
    
        /**
        * Function to print all phrases in the database within a pulldown menu.
        *
        * @param       integer      phrase group
        * @param       integer      language id
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function phraselist_pulldown($phrasegroup = '', $languageid = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($languageid) . "'
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
    
                $html = '<select size="12" style="font-family: verdana; width: 100%; background-color: #fff;" onchange="
                if (this.options[this.selectedIndex].value != \'\')
                {
                    this.form.varname.value = \'{\' + this.options[this.selectedIndex].text + \'}\';
                    this.form.textbox.value = this.options[this.selectedIndex].value;
                }
                ">';
                
                $sql2 = $ilance->db->query("
                        SELECT varname, text_" . mb_strtolower(mb_substr($res['languagecode'], 0, 3)) . " AS text
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "'
                ", 0, null, __FILE__, __LINE__);    
                while ($res2 = $ilance->db->fetch_array($sql2))
                {
                        $html .= '<option value="' . stripslashes($res2['text']) . '">' . $res2['varname'] . '</option>';
                }    
                $html .= '</select>';
                
                return $html;
        }
    
        /**
        * Function to print all template groups within a multiple select list menu.
        *
        * @return      string       HTML representation of the selection menu
        */
        function template_groups()
        {
                global $ilance, $myapi, $phrase, $ilpage;
                
                require_once(DIR_API . 'class.template_files.inc.php');
                $start = new template_files($ilance);
                
                $base = array(DIR_TEMPLATES);
                $start->loop($base);
                                
                $html = '<select name="templates" id="templatelist" style="font-family: verdana; width: 730px; background-color: #ffffff;" size="15" ondblclick="window.location.href=\''.$ilpage['language'].'?cmd=templates&subcmd=edit&id=\' + escape(document.tplform.templates.value) + \'\';" onchange="
                <!--
                if (this.options[this.selectedIndex].value != \'\')
                {
                    this.form.varname.value = \'{\' + this.options[this.selectedIndex].text + \'}\';
                    this.form.textbox.value = this.options[this.selectedIndex].value;
                }
                //-->
                ">';
                
                $html .= '<optgroup label="'.$phrase['_dynamic_templates'].'">';
                foreach ($start->newarr as $value)
                {
                        if ($value != DIR_TEMPLATES AND $value != '.svn' AND $value != 'text-base' AND $value != 'tmp' AND $value != 'props' AND $value != 'prop-base')
                        {
                                $html .= '<option value="'.$value.'">'.$value.'</option>';
                        }
                }
                $html .= '</optgroup>';
                $html .= '</select>';
                return $html;
        }
    
        /**
        * Function to print all phrase groups in the database within a pulldown menu.
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function phrasegroup_pulldown()
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT groupname, description
                        FROM " . DB_PREFIX . "language_phrasegroups
                ", 0, null, __FILE__, __LINE__);
                $html = '<select name="phrasegroup" style="font-family: verdana">';
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $html .= '<option value="' . $res['groupname'] . '">' . stripslashes($res['description']) . '</option>';
                }    
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print all phrase groups in the database within a pulldown menu.
        *
        * @param       integer      language id
        *
        * @return      string       language title
        */
        function fetch_language_name($languageid = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT title
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '".intval($languageid)."'
                ", 0, null, __FILE__, __LINE__);
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $html = stripslashes($res['title']);
                }
                
                return $html;
        }
        
        /**
        * Function to fetch all fields to use within the Reporting section of the AdminCP
        *
        * @return      array         data array returned with fields
        */
        function fetch_reporting_fields($sql, $fields)
        {
                global $ilance, $myapi;
                
                $query = $ilance->db->query($sql);
                $data = array();
                
                $i = 0;
                while ($row = $ilance->db->fetch_array($query))
                {
                        for ($k = 0; $k < count($fields); $k++)
                        {
                                $data[$i][$fields[$k]] = $row[$fields[$k]];
                        }
                        $i++;
                }
                
                return $data;
        }
    
        /**
        * Function to construct and print an html table with predefined headings.
        *
        * @param       array        data
        * @param       array        heading array
        *
        * @return      string       HTML representation of the html table
        */
        function construct_html_table($data, $headings)
        {
                global $ilance, $myapi, $ilconfig, $phrase, $ilpage;
                
                $invoiceTotalIndex = $invoiceTotalAmountIndex = $paidTotalIndex = $taxAmountIndex = -1;
                
                $colspan = count($headings);
                $totalrows = count($data);
                
                $html = '
                <div class="block-wrapper">
                        <div class="block">
                                <div class="block-top">
                                                <div class="block-right">
                                                                <div class="block-left"></div>
                                                </div>
                                </div>
                                <div class="block-header">' . $totalrows . ' transactions found</div>
                                <div class="block-content-yellow" style="padding:9px"><div class="smaller">Any transaction that has been recorded with a total amount more than 0 will be presented below.  Waived and free transactions will be excluded.</div></div>
                                <div class="block-content" style="padding:0px">
                                                
                <table width="100%" cellspacing="' . $ilconfig['table_cellspacing'] . '" cellpadding="' . $ilconfig['table_cellpadding'] . '" border="0" dir="' . $ilconfig['template_textdirection'] . '">
                <tr class="alt2">';
                foreach ($headings AS $index => $cell)
                {
                        //$html .= '<td align="center">' . htmlentities($cell, ENT_QUOTES) . '</td>';
                        $html .= '<td align="center">' . ilance_htmlentities($cell) . '</td>';
                        if ($cell == 'Amount')
                        {
                                $invoiceTotalIndex = $index;
                        }
                        else if ($cell == 'Paid')
                        {
                                $paidTotalIndex = $index;
                        }
                        else if ($cell == 'Total')
                        {
                                $invoiceTotalAmountIndex = $index;
                        }
                        else if ($cell == 'Tax')
                        {
                                $taxAmountIndex = $index;
                        }
                        else if ($cell == 'Paid Date')
                        {
                                $paidDateIndex = $index;
                        }
                        else if ($cell == 'Created')
                        {
                                $createDateIndex = $index;
                        }
                        else if ($cell == 'Due')
                        {
                                $dueDateIndex = $index;
                        }
                        else if ($cell == 'UID')
                        {
                                $userIDIndex = $index;
                        }
                        else if ($cell == 'PID')
                        {
                                $projectIDIndex = $index;
                        }
                }
                $html .= '</tr>';
                
                $invoiceTotal = $paidTotal = $invoiceTotalAmount = $taxTotal = 0.0;
                
                $c = 0;
                foreach ($data AS $row)
                {
                        //if ($c <= $ilance->GPC['limit'])
                        //{
                                //$c++;
                                $index = $row_count = 0;
                                $html .= '<tr class="alt1">';
                                foreach ($row AS $cell)
                                {
                                        $class = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $row_count++;
                                        if ($index == $invoiceTotalIndex)
                                        {
                                                $invoiceTotal += $cell;
                                        }
                                        else if ($index == $paidTotalIndex)
                                        {
                                                $paidTotal += $cell;
                                        }
                                        else if ($index == $invoiceTotalAmountIndex)
                                        {
                                                $invoiceTotalAmount += $cell;
                                        }
                                        else if ($index == $taxAmountIndex)
                                        {
                                                $taxTotal += $cell;
                                        }
                                        
                                        if (isset($userIDIndex) AND $index == $userIDIndex)
                                        {
                                                if (isset($ilance->GPC['useridconvert']) AND $ilance->GPC['useridconvert'])
                                                {
                                                        $html .= '<td align="center" class="smaller"><a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $cell . '">' . fetch_user('username', $cell) . '</a></td>';
                                                }
                                                else
                                                {
                                                        $html .= '<td align="center" class="smaller">' . $cell . '</td>';
                                                }
                                        }
                                        else if (isset($projectIDIndex) AND $index == $projectIDIndex)
                                        {
                                                if (isset($ilance->GPC['projectidconvert']) AND $ilance->GPC['projectidconvert'])
                                                {
                                                        $html .= '<td align="center" class="smaller">' . fetch_auction('project_title', $cell) . '</td>';
                                                }
                                                else
                                                {
                                                        $html .= '<td align="center" class="smaller">' . $cell . '</td>';
                                                }
                                        }                                        
                                        else if ($index == $invoiceTotalIndex)
                                        {
                                                $html .= '<td align="center" class="smaller">' . $ilance->currency->format($cell) . '</td>';
                                        }
                                        else if ($index == $paidTotalIndex)
                                        {
                                                $html .= '<td align="center" class="smaller">' . $ilance->currency->format($cell) . '</td>';
                                        }
                                        else if ($index == $invoiceTotalAmountIndex)
                                        {
                                                $html .= '<td align="center" class="smaller">' . $ilance->currency->format($cell) . '</td>';
                                        }
                                        else if ($index == $taxAmountIndex)
                                        {
                                                $html .= '<td align="center" class="smaller">' . $ilance->currency->format($cell) . '</td>';
                                        }
                                        else if (isset($paidDateIndex) AND $index == $paidDateIndex OR isset($createDateIndex) AND $index == $createDateIndex OR isset($dueDateIndex) AND $index == $dueDateIndex)
                                        {
                                                $html .= '<td align="center" class="smaller">' . print_date($cell, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</td>';
                                        }
                                        else
                                        {
                                                $html .= '<td align="left" class="smaller">' . mb_strtoupper($cell) . '</td>';
                                        }
                                        $index++;
                                }
                                $html .= '</tr>';
                        //}
                }
        
                if ($invoiceTotalIndex != -1 OR $paidTotalIndex != -1 OR $invoiceTotalAmountIndex != -1 OR $taxAmountIndex != -1)
                {
                        $html .= '<tr class="alt1">';
                        for ($i = 0; $i < count($headings); $i++)
                        {
                                $html .= '<td align="center">';
                                if ($i == $invoiceTotalIndex)
                                {
                                        $html .= '<div style="color:#999"><strong>' . $ilance->currency->format(round((float) $invoiceTotal, 2)) . '</strong></div>';
                                }
                                else if ($i == $paidTotalIndex)
                                {
                                        $html .= '<div style="color:#000"><strong>' . $ilance->currency->format(round((float) $paidTotal, 2)) . '</strong></div>';
                                }
                                else if ($i == $invoiceTotalAmountIndex)
                                {
                                        $html .= '<div style="color:#999"><strong>' . $ilance->currency->format(round((float) $invoiceTotalAmount, 2)) . '</strong></div>';
                                }
                                else if ($i == $taxAmountIndex)
                                {
                                        $html .= '<div style="color:#000"><strong>' . $ilance->currency->format(round((float) $taxTotal, 2)) . '</strong></div>';
                                }
                                else
                                {
                                        $html .= "&nbsp;";
                                }
                                $html .= '</td>';
                        }
                        $html .= '</tr>';
                }
                $html .= '<tr><td colspan="' . $colspan . '"><input type="button" value=" ' . $phrase['_back'] . ' " style="font-size:15px" onclick="location.href=\'' . $ilpage['accounting'] . '?cmd=reports&amp;doshow=' . $ilance->GPC['doshow'] . '\'" class="buttons" /></td></tr>';
                $html .= '</table>';
                
                $html .= '</div>
                                        
                                        <div class="block-footer">
                                                        <div class="block-right">
                                                                        <div class="block-left"></div>
                                                        </div>
                                        </div>
                                        
                        </div>
                </div>';
                
                return $html;
	}
	
	/**
        * Function to construct and print comma separated data with predefined headings.
        *
        * @param       array        data
        * @param       array        heading array
        *
        * @return      string       Text representation of the comma separated values
        */
        function construct_csv_data($data, $headings)
	{
                $csv = '';
                foreach ($headings as $cell)
                {
                        $csv .= mb_strtoupper($cell) . ',';
                }
                $csv = mb_substr($csv, 0, -1);
                $csv .= LINEBREAK;
                foreach ($data as $row)
                {
                        foreach ($row as $cell)
                        {
                                $csv .= "\"" . mb_strtoupper($cell) . "\",";
                        }
                        $csv = mb_substr($csv, 0, -1);
                        $csv .= LINEBREAK;
                }
                
                return $csv;
	}
	
	/**
        * Function to construct and print tab separated data with predefined headings.
        *
        * @param       array        data
        * @param       array        heading array
        *
        * @return      string       HTML representation of the html table
        */
        function construct_tsv_data($data, $headings)
	{
                $csv = "";
                foreach ($headings as $cell)
                {
                    $csv .= mb_strtoupper($cell) . "	";
                }
                $csv = mb_substr($csv, 0, -1);
                $csv .= "
                ";
                foreach ($data as $row)
                {
                    foreach ($row as $cell)
                    {
                        $csv .= "'" . mb_strtoupper($cell) . "'	";
                    }            
                    $csv = mb_substr($csv, 0, -1);
                    $csv .= "
                ";
                }
                
                return $csv;
	}
	
	/**
        * Function to construct and print a category pulldown menu.
        *
        * @param       integer      category id
        * @param       string       category tyoe
        * @param       string       short language identifier (eng is default)
        *
        * @return      string       HTML representation of the category pulldown menu
        */
        function category_pulldown($cid = 0, $cattype = 'service', $slng = 'eng')
	{
                global $ilance, $myapi;
                
                if ($cattype == 'service')
                {
                        $sql = $ilance->db->query("
                                SELECT cid, title_" . $slng . " AS categoryname
                                FROM " . DB_PREFIX . "categories
                                WHERE cattype = 'service'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $pulldown = '<select name="category_id" style="font-family: verdana">';
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                            $pulldown .= '<option value="'.$res['category_id'].'" ';
                                            if ($res['cid'] == $cid)
                                            {
                                                    $pulldown .= 'selected="selected"';
                                            }
                                            $pulldown .= '>'.stripslashes($res['categoryname']).'</option>';
                                }
                                $pulldown .= '</select>';
                        }
                }
                else if ($cattype == 'product')
                {
                    $sql = $ilance->db->query("
                            SELECT cid, title_" . $slng . " AS categoryname
                            FROM " . DB_PREFIX . "categories
                            WHERE cattype = 'product'
                            ORDER BY sort ASC
                    ", 0, null, __FILE__, __LINE__);
                    if ($ilance->db->num_rows($sql) > 0)
                    {
                                $pulldown = '<select name="category_id" style="font-family: verdana">';
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $pulldown .= '<option value="'.$res['category_id'].'" ';
                                        if ($res['cid'] == $cid)
                                        {
                                                $pulldown .= 'selected="selected"';
                                        }
                                        $pulldown .= '>'.stripslashes($res['categoryname']).'</option>';
                                }
                                $pulldown .= '</select>';
                        }
                }
                
                return $pulldown;
	}
	
	/**
        * Function to create a new user.
        *
        * @param       string       username
        * @param       string       password
        * @param       string       password salt
        * @param       string       secret question
        * @param       string       secret answer
        * @param       string       email
        * @param       string       first name
        * @param       string       last name
        * @param       string       address 1
        * @param       string       address 2
        * @param       string       city
        * @param       string       state
        * @param       string       zip code
        * @param       string       phone number
        * @param       integer      country id
        * @param       string       date of birth
        * @param       string       referral code
        * @param       integer      language id
        * @param       integer      currency id
        * @param       integer      timezone id
        * @param       boolean      use dst? (default disabled)
        * @param       string       newsletter categories (default none)
        * @param       integer      is admin (default off)
        */
        function construct_new_member($username = '', $password = '', $salt = '', $secretquestion = '', $secretanswer = '', $email = '', $first = '', $last = '', $address = '', $address2 = '', $city = '', $state = '', $zipcode = '', $phone = '', $country = '', $dob = '', $rid = '', $languageid = '', $currencyid = '', $usertimezone = '', $usertimezone_dst = 0, $categories = '', $isadmin = 0)
	{
                global $ilance, $ilconfig, $myapi;
                
                // is account bonus active?
                $accountbonus = 0;
                if ($ilconfig['registrationupsell_bonusactive'] AND empty($ilance->GPC['bonusdisable']))
                {
                        $accountbonus = $ilconfig['registrationupsell_amount'];
                }
                
                $accountnumber = construct_account_number();
                $ipaddress = IPADDRESS;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "users
                        (user_id, ipaddress, username, password, salt, secretquestion, secretanswer, email, first_name, last_name, address, address2,
                        city, state, zip_code, phone, country, date_added, status, dob, rid, account_number, available_balance, total_balance,
                        languageid, currencyid, timezoneid, timezone_dst, notifyservices, notifyproducts, notifyservicescats, emailnotify, isadmin)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($ipaddress) . "',
                        '" . $ilance->db->escape_string($username) . "',
                        '" . $ilance->db->escape_string($password) . "',
                        '" . $ilance->db->escape_string($salt) . "',
                        '" . $ilance->db->escape_string($secretquestion) . "',
                        '" . $ilance->db->escape_string($secretanswer) . "',
                        '" . $ilance->db->escape_string($email) . "',
                        '" . $ilance->db->escape_string($first) . "',
                        '" . $ilance->db->escape_string($last) . "',
                        '" . $ilance->db->escape_string($address) . "',
                        '" . $ilance->db->escape_string($address2) . "',
                        '" . $ilance->db->escape_string($city) . "',
                        '" . $ilance->db->escape_string($state) . "',
                        '" . $ilance->db->escape_string($zipcode) . "',
                        '" . $ilance->db->escape_string($phone) . "',
                        '" . intval($country) . "',
                        '" . DATETIME24H . "',
                        'active',
                        '" . $ilance->db->escape_string($dob) . "',
                        '" . $ilance->db->escape_string($rid) . "',
                        '" . $ilance->db->escape_string($accountnumber) . "',
                        '" . $ilance->db->escape_string($accountbonus) . "',
                        '" . $ilance->db->escape_string($accountbonus) . "',
                        '" . intval($languageid) . "',
                        '" . intval($currencyid) . "',
                        '" . $usertimezone . "',
                        '" . $usertimezone_dst . "',
                        '1',
                        '1',
                        '" . $ilance->db->escape_string($categories) . "',
                        '1',
                        '" . intval($isadmin) . "')
                ", 0, null, __FILE__, __LINE__);
                $user_id = $ilance->db->insert_id();
                
                // track income reported
                if ($accountbonus > 0)
                {
                        insert_income_reported($user_id, $accountbonus, 'credit');
                }
                
                return $user_id;
	}
	
	/**
        * Function to fetch how many categories are currently associated with this particular insertion group.
        *
        * @param       string       category type (service or product)
        * @param       string       insertion group name
        *
        * @return      integer      category count
        */
        function fetch_insertion_catcount($cattype, $group)
	{
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE insertiongroup = '".$ilance->db->escape_string($group)."'
                            AND cattype = '".$ilance->db->escape_string($cattype)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        return $result['count'];
                }
                
                return 0;
	}
        
        /**
        * Function to fetch how many budget ranges are currently associated with this particular insertion group.
        *
        * @param       string       insertion group name
        *
        * @return      integer      category count
        */
        function fetch_insertion_budget_catcount($group)
	{
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "budget
                        WHERE insertiongroup = '".$ilance->db->escape_string($group)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        return $result['count'];
                }
                
                return 0;
	}
	
	/**
        * Function to fetch how many categories are currently associated with this particular budget group.
        *
        * @param       string       budget group name
        *
        * @return      integer      category count
        */
        function fetch_budget_catcount($group)
	{
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE budgetgroup = '".$ilance->db->escape_string($group)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        return $result['count'];
                }
                
                return 0;
	}
        
        /**
        * Function to fetch how many categories are currently associated with this particular bid increment group.
        *
        * @param       string       increment group name
        *
        * @return      integer      category count
        */
        function fetch_increment_catcount($group)
	{
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE incrementgroup = '".$ilance->db->escape_string($group)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        return $result['count'];
                }
                
                return 0;
	}
	
	/**
        * Function to fetch how many categories are currently associated with this particular final value group.
        *
        * @param       string       category type (service or product)
        * @param       string       final value group name
        *
        * @return      integer      category count
        */
        function fetch_fv_catcount($cattype, $group)
	{
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE finalvaluegroup = '".$ilance->db->escape_string($group)."'
                        AND cattype = '".$ilance->db->escape_string($cattype)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql);
                        return $result['count'];
                }
                
                return 0;
	}
        
        /**
        * Function to print a pulldown for enabling or disabling moderation
        *
        * @param       string        value
        * @param       string        key
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_moderation_pulldown($value = '', $variableinfo = '')
        {
                global $phrase;
                
                $html = '<select name="config[' . $variableinfo . ']" style="font-family: verdana">';
                
                if ($value)
                {
                        $html .= '
                        <option value="1" selected="selected">' . $phrase['_disabled'] . '</option>
                        <option value="0">' . $phrase['_enabled'] . '</option>';
                }
                else
                {
                        $html .= '
                        <option value="1">' . $phrase['_disabled'] . '</option>
                        <option value="0" selected="selected">' . $phrase['_enabled'] . '</option>';
                }
                
                $html .= '</select>';
                
                return $html;    
        }
        
        /**
        * Function to print the AdminCP navigation menu
        *
        * @param       string        short form language identifier
        * @param       string        current url
        *
        * @return      string        HTML representation of the AdminCP nav
        */
        function print_adminstuff_nav($slng = 'eng', $currenturl = '') 
        {
                global $ilance, $myapi, $ilconfig, $ilpage, $phrase;
                
                ($apihook = $ilance->api('print_adminstuff_nav_start')) ? eval($apihook) : false;
                
                $xml_file = DIR_XML . 'adminstuff_topnav.xml';
                $xml = file_get_contents($xml_file);
                
                $xml_encoding = '';
                
                if (MULTIBYTE)
                {
                        $xml_encoding = mb_detect_encoding($xml);
                }
                
                if ($xml_encoding == 'ASCII')
                {
                        $xml_encoding = '';
                }
            
                $data = array();
                
                $parser = xml_parser_create($xml_encoding);
                xml_parse_into_struct($parser, $xml, $data);
                $error_code = xml_get_error_code($parser);                
                xml_parser_free($parser);
                
                if ($error_code == 0)
                {
                        $ilance->template = construct_object('api.template');
                        $result = $ilance->template->process_cpnav_xml($data, $xml_encoding, 'ADMIN');
                        
                        $navarray = $result['navarray'];
                        $navitems = array();
                        
                        $navcount = count($navarray);
                        for ($i = 0; $i < $navcount; $i++)
                        {
                                $navitems['id'] = ($i + 1);
                                $navitems['name'] = $phrase["{$navarray[$i][1]}"];
                                $navitems['lc_name'] = mb_strtolower($navarray[$i][1]);
                                $navitems['key'] = $navarray[$i][0];
                                if (empty($navarray[$i][3]))
                                {
                                        // use regular url
                                        $navitems['url'] = $navarray[$i][2];
                                }
                                else
                                {
                                        // use seo url
                                        $navitems['url'] = $navarray[$i][3];
                                }
                                
                                if (isset($navitems['url']) AND $navitems['url'] == $currenturl)
                                {
                                        $navitems['class'] = 'class="selected"';
                                }
                                else
                                {
                                        $navitems['class'] = '';
                                }
                                
                                // fade liveupdate menu option until further notice
                                /*
                                if ($navitems['url'] == 'livesync/admin/index.php')
                                {
                                        $navitems['class'] .= ' style="filter:alpha(opacity=20); -moz-opacity:0.2"';
                                }
                                */
                                
                                ($apihook = $ilance->api('print_adminstuff_nav_condition')) ? eval($apihook) : false;
                                
                                $nav[] = $navitems;	
                        }
                }
                
                ($apihook = $ilance->api('print_adminstuff_nav_end')) ? eval($apihook) : false;
                
                return $nav;
        }
        
        /**
        * Function to print the AdminCP sub navigation menus
        *
        * @param       string        current root nav we're on
        * @param       string        current url we're on
        * @param       string        short form language identifier
        *
        * @return      string        HTML representation of the AdminCP sub nav
        */
        function print_adminstuff_subnav($rootnav = '', $currenturl = '', $slng = 'eng') 
        {
                global $ilance, $myapi, $phrase;
                
                ($apihook = $ilance->api('print_adminstuff_subnav_start')) ? eval($apihook) : false;
                
                $xml_file = DIR_XML . 'adminstuff_topnav.xml';
                $xml = file_get_contents($xml_file);
                
                $xml_encoding = '';
                
                if (MULTIBYTE)
                {
                        $xml_encoding = mb_detect_encoding($xml);
                }
                
                if ($xml_encoding == 'ASCII')
                {
                        $xml_encoding = '';
                }
                
                $data = array();
                
                $parser = xml_parser_create($xml_encoding);
                xml_parse_into_struct($parser, $xml, $data);
                $error_code = xml_get_error_code($parser);
                
                xml_parser_free($parser);
                
                if ($error_code == 0)
                {
                        $ilance->template = construct_object('api.template');
                        $result = $ilance->template->process_cpnav_xml($data, $xml_encoding, 'ADMIN');
                        
                        $navoptions = $result['navoptions'];
                        $navitems = array();
                        
                        $navcount = count($navoptions);
                        for ($x = 0; $x < $navcount; $x++)
                        {
                                if (preg_match("/$rootnav/", $navoptions[$x][2]))
                                {
                                        $navitems['name'] = $phrase["{$navoptions[$x][1]}"];
                                        if (empty($navoptions[$x][3]))
                                        {
                                                // use regular url
                                                $navitems['url'] = $navoptions[$x][2];
                                        }
                                        else
                                        {
                                                // use seo url
                                                $navitems['url'] = $navoptions[$x][3];
                                        }
                                        
                                        if ($navoptions[$x][2] == $currenturl)
                                        {
                                                $navitems['class'] = 'class="sel"';
                                        }
                                        else
                                        {
                                                $navitems['class'] = '';
                                        }
                                        
                                        ($apihook = $ilance->api('print_adminstuff_subnav_condition')) ? eval($apihook) : false;
                                        
                                        $subnav_settings[] = $navitems;	
                                }
                        }	
                }
                
                ($apihook = $ilance->api('print_adminstuff_subnav_end')) ? eval($apihook) : false;
                
                if (!empty($subnav_settings))
                {
                        return $subnav_settings;
                }
        }
        
        /**
        * Function to print out a pulldown menu with all files used in ILance.
        *
        * @return      string        HTML representation of the pulldown menu with auction type values
        */
        function print_template_filelist_pulldown()
        {
                $html = '<select name="templates" style="font-family: verdana">';
                $count = 0;
                $templates = opendir(DIR_TEMPLATES);
                while ($files = readdir($templates))
                {
                        if ($templates[0] != '.' AND $templates[0] != '..')
                        {
                                $html .= '<option value="'.$templates.'">'.$templates.'</option>';
                                $count++;
                        }
                }
                $html .= '</select>';
                closedir($templates);
                
                return $html;
        }
        
        /**
        * Function to fetch the description of a particular phrase group within the datastore
        *
        * @param       integer       phrase group
        *
        * @return      string        HTML representation of the pulldown menu with auction type values
        */
        function api_phrasegroupname($phrasegroup = '')
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT description
                        FROM " . DB_PREFIX . "language_phrasegroups
                        WHERE groupname = '" . $ilance->db->escape_string($phrasegroup) . "'
                ");
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $html = stripslashes($res['description']);
                }
                
                return $html;
        }
        
        /**
        * Function to construct and print out the insertion group pulldown menu
        *
        * @param       string        insertion group name
        * @param       string        category type (service or product)
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function construct_insertion_group_pulldown($insertiongroup, $cattype)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '<select name="insertiongroup" style="font-family: verdana">';
                $html .= '<option value="0">'.$phrase['_no_insertion_group'].'</option>';
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "insertion_groups
                        WHERE state = '".$cattype."'
                        GROUP BY groupname
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                            if (isset($insertiongroup) AND $insertiongroup == $res['groupname'])
                            {
                                    $html .= '<option value="'.$res['groupname'].'" selected="selected">'.ucfirst($res['groupname']).'</option>';	
                            }
                            else 
                            {
                                    $html .= '<option value="'.$res['groupname'].'">'.ucfirst($res['groupname']).'</option>';	
                            }
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to construct and print out the budget group pulldown menu
        *
        * @param       string        budget group name
        * @param       string        category type (service or product)
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function construct_budget_group_pulldown($budgetgroup, $cattype)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '<select name="budgetgroup" style="font-family: verdana">';
                $html .= '<option value="0">'.$phrase['_no_budget_group'].'</option>';
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "budget_groups
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($budgetgroup) AND $budgetgroup == $res['groupname'])
                                {
                                        $html .= '<option value="'.$res['groupname'].'" selected="selected">'.ucfirst($res['groupname']).'</option>';	
                                }
                                else 
                                {
                                        $html .= '<option value="'.$res['groupname'].'">'.ucfirst($res['groupname']).'</option>';
                                }
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to construct and print out the final value group pulldown menu
        *
        * @param       string        final value group name
        * @param       string        category type (service or product)
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function construct_finalvalue_group_pulldown($finalvaluegroup, $cattype)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '<select name="finalvaluegroup" style="font-family: verdana">';
                $html .= '<option value="0">'.$phrase['_no_final_value_group'].'</option>';
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "finalvalue_groups
                        WHERE state = '".$cattype."'
                        GROUP BY groupname
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($finalvaluegroup) AND $finalvaluegroup == $res['groupname'])
                                {
                                        $html .= '<option value="'.$res['groupname'].'" selected="selected">'.ucfirst($res['groupname']).'</option>';	
                                }
                                else 
                                {
                                        $html .= '<option value="'.$res['groupname'].'">'.ucfirst($res['groupname']).'</option>';
                                } 
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to construct and print out the increment group pulldown menu
        *
        * @param       string        increment group name
        * @param       string        category type (service or product)
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function construct_increment_group_pulldown($incrementgroup, $cattype)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '<select name="incrementgroup" style="font-family: verdana">';
                $html .= '<option value="0">No bid increment group</option>';
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "increments_groups
                        GROUP BY groupname
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($incrementgroup) AND $incrementgroup == $res['groupname'])
                                {
                                        $html .= '<option value="'.$res['groupname'].'" selected="selected">'.ucfirst($res['groupname']).'</option>';	
                                }
                                else 
                                {
                                        $html .= '<option value="'.$res['groupname'].'">'.ucfirst($res['groupname']).'</option>';
                                } 
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to construct and print out the role type pulldown menu
        *
        * @param       string        selected role type
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_roletype_pulldown($selected = '')
        {
                $roletypes = array('service','product','both');
                $html = '<select name="roletype" style="font-family: verdana">';
                foreach ($roletypes as $key)
                {
                        if ($key == $selected)
                        {
                                $html .= '<option value="'.$key.'" selected="selected">'.$key.'</option>';
                        }
                        else
                        {
                                $html .= '<option value="'.$key.'">'.$key.'</option>';
                        }    
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to construct and print out the role user type pulldown menu
        *
        * @param       string        selected role user type
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_roleusertype_pulldown($selected = '')
        {
                $roleusertypes = array('servicebuyer','serviceprovider','productbuyer','merchantprovider','all');
                $html = '<select name="roleusertype" style="font-family: verdana">';
                foreach ($roleusertypes as $key)
                {
                        if ($key == $selected)
                        {
                                $html .= '<option value="'.$key.'" selected="selected">'.$key.'</option>';
                        }
                        else
                        {
                                $html .= '<option value="'.$key.'">'.$key.'</option>';
                        }    
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print out bid amount types
        *
        * @param       integer       category id
        * @param       boolean       set default type as entire project? (default true)
        *
        * @return      string        HTML representation of the bid amount types
        */
        function construct_bidamounttypes($cid = 0, $entiredefault = 1)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $checked1 = $checked2 = $checked3 = $checked4 = $checked5 = $checked6 = $checked7 =  $checked8 = $checked9 = '';
                if ($entiredefault)
                {
                        $checked1 = 'checked="checked"';
                }
                
                if ($cid > 0)
                {
                        $ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
                        $data = $ilance->categories->bidamounttypes($cid);
                        if (!empty($data))
                        {
                                $data = unserialize($data);
                                if (is_array($data))
                                {
                                        foreach ($data as $key => $value)
                                        {
                                                if (!empty($value) AND $value == 'entire')
                                                {
                                                        $checked1 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'hourly')
                                                {
                                                        $checked2 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'daily')
                                                {
                                                        $checked3 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'weekly')
                                                {
                                                        $checked4 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'monthly')
                                                {
                                                        $checked6 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'lot')
                                                {
                                                        $checked7 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'weight')
                                                {
                                                        $checked8 = 'checked="checked"';
                                                }
                                                if (!empty($value) AND $value == 'item')
                                                {
                                                        $checked9 = 'checked="checked"';
                                                }
                                        }
                                }
                        }   
                }
                
                $html = '
                <div><input type="checkbox" id="entire" name="bidamounttypes[]" value="entire" ' . $checked1 . ' /><label for="entire">'.$phrase['_for_entire_project'].'</label></div>
                <div><input type="checkbox" id="hourly" name="bidamounttypes[]" value="hourly" ' . $checked2 . ' /><label for="hourly">'.$phrase['_per_hour'].'</label></div>
                <div><input type="checkbox" id="daily" name="bidamounttypes[]" value="daily" ' . $checked3 . ' /><label for="daily">'.$phrase['_per_day'].'</label></div>
                <div><input type="checkbox" id="weekly" name="bidamounttypes[]" value="weekly" ' . $checked4 . ' /><label for="weekly">'.$phrase['_weekly'].'</label></div>
                <div><input type="checkbox" id="monthly" name="bidamounttypes[]" value="monthly" ' . $checked6 . ' /><label for="monthly">'.$phrase['_monthly'].'</label></div>';
                
                $html .= '
                <div><input type="checkbox" id="lot" name="bidamounttypes[]" value="lot" ' . $checked7 . ' /><label for="lot">'.$phrase['_per_lot'].'</label></div>
                <div><input type="checkbox" id="weight" name="bidamounttypes[]" value="weight" ' . $checked8 . ' /><label for="weight">'.$phrase['_per_weight'].'</label></div>
                <div><input type="checkbox" id="item" name="bidamounttypes[]" value="item" ' . $checked9 . ' /><label for="item">'.$phrase['_per_item'].'</label></div>';
                        
                return $html;
        }
        
        /**
        * Function to decode HTML entities
        *
        * @param       string        text
        * @param       string        quotes style (default ENT_COMPAT)
        *
        * @return      string        HTML representation of the decoded string
        */
        function decode_entities($text, $quote_style = ENT_COMPAT, $characterset = 'ISO-8859-1')
        {
                if (function_exists('html_entity_decode'))
                {
                        $text = html_entity_decode($text, $quote_style, $characterset);
                }
                else
                { 
                        $trans_tbl = get_html_translation_table(HTML_ENTITIES, $quote_style);
                        $trans_tbl = array_flip($trans_tbl);
                        $text = strtr($text, $trans_tbl);
                }
                
                $text = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text); 
                $text = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $text);
                
                return $text;
        }
        
        /**
        * Function to print a pulldown menu with any available scripts that can be tracked and auditted
        *
        * @param       string        selected option
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_audit_scripts_pulldown($selected = '')
        {
                global $ilance, $ilpage;
                
                ksort($ilpage);
                $html = '<select name="script" style="font-family: verdana">';
                $html .= '<option value="">All Scripts</option>';
                $html .= '<optgroup label="Official Scripts">';
                foreach ($ilpage as $title => $script)
                {
                        if (isset($selected) AND $selected == $script)
                        {
                                $html .= '<option value="' . $script . '" selected="selected">' . $script . '</option>';
                        }
                        else
                        {
                                $html .= '<option value="' . $script . '">' . $script . '</option>';
                        }
                }
                $html .= '</optgroup>';
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print the members pulldown menu
        *
        * @param       string        selected option
        * @param       boolean       show all users (default true)
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_members_pulldown($selected = '', $showallusers = 1)
        {
                global $ilance, $ilpage;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT user_id, username
                        FROM " . DB_PREFIX . "users
                        ORDER BY username ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '<select name="user_id" style="font-family: verdana">';
                        if ($showallusers)
                        {
                                $html .= '<option value="">All Users</option>';
                        }
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($selected) AND $selected == $res['user_id'])
                                {
                                        $html .= '<option value="' . $res['user_id'] . '" selected="selected">' . $res['username'] . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['user_id'] . '">' . $res['username'] . '</option>';
                                }
                        }
                        $html .= '</select>';
                }
                
                return $html;
        }
        
        /**
        * Function to print the admin users pulldown menu
        *
        * @param       string        selected option
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_admins_pulldown($selected = '')
        {
                global $ilance, $ilpage;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT user_id, username
                        FROM " . DB_PREFIX . "users
                        WHERE isadmin = '1'
                        GROUP BY username
                        ORDER BY username ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '<select name="admin_id" style="font-family: verdana">';
                        $html .= '<option value="">All Admins</option>';
                        
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($selected) AND $selected == $res['user_id'])
                                {
                                        $html .= '<option value="' . $res['user_id'] . '" selected="selected">' . $res['username'] . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['user_id'] . '">' . $res['username'] . '</option>';
                                }
                        }
                        
                        $html .= '</select>';
                }
                
                return $html;
        }
        
	/**
        * Function to fetch the latest version of ILance
        *
        * @return      string        HTML representation of the latest version of ILance
        */
        function print_version()
	{
                global $ilconfig, $phrase;
                
                return $phrase['_version'] . ': <strong>' . $ilconfig['current_version'] . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;SQL: <strong>' . $ilconfig['current_sql_version'] . '</strong>';
        }
        
        /**
        * Function to print the admininstration configuration input template menus.
        *
        * @param       string       config group
        *
        * @return      string       HTML representation of the configuration template
        */
        function construct_admin_input($configgroup = '', $returnurl = '')
        {
                global $ilance, $myapi, $ilconfig, $ilcollapse, $phrase, $page_title, $area_title, $ilpage, $show;
                
                $html = '';
                
                $sqlgrp = $ilance->db->query("
                        SELECT description, groupname, help
                        FROM " . DB_PREFIX . "configuration_groups
                        WHERE parentgroupname = '" . $ilance->db->escape_string($configgroup) . "' OR groupname = '" . $ilance->db->escape_string($configgroup) . "'
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlgrp) > 0)
                {
                        $i = 0;
                        while ($resgrpties = $ilance->db->fetch_array($sqlgrp, DB_ASSOC))
                        {
                                $i++;
                                
                                $html .= '
                                <form method="post" action="' . HTTPS_SERVER_ADMIN . $ilpage['settings'] . '" name="updatesettings" accept-charset="UTF-8" style="margin: 0px;">
                                <input type="hidden" name="cmd" value="globalupdate" />
                                <input type="hidden" name="subcmd" value="_update-config-settings" />
                                <input type="hidden" name="return" value="' . $returnurl . '" />
                                <div class="block-wrapper">
                                        <div class="block">
                                                        <div class="block-top">
                                                                        <div class="block-right">
                                                                                        <div class="block-left"></div>
                                                                        </div>
                                                        </div>
                                                        <div class="block-header" onclick="return toggle(\'admincp_setting_' . $configgroup . '_' . $i . '\')" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'\'"><span style="float:left; padding-right:7px; padding-top:3px"><span style="padding-right:5px"><img id="collapseimg_admincp_setting_' . $configgroup . '_' . $i . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand{collapse[collapseimg_admincp_setting_' . $configgroup . '_' . $i . ']}.gif" border="0" alt="" /></span></span>' . stripslashes($resgrpties['description']) . '</div>';
                                                        $html .= (!empty($resgrpties['help'])) ? '<div class="block-content-yellow" style="padding:9px"><div class="smaller">' . stripslashes($resgrpties['help']) . '</div></div>' : '';
                                                        $html .= '<div class="block-content" id="collapseobj_admincp_setting_' . $configgroup . '_' . $i . '" style="{collapse[collapseobj_admincp_setting_' . $configgroup . '_' . $i . ']} padding:0px">';
                                                        $html .= '
                                                        <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">
                                                        <tr class="alt2">
                                                                <td width="80%">' . $phrase['_description'] . '</td>
                                                                <td align="right" width="12%">' . $phrase['_setting'] . '</td>
                                                                <td align="center" width="8%">' . $phrase['_sort'] . '</td>
                                                        </tr>';
                                
                                $sql = $ilance->db->query("
                                        SELECT inputtype, name, value, inputcode, description, help, sort
                                        FROM " . DB_PREFIX . "configuration
                                        WHERE configgroup = '" . $resgrpties['groupname'] . "'
                                                AND visible = '1'
                                        ORDER BY sort ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $rowcount = 0;
                                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                        {
                                                $res['class'] = ($rowcount % 2) ? 'alt1' : 'alt1';
                                                $rowcount++;
                                                
                                                if ($res['inputtype'] == 'yesno')
                                                {
                                                        $html .= $this->construct_parent_yesno_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], $res['help']);
                                                }
                                                else if ($res['inputtype'] == 'int')
                                                {                                
                                                        $html .= $this->construct_parent_int_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], $res['help']);
                                                }
                                                else if (($res['inputtype'] == 'textarea' OR $res['inputtype'] == 'text' OR $res['inputtype'] == 'pass'))
                                                {
                                                        $html .= $this->construct_parent_textarea_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], $res['help']);
                                                }
                                                else if ($res['inputtype'] == 'pulldown')
                                                {
                                                        $html .= $this->construct_parent_pulldown_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], $res['inputcode'], $res['help']);
                                                }
                                        }
                                }
                                
                                $html .= '<tr><td colspan="3">';
                                $html .= ($show['ADMINCP_TEST_MODE']) ? '<input type="submit" name="save" value=" ' . $phrase['_save'] . ' " onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" class="buttons" style="font-size:15px" disabled="disabled" />' : '<input type="submit" name="save" value=" ' . $phrase['_save'] . ' " onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" class="buttons" style="font-size:15px" />';
                                $html .= '</td></tr></table></div>
                                                        
                                                        <div class="block-footer">
                                                                        <div class="block-right">
                                                                                        <div class="block-left"></div>
                                                                        </div>
                                                        </div>
                                                        
                                        </div>
                                </div></form><div style="padding-bottom:4px"></div>';
                        }
                }
                
                return $html;
        }
        
        /**
        * Function to print out a parents "yes or no" input field (based on radio buttons)
        *
        * @param       integer       configuration varname
        * @param       string        value
        * @param       string        description
        * @param       string        input type
        * @param       string        class (default alt1)
        *
        * @return      string        HTML representation of the Yes/No radio button input selection
        */
        function construct_parent_yesno_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $help = '')
	{
                global $ilance, $myapi, $ilconfig, $phrase, $iltemplate, $page_title, $area_title, $ilpage;
                
                $html = '
                <tr valign="top" class="' . $class . '">
                        <td align="left">
                                <div>
                                        <span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span>
                                        <span style="color:#444"><strong>' . stripslashes($description) . '</strong></span>
                                </div>
                                <div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div>
                        </td>
                        <td align="right">
                                <span style="white-space:nowrap">
                                        <label for="rb_1[' . $variableinfo . ']">
                                                <input type="radio" name="config[' . $variableinfo . ']" value="1" id="rb_1[' . $variableinfo . ']" ';
                                                if ($value == 1)
                                                {
                                                        $html .= 'checked="checked" ';
                                                }
                                                $html .= '>' . $phrase['_yes'] . '
                                        </label>
                                        <label for="rb_0[' . $variableinfo . ']">
                                                <input type="radio" name="config[' . $variableinfo . ']" value="0" id="rb_0[' . $variableinfo . ']" ';
                                                if ($value == 0)
                                                {
                                                        $html .= 'checked="checked" ';
                                                }
                                                $html .= '>' . $phrase['_no'] . '
                                        </label>
                                </span>
                        </td>
                        <td align="center" class="' . $class . '">
                                <input type="text" name="sort[' . $variableinfo . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" />
                        </td>
                </tr>';
                
                return $html;
	}
        
        /**
        * Function to print out a parents integer text input field
        *
        * @param       integer       configuration varname
        * @param       string        value
        * @param       string        description
        * @param       string        input type
        * @param       string        class (default alt1)
        *
        * @return      string        HTML representation of the integer text input selection
        */
        function construct_parent_int_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $help = '')
	{
                global $ilance, $myapi, $ilconfig, $phrase, $iltemplate, $page_title, $area_title, $ilpage;
                
                $html = '
                <tr class="' . $class . '" valign="top">
                        <td align="left">
                                <div>
                                        <span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span>
                                        <span style="color:#444"><strong>' . stripslashes($description) . '</strong></span>
                                </div>
                                <div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div>
                        </td>
                        <td align="right">';
                
                if ($inputtype == 'text')
                {
                        //$html .= '<span style="white-space:nowrap"><input type="text" name="config[' . $variableinfo . ']" value="' . htmlentities($value, ENT_QUOTES) . '" style="width:170px; text-align:center; font-family: verdana" /></span>';
                        $html .= '<span style="white-space:nowrap"><input type="text" name="config[' . $variableinfo . ']" value="' . ilance_htmlentities($value) . '" style="width:170px; text-align:center; font-family: verdana" /></span>';
                }
                else if ($inputtype == 'pass')
                {
                        //$html .= '<span style="white-space:nowrap"><input type="password" name="config[' . $variableinfo . ']" value="' . htmlentities($value, ENT_QUOTES) . '" style="width:170px; text-align:center; font-family: verdana" /></span>';
                        $html .= '<span style="white-space:nowrap"><input type="password" name="config[' . $variableinfo . ']" value="' . ilance_htmlentities($value) . '" style="width:170px; text-align:center; font-family: verdana" /></span>';
                }
                else if ($inputtype == 'textarea')
                {
                        //$html .= '<span style="white-space:nowrap"><textarea name="config[' . $variableinfo . ']" style="width: 325px; height: 84px; font-family: verdana" wrap="physical">' . htmlentities($value, ENT_QUOTES) . '</textarea></span>';
                        $html .= '<span style="white-space:nowrap"><textarea name="config[' . $variableinfo . ']" style="width: 325px; height: 84px; font-family: verdana" wrap="physical">' . ilance_htmlentities($value) . '</textarea></span>';
                }
                else if ($inputtype == 'int')
                {
                        //$html .= '<span style="white-space:nowrap"><input type="text" name="config[' . $variableinfo . ']" value="' . htmlentities($value, ENT_QUOTES) . '" style="width:70px; text-align:center; font-family: verdana" /></span>';
                        $html .= '<span style="white-space:nowrap"><input type="text" name="config[' . $variableinfo . ']" value="' . ilance_htmlentities($value) . '" style="width:70px; text-align:center; font-family: verdana" /></span>';
                }
                
                $html .= '</td>';
                $html .= '<td align="center" class="' . $class . '" valign="top"><input type="text" name="sort[' . $variableinfo . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana;" /></td>';
                $html .= '</tr>';
                
                return $html;
	}
        
        /**
        * Function to print out a parents textarea text input field
        *
        * @param       integer       configuration varname
        * @param       string        value
        * @param       string        description
        * @param       string        input type
        * @param       string        class (default alt1)
        *
        * @return      string        HTML representation of the integer textarea input selection
        */
        function construct_parent_textarea_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $help = '')
	{
                global $ilance, $myapi, $ilconfig, $phrase, $iltemplate, $page_title, $area_title, $ilpage;
                
                $html = '<tr class="' . $class . '" valign="top">
                        <td valign="top" align="left"><div><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><span style="color:#444"><strong>' . stripslashes($description) . '</strong></span></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right">';
                
                if ($inputtype == 'text')
                {
                        $html .= '<span style="white-space:nowrap"><input type="text" name="config[' . $variableinfo . ']" value="' . ilance_htmlentities($value) . '" style="width:200px; font-family: verdana" /></span>';
                }
                else if ($inputtype == 'pass')
                {
                        $html .= '<span style="white-space:nowrap"><input type="password" name="config[' . $variableinfo . ']" value="' . ilance_htmlentities($value) . '" style="width:200px; font-family: verdana" /></span>';
                }
                else if ($inputtype == 'textarea')
                {
                        $html .= '<span style="white-space:nowrap"><textarea name="config[' . $variableinfo . ']" style="width: 300px; height: 84px; font-family: verdana" wrap="physical">' . ilance_htmlentities($value) . '</textarea></span>';
                }
                else if ($inputtype == 'int')
                {
                        $html .= '<span style="white-space:nowrap"><input type="text" name="config[' . $variableinfo . ']" value="' . ilance_htmlentities($value) . '" style="width:200px; text-align:center; font-family: verdana" /></span>';
                }
                
                $html .= '</td>';
                $html .= '<td align="center" class="' . $class . '" valign="top"><input type="text" name="sort[' . $variableinfo . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" /></td>';
                $html .= '</tr>';
                
                return $html;
	}
        
        /**
        * Function to print out a parents pulldown menu input field
        *
        * @param       integer       configuration varname
        * @param       string        value
        * @param       string        description
        * @param       string        input type
        * @param       string        class (default alt1)
        *
        * @return      string        HTML representation of the integer textarea input selection
        */
        function construct_parent_pulldown_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $inputcode = '', $help = '')
	{
                global $ilance, $myapi, $ilconfig, $phrase, $iltemplate, $page_title, $area_title, $ilpage;
                
                $html = '<tr class="' . $class . '" valign="top"><td align="left"><div><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></span><span style="color:#444"><strong>' . stripslashes($description) . '</strong></span></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap">';
                $html .= ($variableinfo == 'globalserverlocale_defaultcurrency') ? $ilance->currency->pulldown('admin', $variableinfo) : $inputcode;
                $html .= '</span></td><td align="center" class="' . $class . '" valign="top"><input type="text" name="sort[' . $variableinfo . ']" value="' . $sort . '" style="width:30px; text-align:center; font-family: verdana" /></td></tr>';
                
                return $html;
	}
        
        /**
        * Function to remove & allow rebuild of the javascript phrase cache file
        *
        * @return      nothing
        */
        function rebuild_language_cache()
	{
		global $ilance, $myapi;
                
		$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
                        while ($languages = $ilance->db->fetch_array($sql))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "language_phrasegroups
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $cachename = '';
                                        while ($groups = $ilance->db->fetch_array($sql2))
                                        {
                                                $cachename = DIR_TMP . 'phrases[' . mb_substr($languages['languagecode'], 0, 3) . '][' . $groups['groupname'] . '].cache';
                                                if (file_exists($cachename))
                                                {
                                                        @unlink($cachename);
                                                }
                                        }
                                        // javascript also
                                        $cachename = DIR_TMP . 'phrases[' . mb_substr($languages['languagecode'], 0, 3) . '].js';
                                        if (file_exists($cachename))
                                        {
                                                @unlink($cachename);
                                        }
                                }
                        }
		}
	}
        
        /**
        * Function to fetch the schedule details of a task used in the self-cron automation system
        *
        * @param       array         array data of schedule
        *
        * @return      string        
        */
        function fetch_cron_schedule($cron)
        {
                global $phrase;
                
                $t = array('hour' => $cron['hour'], 'day' => $cron['day'], 'month' => -1, 'weekday' => $cron['weekday']);
                foreach($t as $field => $value)
                {
                        $t["$field"] = iif($value == -1, '*', $value);
                }
            
                if (is_numeric($cron['minute']))
                {
                        $cron['minute'] = array(0 => $cron['minute']);
                }
                else
                {
                        $cron['minute'] = unserialize($cron['minute']);
                        if (!is_array($cron['minute']))
                        {
                                $cron['minute'] = array(-1);
                        }
                }
            
                if ($cron['minute'][0] == -1)
                {
                        $t['minute'] = '*';
                }
                else
                {
                        $minutes = array();
                        foreach ($cron['minute'] as $nextminute)
                        {
                                $minutes[] = str_pad(intval($nextminute), 2, 0, STR_PAD_LEFT);
                        }
                        $t['minute'] = implode(', ', $minutes);
                }
                $days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                if ($t['weekday'] != '*')
                {
                        $day = $days[intval($t['weekday'])];
                        $t['weekday'] = $phrase['_'.$day];
                        $t['day'] = '*';
                }
                
                return $t;
        }
        
        /**
        * Function to fetch the task variable name for a given cron job within the automation system
        *
        * @param       integer       cron job id
        *
        * @return      string        HTML representation of the string
        */
        function fetch_task_varname($cronid)
	{
		global $ilance;
                
		$value = '';
		$sql = $ilance->db->query("
			SELECT varname
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '".intval($cronid)."'
		", 0, null, __FILE__, __LINE__);    
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			$value = $res['varname'];
		}
                
		return $value;
	}
        
        /**
        * Function to print out a phrase based on a text string for the scheduled task
        *
        * @param       string        selected task
        *
        * @return      string        HTML representation of the string
        */
        function scheduled_task_phrase($selected)
	{
		switch ($selected)
		{
			case 'subscriptions':
			$phrase = 'Subscriptions';
			break;
                        case 'store_subscriptions':
			$phrase = 'Store Subscriptions';
			break;
			case 'rfp':
                        case 'auctions':
			$phrase = 'Auctions';
			break;
			case 'reminders':
			$phrase = 'Reminders';
			break;
			case 'currency':
			$phrase = 'Currencies';
			break;
			case 'dailyreports':
			$phrase = 'Daily Reports';
			break;
			case 'dailyrfp':
			$phrase = 'Daily Newsletters';
			break;
			case 'creditcards':
			$phrase = 'Credit Card Cleanup';
			break;
			case 'warnings':
			$phrase = 'Warnings';
			break;
			case 'monthly':
			$phrase = 'Monthly Cleanup';
			break;
			case 'watchlist':
			$phrase = 'Watchlist';
			break;
			default:
			// turns some_task into Some Task
			$phrase = str_replace('_', ' ', $selected);
			$phrase = ucwords($phrase);
			break;
		}
                
		return $phrase;    
	}
        
        /**
        * Function to print out the scheduled tasks pulldown menu
        *
        * @param       string        selected option
        *
        * @return      string        HTML representation of the pulldown
        */
        function print_scheduled_tasks_pulldown($selected = '')
	{
		global $ilance, $ilconfig, $myapi, $phrase;
                
		$html = '<select name="cronid" id="cronid" tabindex="1" class="input">';
		if (empty($selected))
		{
			$html .= '<option value="0" selected="selected">All Tasks</option>';
		}
		else
		{
			$html .= '<option value="0">All Tasks</option>';
		}
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "cron
			ORDER BY cronid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql))
			{
				if (isset($selected) AND $selected == $res['cronid'])
				{
					$html .= '<option value="' . $res['cronid'] . '" selected="selected">'.$this->scheduled_task_phrase($res['varname']).'</option>';
				}
				else
				{
					$html .= '<option value="' . $res['cronid'] . '">' . $this->scheduled_task_phrase($res['varname']) . '</option>';
				}
			}
		}
		$html .= '</select>';
                
		return $html;
	}
        
        /**
        * Function to print out the profile filter types pulldown menu
        *
        * @param       string        selected option
        *
        * @return      string        HTML representation of the pulldown menu
        */
        function print_profile_filtertype_pulldown($selected = '')
	{
		global $ilance, $ilconfig, $myapi, $phrase;
                
		$html = '
                <select name="filtertype" style="font-family: verdana">
                        <option value="">' . $phrase['_select'] . ' &darr;</option>
                        <option value="pulldown"'; if (isset($selected) AND $selected == 'pulldown') { $html .= 'selected="selected"'; } $html .= '>Pulldown Menu (auction poster can enter single values)</option>
                        <option value="multiplechoice"'; if (isset($selected) AND $selected == 'multiplechoice') { $html .= 'selected="selected"'; } $html .= '>Multiple Choice (auction poster can enter multiple choice values)</option>
                        <option value="range"'; if (isset($selected) AND $selected == 'range') { $html .= 'selected="selected"'; } $html .= '>Range (auction poster can define from and to range values)</option>
                </select>';
                
		return $html;
	}
        
        /**
        * Function to generate a pulldown menu based on auction details.
        *
        * @param       integer       project id (or currently selected auction id value)
        * @param       bool          determines if we should display "Select all" as an option in the pulldown
        *
        * @return      string        HTML representation of the pulldown menu with auction type values
        */
        function auction_details_pulldown($selected = '', $showall = 0, $type = 'service')
        {
                global $ilance, $myapi, $phrase, $ilconfig;
                
                $html = '<select name="project_details" style="font-family: verdana">';
                if ($showall)
                {
                        $html .= '<option value="">' . $phrase['_all'] . '</option>';    
                }
                $html .= '<option value="public"'; if ($selected == "public") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_public'].'</option>';
                $html .= '<option value="invite_only"'; if ($selected == "invite_only") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_invite_only'].'</option>';
                $html .= '<option value="realtime"'; if ($selected == "realtime") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_realtime'].'</option>';
                if ($ilconfig['enable_uniquebidding'] AND $type == 'product')
                {
                        $html .= '<option value="unique"'; if ($selected == "unique") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_lowest_unique_bid'].'</option>';
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to generate a pulldown menu based on auction details.
        *
        * @param       string		 text which will be selected in the pulldown
        * @param       bool          determines if we should display "Select all" as an option in the pulldown
        * @param       string        auction type service/product
        *
        * @return      string        HTML representation of the pulldown menu with auction type values
        */
        function auction_details_pulldown2($selected = '', $showall = 0, $type = 'product')
        {
                global $ilance, $myapi, $phrase, $ilconfig;
                
                $html = '<select name="project_details2" style="font-family: verdana">';
                if ($showall)
                {
                        $html .= '<option value="">' . $phrase['_all'] . '</option>';    
                }
                $html .= '<option value="regular"'; if ($selected == "regular") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_regular'].'</option>';
                $html .= '<option value="fixed"'; if ($selected == "fixed") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_fixed'].'</option>';
                $html .= '</select>';
                
                return $html;
        }        
        
        /**
        * Function to generate a pulldown menu based on auction status details.
        *
        * @param       integer       project id (or currently selected auction id value)
        *
        * @return      string        HTML representation of the pulldown menu with auction status values
        */
        function auction_status_pulldown($selected = '', $showall = true, $type = 'service')
        {
                global $ilance, $myapi, $phrase;
                
                $html = '<select name="status" style="font-family: verdana">';
                if ($showall)
                {                
                        $html .= '<option value="">' . $phrase['_all'] . '</option>';
                }
                $html .= '<option value="open"'; if (isset($selected) AND $selected == "open") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_open'].'</option>';
                
                $html .= '<option value="closed"'; if (isset($selected) AND $selected == "closed") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_closed'].'</option>';
                $html .= '<option value="expired"'; if (isset($selected) AND $selected == "expired") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_ended'].'</option>';
                $html .= '<option value="delisted"'; if (isset($selected) AND $selected == "delisted") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_delisted'].'</option>';
                $html .= '<option value="archived"'; if (isset($selected) AND $selected == "archived") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_archived'].'</option>';
                $html .= '<option value="finished"'; if (isset($selected) AND $selected == "finished") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_finished'].'</option>';
                
                if ($type == 'service')
                {
                        $html .= '<option value="draft"'; if (isset($selected) AND $selected == "draft") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_draft'].'</option>';
                        $html .= '<option value="wait_approval"'; if (isset($selected) AND $selected == "wait_approval") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_waiting_for_acceptance_from_provider'].'</option>';
                        $html .= '<option value="approval_accepted"'; if (isset($selected) AND $selected == "approval_accepted") { $html .= ' selected="selected"'; } $html .= '>'.$phrase['_work_has_begun_provider_accepted_buyers_award'].'</option>';
                }
                
                $html .= '</select>';
                
                return $html;
        }
        /**
        * Function to generate a pulldown menu based on auction state details.
        *
        * @param       integer       project id (or currently selected auction id value)
        *
        * @return      string        HTML representation of the pulldown menu with auction state values
        */
        function auction_state_pulldown($projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $html = '';
                $html .= '<select name="project_state" style="font-family: verdana">';
                
                $sql = $ilance->db->query("
                        SELECT project_state
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if ($res['project_state'] == 'service')
                                {
                                        $html .= '<option value="service" selected="selected">' . $phrase['_service'] . '</option>';
                                        $html .= '<option value="product">' . $phrase['_product'] . '</option>';
                                }
                                else if ($res['project_state'] == 'product')
                                {
                                        $html .= '<option value="service">' . $phrase['_service'] . '</option>';
                                        $html .= '<option value="product" selected="selected">' . $phrase['_product'] . '</option>';
                                }
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to fetch the current auction enhancements details
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the auction enhancements details
        */
        function fetch_auction_enhancements_list($projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase, $ilpage, $iltemplate, $ilconfig;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT featured, highlite, bold, autorelist, buynow, reserve
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        $html .= ($res['featured'] > 0)
                                ? '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_featured" onclick="update_enhancement(\'' . intval($projectid) . '\', \'featured\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_featured'] . '</div>'
                                : '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_featured" onclick="update_enhancement(\'' . intval($projectid) . '\', \'featured\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_featured'] . '</div>';
                        
                        $html .= ($res['bold'] > 0)
                                ? '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_bold" onclick="update_enhancement(\'' . intval($projectid) . '\', \'bold\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_bold'] . '</div>'
                                : '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_bold" onclick="update_enhancement(\'' . intval($projectid) . '\', \'bold\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_bold'] . '</div>';
                                
                        $html .= ($res['highlite'] > 0)
                                ? '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_highlite" onclick="update_enhancement(\'' . intval($projectid) . '\', \'highlite\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_listing_highlight'] . '</div>'
                                : '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_highlite" onclick="update_enhancement(\'' . intval($projectid) . '\', \'highlite\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_listing_highlight'] . '</div>';
                                
                        $html .= ($res['autorelist'] > 0)
                                ? '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_autorelist" onclick="update_enhancement(\'' . intval($projectid) . '\', \'autorelist\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_autorelist'] . '</div>'
                                : '<div style="padding-bottom:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_enhancement_' . intval($projectid) . '_autorelist" onclick="update_enhancement(\'' . intval($projectid) . '\', \'autorelist\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /> ' . $phrase['_autorelist'] . '</div>';
                }
                
                return $html;
        }
        
        /**
        * Function to print the invoice types pulldown menu
        *
        * @param       string       invoice type
        *
        * @return      string       
        */
        function print_invoicetype_pulldown($invoicetype = '', $fieldname = 'invoicetype')
        {
                global $ilance, $phrase, $ilconfig;
                
                $html = '<select name="' . $fieldname . '" style="font-family: verdana"><optgroup label="' . $phrase['_revenue'] . '">';                
                $html .= '<option value="">' . $phrase['_all'] . ' ' . $phrase['_revenue'] . '</option>';
                
                ($apihook = $ilance->api('admincp_print_invoicetype_pulldown_fees_start')) ? eval($apihook) : false;
                
                $html .= '<option value="subscription"'; if (isset($invoicetype) AND $invoicetype == 'subscription') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_subscription_fees_revenue'] . '</option>';
                $html .= '<option value="credential"'; if (isset($invoicetype) AND $invoicetype == 'credential')     { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_credential_verification_fees_revenue'] . '</option>';
                $html .= '<option value="portfolio"'; if (isset($invoicetype) AND $invoicetype == 'portfolio')       { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_featured_portfolio_fees_revenue'] . '</option>';
                $html .= '<option value="enhancements"'; if (isset($invoicetype) AND $invoicetype == 'enhancements') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_listing_enhancement_fees_revenue'] . '</option>';
                $html .= '<option value="fvf"'; if (isset($invoicetype) AND $invoicetype == 'fvf') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_final_value_fees_revenue'] . '</option>';
                $html .= '<option value="insfee"'; if (isset($invoicetype) AND $invoicetype == 'insfee') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_insertion_fees_revenue'] . '</option>';
                if ($ilconfig['escrowsystem_enabled'])
                {
                        $html .= '<option value="escrow"'; if (isset($invoicetype) AND $invoicetype == 'escrow') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_escrow_commission_fees_revenue'] . '</option>';
                }
                $html .= '<option value="withdraw"'; if (isset($invoicetype) AND $invoicetype == 'withdraw') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_withdraw_fees_revenue'] . '</option>';
                $html .= '<option value="p2bfee"'; if (isset($invoicetype) AND $invoicetype == 'p2bfee') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_provider_to_buyer_generated_invoice_fees_revenue'] . '</option>';
                
                ($apihook = $ilance->api('admincp_print_invoicetype_pulldown_fees_end')) ? eval($apihook) : false;
                
                $html .= '</optgroup>';
                $html .= '<optgroup label="' . $phrase['_other'] . '">';
                $html .= '<option value="p2b"'; if (isset($invoicetype) AND $invoicetype == 'p2b') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_provider_to_buyer_generated_invoice'] . '</option>';
                if ($ilconfig['enablenonprofits'])
                {
                        $html .= '<option value="donationfee"'; if (isset($invoicetype) AND $invoicetype == 'donationfee') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_final_value_donation_fees_collected_nonprofits'] . '</option>';
                }
                
                $html .= '<option value="deposits"'; if (isset($invoicetype) AND $invoicetype == 'deposits') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_user_account_balance_deposits'] . '</option>';
                $html .= '<option value="withdraws"'; if (isset($invoicetype) AND $invoicetype == 'withdraws') { $html .= ' selected="selected"'; } $html .= '>&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_user_account_balance_withdrawals'] . '</option>';
                
                ($apihook = $ilance->api('admincp_print_invoicetype_pulldown_other_end')) ? eval($apihook) : false;
                
                $html .= '</optgroup>';
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print the "migrate to" pulldown menu for subscription plans within the AdminCP
        *
        * @param       string       selected option
        *
        * @return      string       
        */
        function print_migrate_to_pulldown($selected = '')
        {
                global $ilance, $phrase;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "subscription
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '<select name="migratetoid" style="font-family:verdana">';
                        $html .= '<option value="none">'.$phrase['_no_migration_subscription_logic'].'</option>';
                        $html .= '<optgroup label="'.$phrase['_migrate_to_subscription'].'">';
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '<option value="'.$res['subscriptionid'].'"';
                                if (isset($selected) AND $res['subscriptionid'] == $selected)
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . stripslashes($res['title']) . ' (' . $res['length'] . print_unit($res['units']) . ')</option>';
                        }
                        $html .= '</optgroup>';
                        $html .= '</select>';
                }
                else
                {
                        $html = $phrase['_no_subscription_plans_to_migrate_users'];
                }
                
                return $html;
        }
        
        /**
        * Function to print the migration billing pulldown menu
        *
        * @param       string       selected option
        *
        * @return      string       
        */
        function print_migrate_billing_pulldown($selected = '')
        {
                global $ilance, $phrase;
                
                $s1 = '';
                if (isset($selected) AND $selected == 'waived')
                {
                        $s1 = 'selected="selected"';
                }
                $s2 = '';
                if (isset($selected) AND $selected == 'unpaid')
                {
                        $s2 = 'selected="selected"';
                }
                $s3 = '';
                if (isset($selected) AND $selected == 'paid')
                {
                        $s3 = 'selected="selected"';
                }
                
                $html  = '<select name="migratelogic" style="font-family: verdana">';
                $html .= '<option value="none">No migration billing logic</option>';
                $html .= '<optgroup label="Migration Billing Logic">';
                $html .= '<option value="waived" ' . $s1 . '>Migrate plan and waive invoice (free)</option>';
                $html .= '<option value="unpaid" ' . $s2 . '>Migrate plan and generate unpaid invoice</option>';
                $html .= '<option value="paid" ' . $s3 . '>Migrate plan and generate a paid invoice</option>';
                $html .= '</optgroup>';
                $html .= '</select>';
                
                return $html;
        }
        
        function fetch_master_phrases_count()
        {
                global $ilance;
                
                $count = 0;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE ismaster = '1'                        
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                
                $count = (int)$res['count'];
                
                return $count;
        }
        
        function fetch_custom_phrases_count()
        {
                global $ilance;
                
                $count = 0;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE ismaster = '0'  
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                
                $count = (int)$res['count'];
                
                return $count;
        }
        
        function fetch_moved_phrases_count()
        {
                global $ilance;
                
                $count = 0;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE (isupdated = '1' OR ismoved = '1')
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                
                $count = (int)$res['count'];
                
                return $count;
        }
        
        function fetch_total_phrases_count()
        {
                global $ilance;
                
                $count = 0;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                
                $count = (int)$res['count'];
                
                return $count;
        }
        
        function fetch_email_department_count($departmentid = 0)
        {
                global $ilance;
                
                $count = 0;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "email
                        WHERE departmentid = '" . intval($departmentid) . "'
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                
                $count = (int)$res['count'];
                if ($count == 0)
                {
                        return '-';
                }
                
                return $count;
        }
               
        function fetch_latest_news()
        {
                global $ilance, $ilpage, $phrase, $ilconfig, $xml_output;
                
                ($apihook = $ilance->api('cron_fetch_latest_news_end')) ? eval($apihook) : false;
                
                return $cronlog;
        }
        
        function fetch_admincp_news()
        {
                global $ilance, $ilpage, $phrase, $show, $ilconfig;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT newsid, content, datetime, visible
                        FROM " . DB_PREFIX . "admincp_news
                        WHERE visible = '1'
                        ORDER BY newsid DESC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['admincpnews'] = true;
                        
                        $html .= '<div class="gray" style="font-size:14px">' . $phyrase['_news_from_team_ilance'] . '</div>';
                        
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '
                                <div style="padding-top:7px">
                                <div>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px 2px 15px 0px;" dir="' . $ilconfig['template_textdirection'] . '">
                                <tr>
                                        <td>
                                                <div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_gray_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div>
                                                <table border="0" cellpadding="0" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                <tr>
                                                        <td style="padding-left:5px;" valign="top"></td>
                                                        <td><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer.gif" width="5" height="1"></td>
                                                        <td style="padding-right:5px"><div class="smaller gray">(<a href="http://news.ilance.com" target="_blank">news.ilance.com</a>) - ' . print_date($res['datetime']) . '</div><div style="padding-top:7px">' . nl2br(stripslashes(html_entity_decode($res['content']))) . '</div></div><div class="smaller blue" style="padding-top:12px">[ <a href="' . $ilpage['dashboard'] . '?cmd=news&amp;do=dismiss&amp;id=' . $res['newsid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\');">' . $phrase['_dismiss'] . '</a> ]</div></td>
                                                </tr>
                                                </table>
                                                </div><div class="s"><div class="e"><div class="w"></div></div></div></div>
                                        </td>
                                </tr>
                                </table>
                                </div>';
                        }
                }
                else
                {
                        $show['admincpnews'] = false;
                }                
                
                return $html;
        }
        
        function construct_revenue_balance()
        {
                global $ilance, $phrase, $ilconfig;
                
                $array = array(
                        'subscription1' => '',
                        'subscription2' => '',
                        'subscription3' => '',
                        'subscription4' => '',
                        'credential1' => '',
                        'credential2' => '',
                        'credential3' => '',
                        'credential4' => '',
                        'portfolio1' => '',
                        'portfolio2' => '',
                        'portfolio3' => '',
                        'portfolio4' => '',
                        'listing1' => '',
                        'listing2' => '',
                        'listing3' => '',
                        'listing4' => '',
                        'fvf1' => '',
                        'fvf2' => '',
                        'fvf3' => '',
                        'fvf4' => '',
                        'if1' => '',
                        'if2' => '',
                        'if3' => '',
                        'if4' => '',
                        'escrowfees1' => '',
                        'escrowfees2' => '',
                        'escrowfees3' => '',
                        'escrowfees4' => '',
                        'withdraw1' => '',
                        'withdraw2' => '',
                        'withdraw3' => '',
                        'withdraw4' => '',
                        'p2b1' => '',
                        'p2b2' => '',
                        'p2b3' => '',
                        'p2b4' => '',
                        'totalsgenerated' => '',
                        'paidgenerated' => '',
                        'owinggenerated' => '',
                        'overduegenerated' => '');
                
                return $array;        
        }
        
        function construct_escrow_balance()
        {
                global $ilance, $phrase, $ilconfig;
                
                // service totals and count
                $totalsgenerated = $serviceescrow1 = $serviceescrow2 = $serviceescrow3 = $serviceescrow4 = 0;
                $serviceescrow = $ilance->db->query("
                        SELECT e.escrowamount
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE e.user_id = u.user_id
                            AND e.status != 'cancelled'
                            AND e.bid_id = b.bid_id
                            AND e.user_id = b.user_id
                            AND e.project_id = p.project_id
                            AND e.invoiceid = i.invoiceid
                            AND i.invoicetype = 'escrow'
                            AND p.project_state = 'service'
                            AND i.projectid = e.project_id
                ");
                if ($ilance->db->num_rows($serviceescrow) > 0)
                {
                        while ($res = $ilance->db->fetch_array($serviceescrow))
                        {
                                $serviceescrow1 += $res['escrowamount'];                
                        }
                        
                        $totalsgenerated += $serviceescrow1;
                }
		
                $serviceescrow1 = $ilance->currency->format($serviceescrow1);
                $serviceescrow2 = $ilance->db->num_rows($serviceescrow);
                unset($serviceescrow);
                
                // service buyer and seller fees
                $serviceescrow = $ilance->db->query("
                        SELECT e.fee, e.fee2
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE e.user_id = u.user_id
                            AND e.status != 'cancelled'
                            AND e.bid_id = b.bid_id
                            AND e.user_id = b.user_id
                            AND e.project_id = p.project_id
                            AND e.invoiceid = i.invoiceid
                            AND i.invoicetype = 'escrow'
                            AND p.project_state = 'service'
                            AND i.projectid = e.project_id
                ");
                if ($ilance->db->num_rows($serviceescrow) > 0)
                {
                        while ($res = $ilance->db->fetch_array($serviceescrow))
                        {
                                $serviceescrow3 += $res['fee'];
                                $serviceescrow4 += $res['fee2'];                
                        }
                }
		
                $serviceescrow3 = $ilance->currency->format($serviceescrow3);
                $serviceescrow4 = $ilance->currency->format($serviceescrow4);
                unset($serviceescrow);
                
                //
                
                // product totals and count
                $totalsgenerated = $productescrow1 = $productescrow2 = $productescrow3 = $productescrow4 = 0;
                $productescrow = $ilance->db->query("
                        SELECT e.escrowamount
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE e.user_id = u.user_id
                            AND e.status != 'cancelled'
                            AND e.bid_id = b.bid_id
                            AND e.user_id = b.user_id
                            AND e.project_id = p.project_id
                            AND e.invoiceid = i.invoiceid
                            AND i.invoicetype = 'escrow'
                            AND p.project_state = 'product'
                            AND i.projectid = e.project_id
                ");
                if ($ilance->db->num_rows($productescrow) > 0)
                {
                        while ($res = $ilance->db->fetch_array($productescrow))
                        {
                                $productescrow1 += $res['escrowamount'];                
                        }
                        
                        $totalsgenerated += $productescrow1;
                }
		
                $productescrow1 = $ilance->currency->format($productescrow1);
                $productescrow2 = $ilance->db->num_rows($productescrow);
                unset($productescrow);
                
                // product buyer and seller fees
                $productescrow = $ilance->db->query("
                        SELECT e.fee, e.fee2
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE e.user_id = u.user_id
                            AND e.status != 'cancelled'
                            AND e.bid_id = b.bid_id
                            AND e.user_id = b.user_id
                            AND e.project_id = p.project_id
                            AND e.invoiceid = i.invoiceid
                            AND i.invoicetype = 'escrow'
                            AND p.project_state = 'product'
                            AND i.projectid = e.project_id
                ");
                if ($ilance->db->num_rows($productescrow) > 0)
                {
                        while ($res = $ilance->db->fetch_array($productescrow))
                        {
                                $productescrow3 += $res['fee2'];
                                $productescrow4 += $res['fee'];                
                        }
                }
		
                $productescrow3 = $ilance->currency->format($productescrow3);
                $productescrow4 = $ilance->currency->format($productescrow4);
                unset($productescrow);
                
                $array = array(
                        'serviceescrow1' => $serviceescrow1,
                        'serviceescrow2' => $serviceescrow2,
                        'serviceescrow3' => $serviceescrow3,
                        'serviceescrow4' => $serviceescrow4,
                        'productescrow1' => $productescrow1,
                        'productescrow2' => $productescrow2,
                        'productescrow3' => $productescrow3,
                        'productescrow4' => $productescrow4,
                        'buynowescrow1' => '',
                        'buynowescrow2' => '',
                        'buynowescrow3' => '',
                        'buynowescrow4' => '',
                        'totalsgenerated' => '',
                        'totalaccounts' => '',
                        'buyerrevenue' => '',
                        'sellerrevenue' => '');
                
                return $array;        
        }
        
        function print_from_to_date_range()
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                $reportfromrange = '<table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '"><tr><td width="11%" nowrap><input type="text" name="range_start[0]" size="3" style="font-family: verdana; background-color:#fff" value="';
                $reportfromrange .= (empty($ilance->GPC['range_start'][0])) ? '01' : $ilance->GPC['range_start'][0];
                
                $reportfromrange .= '"> /&nbsp;</td><td width="9%" nowrap><input type="text" name="range_start[1]" size="3" style="font-family: verdana; background-color:#fff" value="';
                $reportfromrange .= (empty($ilance->GPC['range_start'][1])) ? '01' : $ilance->GPC['range_start'][1];
                
                $reportfromrange .= '"> /&nbsp;</td><td width="13%" valign="top" nowrap><div align="left"><input type="text" name="range_start[2]" size="5" style="font-family: verdana; background-color:#fff" value="';
                $reportfromrange .= (empty($ilance->GPC['range_start'][2])) ? date("Y") : $ilance->GPC['range_start'][2];
                
                $reportfromrange .= '"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase['_to_upper'] . '</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td><td width="9%" nowrap><input type="text" name="range_end[0]" style="font-family: verdana; background-color:#ebebeb" size="3" value="';
                $reportfromrange .= (empty($ilance->GPC['range_end'][0])) ? date("m") : $ilance->GPC['range_end'][0];
                
                $reportfromrange .= '"> /&nbsp;</td><td width="14%" nowrap><input type="text" name="range_end[1]" style="font-family: verdana; background-color:#ebebeb" size="3" value="';
                $reportfromrange .= (empty($ilance->GPC['range_end'][1])) ? date("d") : $ilance->GPC['range_end'][1];
                
                $reportfromrange .= '"> /&nbsp;</td><td width="100%" nowrap><input type="text" name="range_end[2]" style="font-family: verdana; background-color:#ebebeb" size="5" value="';
                $reportfromrange .= (empty($ilance->GPC['range_end'][2])) ? date("Y") : $ilance->GPC['range_end'][2];
                
                $reportfromrange .= '"></td></tr><tr><td nowrap class="smaller">Month</td><td nowrap class="smaller">Day</td><td nowrap class="smaller">Year</td><td nowrap class="smaller">Month</td><td nowrap class="smaller">Day</td><td nowrap class="smaller">Year</td></tr></table>';
                
                return $reportfromrange;
        }
        
        /**
	* Function to remove single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for removal
	* @param        bool            remove invoices for user? (default no)
	* @param        bool            remove listings for user? (default no)
	* @param        bool            remove escrow for user? (default no)
	* @param        bool            remove buynow orders for user? (default no)
	* @param        bool            remove bids for user? (default no)
	*
	* @return       string          Returns HTML string of users removed separated by a comma for display purposes
	*/
        function remove_user($ids = array(), $removeinvoices = false, $removelistings = true, $removeescrow = false, $removebuynoworders = false, $removebids = false)
        {
                global $ilance;
                
                $removedusers = '';
                
                foreach ($ids AS $inc => $userid)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $removedusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
                                
                                ($apihook = $ilance->api('admincp_remove_user_start')) ? eval($apihook) : false;
                                
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE buyer_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE seller_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "audit WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "bankaccounts WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "creditcards WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "emaillog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "invoicelog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "portfolio WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "register_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_categories WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_filter_auction_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE buyer_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE seller_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "referral_data WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "sessions WHERE userid = '" . intval($userid) . "' AND isuser = '1'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscriptionlog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_user WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_user_exempt WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "users WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE watching_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "search_users WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "search_favorites WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "sessions WHERE userid = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "skills_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "calendar WHERE userid = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("DELETE FROM " . DB_PREFIX . "feedback_ratings WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                
                                // invoices
                                if ($removeinvoices)
                                {
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "invoices WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "invoices WHERE p2b_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                }
                                
                                // projects
                                if ($removelistings)
                                {
                                        $sql = $ilance->db->query("SELECT cid FROM " . DB_PREFIX . "projects WHERE user_id = '" . intval($userid) . "' AND STATUS = 'open'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "bulk_tmp WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        if($ilance->db->num_rows($sql) > 0)
                                        {
                                        		$res = $ilance->db->fetch_array($sql);
                                        		foreach($res AS $key => $value)
	                                        	{
	                                        			build_category_count($value, 'subtract', "admin removing multiple users from admincp: subtracting increment count category id $value");
	                                        	}
                                        }
                                }
                                
                                // projects escrow
                                if ($removeescrow)
                                {
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE user_id = '" . intval($ilance->GPC['id']) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE project_user_id = '" . intval($ilance->GPC['id']) . "'", 0, null, __FILE__, __LINE__);
                                }
                                
                                // buy now orders
                                if ($removebuynoworders)
                                {
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE owner_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE buyer_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                }
                                
                                // bids
                                if ($removebids)
                                {
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_uniquebids WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_uniquebids WHERE project_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "project_realtimebids WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                        $ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
                                }
                                
                                ($apihook = $ilance->api('admincp_remove_user_end')) ? eval($apihook) : false;
                        }
                }
                
                return $removedusers;
        }
        
        /**
	* Function to suspend single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for suspension
	*
	* @return       string          Returns HTML string of users suspended separated by a comma for display purposes
	*/
        function suspend_user($ids = array())
        {
                global $ilance;
                
                $suspendusers = '';
                
                foreach ($ids AS $inc => $userid)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $suspendusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
                                
                                ($apihook = $ilance->api('admincp_suspend_user_start')) ? eval($apihook) : false;
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET status = 'suspended'
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                ($apihook = $ilance->api('admincp_suspend_user_end')) ? eval($apihook) : false;
                        }
                }
                
                return $suspendusers;
        }
        
        /**
	* Function to activate single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for activation
	*
	* @return       string          Returns HTML string of users activated separated by a comma for display purposes
	*/
        function activate_user($ids = array())
        {
                global $ilance;
                
                $activatedusers = '';

                foreach ($ids AS $inc => $userid)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $activatedusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
                                
                                ($apihook = $ilance->api('admincp_activate_user_start')) ? eval($apihook) : false;
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET status = 'active'
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                ($apihook = $ilance->api('admincp_activate_user_end')) ? eval($apihook) : false;
                        }
                }
                
                return $activatedusers;
        }
        
        /**
	* Function to ban single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for ban
	*
	* @return       string          Returns HTML string of users banned separated by a comma for display purposes
	*/
        function ban_user($ids = array())
        {
                global $ilance;
                
                $bannedusers = '';
                
                foreach ($ids AS $inc => $userid)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $bannedusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
                                
                                ($apihook = $ilance->api('admincp_ban_user_start')) ? eval($apihook) : false;
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET status = 'banned'
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                ($apihook = $ilance->api('admincp_ban_user_end')) ? eval($apihook) : false;
                        }
                }
                
                return $bannedusers;
        }
        
        /**
	* Function to ban single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for ban
	*
	* @return       string          Returns HTML string of users banned separated by a comma for display purposes
	*/
        function cancel_user($ids = array())
        {
                global $ilance;
                
                $cancelledusers = '';
                
                foreach ($ids AS $inc => $userid)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $cancelledusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
                                
                                ($apihook = $ilance->api('admincp_cancel_user_start')) ? eval($apihook) : false;
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET status = 'cancelled'
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                ($apihook = $ilance->api('admincp_cancel_user_end')) ? eval($apihook) : false;
                        }
                }
                
                return $cancelledusers;
        }
        
        /**
	* Function to unverify single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for unverification
	*
	* @return       string          Returns HTML string of users unverified separated by a comma for display purposes
	*/
        function unverify_user($ids = array())
        {
                global $ilance;
                
                $unverifiedusers = '';
                
                foreach ($ids AS $inc => $userid)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $unverifiedusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
                                
                                ($apihook = $ilance->api('admincp_unverify_user_start')) ? eval($apihook) : false;
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET status = 'unverified'
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                ($apihook = $ilance->api('admincp_unverify_user_end')) ? eval($apihook) : false;
                        }
                }
                
                return $unverifiedusers;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>