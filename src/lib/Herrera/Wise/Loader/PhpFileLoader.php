<?php

namespace Herrera\Wise\Loader;

/**
 * A loader for PHP files.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PhpFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return (is_string($resource)
            && ('php' === strtolower(pathinfo($resource, PATHINFO_EXTENSION))))
            && ((null === $type) || ('php' === $type));
    }

    /**
     * @override
     */
    protected function doLoad($file)
    {
        /** @noinspection PhpIncludeInspection */
        return require $file;
    }
}
