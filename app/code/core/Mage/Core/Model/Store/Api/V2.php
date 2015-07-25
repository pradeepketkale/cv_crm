<?php
class Mage_Core_Model_Store_Api_V2 extends Mage_Core_Model_Store_Api
{
 	/**
     * Retrieve countries list
     *
     * @return array
     */
public function itmes()
    {
    	$stores=Mage::getModel('core/store')->getCollection();
		$result=array();
		foreach ($stores as $store) {
				/*$result[]=array(
				'store_id'=>$store->getId(),
				'code'=>$store->getCode(),
				'website_id'=>$store->getWebsiteId(),
				'group_id'=>$store->getGroupId(),
				'name'=>$store->getName(),
				'sort_order'=>$store->getSortOrder(),
				'is_active'=>$store->getIsActive()
				);*/
			$store->getCode();
			$result[] = $store->toArray(array('store_id', 'code'));	
		}
        return $result;
    }
	/*public function info()
     {
    	$stores=Mage::getModel('core/store')->getCollection();
		$result=array();
		foreach ($stores as $store) {
				$result[]=array(
				'store_id'=>$store->getId(),
				'code'=>$store->getCode(),
				'website_id'=>$store->getWebsiteId(),
				'group_id'=>$store->getGroupId(),
				'name'=>$store->getName(),
				'sort_order'=>$store->getSortOrder(),
				'is_active'=>$store->getIsActive()
				);
		}
        return $result;
     }*/
}