<?php

class Craftsvilla_Craftsvillacustomer_Helper_Data extends Mage_Core_Helper_Abstract
{
   public $charset = 'ABCDEFGHKLMNPRSTUVWYZ0123456789abcdefghijklmnopqrstuvwxyz';
   
   public function getMessageId()
   {
       $code_length=16;
       $code = '';

       for($i = 1, $cslen = strlen($this->charset); $i <= $code_length; ++$i) {
           $code .= $this->charset{rand(0, $cslen - 1)};
       }
       //return 'testing';  // debug, set the code to given string
       return $code;
   }
   
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
    
    
}