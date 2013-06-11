<?php

namespace Herrera\Wise;

use ArrayAccess;
use Herrera\Wise\Exception\InvalidArgumentException;
use Herrera\Wise\Exception\LoaderException;
use Herrera\Wise\Exception\LogicException;
use Herrera\Wise\Exception\ProcessorException;
use Herrera\Wise\Loader\LoaderResolver;
use Herrera\Wise\Processor\ProcessorInterface;
use Herrera\Wise\Resource\ResourceAwareInterface;
use Herrera\Wise\Resource\ResourceCollectorInterface;
use Herrera\Wise\Util\ArrayUtil;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Manages access to the configuration data.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Wise
{
    /**
     * The cache directory path.
     *
     * @var string
     */
    private $cacheDir;

    /**
     * The resource collector.
     *
     * @var ResourceCollectorInterface
     */
    private $collector;

    /**
     * The debug mode flag.
     *
     * @var boolean
     */
    private $debug;

    /**
     * The configuration loader.
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * The list of global parameters.
     *
     * @var array
     */
    private $parameters = array();

    /**
     * The configuration processor.
     *
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * Sets the debugging mode.
     *
     * @param boolean $debug Enable debugging?
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Creates a pre-configured instance of Wise.
     *
     * @param array|string $paths The configuration directory path(s).
     * @param string       $cache The cache directory path.
     * @param boolean      $debug Enable debugging?
     *
     * @return Wise The instance.
     */
    public static function create($paths, $cache = null, $debug = false)
    {
        $wise = new self($debug);

        if ($cache) {
            $wise->setCacheDir($cache);
        }

        $locator = new FileLocator($paths);
        $resolver = new LoaderResolver(
            array(
                new Loader\IniFileLoader($locator),
                new Loader\JsonFileLoader($locator),
                new Loader\PhpFileLoader($locator),
                new Loader\XmlFileLoader($locator),
                new Loader\YamlFileLoader($locator),
            )
        );

        $wise->setCollector(new Resource\ResourceCollector());
        $wise->setLoader(new DelegatingLoader($resolver));

        $resolver->setResourceCollector($wise->getCollector());
        $resolver->setWise($wise);

        return $wise;
    }

    /**
     * Returns the cache directory path.
     *
     * @return string The path.
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Returns the resource collector.
     *
     * @return ResourceCollectorInterface The collector.
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * Returns the list of global parameters.
     *
     * @return array The parameters.
     */
    public function getGlobalParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the configuration loader.
     *
     * @return LoaderInterface The loader.
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Returns the configuration processor.
     *
     * @return ProcessorInterface The processor.
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Checks if debugging is enabled.
     *
     * @return boolean TRUE if it is enabled, FALSE if not.
     */
    public function isDebugEnabled()
    {
        return $this->debug;
    }

    /**
     * Loads the configuration data from a resource.
     *
     * @param mixed   $resource A resource.
     * @param string  $type     The resource type.
     * @param boolean $require  Require processing?
     *
     * @return array The data.
     *
     * @throws LoaderException If the loader could not be used.
     * @throws LogicException  If no loader has been configured.
     */
    public function load($resource, $type = null, $require = false)
    {
        if (null === $this->loader) {
            throw new LogicException('No loader has been configured.');
        }

        if (false === $this->loader->supports($resource, $type)) {
            throw LoaderException::format(
                'The resource "%s"%s is not supported by the loader.',
                is_scalar($resource) ? $resource : gettype($resource),
                $type ? " ($type)" : ''
            );
        }

        if ($this->cacheDir
            && $this->collector
            && is_string($resource)
            && (false === strpos("\n", $resource))
            && (false === strpos("\r", $resource))) {
            $cache = new ConfigCache(
                $this->cacheDir . DIRECTORY_SEPARATOR . basename($resource) . '.cache',
                $this->debug
            );

            if ($cache->isFresh()) {
                /** @noinspection PhpIncludeInspection */
                return require $cache;
            }
        }

        if ($this->collector) {
            $this->collector->clearResources();
        }

        $data = $this->process(
            $this->loader->load($resource, $type),
            $resource,
            $type,
            $require
        );

        if (isset($cache)) {
            $cache->write(
                '<?php return ' . var_export($data, true) . ';',
                $this->collector->getResources()
            );
        }

        return $data;
    }

    /**
     * Loads the configuration data from a resource and returns it flattened.
     *
     * @param mixed   $resource A resource.
     * @param string  $type     The resource type.
     * @param boolean $require  Require processing?
     *
     * @return array The data.
     */
    public function loadFlat($resource, $type = null, $require = false)
    {
        return ArrayUtil::flatten($this->load($resource, $type, $require));
    }

    /**
     * Sets the cache directory path.
     *
     * @param string $path The path.
     */
    public function setCacheDir($path)
    {
        $this->cacheDir = $path;
    }

    /**
     * Sets the resource collector.
     *
     * @param ResourceCollectorInterface $collector The collector.
     */
    public function setCollector(ResourceCollectorInterface $collector)
    {
        $this->collector = $collector;

        if ($this->loader) {
            if ($this->loader instanceof ResourceAwareInterface) {
                $this->loader->setResourceCollector($collector);
            }

            if ($this->loader instanceof DelegatingLoader) {
                $resolver = $this->loader->getResolver();

                if ($resolver instanceof ResourceAwareInterface) {
                    $resolver->setResourceCollector($collector);
                }
            }
        }
    }

    /**
     * Sets a list of global parameters.
     *
     * @param array|ArrayAccess $parameters The parameters.
     *
     * @throws InvalidArgumentException If $parameters is invalid.
     */
    public function setGlobalParameters($parameters)
    {
        if (!is_array($parameters) && !($parameters instanceof ArrayAccess)) {
            throw new InvalidArgumentException(
                'The $parameters argument must be an array or array accessible object.'
            );
        }

        $this->parameters = $parameters;
    }

    /**
     * Sets a configuration loader.
     *
     * @param LoaderInterface $loader A loader.
     */
    public function setLoader(LoaderInterface $loader)
    {
        $this->loader = $loader;

        if ($this->collector && ($loader instanceof ResourceAwareInterface)) {
            $loader->setResourceCollector($this->collector);
        }

        if ($loader instanceof WiseAwareInterface) {
            $loader->setWise($this);
        }

        if ($loader instanceof DelegatingLoader) {
            $resolver = $loader->getResolver();

            if ($this->collector && ($resolver instanceof ResourceAwareInterface)) {
                $resolver->setResourceCollector($this->collector);
            }

            if ($resolver instanceof WiseAwareInterface) {
                $resolver->setWise($this);
            }

        }
    }

    /**
     * Sets a configuration processor.
     *
     * @param ConfigurationInterface $processor A processor.
     */
    public function setProcessor(ConfigurationInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Processes the configuration definition.
     *
     * @param array   $data     The configuration data.
     * @param mixed   $resource A resource.
     * @param string  $type     The resource type.
     * @param boolean $require  Require processing?
     *
     * @return array The processed configuration data.
     *
     * @throws ProcessorException If the processor could not be used and it is
     *                            require that one be used.
     */
    private function process(array $data, $resource, $type, $require)
    {
        if ($this->processor) {
            if ($this->processor instanceof ProcessorInterface) {
                if ($this->processor->supports($resource, $type)) {
                    $data = $this->processor->process($data);
                } elseif ($require) {
                    throw ProcessorException::format(
                        'The resource "%s"%s is not supported by the processor.',
                        is_string($resource) ? $resource : gettype($resource),
                        $type ? " ($type)" : ''
                    );
                }
            } else {
                $processor = new Processor();
                $data = $processor->processConfiguration(
                    $this->processor,
                    $data
                );
            }
        } elseif ($require) {
            throw ProcessorException::format(
                'No processor registered to handle any resource.'
            );
        }

        return $data;
    }
}
