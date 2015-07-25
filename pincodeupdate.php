<?php 




// path where your CSV file is located
//define('CSV_PATH','/var/www/html/development/var/import/');

$cid = Mage::getSingleton('core/resource')->getConnection('core_read');


define('CSV_PATH','/var/www/html/var/import/');

// Name of your CSV file
//$csv_file = CSV_PATH . "aramex_cod.csv";
//$csv_file = CSV_PATH . "aramex_updatedpincode_30092014.csv";
//$csv_file = CSV_PATH . "aramex_updatedpincode_26052015.csv";
//$csv_file = CSV_PATH . "fedex_updatedpincodesheet25-042015.csv";
//$csv_file = CSV_PATH . "aramex_updatedpincode_25042015.csv";
//$csv_file = CSV_PATH . "aramex_updatedpincode_16072015.csv";
$csv_file = CSV_PATH . "aramex_updatedpincode_26052015.csv";
//$csv_file = CSV_PATH . "fedex_updatedpincodesheet_state_04_062015.csv";

//$csv_file = CSV_PATH . "fedex_updatedpincodesheet_state_04_062015.csv";

$csvfile = fopen($csv_file, 'r');
$theData = fgets($csvfile);
$i = 0;
while (!feof($csvfile)) {

$csv_data[] = fgets($csvfile);
$csv_array = explode(",", $csv_data[$i]);
$insert_csv = array();
$insert_csv['pincode'] = $csv_array[0];
$insert_csv['is_cod'] = $csv_array[1];
$insert_csv['carrier'] = $csv_array[2]; 
$insert_csv['state'] = $csv_array[3];

			$query = "INSERT INTO checkout_cod_craftsvilla(pincode,is_cod,carrier,state)
			VALUES('".$insert_csv['pincode']."','".$insert_csv['is_cod']."','".$insert_csv['carrier']."','".$insert_csv['state']."')";
			
			//$s=mysql_query($query, $connect ); 
			$s=$db->query($query); 
			echo "Counter:";
			echo $i++;
	  
  }
fclose($csvfile);
echo "File data successfully imported to database!!"; 
mysql_close($connect); 
?>
