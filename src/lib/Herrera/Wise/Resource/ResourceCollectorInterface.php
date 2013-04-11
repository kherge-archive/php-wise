<?php

namespace Herrera\Wise\Resource;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * Defines how a resource collecting class must be implemented.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface ResourceCollectorInterface
{
    /**
     * Adds a resource to the collection.
     *
     * @param ResourceInterface $resource A resource.
     */
    public function addResource(ResourceInterface $resource);

    /**
     * Removes all resources in the collection.
     */
    public function clearResources();

    /**
     * Returns all of the resources in the collection.
     *
     * @return ResourceInterface[] The resources.
     */
    public function getResources();
}
