<?php

class Craftsvilla_Homepage_Adminhtml_HomepageController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('homepage/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Wishlist Products'), Mage::helper('adminhtml')->__('Wishlist Products'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	
	}
/**

     * Product grid for AJAX request

     */

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('homepage/adminhtml_homepage_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('homepage/homepage')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('homepage_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('homepage/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Wishlist Products'), Mage::helper('adminhtml')->__('Wishlist Products'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('homepage/adminhtml_homepage_edit'))
				->_addLeft($this->getLayout()->createBlock('homepage/adminhtml_homepage_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('homepage')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
		//	print_r($data);exit;
			if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
				try {	
				     /* $sku = $data['sku'];
					  $read = Mage::getSingleton('core/resource')->getConnection('core_read');
						$prod_query = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku ."'";
						$result = $read->query($prod_query)->fetchAll();	
						foreach($result as $_result)
						{
							$prdid = $_result['entity_id'];
						}
				     $_target = Mage::getBaseDir('media') . DS . 'noticeboardimages' . DS;
					$targetimg = Mage::getBaseUrl('media').'noticeboardimages/';
					$name = $_FILES['image']['name'];
					$source_file_path = basename($name);
					$newfilename = mt_rand(10000000, 99999999).'_image.jpg';
					$path = $_target.$newfilename;
					$path1 = $targetimg.$newfilename;
					$pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="90px" height="80px"/></a>';
					file_put_contents($path, file_get_contents($_FILES['image']['tmp_name']));
			        $notice = Mage::getModel('homepage/homepage');
					$notice->setSku($sku)
						   ->setStatus(1)
						   ->setProductId($prdid)
						   ->setImage($pathhtml)
						   ->save();*/
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			}
	  			
	  			
			$model = Mage::getModel('homepage/homepage');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('homepage')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('homepage')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('homepage/homepage');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $homepageIds = $this->getRequest()->getParam('homepage');
        if(!is_array($homepageIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($homepageIds as $homepageId) {
                    $homepage = Mage::getModel('homepage/homepage')->load($homepageId);
                    $homepage->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($homepageIds)
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
        $homepageIds = $this->getRequest()->getParam('homepage');
        if(!is_array($homepageIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($homepageIds as $homepageId) {
                    $homepage = Mage::getSingleton('homepage/homepage')
                        ->load($homepageId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($homepageIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function addtohomepageAction() {
	 $homepageIds = $this->getRequest()->getParam('homepage');
	 foreach($homepageIds as $_homepageIds)
		{
	 $homepage = Mage::getModel('homepage/homepage')->load($_homepageIds);
	 $prid = $homepage['product_id'];
	 $newCategory = array(991);
	 $product = Mage::getModel('catalog/product')->load($prid);
	 $sku =  $product->getSku();
	 $entityid = $product->getId();
	 $product->setCategoryIds(
        array_merge($product->getCategoryIds(), $newCategory)
    );
    $product->save();
	/*$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$up = "update catalog_product_craftsvilla1 set category_id4='991' where `entity_id` = '".$entityid."'";
	$upres = $write->query($up);*/
	
	  $home = Mage::getModel('homepage/homepage')->getCollection()
									->addFieldToFilter('sku', $sku);
									
		
	 foreach($home as $_home){
				 $id = $_home->getHomepageId();
				}
		if($home->count() == 0)
				{
					
					$homeload = Mage::getModel('homepage/homepage')->setHomepageId($id)
											     									->setSku($sku)
												     							    ->setStatus(1)
																				    ->save();
				}
			else
				{
					
				$homeload = Mage::getModel('homepage/homepage')->load($id)
				                           
				                            ->setStatus(1)
											->save();
				}
									  
		}
	  
	  try
	  {
		$this->_getSession()->addSuccess($this->__('Total of %d record(s) added to home page successfully',count($homepageIds)));  
	    $this->_redirect('*/*/index');
	  }
	  catch(Exception $e){
				echo $e->getMessage();
				
			}
	
	}
	
	public function removefromhomepageAction() 
	{
		$homepageIds = $this->getRequest()->getParam('homepage');
		$category = 991;
		foreach($homepageIds as $_homepageIds)
		{
		$homepage = Mage::getModel('homepage/homepage')->load($_homepageIds);
		$prid = $homepage['product_id'];
		
		$product = Mage::getModel('catalog/product')->load($prid);
		$sku =  $product->getSku();
		$entityid = $product->getId();
    	$categoryIDs = $product->getCategoryIds();
		 if (($key = array_search(991, $categoryIDs)) !== false) 
		 {
			unset($categoryIDs[$key]);
			$product->setCategoryIds($categoryIDs);
			$product->save();
   		}
	    $home = Mage::getModel('homepage/homepage')->getCollection()
									->addFieldToFilter('sku', $sku);
			
	 foreach($home as $_home){
				$id = $_home->getHomepageId();
				}		
		if($home->count() == 0)
				{
					
					$homeload = Mage::getModel('homepage/homepage') ->setHomepageId($id)
					                                                                ->setSku($sku)
												     							    ->setStatus(2)
																					->setProductId($entityid)
																				    ->save();
				}
			else
				{
					
				$homeload = Mage::getModel('homepage/homepage')->load($id)
				                            ->setStatus(2)
											->setProductId($entityid)
											->save();
				}
				
			
		}
			
		try
			{
			$this->_getSession()->addSuccess($this->__('Total of %d record(s) removed from home page successfully',count($homepageIds)));  
			$this->_redirect('*/*/index');
			}
			catch(Exception $e){
				echo $e->getMessage();
				
			}

		
		
	}
	
	public function showitemAction() {
		$emailid = $this->getRequest()->getParam('emailid');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$customer = "select * from `customer_entity` WHERE `email`= '".$emailid."'";
					$customerres = $read->query($customer)->fetch();
		$custid = $customerres['entity_id'];
		$wishid = "select * from `wishlist` WHERE `customer_id`= '".$custid."'";
					$wishidres = $read->query($wishid)->fetch();
		$wishlistid = $wishidres['wishlist_id'];
		$wishlistitemid = "select * from `wishlist_item` WHERE `wishlist_id`= '".$wishlistid."'";
	    $wishlistitemidres = $read->query($wishlistitemid)->fetchAll();
		
		foreach($wishlistitemidres as $wish)
		{
		$productid = $wish['product_id'];
		$product = Mage::getModel('catalog/product')->load($productid);
        $sku = $product['sku'];
		
		$home = Mage::getModel('homepage/homepage')->getCollection()
									->addFieldToFilter('sku', $sku);
			
	 foreach($home as $_home){
				$id = $_home->getHomepageId();
				}		
		if($home->count() == 0)
				{
					
					$homeload = Mage::getModel('homepage/homepage')                ->setHomepageId($id)
					                                                                ->setSku($sku)
												     							    ->setStatus(2)
																					->setProductId($productid)
																				    ->save();
				}
			else
				{
					
				$homeload = Mage::getModel('homepage/homepage')->load($id)
				                            ->setStatus(2)
											->setProductId($productid)
											->save();
				}
		
		}
	    $this->_redirect('*/*/index');
	 
	}
	
	
}
