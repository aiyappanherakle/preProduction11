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
* CSV class to perform the majority of importing and exporting functions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

class csv
{
        /*
         Constructor
        */
        function csv()
        {
                
        }
        
        function csv_import($filename = '', $fields = array())
        {
                global $ilance, $ilconfig, $phrase;
                
                if (empty($filename))
                {
                        return false;
                }
                
                $html = '';
                $row_count = 0;
                $rows = array();
                
                $handle = fopen($filename, 'r');
                if (!$handle)
                {
                        $html['response'] = $phrase['_cannot_upload_bulk_item_data__no_records_found'];
                        return $html;
                }
                
                while (($data = fgetcsv($handle, 100000, ',', "'")) !== false)
                {
                        $row_count++;
                        foreach ($data AS $key => $value)
                        {
                                $value = str_replace("'", "''", $value);
                                $data["$key"] = "'" . $ilance->db->escape_string($value) . "'";
                        }
                        $rows[] = implode(",", $data);
                }
                
                fclose($handle);
        
                if (count($rows))
                {
                        $html['response'] = number_format($row_count) . ' ' . $phrase['_items_uploaded'];
                        $html['array'] = $rows;
                }
                else
                {
                        $html['response'] = $phrase['_cannot_upload_bulk_item_data__no_records_found'];
                        $html['array'] = array();
                }
                
                return $html;
        }
        
        function csv_to_db($file, $id, $bulk_id)
        {
        	global $ilance;
        		
        	$sql = $ilance->db->query('LOAD DATA INFILE "' . $file . '" INTO TABLE ' . DB_PREFIX . 'bulk_tmp CHARACTER SET UTF8 FIELDS TERMINATED BY "," ENCLOSED BY "\'" LINES TERMINATED BY "\r\n" (project_title, description, startprice, buynow_price, reserve_price, buynow_qty, project_details, filtered_auctiontype, cid, sample, currency) SET user_id = ' . $id .', bulk_id = ' . $bulk_id);
        
        	return $sql;
        }
        
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>