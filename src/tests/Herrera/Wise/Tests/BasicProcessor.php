<?php

namespace Herrera\Wise\Tests;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
