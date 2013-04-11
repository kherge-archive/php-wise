<?php

namespace Herrera\Wise\Tests\Resource;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Resource\ResourceCollector;
use Symfony\Component\Config\Resource\FileResource;

class ResourceCollectorTest extends TestCase
{
    /**
     * @var ResourceCollector
     */
    private $collector;

    /**
     * @var FileResource
     */
    private $resource;

    public function testAddResource()
    {
        $this->collector->addResource($this->resource);

        $this->assertSame(
            array($this->resource),
            $this->getPropertyValue($this->collector, 'resources')
        );
    }

    /**
     * @depends testAddResource
     */
    public function testClearResources()
    {
        $this->collector->addResource($this->resource);
        $this->collector->clearResources();

        $this->assertSame(
            array(),
            $this->getPropertyValue($this->collector, 'resources')
        );
    }

    /**
     * @depends testAddResource
     */
    public function testGetResources()
    {
        $this->collector->addResource($this->resource);

        $this->assertSame(
            array($this->resource),
            $this->collector->getResources()
        );
    }

    protected function setUp()
    {
        $this->collector = new ResourceCollector();
        $this->resource = new FileResource(__FILE__);
    }
}
