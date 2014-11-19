<?php

namespace Iset\Model;

use Iset\Api\Resource\Service;

class ServiceTableTest extends \PHPUnit_Framework_TestCase
{

    const TEST_SERVICE_KEY = 'foo';

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeFail()
    {
        $gateway = new ServiceTable();
    }

    public function testInitialize()
    {
        $app = \App::configure();
        $gateway = new ServiceTable($app);
        $this->assertInstanceOf('Iset\Model\ServiceTable',$gateway);

        return $gateway;
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewService($gateway)
    {
        $service = new Service();
        $service->name = 'Foo Service';
        $service->key  = self::TEST_SERVICE_KEY;
        $service->notification_url = 'http://foo.com/callback';
        $this->assertInstanceOf('Iset\Api\Resource\Service',$gateway->saveService($service));

        return $service;
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewInvalidService($gateway)
    {
        $service = new Service();
        $result = $gateway->saveService($service);
        $this->assertArrayHasKey('error',$result);
        $this->assertArrayHasKey('details',$result);
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewServiceWithExistentKey($gateway)
    {
        $service = new Service();
        $service->name = 'Foo Service';
        $service->key  = self::TEST_SERVICE_KEY;
        $result = $gateway->saveService($service);
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('The service key or token is already in use',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testGetAllServices($gateway)
    {
        $this->assertInternalType('array',$gateway->fetchAll());
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewService
     */
    public function testGetServiceByKey()
    {
        $gateway = func_get_arg(0);
        $service = func_get_arg(1);
        $this->assertInstanceOf('Iset\Api\Resource\Service',$gateway->getService($service->key));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewService
     */
    public function testGetServiceById()
    {
        $gateway = func_get_arg(0);
        $service = func_get_arg(1);
        $this->assertInstanceOf('Iset\Api\Resource\Service',$gateway->getServiceById($service->id));
    }

    /**
     * @depends testInitialize
     */
    public function testGetNonexistentServiceByKey($gateway)
    {
        $this->assertFalse($gateway->getService('bar'));
    }

    /**
     * @depends testInitialize
     */
    public function testGetNonexistentServiceById($gateway)
    {
        $this->assertFalse($gateway->getServiceById(123));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewService
     */
    public function testRemoveExistentService()
    {
        $gateway = func_get_arg(0);
        $service = func_get_arg(1);
        $this->assertTrue($gateway->deleteService($service));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewService
     */
    public function testRemoveNonexistentService()
    {
        $gateway = func_get_arg(0);
        $service = func_get_arg(1);
        $this->assertFalse($gateway->deleteService($service));
    }
}
