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
            $this->loadLayout()->renderLayout();
            return;
			//$this->_redirect('thoughtyard/products');
        }
        $this->_forward('index', 'index', 'cms');
    }
}
