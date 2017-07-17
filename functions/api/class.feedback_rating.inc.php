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
* Feedback rating class to perform the majority of feedback and rating functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class feedback_rating extends feedback
{
        /*
        * Function to insert a feedback rating from one user to another
        *
        * @param        integer     project id
        * @param        integer     for user id
        * @param        integer     from user id
        * @param        array       criteria ratings (only when giving feedback to seller)
        * @param        string      feedback comments
        * @param        string      feedback type (seller or buyer)
        * @param        string      feedback response experience (positive/neutral/negative)
        * @param        integer     extra bit for addons (stores, etc)
        *
        * @return	string      Returns true if feedback was successfully inserted into the database
        */
        function insert_feedback_rating($projectid = 0, $for_user_id = 0, $from_user_id = 0, $criteria = array(), $comments = '', $from_type = '', $response = '', $extra = 0)
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $show;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "feedback
                        (id, for_user_id, project_id, from_user_id, comments, date_added, response, type)
                        VALUES(
                        NULL,
                        '" . intval($for_user_id) . "',
                        '" . intval($projectid) . "',
                        '" . intval($from_user_id) . "',
                        '" . $ilance->db->escape_string($comments) . "',
                        '" . DATETIME24H . "',
                        '" . $ilance->db->escape_string($response) . "',
                        '" . $ilance->db->escape_string($from_type) . "')
                ", 0, null, __FILE__, __LINE__);
                
                $feedbackid = $ilance->db->insert_id();
                
                ($apihook = $ilance->api('insert_feedback_rating_start')) ? eval($apihook) : false;
                
                $criteriapreview = '--';
                if ($from_type == 'seller')
                {
                        $criteriapreview = '';
                        if (isset($criteria) AND is_array($criteria))
                        {
                                foreach ($criteria AS $id => $rating)
                                {
                                        $criteriapreview .= "* " . $this->fetch_title($id) . ": $rating\n";
                                        
                                        // insert detailed seller ratings
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "feedback_ratings
                                                (id, user_id, project_id, criteria_id, rating)
                                                VALUES
                                                (NULL,
                                                '" . intval($for_user_id) . "',
                                                '" . intval($projectid) . "',
                                                '" . intval($id) . "',
                                                '{$rating}')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $newratingid = $ilance->db->insert_id();
                                        
                                        ($apihook = $ilance->api('insert_feedback_rating_foreach_end')) ? eval($apihook) : false;
                                }
                        }
                }
                
                $title = ($extra == 0) ? fetch_auction('project_title', $projectid) : '';
                
                ($apihook = $ilance->api('insert_feedback_rating_end')) ? eval($apihook) : false;
                
                $ilance->email = construct_dm_object('email', $ilance);

                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                
                $ilance->email->get('feedback_complete_admin');		
                $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', $from_user_id),
                        '{{customer}}' => fetch_user('username', $for_user_id),
                        '{{project_title}}' => $title,
                        '{{project_id}}' => $projectid,
                        '{{feedback_comments}}' => $comments,
                        '{{ratings}}' => $criteriapreview                        
                ));
                
                $ilance->email->send();

                $ilance->email->mail = fetch_user('email', $for_user_id);
                $ilance->email->slng = fetch_user_slng($for_user_id);
                
                $ilance->email->get('feedback_complete_buyer');		
                $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', $from_user_id),
                        '{{customer}}' => fetch_user('username', $for_user_id),
                        '{{project_title}}' => $title,
                        '{{project_id}}' => $projectid,
                        '{{feedback_comments}}' => $comments,
                        '{{ratings}}' => $criteriapreview
                        
                ));
                
                $ilance->email->send();
                
                if ($from_type == 'seller')
                {
                        // #### buyer rating seller for buy now purchase #######
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "buynow_orders
                                SET buyerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND owner_id = '" . intval($for_user_id) . "'
                                        AND buyer_id = '" . intval($from_user_id) . "'
                        ");
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects_escrow
                                SET buyerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND project_user_id = '" . intval($for_user_id) . "'
                                        AND user_id = '" . intval($from_user_id) . "'
                        ");
                        
                        // #### buyer rating service provider ##################
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET buyerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND project_state = 'service'
                                LIMIT 1
                        ");
                        
                        // #### buyer rating seller ############################
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET buyerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND project_state = 'product'
                                LIMIT 1
                        ");
                        
                        ($apihook = $ilance->api('insert_feedback_rating_seller_end')) ? eval($apihook) : false;
                }
                else
                {
                        // #### seller rating buyer for buy now purchase #######
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "buynow_orders
                                SET sellerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND owner_id = '" . intval($from_user_id) . "'
                                        AND buyer_id = '" . intval($for_user_id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        // #### seller rating buyer for escrow won purchase ####
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects_escrow
                                SET sellerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                        AND project_user_id = '" . intval($from_user_id) . "'
                                        AND user_id = '" . intval($for_user_id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        // #### service provider rating buyer ##################
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET sellerfeedback = '1'
                                WHERE project_id = '" . intval($projectid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        
                        ($apihook = $ilance->api('insert_feedback_rating_buyer_end')) ? eval($apihook) : false;
                }
                
                return true;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>