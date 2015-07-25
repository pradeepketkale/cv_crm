<?php

class Mage_Coupons_Model_Observer extends Mage_Core_Model_Abstract
  {
    function __construct(){}
    
    public function getDeleteCoupon(){ 
            
            $couponsData=Mage::getModel('salesrule/coupon')->getCollection()
                    ->addFieldToFilter('times_used',0);

            $today=date('Y-m-d');
            foreach($couponsData as $couponData)
            {
                $expiryDate=$couponData->getExpirationDate();
                $ruleid=$couponData->getRuleId();
                if($today>$expiryDate)
                {
                    foreach($ruleid as $ruleId){
                    $rule=Mage::getModel('salesrule/rule')->load($ruleId);
                    $rule->setIsActive('0')->save();
                    //$couponData->delete()->save();
                    }
                }
            }
        }
}
?>