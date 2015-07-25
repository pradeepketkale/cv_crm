<?php

class Marketplace_Feedback_Block_Vendor_Feedbackinfo extends Mage_Core_Block_Template
{
    static $_vendorId;
    protected $_defaultToolbarBlock = 'feedback/page_pager';
    static $_feedCollection;
    
    public function getPositive()
    {
    
    }
    
    public function getNegative()
    {
    
    }
    
    public function getNeutral()
    {
    
    }
    public function getCustomerFeedbacks()
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
    
    
    
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        
        $collection = $this->getCustomerFeedbacks();
        
        $toolbar->setCollection($collection);
        
        $this->setChild('toolbar', $toolbar);
        Mage::dispatchEvent('testimonial_block_testimonial_collection', array(
            'collection'=>$this->getCustomerFeedbacks(),
        ));

        $this->getCustomerFeedbacks()->load();
        return parent::_beforeToHtml();
    }
    
    
   
   public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }
   
   
   public function getToolbarBlock()
    {
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }
 
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    public function setCollection($collection)
    {
        $this->_feedCollection = $collection;
        return $this;
    }

}
