<?php
class Craftsvilla_Productupdate_Block_Adminhtml_Productupdate_Renderer_Productname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
     
    public function render(Varien_Object $row)
    {	//print_r($row); exit;
        $modelData = $row->getData();
       	//print_r($modelData); exit;
        $entityId = $modelData['entity_id'];  
       	$connread = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $productQuery = "SELECT `value` FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$entiyId."'";
		$productQueryRes = $connread->query($productQuery)->fetch();
		$connread->closeConnection();
		$productName = $productQueryRes['value']; 
		
        $html = $productName;
        return $html;


    }
}
