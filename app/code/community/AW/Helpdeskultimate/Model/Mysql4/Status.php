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

/**
 * Resource model of Status
 */
class AW_Helpdeskultimate_Model_Mysql4_Status extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('helpdeskultimate/status', 'status_id');
    }

    public function setOrdering($status_id, $ordering)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('ordering' => $ordering),
            "status_id = '{$status_id}'"
        );
    }

    public function getUsedCount($status_id)
    {
        $tickets = Mage::getModel('helpdeskultimate/ticket')->getCollection();
        $tickets->getSelect()->where('status = ?', $status_id);
        return $tickets->getSize();
    }

    public function statusExist($value)
    {

        $status = Mage::getModel('helpdeskultimate/status')->getCollection();
        $status->getSelect()
                ->where('label = ?', $value)
                ->where('status_type != \'admin\'');
        return $status->getSize();
    }

}
