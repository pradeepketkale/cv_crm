<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipMicrosite
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
 
class Unirgy_DropshipMicrosite_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            //$vendorSeoData = Mage::helper('vendorseo')->getVendorSeoData($vendor->getId());
           // $seoData = $vendorSeoData->getData();
            $this->getLayout()->helper('page/layout')
                ->applyHandle('two_columns_left');
            $this->loadLayout();
            //if(!empty($seoData)) {
            //$this->getLayout()->getBlock('head')->setTitle($seoData[0]['meta_title']);
            //$this->getLayout()->getBlock('head')->setDescription($seoData[0]['meta_description']);
            //$this->getLayout()->getBlock('head')->setKeywords($seoData[0]['meta_keywords']);
            //}
            $this->renderLayout();
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
}