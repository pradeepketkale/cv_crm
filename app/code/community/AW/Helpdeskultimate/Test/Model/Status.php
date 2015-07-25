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


class AW_Helpdeskultimate_Test_Model_Status extends EcomDev_PHPUnit_Test_Case
{

    public function setUp()
    {

        parent::setUp();
        $this->status = Mage::getModel('helpdeskultimate/status');
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Departments
     * @loadFixture Messages
     * @loadFixture Statuses
     * @dataProvider dataProvider
     * @loadExpectation
     *
     */
    public function testGetOptionArrayFor($storeId, $uid)
    {

        $this->assertEquals(
            array_values(explode(",", $this->expected($uid)->getStatuses())),
            array_values($this->status->getOptionsArrayFor($storeId))
        );
    }

    /**
     * @test
     * @loadFixture Statuses
     *
     */
    public function testGetAllOptions()
    {
        /* Simple coverage */
        $options = $this->status->getAllOptions();

        $this->assertEquals(
            6, count($options)
        );
    }

    /**
     * @test
     * @dataProvider providerForTestGetOption
     * @loadFixture Statuses
     */
    public function testGetOption($optionId, $expected)
    {

        $label = $this->status->getOption($optionId);

        $this->assertEquals(
            $label,
            $expected
        );
    }

    public function providerForTestGetOption()
    {

        return array(array(14, 'german'), array(1, 'Open'), array(9999, '')
        );
    }

    /**
     * @test
     * @dataProvider providerForTestGetOption
     * @loadFixture Statuses
     */
    public function testGetStatusLabel($optionId, $expected)
    {

        $label = $this->status->getStatusLabel($optionId);

        $this->assertEquals(
            $label,
            $expected
        );
    }

    /**
     * @test
     * @loadFixture Statuses
     * @dataProvider dpForIsAllowToSet
     */
    public function testIsAllowToSet($storeId, $statusId, $expected)
    {


        $this->assertEquals(
            $expected,
            $this->status->isAllowToSet($storeId, $statusId)
        );
    }

    public function dpForIsAllowToSet($storeId, $statusId, $expected)
    {

        return array(
            array(0, 1, true),
            array(2, 13, false)
        );
    }

}

?>