<?php

namespace Herrera\Wise\Loader;

use Herrera\Wise\Resource\ResourceAwareInterface;
use Herrera\Wise\Resource\ResourceCollectorInterface;
use Herrera\Wise\Wise;
use Herrera\Wise\WiseAwareInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver as Base;

/**
 * Provides Wise support to the resolver.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class LoaderResolver extends Base implements
    ResourceAwareInterface,
    WiseAwareInterface
{
    /**
     * The resource collector.
     *
     * @var ResourceCollectorInterface
     */
    private $collector;

    /**
     * The Wise instance.
     *
     * @var Wise
     */
    private $wise;

    /**
     * @override
     */
    public function addLoader(LoaderInterface $loader)
    {
        if ($this->collector && ($loader instanceof ResourceAwareInterface)) {
            $loader->setResourceCollector($this->collector);
        }

        if ($this->wise && ($loader instanceof WiseAwareInterface)) {
            $loader->setWise($this->wise);
        }

        parent::addLoader($loader);
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceCollector()
    {
        return $this->collector;
    }

    /**
     * {@inheritDoc}
     */
    public function getWise()
    {
        return $this->wise;
    }

    /**
     * {@inheritDoc}
     */
    public function setResourceCollector(ResourceCollectorInterface $collector)
    {
        $this->collector = $collector;

        foreach ($this->getLoaders() as $loader) {
            if ($loader instanceof ResourceAwareInterface) {
                $loader->setResourceCollector($collector);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setWise(Wise $wise)
    {
        $this->wise = $wise;

        foreach ($this->getLoaders() as $loader) {
            if ($loader instanceof WiseAwareInterface) {
                $loader->setWise($wise);
            }
        }
    }
}
