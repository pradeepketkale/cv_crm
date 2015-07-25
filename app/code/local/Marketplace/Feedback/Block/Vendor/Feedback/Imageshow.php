<?php

class Marketplace_Feedback_Block_Vendor_Feedback_Imageshow extends Mage_Sales_Block_Items_Abstract
{
    public function getImage()
    {	
		$img = '';
		if($this->getRequest()->getParam('id')){
			$id = (int)$this->getRequest()->getParam('id');
			$Feedback = Mage::getModel('feedback/vendor_feedback')->getCollection()
						->addFieldToFilter('feedback_id',$id)
						->addFieldToSelect('image_path')->getData();
			//echo "<pre>"; print_r($Feedback); exit;
			if($Feedback[0]['image_path'])
				$img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."feedback".$Feedback[0]['image_path'];
		}
		return $img;
    }
}
