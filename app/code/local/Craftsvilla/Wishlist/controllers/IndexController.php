<?php
require_once("Mage/Wishlist/controllers/IndexController.php");
class Craftsvilla_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
   
    public function addAction()
    {
        $session = Mage::getSingleton('customer/session');
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $this->_redirect('*/');
            return;
        }

        $productId = (int) $this->getRequest()->getParam('product');
        
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $buyRequest = new Varien_Object($this->getRequest()->getParams());

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist'  => $wishlist,
                    'product'   => $product,
                    'item'      => $result
                )
            );
// Commented the email code of wishlist added By Dileswar on dated 22-11-2012
          
		  /*  $storeId = Mage::app()->getStore()->getId();
            $translate  = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            $subject='add new product to customer wishlist';
            $vars = array();
            $image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
            $sender = Array('name'  => 'Craftsvilla',
     		'email' => 'messages@craftsvilla.com');
            
            $templateId='add_wishlist_email_template_vendor';
            $_customer=$session->getCustomer();
            $recepientEmail=$_customer->getEmail();
            $recepientName=$_customer->getFirstname();
            
            $_helpv = Mage::helper('udropship');
            $_vendor = $_helpv->getVendor($product);
           
            $recepientEmailVendor=$_vendor->getEmail();
            $recepientNameVendor=$_vendor->getVendorName();
            
            $vars['image']=$image;
            $vars['prod_name']=$product->getName();
            $vars['cust_name']=$recepientName;
            $vars['vendor_name']=$recepientNameVendor;
			$vars['prod_url']=$product->getProductUrl();
			
            
            $mailTemplate=Mage::getModel('core/email_template');
          
		    // Send mail To Vendor
            if($_vendor->getUnsubscribeWishlist()==0){
                $mailTemplate->sendTransactional($templateId,$sender,$recepientEmailVendor,$recepientNameVendor,$vars,$storeId);
            }
			$recepientEmailCc='messages@craftsvilla.com';
			$recepientNameCc='Sannvi Home Shoppee';
            $mailTemplate->sendTransactional($templateId,$sender,$recepientEmailCc,$recepientNameCc,$vars,$storeId);
            
            $templateId='add_wishlist_email_template';
            $wishlistUrl=Mage::getBaseUrl().'wishlist';
            
            $vars['replyurl']='<p style="float:left; font-size:12px; width:59%;">
                 <a href="'.$wishlistUrl.'" style="color:#0192B5;"><strong>See your wishlist</strong></a>
              </p> ';
            
            // Send mail To Customer
            $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,$recepientEmail,$recepientName,$vars,$storeId);
            
            
            $translate->setTranslateInline(true);
            */
            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping', $product->getName(), $referer);
            $session->addSuccess($message);
        }
        catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            mage::log($e->getMessage());
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }

        echo "wished";
        //$this->_redirectUrl($_SERVER['HTTP_REFERER']);
    }
  
}
