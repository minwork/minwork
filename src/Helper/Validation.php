<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Basic validation methods
 *
 * @author Christopher Kalkhoff
 *        
 */
class Validation
{

    /**
     * If string contains any whitespace
     *
     * @param string $string            
     * @return bool
     */
    public static function hasWhitespace(string $string): bool
    {
        return (bool) preg_match('/\s/', $string);
    }

    /**
     * If string is proper email address
     *
     * @param string $email            
     * @return bool
     */
    public static function isEmail(string $email, bool $checkDomain = true): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }
        
        if ($checkDomain) {
            // Check if the domain is real
            $domain = explode("@", $email, 2);
            return ! empty($domain[1]) ? checkdnsrr($domain[1]) : false;
        }
        return true;
    }

    /**
     * If string is valid European phone number
     *
     * @param string $phone
     *            Phone number to check
     * @param string $strict
     *            If phone must have exactly 9 digits
     * @return bool
     */
    public static function isPhone(string $phone, bool $strict = false): bool
    {
        return (bool) preg_match(($strict ? '/^((\+|00)\d{1,4})?\d{9}$/' : '/^((\+|00)\d{1,4})?\d{9,}$/'), $phone);
    }

    /**
     * If given string is valid street name
     *
     * @param string $name            
     * @return bool
     */
    public static function isStreetName(string $name): bool
    {
        return (bool) preg_match('/^[\p{L} \.0-9\"\-]+$/ui', $name);
    }

    /**
     * If given string is valid city name
     *
     * @param string $name            
     * @return bool
     */
    public static function isCityName(string $name): bool
    {
        return (bool) preg_match('/^[\p{L} -]+$/ui', $name);
    }

    /**
     * If given string is valid building number
     *
     * @param string $name            
     * @return bool
     */
    public static function isBuildingNumber(string $name): bool
    {
        return (bool) preg_match('/^([0-9]+[A-Z]*[\/-]?)+$/i', $name);
    }

    /**
     * If given string is valid apartment number
     *
     * @param string $name            
     * @return bool
     */
    public static function isApartmentNumber(string $name): bool
    {
        return (bool) preg_match('/^[0-9]+[A-Z]*$/i', $name);
    }

    /**
     * If given var is not empty string or null
     *
     * @param string $var            
     * @return bool
     */
    public static function isNotEmpty($var): bool
    {
        return $var !== '' && !is_null($var);
    }

    /**
     * If string contains only letter of alphabet (and optionally space)
     *
     * @param string $string            
     * @param bool $includeSpace            
     * @return bool
     */
    public static function isAlphabeticOnly(string $string, bool $includeSpace = true): bool
    {
        $regex = $includeSpace ? '/^[\p{L} ]+$/ui' : '/^[\p{L}]+$/ui';
        return (bool) preg_match($regex, $string);
    }

    /**
     * If variable is proper timestamp
     *
     * @param mixed $timestamp            
     * @return bool
     */
    public static function isValidTimeStamp($timestamp): bool
    {
        return ((string) (int) $timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp >= ~ PHP_INT_MAX);
    }

    /**
     * Alias for DateHelper::isEmpty
     *
     * @see DateHelper::isEmpty()
     * @param string $date            
     * @return bool
     */
    public static function isDateEmpty(string $date): bool
    {
        return DateHelper::isEmpty($date);
    }

    /**
     * If variable is float (optional check of decimal digits number)
     *
     * @param mixed $value            
     * @param bool $decimal            
     * @return bool
     */
    public static function isFloat($value, bool $decimal = null): bool
    {
        $options = is_null($decimal) ? null : [
            'options' => [
                'decimal' => $decimal
            ]
        ];
        if ($options === null) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        } else {
            return filter_var($value, FILTER_VALIDATE_FLOAT, $options) !== false;
        }
    }

    /**
     * If variable is integer (optional range check)
     *
     * @param mixed $value            
     * @param int $min            
     * @param int $max            
     * @return bool
     */
    public static function isInt($value, ?int $min = null, ?int $max = null): bool
    {
        $options = [
            'options' => []
        ];
        if (! is_null($min)) {
            $options['options']['min_range'] = intval($min);
        }
        if (! is_null($max)) {
            $options['options']['max_range'] = intval($max);
        }
        if (empty($options['options'])) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        } else {
            return filter_var($value, FILTER_VALIDATE_INT, $options) !== false;
        }
    }

    /**
     * If variable is boolean
     *
     * @param mixed $value            
     * @return bool
     */
    public static function isBoolean($value): bool
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $result !== null;
    }

    /**
     * Check if given string is proper URL (works better than filter_var)
     *
     * @param string $url            
     * @return bool
     */
    public static function isUrl(string $url): bool
    {
        return (bool) preg_match("_(^|[\s.:;?\-\]<\(])(https?:\/\/[-\w;\/?:@&=+$\|\_.!~*\|'()\[\]%#,â�ş]+[\w\/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])_i", $url);
    }
}