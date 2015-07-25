<?php

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$result = $db->query("select * from sales_flat_order limit 0,10")->fetchAll();
//while ($row=mysql_fetch_array($result)) {
foreach($result as $row){
  /*$process_id=$row["Id"];
  if ($row["Time"] > 5 ) {
    $sql="KILL $process_id";
    mysql_query($sql);
  }*/
	echo $row['entity_id']."<br>";
}
?>
