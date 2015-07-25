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

class Unirgy_DropshipMicrosite_Model_Observer
{
    protected $_vendorPassword;

    /**
    * Invoke as soon as possible to get correct base_url in frontend
    *
    * @param mixed $observer
    */
    public function controller_front_init_before($observer)
    {
        $this->_getVendor();
    }

    /**
    * Make frontend layout/block changes vendor specific
    *
    * @param mixed $observer
    */
    public function controller_action_layout_render_before($observer)
    {
        if (!($vendor = $this->_getVendor())) {
            return;
        }
        $root = Mage::app()->getLayout()->getBlock('root');
        if ($root && $root instanceof Mage_Page_Block_Html) {
            $root->addBodyClass('vendor-'.$vendor->getUrlKey());
        }
    }

    /**
    * Filter products collections on frontend by current vendor
    *
    * @param mixed $observer
    */
    public function catalog_block_product_list_collection($observer)
    {
        Mage::helper('umicrosite')->addVendorFilterToProductCollection($observer->getEvent()->getCollection());
    }
/*
    public function catalog_product_collection_apply_limitations_after($observer)
    {
        $vendor = $this->_getVendor();
        $collection = $observer->getEvent()->getCollection();
        try {
            if ($vendor) {
                $collection->addAttributeToFilter('udropship_vendor', $vendor->getId());
echo 1;
            } elseif (Mage::getDesign()->getArea()=='frontend') {
echo 2;
                $res = Mage::getSingleton('core/resource');
                $sql = "select vendor_id from {$res->getTableName('udropship/vendor')} where status='A'";
                $session = Mage::getSingleton('udropship/session');
                if ($session->isLoggedIn() && $session->getVendor()->getStatus()=='I') {
                    $sql .= " OR vendor_id=".$session->getVendor()->getId();
                }
                $collection->addAttributeToFilter('udropship_vendor', array('in'=>new Zend_Db_Expr($sql)));
                //$collection->joinAttribute('udropship_vendor', 'catalog_product/udropship_vendor', 'entity_id');
                //$collection->joinField('udropship_status', 'udropship/vendor', 'status', 'vendor_id=udropship_vendor', $cond);
            }
        } catch (Exception $e) {
            $skip = array(
                'Joined field with this alias is already declared',
                'Invalid alias, already exists in joined attributes',
            );
            if (!in_array($e->getMessage(), $skip)) {
                throw $e;
            }
        }
    }
*/
    /**
    * Deny access to product if not from current vendor and accessed directly by URL
    *
    * @param mixed $observer
    */
    public function catalog_controller_product_init($observer)
    {
        if (!($vendor = $this->_getVendor())) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        if ($product->getUdropshipVendor()!=$vendor->getId()) {
            Mage::throwException('Product is filtered out by vendor');
        }
    }

    /**
    * Filter products collections in adminhtml by current vendor
    *
    * @param mixed $observer
    */
    public function catalog_product_collection_load_before($observer)
    {
        if (!($vendor = $this->_getVendor())) {
            return;
        }
        $collection = $observer->getEvent()->getCollection();
        $collection->addAttributeToFilter('udropship_vendor', $vendor->getId());
    }

    /**
    * Remember vendor password from submitted form
    *
    * @param mixed $observer
    */
    public function udropship_vendor_save_before($observer)
    {
        $vendor = $observer->getEvent()->getVendor();
        if ($vendor->getPassword()) {
            $this->_vendorPassword = $vendor->getPassword();
        } elseif ($vendor->getRegId()) {
            $reg = Mage::getModel('umicrosite/registration')->load($vendor->getRegId());
            $this->_vendorPassword = Mage::helper('core')->decrypt($reg->getPasswordEnc());
        }
    }

    /**
    * Synchronize admin user from vendor object
    *
    * @param mixed $observer
    */
    public function udropship_vendor_save_after($observer)
    {
        $vendor = $observer->getEvent()->getVendor();

        $user = Mage::getModel('admin/user')->load($vendor->getId(), 'udropship_vendor');
        $changed = false;
        $nameChanged = false;
        $new = false;
        if (!$user->getId()) {
            $new = true;
            $user->setData(array(
                'udropship_vendor' => $vendor->getId(),
                'is_active' => 1,
            ));
        }
        if (!$new && $vendor->getVendorName()!=$user->getFirstname()) {
            $nameChanged = true;
        }
//        $isActive = $vendor->getStatus()=='A' ? 1 : 0;
        if ($new
            || $vendor->getVendorName()!=$user->getFirstname()
            || $vendor->getVendorAttn()!=$user->getLastname()
            || $vendor->getEmail()!=$user->getEmail()
//            || $isActive!=$user->getIsActive()
            ) {
            $user->addData(array(
                'firstname' => $vendor->getVendorName(),
                'lastname'  => $vendor->getVendorAttn(),
                'email'     => $vendor->getEmail(),
                'username'  => $vendor->getEmail(),
//                'is_active' => $isActive,
            ));
            $changed = true;
        }
        if (!Mage::helper('core')->validateHash($this->_vendorPassword, $user->getPassword())) {
            $user->setNewPassword($this->_vendorPassword);
            $changed = true;
        } else {
            $user->unsPassword();
        }
        if ($changed) {
            $user->save();
        }

        if ($new) {
            $roles = Mage::getModel('admin/role')->getCollection()
                ->addFieldToFilter('role_name', 'Dropship Vendor')
                ->addFieldToFilter('parent_id', 0);
            foreach ($roles as $role) {
                $user->setRoleId($role->getRoleId())->add();
                break;
            }
        } elseif ($nameChanged) {
            $roles = Mage::getModel('admin/role')->getCollection()
                ->addFieldToFilter('user_id', $user->getId());
            foreach ($roles as $role) {
                $role->setRoleName($vendor->getVendorName())->save();
            }
        }

        if ($vendor->getRegId()) {
            Mage::helper('umicrosite')->sendVendorWelcomeEmail($vendor);
            Mage::getModel('umicrosite/registration')->load($vendor->getRegId())->delete();
        }
    }

    protected function _switchSession($area, $id=null)
    {
        session_write_close();
        $GLOBALS['_SESSION'] = null;
        $session = Mage::getSingleton('core/session');
        if ($id) {
            $session->setSessionId($id);
        }
        $session->start($area);
    }

    public function udropship_vendor_login($observer)
    {
        $vendor = $observer->getEvent()->getVendor();
        $user = Mage::getModel('admin/user')->load($vendor->getId(), 'udropship_vendor');
        if ($user->getId()) {
            $coreSession = Mage::getSingleton('core/session');
            $oId = $coreSession->getSessionId();
            $sId = !empty($_COOKIE['adminhtml']) ? $_COOKIE['adminhtml'] : $oId;
            $this->_switchSession('adminhtml', $sId);
            $session = Mage::getSingleton('admin/session');
            if (!$session->isLoggedIn()) {
                $user->getResource()->recordLogin($user);
                $session->setIsFirstVisit(true);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }
            }
            $this->_switchSession('frontend', $oId);
        }
    }

    public function udropship_vendor_logout($observer)
    {
        $vendor = $observer->getEvent()->getVendor();
        $user = Mage::getModel('admin/user')->load($vendor->getId(), 'udropship_vendor');

        if ($user->getId() && !empty($_COOKIE['adminhtml'])) {
            $coreSession = Mage::getSingleton('core/session');
            $oId = $coreSession->getSessionId();
            $sId = $_COOKIE['adminhtml'];
            $this->_switchSession('adminhtml', $sId);
            $session = Mage::getSingleton('admin/session');
            if ($session->isLoggedIn() && $session->getUser()->getId()==$user->getId()) {
                Mage::getSingleton('admin/session')->unsetAll();
                Mage::getSingleton('adminhtml/session')->unsetAll();
            }
            $this->_switchSession('frontend', $oId);
        }
    }

    public function admin_session_user_login_success($observer)
    {
        $coreSession = Mage::getSingleton('core/session');
        $oId = $coreSession->getSessionId();
        $sId = !empty($_COOKIE['frontend']) ? $_COOKIE['frontend'] : null;

        $user = $observer->getEvent()->getUser();
        $vendorId = $user->getUdropshipVendor();

        if ($user->getUdropshipVendor()) {
            $this->_switchSession('frontend', $sId);
            $session = Mage::getSingleton('udropship/session');
            if (!$session->isLoggedIn()) {
                $session->loginById($vendorId);
            }
            $this->_switchSession('adminhtml', $oId);
        }
    }

    protected $_vendorId;

    public function controller_action_predispatch_adminhtml_index_logout($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($user) {
            $this->_vendorId = $user->getUdropshipVendor();
        }
    }

    public function controller_action_postdispatch_adminhtml_index_logout($observer)
    {
        if (!$this->_vendorId) {
            return;
        }
        $coreSession = Mage::getSingleton('core/session');
        $oId = $coreSession->getSessionId();
        $sId = !empty($_COOKIE['frontend']) ? $_COOKIE['frontend'] : null;

        $this->_switchSession('frontend', $sId);
        $session = Mage::getSingleton('udropship/session');
        if ($session->isLoggedIn() && $session->getId()==$this->_vendorId) {
            $session->setId(null);
        }

        if (!empty($_SESSION['core']['last_url'])) {
            $url = $_SESSION['core']['last_url'];
        } elseif (!empty($_SESSION['core']['visitor_data']['http_referer'])) {
            $url = $_SESSION['core']['visitor_data']['http_referer'];
        } else {
            $url = Mage::getUrl('udropship', array('_store'=>'default'));
        }
        if (false !== strpos($url, 'ajax')) {
            $url = Mage::getUrl('udropship', array('_store'=>'default'));
        } elseif (false !== strpos($url, 'cms/index/noRoute')) {
            $url = Mage::getUrl('udropship', array('_store'=>'default'));
        }
        $this->_switchSession('adminhtml', $oId);

        header("Location: ".$url);
        exit;
    }

    /**
    * Set current vendor ID for saved product when logged in as a vendor
    *
    * @param mixed $observer
    */
    public function catalog_product_prepare_save($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $vendor = $this->_getVendor();
        if ($product && $vendor) {
            $product->setUdropshipVendor($vendor->getId());

            $staging = Mage::getStoreConfig('udropship/microsite/staging_website');
            if ($staging) {
                $newWebsiteIds = $product->getWebsiteIds();
                $product->unsWebsiteIds();
                $websiteIds = $product->getWebsiteIds();
                $product->setWebsiteIds(array_merge($websiteIds, $newWebsiteIds));
            }
        }
    }

    /**
    * Deny access to product editing in admin if does not belong to logged in vendor
    *
    * @param mixed $observer
    */
    public function catalog_product_edit_action($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $vendor = $this->_getVendor();
        if ($product && $vendor && $product->getUdropshipVendor()!=$vendor->getId()) {
            Mage::throwException('Access denied');
        }
    }

    /**
    * Remove Dropship Vendor dropdown when editing products if logged in as a vendor
    *
    * @param mixed $observer
    */
    public function adminhtml_catalog_product_edit_prepare_form($observer)
    {
        if ($this->_getVendor()) {
            $form = $observer->getEvent()->getForm();
            $hideFields = explode(',', Mage::getStoreConfig('udropship/microsite/hide_product_attributes'));
            $hideFields[] = 'udropship_vendor';
            foreach ($form->getElements() as $fieldset) {
                foreach ($fieldset->getElements() as $field) {
                    if (in_array($field->getName(), $hideFields)) {
                        $fieldset->removeField($field->getName());
                    }
                }
            }
        }
    }

    /**
    * Deny access to unauthorized cms page edits and set vendor id before save
    *
    * @param mixed $observer
    */
    public function cms_page_prepare_save($observer)
    {
        $page = $observer->getEvent()->getPage();
        $vendor = $this->_getVendor();
        if ($page && $vendor) {
            if ($page->getId()) {
                $origPage = Mage::getModel('cms/page')->load($page->getId());
                if ($origPage->getUdropshipVendor()!=$vendor->getId()) {
                    Mage::throwException('Access denied');
                }
            }
            $page->setUdropshipVendor($vendor->getId());
        }
    }

    public function adminhtml_version($observer)
    {
        Mage::helper('udropship')->addAdminhtmlVersion('Unirgy_DropshipMicrosite');
    }

    protected function _getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }
}
