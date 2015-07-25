<?php
class Craftsvilla_Bulkuploadcsv_Block_Bulkuploadcsv extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBulkuploadcsv()     
     { 
        if (!$this->hasData('bulkuploadcsv')) {
            $this->setData('bulkuploadcsv', Mage::registry('bulkuploadcsv'));
        }
        return $this->getData('bulkuploadcsv');
        
    }
}