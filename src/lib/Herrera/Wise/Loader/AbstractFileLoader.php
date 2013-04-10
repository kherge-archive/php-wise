<?php

namespace Herrera\Wise\Loader;

use Herrera\Wise\Exception\InvalidReferenceException;
use Herrera\Wise\Resource\ResourceAwareInterface;
use Herrera\Wise\Resource\ResourceCollectorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

/**
 * The parent class for the bundled file-based loaders.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractFileLoader
       extends FileLoader
    implements ResourceAwareInterface
{
    /**
     * The resource collector.
     *
     * @var ResourceCollectorInterface
     */
    private $collector;

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
    public function load($resource, $type = null)
    {
        $file = $this->locator->locate($resource, $type);

        if ($this->collector) {
            $this->collector->addResource(new FileResource($file));
        }

        $data = $this->doLoad($file);

        return $this->process($data, $resource);
    }

    /**
     * Imports other configuration files and resolves references.
     *
     * @param array  $data The data.
     * @param string $file The file source.
     *
     * @return array The processed data.
     *
     * @throws InvalidReferenceException If an invalid reference is used.
     */
    public function process(array $data, $file)
    {
        if (isset($data['imports'])) {
            $dir = dirname($file);

            foreach ($data['imports'] as $import) {
                $this->setCurrentDir($dir);

                $data = array_replace_recursive($data, $this->import(
                    $import['resource'],
                    null,
                    // @codeCoverageIgnoreStart
                    isset($import['ignore_errors'])
                        ? (bool) $import['ignore_errors']
                        : false
                    // @codeCoverageIgnoreEnd
                ));
            }
        }

        array_walk_recursive(
            $data,
            function (&$value) use (&$data) {
                preg_match_all(
                    '/%(?P<reference>[^%]+)%/',
                    $value,
                    $matches
                );

                if (false === empty($matches['reference'])) {
                    foreach ($matches['reference'] as $reference) {
                        $tree = explode('.', $reference);
                        $ref =& $data;

                        foreach ($tree as $branch) {
                            if (false === isset($ref[$branch])) {
                                throw InvalidReferenceException::format(
                                    'The reference "%s" could not be resolved (failed at "%s").',
                                    "%$reference%",
                                    $branch
                                );
                            }

                            $ref =& $ref[$branch];
                        }

                        if (false === is_scalar($ref)) {
                            if (false == preg_match('/^%([^%]+)%$/', $value)) {
                                throw InvalidReferenceException::format(
                                    'The non-scalar reference "%s" cannot be used inline.',
                                    "%$reference%"
                                );
                            }

                            $value = $ref;
                        } else {
                            $value = str_replace("%$reference%", $ref, $value);
                        }
                    }
                }
            }
        );

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function setResourceCollector(ResourceCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * Returns the parsed data of the file.
     *
     * @param string $file The file path.
     *
     * @return array The parsed data.
     */
    abstract protected function doLoad($file);
}