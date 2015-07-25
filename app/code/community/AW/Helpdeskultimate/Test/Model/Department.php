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
 * @package    AW_Helpdeskultimate
 * @version    2.9.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Helpdeskultimate_Test_Model_Department extends EcomDev_PHPUnit_Test_Case {

    public $department = null;

    public function setUp() {
        parent::setUp();
        $this->department = Mage::getModel('helpdeskultimate/department');
    }

    /**
     * @test
     * @loadFixture aw_hdu_department
     * @dataProvider provider__getAddress
     *
     */
    public function getAddress($data) {

        if ($data['contact']) {
            Mage::app()->getStore(0)->setConfig('trans_email/ident_' . $data['contact'] . '/email', $data['email']);
        }
        $department = Mage::getModel('helpdeskultimate/department')->load($data['id']);

        $this->assertEquals($data['email'], $department->getAddress());
    }

    public function provider__getAddress() {

        return array(
            array(array('id' => 1, 'contact' => null, 'email' => 'helpdeskult@yandex.ru')),
            array(array('id' => 2, 'contact' => 'general', 'email' => 'testgetaddress@example.com')),
        );
    }

    /**
     * @test
     * @loadFixture aw_hdu_department
     * @dataProvider provider__getSenderAddress
     */
    public function getSenderAddress($data) {

        Mage::app()->getStore(0)->setConfig('trans_email/ident_' . $data['contact'] . '/email', $data['email']);
        $department = Mage::getModel('helpdeskultimate/department')->load($data['id']);
        $this->assertEquals($data['email'], $department->getSenderAddress());
    }

    public function provider__getSenderAddress() {

        return array(
            array(array('id' => 1, 'contact' => 'general', 'email' => 'helpdeskult@yandex.ru'))
        );
    }

    /**
     * @test
     * @loadFixture aw_hdu_department
     * @dataProvider provider__getSenderAddress
     */
    public function loadByContact($data) {

        $department = Mage::getModel('helpdeskultimate/department')->load(1);
        $contact = Mage::getModel('helpdeskultimate/department')->loadByContact($department->getEmail());
        $this->assertEquals($contact->getId(), $department->getId());
    }

    /**
     * @test
     * @dataProvider provider__loadStats
     * @loadFixture aw_hdu_department
     *
     * This function seems to be deprecated, so simple coverage would be enough
     *  
     */
    public function loadStats($data) {
        Mage::getModel('helpdeskultimate/department')->load(1)->loadStats(1);
    }

    public function provider__loadStats() {

        return array(
            array(1),
            array(3)
        );
    }

    /**
     * @test
     * @dataProvider provider__loadStats
     * @loadFixture aw_hdu_department     
     *
     */
    public function usesAllGateways($data) {
        $exp = $data == 1 ? 1 : 0;
        $use = (int) $this->department->load($data)->usesAllGateways();
        $this->assertEquals($use, $exp);
    }

    /**
     * 
     * @test
     * @dataProvider provider__loadStats
     * @loadFixture aw_hdu_department
     *
     */
    public function getDepartmentName($data) {

        $expName = $data == 1 ? 'General Department' : $this->getDepartment()->load($data)->getName();
        $depName = $this->getDepartment()->load($data)->getName();
        $this->assertEquals($depName, $expName);
    }

    /**
     *
     * @test
     * @dataProvider provider__loadStats
     * @loadFixture aw_hdu_department
     * @loadFixture aw_hdu_gateway
     *
     */
    public function getMainGateway($data) {
        $gateway = $this->getDepartment($data)->getMainGateway();
        // The first gateway should always be returned
        $this->assertEquals($gateway->getId(), 1, 'Incorrect main gateway selection in function getMainGateway');
    }

    private function getDepartment($data = false) {
        $department = Mage::getModel('helpdeskultimate/department');
        if ($data) {
            $department->load($data);
        }
        return $department;
    }

    /**
     * @test
     * @loadFixture aw_hdu_department
     * @dataProvider provider__loadStats
     * 
     */
    public function getMainGatewayEmpty($data) {
        $result = (int) $this->getDepartment($data)->getMainGateway();
        $this->assertEquals($result, 0);
    }

    /**
     * @test
     * @dataProvider provider__beforeSave
     */
    public function _beforeSave($data) {

        /*
         *
         * On _beforeSave
         * 1. Email converted to lowercase
         * 2. Department (if array) implodes to string
         * 3. Contact is the same as email
         *
         */
        $gateways = array(1, 2, 3, 4, 5);
        $email = 'UPPERCASEEMAIL@example.com';
        $department = $this->getDepartment();
        $department->setEmail($email);
        $department->setGateways($gateways);
        $department->save();
        $dep = $this->getDepartment($data);

        $this->assertEquals(implode(',', $gateways), implode(',', $dep->getGateways()));
        $this->assertEquals($dep->getEmail(), strtolower($email));
    }

    public function provider__beforeSave() {

        return array(array(1));
    }

    /**
     * 
     * @test
     * @dataProvider provider__beforeSave
     * @loadFixture aw_hdu_department
     * @loadFixture aw_hdu_gateway
     * 
     */
    public function getDepEmails($data) {
        $emails = $this->getDepartment($data)->getDepEmails();
        $expected = 'helpdeskult@yandex.ru,,,';
        $this->assertEquals($expected, implode(',', $emails));
    }

    /**
     *
     * @test
     * @dataProvider provider__beforeSave
     * @loadFixture aw_hdu_department
     * @loadFixture aw_hdu_gateway
     *
     */
    public function loadByPrimaryStoreId($data) {
        $d = $this->getDepartment()->loadByPrimaryStoreId($data);
        /* Load first available dep by primary_store_id. And this is the fist one */
        $this->assertEquals($d->getId(), $data);
    }

}

?>