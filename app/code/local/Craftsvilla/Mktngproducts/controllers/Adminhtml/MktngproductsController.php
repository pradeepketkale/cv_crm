<?php

class Craftsvilla_Mktngproducts_Adminhtml_MktngproductsController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('mktngproducts/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('mktngproducts/adminhtml_mktngproducts_grid')->toHtml()
        );
    }
	
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('mktngproducts/mktngproducts')->load($id);
			
		if ($model->getId() || $id == 0) {
			
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			
			Mage::register('mktngproducts_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('mktngproducts/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('mktngproducts/adminhtml_mktngproducts_edit'))
				->_addLeft($this->getLayout()->createBlock('mktngproducts/adminhtml_mktngproducts_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mktngproducts')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
			if ($data = $this->getRequest()->getPost()) {
			
			$model = Mage::getModel('mktngproducts/mktngproducts');	
			//print_r($model); exit;	
			$connread = Mage::getSingleton('core/resource')->getConnection('core_read');
			$sku = mysql_escape_string($this->getRequest()->getParam('product_sku'));  
						 
			$fbPostId = $this->getRequest()->getParam('fb_post_id'); 
			
			$productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku."'";
			$productQueryRes = $connread->query($productQuery)->fetch();
			$productId = $productQueryRes['entity_id']; 
			$product = Mage::helper('catalog/product')->loadnew($productId);
			//echo '<pre>'; print_r($product); exit;
			
			$productUrl = $product->getProductUrl();
			$productName = $product->getProductName();
			$vendorId = $product->getUdropshipVendor(); 
			$vendorInfo =Mage::getModel('udropship/vendor')->load($vendorId);
			//echo '<pre>';print_r($vendorInfo); exit;
			$vendorName = $vendorInfo->getVendorName(); 
			$vlink = $vendorInfo->getUrlKey();  
				
			$model->setData($data)
			  	  ->setId($this->getRequest()->getParam('id'));
		
			try {
				if ($model->getCreatedAt == NULL || $model->getUpdatedAt() == NULL) {
					$model->setCreatedAt(now())
						->setUpdateAt(now());
				} else {
					$model->setUpdateAt(now());
				}	
				
				$model->setProductSku($sku);
				//$model->setProductUrl($productUrl);
				$model->setVendorName($vendorName);
				$model->setFbPostId($fbPostId);
				
				$model->save();
				//print_r($model); exit;
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mktngproducts')->__('Product was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mktngproducts')->__('Unable to find product to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('mktngproducts/mktngproducts');
				$model->setId($this->getRequest()->getParam('id'))
					  ->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Product was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $mktngproductsIds = $this->getRequest()->getParam('mktngproducts');
        if($mktngproductsIds[0] == '') {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($mktngproductsIds as $mktngproductsId) {
                    $mktngproducts = Mage::getModel('mktngproducts/mktngproducts')->load($mktngproductsId);
                    $mktngproducts->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($mktngproductsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    public function massPromoteAction() {
        
        $mktngproductsIds = $this->getRequest()->getParam('mktngproducts');
        if($mktngproductsIds[0] == '') {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try { 
                foreach ($mktngproductsIds as $mktngproductsId) {
                    $mktngproducts = Mage::getModel('mktngproducts/mktngproducts')->load($mktngproductsId);
                    $sku = $mktngproducts->getProductSku(); 
                    $connread = Mage::getSingleton('core/resource')->getConnection('core_read');
                    $productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku."'";
					$productQueryRes = $connread->query($productQuery)->fetch();
					$productId = $productQueryRes['entity_id'];
                    $product = Mage::helper('catalog/product')->loadnew($productId);
					//echo '<pre>'; print_r($product); exit;
			
					$vendorId = $product->getUdropshipVendor(); 
					$minprice = $product->getPrice(); 
					$vendorInfo =Mage::getModel('udropship/vendor')->load($vendorId);
					//echo '<pre>';print_r($vendorInfo); exit;
					$vendorName = $vendorInfo->getVendorName();  
                    
                    
                    $hlp = Mage::helper('generalcheck');
					$statsconn = $hlp->getStatsdbconnection();
                    
                    $trendingQuery ="SELECT * FROM `craftsvilla_trending` WHERE `product_id` = '".$productId."'";
                    $trendingQueryRes = mysql_query($trendingQuery,$statsconn);
                    $trendingQueryRes1 = mysql_fetch_array($trendingQueryRes);
                    if($trendingQueryRes1) 
                    {

						$trendingUpdateQuery = "UPDATE `craftsvilla_trending` SET `promo`= 1 , `udropship_vendor`='".$vendorId."' , `min_price`='".$minprice."' WHERE `product_id` = '".$productId."'";  
						$trendingUpdateQueryRes = mysql_query($trendingUpdateQuery, $statsconn);
					
                    } 
                    else 
                    {
                    
		                $trendingInsertQuery = "INSERT INTO `craftsvilla_trending`(`product_id`, `promo`, `udropship_vendor`, `min_price`) VALUES ('$productId',1,'$vendorId','$minprice')";  
		                $trendingInsertQueryRes = mysql_query($trendingInsertQuery, $statsconn);
		                
		            }
                    
                    
                }
                mysql_close($statsconn);
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully promoted', count($mktngproductsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massDepromoteAction() {
        
        $mktngproductsIds = $this->getRequest()->getParam('mktngproducts');
        if($mktngproductsIds[0] == '') {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try { 
                foreach ($mktngproductsIds as $mktngproductsId) {
                    $mktngproducts = Mage::getModel('mktngproducts/mktngproducts')->load($mktngproductsId);
                    $sku = $mktngproducts->getProductSku(); 
                    $connread = Mage::getSingleton('core/resource')->getConnection('core_read');
                    $productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku."'";
					$productQueryRes = $connread->query($productQuery)->fetch();
					$productId = $productQueryRes['entity_id'];
					$product = Mage::helper('catalog/product')->loadnew($productId);
					//echo '<pre>'; print_r($product); exit;
			
					$vendorId = $product->getUdropshipVendor(); 
					$minprice = $product->getPrice(); 
					$vendorInfo =Mage::getModel('udropship/vendor')->load($vendorId);
					//echo '<pre>';print_r($vendorInfo); exit;
					$vendorName = $vendorInfo->getVendorName(); 
                    
                    $hlp = Mage::helper('generalcheck');
					$statsconn = $hlp->getStatsdbconnection();
                    
                    $trendingQuery ="SELECT * FROM `craftsvilla_trending` WHERE `product_id` = '".$productId."'";
                    $trendingQueryRes = mysql_query($trendingQuery,$statsconn);
                    $trendingQueryRes1 = mysql_fetch_array($trendingQueryRes);
                    if($trendingQueryRes1) 
                    {
						$trendingUpdateQuery = "UPDATE `craftsvilla_trending` SET `promo`= 0,`udropship_vendor`='".$vendorId."', `min_price`='".$minprice."' WHERE `product_id` = '".$productId."'"; 
						$trendingUpdateQueryRes = mysql_query($trendingUpdateQuery, $statsconn);
					
                    } 
                    else 
                    {
                        $trendingInsertQuery = "INSERT INTO `craftsvilla_trending`(`product_id`, `promo`,`udropship_vendor`,`min_price`) VALUES ('$productId',0,'$vendorId','$minprice')";  
		                $trendingInsertQueryRes = mysql_query($trendingInsertQuery, $statsconn);
		            }
                    
                    
                }
                mysql_close($statsconn);
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully DePromoted', count($mktngproductsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
	
    public function massStatusAction()
    {
        $mktngproductsIds = $this->getRequest()->getParam('mktngproducts');
        if($mktngproductsIds[0] == '') { 
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else { 
            try {
                foreach ($mktngproductsIds as $mktngproductsId) {
                    $mktngproducts = Mage::getSingleton('mktngproducts/mktngproducts')
                        ->load($mktngproductsId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($mktngproductsIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
