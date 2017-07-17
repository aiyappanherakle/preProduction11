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
* Attachment class to perform the majority of uploading and attachment handling operations within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class attachment
{
        var $totalattachments = null;
        var $totaldiskspace = null;
        var $totaldownloads = null;
        var $storagetype = null;
        var $temp_file_name = null;
        var $file_name = null;
        var $upload_dir = null;
        var $max_file_size = null;
        var $ext_array = array();
        var $filedata = null;
        var $filetype = null;
        var $date_time = null;
        
        /**
        * list of image only mime types
        */
        var $mimetypes = array(
                'image/gif',
                'image/jpeg',
                'image/png',
                'image/psd',
                'image/bmp',
                'image/tiff',
                'image/jp2',
                'image/iff',
                'image/xbm',
                'image/ief',
                'image/vnd.wap.wbmp',
                'image/vnd.microsoft.icon',
                'image/vnd.djvu',
                'image/x-cmu-raster',
                'image/x-portable-anymap',
                'image/x-portable-bitmap',
                'image/x-portable-graymap',
                'image/x-portable-pixmap',
                'image/x-rgb',
                'image/x-xbitmap',
                'image/x-xpixmap',
                'image/x-xwindowdump'
        );
        
        /**
        * Function for printing the innerhtml javascript code in the templates
        *
        * @param       string       attachment div id
        * @param       string       attachment list html contents
        *
        * @return      string       Returns javascript code
        */
        function print_innerhtml_js($attachmentlist = 'attachmentlist', $attachment_list_html = '')
        {
                global $ilance;
                
                $js = '
<script type="text/javascript">
<!--
switch (DOMTYPE)
{
        case "std":
        { 
                var ' . $attachmentlist . ' = window.opener.document.getElementById("' . $attachmentlist . '");
        }
        break;
        
        case "ie4":
        {
                var ' . $attachmentlist . ' = window.opener.document.all["' . $attachmentlist . '"];
        }
}
' . $attachmentlist . '.innerHTML = \'' . $attachment_list_html . '\';
//-->
</script>';
                ($apihook = $ilance->api('print_innerhtml_js_end')) ? eval($apihook) : false;
                
                return $js;
        }
        
        /**
        * Function for returning the total amount of attachments in the system
        *
        * @return      integer      total amount of attachments
        */
        function totalattachments()
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS totalattachments
                        FROM " . DB_PREFIX . "attachment
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return $res['totalattachments'];
                }
                else
                {
                        return '0';
                }
        }
    
        /**
        * Function for returning the total amount of disk space used by attachments in the system
        *
        * @return      integer      total amount of attachments
        */
        function totaldiskspace()
        {
                global $ilance, $myapi;
                $sql = $ilance->db->query("
                        SELECT SUM(filesize) AS totaldiskspace
                        FROM " . DB_PREFIX . "attachment
                        WHERE (filesize != '' OR filesize != '0')
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return print_filesize($res['totaldiskspace']);
                }
                else
                {
                        return print_filesize(0);
                }
        }
        
        /**
        * Function for returning the total downloads based on attachments in the system
        *
        * @return      integer      total number of downloads
        */
        function totaldownloads()
        {
                global $ilance, $myapi;
                $sql = $ilance->db->query("
                        SELECT SUM(counter) AS totaldownloads
                        FROM " . DB_PREFIX . "attachment
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return intval($res['totaldownloads']);
                }
                else
                {
                        return '0';
                }
        }
    
        /**
        * Function for returning the method of storage used by the attachment system
        *
        * @param       string       action of function to return
        *
        * @return      mixed        
        */
        function storagetype($action = '')
        {
                global $ilance, $myapi, $phrase;
                
                if (isset($action) AND $action == 'type')
                {
                        $sql = $ilance->db->query("
                                SELECT value
                                FROM " . DB_PREFIX . "configuration
                                WHERE name = 'attachment_dbstorage'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                if ($res['value'] > 0)
                                {
                                        return $phrase['_attachments_are_currently_being_stored_in_the_database'];
                                }
                                else
                                {
                                        return $phrase['_attachments_are_currently_being_stored_in_the_filepath_system'];
                                }
                        }
                        else
                        {
                                return $phrase['_attachments_are_currently_being_stored_in_the_database'];
                        }
                }
                else if (isset($action) AND $action == 'formaction')
                {
                        $sql = $ilance->db->query("
                                SELECT value
                                FROM " . DB_PREFIX . "configuration
                                WHERE name = 'attachment_dbstorage'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                if ($res['value'] > 0)
                                {
                                        return '<input type="radio" name="action" id="action" value="movetofilepath" checked="checked" /><label for="action">'.$phrase['_move'].' <strong>'.$this->totalattachments().'</strong> '.$phrase['_attachments_from_the_database_to_the_filesystem'].'</label>';
                                }
                                else
                                {
                                        return '<input type="radio" name="action" id="action" value="movetodatabase" checked="checked" /><label for="action">'.$phrase['_move'].' <strong>'.$this->totalattachments().'</strong> '.$phrase['_attachments_from_the_filesystem_to_the_database'].'</label>';
                                }
                        }
                        else
                        {
                                return '<input type="radio" name="action" id="action" value="movetofilepath" checked="checked" /><label for="action">'.$phrase['_move_attachments_from_the_database_to_the_filesystem'].'</label>';
                        }
                }
        }
        
        /**
        * Function for validating the filename extention based on the file being uploaded
        *
        * @return      bool         true or false if extension is valid        
        */
        function validate_extension()
        {
                $extension = mb_strtolower(mb_strrchr($this->file_name, '.'));
                $ext_count = count($this->ext_array);
                
                if (!$this->file_name)
                {
                        return false;
                }
                
                if (!$this->ext_array)
                {
                        return true;
                }
                
                $extensions = array();
                foreach ($this->ext_array AS $value)
                {
                        $first_char = mb_substr($value, 0, 1);
                        $extensions[] = (($first_char <> '.')
                                ? '.' . mb_strtolower($value)
                                : mb_strtolower($value));
                }
                
                foreach ($extensions AS $accepted)
                {
                        if ($accepted == $extension)
                        {
                                return true;
                        }
                }
        }
    
        /**
        * Function for validating the filesize based on the file being uploaded
        *
        * @return      bool         true or false if filesize is valid        
        */
        function validate_size()
        {
                global $ilconfig, $uncrypted;
                
                $temp_file_name = trim($this->temp_file_name);
                $file_name = trim($this->file_name);
                $max_file_size = trim($this->max_file_size);
                $extension = mb_strtolower(mb_strrchr($file_name, '.'));
                $ext_array = $this->ext_array;
                $ext_count = count($ext_array);
                $valid_filesize = true;
                $failedwidth = $failedheight = $failedfilesize = false;
                
                if (isset($temp_file_name))
                {
                        $size = filesize($temp_file_name);
                        foreach ($ext_array AS $value)
                        {
                                $first_char = mb_substr($value, 0, 1);
                                if ($first_char <> '.')
                                {
                                        $extensions[] = '.' . mb_strtolower($value);
                                }
                                else
                                {
                                        $extensions[] = mb_strtolower($value);
                                }
                        }
            
                        // does the uploaded file extension support our upload types?
                        if (in_array($extension, $extensions))
                        {
                                // multiple extension handler
                                if (isset($uncrypted['attachtype']))
                                {
                                        switch ($uncrypted['attachtype'])
                                        {
                                                case 'profile':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_profilemaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_profilemaxwidth'];
                                                        break;
                                                }                                                
                                                case 'project':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_projectmaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_projectmaxwidth'];
                                                        break;
                                                }                                                
                                                case 'itemphoto':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_productphotomaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_productphotomaxwidth'];
                                                        break;
                                                }                                                
                                                case 'bid':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_bidmaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_bidmaxwidth'];
                                                        break;
                                                }                                                
                                                case 'pmb':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_pmbmaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_pmbmaxwidth'];
                                                        break;
                                                }                                                
                                                case 'ws':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_mediasharemaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_mediasharemaxwidth'];
                                                        break;
                                                }                                                
                                                case 'portfolio':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_portfoliomaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_portfoliomaxwidth'];
                                                        break;
                                                }                                                
                                                case 'digital':
                                                {
                                                        $maxheight = $maxwidth = '';                                                        
                                                        break;
                                                }                                                
                                                case 'slideshow':
                                                {
                                                        $maxheight = $ilconfig['attachmentlimit_slideshowmaxheight'];
                                                        $maxwidth = $ilconfig['attachmentlimit_slideshowmaxwidth'];
                                                        break;
                                                }
                                        }
                                }
                                
                                // quick mime type checkup
                                if (!in_array($this->file_type, $this->mimetypes))
                                {
                                        $maxheight = $maxwidth = '';
                                }
                                
                                // check filesize
                                if ($size > $max_file_size)
                                {
                                        $valid_filesize = false;
                                }
                                
                                if (empty($maxheight) AND empty($maxwidth))
                                {
                                        // non image type media
                                        if ($valid_filesize == true)
                                        {
                                                $return = array(
                                                        'success'         => '1', 
                                                        'badwidth'        => '0', 
                                                        'badheight'       => '0', 
                                                        'uploadwidth'     => '0',  
                                                        'uploadheight'    => '0',
                                                        'uploadfilesize'  => $size,
                                                        'failedextension' => '0'
                                                );
                                        }
                                        else 
                                        {
                                                $return = array(
                                                        'success'         => '0', 
                                                        'badwidth'        => '0', 
                                                        'badheight'       => '0', 
                                                        'uploadwidth'     => '0',  
                                                        'uploadheight'    => '0',
                                                        'uploadfilesize'  => $size,
                                                        'failedextension' => '0'
                                                );
                                        }
                                        
                                        return $return;
                                }
                                else 
                                {
                                        $fileinfo = @getimagesize($temp_file_name);
                                        /*Array
                                        (
                                            [0] => 308
                                            [1] => 60
                                            [2] => 3
                                            [3] => width="308" height="60"
                                            [bits] => 8
                                            [mime] => image/png
                                        )
                                        */
                                        
                                        $valid_extension_w = ($fileinfo[0] > $maxwidth)  ? 'false' : 'true';
                                        $valid_extension_h = ($fileinfo[1] > $maxheight) ? 'false' : 'true';
                                        
                                        if (!$fileinfo)
                                        {
                                                $valid_extension_w = $valid_extension_h = 'false';
                                        }
                                        
                                        // return some details to ilance
                                        if ($valid_extension_w == 'true' AND $valid_extension_h == 'true' AND $valid_filesize == 'true')
                                        {
                                                $return = array(
                                                        'success'         => '1', 
                                                        'badwidth'        => '0', 
                                                        'badheight'       => '0', 
                                                        'uploadwidth'     => intval($fileinfo[0]), 
                                                        'uploadheight'    => intval($fileinfo[1]), 
                                                        'uploadfilesize'  => $size,
                                                        'failedextension' => '0'
                                                );
                                                
                                                return $return;
                                        }
                                        else
                                        {
                                                if ($valid_extension_w == 'false')
                                                {
                                                        $failedwidth = '1';
                                                }
                    
                                                if ($valid_extension_h == 'false')
                                                {
                                                        $failedheight = '1';
                                                }
                                                
                                                if ($valid_filesize == false)
                                                {
                                                        $failedfilesize = '1';
                                                }
                                                        
                                                $return = array(
                                                        'success'         => '0', 
                                                        'failedwidth'     => $failedwidth, 
                                                        'failedheight'    => $failedheight, 
                                                        'failedfilesize'  => $failedfilesize,
                                                        'failedextension' => '0',
                                                        'uploadwidth'     => $fileinfo[0], 
                                                        'uploadheight'    => $fileinfo[1], 
                                                        'uploadfilesize'  => $size
                                                );
                                                
                                                return $return;
                                        }					
                                }
                        }
                        else 
                        {
                                // uploaded file is not allowed to upload in this extension!
                                $return = array(
                                        'success'         => '0', 
                                        'failedwidth'     => '0', 
                                        'failedheight'    => '0', 
                                        'failedfilesize'  => '0', 
                                        'failedextension' => '1',
                                        'uploadwidth'     => '0', 
                                        'uploadheight'    => '0', 
                                        'uploadfilesize'  => $size
                                );
                                
                                return $return;
                        }
                }
                else
                {
                        $return = array(
                                'success'         => '0', 
                                'failedwidth'     => '1', 
                                'failedheight'    => '1', 
                                'failedfilesize'  => '1',
                                'failedextension' => '1',
                                'uploadwidth'     => '0', 
                                'uploadheight'    => '0', 
                                'uploadfilesize'  => '0'
                        );
                        
                        return $return;
                }
        }
    
        /**
        * Function to return the actual file type of a file being uploaded
        *
        * @return      string       file type
        */
        function get_file_type()
        {
                $file_type = trim($this->file_type);
                $file_type = ($file_type)
                        ? $file_type
                        : 'Error: no file passed';
                
                return $file_type;
        }
    
        /**
        * Function to return the actual file size of a file being uploaded
        *
        * @return      string       file type
        */
        function get_file_size()
        {
                $temp_file_name = trim($this->temp_file_name);
                $size = (!empty($temp_file_name))
                        ? filesize($temp_file_name)
                        : 'Error: no file passed';
                
                return $size;
        }
    
        /**
        * Function to return the maximum size permitted for upload (should already be assigned)
        *
        * @return      string       maximum file size
        */
        function get_max_size()
        {
                $max_file_size = $this->max_file_size;
                
                $kb = 1024;
                $mb = 1024 * $kb;
                $gb = 1024 * $mb;
                $tb = 1024 * $gb;
        
                if (!empty($max_file_size))
                {
                        if ($max_file_size < $kb)
                        {
                                $max_file_size = "max_file_size Bytes";
                        }
                        else if ($max_file_size < $mb)
                        {
                                $final = round($max_file_size / $kb, 2);
                                $max_file_size = "$final";
                        }
                        else if ($max_file_size < $gb)
                        {
                                $final = round($max_file_size / $mb, 2);
                                $max_file_size = "$final";
                        }
                        else if ($max_file_size < $tb)
                        {
                                $final = round($max_file_size / $gb, 2);
                                $max_file_size = "$final";
                        }
                        else
                        {
                                $final = round($max_file_size / $tb, 2);
                                $max_file_size = "$final";
                        }
                }
                else
                {
                        $max_file_size = 'Error: No size passed';
                }
                
                return $max_file_size;
        }
    
        /**
        * Function to return the full upload directory (should already be assigned)
        *
        * @return      string       full folder path
        */
        function get_upload_directory()
        {
                $upload_dir = trim($this->upload_dir);
                if ($upload_dir)
                {
                        $ud_len = mb_strlen($upload_dir);
                        $last_slash = mb_substr($upload_dir, $ud_len - 1, 1);
                        if ($last_slash <> '/')
                        {
                                $upload_dir = $upload_dir . '/';
                        }
                        else
                        {
                                $upload_dir = $upload_dir;
                        }
                        $handle = @opendir($upload_dir);
                        if ($handle)
                        {
                                $upload_dir = $upload_dir;
                                closedir($handle);
                        }
                        else
                        {
                                $upload_dir = 'ERROR';
                        }
                }
                else
                {
                        $upload_dir = 'ERROR';
                }
                
                return $upload_dir;
        }
        
        function handle_attachtype_rebuild_settings($attachtype = '', $userid = 0, $projectid = 0, $filehash = '')
        {
                global $ilance, $ilconfig, $show, $area_title, $page_title, $phrase;
                
                $array = array();
                $maximum_files = $max_width = $max_height = $max_filesize = $max_size = $extensions = $query = '';
                
                if ($attachtype == 'profile')
                {
                        $area_title = $phrase['_uploading_profile_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_profile_attachments'];
                        
                        $maximum_files = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'maxprofileattachments');
                        $max_filesize = $ilconfig['attachmentlimit_profilemaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_profilemaxsize']);
                        $max_width = $ilconfig['attachmentlimit_profilemaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_profilemaxheight'];
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_profileextensions']);
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                        ";
                }
                
                else if ($attachtype == 'portfolio')
                {
                        $area_title = $phrase['_uploading_portfolio_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_portfolio_attachments'];
                        
                        $maximum_files = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'maxportfolioattachments');
                        $max_filesize = $ilconfig['attachmentlimit_portfoliomaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_portfoliomaxsize']);
                        $max_width = $ilconfig['attachmentlimit_portfoliomaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_portfoliomaxheight'];
                        
                        $show['portfolio_manage'] = true;
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_portfolioextensions']);
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                        ";
                }
                
                else if ($attachtype == 'project')
                {
                        $area_title = $phrase['_uploading_auction_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_auction_attachments'];
                        
                        $maximum_files = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'maxprojectattachments');
                        $max_size = print_filesize($ilconfig['attachmentlimit_projectmaxsize']);
                        $max_filesize = $ilconfig['attachmentlimit_projectmaxsize'];
                        $max_width = $ilconfig['attachmentlimit_projectmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_projectmaxheight'];
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        
						
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND project_id = '" . intval($projectid) . "'
                        ";
                }
                
                else if ($attachtype == 'itemphoto')
                {
                        $area_title = $phrase['_uploading_auction_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_auction_attachments'];
                        
                        $maximum_files = 1;
                        $max_size = print_filesize($ilconfig['attachmentlimit_productphotomaxsize']);
                        $max_filesize = $ilconfig['attachmentlimit_productphotomaxsize'];
                        $max_width = $ilconfig['attachmentlimit_productphotomaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_productphotomaxheight'];
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_productphotoextensions']);
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        //herakle kkk
						if($_SESSION['ilancedata']['user']['isstaff'] == '1')
						$pro_val_id = $projectid;
						else
						$pro_val_id = intval($projectid);
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND project_id = '" . $pro_val_id . "'
                        ";
                }
                
                else if ($attachtype == 'bid')
                {
                        $area_title = $phrase['_uploading_bid_proposal_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_bid_proposal_attachments'];
                        
                        $maximum_files = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'maxbidattachments');
                        $max_filesize = $ilconfig['attachmentlimit_bidmaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_bidmaxsize']);
                        $max_width = $ilconfig['attachmentlimit_bidmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_bidmaxheight'];
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment    
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND project_id = '" . intval($projectid) . "'
                        ";
                }
                
                else if ($attachtype == 'pmb')
                {
                        $area_title = $phrase['_uploading_pmb_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_pmb_attachments'];
                        
                        $maximum_files = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'maxpmbattachments');
                        $max_filesize = $ilconfig['attachmentlimit_pmbmaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_pmbmaxsize']);
                        $max_width = $ilconfig['attachmentlimit_pmbmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_pmbmaxheight'];
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND project_id = '" . intval($projectid) . "'
                        ";
                }
                
                else if ($attachtype == 'digital')
                {
                        $area_title = $phrase['_uploading_auction_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_auction_attachments'];
                        
                        $maximum_files = 1;
                        $max_size = print_filesize($ilconfig['attachmentlimit_digitalfilemaxsize']);
                        $max_filesize = $ilconfig['attachmentlimit_digitalfilemaxsize'];
                        $max_width = 0;
                        $max_height = 0;
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_digitalfileextensions']);
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND project_id = '" . intval($projectid) . "'
                        ";
                }
                
                else if ($attachtype == 'slideshow')
                {
                        $area_title = $phrase['_uploading_auction_attachments'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_uploading_auction_attachments'];
                        
                        $maximum_files = 5;
                        $max_width = $ilconfig['attachmentlimit_slideshowmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_slideshowmaxheight'];
                        $max_filesize = $ilconfig['attachmentlimit_slideshowmaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_slideshowmaxsize']);
                        
                        $show['ifextensions'] = true;
                        $extensions = '';
                        
                        $permittedext = explode(',', $ilconfig['attachmentlimit_productphotoextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value.'&nbsp;';
                        }
                        //herakle kkk
						if($_SESSION['ilancedata']['user']['isstaff'] == '1')
						$pro_val_id = $projectid;
						else
						$pro_val_id = intval($projectid);
                        $query = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND project_id = '" . $pro_val_id . "'
                        ";
                }
                
                ($apihook = $ilance->api('handle_attachtype_rebuild_settings_end')) ? eval($apihook) : false;
                
                $array = array(
                        'maximum_files' => $maximum_files,
                        'max_width'     => $max_width,
                        'max_height'    => $max_height,
                        'max_filesize'  => $max_filesize,
                        'max_size'      => $max_size,
                        'extensions'    => $extensions,
                        'query'         => $query
                );
                
                return $array;
        }
        
        function handle_attachtype_upload_settings($attachtype = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $array = array();
                
                if ($attachtype == 'profile')
                {
                        $max_filesize = $ilconfig['attachmentlimit_profilemaxsize'];
                        $max_size = $ilconfig['attachmentlimit_profilemaxsize'];
                        $upload_to = DIR_PROFILE_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_profileextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_profileextensions']);
                }
                
                else if ($attachtype == 'portfolio')
                {
                        $max_filesize = $ilconfig['attachmentlimit_portfoliomaxsize'];
                        $max_size = $ilconfig['attachmentlimit_portfoliomaxsize'];
                        $upload_to = DIR_PORTFOLIO_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_portfolioextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_portfolioextensions']);
                }
                
                else if ($attachtype == 'project')
                {
                        $max_filesize = $ilconfig['attachmentlimit_projectmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_projectmaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                
                else if ($attachtype == 'itemphoto')
                {
                        $max_filesize = $ilconfig['attachmentlimit_productphotomaxsize'];
                        $max_size = $ilconfig['attachmentlimit_productphotomaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_productphotoextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_productphotoextensions']);
                }
                
                else if ($attachtype == 'bid')
                {
                        $max_filesize = $ilconfig['attachmentlimit_bidmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_bidmaxsize'];
                        $upload_to = DIR_BID_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                
                else if ($attachtype == 'pmb')
                {
                        $max_filesize = $ilconfig['attachmentlimit_pmbmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_pmbmaxsize'];
                        $upload_to = DIR_PMB_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                
                else if ($attachtype == 'ws')
                {
                        $max_filesize = $ilconfig['attachmentlimit_mediasharemaxsize'];
                        $max_size = $ilconfig['attachmentlimit_mediasharemaxsize'];
                        $upload_to = DIR_WS_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                
                else if ($attachtype == 'kb')
                {
                        $max_filesize = $max_size = '';
                        $upload_to = DIR_KB_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                
                else if ($attachtype == 'ads')
                {
                        $max_filesize = $max_size = '';
                        $upload_to = DIR_ADS_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                
                else if ($attachtype == 'digital')
                {
                        $max_filesize = $ilconfig['attachmentlimit_digitalfilemaxsize'];
                        $max_size = $ilconfig['attachmentlimit_digitalfilemaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_digitalfileextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_digitalfileextensions']);
                }
                
                else if ($attachtype == 'slideshow')
                {
                        $max_filesize = $ilconfig['attachmentlimit_slideshowmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_slideshowmaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_slideshowextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_slideshowextensions']);
                }
                
                ($apihook = $ilance->api('handle_attachtype_upload_settings_end')) ? eval($apihook) : false;
                
                $array = array(
                        'max_filesize' => $max_filesize,
                        'max_size'     => $max_size,
                        'upload_to'    => $upload_to,
                        'extensions'   => $extensions
                );
                
                return $array;
        }
    
        /**
        * Function to save the uploaded file attachment to the filesystem or database
        *
        * @return      boolean      true or false based on successful attachment upload
        */
        function save_attachment($user_set)
        {
                global $ilance, $myapi, $ilconfig, $uncrypted, $show, $phrase;
                
                $temp_file_name = trim($this->temp_file_name);
                $file_name = trim(mb_strtolower($this->file_name));
                $file_type = trim(mb_strtolower($this->file_type));
                $file_size = $this->get_file_size();
                $valid_size = $this->validate_size();
                $valid_ext = $this->validate_extension();
                $upload_dir = $this->get_upload_directory();
                
                ($apihook = $ilance->api('save_attachment_start')) ? eval($apihook) : false;
                
                if ($upload_dir == 'ERROR')
                {
                        return false;
                }
                
                if (!$valid_size OR !$valid_ext)
                {
                        return false;
                }
                
                // #### attempt to obtain a passing filehash ###################
                $filehash = (isset($uncrypted['filehash']) AND !empty($uncrypted['filehash'])) ? $uncrypted['filehash'] : md5(uniqid(microtime()));
                
                // #### quickly check if we already have the exact same filehash uploaded as we don't want duplicates from this user
				//herakle kkk
				if($_SESSION['ilancedata']['user']['isstaff'] == '1'){
                $attachid = $ilance->db->fetch_field(DB_PREFIX . "attachment", "filehash = '" . $ilance->db->escape_string($filehash) . "' AND user_id = '" . $user_set . "'", "attachid");}
				else{
				$attachid = $ilance->db->fetch_field(DB_PREFIX . "attachment", "filehash = '" . $ilance->db->escape_string($filehash) . "' AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'", "attachid");}
				
                $filehash = ($attachid > 0) ? md5(uniqid(microtime())) : $filehash;
                
                // #### build attachment upload folder and new file name #######
                $newfilename = $upload_dir . $filehash . '.attach';
                
                // #### defaults for exif and file blob variable for image data so we can ultimately convert to a thumbnail as well
                $exif = $filedata = '';
                
                // #### make sure the user's file was transferred from their pc to the server drive
                if (is_uploaded_file($temp_file_name))
                {
                        // #### make sure php can move the uploaded file into a designated folder with a new filename referenced in the db
                        if (move_uploaded_file($temp_file_name, $newfilename))
                        {
                                // #### fetch exif information (extended image support)
                                if (function_exists('exif_read_data'))
                                {
                                        $exifdata = @exif_read_data($newfilename, 'EXIF');
                                        if (!empty($exifdata))
                                        {
                                                $exif = addslashes(serialize($exifdata));
                                                unset($exifdata);
                                        }
                                }
                                
                                // #### fetch exact filesize of uploaded and moved file
                                $upload_file_size = filesize($newfilename);
                                                    
                                // #### if we are using the database, put file data in temp space and remove actual file from server
                                if ($ilconfig['attachment_dbstorage'])
                                {
                                        $filedata = addslashes(fread(fopen($newfilename, 'rb'), filesize($newfilename)));
                                        @unlink($newfilename);
                                }
                                
                                // #### if we have portfolio upload, upload first then assign the attachid
                                if ($uncrypted['attachtype'] == 'portfolio')
                                {
                                        ($apihook = $ilance->api('save_attachment_portfolio_start')) ? eval($apihook) : false;
                                        
                                        $catid = (isset($ilance->GPC['cid'])) ? intval($ilance->GPC['cid']) : 0;
                                        
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "portfolio
                                                (portfolio_id, user_id, caption, description, category_id, featured, visible)
                                                VALUES(
                                                NULL,
                                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['caption']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                                                '" . $catid . "',
                                                '0',
                                                '1')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $newattachid = $ilance->db->insert_id();
                
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "attachment
                                                (attachid, attachtype, user_id, portfolio_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress, exifdata)
                                                VALUES(
                                                NULL,
                                                '" . $ilance->db->escape_string($uncrypted['attachtype']) . "',
                                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                                '" . intval($newattachid) . "',
                                                '" . $catid . "',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($file_name) . "',
                                                '" . $filedata . "',
                                                '" . $ilance->db->escape_string($file_type) . "',
                                                '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                                '0',
                                                '" . $ilance->db->escape_string($upload_file_size) . "',
                                                '" . $ilance->db->escape_string($filehash) . "',
                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                '" . $exif . "')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $newattachid = $ilance->db->insert_id();
                                        
                                        ($apihook = $ilance->api('save_attachment_portfolio_end')) ? eval($apihook) : false;
                                }
                                
                                // #### regular attachment upload ##############
                                else
                                {
                                        ($apihook = $ilance->api('save_attachment_else_start')) ? eval($apihook) : false;
                                        
                                        $uncrypted['portfolio_id'] = isset($uncrypted['portfolio_id']) ? $uncrypted['portfolio_id'] : 0;
                                        $uncrypted['project_id'] = isset($uncrypted['project_id']) ? $uncrypted['project_id'] : 0;
                                        $uncrypted['pmb_id'] = isset($uncrypted['pmb_id']) ? $uncrypted['pmb_id'] : 0;
                                        $uncrypted['category_id'] = isset($uncrypted['category_id']) ? (int)$uncrypted['category_id'] : 0;
                                        
                                        // #### is admin uploading or managing auction attachments via admincp?
                                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1' AND defined('LOCATION') AND LOCATION == 'admin')
                                        {
                                                ($apihook = $ilance->api('save_attachment_admin_user_start')) ? eval($apihook) : false;
                                                
                                                $sql4 = $ilance->db->query("
                                                        SELECT " . DB_PREFIX . "users.user_id
                                                        FROM " . DB_PREFIX . "users,
                                                        " . DB_PREFIX . "projects
                                                        WHERE " . DB_PREFIX . "users.user_id = " . DB_PREFIX . "projects.user_id
                                                        ORDER BY user_id ASC
                                                ");
                                                $users = $ilance->db->fetch_array($sql4);
                                                
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "attachment
                                                        (attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress)
                                                        VALUES(
                                                        NULL,
                                                        '" . $ilance->db->escape_string($uncrypted['attachtype']) . "',
                                                        '" . intval($users['user_id']) . "',
                                                        '" . intval($uncrypted['portfolio_id']) . "',
                                                        '" . intval($uncrypted['project_id']) . "',
                                                        '" . intval($uncrypted['pmb_id']) . "',
                                                        '" . intval($uncrypted['category_id']) . "',
                                                        '" . DATETIME24H . "',
                                                        '" . $ilance->db->escape_string($file_name) . "',
                                                        '" . $filedata . "',
                                                        '" . $ilance->db->escape_string($file_type) . "',
                                                        '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                                        '0',
                                                        '" . $ilance->db->escape_string($upload_file_size) . "',
                                                        '" . $ilance->db->escape_string($filehash) . "',
                                                        '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "')
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                $newattachid = $ilance->db->insert_id();
                                                
                                                ($apihook = $ilance->api('save_attachment_admin_user_end')) ? eval($apihook) : false;
                                        }
                                        
                                        // #### regular user uploading attachment
                                        else 
                                        {
										     
											  //herakle kkk
											  if($_SESSION['ilancedata']['user']['isstaff'] == '1')
											  {
											  $user_insert = $user_set;
											  $project_val = $uncrypted['project_id'];
											  }
											  else
											  {
											  $user_insert =  $_SESSION['ilancedata']['user']['userid'];
											  $project_val =  intval($uncrypted['project_id']);
											  }
										                  
                                                ($apihook = $ilance->api('save_attachment_regular_user_start')) ? eval($apihook) : false;
                                                
												
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "attachment
                                                        (attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress)
                                                        VALUES(
                                                        NULL,
                                                        '" . $ilance->db->escape_string($uncrypted['attachtype']) . "',
                                                        '" . $user_insert . "',
                                                        '" . intval($uncrypted['portfolio_id']) . "',
                                                        '" . $project_val . "',
                                                        '" . intval($uncrypted['pmb_id']) . "',
                                                        '" . intval($uncrypted['category_id']) . "',
                                                        '" . DATETIME24H . "',
                                                        '" . $ilance->db->escape_string($file_name) . "',
                                                        '" . $filedata . "',
                                                        '" . $ilance->db->escape_string($file_type) . "',
                                                        '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                                        '0',
                                                        '" . $ilance->db->escape_string($upload_file_size) . "',
                                                        '" . $ilance->db->escape_string($filehash) . "',
                                                        '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "')
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                $newattachid = $ilance->db->insert_id();
                                                
                                                ($apihook = $ilance->api('save_attachment_regular_user_end')) ? eval($apihook) : false;
                                        }
                                }
                                
                                return true;
                        }
                }
                
                ($apihook = $ilance->api('save_attachment_end')) ? eval($apihook) : false;
                
                return false;
        }
        
        /**
        * Function to remove a file attachment from the system for a specified user
        *
        * @param       integer      attachment id
        * @param       integer      user id
        *
        * @return      nothing
        */
        function remove_attachment($attachid, $userid)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                        FROM " . DB_PREFIX . "attachment
                        WHERE attachid = '".intval($attachid)."'
                            AND user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        if ($res['attachtype'] != 'ws')
                        {
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment
                                        WHERE attachid = '".intval($attachid)."'
                                            AND user_id = '".intval($userid)."'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                        }
                        else
                        {
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment
                                        WHERE attachid = '".intval($attachid)."'
                                            AND user_id = '".intval($userid)."'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "attachment_folder
                                        SET folder_size = folder_size - ".$res['filesize']."
                                        WHERE id = '".$res['tblfolder_ref']."'
                                ", 0, null, __FILE__, __LINE__);
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