<?php

namespace Iset\Api\Auth;

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $_app = null;
    protected $_gateway = null;

    public function setUp()
    {
        $this->_app = \App::configure();
        $this->_gateway = new \Iset\Model\ServiceTable($this->_app);
    }

    public function testInitialize()
    {
        $auth = new Service($this->_app);
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
        $service = new \Iset\Api\Resource\Service($this->_gateway);
        $service->name = 'Test Service';
        $service->key = 'test-'.rand(1111,9999);
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

        # Removing service
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
