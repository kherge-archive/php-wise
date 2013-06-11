<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

class PhpFileLoaderTest extends TestCase
{
    private $dir;

    /**
     * @var PhpFileLoader
     */
    private $loader;

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('test.php'));
        $this->assertTrue($this->loader->supports('test.php', 'php'));
    }

    public function testDoLoad()
    {
        file_put_contents(
            "{$this->dir}/test.php",
            <<<DATA
<?php return array(
    'imports' => array(
        array('resource' => 'import.php')
    ),
    'root' => array(
        'number' => 123,
        'imported' => '%imported.value%'
    )
);
DATA
        );

        file_put_contents(
            "{$this->dir}/import.php",
            <<<DATA
<?php return array(
    'imported' => array(
        'value' => 'imported value'
    )
);
DATA
        );

        $this->assertSame(
            array(
                'imported' => array(
                    'value' => 'imported value'
                ),
                'imports' => array(
                    array('resource' => 'import.php')
                ),
                'root' => array(
                    'number' => 123,
                    'imported' => 'imported value'
                ),
            ),
            $this->loader->load('test.php')
        );
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->loader = new PhpFileLoader(new FileLocator($this->dir));
    }
}
