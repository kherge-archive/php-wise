<?php

namespace Herrera\Wise\Tests\Processor;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Processor\AbstractProcessor;
use Herrera\Wise\Processor\ProcessorResolver;

class AbstractProcessorTest extends TestCase
{
    /**
     * @var AbstractProcessor
     */
    private $processor;

    /**
     * @var ProcessorResolver
     */
    private $resolver;

    public function testGetResolver()
    {
        $this->setPropertyValue($this->processor, 'resolver', $this->resolver);

        $this->assertSame($this->resolver, $this->processor->getResolver());
    }

    public function testProcess()
    {
        $this->assertSame(
            array('enabled' => false),
            $this->processor->process(array())
        );
    }

    /**
     * @depends testGetResolver
     */
    public function testSetResolver()
    {
        $this->processor->setResolver($this->resolver);

        $this->assertSame($this->resolver, $this->processor->getResolver());
    }

    protected function setUp()
    {
        $this->processor = new ExampleProcessor();
        $this->resolver = new ProcessorResolver();
    }
}
