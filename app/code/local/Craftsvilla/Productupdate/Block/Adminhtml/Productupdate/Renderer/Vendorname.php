<?php
class Craftsvilla_Productupdate_Block_Adminhtml_Productupdate_Renderer_Vendorname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
     
    public function render(Varien_Object $row)
    {	//print_r($row); exit;
        $modelData = $row->getData();
        //print_r($modelData); exit;
        $vendorid = $modelData['udropship_vendor'];  
       $connread = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $productQuery = "SELECT `vendor_name` FROM `udropship_vendor` WHERE `vendor_id` = '".$vendorid."'";
		$productQueryRes = $connread->query($productQuery)->fetch();
		$connread->closeConnection();
		$vendorName = $productQueryRes['vendor_name']; 
		
        $html = $vendorName;
        //$html .= '<a href="'.$productUrl.'" target="_blank">'.$vendorName.'</a>';
        //$html .=  $this->getColumn()->getInlineCss();
        //$html .= '<br/><p>'.$row->getData($this->getColumn()->getIndex()).'</p>';
        return $html;


    }
}
