<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\JsonFileLoader;
use Symfony\Component\Config\FileLocator;

class JsonFileLoaderTest extends TestCase
{
    private $dir;

    /**
     * @var JsonFileLoader
     */
    private $loader;

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('test.json'));
        $this->assertTrue($this->loader->supports('test.json', 'json'));
    }

    public function testDoLoad()
    {
        file_put_contents(
            "{$this->dir}/test.json",
            <<<DATA
{
    "imports": [
        { "resource": "import.json" }
    ],
    "root": {
        "number": 123,
        "imported": "%imported.value%"
    },
    "imported": {
        "value": "imported value"
    }
}
DATA
        );

        file_put_contents(
            "{$this->dir}/import.json",
            <<<DATA
{
    "imported": {
        "value": "imported value"
    }
}
DATA
        );

        $this->assertSame(
            array(
                'imported' => array(
                    'value' => 'imported value'
                ),
                'imports' => array(
                    array('resource' => 'import.json')
                ),
                'root' => array(
                    'number' => 123,
                    'imported' => 'imported value'
                ),
            ),
            $this->loader->load('test.json')
        );
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->loader = new JsonFileLoader(new FileLocator($this->dir));
    }
}
