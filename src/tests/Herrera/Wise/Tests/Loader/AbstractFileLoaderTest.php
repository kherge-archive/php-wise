<?php

namespace Herrera\Wise\Tests\Loader;

use ArrayObject;
use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\AbstractFileLoader;
use Herrera\Wise\Resource\ResourceCollector;
use Herrera\Wise\Tests\Loader\ExampleFileLoader;
use Herrera\Wise\Wise;
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

    public function testGetWise()
    {
        $wise = new Wise();

        $this->setPropertyValue($this->loader, 'wise', $wise);

        $this->assertSame($wise, $this->loader->getWise());
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

    public function testProcessEmpty()
    {
        $this->assertSame(array(), $this->loader->process(null, 'test.file'));
    }

    /**
     * @expectedException \Herrera\Wise\Exception\ImportException
     * @expectedExceptionMessage The "imports" value is not valid in "test.file".
     */
    public function testProcessInvalidImports()
    {
        $this->loader->process(array('imports' => 123), 'test.file');
    }

    /**
     * @expectedException \Herrera\Wise\Exception\ImportException
     * @expectedExceptionMessage One of the "imports" values (#0) is not valid in "test.file".
     */
    public function testProcessInvalidImport()
    {
        $this->loader->process(
            array('imports' => array(123)),
            'test.file'
        );
    }

    /**
     * @expectedException \Herrera\Wise\Exception\ImportException
     * @expectedExceptionMessage A resource was not defined for an import in "test.file".
     */
    public function testProcessInvalidImportMissingResource()
    {
        $this->loader->process(
            array('imports' => array(array())),
            'test.file'
        );
    }

    public function testProcess()
    {
        $wise = new Wise();
        $wise->setGlobalParameters(
            array(
                'global' => array(
                    'value' => 999
                )
            )
        );

        $this->setPropertyValue($this->loader, 'wise', $wise);

        file_put_contents(
            "{$this->dir}/one.php",
            '<?php return ' . var_export(
                array(
                    'imports' => array(
                        array('resource' => 'two.php')
                    ),
                    'global' => '%global.value%',
                    'placeholder' => '%imported.list%',
                    'sub' => array(
                        'inline_placeholder' => 'rand: %imported.list.null%%imported.value%',
                    ),
                    '%imported.key%' => 'a value'
                ),
                true
            ) . ';'
        );

        file_put_contents(
            "{$this->dir}/two.php",
            '<?php return ' . var_export(
                array(
                    'imported' => array(
                        'key' => 'replaced_key',
                        'list' => array(
                            'null' => null,
                            'value' => 123
                        ),
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
                'global' => 999,
                'placeholder' => array(
                    'null' => null,
                    'value' => 123
                ),
                'sub' => array(
                    'inline_placeholder' => 'rand: ' . $rand,
                ),
                'replaced_key' => 'a value',
                'imported' => array(
                    'key' => 'replaced_key',
                    'list' => array(
                        'null' => null,
                        'value' => 123
                    ),
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
     * @expectedExceptionMessage The non-scalar reference "%test.reference%" cannot be used inline.
     */
    public function testProcessNonScalarReference()
    {
        $this->loader->process(
            array(
                'bad_reference' => 'bad: %test.reference%',
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
     * @expectedException \Herrera\Wise\Exception\InvalidReferenceException
     * @expectedExceptionMessage The reference "%a.b.c.d%" could not be resolved (failed at "a").
     */
    public function testResolveReferenceInvalid()
    {
        $this->loader->resolveReference('a.b.c.d', array());
    }

    public function testResolveReference()
    {
        $array = array(
            'a' => array(
                'b' => array(
                    'c' => array(
                        'd' => 123
                    )
                )
            )
        );

        $object = new ArrayObject($array);

        $this->assertEquals(
            123,
            $this->loader->resolveReference('a.b.c.d', $array)
        );

        $this->assertEquals(
            123,
            $this->loader->resolveReference('a.b.c.d', $object)
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

    /**
     * @depends testGetWise
     */
    public function testSetWise()
    {
        $wise = new Wise();

        $this->loader->setWise($wise);

        $this->assertSame($wise, $this->loader->getWise());
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->collector = new ResourceCollector();
        $this->loader = new ExampleFileLoader(new FileLocator($this->dir));
    }
}
