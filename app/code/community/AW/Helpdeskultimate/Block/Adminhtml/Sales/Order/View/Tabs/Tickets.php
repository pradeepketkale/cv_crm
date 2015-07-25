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


class AW_Helpdeskultimate_Block_Adminhtml_Sales_Order_View_Tabs_Tickets extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('helpdeskultimate')->__('Help Desk');
    }

    public function getTabTitle()
    {
        return Mage::helper('helpdeskultimate')->__('Help Desk');
    }


    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */


    protected function _toHtml()
    {
        $id = $this->getRequest()->getParam('order_id');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setClass('add')
                ->setType('button')
                ->setOnClick('window.location.href=\'' . $this->getUrl('helpdeskultimate_admin/ticket/new', array('order_id' => $id)) . '\'')
                ->setLabel($this->__('Create ticket from this order'));
        if (!Mage::helper('helpdeskultimate')->checkVersion('1.4')) {
            $button->setStyle('margin:10px;float:none;clear:both;');
        } else if (Mage::getVersion() == '1.4.2.0') {
            $button->setStyle('margin:10px;float:none;');
        } else {
            $button->setStyle('margin:10px;float:right;');
        }
        $grid = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_grid');
        $grid->setDefaultFilter(array('order_id' => $id));
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setUserMode();
        $grid->setOrderMode(1);
        $grid->setOnePage();
        //return '<div class="content-buttons-placeholder" style="height:25px;"><p class="content-buttons form-buttons" >'.$button->toHtml().'</p></div>';//$grid->toHtml();
        return $button->toHtml() . $grid->toHtml();
    }
}
