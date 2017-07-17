<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base1.0.8 Build 85
|| # -------------------------------------------------------------------- # ||
|| # Customer License # KapIxNXTSUYf3LjCGHiWk1XevwZ-ISZStLboZ-ErQdU-pATvJ3
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

if (!defined('LOCATION') OR defined('LOCATION') AND LOCATION != 'admin')
{
	echo 'This script cannot be parsed indirectly.';
	exit();
}

$sqlfolder = $ilance->db->query("
        SELECT folder
        FROM " . DB_PREFIX . "modules_group
        WHERE modulegroup = 'lancekb'
");
if ($ilance->db->num_rows($sqlfolder) > 0)
{
	$resfolder = $ilance->db->fetch_array($sqlfolder);
}

// #### UPDATE DISPLAY ORDER FOR CATEGORIES ####################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatesort')
{
	if (isset($ilance->GPC['sort']) AND $ilance->GPC['sort'] != '')
	{
		foreach ($ilance->GPC['sort'] AS $key => $value)
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "kbcategory
                                SET sort = '" . intval($value) . "'
                                WHERE categoryid = '" . intval($key) . "'
                                LIMIT 1
                        ");
		}
		print_action_success("Category ID sorting was saved.", $ilpage['components'] . '?module=lancekb');
		exit();
	}
}

// #### REMOVE CATEGORY ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-category' AND isset($ilance->GPC['catid']) AND $ilance->GPC['catid'] > 0)
{
        $res = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "kbposts
                WHERE catid = '" . intval($ilance->GPC['catid']) . "'
        ");
	while ($obj = $ilance->db->fetch_object($res))
        {
                $tid = intval($obj->postsid);
                
		$ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "attachment
                        WHERE tblfolder_ref = '" . $tid . "'
                                AND attachtype = 'kb'
                ");
		
		$ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "kbratings
                        WHERE postsid = '" . $tid . "'
                ");
	}
        
	$ilance->db->query("
                DELETE FROM " . DB_PREFIX . "kbcategory
                WHERE categoryid = '" . intval($ilance->GPC['catid']) . "'
        ");
	
	$ilance->db->query("
                DELETE FROM " . DB_PREFIX . "kbcategory
                WHERE parent = '" . intval($ilance->GPC['catid']) . "'
        ");
	
	$ilance->db->query("
                DELETE FROM " . DB_PREFIX . "kbposts
                WHERE catid = '" . intval($ilance->GPC['catid']) . "'
        ");
	
	print_action_success("Knowledge base category ID <strong>".intval($ilance->GPC['catid'])."</strong> was removed from the database.", $ilpage['components'] . '?module=lancekb');
}

// #### ADD CATEGORY ###########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-category' AND isset($ilance->GPC['module']) AND $ilance->GPC['module'] == 'lancekb')
{
	$ilance->db->query("
                INSERT INTO " . DB_PREFIX . "kbcategory
                (catname, parent, description, adminaccess) 
                VALUES(
		'" . $ilance->db->escape_string($ilance->GPC['catname']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['parent']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['desc']) . "',
                'N')
        ");
        
	print_action_success("New category <strong>".$ilance->GPC['catname']."</strong> was added to the knowledge base system.", $ilpage['components'] . '?module=lancekb');
}

// #### ADD ARTICLE ############################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-article' AND isset($ilance->GPC['module']) AND $ilance->GPC['module'] == 'lancekb')
{
        $message = '';
	if (!empty($ilance->GPC['message']))
	{
		$message = $ilance->GPC['message'];
	}
	
	$ilance->db->query("
                INSERT INTO " . DB_PREFIX . "kbposts
                (postsid, catid, name, email, approved, adminaccess, subject, answer, keywords, insdate, moddate)  
                VALUES(
                NULL,
                '" . intval($ilance->GPC['catid']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['name']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['email']) . "',
                '1',
                '" . $ilance->db->escape_string($ilance->GPC['adminaccess']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['subject']) . "',
                '" . $ilance->db->escape_string($message) . "',
                '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
                '" . DATETIME24H . "',
                '" . DATETIME24H . "')
        ");
        
	$newfileid = $ilance->db->insert_id();
        
	if (!empty($attachment_name))
        {
		$filehash = md5(time());
		$temp_file_name = trim($_FILES['attachment']['tmp_name']);
		$file_name = trim(mb_strtolower($_FILES['attachment']['name']));
		$file_type = trim(mb_strtolower($_FILES['attachment']['type']));
		$file_size = $_FILES['attachment']['size'];
		$upload_dir = DIR_KB_ATTACHMENTS;
		if (move_uploaded_file($temp_file_name, $upload_dir . $filehash . '.attach'))
                {
			$upload_file_size = filesize($upload_dir . $filehash . '.attach');
			if ($ilconfig['attachment_dbstorage'])
                        {
				$filedata = addslashes(fread(fopen($upload_dir . $filehash . '.attach', "rb"), filesize($upload_dir . $filehash . '.attach')));
				@unlink($upload_dir . $filehash . '.attach');
				@unlink($temp_file_name);
			}
			else
                        {
				$filedata = '';
			}
			
			$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, date, filename, filedata, filetype, visible, counter,
                                filesize, filehash, ipaddress, tblfolder_ref)
                                VALUES(
                                NULL,
                                'kb',
                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($file_name) . "',
                                '" . $filedata . "',
                                '" . trim($ilance->db->escape_string($file_type)) . "',
                                '" . $ilconfig['attachment_moderationdisabled'] . "',
                                '0',
                                '" . $ilance->db->escape_string($upload_file_size) . "',
                                '" . $ilance->db->escape_string($filehash) . "',
                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                '" . intval($newfileid) . "')
                        ");
		}
	}
        
	print_action_success("New article \"<strong>".$ilance->GPC['name']."</strong>\" was saved to the knowledge base system.", $ilpage['components'] . '?module=lancekb');
}

// #### UPDATE ARTICLE #########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-article' AND isset($ilance->GPC['module']) AND $ilance->GPC['module'] == 'lancekb')
{
	$approved = isset($ilance->GPC['approved']) ? 1 : 0;
	$subject = $ilance->GPC['subject'];
	$message = '';
	if (!empty($ilance->GPC['message']))
	{
		$message = $ilance->GPC['message'];
	}
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "kbposts
		SET catid = '" . intval($ilance->GPC['catid']) . "',
		name = '" . $ilance->db->escape_string($ilance->GPC['name']) . "',
		email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "',
		subject = '" . $ilance->db->escape_string($subject) . "',
		answer = '" . $ilance->db->escape_string($message) . "',
		keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
		approved = '" . $approved . "',
		moddate = '" . DATETIME24H . "'
		WHERE postsid = '" . intval($ilance->GPC['id']) . "'
	");
        
	print_action_success("Article \"<strong>$subject</strong>\" was successfully re-saved to the database.", $ilpage['components'] . '?module=lancekb');
}

// #### UPDATE COMMENT SETTINGS ################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-comment-settings' AND isset($ilance->GPC['module']) AND $ilance->GPC['module'] == 'lancekb')
{
	if (isset($ilance->GPC['setting']) AND $ilance->GPC['setting'] != "")
        {
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "kb_configuration
			SET value = '".intval($ilance->GPC['setting'])."'
			WHERE name = 'moderation'
		");
	}
	
	if (isset($ilance->GPC['csetting']) AND $ilance->GPC['csetting'] != "")
        {
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "kb_configuration
			SET value = '".intval($ilance->GPC['csetting'])."'
			WHERE name = 'enablecomments'
		");
	}
	
	if (isset($ilance->GPC['esetting']) AND $ilance->GPC['esetting'] != "")
        {
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "kb_configuration
			SET value = '".intval($ilance->GPC['esetting'])."'
			WHERE name = 'enablekb'
		");
	}
        
	print_action_success("Article comment settings have been saved to the database.", $ilpage['components'] . '?module=lancekb');
}

// #### UPDATE CATEGORY ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-category' AND $ilance->GPC['catid'] > 0)
{
	if ($ilance->GPC['catid'] == $ilance->GPC['parent'])
	{
		print_action_failed("Category update failed because the parent category cannot be the same as the main category.  Please try again by selecting a different parent category.", $ilpage['components'] . '?module=lancekb');
		exit();
	}
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "kbcategory
		SET catname = '" . $ilance->db->escape_string($ilance->GPC['catname']) . "', 
		parent = '" . intval($ilance->GPC['parent']) . "', 
		description = '" . $ilance->db->escape_string($ilance->GPC['desc']) . "'
		WHERE categoryid = '" . intval($ilance->GPC['catid']) . "'
	");
        
	print_action_success("Article category settings have been saved to the database.", $ilpage['components'] . '?module=lancekb');
}

// #### ENABLE MEMBERS-ONLY OPTION TO CATEGORY #################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_membersonly-enable' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "kbcategory
		SET adminaccess = 'Y'
		WHERE categoryid = '".intval($ilance->GPC['id'])."'
	");
        
	print_action_success("Article category ID <strong>".intval($ilance->GPC['id'])."</strong> was changed to members-only access [requires customer to be logged-in to their account]", $ilpage['components'] . '?module=lancekb');
}

// #### DISABLE MEMBERS-ONLY OPTION TO CATEGORY ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_membersonly-disable' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "kbcategory
		SET adminaccess = 'N'
		WHERE categoryid = '".intval($ilance->GPC['id'])."'
	");
        
	print_action_success("Article category ID <strong>".intval($ilance->GPC['id'])."</strong> was changed to allow guest view access.", $ilpage['components'] . '?module=lancekb');
}

// #### APPROVE COMMENT ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_approve-comment' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "kbcomments
		SET approved = '1'
		WHERE commentsid = '" . intval($ilance->GPC['id']) . "'
	");
        
	print_action_success("Comment ID <strong>".intval($ilance->GPC['id'])."</strong> was approved for public viewing.", $ilpage['components'] . '?module=lancekb');
}
// #### APPROVE COMMENT ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_unapprove-comment' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "kbcomments
		SET approved = '0'
		WHERE commentsid = '" . intval($ilance->GPC['id']) . "'
	");
        
	print_action_success("Comment ID <strong>".intval($ilance->GPC['id'])."</strong> was unapproved.", $ilpage['components'] . '?module=lancekb');
}

// #### REMOVE COMMENT #########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-comment' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "kbcomments
		WHERE commentsid = '".intval($ilance->GPC['id'])."'
		LIMIT 1
	");
        
	print_action_success("Comment ID <strong>".intval($ilance->GPC['id'])."</strong> was removed from the database.", $ilpage['components'] . '?module=lancekb');
}

// #### REMOVE ARTICLE #########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-article' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
                DELETE FROM " . DB_PREFIX . "kbposts
                WHERE postsid = '".intval($ilance->GPC['id'])."'
                LIMIT 1
        ");
    
	$ilance->db->query("
                DELETE FROM " . DB_PREFIX . "attachment
                WHERE tblfolder_ref = '".intval($ilance->GPC['id'])."'
                        AND attachtype = 'kb'
                LIMIT 1
        ");
        
	print_action_success("Article ID <strong>".intval($ilance->GPC['id'])."</strong> was removed from the database along with any attachments tied to the article.", $ilpage['components'] . '?module=lancekb');
}

// #### REMOVE ARTICLE ATTACHMENT ##############################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-article-attachment' AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
                DELETE FROM " . DB_PREFIX . "attachment
                WHERE attachid = '".intval($ilance->GPC['id'])."'
                        AND attachtype = 'kb'
                LIMIT 1
        ");
        
	print_action_success("Article attachment ID <strong>".intval($ilance->GPC['id'])."</strong> was removed from the database.", $ilpage['components'] . '?module=lancekb');
}

// #### UPDATE SETTINGS ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-settings')
{
        foreach($_POST as $key => $value)
        {
                if (isset($value))
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "kb_configuration 
                                SET value = '".$ilance->db->escape_string($value)."'
                                WHERE name = '".$ilance->db->escape_string($key)."'
                                LIMIT 1
                        ");
                }
        }
        
        print_action_success("Main settings have been saved. New changes should take effect immediately.", $ilpage['components'] . '?module=lancekb');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Tue, Jan 11th, 2011
|| ####################################################################
\*======================================================================*/
?>