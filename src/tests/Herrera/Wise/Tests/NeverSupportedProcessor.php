<?php

namespace Herrera\Wise\Tests;

use Herrera\Wise\Processor\AbstractProcessor;

class NeverSupportedProcessor extends AbstractProcessor
{
    public function getConfigTreeBuilder()
    {
    }

    public function supports($resource, $type = null)
    {
        return false;
    }
}
