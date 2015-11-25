<?php
class Unirgy_Dropship_Block_Renderer_Accountnumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
		try{        
		$data = Zend_Json::decode($row->getCustomVarsCombined());
        return $data['bank_ac_number'];
		}
		catch (Exception $e) {
			echo 'Failed';
			}

    }
}
?>
