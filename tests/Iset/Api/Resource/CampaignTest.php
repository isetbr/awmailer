<?php

namespace Iset\Api\Resource;

class CampaignTest extends \PHPUnit_Framework_TestCase
{

    protected $_app = null;
    protected $_gateway = null;

    public function setUp()
    {
        $this->_app = \App::configure();
        $this->_gateway = new \Iset\Model\CampaignTable($this->_app);
    }

    public function testInitialize()
    {
        $campaign = new Campaign();
        $this->assertInstanceOf('Iset\Api\Resource\Campaign',$campaign);

        return $campaign;
    }

    public function testInitializeWithTableGateway()
    {
        $campaign = new Campaign($this->_gateway);
        $this->assertInstanceOf('Iset\Api\Resource\Campaign',$campaign);

        return $campaign;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeWithTableGatewayFail()
    {
        $campaign = new Campaign(new \stdClass());
    }

    /**
     * @depends testInitialize
     */
    public function testValidate($campaign)
    {
        $campaign->service = 1;
        $campaign->subject = 'Test Campaign';
        $campaign->body = 'Test Body';
        $campaign->headers = array('Header-Test'=>'test-value');
        $campaign->external = 123;

        $this->assertTrue($campaign->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithoutService($campaign)
    {
        $campaign->service = null;
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('A Service must be specified',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithInvalidService($campaign)
    {
        $campaign->service = 'test';
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('A service must be an instance of a Service object or the id of service', $result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidService($campaign)
    {
        $campaign->service = 1;
        $this->assertTrue($campaign->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithoutSubject($campaign)
    {
        $campaign->subject = null;
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('You must set a subject',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidSubject($campaign)
    {
        $campaign->subject = 'Test Campaign';
        $this->assertTrue($campaign->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithoutBody($campaign)
    {
        $campaign->body = null;
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('You must set a body',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidBody($campaign)
    {
        $campaign->body = 'Test body';
        $this->assertTrue($campaign->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithoutHeaders($campaign)
    {
        $campaign->headers = null;
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('Headers must be an array',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithInvalidHeaders($campaign)
    {
        $campaign->headers = 'test';
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('Headers must be an array',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidHeaders($campaign)
    {
        $campaign->headers = array();
        $this->assertTrue($campaign->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithoutExternal($campaign)
    {
        $campaign->external = null;
        $result = $campaign->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('Invalid external identification',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidExternal($campaign)
    {
        $campaign->external = 123;
        $this->assertTrue($campaign->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testExchangeArray($campaign)
    {
        $data = array(
            'idcampaign'=>1,
            'idservice'=>1,
            'key'=>'testkey',
            'total'=>0,
            'sent'=>0,
            'fail'=>0,
            'progress'=>0,
            'status'=>0,
            'subject'=>'Test Campaign',
            'body'=>'Test body',
            'headers'=>array(),
            'user_vars'=>0,
            'user_headers'=>0,
            'external'=>123,
            'additional_info'=>'test'
        );

        $campaign->exchangeArray($data);

        $this->assertEquals(1,$campaign->id);
        $this->assertEquals(1,$campaign->service);
        $this->assertEquals('testkey',$campaign->getCampaignKey());
        $this->assertEquals(0,$campaign->total);
        $this->assertEquals(0,$campaign->sent);
        $this->assertEquals(0,$campaign->fail);
        $this->assertEquals(0,$campaign->progress);
        $this->assertEquals(0,$campaign->status);
        $this->assertEquals('Test Campaign',$campaign->subject);
        $this->assertEquals('Test body',$campaign->body);
        $this->assertInternalType('array',$campaign->headers);
        $this->assertEquals(0,$campaign->user_vars);
        $this->assertEquals(0,$campaign->user_headers);
        $this->assertEquals(123,$campaign->external);
        $this->assertEquals('test',$campaign->additional_info);
    }

    /**
     * @depends testInitialize
     */
    public function testAsArray($campaign)
    {
        $this->assertInternalType('array',$campaign->asArray());
    }

    /**
     * @depends testInitializeWithTableGateway
     */
    public function testSave($campaign)
    {
        $campaign->service = 1;
        $campaign->subject = 'Test Campaign';
        $campaign->body = 'Test body';
        $campaign->external = 123;

        $this->assertTrue($campaign->save());

        return $campaign;
    }

    public function testSaveFail()
    {
        $campaign = new Campaign($this->_gateway);
        $result = $campaign->save();
        $this->assertArrayHasKey('error',$result);
    }

    /**
     * @depends testSave
     */
    public function testDelete($campaign)
    {
        $this->assertTrue($campaign->delete());

        return $campaign;
    }

    /**
     * @depends testDelete
     */
    public function testDeleteFail($campaign)
    {
        $this->assertFalse($campaign->delete());
    }
}
