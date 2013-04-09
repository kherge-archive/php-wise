<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\AbstractFileLoader;
use Herrera\Wise\Resource\ResourceCollector;
use Herrera\Wise\Tests\Loader\ExampleFileLoader;
use Symfony\Component\Config\FileLocator;

class AbstractFileLoaderTest extends TestCase
{
    private $dir;

    /**
     * @var ResourceCollector
     */
    private $collector;

    /**
     * @var AbstractFileLoader
     */
    private $loader;

    public function testGetResourceCollector()
    {
        $this->setPropertyValue($this->loader, 'collector', $this->collector);

        $this->assertSame(
            $this->collector,
            $this->loader->getResourceCollector()
        );
    }

    public function testLoad()
    {
        $data = array('rand' => rand());

        file_put_contents(
            "{$this->dir}/test.php",
            '<?php return ' . var_export($data, true) . ';'
        );

        $this->setPropertyValue($this->loader, 'collector', $this->collector);
        $this->assertSame($data, $this->loader->load('test.php'));

        $resources = $this->collector->getResources();

        $this->assertCount(1, $resources);
        $this->assertInstanceOf(
            'Symfony\\Component\\Config\\Resource\\FileResource',
            $resources[0]
        );
    }

    public function testProcess()
    {
        file_put_contents(
            "{$this->dir}/one.php",
            '<?php return ' . var_export(
                array(
                    'imports' => array(
                        array('resource' => 'two.php')
                    ),
                    'placeholder' => '%imported.value%',
                    'inline_placeholder' => 'rand: %imported.value%'
                ),
                true
            ) . ';'
        );

        file_put_contents(
            "{$this->dir}/two.php",
            '<?php return ' . var_export(
                array(
                    'imported' => array(
                        'value' => $rand = rand()
                    )
                ),
                true
            ) . ';'
        );

        $this->assertEquals(
            array(
                'imports' => array(
                    array(
                        'resource' => 'two.php'
                    )
                ),
                'placeholder' => $rand,
                'inline_placeholder' => 'rand: ' . $rand,
                'imported' => array(
                    'value' => $rand
                )
            ),
            $this->loader->load('one.php')
        );
    }

    /**
     * @expectedException \Herrera\Wise\Exception\InvalidReferenceException
     * @expectedExceptionMessage The reference "%test.reference%" could not be resolved (failed at "test").
     */
    public function testProcessInvalidReference()
    {
        $this->loader->process(
            array(
                'bad_reference' => '%test.reference%'
            ),
            'test.php'
        );
    }

    /**
     * @expectedException \Herrera\Wise\Exception\InvalidReferenceException
     * @expectedExceptionMessage The reference "%test.reference%" is not a scalar value.
     */
    public function testProcessNonScalarReference()
    {
        $this->loader->process(
            array(
                'bad_reference' => '%test.reference%',
                'test' => array(
                    'reference' => array(
                        'value' => 123
                    )
                )
            ),
            'test.php'
        );
    }

    /**
     * @depends testGetResourceCollector
     */
    public function testSetResourceCollector()
    {
        $this->loader->setResourceCollector($this->collector);

        $this->assertSame(
            $this->collector,
            $this->loader->getResourceCollector()
        );
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->collector = new ResourceCollector();
        $this->loader = new ExampleFileLoader(new FileLocator($this->dir));
    }
}