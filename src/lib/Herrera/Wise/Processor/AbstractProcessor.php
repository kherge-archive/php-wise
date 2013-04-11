<?php

namespace Herrera\Wise\Processor;

use Symfony\Component\Config\Definition\Processor;

/**
 * An abstract processor implementing basic functionality.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * The processor resolver.
     *
     * @var ProcessorResolverInterface
     */
    private $resolver;

    /**
     * {@inheritDoc}
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $data)
    {
        $processor = new Processor();

        return $processor->processConfiguration($this, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function setResolver(ProcessorResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
}
