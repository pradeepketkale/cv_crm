<?php

class Unirgy_Dropship_Block_Renderer_Courier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row)
    {
        $shipment=Mage::getModel('sales/order_shipment_track')->getCollection()
                ->addFieldToFilter('parent_id',$row->getId());
        $c=0;
        foreach($shipment as $k => $v) {
            $sku=$v['courier_name'];
            echo $sku.'<br />';
        }return;
    }   

}
?>
