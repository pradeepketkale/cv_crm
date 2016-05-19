<?php
class Craftsvilla_Seovendor_Adminhtml_SeovendorbackendController extends Mage_Adminhtml_Controller_Action
{
	  protected function _initAction()
		{
		$this->loadLayout()
				 ->_setActiveMenu("seovendor/seovendor")
				 ->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo  Manager"),Mage::helper("adminhtml")->__("Vendorseo Manager"));
				return $this;
		}
	
	 	public function indexAction() 
		{
				$this->_title($this->__("SeoVendor Manager"));
				$this->_initAction();
				$this->renderLayout();
		}
		
		public function gridAction()
		{
			 $this->loadLayout();
			 $this->getResponse()->setBody(
			 $this->getLayout()->createBlock('seovendor/adminhtml_seovendor_grid')->toHtml()
			 );
		}
		
		public function editAction()
		{			    
				//$this->_title($this->__("Vendorseo"));
				
				$this->loadLayout();
				$this->_setActiveMenu("seovendor/seovendor");
				$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Manager"), Mage::helper("adminhtml")->__("Vendorseo Manager"));
				$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Description"), Mage::helper("adminhtml")->__("Vendorseo Description"));
				
						
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("seovendor/seovendor")->load($id);
				
				if ($model->getId()) {
					
						$model->setData($model->getData());
						$model->save();
						Mage::register("seovendor_data", $model);
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("seovendor")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
				$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
				$this->_addContent($this->getLayout()->createBlock("seovendor/adminhtml_seovendor_edit"))
				     ->_addLeft($this->getLayout()->createBlock("seovendor/adminhtml_seovendor_edit_tabs"));
				$this->renderLayout();
		}

		public function newAction()
		{

			$this->loadLayout();
			$this->_setActiveMenu("seovendor/seovendor");
	
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
	
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Manager"), Mage::helper("adminhtml")->__("Vendorseo Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vendorseo Description"), Mage::helper("adminhtml")->__("Vendorseo Description"));
	
	
			$this->_addContent($this->getLayout()->createBlock("seovendor/adminhtml_seovendor_edit"))
			     ->_addLeft($this->getLayout()->createBlock("seovendor/adminhtml_seovendor_edit_tabs"));
	
			$this->renderLayout();

		}

		public function getSeoDataAction()
		{
			if(isset($_GET['v_id'])) {
			 $id = $_GET['v_id'] ;
			 $seoData = Mage::helper('seovendor')->getVendorSeoData($id);
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
					$vendorCheck = Mage::helper('seovendor')->getVendorSeoData($vendor_id);
					$vendorData  = $vendorCheck->getData()[0] ;

					//$vendorName = Mage::helper('seovendor')->getVendorNameById($vendor_id);
					$metaTitle = $post_data['meta_title'];
					$metaDescription = $post_data['meta_description'];
					$metaKeywords = $post_data['meta_keywords'];
					$vendorDescription = $post_data['vendor_description'];

					// check for update seodata from edit section
					if($this->getRequest()->getParam("id")) {
							 
					 $model = Mage::getModel("seovendor/seovendor")->load($this->getRequest()->getParam("id"));
					   try {
							  $vendor_id =  $this->getRequest()->getParam("id");
							  
							  Mage::helper('seovendor')->updateVendorSeoData($post_data,$vendor_id);
						  }
						  catch (Exception $e) {
						  Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						  }

					} else { // new seo data
						  try {
							  Mage::helper('seovendor')->insertVendorSeoData($post_data,$vendor_id);
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
						$model = Mage::getModel("seovendor/seovendor");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						//echo '<pre>'; print_r(Mage::registry("seovendor_data")); exit ;
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
                      $model = Mage::getModel("seovendor/seovendor");
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
			$fileName   = 'seovendor.csv';
			$grid       = $this->getLayout()->createBlock('seovendor/adminhtml_seovendor_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'seovendor.xml';
			$grid       = $this->getLayout()->createBlock('seovendor/adminhtml_seovendor_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}