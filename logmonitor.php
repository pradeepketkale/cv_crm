<?php

/***************************************************
 * Magento Log File Contents Monitor. GNU/GPL
 * landau@fiascolabs.com
 * provided without warranty or support
 ***************************************************/

/***********************
 * Scan Magento local.xml file for connection information
 ***********************/

if (file_exists('./app/etc/local.xml')) {

$xml = simplexml_load_file('./app/etc/local.xml');

$tblprefix = $xml->global->resources->db->table_prefix;
$dbhost = $xml->global->resources->default_setup->connection->host;
$dbuser = $xml->global->resources->default_setup->connection->username;
$dbpass = $xml->global->resources->default_setup->connection->password;
$dbname = $xml->global->resources->default_setup->connection->dbname;

$tables = array(
   'dataflow_batch_export',
   'dataflow_batch_import',
   'log_customer',
   'log_quote',
   'log_summary',
   'log_summary_type',
   'log_url',
   'log_url_info',
   'log_visitor',
   'log_visitor_info',
   'log_visitor_online',
   'report_event'
);

} 

else {
    exit('Failed to open ./app/etc/local.xml');
}

/** Get current date, time, UTC and offset **/

$date   = date("Y-m-d");
$time   = date("H:i:s T");
$offset = date("P");
$utc    = gmdate("H:i:s");

/***********************
 * Start HTML output
 ***********************/

echo "<html><head><title>Magento Log File Contents on " . $date . " " . $time . "</title>
<meta http-equiv=\"refresh\" content=\"30\">
<style type=\"text/css\">html {width: 100%; font-family:  Arial,Helvetica, sans-serif;}
body {line-height:1.0em; font-size: 100%;}
table {border-spacing: 1px;}
th.stattitle {text-align: left; font-size: 100%; font-weight: bold; color: white; background-color: #101010;}
th {text-align: center; font-size: 90%; font-weight: bold; padding: 5px; border-bottom: 1px solid black; border-left: 1px solid black; }
td {font-size: 90%; padding: 4px; border-bottom: 1px solid black; border-left: 1px solid black;}
</style>
</head><body>";

/** Output title, connection info and cron job monitor runtime **/

echo "<h2>Magento Log File Contents Report</h2><h3>Connection: ".$dbuser."@".$dbhost."&nbsp;&nbsp;&ndash;&nbsp;&nbsp;Database: " . $dbname . "</h3>";
echo "<h3>Runtime: " . $date . "&nbsp;&nbsp;&nbsp;" .$time . "&nbsp;&nbsp;&nbsp;" . $utc . " UTC</h3>";
echo "<h4>Note: Your timezone offset is " . $offset . " hours from UTC</h4>";

/** Connect to database **/

$conn = mysql_connect($dbhost,$dbuser,$dbpass);
@mysql_select_db($dbname) or die("Unable to select database");

/***********************
 * Get table record counts
 ***********************/

echo '<table><tbody><tr><th class="stattitle" colspan="2">Log Tables</th></tr>';
echo '<tr><th>Table</th><th>Row Count</th></tr>';

foreach($tables as $tblname) {
   $result  = mysql_query("SELECT COUNT(*) FROM " . $tblprefix . $tblname) or die(mysql_error());
   $numrows = mysql_fetch_array($result);
   $num     = $numrows[0];

   /* Table output */
   echo '<tr>';
   echo '<td>'.$tblprefix.$tblname.'</td>';
   echo '<td align="right">'.$num."</td>"; 
   echo '</tr>';                 
}

echo '</tbody></table></body></html>';

/***********************
 * End of report
 ***********************/

mysql_close($conn);
?>