<?php

class Marketplace_Feedback_Block_Vendor_Feedback_Pendinginfo extends Mage_Sales_Block_Items_Abstract
{
   static $_product = null;
   static $_collection;
   
   public function __construct($product = null )
    {
        if(isset($product)) 
        {
        $this->getProduct();
        }
       
    }
    protected function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('product');
        }
        return $this->_product;
    }
   
   
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }
    
   
    public function getCustomerFeedbacks()
    {
      //$vendor = Mage::helper('umicrosite')->getCurrentVendor();
      $vendorid = $this->getProductUdropshipVendor();
      
               if(!empty($vendorid))
       		 {
		$collection = Mage::getModel('feedback/vendor_feedback')->getCollection()
		->addFilter('vendor_id', $vendorid);
                 $collection->setOrder('feedback_at','DESC');
               } 
            $this->setCollection($collection);
            return $this;
//           return  $collection->getData();
           
           
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
    }
   
	public function getShipment()
    {	
		$id = $_SESSION['feedback_shippment_id'];
		$shipment = '';
		if($this->getRequest()->getParam('id')){
			$id = (int)$this->getRequest()->getParam('id');
			$_SESSION['feedback_shippment_id'] = '';
			$_SESSION['feedback_shippment_id'] = $id;
		}
		if($id) {
			$shipment = Mage::getModel('sales/order_shipment')->load($id);
			return $shipment;
		}
    }
    
}
