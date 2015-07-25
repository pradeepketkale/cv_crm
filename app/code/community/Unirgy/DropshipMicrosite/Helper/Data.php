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

class Unirgy_DropshipMicrosite_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_currentVendor;
    protected $_origBaseUrl;
    protected $_parsedBaseUrl;
    protected $_vendorBaseUrl = array();

    public function getLandingPageTitle($vendor=null)
    {
    	if ($vendor==null) {
    		if (!$this->getCurrentVendor()) return '';
    		$vendor = $this->getCurrentVendor();
    	}
    	$title = Mage::getStoreConfig('udropship/microsite/landing_page_title');
    	if ($vendor->getData('landing_page_title')) {
    		$title = $vendor->getData('landing_page_title');
    	}
    	$title = str_replace('[vendor_name]', $vendor->getVendorName(), $title);
    	
    	/*Craftsvilla Comment
    	 * Added page number for pagination to title
    	 * Added by suresh on 30-06-2012.
    	 */
    	if($_GET['p'])
    	{
    		//return !empty($title) ? "Page ".$_GET['p']." - ".$title : "Page ".$_GET['p']." - ".$vendor->getVendorName();
    		return "Page ".$_GET['p']." of ".$vendor->getVendorName()." - Craftsvilla"; 
    	}
    	else {
			return !empty($title) ? $title : $vendor->getVendorName();    		
    	}
    }
    
    /*Start
    *Craftsvilla Comment
    *Added Meta Description by suresh on 28-06-2012
    */
    public function getLandingPageMetaDescription($vendor=null)
    {
    	if ($vendor==null) {
    		if (!$this->getCurrentVendor()) return '';
    		$vendor = $this->getCurrentVendor();
    	}
    	
    	if($_GET['p'])
    	{
   			$description = "Page ".$_GET['p']." of ".$vendor->getData('vendor_name')." - Craftsvilla";	
    	}
    	else {
    		$description = Mage::getStoreConfig('udropship/udropship/meta_description');
	    	if ($vendor->getData('meta_description')) {
	    		$description = $vendor->getData('meta_description');
	    	}
	    	
	    	$description = str_replace('[meta_description]', $vendor->getMetaDescription(), $description);
    	}

    	if(!empty($description))
    	{
    		if(strlen($description)>170)
    		{
    			$description = substr($description, 0, 170);
    		}
    	}
    	else
    	{
    		if(strlen($vendor->getShopDescription())>170)
    		{
    			$description = substr($vendor->getShopDescription(), 0, 170);
    		}
    		else {
    			$description = $vendor->getShopDescription();
    		}	
    	}
    	
    	return $description;
    }
    /*End
    *Craftsvilla Comment
    *Added Meta Description by suresh on 28-06-2012
    */

    public function resetCurrentVendor()
    {
        $this->_currentVendor = null;
        return $this;
    }
    protected $_gcvCycleCheck=false;
    public function getCurrentVendor()
    {
        if (is_null($this->_currentVendor) && !$this->_gcvCycleCheck) {
            $this->_gcvCycleCheck = true;
            if (Mage::app()->getStore()->isAdmin()) {
                if (($vendor = $this->getAdminhtmlVendor())) {
                    $this->_currentVendor = $vendor; // it's adminhtml (from user)
                } else {
                    $this->_currentVendor = false;
                }
            } elseif (($vendor = $this->getFrontendVendor())) {
                $this->_currentVendor = $vendor; // it's a frontend (from subdomain)
            } elseif (($product = Mage::registry('current_product'))) {
                $this->_currentVendor = Mage::helper('udropship')->getVendor($product);
                if (!$this->_currentVendor->getId()) {
                    $this->_currentVendor = false;
                }
            } else {
                // if route known, make it permanent
                if (Mage::app()->getRequest()->getRouteName()) {
                    $this->_currentVendor = false;
                }
            }
            $this->_gcvCycleCheck = false;
        }
        return $this->_currentVendor;
    }

    protected function _getVendorKeyFromRequest($url=false)
    {
        $request = $url
            ? new Mage_Core_Controller_Request_Http($url)
            : Mage::app()->getRequest();
        $pathInfo = $request->getPathInfo();
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $parsedRequest = $this->_parseRequest($request);
        return $parsedRequest[0];
    }
    protected function _parseRequest($request=false)
    {
        $request = $request ? $request : Mage::app()->getRequest();
        $requestUri = $request->getRequestUri();
        if (null === $requestUri) {
            $parsedRequest = array(null, null, null);
        } else {
            $pos = strpos($requestUri, '?');
            if ($pos) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            $baseUrl = $request->getBaseUrl();
            $pathInfo = substr($requestUri, strlen($baseUrl));
            if ((null !== $baseUrl) && (false === $pathInfo)) {
                $pathInfo = '';
            } elseif (null === $baseUrl) {
                $pathInfo = $requestUri;
            }
            if ($baseUrl && strlen($baseUrl)) {
                $baseUrl = substr($requestUri, 0, strlen($baseUrl));
            }
            $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
            $vUrlKey = $pathParts[0];
            $pathInfo = '/'.(isset($pathParts[1]) ? $pathParts[1] : '');
            $parsedRequest = array(
                $vUrlKey,
                $pathInfo,
                rtrim($baseUrl, '/').$pathInfo. ($pos!==false ? substr($requestUri, $pos) : '')
            );
        }
        return $parsedRequest;
    }

    protected function _removeVendorKeyFromRequest()
    {
        $parsedRequest = $this->_parseRequest();
        Mage::app()->getRequest()->setRequestUri($parsedRequest[2]);
        Mage::app()->getRequest()->setActionName(null);
        Mage::app()->getRequest()->setPathInfo();
    }

    public function getUrlFrontendVendor($url)
    {
        return $this->_getFrontendVendor($url);
    }
    public function getFrontendVendor()
    {
        return $this->_getFrontendVendor();
    }
    protected function _getFrontendVendor($useUrl=false)
    {
        $url = null;
        if (!$useUrl) {
            $this->_origBaseUrl = Mage::getStoreConfig('web/unsecure/base_link_url');
            $url = parse_url($this->_origBaseUrl);
            $this->_parsedBaseUrl = $url;
            $httpHost = $_SERVER["HTTP_HOST"];
        } else {
            $url = parse_url($useUrl);
            $httpHost =  $url['host'];
        }

        if (empty($httpHost)) {
            return false;
        }
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if ($level==1) {
            $vUrlKey = $this->_getVendorKeyFromRequest($useUrl);
        } else {
            $host = $httpHost;
            $hostArr = explode('.', trim($host, '.'));
            $i = sizeof($hostArr)-$level;
            $vUrlKey = @$hostArr[$i];
        }

        if (empty($level) || empty($vUrlKey)) {
            return false;
        }
//added a line to block url generalcheck on dated 01-07-2014	
//	if($vUrlKey == 'generalcheck'){ return false;}

//added a line to block url keys on dated 25-08-2014
$urlnocheck = array("generalcheck","jewellery-jewelry","jewellery-jewelry.html","sarees-sari.html","sarees-sari","clothing","bags.html","bags","home-decor-products.html","home-decor-products","akamai","catalog","hcheckout","checkout","marketplace","searchresults","customer","kribhasanvi","ajaxcartpro");

	if(in_array($vUrlKey,$urlnocheck)){return false;}

        $vendor = Mage::getModel('udropship/vendor')->load($vUrlKey, 'url_key');
        if (!$vendor->getId()) {
            return false;
        }
        if ($vendor->getStatus()!='A') {
            Mage::getSingleton('core/session', array('name'=>'frontend'))->start('frontend');
            $session = Mage::getSingleton('udropship/session');
#echo "<pre>"; print_r($session->debug()); echo "</pre>";
            if ($session->getId()!=$vendor->getId()) {
                return false;
            }
        }

        if (!$useUrl) {
            if ($level>1 && $this->updateStoreBaseUrl()) {
                $baseUrl = $url['scheme'].'://'.$host.(isset($url['path']) ? $url['path'] : '/');
                Mage::app()->getStore()->setConfig('web/unsecure/base_link_url', $baseUrl);
            } else {
                $this->_removeVendorKeyFromRequest();
            }
        }

        return $vendor;
    }

    public function getAdminhtmlVendor()
    {
        Mage::getSingleton('core/session', array('name'=>'adminhtml'))->start('adminhtml');
        $user = Mage::getSingleton('admin/session')->getUser();
        if (!$user) {
            return false;
        }
        $vId = $user->getUdropshipVendor();
        if ($vId) {
            $vendor = Mage::getModel('udropship/vendor')->load($vId);
            if ($vendor->getId()) {
                return $vendor;
            }
        }
        return false;
    }

    public function getManageProductsUrl()
    {
        $params = array();
        $hlp = Mage::getSingleton('adminhtml/url');
        if ($hlp->useSecretKey()) {
            $params[Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME] = $hlp->getSecretKey();
        }
        return $hlp->getUrl('adminhtml/catalog_product', $params);
    }

    public function getVendorBaseUrl($vendor=null)
    {
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if (is_null($vendor) || $vendor===true) {
            $vendor = $this->getCurrentVendor();
        } else {
            $vendor = Mage::helper('udropship')->getVendor($vendor);
        }
        if (!$level || !$vendor || !$vendor->getId() || !$vendor->getUrlKey()) {
            return $this->_origBaseUrl;
        }
        $vId = $vendor->getId();
        if (!isset($this->_vendorBaseUrl[$vId])) {
            $store = Mage::app()->getStore();
            if ($this->updateStoreBaseUrl() && $this->getFrontendVendor() && $this->getFrontendVendor()->getId()==$vendor->getId()) {
                $baseUrl = $store->getBaseUrl();
            } else {
                if (1 == $level) {
                    $store->useVendorUrl($vId);
                    $baseUrl = $store->getBaseUrl();
                    $store->resetUseVendorUrl();
                } else {
                    $url = $this->_parsedBaseUrl;
                    $hostArr = explode('.', trim($url['host'], '.'));
                    $l = sizeof($hostArr);
                    if ($l-$level>=0) {
                        $hostArr[$l-$level] = $vendor->getUrlKey();
                    } else {
                        array_unshift($hostArr, $vendor->getUrlKey());
                    }
                    $baseUrl = $url['scheme'].'://'.join('.', $hostArr).(isset($url['path']) ? $url['path'] : '/');
                }
            }
            $this->_vendorBaseUrl[$vId] = $baseUrl;
        }
        return $this->_vendorBaseUrl[$vId];
    }

    public function withOrigBaseUrl($url, $prefix='')
    {
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if (!$level) {
            return $url;
        }
        $p = parse_url($url);
        $host = join('.', array_slice(explode('.', trim($p['host'], '.')), 1-$level));
        return $p['scheme'].'://'.$prefix.$host.$p['path']
            .(!empty($p['query'])?'?'.$p['query']:'')
            .(!empty($p['fragment'])?'?'.$p['fragment']:'');
/*
        $o = $this->_origBaseUrl;
        $v = Mage::getStoreConfig('web/unsecure/base_url');
        return $o!=$v ? str_replace($v, $o, $url) : $url;
*/
    }

    public function updateStoreBaseUrl()
    {
        return Mage::getStoreConfig('udropship/microsite/update_store_base_url');
    }

    /**
    * Get URL specific for vendor
    *
    * @param boolean|integer|Unirgy_Dropship_Model_Vendor $vendor
    * @param string|Mage_Catalog_Model_Product $orig original product or URL to be converted to vendor specific
    */
    public function getVendorUrl($vendor, $origUrl=null)
    {
    	if ($vendor===true) {
    		$vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return $origUrl;
            }
        } else {
        	$vendor = Mage::helper('udropship')->getVendor($vendor);
        }
        
        $vendorBaseUrl = $this->getVendorBaseUrl($vendor);
        
        if (is_null($origUrl)) {
       		return $vendorBaseUrl;	
        }
        if ($origUrl instanceof Mage_Catalog_Model_Product) {
            $origUrl = $origUrl->getProductUrl();
        }
        
        if ($this->updateStoreBaseUrl() && ($curVendor = $this->getCurrentVendor())) {
            if ($curVendor->getId()==$vendor->getId()) {
                return $origUrl;
            }
            $origBaseUrl = $this->getVendorBaseUrl($curVendor);
        } else {
            $origBaseUrl = $this->_origBaseUrl;
        }
        
        return str_replace($origBaseUrl, $vendorBaseUrl, $origUrl);
    }

    public function getProductUrl($product)
    {
        return $this->getVendorUrl(Mage::helper('udropship')->getVendor($product), $product);
    }

    public function getVendorRegisterUrl()
    {
        return Mage::getUrl('umicrosite/vendor/register');
    }

    public function sendVendorSignupEmail($registration)
    {
        $store = Mage::app()->getStore();
        Mage::helper('udropship')->setDesignStore($store);
        /***** Craftsvilla comment - Added by Mandar on 27/04/2012 for password decrypt *****/
            
        $password = Mage::helper('core')->decrypt($registration->getPasswordEnc());
            
        /************************************************************************************/
        Mage::getModel('core/email_template')->sendTransactional(
            $store->getConfig('udropship/microsite/signup_template'),
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            $registration->getEmail(),
            $registration->getVendorName(),
            array(
                'store_name' => $store->getName(),
                'vendor' => $registration,
                'password' => $password,
            )
        );
        Mage::helper('udropship')->setDesignStore();

        return $this;
    }
    
    /***** Craftsvilla comment - Added by Mandar on 27/04/2012 add parameter password  *****/
    public function sendVendorWelcomeEmail($vendor)
    {
        $store = Mage::app()->getStore();
        Mage::helper('udropship')->setDesignStore($store);
        $password = Mage::helper('core')->decrypt($vendor->getPasswordEnc());
        Mage::getModel('core/email_template')->sendTransactional(
            $store->getConfig('udropship/microsite/welcome_template'),
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            $vendor->getEmail(),
            $vendor->getVendorName(),
            array(
                'store_name' => $store->getName(),
                'vendor' => $vendor,
                'password' => $password,
            )
        );
        Mage::helper('udropship')->setDesignStore();

        return $this;
    }

    public function getDomainName()
    {
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if (!$level) {
            return '';
        }
        $baseUrl = Mage::getStoreConfig('web/unsecure/base_url');
        $url = parse_url($baseUrl);
        $hostArr = explode('.', $url['host']);
        return join('.', array_slice($hostArr, -($level-1)));
    }

    /**
    * Send new registration to store owner
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    * @param string $comment
    */
    public function sendVendorRegistration($registration)
    {
        $store = Mage::app()->getStore($registration->getStoreId());
        $to = $store->getConfig('udropship/microsite/registration_receiver');
        $subject = $store->getConfig('udropship/microsite/registration_subject');
        $template = $store->getConfig('udropship/microsite/registration_template');
        $ahlp = Mage::getModel('adminhtml/url');

        if ($to && $subject && $template) {
            $data = $registration->getData();
            $data['store_name'] = $store->getName();
            $data['registration_url'] = $ahlp->getUrl('umicrositeadmin/adminhtml_registration/edit', array(
                'reg_id' => $registration->getId(),
                'key' => null,
            ));
            $data['all_registrations_url'] = $ahlp->getUrl('umicrositeadmin/adminhtml_registration', array(
                'key' => null,
            ));

            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            foreach (explode(',', $to) as $toEmail) {
                mail(trim($toEmail), $subject, $template, 'From: "'.$registration->getVendorName().'" <'.$registration->getEmail().'>');
            }
        }

        return $this;
    }

    public function addVendorFilterToProductCollection($collection)
    {
        $vendor = $this->getCurrentVendor();

        try {
            if ($vendor) {
                if (Mage::getStoreConfigFlag('udropship/microsite/front_show_all_products')) {
                    
                    $collection->addIdFilter(array_keys($vendor->getAssociatedProducts()));
                    $alreadyJoined = false;
                    foreach ($collection->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $column) {
                        if ($column[2]=='vendor_product_id' || $column[2]=='udmulti_status') {
                            $alreadyJoined = true;
                            break;
                        }
                    }
                    if (!$alreadyJoined) {
                        /*
                        $collection->joinTable(
                            'udropship/vendor_product', 'product_id=entity_id',
                            array('udmulti_status'=>'status','vendor_product_id'=>'vendor_product_id'),
                            "{{table}}.vendor_id='{$vendor->getId()}'", 'left'
                        );
                        $collection->addAttributeToFilter(array(
                            array('attribute' => 'udmulti_status', 'eq'=>1),
                            array('attribute' => 'vendor_product_id', 'null'=>1),
                        ));
                        */
                    }

                } else {
					
                /*below line commented By Dileswar On dated 03-10-2013  ---------------START-------------*/
				    //$collection->addAttributeToFilter('udropship_vendor', $vendor->getId());
			 /*below line commented By Dileswar On dated 03-10-2013  ---------------END-------------*/
			 /*below line Added for retrieving the value from table new `catalog-product-vendor` By Dileswar On dated 03-10-2013  ---------------start-------------*/		
					$alreadyJoined1 = false;
					//echo '<pre>';print_r($collection->getSelect()->getPart(Zend_Db_Select::COLUMNS));exit;
					 foreach ($collection->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $column) {
                        //print_r($column);exit;
						if ($column[1]=='udropship_vendor') {
                            $alreadyJoined1 = true;
                            break;
                        	}
						}
						if (!$alreadyJoined1) {
					$select = $collection->getSelect();
					
					// Below four lines commnted an added only one condition to get only specified vendor products ..it changed on dated 17-01-2013 mby dileswar 
					
					$select->where("e.udropship_vendor = '".$vendor->getId()."'");
					/*$select->join(
								array('o_item' => 'catalog_product_vendor'),
									  'o_item.product_id = e.entity_id AND o_item.udropship_vendor = "' . $vendor->getId() . '"',
					            array('product_id', 'udropship_vendor')
					);*/
					//->group('e.entity_id');
					}

					//echo $collection->getSelect()->__toString();exit;
			 /*below line Added for retrieving the value from table new `catalog-product-vendor` By Dileswar On dated 03-10-2013  ---------------END-------------*/		
					
					
					
					
					
					
					/// Craftsvilla Comment Added By Amit Pitre On 31-07-2012 for Product Qty In Vendor Shop Page.//////
			
			//***----Commented By Dileswar--To block the `Sold Out` Banner on List page On Dated 22-03-2013........---***//		
			
					//$collection->joinAttribute('udropship_vendor', 'catalog_product/udropship_vendor', 'entity_id', null, 'left');
					//$collection->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array('is_in_stock' => 'is_in_stock'), null , 'left');
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                }
            } else {
                                //***----Below Two line Commented By Dileswar--To block the extra query List page On Dated 21-02-2014........from line-558 to 572---***//		
			    /*$cond = "{{table}}.vendor_id IS null OR {{table}}.status='A'";
                $session = Mage::getSingleton('udropship/session');
                if ($session->isLoggedIn() && $session->getVendor()->getStatus()=='I') {
                    $cond .= " OR {{table}}.vendor_id=".$session->getVendor()->getId();
                }
                $alreadyJoined = false;
                foreach ($collection->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $column) {
                    
                    
                    if ($column[2]=='udropship_vendor' || $column[2]=='udropship_status') {
                        $alreadyJoined = true;
                        break;
                    }
                }*/
               
                //if (!$alreadyJoined) {
                 //***----Below Two line Commented By Dileswar--To block the extra query List page On Dated 21-02-2014........---***//		
				    //$collection->joinAttribute('udropship_vendor', 'catalog_product/udropship_vendor', 'entity_id', null, 'left');
                   // $collection->joinField('udropship_status', 'udropship/vendor', 'status', 'vendor_id=udropship_vendor', $cond, 'left');
                 	//echo $collection->getSelect()->__toString();exit;
				 //***----Commented By Dileswar--To block the `Sold Out` Banner on List page On Dated 22-03-2013........---***//		
				    
					//$collection->joinField('seller_priority', 'udropship/vendor', 'seller_priority','vendor_id=udropship_vendor',$cond, 'left');
					/// Craftsvilla Comment Added By Amit Pitre On 31-07-2012 for Product Qty In Product List Page.//////
					//$collection->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array('is_in_stock' => 'is_in_stock'), null , 'left');
					///////////////////////////////////////////////////////////////////////////////////////////////////////
                    //$collection->setOrder('seller_priority', 'DESC');
                //}
            }

        } catch (Exception $e) {
            $skip = array(
                Mage::helper('eav')->__('Joined field with this alias is already declared'),
                Mage::helper('eav')->__('Invalid alias, already exists in joined attributes'),
                Mage::helper('eav')->__('Invalid alias, already exists in joint attributes.'),
            );
            if (!in_array($e->getMessage(), $skip)) {
                throw $e;
            }
        }
        return $this;
    }

////////////////////////////////////  Craftsvilla Comment - Below functions are used to resize images other than product images (on 04-05-2012)/////////////

	/**
     * Returns the resized Image URL
     *
     * @param string $imgUrl - This is relative to the the media folder (custom/module/images/example.jpg)
     * @param int $x Width
     * @param int $y Height
     */
    public function getResizedUrl($imgUrl,$x,$y=NULL){
        $imgPath=$this->splitImageValue($imgUrl,"path");
        $imgName=$this->splitImageValue($imgUrl,"name");
 
        /**
         * Path with Directory Seperator
         */
        $imgPath=str_replace("/",DS,$imgPath);
 
        /**
         * Absolute full path of Image
         */
        $imgPathFull=Mage::getBaseDir("media").DS.$imgPath.DS.$imgName;
 
        /**
         * If Y is not set set it to as X
         */
        $widht=$x;
        $y?$height=$y:$height=$x;
 
        /**
         * Resize folder is widthXheight
         */
        $resizeFolder=$widht."X".$height;
 
        /**
         * Image resized path will then be
         */
        $imageResizedPath=Mage::getBaseDir("media").DS.$imgPath.DS.$resizeFolder.DS.$imgName;
 
        /**
         * First check in cache i.e image resized path
         * If not in cache then create image of the width=X and height = Y
         */
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) :
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->resize($widht,$height);
            $imageObj->save($imageResizedPath);
        endif;
 
        /**
         * Else image is in cache replace the Image Path with / for http path.
         */
        $imgUrl=str_replace(DS,"/",$imgPath);
 
        /**
         * Return full http path of the image
         */
        return Mage::getBaseUrl("media").$imgUrl."/".$resizeFolder."/".$imgName;
    }
 
    /**
     * Splits images Path and Name
     *
     * Path=custom/module/images/
     * Name=example.jpg
     *
     * @param string $imageValue
     * @param string $attr
     * @return string
     */
    public function splitImageValue($imageValue,$attr="name"){
        $imArray=explode("/",$imageValue);
 
        $name=$imArray[count($imArray)-1];
        $path=implode("/",array_diff($imArray,array($name)));
        if($attr=="path"){
            return $path;
        }
        else
            return $name;
 
    }
public function checkBankAccountExists($bank_ac_number)
{
        $bank_ac_number1 = str_replace("'","",$bank_ac_number);
        $generalhlp = Mage::helper('generalcheck');
        $mainconn = $generalhlp->getMaindbconnection();
        $vendorInfoCraftsvilla = $generalhlp->getvendorInfoCraftsvillaTable();
        $bankquery = "SELECT `bank_account_number` from `".$vendorInfoCraftsvilla."` WHERE `bank_account_number` = '".$bank_ac_number1."'";
        $bankdetails = mysql_query($bankquery,$mainconn);
$val = mysql_fetch_array($bankdetails);

        mysql_close($mainconn);
        if($val['bank_account_number'])
        {
                 return true;
        }
        else{
                return false;
        }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
