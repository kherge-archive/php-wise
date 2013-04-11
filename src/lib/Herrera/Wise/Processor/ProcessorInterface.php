<?php

namespace Herrera\Wise\Processor;

use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines how a processor class must be implemented.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface ProcessorInterface extends ConfigurationInterface
{
    /**
     * Returns the processor resolver.
     *
     * @return ProcessorResolverInterface The resolver.
     */
    public function getResolver();

    /**
     * Processes the configuration data.
     *
     * @param array $data The data.
     *
     * @return array The processed data.
     */
    public function process(array $data);

    /**
     * Sets the processor resolver.
     *
     * @param ProcessorResolverInterface $resolver The resolver.
     */
    public function setResolver(ProcessorResolverInterface $resolver);

    /**
     * Checks if a resource is supported by this processor.
     *
     * @param mixed  $resource A resource.
     * @param string $type     The resource type.
     *
     * @return boolean TRUE if it is supported, FALSE if not.
     */
    public function supports($resource, $type = null);
}
