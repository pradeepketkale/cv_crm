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

class AW_Helpdeskultimate_Block_Customer_Antibot_Revalidate extends Mage_Core_Block_Template
{

    /**
     * Returns refere url
     * @return string
     */
    public function getAction()
    {
        return Mage::getSingleton('core/session')->getPostUrl();

    }

    public function getSeed()
    {
        return Mage::getModel('helpdeskultimate/antibot')->getSeed();
    }

    /**
     * Returns fail key
     * @return string
     */
    public function getFailKey()
    {
        if (!$this->getData('fail_key')) {
            $this->setData('fail_key', rand(10000, 99999));
        }
        return $this->getData('fail_key');
    }

    /**
     * Returns fail key hash for check
     * @return string
     */
    public function getFailKeyHash()
    {
        return md5($this->getFailKey());
    }
}