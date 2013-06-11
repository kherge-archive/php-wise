<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class YamlFileLoaderTest extends TestCase
{
    private $dir;

    /**
     * @var YamlFileLoader
     */
    private $loader;

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('test.yml'));
        $this->assertTrue($this->loader->supports('test.yml', 'yaml'));
    }

    public function testDoLoad()
    {
        file_put_contents(
            "{$this->dir}/test.yml",
            <<<DATA
imports:
    - { resource: import.yml }

root:
    number: 123
    imported: %imported.value%
DATA
        );

        file_put_contents(
            "{$this->dir}/import.yml",
            <<<DATA
imported:
    value: imported value
DATA
        );

        $this->assertSame(
            array(
                'imported' => array(
                    'value' => 'imported value'
                ),
                'imports' => array(
                    array('resource' => 'import.yml')
                ),
                'root' => array(
                    'number' => 123,
                    'imported' => 'imported value'
                ),
            ),
            $this->loader->load('test.yml')
        );
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->loader = new YamlFileLoader(new FileLocator($this->dir));
    }
}
