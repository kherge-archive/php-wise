<?php

namespace Herrera\Wise\Exception;

use Exception;

/**
 * Adds functionality to the standard exception class.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractException extends Exception implements ExceptionInterface
{
    /**
     * Creates an exception using a formatted message.
     *
     * @param string $format    The message format.
     * @param mixed  $value,... A value.
     *
     * @return static The exception.
     */
    public static function format($format, $value = null)
    {
        if (1 < func_num_args()) {
            $format = vsprintf($format, array_slice(func_get_args(), 1));
        }

        return new static($format);
    }
}
