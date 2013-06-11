<?php

namespace Herrera\Wise\Tests\Processor;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Processor\DelegatingProcessor;
use Herrera\Wise\Processor\ProcessorResolver;

class DelegatingProcessorTest extends TestCase
{
    /**
     * @var DelegatingProcessor
     */
    private $processor;

    public function testConstruct()
    {
        $this->assertNotNull(
            $this->getPropertyValue($this->processor, 'resolver')
        );

        $this->assertSame(
            $this->processor->getResolver(),
            $this->getPropertyValue($this->processor, 'resolver')
        );
    }

    /**
     * @expectedException \Herrera\Wise\Exception\ProcessorException
     * @expectedExceptionMessage The support() method did not find a processor.
     */
    public function testGetConfigTreeBuilderNoneAvailable()
    {
        $this->processor->getConfigTreeBuilder();
    }

    public function testGetConfigTreeBuilder()
    {
        $this->setPropertyValue(
            $this->processor,
            'last',
            new ExampleProcessor()
        );

        $this->assertInstanceOf(
            'Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder',
            $this->processor->getConfigTreeBuilder()
        );
    }

    public function testSupports()
    {
        $this->assertFalse($this->processor->supports('test'));
        $this->assertTrue($this->processor->supports(array(), 'example'));
    }

    protected function setUp()
    {
        $this->processor = new DelegatingProcessor(
            new ProcessorResolver(array(new ExampleProcessor()))
        );
    }
}
