<?php

class Marketplace_Feedback_Block_Vendor_Product_Info extends Mage_Sales_Block_Items_Abstract
{

   protected $_product = null;

    function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('product');
        }
        return $this->_product;
    }
   
   public function getVendorProductData(array $includeAttr = array())
    {
        $data = array();
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            if (in_array($attribute->getAttributeCode(), $includeAttr)) {
               //$value = $attribute->getFrontend()->getValue($product);
               $value = $product->getData($attribute->getAttributeCode());
                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code'  => $attribute->getAttributeCode()
                    );
                }
            }
        }
        return $data;
    }
   
    public function getShipmentItem()
    {
      if (!$this->hasData('shipment')) {
            $id = (int)$this->getRequest()->getParam('id');
            $shipment = Mage::getModel('sales/order_shipment_item')->load($id);
            $this->setData('shipment', $shipment);
        }
        return $this->getData('shipment');
    }
    
   
    public function getFeedbackforshipment($shipmentitems)
    {
      $feedback_id=$shipmentitems->getData('feedback_id');
            
               if(!empty($feedback_id))
       		 {
			$collection = Mage::getModel('feedback/vendor_feedback')->getCollection()
			->addFilter('feedback_id', $feedback_id);
			//->addAttributeToSelect('*');	
			//->addAttributeToFilter('feedback_id', array('lteq' => 5))
			//->addFilter('feedback_at', array('like' => '2%'))
       			 //->setPageSize(2);
       			 //->setPage(1,2)->load();
               	 	 //$collection->setOrder('feedback_at','DESC');
           } 
           //print_r($collection->getData());
           //return;
           return $collection;
    }
    
    public function getFeedbackShipmentItem()
    {
      $vendor = Mage::helper('umicrosite')->getCurrentVendor();
      $vendorid = $vendor->getId();
               if(!empty($vendorid))
       		 {
		$collection = Mage::getModel('feedback/vendor_feedback')->getCollection()
		->addFilter('vendor_id', $vendorid);
		//->addAttributeToSelect('*');	
		//->addAttributeToFilter('feedback_id', array('lteq' => 5))
		//->addFilter('feedback_at', array('like' => '2%'))
       		 //->setPageSize(2);
       		 //->setPage(1,2)->load();
                 $collection->setOrder('feedback_at','DESC');
           } 
            $page = $this->getRequest()->getParam('page');
            $currentPage = (int)$this->getRequest()->getParam('page');
	    if(!$currentPage){
	        $currentPage = 1;
	    }
	    $currentLimit = (int)$this->getRequest()->getParam('limit');
	        if(!$currentLimit){
	        $currentLimit = 1;
	    }
	    
	    $collection->setPageSize($currentLimit);
	   // echo count($collection)."<br>";
	    
	    $collection->setCurPage($currentPage);
	    //echo count($collection)."<br>";
      
      
       return  $collection;
    
     echo $this->getRequest()->getParam('id');
     if (!$this->hasData('shipment')) {
            $id = (int)$this->getRequest()->getParam('id');
            $shipment = Mage::getModel('sales/order_shipment_item')->load($id);
            $this->setData('shipment', $shipment);
        }
        return $this->getData('shipment');
    }
    
}
