<?php

namespace Herrera\Wise\Tests\Processor;

use Herrera\Wise\Processor\AbstractProcessor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ExampleProcessor extends AbstractProcessor
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('example');

        $root->children()
                 ->booleanNode('enabled')
                     ->defaultFalse()
                 ->end()
             ->end();

        return $builder;
    }

    public function supports($resource, $type = null)
    {
        return is_array($resource)
            && ('example' === $type);
    }
}
