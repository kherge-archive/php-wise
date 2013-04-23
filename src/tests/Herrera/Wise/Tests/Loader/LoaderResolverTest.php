<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\Wise\Loader\LoaderResolver;
use Herrera\Wise\Resource\ResourceCollector;
use Herrera\Wise\Tests\Loader\ExampleFileLoader;
use Herrera\Wise\Wise;
use Herrera\PHPUnit\TestCase;
use Symfony\Component\Config\FileLocator;

class LoaderResolverTest extends TestCase
{
    /**
     * @var ResourceCollector
     */
    private $collector;

    /**
     * @var LoaderResolver
     */
    private $resolver;

    /**
     * @var Wise
     */
    private $wise;

    public function testAddLoader()
    {
        $this->setPropertyValue($this->resolver, 'collector', $this->collector);
        $this->setPropertyValue($this->resolver, 'wise', $this->wise);

        $loader = new ExampleFileLoader(new FileLocator());

        $this->resolver->addLoader($loader);

        $this->assertSame($this->collector, $loader->getResourceCollector());
        $this->assertSame($this->wise, $loader->getWise());
    }

    public function testGetResourceCollector()
    {
        $this->setPropertyValue($this->resolver, 'collector', $this->collector);

        $this->assertSame(
            $this->collector,
            $this->resolver->getResourceCollector()
        );
    }

    public function testGetWise()
    {
        $this->setPropertyValue($this->resolver, 'wise', $this->wise);

        $this->assertSame($this->wise, $this->resolver->getWise());
    }

    public function testSetResourceCollector()
    {
        $loader = new ExampleFileLoader(new FileLocator());

        $this->resolver->addLoader($loader);
        $this->resolver->setResourceCollector($this->collector);

        $this->assertSame($this->collector, $loader->getResourceCollector());
    }

    public function testSetWise()
    {
        $loader = new ExampleFileLoader(new FileLocator());

        $this->resolver->addLoader($loader);
        $this->resolver->setWise($this->wise);

        $this->assertSame($this->wise, $loader->getWise());
    }

    protected function setUp()
    {
        $this->collector = new ResourceCollector();
        $this->resolver = new LoaderResolver();
        $this->wise = new Wise();
    }
}
