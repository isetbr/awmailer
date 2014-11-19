<?php

namespace Iset\Provider;

class ZendCacheServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testInitialize()
    {
        $service = new ZendCacheServiceProvider();
        $this->assertInstanceOf('Iset\Provider\ZendCacheServiceProvider', $service);

        return $service;
    }

    /**
     * @depends testInitialize
     */
    public function testServiceProviderImplementation($service)
    {
        $this->assertInstanceOf('Silex\ServiceProviderInterface', $service);
    }

    /**
     * @depends testInitialize
     * @expectedException PHPUnit_Framework_Error
     */
    public function testRegisterFail($service)
    {
        $service->register();
    }

    /**
     * @depends testInitialize
     */
    public function testRegisterSuccess($service)
    {
        $app = new \Silex\Application();
        $service->register($app);
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Filesystem',$app['cache']);

        return $app;
    }
}
