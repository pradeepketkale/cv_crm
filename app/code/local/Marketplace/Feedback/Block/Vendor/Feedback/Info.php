<?php

class Marketplace_Feedback_Block_Vendor_Feedback_Info extends Mage_Sales_Block_Items_Abstract
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
   
   
     public function getProductUdropshipVendor()
     {
        return $this->_product->getUdropshipVendor();
     }
     //2011-12-13 00:21:08
     
    public function getPositive()
    {
     $lastyear =  date('Y-m-d', strtotime('-1 year'));
     return  $this->_collection->addFilter('feedback', 1)->addFilter('feedback_at >' ,  $lastyear);
    }
    
    public function getNegative()
    {
      return $this->_collection->addFilter('feedback', '-1');

    }
    
    public function getNeutral()
    {
      return $this->_collection->addFilter('feedback', 0);
    }
   
   
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }
    
    public function getTotalFeedbackHtml()
    {
     $percentage = count($this->getCustomerFeedbacks()->getPositive()->getData())/count($this->getCustomerFeedbacks()->getData())*100;
     echo "Feedback: ".count($this->getCustomerFeedbacks()->getData())." ".$percentage ."% Positive";
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
   
   public function saveshipmentcomplete()
    {
    //Array ( [0] => Pending [10] => Exported [9] => Acknowledged [5] => Backorder [4] => On Hold [3] => Ready to Ship [8] => Pending Pickup [2] => Label(s) printed [1] => Shipped [7] => Delivered [6] => Canceled ) 
    
    
       $id = (int)$this->getRequest()->getParam('ship_id');
         if ($id) {  
              //print_r($statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash());
              $shipment = Mage::getModel('sales/order_shipment')->load($id);
              $_hlp = Mage::helper('udropship');
              $currentstat=$shipment->getUdropshipStatus();
             
              if($currentstat!=4 || $currentstat!=6 || $currentstat!=1)
              {
              $response = $_hlp->setShipmentComplete($shipment);
              return  "new";
              //echo $_hlp->getShipmentStatusName($shipment);
              }
              else
              {
                return "old";
              }
            //$this->setData('shipment', $shipment);
        }
        //return $this->getData('shipment');
    }
   
    public function getShipmentItem()
    {
      ///echo $this->getRequest()->getParam('id');
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
