<?php

namespace Herrera\Wise\Loader;

/**
 * A loader for INI files.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class IniFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return (is_string($resource)
            && ('ini' === strtolower(pathinfo($resource, PATHINFO_EXTENSION))))
            && ((null === $type) || ('ini' === $type));
    }

    /**
     * @override
     */
    protected function doLoad($file)
    {
        return parse_ini_file($file, true);
    }
}
