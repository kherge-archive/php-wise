<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class XmlFileLoaderTest extends TestCase
{
    private $dir;

    /**
     * @var XmlFileLoader
     */
    private $loader;

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('test.xml'));
        $this->assertTrue($this->loader->supports('test.xml', 'xml'));
    }

    public function testDoLoad()
    {
        file_put_contents(
            "{$this->dir}/test.xml",
            <<<DATA
<array>
  <array key="imports">
    <array>
      <str key="resource">import.xml</str>
    </array>
  </array>
  <array key="root">
    <int key="number">123</int>
    <str key="imported">%imported.value%</str>
    <bool key="enabled">1</bool>
    <float key="unit">1.23</float>
  </array>
</array>
DATA
        );

        file_put_contents(
            "{$this->dir}/import.xml",
            <<<DATA
<array>
  <array key="imported">
    <str key="value">imported value</str>
  </array>
</array>
DATA
        );

        $this->assertSame(
            array(
                'imported' => array(
                    'value' => 'imported value'
                ),
                'imports' => array(
                    array('resource' => 'import.xml')
                ),
                'root' => array(
                    'number' => 123,
                    'imported' => 'imported value',
                    'enabled' => true,
                    'unit' => 1.23
                ),
            ),
            $this->loader->load('test.xml')
        );
    }

    protected function setUp()
    {
        $this->dir = $this->createDir();
        $this->loader = new XmlFileLoader(new FileLocator($this->dir));
    }
}
