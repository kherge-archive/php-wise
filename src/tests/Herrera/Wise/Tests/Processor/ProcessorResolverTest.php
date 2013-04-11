<?php

namespace Herrera\Wise\Tests\Processor;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Processor\ProcessorResolver;

class ProcessorResolverTest extends TestCase
{
    /**
     * @var ExampleProcessor
     */
    private $processor;

    /**
     * @var ProcessorResolver
     */
    private $resolver;

    public function testConstruct()
    {
        $this->assertSame(
            array($this->processor),
            $this->getPropertyValue($this->resolver, 'processors')
        );
    }

    public function testAddProcessor()
    {
        $resolver = new ProcessorResolver();
        $resolver->addProcessor($this->processor);

        $this->assertSame(
            array($this->processor),
            $this->getPropertyValue($resolver, 'processors')
        );
    }

    /**
     * @depends testConstruct
     */
    public function testGetProcessors()
    {
        $this->assertSame(
            array($this->processor),
            $this->resolver->getProcessors()
        );
    }

    /**
     * @depends testConstruct
     */
    public function testResolve()
    {
        $this->assertFalse($this->resolver->resolve('test'));
        $this->assertSame(
            $this->processor,
            $this->resolver->resolve(array(), 'example')
        );
    }

    protected function setUp()
    {
        $this->processor = new ExampleProcessor();
        $this->resolver = new ProcessorResolver(array($this->processor));
    }
}
