<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Community Forum 3.2.0 Build 1105
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2010 ILance Inc. All Rights Reserved.                # ||
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
        WHERE modulegroup = 'lancebb'
");
if ($ilance->db->num_rows($sqlfolder) > 0)
{
	$resfolder = $ilance->db->fetch_array($sqlfolder);
}

// #### UPDATE FORUM CATEGORY SORTING ##########################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatesort')
{
	if (isset($ilance->GPC['sort']))
	{
		foreach ($ilance->GPC['sort'] as $key => $value)
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "forum_categories
                                SET sort = '" . intval($value) . "'
                                WHERE fid = '" . intval($key) . "'
                                LIMIT 1
                        ");
		}
                
		print_action_success("Forum category ID sorting was saved.", $ilpage['components'] . '?module=lancebb');
		exit();
	}
}

// #### MODERATING NEW TOPICS POSTED ###########################################
else if (isset($ilance->GPC['subcmd']) AND ($ilance->GPC['subcmd'] == 'moderatetopics' OR $ilance->GPC['subcmd'] == 'moderateposts'))
{
        $query = '';
        switch ($ilance->GPC['subcmd'])
        {
                case 'moderatetopics':
		{
			if (isset($ilance->GPC['action']))
			{
				foreach ($ilance->GPC['action'] AS $postid => $value)
				{
					switch ($value)
					{
						// ### REMOVE TOPIC ############
						case '-1':
						{
							$ilance->lancebb->remove_topic(intval($ilance->GPC['tid']));
							print_action_success("Forum topic ID <strong>".$ilance->GPC['tid']."</strong> was removed.", $ilpage['components'].'?module=lancebb');
							exit();
							break;
						}
					
						// #### VALIDATE TOPIC #########
						case '1':
						{
							// validate topic
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "forum_topics
								SET visible = '1'
								WHERE tid = '" . intval($ilance->GPC['tid']) . "'
								LIMIT 1
							");
							
							// validate first post (so it doesn't show up in post moderation queue below)
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "forum_posts
								SET visible = '1'
								WHERE tid = '" . intval($ilance->GPC['tid']) . "'
							");
							if (isset($ilance->GPC['title']))
							{
								foreach ($ilance->GPC['title'] AS $postid => $value)
								{
									$title = $value;
									if (!empty($title))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "forum_topics
											SET title = '" . addslashes($title) . "'
											WHERE tid = '" . intval($ilance->GPC['tid']) . "'
											LIMIT 1
										");       
									}
	
								}
							}
							if (isset($ilance->GPC['text']))
							{
								foreach ($ilance->GPC['text'] AS $postid => $value)
								{
									if (!empty($value))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "forum_posts
											SET title = '" . addslashes($title) . "',
											message = '" . addslashes($value) . "',
											visible = '1'
											WHERE tid = '" . intval($ilance->GPC['tid']) . "'
												AND pid = '" . intval($postid) . "'
											LIMIT 1
										");
									}
								}
							}
							
							if (isset($ilance->GPC['notes']))
							{
								foreach ($ilance->GPC['notes'] AS $postid => $value)
								{
									if (!empty($value))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "forum_posts
											SET notes = '" . addslashes($value) . "'
											WHERE tid = '" . intval($ilance->GPC['tid']) . "'
												AND pid = '" . intval($postid) . "'
											LIMIT 1
										");       
									}
								}
							}
							
							print_action_success("Forum topic ID <strong>" . $ilance->GPC['tid'] . "</strong> was verified and is now publically viewable.", $ilpage['components'].'?module=lancebb');
							exit();
							
							break;
						}
					}
				}
			}
			break;
		}
                case 'moderateposts':
		{
			if (isset($ilance->GPC['action']))
			{
				foreach ($ilance->GPC['action'] as $postid => $value)
				{
					switch ($value)
					{
						// ### REMOVE POST #####################
						case '-1':
						{
							$ilance->lancebb->remove_post(intval($postid));
							print_action_success("Forum post ID <strong>".$postid."</strong> was removed.", $ilpage['components'].'?module=lancebb');
							exit();
							break;
						}
					
						// #### VALIDATE POST ##################
						case '1':
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "forum_posts
								SET visible = '1'
								WHERE tid = '" . intval($ilance->GPC['tid']) . "'
									AND pid = '".intval($postid)."'
								LIMIT 1
							");
							if (isset($ilance->GPC['title']))
							{
								foreach ($ilance->GPC['title'] AS $postid => $value)
								{
									$title = $value;
									if (!empty($title))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "forum_posts
											SET title = '".addslashes($title)."'
											WHERE tid = '" . intval($ilance->GPC['tid']) . "'
												AND pid = '".intval($postid)."'
											LIMIT 1
										");       
									}
	
								}
							}
							if (isset($ilance->GPC['text']))
							{
								foreach ($ilance->GPC['text'] AS $postid => $value)
								{
									if (!empty($value))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "forum_posts
											SET message = '" . addslashes($value) . "'
											WHERE tid = '" . intval($ilance->GPC['tid']) . "'
											AND pid = '" . intval($postid) . "'
											LIMIT 1
										");
									}
								}
							}
							if (isset($ilance->GPC['notes']))
							{
								foreach ($ilance->GPC['notes'] AS $postid => $value)
								{
									if (!empty($value))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "forum_posts
											SET notes = '" . addslashes($value) . "'
											WHERE tid = '" . intval($ilance->GPC['tid']) . "'
												AND pid = '" . intval($postid) . "'
											LIMIT 1
										");       
									}
								}
							}
							
							print_action_success("Forum post ID <strong>" . $postid . "</strong> was verified and is now publically viewable.", $ilpage['components'] . '?module=lancebb');
							exit();
							break;
						}
					}
				}
			}
			break;
		}
        }
        
}

// #### REMOVING FORUM CATEGORY ################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-category' AND isset($ilance->GPC['catid']) AND $ilance->GPC['catid'] > 0)
{
	$ilance->lancebb->remove_category($ilance->GPC['catid']);
	print_action_success("Forum category ID <strong>".intval($ilance->GPC['catid'])."</strong> was removed from the database.", $ilpage['components'] . '?module=lancebb');
}

// #### ADDING NEW FORUM CATEGORY ##############################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-category' AND isset($ilance->GPC['module']) AND $ilance->GPC['module'] == "lancebb")
{
	$ilance->db->query("
                INSERT INTO " . DB_PREFIX . "forum_categories
                (fid, title, description, sort, newpostemail, newthreademail, parentid, password, link, canpost) 
                VALUES(
                NULL,
                '" . $ilance->db->escape_string($ilance->GPC['catname']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['desc']) . "',
                '" . intval($ilance->GPC['sort']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['newpostemail']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['newtopicemail']) . "',
                '" . intval($ilance->GPC['parent']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['password']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['link']) . "',
		'" . intval($ilance->GPC['canpost']) . "')
        ");
        
        print_action_success("New category <strong>" . $ilance->GPC['catname'] . "</strong> was added to the forum system.", $ilpage['components'] . '?module=lancebb');
}

// #### UPDATING FORUM CATEGORY ################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-category' AND isset($ilance->GPC['catid']) AND $ilance->GPC['catid'] > 0)
{
	$ilance->db->query("
                UPDATE " . DB_PREFIX . "forum_categories
                SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "', 
                description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                sort = '" . intval($ilance->GPC['sort']) . "',
                newpostemail = '" . $ilance->db->escape_string($ilance->GPC['newpostemail']) . "',
                newthreademail = '" . $ilance->db->escape_string($ilance->GPC['newthreademail']) . "',
                parentid = '" . intval($ilance->GPC['parent']) . "',
                password = '" . $ilance->db->escape_string($ilance->GPC['password']) . "',
                link = '" . $ilance->db->escape_string($ilance->GPC['link']) . "',
		canpost = '" . intval($ilance->GPC['canpost']) . "'
                WHERE fid = '" . intval($ilance->GPC['catid']) . "'
                LIMIT 1
        ");
        
        print_action_success("Forum category settings have been saved.", $ilpage['components'] . '?module=lancebb');
}

// #### UPDATE FORUM SETTINGS ##################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-settings')
{
	foreach ($_POST AS $key => $value)
	{
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "forum_configuration 
                        SET value = '" . $ilance->db->escape_string($value) . "'
                        WHERE name = '" . $ilance->db->escape_string($key) . "'
                        LIMIT 1
                ");
	}
        
	print_action_success("Main settings have been saved. New changes should take effect immediately.", $ilpage['components'] . '?module=lancebb');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>