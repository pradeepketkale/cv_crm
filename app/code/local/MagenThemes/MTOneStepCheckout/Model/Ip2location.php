<?php
/******************************************************
 * @package MT OneStepCheckOut module for Magento 1.4.x.x and Magento 1.5.x.x
 * @version 1.5.2.1
 * @author http://www.magentheme.com
 * @copyright (C) 2011- MagenTheme.Com
 * @license PHP files are GNU/GPL
*******************************************************/
?>
<?php
class MagenThemes_MTOneStepCheckout_Model_Ip2location
{
    var $countries_list; //Percorso lista degli stati
    var $flags_dir;      //Directory con le bandiere
    var $_COUNTRIES;     //Array con la lista degli stati
    var $DATABASE = 'whois.ripe.net'; //server open socket

    public function __construct() {
        $this->ip2country($this->getCountryList(), $this->getFlagsDir());
        if(Mage::helper('mtonestepcheckout')->geoIpDatabase() && Mage::helper('mtonestepcheckout')->geoIpDatabase() != $this->DATABASE) {
            $this->DATABASE = Mage::helper('mtonestepcheckout')->geoIpDatabase();
        }
    }

    private function ip2country($cl,$fd,$ip=false)
    {
        $this->countries_list=$cl;
        $this->flags_dir=$fd;

        $this->readCountries();// or die ("Unable to read the countries");

        if ((bool)$ip) return $this->parseIP($ip);
    }

    public function parseIP($ip)
    {
        $info = '' ;
        $sk=fsockopen($this->DATABASE, 43, $errno, $errstr, 30) or  die ("Unable to connect to the server");
        fputs ($sk, $ip ."\r\n") or die ("Unable to send data to the server");
        while (!feof($sk))
        {
            $info.= fgets ($sk, 2048);
        }

        if (preg_match( '/^\x20*country\x20*:\x20*(\w{2})/im',$info,$arr ))
        {
            $found=false;
            for($i=0;$i<count($this->_COUNTRIES);$i++)
            {
                $c=$this->_COUNTRIES[$i];
                if (trim($c[0]) == trim($arr[1])) return $c;
            }
            return array("??","","");
        }
        else array("??","","");
    }

    public function readCountries()
    {
        if (file_exists($this->countries_list))
        {
            $handle = file($this->countries_list) or die("Unable to open the countries's file list");
            foreach($handle as $row)
            {
                list($iso,$name,$flag) = explode(";",$row);
                $this->_COUNTRIES[]=array($iso,$name,$this->flags_dir.$flag);
            }
            return true;
        } else {
            return false;
        }
    }

    private function getCountryList() {
        return Mage::getBaseDir('media').'/mtonestepcheckout/countries.txt';
    }

    private function getFlagsDir() {
        return Mage::getBaseUrl('media').'mtonestepcheckout/images/';
    }
}
