<?php

namespace Iset\Model;

use Iset\Api\Resource\IpAddress;

class IpAddressTableTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeFail()
    {
        $gateway = new IpAddressTable();
    }

    public function testInitialize()
    {
        $app = \App::configure();
        $gateway = new IpAddressTable($app);
        $this->assertInstanceOf('Iset\Model\IpAddressTable',$gateway);

        return $gateway;
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewIpAddress($gateway)
    {
        $ipaddress = new IpAddress();
        $ipaddress->ipaddress = '127.0.1.1';
        $this->assertInstanceOf('Iset\Api\Resource\IpAddress',$gateway->saveIpAddress($ipaddress));

        return $ipaddress;
    }

    /**
     * @depends testInitialize
     */
    public function testSaveNewInvalidIpAddress($gateway)
    {
        $ipaddress = new IpAddress();
        $ipaddress->ipaddress = null;
        $result = $gateway->saveIpAddress($ipaddress);
        $this->assertArrayHasKey('error',$result);
        $this->assertArrayHasKey('details',$result);
        $this->assertEquals('Invalid IP address, see details for more information',$result['error']);
        $this->assertEquals('Invalid ip address',$result['details']);
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewIpAddress
     */
    public function testSaveExistentIpAddress()
    {
        $gateway = func_get_arg(0);
        $ipaddress = func_get_arg(1);
        $result = $gateway->saveIpAddress($ipaddress);
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('IP Address already allowed',$result['error']);
    }

    /**
     * @depends testInitialize
     */
    public function testGetAllIpAddresses($gateway)
    {
        $this->assertInternalType('array',$gateway->fetchAll());
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewIpAddress
     */
    public function testGetExistentIpAddress()
    {
        $gateway = func_get_arg(0);
        $ipaddress = func_get_arg(1);
        $this->assertInstanceOf('Iset\Api\Resource\IpAddress',$gateway->getIpAddress($ipaddress->ipaddress));
    }

    /**
     * @depends testInitialize
     */
    public function testGetNonexistentIpAddress($gateway)
    {
        $this->assertFalse($gateway->getIpAddress('255.255.255.0'));
    }

    /**
     * @depends testInitialize
     * @depends testSaveNewIpAddress
     */
    public function testRemoveIpAddress()
    {
        $gateway = func_get_arg(0);
        $ipaddress = func_get_arg(1);
        $this->assertTrue($gateway->deleteIpAddress($ipaddress));

        return $ipaddress;
    }

    /**
     * @depends testInitialize
     * @depends testRemoveIpAddress
     */
    public function testRemoveNonexistentIpAddress()
    {
        $gateway = func_get_arg(0);
        $ipaddress = func_get_arg(1);
        $this->assertFalse($gateway->deleteIpAddress($ipaddress));
    }
}
