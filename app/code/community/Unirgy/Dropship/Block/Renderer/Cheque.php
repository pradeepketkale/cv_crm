<?php
class Unirgy_Dropship_Block_Renderer_Cheque extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $data = Zend_Json::decode($row->getCustomVarsCombined());
        return $data['check_pay_to'];
    }
}
?>
