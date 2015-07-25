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


class AW_Helpdeskultimate_Test_Model_Messages extends EcomDev_PHPUnit_Test_Case
{

    public function setUp()
    {

        parent::setUp();
        $this->message = Mage::getModel('helpdeskultimate/message');
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Departments
     * @loadFixture Messages
     * @loadFixture Statuses
     * @dataProvider DP__TestValidate
     *
     */
    public function testValidate($messageId, $content, $expected)
    {

        $message = $this->message->load($messageId);
        $message->setContent($content);

        $this->assertEquals(
            $expected,
            $message->validate()
        );
    }

    public function DP__TestValidate()
    {
        return array(
            array(1, '', array('Content can\'t be empty')),
            array(1, 'some content', true)
        );
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Departments
     * @loadFixture Messages
     * @loadFixture Statuses
     * @dataProvider DP__TestGetTicket
     *
     */
    public function testGetTicket($messageId, $uid)
    {

        $this->message->load($messageId);
        $relatedTicket = $this->message->getTicket();

        if (is_object($relatedTicket)) {
            $this->assertEquals(
                $uid,
                $relatedTicket->getUid(),
                'Ticket related to message is incorrect'
            );
        } else {
            $this->fail('Related value should be an object');
        }
    }

    public function DP__TestGetTicket()
    {
        return array(
            array(1, "EBY-20287"), array(3, "EBY-20287"), array(2, "VUF-43395")
        );
    }

    /*     * ******************************************************************* */

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Departments
     * @loadFixture Messages
     * @loadFixture Statuses
     * @dataProvider DP__TestGetFolderName
     */
    public function testGetFolderName($messageId)
    {

        $path = $this->message->load($messageId)->getFolderName();

        if (!is_string($path)) {
            $this->fail("Folder name should be a string");
        }
    }

    public function DP__TestGetFolderName()
    {

        return array(
            array(1)
        );
    }

    /*     * ******************************************************************* */

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Departments
     * @loadFixture Messages
     * @loadFixture Statuses
     * @dataProvider DP__TestGetFolderName
     */
    public function testGetFilename($messageId)
    {

        $message = $this->message->load($messageId);
        $message->setFilename('test.jpg');
        $file = $message->getFilename();

        if (!is_string($file)) {
            $this->fail("Incorrect File path {$file}");
        }
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Departments
     * @loadFixture Messages
     * @loadFixture Statuses
     * @dataProvider dataProvider
     * @loadExpectation testBeforeSave
     */
    public function testAfterLoad($messageId, $content, $uid)
    {

        /* Before save method has identical functionality */
        $message = Mage::getModel('helpdeskultimate/message')->load($messageId);
        $message->setContent($content);
        $message->save();
        $message = Mage::getModel('helpdeskultimate/message')->load($messageId);

        $this->assertEquals(
            $this->expected($uid)->getContent(),
            $message->getContent(),
            'Replace pattern on ticket content is incorrect. Please, check syntax: preg_replace( ((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?i, http\\3://\\5\\6\\8\\9", $this->getContent()'
        );
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Messages
     * @loadFixture Departments
     * @dataProvider dataProvider
     * @loadExpectation
     *
     */
    public function testGetParsedContent($id, $content, $uid)
    {

        $message = $this->message->load($id);
        $message->setContent($content);

        $this->assertEquals(
            $this->expected($uid)->getContent(),
            $message->getParsedContent(),
            'Viriables are not parsed correctly. Plse, check'
        );
    }

    public function testIsDepartmentReply()
    {
        $this->assertEmpty(
            $this->message->isDepartmentReply()
        );
    }

    public function testGetAdminFileUrl()
    {
        if (!is_string($this->message->getAdminFileUrl())) {
            $this->fail('URL must be a string');
        }
    }

    public function testGetFileUrl()
    {
        if (!is_string($this->message->getFileUrl())) {
            $this->fail('URL must be a string');
        }
    }

}

?>