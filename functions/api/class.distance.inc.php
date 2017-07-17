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
* Distance calculation class to perform the majority of distance and radius calculation functions in ILance.
*
* The current state of the distance calculation server currently supports:
* 
* a) Canada
* b) UK
* c) United States
* d) Netherlands
* e) Australia
* f) Germany
* g) Poland
* h) Spain
* i) India
* j) Belgium
* k) France
* l) Italy
* m) Japan
*
* @notes        Actual distance "datas" can be purchased online from a reliable geo-code solution provider.
*               DB Table Fields required are (3): ZIPCode, Longitude & Latitude
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class distance
{
        /**
        * Country ids to short form identifiers accepted in the distance calculation server
        */
        var $countries = array(
                '262' => 'UK',
                '330' => 'CAN',
                '500' => 'USA',
                '114' => 'NL',
		'307' => 'AUS',
                '361' => 'DE',
                '130' => 'PL',
		'156' => 'SP',
                '375' => 'IN',
                '315' => 'BE',
		'357' => 'FR',
		'381' => 'IT',
		'384' => 'JP'
        );
        
        /**
        * Accepted country ids allowed for distance calculation operations
        */
        var $accepted_countries = array(
                '262',
                '330',
                '500',
                '114',
		'307',
                '361',
                '130',
		'156',
                '375',
                '315',
		'357',
		'381',
		'384'
        );
	
	/**
	* Lables for the AdminCP > Distance area
	*/
	var $distance_titles = array(
		'CAN' => 'Canada',
                'USA' => 'United States',
                'UK' => 'United Kingdom',
                'NL' => 'Netherlands',
		'AUS' => 'Australia',
                'DE' => 'Germany',
                'PL' => 'Poland',
		'SP' => 'Spain',
                'IN' => 'India',
                'BE' => 'Belgium',
		'FR' => 'France',
		'IT' => 'Italy',
		'JP' => 'Japan'
	);
	
	/**
	* Cities for the AdminCP > Distance area
	*/
	var $distance_cities = array(
		'CAN' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'USA' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'UK' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'NL' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'AUS' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'DE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'PL' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'SP' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'IN' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'BE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'FR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'IT' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'JP' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />'
	);
	
	/**
	* States for the AdminCP > Distance area
	*/
	var $distance_states = array(
		'CAN' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'USA' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'UK' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'NL' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'AUS' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'DE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'PL' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'SP' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'IN' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'BE' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'FR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'IT' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'JP' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />'
	);
	
	/**
	* Area codes for the AdminCP > Distance area
	*/
	var $distance_areacodes = array(
		'CAN' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'USA' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'UK' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'NL' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'AUS' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'DE' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'PL' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'SP' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'IN' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'BE' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'FR' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'IT' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'JP' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />'
	);
	
	/**
	* Table counts for the AdminCP > Distance area
	*/
	var $distance_count = array(
		'CAN' => '774,014',
                'USA' => '70,706',
                'UK' => '1,969,257',
                'NL' => '435,296',
		'AUS' => '16,079',
                'DE' => '16,375',
                'PL' => '21,987',
		'SP' => '54,116',
                'IN' => '14,568',
                'BE' => '3,778',
		'FR' => '39,069',
		'IT' => '17,965',
		'JP' => '83,289'
	);
        	
        /**
        * Database tables to call based on a distance operation
        */
        var $dbtables = array(
                'CAN' => 'distance_canada',
                'USA' => 'distance_usa',
                'UK' => 'distance_uk',
                'NL' => 'distance_nl',
		'AUS' => 'distance_au',
                'DE' => 'distance_de',
                'PL' => 'distance_pl',
		'SP' => 'distance_sp',
                'IN' => 'distance_in',
                'BE' => 'distance_be',
		'FR' => 'distance_fr',
		'IT' => 'distance_it',
		'JP' => 'distance_jp',
                
		// when searching from Canada to x country
                'CANUSA' => array('distance_canada', 'distance_usa'),
		'CANUK' => array('distance_canada', 'distance_uk'),
                'CANNL' => array('distance_canada', 'distance_nl'),
		'CANAUS' => array('distance_canada', 'distance_au'),
                'CANDE' => array('distance_canada', 'distance_de'),
                'CANPL' => array('distance_canada', 'distance_pl'),
		'CANSP' => array('distance_canada', 'distance_sp'),
                'CANIN' => array('distance_canada', 'distance_in'),
                'CANBE' => array('distance_canada', 'distance_be'),
		'CANFR' => array('distance_canada', 'distance_fr'),
		'CANIT' => array('distance_canada', 'distance_it'),
		'CANJP' => array('distance_canada', 'distance_jp'),
                
		// when searching from US to x country
		'USACAN' => array('distance_usa', 'distance_canada'),
		'USAUK' => array('distance_usa', 'distance_uk'),
                'USANL' => array('distance_usa', 'distance_nl'),
		'USAAUS' => array('distance_usa', 'distance_au'),
                'USADE' => array('distance_usa', 'distance_de'),
                'USAPL' => array('distance_usa', 'distance_pl'),
		'USASP' => array('distance_usa', 'distance_sp'),
                'USAIN' => array('distance_usa', 'distance_in'),
                'USABE' => array('distance_usa', 'distance_be'),
		'USAFR' => array('distance_usa', 'distance_fr'),
		'USAIT' => array('distance_usa', 'distance_it'),
		'USAJP' => array('distance_usa', 'distance_jp'),
                
		// when searching from UK to x country
		'UKCAN' => array('distance_uk', 'distance_canada'),
		'UKUSA' => array('distance_uk', 'distance_usa'),
                'UKNL' => array('distance_uk', 'distance_nl'),
		'UKAUS' => array('distance_uk', 'distance_au'),
                'UKDE' => array('distance_uk', 'distance_de'),
                'UKPL' => array('distance_uk', 'distance_pl'),
		'UKSP' => array('distance_uk', 'distance_sp'),
                'UKIN' => array('distance_uk', 'distance_in'),
                'UKBE' => array('distance_uk', 'distance_be'),
		'UKFR' => array('distance_uk', 'distance_fr'),
		'UKIT' => array('distance_uk', 'distance_it'),
		'UKJP' => array('distance_uk', 'distance_jp'),
                
		// when searching from NL to x country
                'NLCAN' => array('distance_nl', 'distance_canada'),
		'NLUSA' => array('distance_nl', 'distance_usa'),
                'NLUK' => array('distance_nl', 'distance_uk'),
		'NLAUS' => array('distance_nl', 'distance_au'),
                'NLDE' => array('distance_nl', 'distance_de'),
                'NLPL' => array('distance_nl', 'distance_pl'),
		'NLSP' => array('distance_nl', 'distance_sp'),
                'NLIN' => array('distance_nl', 'distance_in'),
                'NLBE' => array('distance_nl', 'distance_be'),
		'NLFR' => array('distance_nl', 'distance_fr'),
		'NLIT' => array('distance_nl', 'distance_it'),
		'NLJP' => array('distance_nl', 'distance_jp'),
		
		// when searching from AUS to x country
		'AUSCAN' => array('distance_au', 'distance_canada'),
		'AUSUSA' => array('distance_au', 'distance_usa'),
                'AUSUK' => array('distance_au', 'distance_uk'),
		'AUSNL' => array('distance_au', 'distance_nl'),
                'AUSDE' => array('distance_au', 'distance_de'),
                'AUSPL' => array('distance_au', 'distance_pl'),
		'AUSSP' => array('distance_au', 'distance_sp'),
                'AUSIN' => array('distance_au', 'distance_in'),
                'AUSBE' => array('distance_au', 'distance_be'),
		'AUSFR' => array('distance_au', 'distance_fr'),
		'AUSIT' => array('distance_au', 'distance_it'),
		'AUSJP' => array('distance_au', 'distance_jp'),
                
                // when searching from DE to x country
		'DECAN' => array('distance_de', 'distance_canada'),
		'DEUSA' => array('distance_de', 'distance_usa'),
                'DEUK' => array('distance_de', 'distance_uk'),
		'DENL' => array('distance_de', 'distance_nl'),
                'DEAUS' => array('distance_de', 'distance_au'),
                'DEPL' => array('distance_de', 'distance_pl'),
		'DESP' => array('distance_de', 'distance_sp'),
                'DEIN' => array('distance_de', 'distance_in'),
                'DEBE' => array('distance_de', 'distance_be'),
		'DEFR' => array('distance_de', 'distance_fr'),
		'DEIT' => array('distance_de', 'distance_it'),
		'DEJP' => array('distance_de', 'distance_jp'),
                
                // when searching from PL to x country
		'PLCAN' => array('distance_pl', 'distance_canada'),
		'PLUSA' => array('distance_pl', 'distance_usa'),
                'PLUK' => array('distance_pl', 'distance_uk'),
		'PLNL' => array('distance_pl', 'distance_nl'),
                'PLAUS' => array('distance_pl', 'distance_au'),
                'PLDE' => array('distance_pl', 'distance_de'),
		'PLSP' => array('distance_pl', 'distance_sp'),
                'PLIN' => array('distance_pl', 'distance_in'),
                'PLBE' => array('distance_pl', 'distance_be'),
		'PLFR' => array('distance_pl', 'distance_fr'),
		'PLIT' => array('distance_pl', 'distance_it'),
		'PLJP' => array('distance_pl', 'distance_jp'),
		
		// when searching from SP to x country
		'SPCAN' => array('distance_sp', 'distance_canada'),
		'SPUSA' => array('distance_sp', 'distance_usa'),
                'SPUK' => array('distance_sp', 'distance_uk'),
		'SPNL' => array('distance_sp', 'distance_nl'),
                'SPAUS' => array('distance_sp', 'distance_au'),
                'SPDE' => array('distance_sp', 'distance_de'),
		'SPPL' => array('distance_sp', 'distance_pl'),
                'SPIN' => array('distance_sp', 'distance_in'),
                'SPBE' => array('distance_sp', 'distance_be'),
		'SPFR' => array('distance_sp', 'distance_fr'),
		'SPIT' => array('distance_sp', 'distance_it'),
		'SPJP' => array('distance_sp', 'distance_jp'),
                
                // when searching from IN to x country
		'INCAN' => array('distance_in', 'distance_canada'),
		'INUSA' => array('distance_in', 'distance_usa'),
                'INUK' => array('distance_in', 'distance_uk'),
		'INNL' => array('distance_in', 'distance_nl'),
                'INAUS' => array('distance_in', 'distance_au'),
                'INDE' => array('distance_in', 'distance_de'),
		'INPL' => array('distance_in', 'distance_pl'),
                'INSP' => array('distance_in', 'distance_sp'),
                'INBE' => array('distance_in', 'distance_be'),
		'INFR' => array('distance_in', 'distance_fr'),
		'INIT' => array('distance_in', 'distance_it'),
		'INJP' => array('distance_in', 'distance_jp'),
                
                // when searching from BE to x country
		'BECAN' => array('distance_be', 'distance_canada'),
                'BEUSA' => array('distance_be', 'distance_usa'),
                'BEUK' => array('distance_be', 'distance_uk'),
                'BENL' => array('distance_be', 'distance_nl'),
                'BEAUS' => array('distance_be', 'distance_au'),
                'BEDE' => array('distance_be', 'distance_de'),
                'BEPL' => array('distance_be', 'distance_pl'),
                'BESP' => array('distance_be', 'distance_sp'),
		'BEFR' => array('distance_be', 'distance_fr'),
		'BEIT' => array('distance_be', 'distance_it'),
		'BEJP' => array('distance_be', 'distance_jp'),
		
		// when searching from FR to x country
		'FRCAN' => array('distance_fr', 'distance_canada'),
                'FRUSA' => array('distance_fr', 'distance_usa'),
                'FRUK' => array('distance_fr', 'distance_uk'),
                'FRNL' => array('distance_fr', 'distance_nl'),
                'FRAUS' => array('distance_fr', 'distance_au'),
                'FRDE' => array('distance_fr', 'distance_de'),
                'FRPL' => array('distance_fr', 'distance_pl'),
                'FRSP' => array('distance_fr', 'distance_sp'),
		'FRBE' => array('distance_fr', 'distance_be'),
		'FRIT' => array('distance_fr', 'distance_it'),
		'FRJP' => array('distance_fr', 'distance_jp'),
		
		// when searching from IT to x country
		'ITCAN' => array('distance_it', 'distance_canada'),
                'ITUSA' => array('distance_it', 'distance_usa'),
                'ITUK' => array('distance_it', 'distance_uk'),
                'ITNL' => array('distance_it', 'distance_nl'),
                'ITAUS' => array('distance_it', 'distance_au'),
                'ITDE' => array('distance_it', 'distance_de'),
                'ITPL' => array('distance_it', 'distance_pl'),
                'ITSP' => array('distance_it', 'distance_sp'),
		'ITBE' => array('distance_it', 'distance_be'),
		'ITFR' => array('distance_it', 'distance_fr'),
		'ITJP' => array('distance_it', 'distance_jp'),
		
		// when searching from JP to x country
		'JPCAN' => array('distance_jp', 'distance_canada'),
                'JPUSA' => array('distance_jp', 'distance_usa'),
                'JPUK' => array('distance_jp', 'distance_uk'),
                'JPNL' => array('distance_jp', 'distance_nl'),
                'JPAUS' => array('distance_jp', 'distance_au'),
                'JPDE' => array('distance_jp', 'distance_de'),
                'JPPL' => array('distance_jp', 'distance_pl'),
                'JPSP' => array('distance_jp', 'distance_sp'),
		'JPBE' => array('distance_jp', 'distance_be'),
		'JPFR' => array('distance_jp', 'distance_fr'),
		'JPIT' => array('distance_jp', 'distance_it'),
        );
	
        /**
        * Function to fetch the distance between two postal/zip codes (postal/zip code 1 vs postal/zip code 2).
        * The usage of this calculation works best within Canada & USA although it will also work for other countries.
        * Additionally this function will accept two city names (city name 1 vs city name 2).  This function is now cachable.
        *
        * @param       string       zip code 1
        * @param       string       zip code 2
        * @param       string       zip code search type (de, usde, cande, nlde, etc)
        * @param       string       city 1
        * @param       string       city 2
        * @param       string       force which country datastore to use (CAN, USA, CANUSA, USACAN, etc)
        *
        * @return      integer      This function returns the actual distance between the two elements and
        *                           will also properly format the result calculation (based on Miles)
        *                           into KM if you have this option enabled within the AdminCP.
        */
        function fetch_distance_response($zipcode1 = '', $zipcode2 = '', $zipcodetype = '', $city1 = '', $city2 = '', $inputdistance = 0)
        {
                global $ilconfig, $ilance;

                $distance = '';
		
                $response = ($inputdistance > 0) ? $inputdistance : $this->fetch_distance($zipcode1, $zipcode2, $zipcodetype);
                if ($response > 0)
                {
                        $distance = ($ilconfig['globalserver_distanceformula'] > 0)
				? round(($response * $ilconfig['globalserver_distanceformula']), 1) . ' ' . $ilconfig['globalserver_distanceresults']
				: round($response, 1) . ' ' . $ilconfig['globalserver_distanceresults'];
                }
                else if ($zipcode1 == $zipcode2)
                {
                        $distance = '~ 1 ' . $ilconfig['globalserver_distanceresults'];
                }
                else if ($response == 0)
                {
                        $distance = '-';
                }
                
                return $distance;
        }
        
        /**
        * Function to print the results of the internal function fetch_distance_response to fetch the
        * distance between two postal/zip codes (postal/zip code 1 vs postal/zip code 2).
        *
        * @param       integer      country id 1
        * @param       string       zip code 1
        * @param       string       country id 2 (optional)
        * @param       string       zip code 2
        * @param       integer      input distance (optional if we already have a number and just want formatting)
        *
        * @return      string       This function returns the actual distance between two areas (ie: 39.4 KM)
        *                           otherwise the printed result will be " - "
        */
        function print_distance_results($countryid1 = 0, $zipcode1 = '', $countryid2 = 0, $zipcode2 = '', $inputdistance = 0)
        {   
                global $ilance, $ilconfig, $ilpage, $phrase;
                
                if (in_array($countryid1, $this->accepted_countries) AND in_array($countryid2, $this->accepted_countries) AND !empty($zipcode1) AND !empty($zipcode2))
                {
                        // #### SAME COUNTRIES #################################
                        if ($countryid1 == $countryid2)
                        {
                                return $this->fetch_distance_response($zipcode1, $zipcode2, $this->countries["$countryid1"], '', '', $inputdistance);
                        }
                        
                        // #### DIFFERENT COUNTRIES ############################
                        else
                        {
                                return $this->fetch_distance_response($zipcode1, $zipcode2, $this->countries["$countryid1"] . $this->countries["$countryid2"], '', '', $inputdistance);
                        }
                }
                
                return '-';
        }
        
        /**
        * Function to calculate the great circle distance between two zip or postal codes.
        *
        * @param       integer      latitude 1
        * @param       integer      longitude 1
        * @param       integer      latitude 2
        * @param       integer      longitude 2
        * @param       string       unit measure (K = km, N = nautical, M = miles)
        *
        * @return      integer      This function returns the distance between two longitude and latitude coordinates (default = miles)
        */
	function great_circle_distance($lat1 = '', $lon1 = '', $lat2 = '', $lon2 = '')
	{
		$theta = ($lon1 - $lon2);
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$final = ($dist * 60 * 1.1515);
	
		return $final;
	}
	
        /**
        * Function to calculate the great circle distance between two zip or postal codes.
        *
        * @param       string       zip code 1
        * @param       string       zip code 2
        * @param       string       zip code fetch type (de, usde, cande, nlde, etc)
        *
        * @return      integer      This function returns the distance between two longitude and latitude coordinates (default = miles)
        */
	function fetch_distance($zipcode1 = '', $zipcode2 = '', $zipcodetype = '')
	{
		global $ilance;
		
                $lat1 = $lon1 = $lat2 = $lon2 = 0;
		
		if (!empty($this->dbtables["$zipcodetype"]))
		{
			if (is_array($this->dbtables["$zipcodetype"]))
			{
				// zip code 1 and 2 from different countries
				$table1 = $this->dbtables["$zipcodetype"][0];
				$table2 = $this->dbtables["$zipcodetype"][1];
			}
			else
			{
				// zip code 1 and 2 from same country
				$table1 = $this->dbtables["$zipcodetype"];
				$table2 = $this->dbtables["$zipcodetype"];
			}
                        
			$sql = $ilance->db->query("
				SELECT Latitude, Longitude
				FROM " . DB_PREFIX . "$table1
				WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode1))) . "'
                                LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resl = $ilance->db->fetch_array($sql, DB_ASSOC);
				$lat1 = $resl['Latitude'];
				$lon1 = $resl['Longitude'];
			}
                        unset($resl, $sql);
		
			$sql = $ilance->db->query("
				SELECT Latitude, Longitude
				FROM " . DB_PREFIX . "$table2
				WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode2))) . "'
                                LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resl = $ilance->db->fetch_array($sql, DB_ASSOC);
				$lat2 = $resl['Latitude'];
				$lon2 = $resl['Longitude'];
			}
                        unset($resl, $sql);
		
			return $this->great_circle_distance($lat1, $lon1, $lat2, $lon2);
		}
		
		return 0;
	}	
	
        /**
        * Function to fetch a single specific longitude and latitude point for a given (zip, postal code or city name).
        *
        * @param       string       zip or postal code
        * @param       integer      country id
        *
        * @return      integer      This function returns the longitude and latitude points for a given zip/postal/city name
        */
	function fetch_zip_longitude_latitude($zipcode = '', $countryid = '')
	{
		global $ilance;
		
		if (in_array($countryid, $this->accepted_countries))
		{
                        $sql = $ilance->db->query("
				SELECT Latitude, Longitude
				FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
				WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
				LIMIT 1
			");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                return $ilance->db->fetch_array($sql);
                        }
		}			
                
                return array();
	}
	
        /**
        * Function to fetch valid sql code based on distance calculation mainly used within the search system
        *
        * @param       string       zip code
        * @param       integer      country id
        * @param       string       field name of zipcode
        *
        * @return      array        Returns array holding 'leftjoin' and their associated distance 'fields'
        */
        function fetch_sql_as_distance($zipcode = '', $countryid = 0, $fieldname = '')
        {
                $details = $this->fetch_zip_longitude_latitude($zipcode, $countryid);
                
                if (empty($details) OR !is_array($details) OR count($details) == 0)
		{
                        $details[0] = $details[1] = 0;
		}
                
                if (in_array($countryid, $this->accepted_countries))
                {
                        $return['leftjoin'] = " LEFT JOIN " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . " z ON $fieldname = z.ZIPCode ";
                        $return['fields'] = ", (3958 * 3.1415926 * sqrt((z.Latitude - $details[0]) * (z.Latitude - $details[0]) + cos(z.Latitude / 57.29578) * cos($details[0] / 57.29578) * (z.Longitude - $details[1]) * (z.Longitude - $details[1])) / 180) AS distance ";
                }
                
                if (!empty($return))
                {
                        return $return;
                }
                
                return false;
        }
        
        /**
        * Function to return an array of the zip or postal codes within $radius of $zipcode.
        * Returns an array with keys as the zip or postal codes and their cooresponding values as
        * the distance from the zipcode defined in $zipcode.
        *
        * @param       string       table name of requested zipcode data
        * @param       string       fieldname of zipcode
        * @param       string       zip or postal code
        * @param       integer      radius to search
        * @param       integer      country id
        * @param       boolean      include distance in the array output? ie: [90210] => 33.5 (default false)
        * @param       boolean      defines if the returned output is an SQL left join (to use later in search)
        * @param       boolean      defines if the returned output should only contain the city name for the zip code
        *
        * @return      integer      This function returns the longitude and latitude points for a given zip or postal code
        */
	function fetch_zips_in_range($zipcodetable = '', $fieldname = '', $zipcode = '', $radius = '', $countryid = '', $includedistance = false, $leftjoinonly = false, $radiusjoin = false, $fetchcityonly = false)
	{
		global $ilance;
	
		if ($fetchcityonly)
		{
			if (in_array($countryid, $this->accepted_countries))
                        {
				if ($ilance->db->field_exists('City', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]))
				{
					return $ilance->db->fetch_field(DB_PREFIX . $this->dbtables[$this->countries["$countryid"]], "ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'", "City");
				}
			}
			
			return false;
		}
		
		$details = $this->fetch_zip_longitude_latitude($zipcode, $countryid);
		if (count($details) == 0)
		{
                        $details[0] = $details[1] = 0;
		}
			
		$lat_range = ($radius / 69.172);
		$lon_range = abs($radius / (cos(deg2rad($details[0])) * 69.172));
		$min_lat = number_format($details[0] - $lat_range, '4', '.', '');
                $min_lon = number_format($details[1] - $lon_range, '4', '.', '');                
		$max_lat = number_format($details[0] + $lat_range, '4', '.', '');
		$max_lon = number_format($details[1] + $lon_range, '4', '.', '');
	
		$return = array();
                $return['condition'] = '';
	
		// #### prepare an sql statement for include in our advanced search
                if ($leftjoinonly)
                {
                        if (in_array($countryid, $this->accepted_countries))
                        {
                                $return['leftjoin'] = " LEFT JOIN " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . " z ON $fieldname = z.ZIPCode ";
                                $return['fields'] = ", (3958 * 3.1415926 * sqrt((z.Latitude - $details[0]) * (z.Latitude - $details[0]) + cos(z.Latitude / 57.29578) * cos($details[0] / 57.29578) * (z.Longitude - $details[1]) * (z.Longitude - $details[1])) / 180) AS distance ";
                                $return['condition'] = "AND z.Latitude BETWEEN '$min_lat' AND '$max_lat' AND z.Longitude BETWEEN '$min_lon' AND '$max_lon' ";
                        }
                }
		
		// #### ask the database for the zip, long and lat surrounding supplied zip code
                else
                {
                        if (in_array($countryid, $this->accepted_countries))
                        {
                                $sql = $ilance->db->query("
                                        SELECT c.ZIPCode, c.Latitude, c.Longitude
                                        FROM " . DB_PREFIX . "$zipcodetable
                                        LEFT JOIN " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . " c ON $fieldname = c.ZIPCode
                                        WHERE c.Latitude BETWEEN '$min_lat' AND '$max_lat' AND c.Longitude BETWEEN '$min_lon' AND '$max_lon'
                                ");
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                        {
                                                $dist = $this->great_circle_distance($details[0], $details[1], $res['Latitude'], $res['Longitude']);
                                                if ($includedistance)
                                                {
                                                        // double check that we are within our radius parameter
                                                        if ($dist <= $radius)
                                                        {
                                                                $return[$res['ZIPCode']] = round($dist, 2);
                                                        }
                                                }
                                                else
                                                {
                                                        // double check that we are within our radius parameter
                                                        if ($dist <= $radius)
                                                        {
                                                                $return[] = $res['ZIPCode'];
                                                        }
                                                }
                                        }
                                        
                                        if ($radiusjoin)
                                        {
                                                if (isset($return) AND count($return) > 0)
                                                {
                                                        $vmp = 'AND (';
                                                        foreach ($return AS $zipcode)
                                                        {
                                                                if (!empty($zipcode))
                                                                {
                                                                        $vmp .= " $fieldname LIKE '%" . $ilance->db->escape_string(format_zipcode($zipcode)) . "%' OR";
                                                                }
                                                        }
                                                        $tmp = $vmp;
                                                        $tmp = mb_substr($tmp, 0, -3);
                                                        $return['condition'] .= $tmp . ')';
                                                }
                                        }
                                }        
                        }
                }
		
		return $return;
	}
	
	function fetch_installed_countries()
	{
		global $ilance, $show;
		
		$show['nodistancerows'] = true;
		$rows = array();
		foreach ($this->dbtables AS $shortlng => $dbtable)
		{
			if (!is_array($dbtable) AND $ilance->db->table_exists(DB_PREFIX . $dbtable))
			{
				$sql = $ilance->db->query("
					SELECT ZIPCode
					FROM " . DB_PREFIX . $dbtable . "
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$show['nodistancerows'] = false;
					
					$rows['title'] = $this->distance_titles[$shortlng];
					$rows['cities'] = $this->distance_cities[$shortlng];
					$rows['states'] = $this->distance_states[$shortlng];
					$rows['areacodes'] = $this->distance_areacodes[$shortlng];
					$rows['zipcodecount'] = $this->distance_count[$shortlng];
					$rows[] = $rows;
				}
			}
		}
		
		return $rows;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>