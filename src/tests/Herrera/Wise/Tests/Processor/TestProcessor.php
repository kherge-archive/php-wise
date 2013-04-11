<?php

namespace Herrera\Wise\Tests\Processor;

use Herrera\Wise\Processor\AbstractProcessor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class TestProcessor extends AbstractProcessor
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

    public function supports($resource, $type = null)
    {
        return ((null === $type) || ('php' === $type));
    }
}
