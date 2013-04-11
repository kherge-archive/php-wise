<?php

namespace Herrera\Wise\Loader;

use Symfony\Component\Yaml\Yaml as Parser;

/**
 * A loader for YAML files.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class YamlFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return (is_string($resource)
            && ('yml' === strtolower(pathinfo($resource, PATHINFO_EXTENSION))))
            && ((null === $type) || ('yaml' === $type));
    }

    /**
     * @override
     */
    protected function doLoad($file)
    {
        return Parser::parse($file);
    }
}
