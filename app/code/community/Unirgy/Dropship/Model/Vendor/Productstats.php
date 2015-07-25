<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Unirgy_Dropship_Model_Vendor_Productstats extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_productstats');
        parent::_construct();
    }
    
    public function setProductStats($vendorId = null, $productId = null)
    { 
        $date           = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $pageviewsCount = $this->getCollection()->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('product_id', $productId);
        if(count($pageviewsCount) == 0){
            $this->setVendorId($vendorId);
            $this->setProductId($productId);
            $this->setPageviews(1);
            $this->save();
        }else{
            $data = $pageviewsCount->getData();
            $this->setVendorId($vendorId);
            $this->setStatType($statType);
            $this->setPageviews($data[0]['pageviews']+1);
            $this->setId($data[0]['id']);
            $this->save();
        }
    }
    
    public function getPageviewByProduct($productId = null)
    {
        $model = $this->getCollection()->addFieldToFilter('product_id', $productId)->getData();
        if($model[0]['pageviews'] != null){
            return $model[0]['pageviews'];
        }else{
            return 0;
        }
    }
}
?>
