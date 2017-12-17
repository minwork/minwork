<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Pack of useful array functions
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class ArrayHelper
{

    /**
     * Convert any var matching exmaples showed below into array of keys
     *
     * @param mixed $keys
     *            <pre>
     *            0
     *            'key'
     *            'key1.key2.key3'
     *            ['key1', 'key2', 'key3']
     *            object(stdClass) {
     *            ['prop1']=> 'key1',
     *            ['prop2']=> 'key2',
     *            }
     *            </pre>
     * @return array
     */
    public static function getKeysArray($keys): array
    {
        if (is_string($keys)) {
            return empty($keys) ? [] : explode('.', $keys);
        }
        return is_null($keys) ? [] : array_values(self::forceArray($keys));
    }

    /**
     * Get nested element of an array or object implementing array access without triggering warning
     *
     * @param array|\ArrayAccess $array
     *            Array or object implementing array access to get element from
     * @param mixed $keys
     *            Keys indicator
     * @param mixed $default
     *            Default value if element was not found
     * @see ArrayHelper::getKeysArray
     * @return null|mixed
     */
    public static function getNestedElement($array, $keys, $default = null)
    {
        $keys = self::getKeysArray($keys);
        foreach ($keys as $key) {
            if (! is_array($array) && ! $array instanceof \ArrayAccess) {
                return $default;
            }
            if (($array instanceof \ArrayAccess && $array->offsetExists($key)) || array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return $default;
            }
        }
        return $array;
    }

    /**
     * Handle multidimensional array access using array of keys (get or set depending on $value argument)
     *
     * @see ArrayHelper::getKeysArray
     * @param array $array
     * @param mixed $keys
     *            Keys needed to access desired array element (for possible formats look at getKeysArray method)
     * @param mixed $value
     *            Value to set (if null this function will work as get)
     */
    public static function handleElementByKeys(array &$array, $keys, $value = null)
    {
        $tmp = &$array;
        $keys = self::getKeysArray($keys);
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
     * Make variable an array
     *
     * @param mixed $var
     * @return array
     */
    public static function forceArray($var): array
    {
        if (! is_array($var)) {
            if (is_object($var)) {
                return $var instanceof \ArrayAccess ? $var : [
                    $var
                ];
            } else {
                return [
                    $var
                ];
            }
        }
        return $var;
    }

    /**
     * Clone array with every object inside it
     *
     * @param array $array
     * @return array
     */
    public static function clone(array $array): array
    {
        $cloned = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $cloned[$key] = self::clone($value);
            } elseif (is_object($value)) {
                $cloned[$key] = clone $value;
            } else {
                $cloned[$key] = $value;
            }
        }
        return $cloned;
    }

    /**
     * Get random array value
     *
     * @param array $array
     * @return mixed
     */
    public static function random(array $array, int $count = 1)
    {
        if (empty($array)) {
            return null;
        }
        return $count == 1 ? $array[array_rand($array)] : array_intersect_key($array, array_flip(array_rand($array, $count) ?? []));
    }

    /**
     * Recursively check if all of array values match empty condition
     *
     * @param array $array
     * @return boolean
     */
    public static function isEmpty($array): bool
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
     * Check if array is associative
     *
     * @param array $array
     * @param bool $strict
     *            If false then this function will match any array that doesn't contain integer keys
     * @return boolean
     */
    public static function isAssoc(array $array, bool $strict = false): bool
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
     * Check if array contain only numeric values
     *
     * @param array $array
     * @return bool
     */
    public static function isNumeric(array $array): bool
    {
        return ctype_digit(implode('', $array));
    }

    /**
     * Check if array values are unique
     *
     * @param array $array
     * @return bool
     */
    public static function isUnique(array $array): bool
    {
        return array_unique(array_values($array)) === array_values($array);
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

    /**
     * Filter array by preserving only those which keys are present in $keys expression
     *
     * @param array $array
     * @param mixed $keys
     *            Look at getKeysArray function
     * @see \Minwork\Helper\ArrayHelper::getKeysArray()
     * @return array
     */
    public static function filterByKeys(array $array, $keys, bool $exclude = false): array
    {
        if (is_null($keys)) {
            return $array;
        }
        $keysArray = self::getKeysArray($keys);
        if (empty($keysArray)) {
            return $exclude ? $array : [];
        }
        return $exclude ? array_diff_key($array, array_flip($keysArray)) : array_intersect_key($array, array_flip($keysArray));
    }

    /**
     * Check if array has specified keys
     *
     * @param array $array
     * @param mixed $keys
     *            Look at getKeysArray function
     * @see \Minwork\Helper\ArrayHelper::getKeysArray()
     * @param bool $strict
     *            If array must have every key
     * @return bool
     */
    public static function hasKeys(array $array, $keys, bool $strict = false): bool
    {
        foreach (self::getKeysArray($keys) as $key) {
            if (array_key_exists($key, $array) && ! $strict) {
                return true;
            } elseif (! array_key_exists($key, $array) && $strict) {
                return false;
            }
        }
        return $strict ? true : false;
    }

    /**
     * Get even array values
     *
     * @param array $array
     * @return array
     */
    public static function evenValues(array $array): array
    {
        $actualValues = array_values($array);
        $values = array();
        for ($i = 0; $i <= count($array) - 1; $i += 2) {
            $values[] = $actualValues[$i];
        }
        return $values;
    }

    /**
     * Get odd array values
     *
     * @param array $array
     * @return array
     */
    public static function oddValues(array $array): array
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
     * Group list of objects by value returned from supplied method.<br><br>
     * <u>Example</u><br>
     * Let's say we have a list of Foo objects [Foo1, Foo2, Foo3] and all of them have method bar which return string.<br>
     * If method bar return duplicate strings then all keys will contain list of corresponding objects like this:<br>
     * <pre>
     * ['string1' => [Foo1], 'string2' => [Foo2, Foo3]]
     * </pre>
     * If flat param is equal to <i>true</i> then every object returning duplicate key will replace previous one, like:<br>
     * <pre>
     * ['string1' => Foo1, 'string2' => Foo3]
     * </pre>
     *
     * @param array $objects
     * @param string $method
     * @param bool $flat
     * @param mixed ...$args
     * @return array
     */
    public static function groupObjects(array $objects, string $method, ...$args): array
    {
        $return = [];
        
        foreach ($objects as $object) {
            if (is_object($object)) {
                $key = $object->$method(...$args);
                if (! array_key_exists($key, $return)) {
                    $return[$key] = [
                        $object
                    ];
                } else {
                    $return[$key][] = $object;
                }
            }
        }
        
        return $return;
    }

    /**
     * Filter objects array using supplied method name.<br>
     * Discard any object which method return value convertable to false
     * 
     * @param array $objects
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function filterObjects(array $objects, string $method, ...$args): array
    {
        $return = [];
        
        foreach ($objects as $key => $object) {
            if (is_object($object) && $object->$method(...$args)) {
                $return[$key] = $object;
            }
        }
        
        return $return;
    }

    /**
     * Flatten single element arrays<br>
     * Let's say we have an array like this:<br>
     * <pre>
     * ['foo' => ['bar'], 'foo2' => ['bar2', 'bar3' => ['foo4']]
     * </pre>
     * then we have result:<br>
     * <pre>
     * ['foo' => 'bar', 'foo2' => ['bar2', 'bar3' => 'foo4']]
     * </pre>
     * 
     * @param array $array
     * @return array
     */
    public static function flattenSingle(array $array): array
    {
        $return = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (count($value) === 1) {
                    $return[$key] = reset($value);
                } else {
                    $return[$key] = self::flattenSingle($value);
                }
            } else {
                $return[$key] = $value;
            }
        }
        
        return $return;
    }
}