<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Previousnext
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Previousnext_Block_Previousnext extends Mage_Catalog_Block_Product_Abstract
{
	const LOOP = 'previousnext/general/loopproducts';
	const DISPLAY_CONTROLS = 'previousnext/general/displaycontrols';
	const STRING_LENGTH = 'previousnext/general/symbolsnumber';
	const STRING_ENDING = 'previousnext/general/ending';

	const UP_ENABLED = 'previousnext/upcontrol/upcontrol';

	const PREVIOUS_LINK_TEXT = 'previousnext/previouscontrol/linktext';
	const NEXT_LINK_TEXT = 'previousnext/nextcontrol/linktext';
	const UP_LINK_TEXT = 'previousnext/upcontrol/linktext';

	const PREVIOUS_IMAGE = 'previousnext/previouscontrol/image';
	const NEXT_IMAGE = 'previousnext/nextcontrol/image';
	const UP_IMAGE = 'previousnext/upcontrol/image';

    protected $previousProduct;
    protected $nextProduct;

    protected function _tohtml()
    {
		/* If customer want to disable standart display of PN block, he can insert
         * block initialization from phtml. from_xml variable is a flag in xml file,
         * that means that block is calling from xml
         */
        if ($this->getFromXml()=='yes'&&!Mage::getStoreConfig(self::DISPLAY_CONTROLS))
            return parent::_toHtml();

		$this->setLinksforProduct();
        $this->setTemplate("previousnext/links.phtml");
		return parent::_toHtml();
    }

	/**
     * Set $this->previousProduct and $this->nextProduct variables
     */
    protected function setLinksforProduct()
	{
        $current_product = Mage::registry('current_product');
		$category = Mage::registry('current_category');
		$products = Mage::getModel('catalog/layer')->getProductCollection();

		$product_ids = array();
		foreach ($products as $key=>$item)
			$product_ids[] = $key;

		$prevId = $nextId = 0;
        foreach ($product_ids as $key=>$item)
		{
			if ($item == $current_product->getId())
			{
				if (($key-1)>=0)
					$prevId = $product_ids[$key-1];
				elseif (Mage::getStoreConfig(self::LOOP))
					$prevId = $product_ids[count($product_ids)-1];
				else
					$prevId = -1;

				if (($key+1)!=count($product_ids))
					$nextId = $product_ids[$key+1];
				elseif (Mage::getStoreConfig(self::LOOP))
					$nextId = $product_ids[0];
				else
					$nextId = -1;
			}
		}
        $currentStoreId = Mage::app()->getStore()->getId();
        $this->previousProduct = Mage::getModel('catalog/product')->setStoreId($currentStoreId)->load($prevId);
        $this->nextProduct = Mage::getModel('catalog/product')->setStoreId($currentStoreId)->load($nextId);
	}

	public function getPreviousProductText()
	{
        if ($this->previousProduct->getId()==Mage::registry('current_product')->getId())
            return '';
		return $this->getFormatedText(Mage::getStoreConfig(self::PREVIOUS_LINK_TEXT), $this->previousProduct);
	}
	
	public function getNextProductText()
	{
		if ($this->nextProduct->getId()==Mage::registry('current_product')->getId())
            return '';
		return $this->getFormatedText(Mage::getStoreConfig(self::NEXT_LINK_TEXT), $this->nextProduct);
	}
	
	public function getUpLevelText()
	{ return $this->getFormatedText(Mage::getStoreConfig(self::UP_LINK_TEXT)); }

	/**
     * Format linkText string length, replace #PRODUCT and $CATEGORY variables to product and category names
     * @param string $linkText
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getFormatedText($linkText, $product=null)
	{
		if ($product)
		{
			$productName = $product->getName();
			$origLength = strlen($productName);
			$ready = substr($productName, 0, Mage::getStoreConfig(self::STRING_LENGTH));
			$newLength = strlen($ready);
						
			if ($newLength < $origLength)
				$ready .= Mage::getStoreConfig(self::STRING_ENDING);

			$readyLink = str_replace('#PRODUCT#', $ready, $linkText);
		}
		else
		{
			$categoryName = Mage::registry('current_category')->getName();
			$origLength = strlen($categoryName);
			$ready = substr($categoryName, 0, Mage::getStoreConfig(self::STRING_LENGTH));
			$newLength = strlen($ready);

			if ($newLength < $origLength)
				$ready .= Mage::getStoreConfig(self::STRING_ENDING);

			$readyLink = str_replace('#CATEGORY#', $ready, $linkText);
		}

		return $readyLink;
	}

	public function getPreviousProductLabel()
	{ return htmlspecialchars($this->previousProduct->getName()); }

	public function getNextProductLabel()
	{ return htmlspecialchars($this->nextProduct->getName());	}

	public function getUpCategoryLabel()
	{ return htmlspecialchars(Mage::registry('current_category')->getName());	}

	public function getPreviousProductImage()
	{ return $this->getImageFromConfig(Mage::getStoreConfig(self::PREVIOUS_IMAGE));	}
	
	public function getNextProductImage()
	{ return $this->getImageFromConfig(Mage::getStoreConfig(self::NEXT_IMAGE));	}

    public function getUpLevelImage()
	{ return $this->getImageFromConfig(Mage::getStoreConfig(self::UP_IMAGE)); }

	protected function getImageFromConfig($path)
	{
		if ($path)
			return Mage::getBaseUrl('media') . 'catalog/product/awpn/' . $path;
		return;
	}

    public function getPreviousProductLink()
	{
        if ($this->previousProduct->getId())
            return $this->getProductLinkUrl($this->previousProduct);
		return '';
	}

    public function getNextProductLink()
	{
		if ($this->nextProduct->getId())
            return $this->getProductLinkUrl($this->nextProduct);
		return '';
	}

    public function getUpLevelLink()
	{
		if (Mage::registry('current_category')&&Mage::getStoreConfig(self::UP_ENABLED))
			return Mage::registry('current_category')->getUrl();
    	return '';
	}

    /**
     * Get link for product
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getProductLinkUrl($product)
    {
        if (preg_match('/^1.4/', Mage::getVersion()))
        {
            $additional = array();
            if (!Mage::getStoreConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY))
                $additional['_ignore_category'] = true;
            $url = $product->getUrlModel()->getUrl($product, $additional);
        }
        else
        {
            $url = $product->getProductUrl();
        }
        return $url;
   }
}