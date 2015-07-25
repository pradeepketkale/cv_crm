<?php
class Craftsvilla_Homepage_Block_Homepage extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		 /*$this->setChild( 'grid',
        $this->getLayout()->createBlock( 'homepage/adminhtml_homepage2_grid', 'adminhtml_homepage.grid')->setSaveParametersInSession(true) );*/
		return parent::_prepareLayout();
    }
    
     public function getHomepage()     
     { 
        if (!$this->hasData('homepage')) {
            $this->setData('homepage', Mage::registry('homepage'));
        }
        return $this->getData('homepage');
        
    }
}