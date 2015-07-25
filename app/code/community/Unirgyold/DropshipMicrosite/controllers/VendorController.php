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

class Unirgy_DropshipMicrosite_VendorController extends Mage_Core_Controller_Front_Action
{
    protected $_loginFormChecked = false;

    protected function _setTheme()
    {
        $theme = explode('/', Mage::getStoreConfig('udropship/vendor/interface_theme'));
        if (empty($theme[0]) || empty($theme[1])) {
            $theme = 'default/default';
        }
        Mage::getDesign()->setPackageName($theme[0])->setTheme($theme[1]);
    }

    protected function _renderPage($handles=null, $active=null)
    {
        $this->_setTheme();
        $this->loadLayout($handles);
        if (($root = $this->getLayout()->getBlock('root'))) {
            $root->addBodyClass('udropship-vendor');
        }
        if ($active && ($head = $this->getLayout()->getBlock('header'))) {
            $head->setActivePage($active);
        }
        $this->_initLayoutMessages('udropship/session');
        $this->renderLayout();
    }

    public function registerAction()
    {
        $this->_renderPage(null, 'register');
    }

    public function registerPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('umicrosite');
        try {
            $data = $this->getRequest()->getParams();
            $session->setRegistrationFormData($data);
            $reg = Mage::getModel('umicrosite/registration')
                ->setData($data)
                ->validate()
                ->save();
            $hlp->sendVendorSignupEmail($reg);
            $hlp->sendVendorRegistration($reg);
            $session->unsRegistrationFormData();
            $session->addSuccess($hlp->__('Thank you for application. As soon as your registration has been verified, you will receive an email confirmation'));
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('*/*/register');
            return;
        }
        $this->_redirect('udropship/vendor');
    }

    public function registerSuccessAction()
    {
        $this->_renderPage(null, 'register');
    }
}