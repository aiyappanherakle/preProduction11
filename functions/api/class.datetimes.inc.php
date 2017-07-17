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
* Date and Time class to perform the majority of date and timezone functions in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class datetimes
{
	var $tz_offset;
	var $days = array();
        
        /**
        * Constructor
        */
	function datetimes()
	{
		global $ilconfig, $ilance;
		
		define('DATETIME24H',
		gmdate(
			'Y-m-d H:i:s',
			time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))
		);
		define('DATETIME24HNODST',
		gmdate(
			'Y-m-d H:i:s',
			time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone']))
		);
		define('DATETODAY',
		gmdate(
			'Y-m-d',
			time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))
		);
		define('DATEYESTERDAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 1,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('DATETOMORROW',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 2,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('DATEIN30DAYS',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 30,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('DATEIN60DAYS',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 60,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('DATEIN90DAYS',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 90,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('DATEIN180DAYS',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 180,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('DATEIN365DAYS',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 365,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('ONEDAYFROMNOW',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 1,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('THREEDAYSFROMNOW',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 3,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('SIXDAYSFROMNOW',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 6,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('NINEDAYSFROMNOW',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 9,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('ONEDAYAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 1,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('THREEDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 3,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('SIXDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 6,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		// murugan changes on jan 28 for 7days ago
		define('SEVENDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 7,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('NINEDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 9,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('TWELVEDAYSAGO',
		gmdate('Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 12,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('FIFETEENDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 15,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('TWENTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 20,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('TWENTYNINEDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 29,
				gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('THIRTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 30,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('SIXTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 60,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('NINETYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 90,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		// Herakle Murugan Changes On Nov 8 For 180 Day and 360 DAy Ago
		
		define('ONEEIGHTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 180,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('THREESIXTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 360,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('SEVENTWENTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 720,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		define('THOUSANDEIGHTYDAYSAGO',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 1080,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		
		// Herakle Murugan Code End Here 
		define('TIMENOW',
		gmdate(
			'H:i:s',
			time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))
		);
		define('CURRENTHOUR',
		gmdate(
			'H',
			time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))
		);
		define('CURRENTYEAR',
		gmdate(
			'Y',
			time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))
		);
		define('TIMESTAMPNOW',
		mktime(
			gmdate(
				'H',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'i',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				's',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])))
		);
		define('DATEINVOICEDUE',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + $ilconfig['invoicesystem_maximumpaymentdays'] + 1,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])))) . ' ' . TIMENOW
		);
		define('DATETIME1Y',
		gmdate(
			'Y-m-d H:i:s',
			mktime(
				gmdate(
					'H',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					'i',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					's',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					'm',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					'd',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 365,
				gmdate(
					'Y',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('listaction',
		gmdate(
			'Y-m-d H:i:s',
			mktime(
				gmdate(
					'H',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					'i',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					's',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 10,
				gmdate(
					'm',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					'd',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				gmdate(
					'Y',
					time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('CURRENTAUCTION',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				
			gmdate('d')+ (7-$this->day_of_week(gmdate('Y'), gmdate('m'), gmdate('d'))),
			
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
				
		);		
		
		define('NEXTAUCTION',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
				
			gmdate('d')+ (7+(7-$this->day_of_week(gmdate('Y'), gmdate('m'), gmdate('d')))),
			
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
				
		);		
	}

	/**
        * Function to fetch the date in the past based on a supplied days argument
        */
        function fetch_date_ago($format = 'Y-m-d', $days)
	{
                global $ilconfig;
                
                $value = gmdate(
                        $format,
                        mktime(0, 0, 0,
                        gmdate(
                                'm',
                                time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
                        gmdate(
                                'd',
                                time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - $days,
                        gmdate(
                                'Y',
                                time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])))
                );
                
                return $value;
	}
	
	function fetch_date_fromnow($days)
	{
                global $ilconfig;
                
                $value = gmdate(
                    'Y-m-d',
                    mktime(0,0,0,
                    gmdate(
                            'm',
                            time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
                    gmdate(
                            'd',
                            time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + $days,
                    gmdate(
                            'Y',
                            time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))));
                
                return $value;
        }
    
	function fetch_days_between($m1, $d1, $y1, $m2, $d2, $y2)
	{
                return intval((mktime(0, 0, 0, $m2, $d2, $y2) - mktime(0, 0, 0, $m1, $d1, $y1)) / 86400);
	}
        
        /**
        * Function to determine if today's day is a business day (ie: sat & sun will return false)
        */
        function is_business_day()
        {
                $dotw = $this->day_of_week(gmdate('Y'), gmdate('m'), gmdate('d'));
                if ($dotw != 6 AND $dotw != 0)
                {
                        return 'true';
                }
                
                return 'false';
        }
    
	function is_leap_year($year)
	{
		if ((intval($year) % 4 == 0) AND (intval($year) % 100 != 0) OR (intval($year) % 400 == 0))
                {
			return 1;
                }
		else
                {
			return 0;
                }
	}
    
	function days_in_month($month, $year)
	{
		$days = array(
			1 => 31,
			2 => 28 + $this->is_leap_year(intval($year)),
			3 => 31,
			4 => 30,
			5 => 31,
			6 => 30,
			7 => 31,
			8 => 31,
			9 => 30,
			10 => 31,
			11 => 30,
			12 => 31
		);
                
		return $days[intval($month)];
	}

	function date_valid($year, $month, $day)
	{
		return checkdate(intval($month),intval($day),intval($year));
	}
    
	function time_valid($hour, $minutes, $seconds)
	{
		if (intval($hour) < 0 || intval($hour) > 24)
		{
			return False;
		}
		if (intval($minutes) < 0 || intval($minutes) > 59)
		{
			return False;
		}
		if (intval($seconds) < 0 || intval($seconds) > 59)
		{
			return False;
		}

		return True;
	}
    
        /**
        * Function to fetch the day of the week based on a supplied date argument.
        * 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday
        */
	function day_of_week($year, $month, $day)
	{
		if ($month > 2)
		{
			$month -= 2;
		}
		else
		{
			$month += 10;
			$year--;
		}
		$day = (floor((13 * $month - 1) / 5) + $day + ($year % 100) + floor(($year % 100) / 4) + floor(($year / 100) / 4) - 2 * floor($year / 100) + 77);
                
		return (($day - 7 * floor($day / 7)));
	}
    
	function day_of_year($year, $month, $day)
	{
		$days = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
		$julian = ($days[$month - 1] + $day);
		if ($month > 2 AND $this->is_leap_year($year))
		{
			$julian++;
		}
                
		return($julian);
	}

	function date_compare($a_year, $a_month, $a_day, $b_year, $b_month, $b_day)
	{
		$a_date = mktime(0, 0, 0, intval($a_month), intval($a_day), intval($a_year));
		$b_date = mktime(0, 0, 0, intval($b_month), intval($b_day), intval($b_year));
		if ($a_date == $b_date)
		{
			return 0;
		}
		else if ($a_date > $b_date)
		{
			return 1;
		}
		else if ($a_date < $b_date)
		{
			return -1;
		}
	}
    
	function time_compare($a_hour, $a_minute, $a_second, $b_hour, $b_minute, $b_second)
	{
		$a_time = mktime(intval($a_hour), intval($a_minute), intval($a_second), 0, 0, 70);
		$b_time = mktime(intval($b_hour), intval($b_minute), intval($b_second), 0, 0, 70);
		if ($a_time == $b_time)
		{
			return 0;
		}
		else if ($a_time > $b_time)
		{
			return 1;
		}
		else if ($a_time < $b_time)
		{
			return -1;
		}
	}

	function localdates($localtime)
	{
                global $ilance, $myapi;
                
                $date = array('raw', 'day', 'month', 'year', 'full', 'dow', 'dm', 'bd');
                
                $date['raw'] = $localtime;
                
                $date['year'] = intval($ilance->common->show_date($date['raw'], 'Y'));
                $date['month'] = intval($ilance->common->show_date($date['raw'], 'm'));
                $date['day'] = intval($ilance->common->show_date($date['raw'], 'd'));
                $date['full'] = intval($ilance->common->show_date($date['raw'], 'Ymd'));
                $date['bd'] = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);
                $date['dm'] = intval($ilance->common->show_date($date['raw'], 'dm'));
                $date['dow'] = $this->day_of_week($date['year'], $date['month'], $date['day']);
                $date['hour'] = intval($ilance->common->show_date($date['raw'], 'H'));
                $date['minute'] = intval($ilance->common->show_date($date['raw'], 'i'));
                $date['second'] = intval($ilance->common->show_date($date['raw'], 's'));
		
                return $date;
	}
    
	function gmtdate($localtime)
	{
		return $this->localdates($localtime - $this->tz_offset);
	}
	    
	function fetch_timestamp_from_datetime($datetime)
	{
		return strtotime($datetime);
	}
        
        function fetch_datetime_from_timestamp($timestamp)
        {
                return date("Y-m-d H:i:s", $timestamp);
        }
	
	function construct_timezone_pulldown($pulldowntype = 'registration', $variableinfo = '')
	{
                global $ilance, $ilconfig, $myapi;
                
                if ($pulldowntype == 'registration')
                {
                        $html = '<select name="timezone" style="font-family: verdana">';
                }
                else if ($pulldowntype == 'admin')
                {
                        $html = '<select name="config[' . $variableinfo . ']" style="font-family: verdana; width:260px">';
                        $ilconfig['globalserverlocale_officialtimezone'] = $ilance->db->fetch_field(DB_PREFIX . "configuration", "name = 'globalserverlocale_officialtimezone'", "value");
                }
                       
                $sql = $ilance->db->query("
                        SELECT timezoneid, timezone
                        FROM " . DB_PREFIX . "timezones
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if ($ilconfig['globalserverlocale_officialtimezone'] == $res['timezoneid'])
                                {
                                        $html .= '<option value="' . $res['timezoneid'] . '" selected="selected">' . $res['timezone'] . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['timezoneid'] . '">' . $res['timezone'] . '</option>';
                                }
                        }
                }
                
                $html .= '</select>';
		
                return $html;
	}
	
	function construct_user_timezone_pulldown()
	{
                global $ilance;
                
                $html  = '<select name="usertimezone" style="font-family: verdana">';
                
                $sql = $ilance->db->query("
                        SELECT timezoneid, timezone
                        FROM " . DB_PREFIX . "timezones
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (!empty($_SESSION['ilancedata']['user']['timezoneid']) AND $_SESSION['ilancedata']['user']['timezoneid'] == $res['timezoneid'])
                                {
                                        $html .= '<option value="' . $res['timezoneid'] . '" selected="selected">' . $res['timezone'] . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['timezoneid'] . '">' . $res['timezone'] . '</option>';
                                }
                        }
                }
                $html .= '</select>';
		
                return $html;
	}
	
	function timezone_convert($conv_fr_zon = 0, $conv_fr_time = '', $conv_to_zon = 0)
	{ 
		$date_val_arr = explode(" ", $conv_fr_time); 
		$send_date = $date_val_arr[0]; 
		$conv_hour_val = explode(":", $date_val_arr[1]); 
		$time_diff = $conv_fr_zon - $conv_to_zon; 
		
		$time_val = date("H:i:s", (time() + ($time_diff * 60 * 60)));
		$time_arr = explode(":", $time_val); 
		$time_diff_arr = explode(".", $time_diff); 
		
		$hour = $conv_hour_val[0]; 
		$hour = $hour - $time_arr[0]; 
		
		$send_hour = date("H"); 
		$send_hour = $send_hour+($hour); 
		
		$send_minit = $conv_hour_val[1]; 
		$send_minit = $send_minit-$time_arr[1]; 
		
		$minit = date("i"); 
		$minit = $minit+($send_minit); 
		
		if($time_diff<0) 
		   $send_hour=$send_hour-1; 
		
				    
		if($minit==-1) 
		{ 
		   if($time_diff>=0) 
		   $send_hour=$send_hour-1; 
		   $minit=59; 
		} 
		if($minit>=60) 
		{ 
		   $send_hour=$send_hour+1; 
		   $minit=$minit-60; 
		   $tmp_send_minit=$minit; 
		} 
		
		if($send_hour>24) 
		{ 
		   $send_date_arr=explode("-",$send_date); 
		   $send_date_arr[2]=$send_date_arr[2]+1; 
		   $send_date_arr=$this->makeproper($send_date_arr); 
		    
		   if($send_date_arr[2]<10) 
		   $send_date_arr[2]="0".$send_date_arr[2]; 
			    
		   $send_hour=$send_hour-24; 
		   $send_date=implode("-",$send_date_arr); 
		    
		} 
		elseif($send_hour<0) 
		{ 
		   $send_date_arr=explode("-",$send_date); 
		   $send_date_arr[2]=$send_date_arr[2]-1; 
		   $send_date_arr=$this->makeproper($send_date_arr); 
		    
		   if($send_date_arr[2]<10) 
		   $send_date_arr[2]="0".$send_date_arr[2]; 
		    
		   $send_date=implode("-",$send_date_arr); 
		   $send_hour=$send_hour+24; 
		} 
		$minit=abs($minit); 
		if(abs($send_hour)<10) 
		   $send_hour="0".$send_hour; 
		if($minit<10)                 
		   $minit="0".$minit; 
		$send_str=$send_date." ".$send_hour.":".$minit;  
		return $send_str; 
	}//function conver_to_time($conv_to_zon,$conv_fr_zon,$conv_fr_time="") 
	 
	function makeproper($arr) 
	{ 
	    $tempmonth=$arr[1]-1; 
	    if($tempmonth==2) 
	    { 
		if($arr[0]%4==0) 
		{ 
		    if($arr[2]>29) 
		    { 
			$arr[1]=$arr[1]+1; 
			$arr[2]=1; 
		    } 
		    elseif($arr[2]<=0) 
		    { 
			$arr[1]=$arr[1]-1; 
			$arr[2]=29; 
		    } 
		} 
		else 
		{ 
		    if($arr[2]>28) 
		    { 
			$arr[1]=$arr[1]+1; 
			$arr[2]=1; 
		    } 
		    elseif($arr[2]<=0) 
		    { 
			$arr[1]=$arr[1]-1; 
			$arr[2]=28; 
		    } 
		} 
	    } 
	    elseif($tempmonth==1 || $tempmonth==3 || $tempmonth==5 || $tempmonth==7 || $tempmonth==8 || $tempmonth==10 || $tempmonth==12) 
	    { 
		if($arr[2]>31) 
		{ 
		    $arr[1]=$arr[1]+1; 
		    $arr[2]=1; 
		} 
		elseif($arr[2]<=0) 
		{ 
		    $arr[1]=$arr[1]-1; 
		    $arr[2]=31; 
		} 
	    } 
	    elseif($tempmonth==4 || $tempmonth==6 || $tempmonth==9 || $tempmonth==11) 
	    { 
		if($arr[2]>30) 
		{ 
		    $arr[1]=$arr[1]+1; 
		    $arr[2]=1; 
		} 
		elseif($arr[2]<=0) 
		{ 
		    $arr[1]=$arr[1]-1; 
		    $arr[2]=30; 
		} 
	    } 
	    return $arr; 
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>