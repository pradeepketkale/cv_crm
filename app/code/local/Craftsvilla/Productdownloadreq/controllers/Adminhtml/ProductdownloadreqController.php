<?php

class Craftsvilla_Productdownloadreq_Adminhtml_ProductdownloadreqController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('productdownloadreq/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Request'), Mage::helper('adminhtml')->__('Manage Request'));
		
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
         $this->getLayout()->createBlock('productdownloadreq/adminhtml_productdownloadreq_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('productdownloadreq/productdownloadreq')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('productdownloadreq_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('productdownloadreq/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Request'), Mage::helper('adminhtml')->__('Manage Request'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('request '), Mage::helper('adminhtml')->__('Request'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('productdownloadreq/adminhtml_productdownloadreq_edit'))
				->_addLeft($this->getLayout()->createBlock('productdownloadreq/adminhtml_productdownloadreq_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productdownloadreq')->__('item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('productdownloadreq/productdownloadreq');		
			//from here I have to start..
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			try {
				/*if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}*/	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productdownloadreq')->__('Request was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if($this->getRequest()->getParam('back')) {
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productdownloadreq')->__('Unable to find request to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('productdownloadreq/productdownloadreq');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Request was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $productdownloadreqIds = $this->getRequest()->getParam('productdownloadreq');
        if(!is_array($productdownloadreqIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Request(s)'));
        } else {
            try {
                foreach ($productdownloadreqIds as $productdownloadreqId) {
                    $productdownloadreq = Mage::getModel('productdownloadreq/productdownloadreq')->load($productdownloadreqId);
                    $productdownloadreq->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($productdownloadreqIds)
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
	    $productdownloadreqIds = $this->getRequest()->getParam('productdownloadreq');
		$pmstatus = $this->getRequest()->getParam('status');
		$getstatus = Mage::getModel('productdownloadreq/productdownloadreq')->load($productdownloadreqIds)->getStatus();
		if(!is_array($productdownloadreqIds)) 
				{
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Request(s)'));
				} 
			else 
				{
					try {
						foreach ($productdownloadreqIds as $productdownloadreqId) {
						$productdownloadreq = Mage::getSingleton('productdownloadreq/productdownloadreq')
									->load($productdownloadreqId)
									->setStatus($this->getRequest()->getParam('status'))
									->setIsMassupdate(true)
									->save();
						
						
						
						$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully updated', count($productdownloadreqIds)) );
						}
					
					} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
					}
				}
			
        $this->_redirect('*/*/index');
    }
	
	 public function generateAction()
    {
		$bulkdownloadIds = $this->getRequest()->getParam('productdownloadreq');
		$bulkdownload = Mage::getModel('productdownloadreq/productdownloadreq')->load($bulkdownloadIds);
		$vendorid = $bulkdownload['vendorname'];
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		$vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$getactivity = Mage::getModel('productdownloadreq/productdownloadreq')->load($bulkdownloadIds)->getActivity();
		$products = Mage::getModel('catalog/product')->getCollection();
		$products->addAttributeToFilter('udropship_vendor', $vendorid);
		$products->addAttributeToSelect('*');
		$products->load();  
		if($getactivity == 1)
		{	       
			$cvsData ='Craftsvilla SKU Id,Vendor SKU,Product Name,Description,Price,Special Price,Image,Quantity'.PHP_EOL;
			foreach($products as $_prod) 
			{   
			     
			    $sku = $_prod->getSku();
				$vsku = $_prod->getVendorsku();
				$prname = $_prod->getName();
				$prnamerep = str_replace('"','',$prname);
				$price = $_prod->getPrice();
				//$metadesc = $_prod->getMetaDescription();
				$desc = $_prod->getDescription();
				$desc_rep = str_replace('"','',$desc);
				$sprice =  $_prod->getSpecialPrice();
				//$shortdesc = $_prod->getShortDescription(); 
				$image = $_prod->getMediaConfig()->getMediaUrl($_prod->getData('image'));
				$num= Mage::getModel('cataloginventory/stock_item')->loadByProduct($_prod)->getQty();
				$cvsData .= "\"$sku\",\"$vsku\",\"$prnamerep\",\"$desc_rep\",\"$price\",\"$sprice\",\"$image\",\"$num\"".PHP_EOL;
			}
		    $pathreport = Mage::getBaseDir('media'). DS . 'productcsv'. DS;
			$targetcsv = Mage::getBaseUrl('media').'productcsv/';
			$file = $bulkdownloadIds[0].'_report.csv';
			$productcsvpath = $pathreport.$file;
			$productcsvpath1 = $targetcsv.$file;
			$fp = fopen("$productcsvpath", "a");
			if($fp)
				{
					fwrite($fp,$cvsData);
					fclose($fp);
				}	
        
		}
				
     if($getactivity == 2)
		{
			$cvsData ='Craftsvilla SKU Id,Vendor SKU,Quantity'.PHP_EOL;
			foreach($products as $_prod) {   
			     
			    $sku = $_prod->getSku();
				$vsku = $_prod->getVendorsku();
				$num= Mage::getModel('cataloginventory/stock_item')->loadByProduct($_prod)->getQty();
				$cvsData .= "\"$sku\",\"$vsku\",\"$num\"".PHP_EOL;
			}
		    $pathreport = Mage::getBaseDir('media'). DS . 'productcsv'. DS;
			$targetcsv = Mage::getBaseUrl('media').'productcsv/';
			$file = $bulkdownloadIds[0].'_report.csv';
			$productcsvpath = $pathreport.$file;
			$productcsvpath1 = $targetcsv.$file;
			$fp = fopen("$productcsvpath", "a");
			if($fp)
				{
					fwrite($fp,$cvsData);
					fclose($fp);
				}	
        
  
	     }
	  
	       $bulkdownload->setCsvdownload($file)
				              ->setStatus(2)
							  ->save();
		  $this->_redirect('*/*/index');
		  
		  $storeId = Mage::app()->getStore()->getId();	  
						$templateId = 'bulkdownload_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
		                $vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Product Download Request On Craftsvilla.com Is Complete';
						
       
				$downloadReport =  "<a href=".$productcsvpath1.">".$file."</a>";
						
            
					$vars = Array('downloadReport' =>$downloadReport);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->addBcc('monica@craftsvilla.com')
							->sendTransactional($templateId, $sender, $vendoremail, $vendorname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
			
	}
}
