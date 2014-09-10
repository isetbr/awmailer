<?php

namespace Iset\Api\Resource;

use Iset\Api\Resource\Service;

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testInitialize()
    {
        $service = new Service();
        $this->assertInstanceOf('Iset\Api\Resource\Service',$service);
        return $service;
    }

    public function testInitializeWithTableGateway()
    {
        $service = new Service(new \Iset\Model\ServiceTable(\App::configure()));
        $this->assertInstanceOf('Iset\Api\Resource\Service',$service);
        return $service;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeWithTableGatewayFail()
    {
        $service = new Service(new \stdClass());
    }

    /**
     * @depends testInitialize
     */
    public function testValidate($service)
    {
        $service->name = 'Foo Service';
        $service->key = 'foo';
        $service->notification_url = 'http://domain.com/callback';
        $this->assertTrue($service->validate());
        return $service;
    }

    /** 
     * @depends testInitialize
     */
    public function testValidateFailWithoutName($service)
    {
        $service->name = null;
        $result = $service->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('A service name must be specified',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailInvalidName($service)
    {
        $service->name = array();
        $result = $service->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('A service name must be an string',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidName($service)
    {
        $service->name = 'Foo Service';
        $this->assertTrue($service->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailWithoutKey($service)
    {
        $service->key = null;
        $result = $service->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('A service key must be specified',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailInvalidKey($service)
    {
        $service->key = array();
        $result = $service->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('A service key must be an string',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidKey($service)
    {
        $service->key = 'foo';
        $this->assertTrue($service->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testValidateFailInvalidNotificationUrl($service)
    {
        $service->notification_url = 'foo.bar';
        $result = $service->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('The notification url is not a valid URI',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testValidateWithValidUrl($service)
    {
        $service->notification_url = 'http://domain.com/callback';
        $this->assertTrue($service->validate());
    }

    /**
     * @depends testInitialize
     */
    public function testExchangeArray($service)
    {
        $data = array(
            'idservice'=>1,
            'name'=>'Foo Service',
            'key'=>'foo',
            'notification_url'=>'http://domain.com/callback'
        );

        $service->exchangeArray($data);
        $this->assertEquals(1,$service->id);
        $this->assertEquals('Foo Service',$service->name);
        $this->assertEquals('foo',$service->key);
        $this->assertEquals('http://domain.com/callback',$service->notification_url);
        return $service;
    }

    /**
     * @depends testInitialize
     */
    public function testAsArray($service)
    {
        $this->assertInternalType('array',$service->asArray());
    }

    /**
     * @depends testInitializeWithTableGateway
     * @depends testValidate
     */
    public function testSave($service)
    {
        $service->name = 'Foo Service';
        $service->key = 'foo';
        $service->notification_url = 'http://domain.com/callback';

        $this->assertTrue($service->save());
        return $service;
    }

    public function testSaveFail()
    {
        $service = new Service(new \Iset\Model\ServiceTable(\App::configure()));
        $result = $service->save();
        $this->assertArrayHasKey('error',$result);
    }

    /**
     * @depends testSave
     */
    public function testDelete($service)
    {
        $this->assertTrue($service->delete());
        return $service;
    }

    /**
     * @depends testDelete
     */
    public function testDeleteFail($service)
    {
        $this->assertFalse($service->delete());
    }
}