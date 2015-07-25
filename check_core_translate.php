<?php 


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

			$query = "SELECT * FROM `core_translate`";
			$s= $db->query($query)->fetch(); 
echo "Checked core translate!"; 
?>
