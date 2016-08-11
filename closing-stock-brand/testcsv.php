 <?php 
	$csvResultArr = array('Entity_id','Sku','ProductName','Price','Qty','Color','Size','Category','Sub-Category', 'vendor name');	
	$path ='/tmp/dailyreport_vendors.csv';
	$fp = fopen($path, 'w');
	fputcsv($fp, $csvResultArr, ',', '"');

	$fileatt_type = "text/csv";
	$myfile = $path ; //"myfile.csv";

        $file_size = filesize($myfile);
        $handle = fopen($myfile, "r");
        $content = fread($handle, $file_size);
        fclose($handle);

        $content = chunk_split(base64_encode($content));

        $message = "<html>
		<head>
		  <title>List of New Price Changes</title>
		</head>
		<body><table><tr><td>MAKE</td></tr></table></body></html>";

        $uid = md5(uniqid(time()));

        $header = "From: Places <places@craftsvilla.com>\r\n";
        #$header .= "Reply-To: ".$replyto."\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-type:text/html; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= $message."\r\n\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: text/csv; name=\"dailyreport_vendors.csv\"\r\n"; // use diff. tyoes here
    	$header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"dailyreport_vendors.csv\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
        $header .= "--".$uid."--";

	mail('rajesh.bishnoi@tejora.com', 'kuchbhi', $message, $header);
	unlink('/tmp/dailyreport_vendors.csv');
?>
