<?php

namespace Iset\Api\Auth;

class IpAddressTest extends \PHPUnit_Framework_TestCase
{

    protected $_app = null;
    protected $_gateway = null;

    public function setUp()
    {
        $this->_app = \App::configure();
        $this->_gateway = new \Iset\Model\IpAddressTable($this->_app);
    }

    public function testInitialize()
    {
        $auth = new IpAddress($this->_app);
        $this->assertInstanceOf('Iset\Api\Auth\IpAddress',$auth);

        return $auth;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeFail()
    {
        $auth = new IpAddress();
    }

    public function testAllowIpAddress()
    {
        $ipaddress = new \Iset\Api\Resource\IpAddress($this->_gateway);
        $ipaddress->ipaddress = '127.0.1.1';
        $this->assertTrue($ipaddress->save());

        return $ipaddress;
    }

    /**
     * @depends testInitialize
     * @depends testAllowIpAddress
     */
    public function testAuth()
    {
        $auth = func_get_arg(0);
        $ipaddress = func_get_arg(1);

        $this->assertInstanceOf('Iset\Api\Resource\IpAddress',$auth->validate($ipaddress->ipaddress));
        $ipaddress->delete();
    }

    /**
     * @depends testInitialize
     * @depends testAllowIpAddress
     */
    public function testAuthFail()
    {
        $auth = func_get_arg(0);
        $ipaddress = func_get_arg(1);

        $this->assertFalse($auth->validate($ipaddress->ipaddress));
    }
}
