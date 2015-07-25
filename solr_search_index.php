<?php 
error_reporting(E_ALL & ~E_NOTICE);

$url ='http://175.41.147.59:8983/solr/dataimport?command=full-import';

$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 0); 
		curl_setopt($ch,CURLOPT_URL, $url);
		//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt( $ch, CURLOPT_POST, 1);
		//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		//echo $response;
		if(curl_errno($ch))
			{		
				print curl_error($ch);
			}
curl_close($ch);	
?>
