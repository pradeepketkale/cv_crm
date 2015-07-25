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


class AW_Helpdeskultimate_Test_Model_Ticket extends EcomDev_PHPUnit_Test_Case
{

    public $ticket;
    public $abuse;

    public function setUp()
    {

        parent::setUp();
        $this->ticket = Mage::getModel('helpdeskultimate/ticket');
        $this->ticketFlat = Mage::getModel('helpdeskultimate/ticket_flat');
        $this->message = Mage::getModel('helpdeskultimate/message');


        $this->abuse = AW_Helpdeskultimate_Model_Ticket::ABUSE_IDS;
    }

    /**
     * @test
     */
    public function testGenerateUid()
    {
        for ($i = 0; $i < 3; $i++) {
            $uid = $this->ticket->generateUid();
            if (!preg_match("#\w{3}-\d{5}#is", $uid)) {
                $this->fail("Generate function works incorrecly. It should return ticket id with pattern \w{3}-\d{5} current uid id {$uid}");
            }
            if (stripos($this->abuse, substr($uid, 0, 3)) !== false) {
                $this->fail("Sequence of letters should not contain reserved words {$this->abuse}. Current uid is {$uid}");
            }
        }
    }

    /**
     *
     * @test
     * @loadFixture
     * @loadExpectation
     * @dataProvider dataProvider
     *
     */
    public function testGetStatusText($statusId, $storeId, $uid)
    {

        $this->ticket->setStatus($statusId);
        if ($storeId) {
            Mage::app()->getRequest()->setParam('store', $storeId);
        }
        $this->assertEquals(strtolower($this->ticket->getStatusText()), $this->expected($uid)->getLabel());
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Messages
     * @dataProvider dataProvider
     */
    public function testGetMessagesCount($modelId, $uid)
    {

        $this->ticket->setId($modelId);
        $this->assertEquals($this->ticket->getMessagesCount(), $this->expected($uid)->getCount());
    }

    /**
     *
     * @test
     * @dataProvider dataProvider
     * @loadExpectation
     *
     */
    public function testIsReadOnly($userId, $lockedBy, $bool, $uid)
    {

        $this->ticket->setLockedBy($userId);
        $user = new Varien_Object(array('id' => $lockedBy));
        Mage::getSingleton('admin/session')->setUser($user);
        $this->assertEquals($this->ticket->isReadOnly(), (bool)$this->expected($uid)->getResult());
    }

    /**
     * @test
     */
    public function testGetLockedUser()
    {

        $this->ticket->setLockedBy(1);
        $this->ticket->getLockedUser();
    }

    /**
     * @test
     * @loadFixture Tickets
     * @dataProvider dataProvider
     * @loadExpectation
     *
     */
    public function testBeforeSave($ticketId, $content, $uid)
    {

        $ticket = Mage::getModel('helpdeskultimate/ticket')->load(1);
        $ticket->setContent($content);

        $tuid = $ticket->getUid();
        $ticket->unsUid();

        $ticket->unsCreatedTime();

        $createdBy = $ticket->getCreatedBy();
        $ticket->unsCreatedBy();

        $contentType = $ticket->getContentType();
        $ticket->unsContentType();

        $isVirtual = $ticket->getIsVirtual();
        $ticket->unsCustomerId();
        $ticket->save();


        $ticket = Mage::getModel('helpdeskultimate/ticket')->load(1);

        $this->assertEquals(
            $this->expected($uid)->getContent(),
            $ticket->getContent(),
            'Replace pattern on ticket content is incorrect. Please, check syntax: preg_replace( ((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?i, http\\3://\\5\\6\\8\\9", $this->getContent()'
        );

        if ($tuid == $ticket->getUid()) {
            $this->fail('New ticket uid should be generated');
        }
        if ($ticket->getCreatedBy() !== 'customer') {
            $this->fail('Created By value should be set to customer by default');
        }
        if ($ticket->getContentType() !== AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE) {
            $this->fail('Content type of the ticket should be set to ' . AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE . ' by default');
        }
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadExpectation testBeforeSave
     * @dataProvider dataProvider
     */
    public function testAfterLoad($ticketId, $content, $uid)
    {

        $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
        $ticket->setContent($content);
        $ticket->save();
        $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);

        $this->assertEquals(
            $ticket->getDepartmentId(),
            $ticket->getDepartmentSavedId()
        );

        $this->assertEquals(
            $ticket->getStatus(),
            $ticket->getStatusId()
        );

        $this->assertEquals(
            $ticket->getPriority(),
            $ticket->getSavedPriority()
        );

        $this->assertEquals(
            $this->expected($uid)->getContent(),
            $ticket->getContent(),
            'Replace pattern on ticket content is incorrect. Please, check syntax: preg_replace( ((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?i, http\\3://\\5\\6\\8\\9", $this->getContent()'
        );
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadExpectation testBeforeSave
     */
    public function testAfterSave()
    { // Core logger integration
        $ticket = $this->ticket->load(1);
        $ticket->setStatus('status', 'pending');
        $ticket->setOrigData('status', 'waiting for customer');

        $ticketId = $ticket->getUid($ticket->setUid($this->ticket->generateUid()));

        $ticket->save();

        $coreCollection = Mage::getModel('awcore/logger')->getCollection();
        $coreCollection->getSelect()->order('id DESC');
        $item = $coreCollection->getFirstItem();

        preg_match("#" . $ticketId . "#is", $item->getTitle(), $matches);

        $this->assertEquals(
            $ticketId,
            $matches[1],
            'Ticket with changed status is not written into aw_core_logger table'
        );
    }

    /**
     * @test
     * @dataProvider uidProvider
     */
    public function testLoadByUid($id)
    {

        $ticket = $this->ticket->load($id);
        $dti = $this->ticket->loadByUid($id);

        $this->assertEquals(
            $ticket->getUid(),
            $dti->getUid(),
            'Fucntion loadByUid works incorrectly'
        );
    }

    /**
     * @test
     *
     */
    public function testFilename()
    {

        /* Unabled to test $model->_data['filename'] overwrite $model->getFilename method */
    }

    /**
     * @test
     * @dataProvider uidProvider
     * @loadFixture Tickets
     *
     */
    public function testGetFolderName($id)
    {
        $ticket = $this->ticket->load($id);
        $folderName = $ticket->getFolderName();
        $path = Mage::getBaseDir('media') . DS . 'helpdeskultimate' . DS . 'ticket-' . $ticket->getId() . DS;
        if (!file_exists($path)) {
            $this->fail('Ticket folter should be created automatically if not exists');
        }
    }

    public function uidProvider()
    {
        return array(array(1, 1), array(2, 2));
    }

    /**
     * @test
     * @dataProvider uidProvider
     * @loadFixture Tickets
     * @loadFixture Messages
     *
     */
    public function testBeforeDelete($id, $count)
    {

        $this->ticket->load($id)->delete();
        $realCount = $this->message->getCollection()->count();

        $this->assertEquals(
            $realCount,
            $count,
            'Not all associated with ticket messages are deleted. Please, check the syntax'
        );
    }

    /**
     * @test
     * @dataProvider validateProvider
     * @loadFixture Tickets
     * @loadFixture Messages
     *
     */
    public function testValidate($id, $bool)
    {

        $this->ticket->load($id)->validate();

        $this->assertEquals(
            $this->ticket->load($id)->validate(),
            $bool
        );
    }

    public function validateProvider()
    {
        return array(array(1, true), array(999, array('Please specify title', 'Content can\'t be empty')));
    }

    /**
     * @test
     * @dataProvider externalLoadProvider
     * @loadFixture Tickets
     * @loadFixture Messages
     */
    public function testGetCustomerHtml($id, $storeId)
    {
        /* This function also affects getAccessUrl, getAccessKey functions
         * After URL is generated we parse and get unique keys and sent them into 
         * loadExternal function to see if this chain works correctly
         */

        $ticket = $this->ticket->load($id);

        $ticketUid = $ticket->getUid();
        $ticket->setStoreId($storeId);
        $ticket->unsCustomerId();

        $encodedUrl = $ticket->getAccessUrl();

        preg_match("#key/(.+?)/#is", $encodedUrl, $matches);
        $key = $matches[1];
        preg_match("#uid/(.+?)/#is", $encodedUrl, $matches);
        $uid = $matches[1];


        $ticketExt = $ticket->loadExternal($uid, $key);

        $this->assertEquals(
            $ticketUid,
            $ticketExt->getUid(),
            'External ticket cannot be loaded'
        );

        /* Just to cover model code */
        $ticket->getCustomerUrlHtml();
    }

    public function externalLoadProvider()
    {
        return array(array(1, 1), array(2, 2));
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Messages
     * @loadFixture Departments
     * @dataProvider depsProvider
     */
    public function testSetDepartmentId($id, $result, $flag)
    {

        $ticket = $this->ticket->load($id);
        $ticket->setDepartmentId($id, $flag);

        $this->assertEquals(
            $ticket->getDepartment()->getId(),
            $result
        );
    }

    public function depsProvider()
    {

        return array(
            array(2, 1, true),
            array(2, 2, false)
        );
    }

    /**
     * @test
     * @loadFixture Tickets
     * @loadFixture Messages
     * @loadFixture Departments
     * @dataProvider depsProvider
     */
    public function testGetInitiator($id, $result, $flag)
    {

        $ticket = $this->ticket->load($id);

        $this->assertEquals(
            'Mage_Customer_Model_Customer',
            get_class($ticket->getInitiator())
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

        $ticket = $this->ticket->load($id);
        $ticket->setContent($content);

        $this->assertEquals(
            $this->expected($uid)->getContent(),
            $ticket->getParsedContent(),
            'Viriables are not parsed correctly. Plse, check'
        );
    }

    /**
     * @test
     * @dataProvider externalLoadProvider
     */
    public function testGetOrder($id, $store)
    {

        /* As we have no orders, we simply call this method */
        Mage::app()->getStore(0)->setConfig(AW_Helpdeskultimate_Model_Ticket::XML_ORDERS_ENABLED, 1);
        $this->assertEmpty($this->ticket->getOrder());
    }

    /**
     * @test
     * @dataProvider externalLoadProvider
     */
    public function testSetCustomer($id, $store)
    {

        $customer = new Varien_Object();

        $customer->setName('Andrej');
        $customer->setEmail('helpdeskult@yandex.ru');
        $customer->setCustomerId($id);

        $ticket = $this->ticket->load(1);
        $ticket->setCustomer($customer);

        $this->assertEquals(
            'Andrej',
            $ticket->getCustomer()->getName()
        );
        $this->assertEquals(
            'helpdeskult@yandex.ru',
            $ticket->getCustomer()->getEmail()
        );
        $this->assertEquals(
            $id,
            $ticket->getCustomer()->getCustomerId()
        );

        return $ticket;
    }

    /**
     * @test
     * @dataProvider externalLoadProvider
     */
    public function testSetCustomerId($id, $store)
    {

        $ticket = $this->ticket->load($id);
        $ticket->getCustomer();
    }

    /**
     * @test
     * @dataProvider getFileUrl
     */
    public function testGetFileUrl($id, $store)
    {

        Mage::app()->getStore()->setCode($store);
        $ticket = $this->ticket->load($id);

        if ($store == 'admin') {

            Mage::getSingleton('core/session', array('name' => 'adminhtml'));
            $user = Mage::getModel('admin/user')->loadByUsername('master');
            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }

            $session = Mage::getSingleton('admin/session');
            $session->setIsFirstVisit(true);
            $session->setUser($user);
            $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
        }

        $ticket->getFileUrl();
        $ticket->getUrl();
    }

    public function getFileUrl()
    {

        return array(
            array(1, 'admin'),
            array(2, 'customer')
        );
    }

    /**
     * @test
     * @dataProvider getFileUrl
     */
    public function testGetOldDepartment($id, $store)
    {
        /* Simple coverage */

        $ticket = $this->ticket->load($id);
        $ticket->getOldDepartment();
    }

}

?>