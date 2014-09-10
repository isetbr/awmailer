<?php

namespace Iset\Api\Auth;

use Iset\Api\Auth\Service;

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testInitialize()
    {
        $auth = new Service(\App::configure());
        $this->assertInstanceOf('Iset\Api\Auth\Service',$auth);
        return $auth;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeFail()
    {
        $auth = new Service();
    }

    public function testAllowService()
    {
        $service = new \Iset\Api\Resource\Service(new \Iset\Model\ServiceTable(\App::configure()));
        $service->name = 'Test Service';
        $service->key = 'test';
        $this->assertTrue($service->save());
        return $service;
    }

    /**
     * @depends testInitialize
     * @depends testAllowService
     */
    public function testAuth()
    {
        $auth = func_get_arg(0);
        $service = func_get_arg(1);

        $this->assertInstanceOf('Iset\Api\Resource\Service',$auth->validate($service->key,$service->getToken()));
        $service->delete();
    }

    /**
     * @depends testInitialize
     * @depends testAllowService
     */
    public function testAuthFail()
    {
        $auth = func_get_arg(0);
        $service = func_get_arg(1);

        $this->assertFalse($auth->validate($service->key,$service->getToken()));
    }
}