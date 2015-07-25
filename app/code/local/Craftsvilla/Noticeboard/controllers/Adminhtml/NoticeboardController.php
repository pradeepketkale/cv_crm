<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();
class Craftsvilla_Noticeboard_Adminhtml_NoticeboardController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('noticeboard/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->loadLayout();  
		 $this->getLayout()->createBlock('noticeboard/adminhtml_noticeboard');
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
        $this->getLayout()->createBlock('noticeboard/adminhtml_noticeboard_grid')->toHtml()
        );
    }
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('noticeboard/noticeboard')->load($id);
         
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('noticeboard_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('noticeboard/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('noticeboard/adminhtml_noticeboard_edit'))
				->_addLeft($this->getLayout()->createBlock('noticeboard/adminhtml_noticeboard_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('noticeboard')->__('Item does not exist'));
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
				     $vendorid = $data['vendor'];
					 $content = $data['content'];
				      $_target = Mage::getBaseDir('media') . DS . 'noticeboardimages' . DS;
					$targetimg = Mage::getBaseUrl('media').'noticeboardimages/';
					$name = $_FILES['image']['name'];
					$source_file_path = basename($name);
					$newfilename = mt_rand(10000000, 99999999).'_image.jpg';
					$path = $_target.$newfilename;
					$path1 = $targetimg.$newfilename;
					$pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="90px" height="80px"/></a>';
					file_put_contents($path, file_get_contents($_FILES['image']['tmp_name']));
			       $notice = Mage::getModel('noticeboard/noticeboard');
					$notice->setContent($content)
						   ->setType(3)
						   ->setStatus(1)
						   ->setVendor('admin')
						   ->setCreated(NOW())
						   ->setImage($pathhtml)
						   ->save();
					/* Starting upload */	
		/*			$uploader = new Varien_File_Uploader('image');
					
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
	  			
	  			
			$model = Mage::getModel('noticeboard/noticeboard');	
			
			$id = $this->getRequest()->getParam('id');	
			$model->setData($data)
			    ->setVendor('admin')
				->setId($this->getRequest()->getParam('id'))
				->setComment($this->getRequest()->getParam('comment'));
				
			try {
				
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					
					$model->setUpdateTime(now());
				}	
				
				$model->save();*/
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('noticeboard')->__('Item was successfully saved'));
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
	}
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('noticeboard')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('noticeboard/noticeboard');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Notice was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $noticeboardIds = $this->getRequest()->getParam('noticeboard');
        if(!is_array($noticeboardIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select notice(s)'));
        } else {
            try {
                foreach ($noticeboardIds as $noticeboardId) {
                    $noticeboard = Mage::getModel('noticeboard/noticeboard')->load($noticeboardId);
                    $noticeboard->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($noticeboardIds)
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
        $noticeboardIds = $this->getRequest()->getParam('noticeboard');
        if(!is_array($noticeboardIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select notice(s)'));
        } else {
            try {
				
                foreach ($noticeboardIds as $noticeboardId) {
                  //echo $this->getRequest()->getParam('status');exit;
				   $noticeboard = Mage::getSingleton('noticeboard/noticeboard')
				   
                        ->load($noticeboardId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($noticeboardIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	

	}
