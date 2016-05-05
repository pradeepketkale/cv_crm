<?php

class Craftsvilla_Vendorseo_Adminhtml_VendorseoController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("vendorseo/vendorseo")->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo  Manager"),Mage::helper("adminhtml")->__("Vendorseo Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Vendorseo"));
			    $this->_title($this->__("Manager Vendorseo"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
				$this->_title($this->__("Vendorseo"));
				//$this->_title($this->__("Vendorseo"));
				//$this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("vendorseo/vendorseo")->load($id);
				
				if ($model->getId()) {
					
						$model->setData($model->getData());
						$model->save();
						Mage::register("vendorseo_data", $model);
								
						$this->loadLayout();
						$this->_setActiveMenu("vendorseo/vendorseo");
						$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Manager"), Mage::helper("adminhtml")->__("Vendorseo Manager"));
						$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Description"), Mage::helper("adminhtml")->__("Vendorseo Description"));
						$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
						$this->_addContent($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit"))->_addLeft($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit_tabs"));
						$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("vendorseo")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{
                        $this->_title($this->__("Vendorseo"));
			//$this->_title($this->__("Vendorseo"));
			$this->_title($this->__("New Item"));
	
	                $id   = $this->getRequest()->getParam("id");
			$model  = Mage::getModel("vendorseo/vendorseo")->load($id);
	
			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
	
			Mage::register("vendorseo_data", $model);
	
			$this->loadLayout();
			$this->_setActiveMenu("vendorseo/vendorseo");
	
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
	
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Manager"), Mage::helper("adminhtml")->__("Vendorseo Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Description"), Mage::helper("adminhtml")->__("Vendorseo Description"));
	
	
			$this->_addContent($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit"))->_addLeft($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit_tabs"));
	
			$this->renderLayout();

		}

		public function getdataAction()
		{
			$vendor = $this->getRequest()->getParams();
			exit();
			if(isset($vendor['vendor_id']) && $vendor['vendor_id'] > 0){
				$read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$query = 'select meta_title, meta_description, meta_keywords from vendor_info_craftsvilla where vendor_id = '.$vendor['vendor_id'];	
				$result = $read->query($query)->fetch();
				echo "<pre>";
				print_r($result);
			}			
		}

		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();
		       
			        if ($post_data) {
				       
				    
					try {
				                $vendor_id = (int)$post_data['vendor_name'];
						$vendorName = Mage::helper('vendorseo')->getVendorNameById($vendor_id);
						$metaTitle = $post_data['meta_title'];
						$metaDescription = $post_data['meta_description'];
						$metaKeywords = $post_data['meta_keywords'];
						
					        if($this->getRequest()->getParam("id")) {
				                $model = Mage::getModel("vendorseo/vendorseo")->load($this->getRequest()->getParam("id"));
						$model->setData('vendor_id', $vendor_id);
						$model->setData('vendor_name', $vendorName);
						$model->setData('meta_title', $metaTitle);
						$model->setData('meta_description', $metaDescription);
						$model->setData('meta_keywords', $metaKeywords);
				                } else {
						
						//echo $vendorName.'<-->'.gettype($vendor_id); exit ;
						$model = Mage::getModel("vendorseo/vendorseo");
						$model->setData('vendor_id', $vendor_id);
						$model->setData('vendor_name', $vendorName);
						$model->setData('meta_title', $metaTitle);
						$model->setData('meta_description', $metaDescription);
						$model->setData('meta_keywords', $metaKeywords);
						}
						$model->save();
						//$model->setData($post_data);
						//->setId($this->getRequest()->getParam("id"))
						

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Vendorseo was successfully saved"));
						//Mage::getSingleton("adminhtml/session")->setVendorseoData(false);
						
						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setVendorseoData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}
				     
				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("vendorseo/vendorseo");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('vendor_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("vendorseo/vendorseo");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'vendorseo.csv';
			$grid       = $this->getLayout()->createBlock('vendorseo/adminhtml_vendorseo_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'vendorseo.xml';
			$grid       = $this->getLayout()->createBlock('vendorseo/adminhtml_vendorseo_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
