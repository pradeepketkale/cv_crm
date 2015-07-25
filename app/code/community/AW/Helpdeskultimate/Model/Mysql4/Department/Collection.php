<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @version    2.9.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Helpdeskultimate_Model_Mysql4_Department_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/department');


    }

    public function addActiveFilter()
    {
        return $this->setActiveFilter();
    }

    public function setActiveFilter()
    {
        $this->getSelect()
                ->where("enabled!=?", 0);
        return $this;
    }

    public function setVisibilityFilter()
    {
        $this->getSelect()
                ->where("visibility=?", 1);
        return $this;
    }

    public function setContactFilter($email)
    {
        $this->getSelect()
                ->where("contact=?", $email);
        return $this;
    }

    public function setPrimaryStoreIdFilter($id)
    {
        $this->getSelect()
                ->where("primary_store_id=?", $id);
        return $this;
    }

    /**
     * Adds filter by gateway_id
     * @param object $id
     * @return
     */
    public function setGatewayIdFilter($id)
    {
        $this->getSelect()
                ->where("gateways='$id'
                OR gateways LIKE '%,id'
                OR gateways LIKE '%,id,%'
                OR gateways LIKE 'id,%'
            ");
        return $this;
    }

    public function getAffectedStores()
    {
        $this->getSelect()
                ->distinct()
                ->from(array('p' => $this->getTable('helpdeskultimate/department')), 'store_id');
        ;

        var_dump($this->getSelect()->assemble());
        return $this;
    }
}









