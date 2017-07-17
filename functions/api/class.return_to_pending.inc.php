<?php
	class return_to_pending
	{
		function get_coin_info($get_filtervalue)
		{
			global $ilance;
			$get_coin_details = $ilance->db->query("
			SELECT coin_id,user_id,consign_id,invoiceid
			FROM " . DB_PREFIX . "coin_return
			WHERE coin_id = '".$get_filtervalue."'  ");

			return $get_coin_details;
		}

		function putback_pending_for_returned_coins($selected_coins = 0,$selected_consign_id = 0,
				$selected_returned_invoice = 0, $selected_seller_id = 0)
		{

			global $ilance;
			$selected_coins = $selected_coins ? $selected_coins : '0';
			$selected_consign_id = $selected_consign_id ? $selected_consign_id : '0';
			$selected_returned_invoice = $selected_returned_invoice ? $selected_returned_invoice : '0';			
			$selected_seller_id = $selected_seller_id ? $selected_seller_id : '0';

			// update coins_retruned tbl
				// project_id , pending = 0

            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "coins_retruned
                    SET project_id = 0,
                    pending = 0
                    WHERE coin_id = '".$selected_coins."' ");

			// insert coins tbl
			$ilance->db->query("INSERT INTO " . DB_PREFIX . "coins
												SELECT * FROM " . DB_PREFIX . "coins_retruned 
												WHERE 	coin_id = '".$selected_coins."' 
												AND user_id = '".$selected_seller_id."'
												");			            

            // delete invoice tbl
            $ilance->db->query("
                    DELETE FROM " . DB_PREFIX . "invoices
                    WHERE invoiceid IN (".$selected_returned_invoice.") ");
            // delete coin_return tbl
            $ilance->db->query("
                    DELETE FROM " . DB_PREFIX . "coin_return
                    WHERE coin_id IN (".$selected_coins.")  
                    AND user_id IN (".$selected_seller_id.") ");            
            // delete coins_retruned tbl
            $ilance->db->query("
                    DELETE FROM " . DB_PREFIX . "coins_retruned
					WHERE coin_id = '".$selected_coins."' 
					AND user_id = '".$selected_seller_id."'  ");
			
		}



	}
?>