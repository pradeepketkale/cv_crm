<?php
class Craftsvilla_Wishlist_PublicshareController extends Mage_Core_Controller_Front_Action
{
    public function sharepublicAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function sharepublicproductsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
	 public function trendAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
	
     public function shareoptionAction()
    {
        $custShareOpt=$this->getRequest()->getParam('custshare');
        $custId=$this->getRequest()->getParam('custid');
        $_customer=Mage::getModel('customer/customer')->load($custId);
        $_customer->setWishlistShare($custShareOpt)->save();
        
        if($custShareOpt==0){
			echo "0";
		}
		else{
			echo "1";
		}
        
         
    }
    
    public function recentwishlistproductsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function notificationAction()
    {
        
        $addedAt=$this->getRequest()->getParam('lastid');
     //echo 'hhh==='.$addedAt;exit;
        $itemsCollection=Mage::getModel('wishlist/item')->getCollection()
                        ->addFieldToSelect('added_at')
                        ->addFieldToFilter('added_at', array('gt' => $addedAt))
                        ->setOrder('added_at','DESC');
           
        $newAdded=$itemsCollection->count();
        //if($newAdded>0):
            echo $newAdded;
        //endif;
    
    }
    
    public function addnewprodAction()
    {
       $addedAt=$this->getRequest()->getParam('lastid');

        $_itemCollection=Mage::getModel('wishlist/item')->getCollection()
                        ->addFieldToSelect(array('product_id','added_at'))
                        ->addFieldToFilter('added_at', array('gt' => $addedAt))
                        ->setOrder('added_at', 'DESC')->setPageSize(10);
        
        if($_itemCollection->count()>0): 
            $_collection=$_itemCollection->getData();
            $valueId=$_collection[0]['added_at'];
        //echo 'hhh===='.$valueId;
           $_itemData='';
           foreach($_itemCollection as $_item):
                $_productCollection=Mage::getModel('catalog/product')->load($_item->getProductId());
                $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_productCollection)->getQty();

             $_itemData .=   
                '<div class="item">
                  <div class="prCnr">';
             $_itemData .='<a href="'.$_productCollection->getProductUrl().'">';
             $_itemData .='<div class="pro_image"><img src="'.Mage::helper('catalog/image')->init($_productCollection, 'small_image')->resize(170).'" width="170" /></div>';
             $_itemData .='<div class="productinfo">
                            <p class="productname">'.$_productCollection->getName().'</p>';
                          if($_productCollection->getSpecialPrice()):
                          $_itemData .='<p class="old-price floatr">             
                                    <span id="old-price-27370" class="price">Rs. '.number_format($_productCollection->getPrice(),0).'</span>
                                </p>
                                <p class="special-price">
                                    <span id="product-price-27370" class="price">Rs. '.number_format($_productCollection->getSpecialPrice(),0).'</span>
                                </p>';
                          else:
                          $_itemData .='<p>
                                    <span id="product-price-27370" class="price">Rs. '. number_format($_productCollection->getPrice(),0).'</span>
                                </p>';
                          endif; 

                          if($qtyStock<1):
               $_itemData .='<p class="sold_icon spriteimg"></p>';
                          elseif($_productCollection->getSpecialPrice()):
               $_itemData .='<p class="sell_icon spriteimg"></p>';
                          endif;
               $_itemData .='</div>
                    </a>
                  </div>
                </div>';
             endforeach;
             $json=array();
             $json['divvalue'] =$_itemData;
             $json['newvalue'] =$valueId;
             
                    
            echo json_encode($json);
        else:
            echo 0;
        endif;
        
    }
    
    public function wishthisprodAction()
    {
    	if(Mage::getSingleton('customer/session')->isLoggedIn())
        echo $this->getLayout()->createBlock('catalog/product_view')->setTemplate('catalog/product/view/addto.phtml')->toHtml();
    	else 
    	echo 'notlogged';
    }
	
	public function isCustomerLoggedAction()
    {
		
		if(Mage::getSingleton('customer/session')->isLoggedIn())
		{
			
			echo  'login';
		}
		else
		{
			
			echo  'logout';
		}
        
    }
}

?>
