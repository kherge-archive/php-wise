<?php

namespace Herrera\Wise\Resource;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * A simple resource collector.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ResourceCollector implements ResourceCollectorInterface
{
    /**
     * The collection of resources.
     *
     * @var ResourceInterface[]
     */
    private $resources = array();

    /**
     * {@inheritDoc}
     */
    public function addResource(ResourceInterface $resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function clearResources()
    {
        $this->resources = array();
    }

    /**
     * {@inheritDoc}
     */
    public function getResources()
    {
        return $this->resources;
    }
}
