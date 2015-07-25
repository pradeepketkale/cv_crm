<?php
abstract class Unirgy_Dropship_Model_Abstract extends Mage_Core_Model_Abstract
{
    protected $_rgb = array();
    protected $_moveStack = array();
    protected $_imageCache = array();
    protected $_pageWidth = 8.5;
    protected $_pageHeight = 11;
    protected $_lineHeight = 1.3;
    protected $_marginBottom = 1;
    
    const ENCLOSURE = '"';
    const DELIMITER = ',';
    
    
    /**
     * Formats a price by adding the currency symbol and formatting the number 
     * depending on the current locale.
     *
     * @param Float $price The price to format
     * @param Mage_Sales_Model_Order $formatter The order to format the price by implementing the method formatPriceTxt($price)
     * @return String The formatted price
     */
    protected function formatPrice($price, $formatter) 
    {
        return $formatter->formatPriceTxt($price);
    }



    protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 22, 760, 170, 800);
            }
        }
        //return $page;
    }

    protected function insertLogoSmall(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 285, 760, 360, 790);
            }
        }
        //return $page;
    }

    protected function insertAddress(&$page, $store = null)
    {
        $this->_setFontBold($page, 6);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
       
        $this->y = 800;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), 285, $this->y-153, 'UTF-8');
                $this->y -=9;
            }
        }
        //return $page;
    }


    protected function insertFooterAddress(&$page, $store = null)
    {
        $this->_setFontBold($page, 8);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        $this->y = 35;
        $address=Mage::getStoreConfig('sales/identity/address', $store);
        $values=str_replace("\n", '', $address);
        $page->drawText(trim(strip_tags($values)), 50, $this->y, 'UTF-8');
        
        //return $page;
    }

    /**
     * Format address
     *
     * @param string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 50, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    protected function _formatAddressShipment($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 65, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    
    protected function _beforeGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
    }

    /**
     * After getPdf processing
     *
     */
    protected function _afterGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    }

    
    /**
     * Draw Item process
     *
     * @param Varien_Object $item
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     * @return Zend_Pdf_Page
     */
    
    protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

  
    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        $this->x = 0;
        $this->y = $page->getHeight();
        $this->xdpi = $page->getWidth()/$this->_pageWidth;
        $this->ydpi = $page->getHeight()/$this->_pageHeight;

        $this->setPage($page)
            ->fillColor(0)
            ->lineColor(0)
            ->setUnits('inch')
            ->setAlign('left')
            ->setLineHeight($this->_lineHeight)
            ->font('normal', 10)
            ->setMarginBottom($this->_marginBottom)
        ;

        return $page;
    }
    
    public function move($x=0, $y=0, $units=null)
    {
        $page = $this->getPage();
        $units = strtoupper(!is_null($units) ? $units : $this->getUnits());
        switch ($units) {
        case 'INCH':
            if (!is_null($x)) $this->x = $this->xdpi*$x;
            if (!is_null($y)) $this->y = $page->getHeight()-$this->ydpi*$y;
            break;

        case 'POINT':
            if (!is_null($x)) $this->x = $x;
            if (!is_null($y)) $this->y = $page->getHeight()-$y;
        }
        return $this;
    }

    public function moveRel($x=0, $y=0, $units=null)
    {
        $page = $this->getPage();
        $units = strtoupper(!is_null($units) ? $units : $this->getUnits());
        switch ($units) {
        case 'INCH':
            $this->x += $this->xdpi*$x;
            $this->y -= $this->ydpi*$y;
            break;

        case 'POINT':
            $this->x += $x;
            $this->y -= $y;
        }
        return $this;
    }

    public function movePush()
    {
        array_push($this->_moveStack, array($this->x, $this->y));
        return $this;
    }

    public function movePop($x=0, $y=0, $units=null)
    {
        $p = array_pop($this->_moveStack);
        $this->x = $p[0];
        $this->y = $p[1];
        $this->moveRel($x, $y, $units);
        return $this;
    }

    public function font($type, $size=null)
    {
        switch (strtoupper($type)) {
        case 'NORMAL':
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
            break;
        case 'BOLD':
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
            break;
        case 'ITALIC':
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
            break;
        case 'BOLD_ITALIC':
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
            break;
        default:
            Mage::throwException('Invalid font type');
        }
        $this->setFont($font);
        if ($size) {
            $this->setFontSize($size);
        }
        $this->getPage()->setFont($font, $this->getFontSize());
        return $this;
    }

    public function fontSize($size)
    {
        $this->setFontSize($size);
        $this->getPage()->setFont($this->getFont(), $size);
        return $this;
    }
    
    public function text($text, $moveTo=null, $maxLength=null)
    {
        $page = $this->getPage();
        $origLines = explode("\n", $text);
        $lines = array();
        $widths = array();
        foreach ($origLines as $line) {
            $line = trim($line);
            if ($maxLength) {
                while (strlen($line)>$maxLength) {
                    $cutLine = substr($line, 0, $maxLength);
                    $cutoff = strrpos($cutLine, ' ');
                    if ($cutoff===false) {
                        $cutoff = $maxLength;
                    }
                    $lines[] = trim($_line=substr($line, 0, $cutoff));
                    $widths[] = $this->getTextWidth($_line);
                    $line = trim(substr($line, $cutoff));
                }
            }
            if (strlen($line)>0) {
                $lines[] = $line;
                $widths[] = $this->getTextWidth($line);
            }
        }
        $lineHeight = $this->getFontSize()*($this->getLineHeight() ? $this->getLineHeight() : 1.3);
        $align = strtoupper($this->getAlign());
        $t = $this->y;
        $this->movePush();
        foreach ($lines as $i=>$line) {
            switch ($align) {
            case 'RIGHT':
                $offset = $widths[$i];
                break;
            case 'CENTER':
                $offset = $widths[$i]/2;
                break;
            default:
                $offset = 0;
            }
            $page->drawText($line, $this->x-$offset, $this->y-$this->getFontSize(), 'UTF-8');
            $this->moveRel(0, $lineHeight, 'point');
        }
        $height = $t-$this->y;
        $this->setMaxHeight(max($this->getMaxHeight(), $height));
        switch (strtoupper($moveTo)) {
        case 'RIGHT':
            $this->movePop(array_reduce($widths, 'max'), 0, 'point');
            break;
        case 'DOWN':
            $this->movePop(0, $height, 'point');
            break;
        default:
            $this->movePop();
        }
        return $this;
    }

    public function price($amount, $moveTo=null, $maxLength=null)
    {
        $this->text(Mage::helper('core')->formatPrice($amount, false), $moveTo, $maxLength);
        return $this;
    }

    public function getTextHeight($units=null)
    {
        $txtHeight = $this->getFontSize()*($this->getLineHeight() ? $this->getLineHeight() : 1.3);
        $units = strtoupper(!is_null($units) ? $units : $this->getUnits());
        switch ($units) {
        case 'INCH':
            $txtHeight = $txtHeight>0 ? $txtHeight/$this->xdpi : $txtHeight;
            break;
        }
        return $txtHeight;
    }
    
    public function getTextWidth($string)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $font = $this->getFont();
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $this->getFontSize();
        return $stringWidth;
    }

    public function image($path, $w, $h)
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }
        $l = $this->x;
        $t = $this->y;
        $this->movePush()->moveRel($w, $h);

        if (!isset($this->_imageCache[$path])) {
            try {
                $this->_imageCache[$path] = Zend_Pdf_Image::imageWithPath($path);
            } catch (Exception $e) {
                $this->_imageCache[$path] = false;
                Mage::log('Unable to load image: %s', $path);
            }
        }
        if ($this->_imageCache[$path]) {
            $this->getPage()->drawImage($this->_imageCache[$path], $l, $this->y, $this->x, $t);
            $this->movePop();
        }
        return $this;
    }

    public function rectangle($w, $h, $fillColor=null, $lineColor=null)
    {
        $l = $this->x;
        $t = $this->y;
        $this->movePush()->moveRel($w, $h);
        if (!is_null($fillColor)) {
            $oldFillColor = $this->getFillColor();
            $this->fillColor($fillColor);
        }
        if (!is_null($lineColor)) {
            $oldLineColor = $this->getLineColor();
            $this->lineColor($lineColor);
        }

        $this->getPage()->drawRectangle($l, $t, $this->x, $this->y);

        if (isset($oldFillColor)) {
            $this->fillColor($oldFillColor);
        }
        if (isset($oldLineColor)) {
            $this->lineColor($oldLineColor);
        }
        $this->movePop();
        return $this;
    }

    public function line($w, $h, $lineColor=null)
    {
        $l = $this->x;
        $t = $this->y;
        $this->movePush()->moveRel($w, $h);
        if (!is_null($lineColor)) {
            $oldLineColor = $this->getLineColor();
            $this->lineColor($lineColor);
        }

        $this->getPage()->drawLine($l, $t, $this->x, $this->y);

        if (isset($oldLineColor)) {
            $this->lineColor($oldLineColor);
        }
        $this->movePop();
        return $this;
    }

    public function colorValue($c)
    {
        if (!is_array($c)) {
            $c = array($c, $c, $c);
        }
        $key = join('-', $c);
        if (empty($this->_rgb[$key])) {
            $this->_rgb[$key] = new Zend_Pdf_Color_Rgb($c[0], $c[1], $c[2]);
        }
        return $this->_rgb[$key];
    }

    public function fillColor($color)
    {
        $this->setFillColor($color);
        $this->getPage()->setFillColor($this->colorValue($color));
        return $this;
    }

    public function lineColor($color)
    {
        $this->setLineColor($color);
        $this->getPage()->setLineColor($this->colorValue($color));
        return $this;
    }
    
    public function checkPageOverflow($add=0, $headerMethod='insertPageHeader')
    {
        if (strtoupper($this->getUnits())=='INCH') {
            $marginBottom = $this->xdpi*($add+$this->getMarginBottom());
        }
        if ($this->y<=$marginBottom) {
            $this->newPage();
            //$this->$headerMethod();
        }
        return $this;
    }
    
    protected function getHeadRowValues() 
    {
        return array(
            'Date of Shipment',
            'Shipment No',
            'SKU Id',
            'Name',
            'Quantity',
            'Selling Price',
            'Shipping',
            'Shipment Payout Status',
            'Shipment Payout Update Date',
            'Commission',
            'Net Amount'
    	);
    }
    
    protected function writeHeadRow($fp) 
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

}
?>