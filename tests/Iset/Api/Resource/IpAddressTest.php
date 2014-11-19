<?php

namespace Iset\Api\Resource;

class IpAddressTest extends \PHPUnit_Framework_TestCase
{

    public function testInitialize()
    {
        $ipaddress = new IpAddress();
        $this->assertInstanceOf('Iset\Api\Resource\IpAddress',$ipaddress);

        return $ipaddress;
    }

    public function testInitializeWithTableGateway()
    {
        $ipaddress = new IpAddress(new \Iset\Model\IpAddressTable(\App::configure()));
        $this->assertInstanceOf('Iset\Api\Resource\IpAddress',$ipaddress);

        return $ipaddress;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInitializeWithTableGatewayFail()
    {
        $ipaddress = new IpAddress(new \stdClass());
    }

    /**
     * @depends testInitialize
     */
    public function testValidate($ipaddress)
    {
        $ipaddress->ipaddress = '127.0.1.1';
        $this->assertTrue($ipaddress->validate());

        return $ipaddress;
    }

    /**
     * @depends testInitializeWithTableGateway
     */
    public function testValidateFailInvalidIpAddress($ipaddress)
    {
        $ipaddress->ipaddress = null;
        $result = $ipaddress->validate();
        $this->assertArrayHasKey('error',$result);
        $this->assertEquals('Invalid ip address',$result['error']);

        return $ipaddress;
    }

    /**
     * @depends testInitializeWithTableGateway
     */
    public function testExchangeArray($ipaddress)
    {
        $ipaddress->exchangeArray(array('ipaddress'=>'127.0.1.1'));
        $this->assertEquals('127.0.1.1',$ipaddress->ipaddress);

        return $ipaddress;
    }

    /**
     * @depends testExchangeArray
     */
    public function testAsArray($ipaddress)
    {
        $this->assertInternalType('array',$ipaddress->asArray());
    }

    /**
     * @depends testExchangeArray
     */
    public function testSave($ipaddress)
    {
        $this->assertTrue($ipaddress->save());

        return $ipaddress;
    }

    /**
     * @depends testValidateFailInvalidIpAddress
     */
    public function testSaveFail($ipaddress)
    {
        $result = $ipaddress->save();
        $this->assertArrayHasKey('error',$result);
    }

    /**
     * @depends testSave
     */
    public function testDelete($ipaddress)
    {
        $this->assertTrue($ipaddress->delete());

        return $ipaddress;
    }

    /**
     * @depends testDelete
     */
    public function testDeleteFail($ipaddress)
    {
        $this->assertFalse($ipaddress->delete());
    }
}
