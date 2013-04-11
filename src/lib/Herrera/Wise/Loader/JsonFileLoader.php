<?php

namespace Herrera\Wise\Loader;

use Herrera\Json\Json as Parser;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * A loader for JSON files.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class JsonFileLoader extends AbstractFileLoader
{
    /**
     * The JSON parser.
     *
     * @var Parser
     */
    private $json;

    /**
     * @override
     */
    public function __construct(FileLocatorInterface $locator)
    {
        parent::__construct($locator);

        $this->json = new Parser();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return (is_string($resource)
            && ('json' === strtolower(pathinfo($resource, PATHINFO_EXTENSION))))
            && ((null === $type) || ('json' === $type));
    }

    /**
     * @override
     */
    protected function doLoad($file)
    {
        return $this->json->decodeFile($file, true);
    }
}
