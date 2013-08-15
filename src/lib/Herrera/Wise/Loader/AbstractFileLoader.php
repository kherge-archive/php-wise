<?php

namespace Herrera\Wise\Loader;

use ArrayAccess;
use Herrera\Wise\Exception\ImportException;
use Herrera\Wise\Exception\InvalidReferenceException;
use Herrera\Wise\Resource\ResourceAwareInterface;
use Herrera\Wise\Resource\ResourceCollectorInterface;
use Herrera\Wise\Util\ArrayUtil;
use Herrera\Wise\Wise;
use Herrera\Wise\WiseAwareInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

/**
 * The parent class for the bundled file-based loaders.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractFileLoader extends FileLoader implements ResourceAwareInterface, WiseAwareInterface
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
     * Replace the placeholder value(s).
     *
     * @param mixed $input  The input.
     * @param array $data   The data the input is from.
     * @param array $global The global values.
     *
     * @return mixed The result.
     *
     * @throws InvalidReferenceException If an invalid reference is used.
     */
    public function doReplace($input, $data, $global)
    {
        preg_match_all(
            '/%(?P<reference>[^%]+)%/',
            $input,
            $matches
        );

        if (false === empty($matches['reference'])) {
            foreach ($matches['reference'] as $reference) {
                try {
                    $ref = $this->resolveReference($reference, $data);
                } catch (InvalidReferenceException $exception) {
                    if (empty($global)) {
                        throw $exception;
                    }

                    $ref = $this->resolveReference($reference, $global);
                }

                if ((false === is_null($ref))
                    && (false === is_scalar($ref))
                    && (false == preg_match('/^%(?:[^%]+)%$/', $input))) {
                    throw InvalidReferenceException::format(
                        'The non-scalar reference "%s" cannot be used inline.',
                        "%$reference%"
                    );
                }

                if ("%$reference%" === $input) {
                    $input = $ref;
                } else {
                    $input = str_replace("%$reference%", $ref, $input);
                }
            }
        }

        return $input;
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
     * @throws ImportException           If "imports" is invalid.
     * @throws InvalidReferenceException If an invalid reference is used.
     */
    public function process($data, $file)
    {
        if (empty($data)) {
            return array();
        }

        if (isset($data['imports'])) {
            if (false === is_array($data['imports'])) {
                throw ImportException::format(
                    'The "imports" value is not valid in "%s".',
                    $file
                );
            }

            $dir = dirname($file);

            foreach ($data['imports'] as $i => $import) {
                if (false === is_array($import)) {
                    throw ImportException::format(
                        'One of the "imports" values (#%d) is not valid in "%s".',
                        $i,
                        $file
                    );
                }

                if (false === isset($import['resource'])) {
                    throw ImportException::format(
                        'A resource was not defined for an import in "%s".',
                        $file
                    );
                }

                $this->setCurrentDir($dir);

                $data = array_replace_recursive(
                    $this->import(
                        $import['resource'],
                        null,
                        isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false
                    ),
                    $data
                );
            }
        }

        $global = $this->wise ? $this->wise->getGlobalParameters() : array();
        $_this = $this;

        ArrayUtil::walkRecursive(
            $data,
            function (&$value, $key, &$array) use (&$data, $global, $_this) {
                $value = $_this->doReplace($value, $data, $global);

                if (false !== strpos($key, '%')) {
                    unset($array[$key]);

                    $key = $_this->doReplace($key, $data, $global);

                    $array[$key] = $value;
                }
            }
        );

        return $data;
    }

    /**
     * Resolves the reference and returns its value.
     *
     * @param string            $reference A reference.
     * @param array|ArrayAccess $values    A list of values.
     *
     * @return mixed The referenced value.
     *
     * @throws InvalidReferenceException If the reference is not valid.
     */
    public function resolveReference($reference, $values)
    {
        foreach (explode('.', $reference) as $leaf) {
            if ((!is_array($values) && !($values instanceof ArrayAccess))
                || (is_array($values) && !array_key_exists($leaf, $values))
                || (($values instanceof ArrayAccess) && !$values->offsetExists($leaf))) {
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
