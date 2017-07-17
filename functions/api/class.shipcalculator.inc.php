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
* Shipping Calculator class to perform the majority of realtime shipping rate calculations in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class shipcalculator
{
        function shipcalculator()
        {
                
        }
        
        function get_rates($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $carriers = array(), $shipcode = '')
        {
                $service = array();

                // #### FedEx ##################################################
                if (isset($carriers['fedex']) AND $carriers['fedex'] AND $fedex = $this->get_fedex_rates($weight, $destination_zipcode, $destination_country, $origin_zipcode, $origin_country, $shipcode))
                {
                        $service = array_merge_recursive($service, $fedex);
                }
                
                // #### UPS ####################################################
                if (isset($carriers['ups']) AND $carriers['ups'] AND $ups = $this->get_ups_rates($weight, $destination_zipcode, $destination_country, $origin_zipcode, $origin_country, $shipcode))
                {
                        
                        $service = array_merge_recursive($service, $ups);
                }
                
                // #### USPS ###################################################
                if (isset($carriers['usps']) AND $carriers['usps'] AND $usps = $this->get_usps_rates($weight, $destination_zipcode, $destination_country, $origin_zipcode, $origin_country, $shipcode))
                {
                        $service = array_merge_recursive($service, $usps);
                }
        
                return $service;
        }
        
        function get_fedex_rates($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $shipcode = '01')
        {
                $weight = ($weight < 1) ? 1.0 : $weight;
                $service = $this->fedex_rateshop($weight, $destination_zipcode, $destination_country, $origin_zipcode, $origin_country, $shipcode);
                
                return $service;
        }
        
        function get_usps_rates($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $shipcode = 'Express')
        {
                global $ilance;
                
                $weight = ($weight < 1) ? 1.0 : $weight;
                $service = false;
                $usps_service = array();
                
                $sql = $ilance->db->query("
                        SELECT shipcode, title
                        FROM " . DB_PREFIX . "shippers
                        WHERE carrier = 'usps'
                        ORDER BY shipperid ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $usps_service[$res['shipcode']] = $res['title'];
                        }
                }
        
                $usps_result = $this->usps_rateshop($weight, $destination_zipcode, $destination_country, $origin_zipcode, $origin_country, $shipcode);
                $array = $this->xmlize($usps_result);
        
                if (isset($array["RateResponse"]["#"]["Package"]))
                {
                        $parser = $array["RateResponse"]["#"]["Package"];
        
                        for ($i = 0; $i < sizeof($parser); $i++)
                        {
                                if (isset($parser[$i]["#"]["Error"][0]["#"]["Number"][0]["#"]))
                                {
                                        $service["carrier"][$i]	= 'usps';
                                        $service["code"][$i] = 0;
                                        $service["name"][$i] = 0;
                                        $service["price"][$i] = 0;
                                }
                                else
                                {
                                        if (isset($usps_service[$parser[$i]["#"]["Service"][0]["#"]]))
                                        {
                                                $service["carrier"][$i]	= 'usps';
                                                $service["code"][$i] = $parser[$i]["#"]["Service"][0]["#"];
                                                $service["name"][$i] = $usps_service[$parser[$i]["#"]["Service"][0]["#"]];
                                                $service["price"][$i] = $parser[$i]["#"]["Postage"][0]["#"];
                                        }
                                }
                        }
                }
        
                return $service;
        }
        
        function get_ups_rates($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $shipcode = '03')
        {
                global $ilance;
                
                $weight = ($weight < 1) ? 1.0 : $weight;
                $service = false;
                $ups_service = array();
                
                $sql = $ilance->db->query("
                        SELECT shipcode, title
                        FROM " . DB_PREFIX . "shippers
                        WHERE carrier = 'ups'
                        ORDER BY shipperid ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $ups_service[$res['shipcode']] = $res['title'];
                        }
                }
                
                $ups_result = $this->ups_rateshop($weight, $destination_zipcode, $destination_country, $origin_zipcode, $origin_country, $shipcode);
                $array = $this->xmlize($ups_result);
        
                if (isset($array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["ResponseStatusCode"][0]["#"]))
                {
                        if ($array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["ResponseStatusCode"][0]["#"] != 0)
                        {
                                $parser = $array["RatingServiceSelectionResponse"]["#"]["RatedShipment"];
                                for($i = 0; $i < sizeof($parser); $i++)
                                {
                                        if (isset($ups_service[$parser[$i]["#"]["Service"][0]["#"]["Code"][0]["#"]]))
                                        {
                                                $service["carrier"][$i]	= 'ups';
                                                $service["code"][$i] = $parser[$i]["#"]["Service"][0]["#"]["Code"][0]["#"];
                                                $service["name"][$i] = $ups_service[$parser[$i]["#"]["Service"][0]["#"]["Code"][0]["#"]];
                                                $service["price"][$i] = $parser[$i]["#"]["TotalCharges"][0]["#"]["MonetaryValue"][0]["#"];
                                        }
                                }
                        }
                }
                
                return $service;
        }
        
        function ups_rateshop($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $shipcode = '03')
        {
                global $ilance;
                
                $weight = ($weight < 1) ? 1.0 : $weight;
                
                $sql = $ilance->db->query("
                        SELECT gatewayresult
                        FROM " . DB_PREFIX . "shipping_rates_cache
                        WHERE carrier = 'ups'
                                AND shipcode = '" . $ilance->db->escape_string($shipcode) . "'
                                AND from_country = '" . $ilance->db->escape_string($origin_country) . "'
                                AND from_zipcode = '" . $ilance->db->escape_string($origin_zipcode) . "'
                                AND to_country = '" . $ilance->db->escape_string($destination_country) . "'
                                AND to_zipcode = '" . $ilance->db->escape_string($destination_zipcode) . "'
                                AND weight = '" . $ilance->db->escape_string($weight) . "'
                ");
                if ($ilance->db->num_rows($sql) == 0)
                {
                        $xml = "
<?xml version=\"1.0\"?>
<AccessRequest xml:lang=\"en-US\">
        <AccessLicenseNumber>" . UPS_ACCESS_ID . "</AccessLicenseNumber>
        <UserId>" . UPS_PASSWORD . "</UserId>
        <Password>" . UPS_PASSWORD . "</Password>
</AccessRequest>
<?xml version=\"1.0\"?>
<RatingServiceSelectionRequest xml:lang=\"en-US\">
<Request>
        <TransactionReference>
                <CustomerContext>Rating and Service</CustomerContext>
                <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>Rate</RequestAction>
        <RequestOption>shop</RequestOption>
</Request>
<PickupType>
        <Code>01</Code>
</PickupType>
<Shipment>
        <Shipper>
                <Address>
                        <CountryCode>$origin_country</CountryCode>
                        <PostalCode>$origin_zipcode</PostalCode>
                </Address>
        </Shipper>
        <ShipTo>
                <Address>
                        <CountryCode>$destination_country</CountryCode>
                        <ResidentialAddress>1</ResidentialAddress>
                        <PostalCode>$destination_zipcode</PostalCode>
                </Address>
        </ShipTo>
        <Service>
                <Code>$shipcode</Code>
        </Service>
        <Package>
                <OversizePackage>0</OversizePackage>
                <Dimensions>
                        <Length>12</Length>
                        <Width>12</Width>
                        <Height>12</Height>
                </Dimensions>
                <PackagingType>
                        <Code>02</Code>
                        <Description>Package</Description>
                </PackagingType>
                <Description>Rate Shopping</Description>
                <PackageWeight>
                        <Weight>$weight</Weight>
                </PackageWeight>
        </Package>
<ShipmentServiceOptions/>
</Shipment>
</RatingServiceSelectionRequest>";

                        $ch = curl_init();
                        curl_setopt ($ch, CURLOPT_URL, UPS_SERVER);
                        curl_setopt ($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        
                        // #### save rate for later (up to 1 month) ############
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "shipping_rates_cache
                                (carrier, shipcode, from_country, from_zipcode, to_country, to_zipcode, weight, datetime, gatewayresult)
                                VALUES(
                                'ups',
                                '" . $ilance->db->escape_string($shipcode) . "',
                                '" . $ilance->db->escape_string($origin_country) . "',
                                '" . $ilance->db->escape_string($origin_zipcode) . "',
                                '" . $ilance->db->escape_string($destination_country) . "',
                                '" . $ilance->db->escape_string($destination_zipcode) . "',
                                '" . $ilance->db->escape_string($weight) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($result) . "')
                        ");
                }
                else
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "shipping_rates_cache
                                SET traffic = traffic + 1
                                WHERE carrier = 'ups'
                                        AND shipcode = '" . $ilance->db->escape_string($shipcode) . "',
                                        AND from_country = '" . $ilance->db->escape_string($origin_country) . "'
                                        AND from_zipcode = '" . $ilance->db->escape_string($origin_zipcode) . "'
                                        AND to_country = '" . $ilance->db->escape_string($destination_country) . "'
                                        AND to_zipcode = '" . $ilance->db->escape_string($destination_zipcode) . "'
                                        AND weight = '" . $ilance->db->escape_string($weight) . "'
                        ");
                        
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $result = $res['gatewayresult'];
                }                
        
                return $result;
        }
        
        function usps_rateshop($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $shipcode = 'Express')
        {
                global $ilance;
                
                $weight = ($weight < 1) ? 1.0 : $weight;
                
                $sql = $ilance->db->query("
                        SELECT gatewayresult
                        FROM " . DB_PREFIX . "shipping_rates_cache
                        WHERE carrier = 'usps'
                                AND shipcode = '" . $ilance->db->escape_string($shipcode) . "'
                                AND from_country = '" . $ilance->db->escape_string($origin_country) . "'
                                AND from_zipcode = '" . $ilance->db->escape_string($origin_zipcode) . "'
                                AND to_country = '" . $ilance->db->escape_string($destination_country) . "'
                                AND to_zipcode = '" . $ilance->db->escape_string($destination_zipcode) . "'
                                AND weight = '" . $ilance->db->escape_string($weight) . "'
                ");
                if ($ilance->db->num_rows($sql) == 0)
                {
                        $request = "<RateRequest USERID=\"" . USPS_LOGIN . "\" PASSWORD=\"" . USPS_PASSWORD . "\">";
                        
                        $sql = $ilance->db->query("
                                SELECT shipcode, title
                                FROM " . DB_PREFIX . "shippers
                                WHERE carrier = 'usps'
                                ORDER BY shipperid ASC
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $count = 0;
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $request .= "
<Package ID=\"$count\">
        <Service>$res[shipcode]</Service>
        <ZipOrigination>$origin_zipcode</ZipOrigination>
        <ZipDestination>$destination_zipcode</ZipDestination>
        <Pounds>$weight</Pounds>
        <Ounces>0</Ounces>
        <Container>None</Container>
        <Size>Regular</Size>
        <Machinable>False</Machinable>
</Package>";
                                        $count++;
                                }
                        }
                        
                        $request .= "</RateRequest>";

                        $xml = "API=Rate&XML=" . $request;
                
                        $string = USPS_SERVER . $xml;
                
                        $ch = curl_init();
                        curl_setopt ($ch, CURLOPT_URL, USPS_SERVER);
                        curl_setopt ($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        
                        // #### save rate for later (up to 1 month) ############
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "shipping_rates_cache
                                (carrier, shipcode, from_country, from_zipcode, to_country, to_zipcode, weight, datetime, gatewayresult)
                                VALUES(
                                'usps',
                                '" . $ilance->db->escape_string($shipcode) . "',
                                '" . $ilance->db->escape_string($origin_country) . "',
                                '" . $ilance->db->escape_string($origin_zipcode) . "',
                                '" . $ilance->db->escape_string($destination_country) . "',
                                '" . $ilance->db->escape_string($destination_zipcode) . "',
                                '" . $ilance->db->escape_string($weight) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($result) . "')
                        ");
                }
                else
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "shipping_rates_cache
                                SET traffic = traffic + 1
                                WHERE carrier = 'usps'
                                        AND shipcode = '" . $ilance->db->escape_string($shipcode) . "'
                                        AND from_country = '" . $ilance->db->escape_string($origin_country) . "'
                                        AND from_zipcode = '" . $ilance->db->escape_string($origin_zipcode) . "'
                                        AND to_country = '" . $ilance->db->escape_string($destination_country) . "'
                                        AND to_zipcode = '" . $ilance->db->escape_string($destination_zipcode) . "'
                                        AND weight = '" . $ilance->db->escape_string($weight) . "'
                        ");
                        
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $result = $res['gatewayresult'];
                }
        
                return $result;
        }
        
        function fedex_rateshop($weight = '1.0', $destination_zipcode = '', $destination_country = 'US', $origin_zipcode = '', $origin_country = 'US', $shipcode = '01')
        {
                global $ilance;
                
                $weight = ($weight < 1) ? 1.0 : $weight;
        
                $sql = $ilance->db->query("
                        SELECT gatewayresult
                        FROM " . DB_PREFIX . "shipping_rates_cache
                        WHERE carrier = 'fedex'
                                AND shipcode = '" . $ilance->db->escape_string($shipcode) . "'
                                AND from_country = '" . $ilance->db->escape_string($origin_country) . "'
                                AND from_zipcode = '" . $ilance->db->escape_string($origin_zipcode) . "'
                                AND to_country = '" . $ilance->db->escape_string($destination_country) . "'
                                AND to_zipcode = '" . $ilance->db->escape_string($destination_zipcode) . "'
                                AND weight = '" . $ilance->db->escape_string($weight) . "'
                ");
                if ($ilance->db->num_rows($sql) == 0)
                {
                        $data = '0,"25"';				// TransactionCode
                        $data .= '10,"' . FEDEX_ACCOUNT . '"';		// Sender fedex account number
                        $data .= '498,"' . FEDEX_ACCESS_ID . '"';	// Meter number
                        $data .= '9,"' . $origin_zipcode . '"';		// Origin postal code
                        $data .= '117,"' . $origin_country . '"';	// Origin country
                        $data .= '17,"' . $destination_zipcode . '"';	// Recipient zip code
                        $data .= '50,"' . $destination_country . '"';	// Recipient country
                        $data .= '75,"LBS"';				// Weight units
                        $data .= '1116,"I"';				// Dimension units
                        $data .= '1401,"' . $weight . '"';		// Total weight
                        $data .= '59,"12"';                             // Total length
                        $data .= '58,"12"';                             // Total width
                        $data .= '57,"12"';                             // Total height
                        $data .= '1529,"1"';				// Quote discounted rates
                        $data .= '1273,"01"';                         // Package type
                        if (isset($shipcode))
                        {
                                //$data .= '1274,"' . $shipcode . '"';    // Service type
                        }
                        $data .= '1333,"1"';				// Drop of drop off or pickup
                        $data .= '99,""';				// End of record
                
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_URL, FEDEX_SERVER);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Referer: iLance
Host: " . FEDEX_SERVER,
"Accept: image/gif,image/jpeg,image/pjpeg,text/plain,text/html,*/*",
"Pragma:",
"Content-Type:image/gif"));
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        
                        // #### save rate for later (up to 1 month) ############
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "shipping_rates_cache
                                (carrier, shipcode, from_country, from_zipcode, to_country, to_zipcode, weight, datetime, gatewayresult)
                                VALUES(
                                'fedex',
                                '" . $ilance->db->escape_string($shipcode) . "',
                                '" . $ilance->db->escape_string($origin_country) . "',
                                '" . $ilance->db->escape_string($origin_zipcode) . "',
                                '" . $ilance->db->escape_string($destination_country) . "',
                                '" . $ilance->db->escape_string($destination_zipcode) . "',
                                '" . $ilance->db->escape_string($weight) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($result) . "')
                        ");
                }
                else
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "shipping_rates_cache
                                SET traffic = traffic + 1
                                WHERE carrier = 'fedex'
                                        AND shipcode = '" . $ilance->db->escape_string($shipcode) . "'
                                        AND from_country = '" . $ilance->db->escape_string($origin_country) . "'
                                        AND from_zipcode = '" . $ilance->db->escape_string($origin_zipcode) . "'
                                        AND to_country = '" . $ilance->db->escape_string($destination_country) . "'
                                        AND to_zipcode = '" . $ilance->db->escape_string($destination_zipcode) . "'
                                        AND weight = '" . $ilance->db->escape_string($weight) . "'
                        ");
                        
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $result = $res['gatewayresult'];
                }
                
                $service = false;
                $fedex_service = array();
                
                $sql = $ilance->db->query("
                        SELECT shipcode, title
                        FROM " . DB_PREFIX . "shippers
                        WHERE carrier = 'fedex'
                        ORDER BY shipperid ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $fedex_service[$res['shipcode']] = $res['title'];
                        }
                }
                
                $current = 0;
                $length = strlen($result);
                $resultArray = array();
        
                while ($current < $length)
                {
                        $endpos = strpos($result, ',', $current);
                        if ($endpos === FALSE)
                        {
                                break;
                        }
        
                        $index = substr($result, $current, $endpos - $current);
                        $current = $endpos + 2;
                        $endpos = strpos($result, '"', $current);
                        $resultArray[$index] = substr($result, $current, $endpos - $current);
                        $current = $endpos + 1;
                }
        
                if (isset($resultArray[1133]))
                {
                        $i = 0;
                        $count = 1;
                        while ($count <= $resultArray[1133])
                        {
                                if (isset($fedex_service[$resultArray["1274-$count"]]))
                                {
                                        $service["carrier"][$i]	= 'fedex';
                                        $service["code"][$i] = $resultArray["1274-$count"];
                                        $service["name"][$i] = $fedex_service[$resultArray["1274-$count"]];
                                        $service["price"][$i] = $resultArray["1419-$count"];
                                        $i++;
                                }        
                                $count++;
                        }
                }
              
                return $service;
        }
        
        function xml_depth($vals, &$i)
        { 
                $children = array();
                
                if (isset($vals[$i]['value'])) array_push($children, $vals[$i]['value']); 
                while (++$i < count($vals))
                { 
                        switch ($vals[$i]['type'])
                        { 
                                case 'cdata':
                                        array_push($children, $vals[$i]['value']); 
                                break; 
                                case 'complete': 
                                        $tagname = $vals[$i]['tag'];
                                        if (isset($children["$tagname"]))
                                        {
                                                $size = sizeof($children["$tagname"]);
                                        }
                                        else
                                        {
                                                $size = 0;
                                        }
                                        if (isset($vals[$i]['value']))
                                        {
                                                $children[$tagname][$size]["#"] = $vals[$i]['value'];
                                        }
                                        if(isset($vals[$i]["attributes"]))
                                        {
                                                $children[$tagname][$size]["@"] = $vals[$i]["attributes"];
                                        }
                                break; 
        
                                case 'open': 
                                        $tagname = $vals[$i]['tag'];
                                        if (isset($children["$tagname"]))
                                        {
                                                $size = sizeof($children["$tagname"]);
                                        }
                                        else
                                        {
                                                $size = 0;
                                        }
                                        if(isset($vals[$i]["attributes"]))
                                        {
                                                $children["$tagname"][$size]["@"] = $vals[$i]["attributes"];
                                                $children["$tagname"][$size]["#"] = $this->xml_depth($vals, $i);
                                        }
                                        else
                                        {
                                                $children["$tagname"][$size]["#"] = $this->xml_depth($vals, $i);
                                        }
                                break; 
        
                                case 'close':
                                        return $children; 
                                break;
                        } 
        
                } 
        
                return $children;
        
        }
        
        function xmlize($data)
        {
                $vals = $index = $array = array();
                $parser = xml_parser_create();
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
                xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
                xml_parse_into_struct($parser, $data, $vals, $index);
                xml_parser_free($parser);
        
                $i = 0; 
        
                if (isset($vals[$i]['tag']))
                {
                        $tagname = $vals[$i]['tag'];
                        if (isset($vals[$i]["attributes"]))
                        {
                                $array[$tagname]["@"] = $vals[$i]["attributes"];
                        }
        
                        $array[$tagname]["#"] = $this->xml_depth($vals, $i);
                }
        
                return $array;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>