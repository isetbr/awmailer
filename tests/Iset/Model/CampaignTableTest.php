<?php

namespace Iset\Model;

use Iset\Api\Resource\Campaign;
use Iset\Model\CampaignTable;

class CampaignTableTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeFail()
    {
        $gateway = new CampaignTable();
    }

    public function testInitialize()
    {
        $app = \App::configure();
        $gateway = new CampaignTable($app);
        $this->assertInstanceOf('Iset\Model\CampaignTable',$gateway);
        return $gateway;
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewCampaign($gateway)
    {
        # Creating campaign
        $campaign = new Campaign();
        $campaign->service = 1;
        $campaign->subject = 'Test Campaign';
        $campaign->body = 'Test Body';
        $campaign->headers = array('Header-Test'=>'test-value');
        $campaign->external = 123;

        # Saving campaign
        $this->assertInstanceOf('Iset\Api\Resource\Campaign',$gateway->saveCampaign($campaign));

        return $campaign;
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewInvalidCampaign($gateway)
    {
        $campaign = new Campaign();
        $result = $gateway->saveCampaign($campaign);
        $this->assertArrayHasKey('error',$result);
        $this->assertArrayHasKey('details',$result);
    }

    /**
     * @depends testInitialize
     */
    public function testGetAllCampaigns($gateway)
    {
        $this->assertInternalType('array',$gateway->fetchAll());
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewCampaign
     */
    public function testGetExistentCampaign()
    {
        $gateway = func_get_arg(0);
        $campaign = func_get_arg(1);
        $this->assertInstanceOf('Iset\Api\Resource\Campaign',$gateway->getCampaign($campaign->id));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewCampaign
     */
    public function testGetExistentCampaignWithServiceId()
    {
        $gateway = func_get_arg(0);
        $campaign = func_get_arg(1);
        $this->assertInstanceOf('Iset\Api\Resource\Campaign',$gateway->getCampaign($campaign->id,$campaign->service));
    }

    /** 
     * @depends testInitialize
     */
    public function testGetNonexistentCampaign($gateway)
    {
        $this->assertFalse($gateway->getCampaign(123));
    }

    /**
     * @depends testInitialize
     */
    public function testGetNonexistentCampaignWithServiceId($gateway)
    {
        $this->assertFalse($gateway->getCampaign(123,123));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewCampaign
     */
    public function testRemoveCampaign()
    {
        $gateway = func_get_arg(0);
        $campaign = func_get_arg(1);
        $this->assertTrue($gateway->deleteCampaign($campaign));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewCampaign
     */
    public function testRemoveNonexistentCampaign()
    {
        $gateway = func_get_arg(0);
        $campaign = func_get_arg(1);
        $this->assertFalse($gateway->deleteCampaign($campaign));
    }
}