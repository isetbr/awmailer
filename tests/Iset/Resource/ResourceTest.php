<?php

namespace Iset\Resource;

class ResourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testAbstractResourceConstructFail()
    {
        $resource = new Resource();
    }

    public function testAbstractResourceConstructSuccess()
    {
        $resource = new Resource('foo');
        $this->assertInstanceOf('Iset\Resource\AbstractResource',$resource);

        return $resource;
    }

    /**
     * @depends testAbstractResourceConstructSuccess
     */
    public function testRetrieveResourceName($resource)
    {
        $this->assertEquals('foo',$resource->getResourceName());
    }
}
