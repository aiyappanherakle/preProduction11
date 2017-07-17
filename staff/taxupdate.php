 <?php
 require_once('./../functions/config.php');
 if (isset($ilance->GPC['taxupdate']))
                {
				
					$column_names = array('zipcode','amount','reportingcodecounty');	  
				 
					
					if((!empty($_FILES['upload'])) && ($_FILES['upload']['error'] == 0))
					{						
						if($_FILES['upload']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
						{						
							if($_FILES['upload']['size'] > 1000000)
							{
								print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
								exit();
							}
							else
							{
								$handle = fopen($_FILES['upload']['tmp_name'],'r');
								$row_count = 0;	
																		
								while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
								 
							   { 							
									$row_count++;
									if ($row_count==1) continue;
									/*if(count($data) != count($column_names))
									{
									print_action_failed("We're sorry. CSV file is not correct. Number of columns in
	 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
								exit();
									}*/
								
									     $temp_data['ZipCode'] = $data[0];								
										   $temp_data['SalesTaxRate'] = $data[1];
										   $temp_data['ReportingCodeCounty'] = $data[2];
									 $sqlusercheck = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "taxes
											WHERE zipcode = '".$temp_data['ZipCode']."'");
									 if($ilance->db->num_rows($sqlusercheck) > 0)
									{
										
										   
										   $ilance->db->query("UPDATE ".DB_PREFIX."taxes
										   						SET amount = '".$temp_data['SalesTaxRate']."' ,
																    reportingcodecounty = '".$temp_data['ReportingCodeCounty']."' 
																WHERE zipcode = '".$temp_data['ZipCode']."'");
										   
									}
									else
									  {
									  	
									continue;
									
		
							
									}
										
																															
								 }							
							  
							}							
						}
						
						else
						{
							print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
							exit();
						}	
						
						fclose($handle);
						print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF']);
									exit();									
					}			
					else 
					{
					   
						print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
						exit();							
					}
                      
                }
				
?>

<form enctype="multipart/form-data" style="margin: 0px;" accept-charset="UTF-8" action="taxupdate.php" method="post" id="catalog">
<input type="file" name="upload">
<input type="submit" name="taxupdate" value="Go">
</form>