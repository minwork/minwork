<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Pack of usefull array functions
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class ArrayHelper
{

    /**
     * Convert keys indicator to array of keys
     * 
     * @param mixed $keys
     *            Example of valid values: <pre>
     *            0
     *            'key'
     *            'key1.key2.key3'
     *            ['key1', 'key2', 'key3']
     *            object(stdClass) { 
     *              ['prop1']=> 'key1',
     *              ['prop2']=> 'key2',
     *            }
     *            </pre>
     * @return array
     */
    protected static function prepareKeysArray($keys): array
    {
        if (is_string($keys)) {
            return explode('.', $keys);
        }
        return self::forceArray($keys);
    }

    /**
     * Gently get nested element of an array
     * 
     * @param array $array
     *            Array to get element from
     * @param mixed $keys
     *            Keys indicator
     * @see ArrayHelper::prepareKeysArray
     * @return NULL|mixed
     */
    public static function getNestedElement(array $array, $keys)
    {
        $keys = self::prepareKeysArray($keys);
        foreach ($keys as $key) {
            if (! is_array($array)) {
                return null;
            }
            
            if (array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return null;
            }
        }
        return $array;
    }

    /**
     * Make variable an array (unless it's null)
     *
     * @param mixed $var            
     */
    public static function forceArray($var)
    {
        if (! is_array($var)) {
            if (is_object($var)) {
                return $var instanceof \ArrayAccess ? $var : [
                    $var
                ];
            } elseif (is_null($var)) {
                return $var;
            } else {
                return [
                    $var
                ];
            }
        }
        return $var;
    }

    /**
     * Handle multidimensional array access using array of keys
     *
     * @param array $array            
     * @param array $keys            
     * @param mixed $value            
     */
    public static function handleElementByKeys(array &$array, array $keys, $value = null)
    {
        $tmp = &$array;
        while (count($keys) > 0) {
            $key = array_shift($keys);
            if (! is_array($tmp)) {
                if (is_null($value)) {
                    return null;
                } else {
                    $tmp = [];
                }
            }
            if (! isset($tmp[$key]) && is_null($value)) {
                return null;
            }
            $tmp = &$tmp[$key];
        }
        if (is_null($value)) {
            return $tmp;
        } else {
            $tmp = $value;
            return true;
        }
    }

    /**
     * Recursively checks if all of array values match empty condition
     *
     * @param array $array            
     * @return boolean
     */
    public static function isEmpty($array)
    {
        if (is_array($array)) {
            foreach ($array as $v) {
                if (! self::isEmpty($v)) {
                    return false;
                }
            }
        } elseif (! empty($array)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get even values of array
     *
     * @param array $array            
     * @return array
     */
    public static function evenValues($array)
    {
        $actualValues = array_values($array);
        $values = array();
        for ($i = 0; $i <= count($array) - 1; $i += 2) {
            $values[] = $actualValues[$i];
        }
        return $values;
    }

    /**
     * Get odd values of array
     *
     * @param array $array            
     * @return array
     */
    public static function oddValues($array)
    {
        $actualValues = array_values($array);
        $values = array();
        if (count($actualValues) > 1) {
            for ($i = 1; $i <= count($array) - 1; $i += 2) {
                $values[] = $actualValues[$i];
            }
        }
        return $values;
    }

    /**
     * Check if array is associative<br>
     * If strict option is disabled function will match any array that doesn't contain integer keys
     *
     * @param array $array            
     * @return boolean
     */
    public static function isAssoc(array $array, $strict = false): bool
    {
        if ($strict) {
            return array_keys($array) !== range(0, count($array) - 1);
        } else {
            foreach (array_keys($array) as $key) {
                if (! is_int($key)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Check if every array element is array
     *
     * @param array $array            
     * @return bool
     */
    public static function isArrayOfArrays(array $array): bool
    {
        foreach ($array as $el) {
            if (! is_array($el)) {
                return false;
            }
        }
        return true;
    }
}