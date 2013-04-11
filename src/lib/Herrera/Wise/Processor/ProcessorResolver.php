<?php

namespace Herrera\Wise\Processor;

/**
 * A simple processor resolver.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ProcessorResolver implements ProcessorResolverInterface
{
    /**
     * The collection of processors.
     *
     * @var ProcessorInterface[]
     */
    private $processors = array();

    /**
     * Sets processors in the resolver.
     *
     * @param ProcessorInterface[] $processors The processors.
     */
    public function __construct(array $processors = array())
    {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    /**
     * Adds a processor to the resolve.
     *
     * @param ProcessorInterface $processor A processor.
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Returns the processors in the resolve.
     *
     * @return ProcessorInterface[] The processors.
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($resource, $type = null)
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($resource, $type)) {
                return $processor;
            }
        }

        return false;
    }
}
