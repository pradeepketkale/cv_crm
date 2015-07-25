<?php
class Unirgy_Dropship_Block_Renderer_Noofproducts extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $query = "SELECT count(*) as total  FROM `catalog_product_flat_1` as cpf join `cataloginventory_stock_item` as csi on csi.product_id=cpf.entity_id  WHERE cpf.udropship_vendor = ".$row->getVendorId()." and cpf.visibility !=1 And csi.qty > 0 And csi.is_in_stock=1";
        $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);        
        return $data[0]['total'];
    }
}
?>
