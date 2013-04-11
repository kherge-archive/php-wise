<?php

namespace Herrera\Wise\Util;

/**
 * Provides utilities for management arrays.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ArrayUtil
{
    /**
     * Flattens an associative array.
     *
     * @param array  $array  An array.
     * @param string $prefix A key prefix.
     * @param string $join   The key join character.
     *
     * @return array The flattened array.
     */
    public static function flatten(array $array, $prefix = '', $join = '.')
    {
        $flat = array();

        foreach ($array as $key => $value) {
            $key = $prefix ? $prefix . $join . $key : $key;

            if (is_array($value)) {
                $flat = array_merge(
                    $flat,
                    self::flatten($value, $key, $join)
                );
            } else {
                $flat[$key] = $value;
            }
        }

        return $flat;
    }
}
