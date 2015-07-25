<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */  
class Amasty_Xcoupon_Model_Salesrule_Mysql4_Rule_Collection extends Mage_SalesRule_Model_Mysql4_Rule_Collection
{
    // we do not support versions less than 1.4.1.0
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode='', $now=null)
    {
        if ($couponCode)
            $couponCode = explode(',', $couponCode); // multiple coupon compatibility
              
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }
        
        /* We need to overwrite joinLeft if coupon is applied */
        $this->getSelect()->reset();
        $this->getSelect()->from(array('main_table' => $this->getMainTable()));        

        $this->getSelect()->where('is_active=1');
        $this->getSelect()->where('find_in_set(?, website_ids)', (int)$websiteId);
        $this->getSelect()->where('find_in_set(?, customer_group_ids)', (int)$customerGroupId);

        if ($couponCode) {
            $this->getSelect()->join(array('c' => $this->getTable('salesrule/coupon')), 
                'main_table.rule_id = c.rule_id ' . $this->getSelect()->getAdapter()->quoteInto('AND c.code IN(?)', $couponCode), 
                array('code')
            ); 
            $this->getSelect()->group('main_table.rule_id');
        } 
        else {
            $this->getSelect()->where('main_table.coupon_type = ?', Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON);
        }
        
        $this->getSelect()->where('from_date is null or from_date<=?', $now);
        $this->getSelect()->where('to_date is null or to_date>=?', $now);
        $this->getSelect()->order('sort_order');
        
        return $this;
    }
}