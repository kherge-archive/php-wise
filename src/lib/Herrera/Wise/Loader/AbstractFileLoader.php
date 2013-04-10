<?php

namespace Herrera\Wise\Loader;

use Herrera\Wise\Exception\InvalidReferenceException;
use Herrera\Wise\Resource\ResourceAwareInterface;
use Herrera\Wise\Resource\ResourceCollectorInterface;
use Herrera\Wise\Wise;
use Herrera\Wise\WiseAwareInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

/**
 * The parent class for the bundled file-based loaders.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractFileLoader
       extends FileLoader
    implements ResourceAwareInterface,
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

        $global = $this->wise ? $this->wise->getGlobalParameters() : array();
        $_this = $this;

        array_walk_recursive(
            $data,
            function (&$value) use (&$data, $global, $_this) {
                preg_match_all(
                    '/%(?P<reference>[^%]+)%/',
                    $value,
                    $matches
                );

                if (false === empty($matches['reference'])) {
                    foreach ($matches['reference'] as $reference) {
                        try {
                            $ref = $_this->resolveReference($reference, $data);
                        } catch (InvalidReferenceException $exception) {
                            if (empty($global)) {
                                throw $exception;
                            }

                            $ref = $_this->resolveReference($reference, $global);
                        }

                        if (false === is_scalar($ref)) {
                            if (false == preg_match('/^%(?:[^%]+)%$/', $value)) {
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
     * Resolves the reference and returns its value.
     *
     * @param string $reference A reference.
     * @param array  $values    A list of values.
     *
     * @return mixed The referenced value.
     *
     * @throws InvalidReferenceException If the reference is not valid.
     */
    public function resolveReference($reference, array $values)
    {
        foreach (explode('.', $reference) as $leaf) {
            if ((false === is_array($values))
                || (false === array_key_exists($leaf, $values))) {
                throw InvalidReferenceException::format(
                    'The reference "%s" could not be resolved (failed at "%s").',
                    "%$reference%",
                    $leaf
                );
            }

            $values = $values[$leaf];
        }

        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function setResourceCollector(ResourceCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * {@inheritDoc}
     */
    public function setWise(Wise $wise)
    {
        $this->wise = $wise;
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