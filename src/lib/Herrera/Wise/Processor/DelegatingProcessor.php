<?php

namespace Herrera\Wise\Processor;

use Herrera\Wise\Exception\ProcessorException;

/**
 * Delegates processing to a processor resolver.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class DelegatingProcessor extends AbstractProcessor
{
    /**
     * The last processor returned by the resolver.
     *
     * @var ProcessorInterface
     */
    private $last;

    /**
     * Sets the processor resolver.
     *
     * @param ProcessorResolverInterface $resolver The resolver.
     */
    public function __construct(ProcessorResolverInterface $resolver)
    {
        $this->setResolver($resolver);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ProcessorException If no processor is set.
     */
    public function getConfigTreeBuilder()
    {
        if (null === $this->last) {
            throw new ProcessorException(
                'The support() method did not find a processor.'
            );
        }

        return $this->last->getConfigTreeBuilder();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        $this->last = $this->getResolver()->resolve($resource, $type) ?: null;

        return (null !== $this->last);
    }
}
