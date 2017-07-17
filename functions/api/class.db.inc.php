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

switch (DB_SERVER_TYPE)
{
        // #### mysql ##########################################################
        case 'mysql':
        {
                if (!empty($_SESSION['DIR_SERVER_ROOT']))
                {
                        require_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database.inc.php');
                        require_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database_mysql.inc.php');
                }
                else
                {
                        require_once(DIR_API . 'class.database.inc.php');
                        require_once(DIR_API . 'class.database_mysql.inc.php');
                }

                $ilance->db = new ilance_mysql($ilance);
                
                /**
                * fetch the mysql server version for use within any script (including installer)
                */
                $mysqlver = $ilance->db->query_fetch("SELECT version() AS version", 0, null, __FILE__, __LINE__);
                define('MYSQL_VERSION', $mysqlver['version']);
                define('MYSQL_ENGINE', (version_compare($mysqlver['version'], '4.0.18', '<')) ? 'TYPE' : 'ENGINE');
                define('MYSQL_TYPE', (version_compare($mysqlver['version'], '4.1', '<')) ? 'MyISAM' : 'MyISAM');
                unset($mysqlver);
        }
        break;
        
        // #### mysqli #########################################################
        case 'mysqli':
        {
                if (!empty($_SESSION['DIR_SERVER_ROOT']))
                {
                        require_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database.inc.php');
                        require_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database_mysqli.inc.php');
                }
                else
                {
                        require_once(DIR_API . 'class.database.inc.php');
                        require_once(DIR_API . 'class.database_mysqli.inc.php');
                }
                
                $ilance->db = new ilance_mysqli($ilance);
                
                /**
                * fetch the mysql server version for use within any script (including installer)
                */
                $mysqlver = $ilance->db->query_fetch("SELECT version() AS version", 0, null, __FILE__, __LINE__);
                define('MYSQL_VERSION', $mysqlver['version']);
                define('MYSQL_ENGINE', (version_compare($mysqlver['version'], '4.0.18', '<')) ? 'TYPE' : 'ENGINE');
                define('MYSQL_TYPE', (version_compare($mysqlver['version'], '4.1', '<')) ? 'MyISAM' : 'MyISAM');
                unset($mysqlver);
        }
        break;

        // #### mssql ##########################################################
        case 'mssql':
        {
                if (!empty($_SESSION['DIR_SERVER_ROOT']))
                {
                        require_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database.inc.php');
                        require_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database_mssql.inc.php');
                }
                else
                {
                        require_once(DIR_API . 'class.database.inc.php');
                        require_once(DIR_API . 'class.database_mssql.inc.php');
                }
                
                $ilance->db = new ilance_mssql($ilance);
                
                define('MYSQL_VERSION', '');
                define('MYSQL_ENGINE', '');
                define('MYSQL_TYPE', '');
        }
        break;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>