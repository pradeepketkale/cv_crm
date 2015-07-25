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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* PDF label adapter
*
* Accepts following properties:
* - batch Unirgy_Dropship_Model_Label_Batch
* - vendor Unirgy_Dropship_Model_Vendor
* - force boolean
*/
class Unirgy_Dropship_Model_Label_Pdf
    extends Unirgy_Dropship_Model_Label_Abstract_Type
    implements Unirgy_Dropship_Model_Label_Interface_Type
{
    protected function _construct()
    {
        parent::_construct();
        $this->setContentType('application/x-pdf');
    }

    /**
    * When creating a new label, update shipment track record with filename info
    *
    * @param Mage_Sales_Model_Order_Shipment_Track $track
    * @param array $labelImages
    */
    public function updateTrack($track, $labelImages)
    {
        $fileNames = array();
        $labelDir = Mage::getConfig()->getVarDir('label').DS;

        foreach ((array)$labelImages as $i=>$label) {
           $fn = $track->getNumber().'-'.$i.'.png';
           $fileNames[] = $fn;
           file_put_contents($labelDir.$fn, base64_decode($label));
        }

        $track->setLabelImage(join("\n", $fileNames));
        $track->setLabelFormat('PDF');

        return $this;
    }

    /**
    * Generate PDF output
    *
    * @param Mage_Sales_Model_Mysql4_Order_Shipment_Track_Collection|array $tracks
    */
    public function renderTracks($tracks)
    {
        if ($this->getBatch()) {
            $batchPathName = $this->getBatchPathName($this->getBatch());
            if (!$this->getForce() && file_exists($batchPathName)) {
                //return file_get_contents($fileName);
            }
        }

        $v = $this->getVendor();
        if (!$v) {
            Mage::throwException('Vendor is not set');
        }

        $labelDir = Mage::getConfig()->getVarDir('label').DS;

        $pdf = new Zend_Pdf();
        foreach ($tracks as $track) {
            if ($track->getLabelFormat()!='PDF') {
                continue;
            }

            $data = null;
            if (($serialized = $track->getLabelRenderOptions())) {
                $data = unserialize($serialized);
            }

            $fileNames = explode("\n", $track->getLabelImage());
            foreach ($fileNames as $i=>$fileName) {
                $pdf->pages[] = $this->_renderPage($pdf, $labelDir.$fileName, $data);
            }
        }

        if ($this->getBatch()) {
            $pdf->save($batchPathName);
            return file_get_contents($batchPathName);
        }

        return $pdf->render();
    }

    /**
    * Render PDF page from PNG label image
    *
    * @param Zend_Pdf $pdf
    * @param string $fileName
    */
    protected function _renderPage(Zend_Pdf $pdf, $fileName, $data=null)
    {
        $v = $this->getVendor();
        $image = Zend_Pdf_Image::imageWithPath($fileName);

        $page = $pdf->newPage($v->getPdfPageSize());
        $wp = $page->getWidth()/$v->getPdfPageWidth();
        $hp = $page->getHeight()/$v->getPdfPageHeight();

        $r = (int)$v->getPdfLabelRotate();
        $l = (float)$v->getPdfLabelLeft();
        $t = (float)$v->getPdfLabelTop();
        $w = (float)$v->getPdfLabelWidth();
        $h = (float)$v->getPdfLabelHeight();

        if (!is_null($data)) {
            extract($data);
        }

        if ($r==90 || $r==270) {
            $tmp = $w; $w = $h; $h = $tmp;
        }
        $b = $v->getPdfPageHeight()-$t-$h;
        $page->drawImage($image, $l*$wp, $b*$hp, ($l+$w)*$wp, ($b+$h)*$hp);

        return $page;
    }

    public function renderBatchContent($batch=null)
    {
        if (is_null($batch)) {
            $batch = $this->getBatch();
        } else {
            $this->setBatch($batch);
        }

        $this->setVendor($batch->getVendor());

        return array(
            'content' => $this->renderTracks($batch->getBatchTracks()),
            'filename' => $this->getBatchFileName($batch),
            'type' => $this->getContentType(),
        );
    }

    public function renderTrackContent($track=null)
    {
        if (is_null($track)) {
            $track = $this->getTrack();
        } else {
            $this->setTrack($track);
        }

        $vendor = Mage::helper('udropship')->getVendor($track->getShipment()->getUdropshipVendor());
        $this->setVendor($vendor);

        return array(
            'content' => $this->renderTracks(array($track)),
            'filename' => $track->getNumber().'.pdf',
            'type' => $this->getContentType(),
        );
    }

    public function getBatchFileName($batch)
    {
        return 'label_batch-'.$batch->getId().'.pdf';
    }
}