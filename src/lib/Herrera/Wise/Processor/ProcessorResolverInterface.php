<?php

namespace Herrera\Wise\Processor;

/**
 * Defines how a processor resolver class must be implemented.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface ProcessorResolverInterface
{
    /**
     * Returns the processor able to handle the resource.
     *
     * @param mixed  $resource The resource.
     * @param string $type     The resource type.
     *
     * @return ProcessorInterface|boolean The processor, or FALSE if none.
     */
    public function resolve($resource, $type = null);
}
