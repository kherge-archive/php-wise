<?php

namespace Herrera\Wise\Resource;

/**
 * Indicates that the class supports a resource collector.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface ResourceAwareInterface
{
    /**
     * Returns the resource collector.
     *
     * @return ResourceCollectorInterface The collector.
     */
    public function getResourceCollector();

    /**
     * Sets the resource collector.
     *
     * @param ResourceCollectorInterface $collector The collector.
     */
    public function setResourceCollector(ResourceCollectorInterface $collector);
}
