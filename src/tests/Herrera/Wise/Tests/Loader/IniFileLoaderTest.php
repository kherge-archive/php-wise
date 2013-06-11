<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\IniFileLoader;
use Symfony\Component\Config\FileLocator;

class IniFileLoaderTest extends TestCase
{
    private $dir;

    /**
     * @var IniFileLoader
     */
    private $loader;

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('test.ini'));
        $this->assertTrue($this->loader->supports('test.ini', 'ini'));
    }

    public function testDoLoad()
    {
        file_put_contents(
            "{$this->dir}/test.ini",
            <<<DATA
[imports]
0[resource] = "import.ini"

[root]
number = 123
imported = "%imported.value%"
DATA
        );

        file_put_contents(
            "{$this->dir}/import.ini",
            <<<DATA
[imported]
value = "imported value"
DATA
        );

        $this->assertSame(
            array(
                'imported' => array(
                    'value' => 'imported value'
                ),
                'imports' => array(
                    array('resource' => 'import.ini')
                ),
                'root' => array(
                    'number' => '123',
                    'imported' => 'imported value'
                ),
            ),
            $this->loader->load('test.ini')
        );
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->loader = new IniFileLoader(new FileLocator($this->dir));
    }
}
