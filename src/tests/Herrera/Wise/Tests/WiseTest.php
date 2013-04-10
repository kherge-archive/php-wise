<?php

namespace Herrera\Wise\Tests;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\PhpFileLoader;
use Herrera\Wise\Processor\AbstractProcessor;
use Herrera\Wise\Resource\ResourceCollector;
use Herrera\Wise\Tests\Processor\TestProcessor;
use Herrera\Wise\Wise;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;

class WiseTest extends TestCase
{
    /**
     * @var string
     */
    private $cache;

    /**
     * @var ResourceCollector
     */
    private $collector;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var PhpFileLoader
     */
    private $loader;

    /**
     * @var TestProcessor
     */
    private $processor;

    /**
     * @var Wise
     */
    private $wise;

    public function testConstruct()
    {
        $this->assertTrue($this->getPropertyValue($this->wise, 'debug'));

        $wise = new Wise();

        $this->assertFalse($this->getPropertyValue($wise, 'debug'));
    }

    public function testCreate()
    {
        file_put_contents(
            $this->dir . '/test.php',
            <<<PHP
<?php return array(
    'root' => array(
        'number' => 123
    )
);
PHP
        );

        $wise = Wise::create($this->dir, $this->cache, true);
        $expected = array(
            'root' => array(
                'number' => 123
            )
        );

        $this->assertEquals($expected, $wise->load('test.php', 'php'));
        $this->assertFileExists($this->cache . '/test.php.cache');
        $this->assertFileExists($this->cache . '/test.php.cache.meta');
    }

    public function testGetCacheDir()
    {
        $this->setPropertyValue($this->wise, 'cacheDir', $this->cache);

        $this->assertEquals($this->cache, $this->wise->getCacheDir());
    }

    public function testGetCollector()
    {
        $this->setPropertyValue($this->wise, 'collector', $this->collector);

        $this->assertSame($this->collector, $this->wise->getCollector());
    }

    public function testGetGlobalParameters()
    {
        $this->setPropertyValue(
            $this->wise,
            'parameters',
            array('value' => 123)
        );

        $this->assertEquals(
            array('value' => 123),
            $this->wise->getGlobalParameters()
        );
    }

    public function testGetLoader()
    {
        $this->setPropertyValue($this->wise, 'loader', $this->loader);

        $this->assertSame($this->loader, $this->wise->getLoader());
    }

    public function testGetProcessor()
    {
        $this->setPropertyValue($this->wise, 'processor', $this->processor);

        $this->assertSame($this->processor, $this->wise->getProcessor());
    }

    /**
     * @depends testConstruct
     */
    public function testIsDebugEnabled()
    {
        $this->assertTrue($this->wise->isDebugEnabled());
    }

    /**
     * @expectedException \Herrera\Wise\Exception\LogicException
     * @expectedExceptionMessage No loader has been configured.
     */
    public function testLoadLoaderNotSet()
    {
        $this->wise->load('test');
    }

    /**
     * @expectedException \Herrera\Wise\Exception\LoaderException
     * @expectedExceptionMessage The resource "123" (test) is not supported by the loader.
     */
    public function testLoadLoaderNotSupported()
    {
        $this->setPropertyValue($this->wise, 'loader', $this->loader);

        $this->wise->load(123, 'test');
    }

    public function testLoad()
    {
        file_put_contents(
            $this->dir . '/test.php',
            <<<PHP
<?php return array(
    'root' => array(
        'number' => 123
    )
);
PHP
        );

        $expected = array(
            'enabled' => false,
            'number' => '123'
        );

        $this->setPropertyValue($this->wise, 'loader', $this->loader);

        $this->assertEquals(
            array(
                'root' => array(
                    'number' => 123
                )
            ),
            $this->wise->load('test.php', 'php')
        );

        $this->setPropertyValue($this->wise, 'cacheDir', $this->cache);
        $this->setPropertyValue($this->wise, 'collector', $this->collector);
        $this->setPropertyValue($this->wise, 'debug', true);
        $this->setPropertyValue($this->wise, 'processor', $this->processor);

        $this->assertEquals($expected, $this->wise->load('test.php', 'php'));
        $this->assertFileExists($this->cache . '/test.php.cache');
        $this->assertFileExists($this->cache . '/test.php.cache.meta');
        $this->assertEquals($expected, require $this->cache . '/test.php.cache');

        $meta = unserialize(
            file_get_contents(
                $this->cache . '/test.php.cache.meta'
            )
        );

        $this->assertCount(1, $meta);
        $this->assertInstanceOf(
            'Symfony\\Component\\Config\\Resource\\FileResource',
            $meta[0]
        );

        file_put_contents($this->dir . '/test.php', '');
        touch(
            $this->dir . '/test.php',
            filemtime($this->cache . '/test.php.cache') - 1000
        );

        $this->assertEquals($expected, $this->wise->load('test.php', 'php'));
    }

    public function testLoadWithBasicProcessor()
    {
        file_put_contents(
            $this->dir . '/test.php',
            <<<PHP
<?php return array(
    'root' => array(
        'number' => 123
    )
);
PHP
        );

        $this->setPropertyValue($this->wise, 'loader', $this->loader);
        $this->setPropertyValue($this->wise, 'processor', new BasicProcessor());

        $this->assertEquals(
            array(
                'enabled' => false,
                'number' => 123
            ),
            $this->wise->load('test.php', 'php')
        );
    }

    public function testLoadNoProcessorSupported()
    {
        file_put_contents(
            $this->dir . '/test.php',
            <<<PHP
<?php return array(
    'root' => array(
        'number' => 123
    )
);
PHP
        );

        $this->setPropertyValue($this->wise, 'loader', $this->loader);
        $this->setPropertyValue(
            $this->wise,
            'processor',
            new NeverSupportdProcessor()
        );

        $this->setExpectedException(
            'Herrera\\Wise\\Exception\\ProcessorException',
            'The resource "test.php" (php) is not supported by the processor.'
        );

        $this->wise->load('test.php', 'php', true);
    }

    public function testLoadNoProcessorRegistered()
    {
        file_put_contents(
            $this->dir . '/test.php',
            <<<PHP
<?php return array(
    'root' => array(
        'number' => 123
    )
);
PHP
        );

        $this->setPropertyValue($this->wise, 'loader', $this->loader);

        $this->setExpectedException(
            'Herrera\\Wise\\Exception\\ProcessorException',
            'No processor registered to handle any resource.'
        );

        $this->wise->load('test.php', 'php', true);
    }

    /**
     * @depends testGetCacheDir
     */
    public function testSetCacheDir()
    {
        $this->wise->setCacheDir($this->cache);

        $this->assertEquals($this->cache, $this->wise->getCacheDir());
    }

    /**
     * @depends testGetCollector
     */
    public function testSetCollector()
    {
        $this->setPropertyValue($this->wise, 'loader', $this->loader);

        $this->wise->setCollector($this->collector);

        $this->assertSame($this->collector, $this->wise->getCollector());
        $this->assertSame(
            $this->collector,
            $this->loader->getResourceCollector()
        );
    }

    /**
     * @depends testGetGlobalParameters
     */
    public function testSetGlobalParameters()
    {
        $this->wise->setGlobalParameters(array('value' => 123));

        $this->assertEquals(
            array('value' => 123),
            $this->wise->getGlobalParameters()
        );
    }

    /**
     * @depends testGetLoader
     */
    public function testSetLoader()
    {
        $this->setPropertyValue($this->wise, 'collector', $this->collector);

        $this->wise->setLoader($this->loader);

        $this->assertSame($this->loader, $this->wise->getLoader());
        $this->assertSame(
            $this->collector,
            $this->loader->getResourceCollector()
        );
    }

    /**
     * @depends testGetProcessor
     */
    public function testSetProcessor()
    {
        $this->wise->setProcessor($this->processor);

        $this->assertSame($this->processor, $this->wise->getProcessor());

        $processor = new BasicProcessor();

        $this->wise->setProcessor($processor);

        $this->assertSame($processor, $this->wise->getProcessor());
    }

    protected function setUp()
    {
        $this->cache = $this->createDir();
        $this->dir = $this->createDir();

        $this->collector = new ResourceCollector();
        $this->loader = new PhpFileLoader(new FileLocator($this->dir));
        $this->processor = new TestProcessor();
        $this->wise = new Wise(true);

        $this->loader->setResourceCollector($this->collector);
    }
}

class BasicProcessor implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('root');

        $root->children()
                 ->booleanNode('enabled')
                     ->defaultFalse()
                 ->end()
                 ->integerNode('number')->end()
             ->end();

        return $builder;
    }
}

class NeverSupportdProcessor extends AbstractProcessor
{
    public function getConfigTreeBuilder()
    {
    }

    public function supports($resource, $type = null)
    {
        return false;
    }
}