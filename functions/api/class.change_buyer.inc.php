<?php
	class change_buyer
	{
		function get_all_payment_pending_invoice_list($get_filtervalue)
		{
			global $ilance;
			$select_all_payment_pending_invoices = $ilance->db->query("
			SELECT projectid,invoiceid,p2b_user_id,user_id,amount,totalamount,taxamount,status
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '".$get_filtervalue."' 
			AND status = 'unpaid'	and not combine_project
			AND isfvf != 1
			AND isif != 1 
			AND isbuyerfee != 1 
			AND isenhancementfee != 1 ");

			return $select_all_payment_pending_invoices;
		}

		function get_all_buyerfee_invoice_list($old_buyer_id,$selected_coins)
		{
			global $ilance;
			$select_all_buyer_invoices = $ilance->db->query("
			SELECT invoiceid
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '".$old_buyer_id."' 
			AND status = 'paid'	
			AND projectid IN (".$selected_coins.") 
			and not combine_project
			AND isfvf != 1
			AND isif != 1 
			AND isbuyerfee = 1 
			AND isenhancementfee != 1 ");

			return $select_all_buyer_invoices;
		}
		function get_all_invoice_projects($old_buyer_id,$selected_coins)
		{
			global $ilance;
			$select_all_invoice_projects = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "invoice_projects
			WHERE buyer_id = '".$old_buyer_id."' 
			AND project_id IN (".$selected_coins.") ");

			return $select_all_invoice_projects;
		}		
		function get_all_buynow_order_list($old_buyer_id,$selected_coins,$selected_payment_invoice)
		{
			global $ilance;
			$select_all_buynow_orders = $ilance->db->query("
			SELECT orderid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE buyer_id = '".$old_buyer_id."' 
			AND project_id IN (".$selected_coins.") 
			AND invoiceid IN (".$selected_payment_invoice.") ");

			return $select_all_buynow_orders;
		}
		function get_all_bids_list($old_buyer_id,$selected_coins)
		{
			global $ilance;
			$select_all_bids = $ilance->db->query("
			SELECT bid_id
			FROM " . DB_PREFIX . "project_bids
			WHERE user_id = '".$old_buyer_id."' 
			AND project_id IN (".$selected_coins.") ");

			return $select_all_bids;
		}
		function get_all_proxy_bids_list($old_buyer_id,$selected_coins)
		{
			global $ilance;
			$select_all_proxy_bids = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "proxybid
			WHERE user_id = '".$old_buyer_id."' 
			AND project_id IN (".$selected_coins.") ");

			return $select_all_proxy_bids;
		}	
		function get_escorw_list($old_buyer_id,$selected_coins)
		{
			global $ilance;
			$select_escrow_id = $ilance->db->query("
			SELECT escrow_id
			FROM " . DB_PREFIX . "projects_escrow	
			WHERE user_id = '".$old_buyer_id."' 
			AND project_id IN (".$selected_coins.") ");

			return $select_escrow_id;
		}

		function changed_buyer_for_all_tables($changed_buyerid = 0,$selected_coins = 0,$selected_payment_invoice = 0, 
					$selected_buyerfee_invoice = 0,$selected_invoice_projects = 0, $selected_buynow_order = 0, 
					$selected_bids_id = 0, $selected_proxy_bids_id = 0,$selected_escrow_id = 0)
		{
			global $ilance;
			$selected_payment_invoice = $selected_payment_invoice ? $selected_payment_invoice : '0';
			$selected_buyerfee_invoice = $selected_buyerfee_invoice ? $selected_buyerfee_invoice : '0';
			$selected_invoice_projects = $selected_invoice_projects ? $selected_invoice_projects : '0';			
			$selected_buynow_order = $selected_buynow_order ? $selected_buynow_order : '0';
			$selected_bids_id = $selected_bids_id ? $selected_bids_id : '0';
			$selected_proxy_bids_id = $selected_proxy_bids_id ? $selected_proxy_bids_id : '0';
			$selected_escrow_id = $selected_escrow_id ? $selected_escrow_id : '0';


			// update projects tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "projects
                    SET winner_user_id = '" . $changed_buyerid . "'
                    WHERE project_id IN (".$selected_coins.")
                    AND filtered_auctiontype = 'regular' ");
            // update invoice tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "invoices
                    SET user_id = '" . $changed_buyerid . "'
                    WHERE invoiceid IN (".$selected_payment_invoice.") ");
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "invoices
                    SET user_id = '" . $changed_buyerid . "'
                    WHERE invoiceid IN (".$selected_buyerfee_invoice.") ");
            // update invoice projects tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "invoice_projects
                    SET buyer_id = '" . $changed_buyerid . "'
                    WHERE id IN (".$selected_invoice_projects.") ");
            // update buynow order tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "buynow_orders
                    SET buyer_id = '" . $changed_buyerid . "'
                    WHERE orderid IN (".$selected_buynow_order.") ");
            // update project bids tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "project_bids
                    SET user_id = '" . $changed_buyerid . "'
                    WHERE bid_id IN (".$selected_bids_id.") ");
            // update proxy bids tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "proxybid
                    SET user_id = '" . $changed_buyerid . "'
                    WHERE id IN (".$selected_proxy_bids_id.") ");
            // update escrow tbl
            $ilance->db->query("
                    UPDATE " . DB_PREFIX . "projects_escrow
                    SET user_id = '" . $changed_buyerid . "'
                    WHERE escrow_id IN (".$selected_escrow_id.") ");

			
		}



	}
?>