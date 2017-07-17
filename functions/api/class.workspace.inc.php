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
* Workspace class to perform the majority of mediashare/workspace functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class workspace
{
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function remove_mediashare_data($projectid = 0)
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
            
                $sql = $ilance->db->query("
                        SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                        FROM " . DB_PREFIX . "attachment
                        WHERE attachtype = 'ws'
                                AND project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $sql_can_remove = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "attachment_folder
                                        WHERE id = '" . $res['tblfolder_ref'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_can_remove) > 0)
                                {
                                        // remove workspace media folders
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "attachment_folder
                                                WHERE id = '" . $res['tblfolder_ref'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                if ($ilconfig['attachment_dbstorage'])
                                {
                                        // remove attachment from database
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "attachment
                                                WHERE attachid = '" . $res['attachid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        // remove attachment from file system
                                        @unlink(DIR_WS_ATTACHMENTS . $res['filehash'] . '.attach');
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "attachment
                                                WHERE attachid = '" . $res['attachid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "attachment_folder
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function remove_mediashare_data_cron($projectid = 0)
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
            
                $sql = $ilance->db->query("
                        SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                        FROM " . DB_PREFIX . "attachment
                        WHERE attachtype = 'ws'
                                AND project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $removedlist = '';
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $sql_can_remove = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "attachment_folder
                                        WHERE id = '" . $res['tblfolder_ref'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_can_remove) > 0)
                                {
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "attachment_folder
                                                WHERE id = '" . $res['tblfolder_ref'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                if ($ilconfig['attachment_dbstorage'] == 1)
                                {
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "attachment
                                                WHERE attachid = '" . $res['attachid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        @unlink(DIR_WS_ATTACHMENTS . $res['filehash'] . '.attach');
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "attachment
                                                WHERE attachid = '" . $res['attachid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                $removedlist .= "- " . stripslashes($res['filename']) . " (" . $res['filesize'] . " " . $phrase['_bytes'] . ")\n";
                        }
                        
                        $projecttitle = fetch_auction('project_title', intval($projectid));
                        
                        // mail admin
                        $subject = "MediaShare Items Purged for (".intval($projectid)."): ".$projecttitle;
                        $message = "Administration,\n\nThis email is to inform you that the automated mediashare tasks have run for auctions that are finished for (".intval($projectid)."): ".$projecttitle." and have had a seven-day window open for usage of the file distribution system between the buyer and seller.\n\n========================\n\n".$removedlist."\n========================\nAny media within the folders of this particular auction have been purged to conserve disk usage and both parties have been notified. You can modify the seven-day window value by referring to the \"/functions/cron/cron.rfp.php\" application file.";
                        send_email(SITE_EMAIL,$subject,$message,SITE_EMAIL);
                        
                        $sqlbuyer = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . intval($projectid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlbuyer) > 0)
                        {
                                $resbuyer = $ilance->db->fetch_array($sqlbuyer);
                        }
                
                        $sqlseller = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "project_bids
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND bidstatus = 'awarded'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlseller) > 0)
                        {
                                $resseller = $ilance->db->fetch_array($sqlseller);
                        }
                
                        $subject = "MediaShare Items Purged for (".intval($projectid)."): ".$projecttitle;
                        $message = "This email is to inform you that Auction (".intval($projectid)."): ".$projecttitle." has been finished for seven-days and the file distribution system between you and the service provider for this particular auction have been purged (removed).\n\n========================\n\n".$removedlist."\n========================\nWe take this step to ensure our marketplace is organized resulting in a better service to you.\n\nThank you for using ".SITE_NAME."!\n";
                        send_email(fetch_user('email', $resbuyer['user_id']), $subject, $message, SITE_EMAIL);
                        
                        $subject = "MediaShare Items Purged for Auction (".intval($projectid)."): ".$projecttitle;
                        $message = "This email is to inform you that Auction (".intval($projectid)."): ".$projecttitle." has been finished for seven-days and the file distribution system between you and the service buyer for this particular auction have been purged (removed).\n\n========================\n\n".$removedlist."\n========================\n  We take this step to ensure our marketplace is organized resulting in a better service to you.\n\nThank you for using ".SITE_NAME."!\n";
                        send_email(fetch_user('email', $resseller['user_id']), $subject, $message, SITE_EMAIL);
                }
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "attachment_folder
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function remove_mediashare_data_bidid($bidid = 0)
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_bids WHERE bid_id = '".intval($bidid)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $resp = $ilance->db->fetch_array($sql);
                        
                        $sql_attachments = $ilance->db->query("
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = 'ws'
                                        AND project_id = '" . $resp['project_id'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_attachments) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql_attachments))
                                {
                                        $sql_can_remove = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "attachment_folder
                                                WHERE id = '" . $res['tblfolder_ref'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_can_remove) > 0)
                                        {
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "attachment_folder
                                                        WHERE id = '" . $res['tblfolder_ref'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        if ($ilconfig['attachment_dbstorage'] == 1)
                                        {
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "attachment
                                                        WHERE attachid = '" . $res['attachid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else
                                        {
                                                @unlink(DIR_WS_ATTACHMENTS . $res['filehash'] . '.attach');
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "attachment
                                                        WHERE attachid = '" . $res['attachid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                        }
                        
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "attachment_folder
                                WHERE project_id = '" . $resp['project_id'] . "'
                                        AND (buyer_id = '" . $resp['user_id'] . "' OR seller_id = '" . $resp['user_id'] . "')
                        ", 0, null, __FILE__, __LINE__);
                }
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function print_attachment_gauge($userid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase, $show;
                
                $ilance->subscription = construct_object('api.subscription');
                
                $endingicon = 'end-empty.gif';
                $endingstyle = '';
                $show['nouploading'] = false;
                
                $sql = $ilance->db->query("
                        SELECT SUM(filesize) AS attach_usage_total
                        FROM " . DB_PREFIX . "attachment
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        $total = $res['attach_usage_total'];
                        $limit = $ilance->subscription->check_access(intval($userid), 'attachlimit');
                        
                        $totalusage = '<span class="blue">' . print_filesize($total) . '</span> ' . $phrase['_of'] . ' <span class="blue">' . print_filesize($limit) . '</span> ' . $phrase['_total'];
                        
                        if ($total > $limit)
                        {
                                $endingicon = 'end-filled.gif';
                                $show['nouploading'] = true;
                        }
                        
                        $percentage_total = $limit;
                        $percentage_used = round(($total / $limit) * 100);
                        $percentage_left = (100 - $percentage_used);
                }
                else
                {
                        $total = 0;
                        $limit = $ilance->subscription->check_access(intval($userid), 'attachlimit');
                        
                        $percentage_total = $limit;
                        $percentage_used = round(($total / $limit) * 100);
                        $percentage_left = ($percentage_used - 100);
                }
                
                $html = '
                <table width="100%" border="0" align="left" cellpadding="0" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
                <tr> 
                    <td width="69%" class="gaugeArea">
                        <div class="smaller gray" style="padding-bottom:3px"><span style="float:right"><span class="blue"><strong>' . $percentage_used . '%</strong></span> ' . $phrase['_used'] . '</span>' . $totalusage . '</div>
                        <table width="100%" height="9" align="center" cellpadding="0" cellspacing="0" class="gaugeLayout" dir="' . $ilconfig['template_textdirection'] . '">
                        <tr> 
                                <td width="4"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'begin-filled.gif" /></td>
                                <td width="' . $percentage_used . '%" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'fill.gif); background-repeat:repeat-x; background-position:center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'fill.gif" alt="'.round($percentage_left).'% used" /></td>
                                <td width="' . $percentage_left . '%" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'empty.gif); background-repeat:repeat-x; background-position:center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'fill.gif" alt="" /></td>
                                <td width="4" ' . $endingstyle . '><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . $endingicon . '" /></td>
                        </tr>
                        </table>
                        
                    </td>
                    <td width="31%" nowrap="nowrap"><div align="center"></div></td>
                </tr>
                </table>';
                
                return $html;
        }
        
        /**
        * Function to check finished auctions 1 week after closing to remove mediashare (conserve diskspace)
        */
        function remove_mediashare_content_daily($daysago = 7)
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $cronlog = '';
                
                $sql = $ilance->db->query("
                        SELECT p.project_id
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "attachment_folder AS w
                        WHERE p.project_id = w.project_id
                            AND p.date_end <= DATE_SUB('" . DATETODAY . " 00:00:00', INTERVAL $daysago DAY)
                            AND (p.status = 'finished' OR p.status = 'archived' OR p.status = 'delisted')
                        GROUP BY p.project_id
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($resms = $ilance->db->fetch_array($sql))
                        {
                                $this->remove_mediashare_data_cron($resms['project_id']);
                                $cronlog .= 'Removed mediashare contents for auction #' . $resms['project_id'] . ', ';
                        }
                }
                
                return $cronlog;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>