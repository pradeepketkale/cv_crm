<?php
class Craftsvilla_Vendorseo_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getVendorNameById($id) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "select vendor_name from vendor_info_craftsvilla where vendor_id =".$id. " LIMIT 1";
        $result = $read->query($query)->fetch();
        $read->closeConnection();
        return trim($result['vendor_name']);
       
    }
    
    public function getVendorSeoData($id) {
        $vendorSeoData = Mage::getModel("vendorseo/vendorseo")->getCollection();
        $vendorSeoData->addFieldToFilter('vendor_id',array('eq'=> $id)) ;
        return $vendorSeoData ;
    }
    
    public function getVendorList(){
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "select vendor_id, vendor_name from vendor_info_craftsvilla where vendor_name is not null and vendor_name <> '' order by vendor_name asc";
        $result = $read->query($query)->fetchAll();
        $read->closeConnection();
        $vendor_name = array('-1' => "Please select vendor.");
        
        foreach ($result as $key => $value) {
           $vendor_name[$value['vendor_id']] = $value['vendor_name'];
        }
        return $vendor_name ;
    }
    
    public function insertVendorSeoData($post_data,$vendorId){
        $resource = Mage::getSingleton('core/resource');
		  $writeConnection = $resource->getConnection('core_write');
        
        $readConnection = $resource->getConnection('core_read');
        
        $metaTitle = $post_data['meta_title'];
        $metaDescription = $post_data['meta_description'];
        $metaKeywords = $post_data['meta_keywords'];
        $vendorDescription = $post_data['vendor_description'];
        
        $selectQuery = "SELECT vendor_id FROM cv_vendor where vendor_id=".$vendorId;
        $result = $readConnection->query($selectQuery)->fetchAll();
        $readConnection->closeConnection();
        if(!empty($result))
        {
            $vendor_id = $result[0]['vendor_id'];
            $this->updateVendorSeoData($post_data,$vendor_id);
        }
        else {
        
            $insertQuery = "INSERT INTO cv_vendor (`vendor_id`,`meta_title`,`meta_description`,`meta_keywords`,`vendor_description`) values('$vendorId','$metaTitle','$metaDescription','$metaKeywords','$vendorDescription')" ;
          
            $writeConnection->query($insertQuery);
            $writeConnection->closeConnection();
        }
    }
    
    public function updateVendorSeoData($post_data,$vendor_id) {
        $resource = Mage::getSingleton('core/resource');
		  $writeConnection = $resource->getConnection('core_write');
        
        $metaTitle = $post_data['meta_title'];
        $metaDescription = $post_data['meta_description'];
        $metaKeywords = $post_data['meta_keywords'];
        $vendorDescription = $post_data['vendor_description'];
        
        $updateQuery = "Update cv_vendor set meta_title='$metaTitle', meta_description='$metaDescription', meta_keywords='$metaKeywords',vendor_description='$vendorDescription' WHERE vendor_id='$vendor_id' " ;
      
        $writeConnection->query($updateQuery);
        $writeConnection->closeConnection();
    }
}
	 