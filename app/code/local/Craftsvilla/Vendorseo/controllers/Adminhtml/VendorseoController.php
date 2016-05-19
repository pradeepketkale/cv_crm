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
				$this->_title($this->__("Manager Vendorseo"));
				$this->_initAction();
				$this->renderLayout();
		}
		
public function gridAction()
{
	 $this->loadLayout();
	 $this->getResponse()->setBody(
	 $this->getLayout()->createBlock('vendorseo/adminhtml_vendorseo_grid')->toHtml()
	 );
}
	 
		public function editAction()
		{			    
				//$this->_title($this->__("Vendorseo"));
				
				$this->loadLayout();
				$this->_setActiveMenu("vendorseo/vendorseo");
				$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Manager"), Mage::helper("adminhtml")->__("Vendorseo Manager"));
				$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Description"), Mage::helper("adminhtml")->__("Vendorseo Description"));
				
						
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("vendorseo/vendorseo")->load($id);
				
				if ($model->getId()) {
					
						$model->setData($model->getData());
						$model->save();
						Mage::register("vendorseo_data", $model);
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("vendorseo")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
				$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
				$this->_addContent($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit"))
				     ->_addLeft($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit_tabs"));
				$this->renderLayout();
		}

		public function newAction()
		{
				
//          $this->_title($this->__("Vendorseo"));
//			$this->_title($this->__("New Item"));
//            $id   = $this->getRequest()->getParam("id");
//			$model  = Mage::getModel("vendorseo/vendorseo")->load($id);
//	
//			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
//			if (!empty($data)) {
//				$model->setData($data);
//			}
//	
//			Mage::register("vendorseo_data", $model);
	
			$this->loadLayout();
			$this->_setActiveMenu("vendorseo/vendorseo");
	
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
	
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Manager"), Mage::helper("adminhtml")->__("Vendorseo Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Description"), Mage::helper("adminhtml")->__("Vendorseo Description"));
	
	
			$this->_addContent($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit"))
			     ->_addLeft($this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit_tabs"));
	
			$this->renderLayout();

		}

		public function getSeoDataAction()
		{
					 if(isset($_GET['v_id'])) {
					  $id = $_GET['v_id'] ;
					  $seoData = Mage::helper('vendorseo')->getVendorSeoData($id);
					  $data = $seoData->getData();
					  if(empty($data[0])) {
								 $data['empty'] = 'Y';
								echo json_encode($data);
					  } else {
					         echo json_encode($data[0]);
					  }
					  exit ;
					 }
		}

		public function saveAction()
		{
			$post_data=$this->getRequest()->getPost();
			if ($post_data) {
				try {
					$vendor_id = (int)$post_data['vendor_name'];
					$vendorCheck = Mage::helper('vendorseo')->getVendorSeoData($vendor_id);
					$vendorData  = $vendorCheck->getData()[0] ;

					//$vendorName = Mage::helper('vendorseo')->getVendorNameById($vendor_id);
					$metaTitle = $post_data['meta_title'];
					$metaDescription = $post_data['meta_description'];
					$metaKeywords = $post_data['meta_keywords'];
					$vendorDescription = $post_data['vendor_description'];

					// check for update seodata from edit section
					if($this->getRequest()->getParam("id")) {
							 
					 $model = Mage::getModel("vendorseo/vendorseo")->load($this->getRequest()->getParam("id"));
					   try {
							  $vendor_id =  $this->getRequest()->getParam("id");
							  
							  Mage::helper('vendorseo')->updateVendorSeoData($post_data,$vendor_id);
						  }
						  catch (Exception $e) {
						  Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						  }

					} else { // new seo data
						  try {
							  Mage::helper('vendorseo')->insertVendorSeoData($post_data,$vendor_id);
						  }
						  catch (Exception $e) {
						  Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						  }
					}

					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Vendor Seo Data was successfully saved"));
					Mage::getSingleton("adminhtml/session")->setVendorseoData(false);

					if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $model->getVendorId()));
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
						//echo '<pre>'; print_r(Mage::registry("vendorseo_data")); exit ;
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__( "Vendor Seo Data was successfully deleted"));
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
