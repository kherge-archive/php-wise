<?php

namespace Herrera\Wise\Tests\Loader;

use Herrera\Wise\Loader\AbstractFileLoader;

class ExampleFileLoader extends AbstractFileLoader
{
    public function doLoad($file)
    {
        /** @noinspection PhpIncludeInspection */
        return require $file;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource);
    }
}
