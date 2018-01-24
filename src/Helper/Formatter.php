<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

use Minwork\Http\Utility\Server;

/**
 * Various string formatting functions
 *
 * @author Christopher Kalkhoff
 *        
 */
class Formatter
{
    const STRING_ENCODING = "utf-8";

    const STRING_NORMALIZED_LANG = "en_GB";

    /**
     * Get number sign in form of '1' / '-1' or '+' / '-' if $text is true 
     * @param float $number
     * @param bool $text
     * @return int|string
     */
    public static function sign(float $number, bool $text = false)
    {
        if ($text) {
            if ($number > 0) {
                return '+';
            } elseif ($number < 0) {
                return '-';
            }
            return '';
        }
        return ($number > 0) - ($number < 0);
    }
    
    /**
     * Make string float replacing any commas with dots
     *
     * @param string $string            
     */
    public static function makeFloat(string $string): float
    {
        return floatval(str_replace(",", ".", $string));
    }

    /**
     * Return $source or $value if source is empty
     *
     * @param mixed $source            
     * @param mixed $value            
     */
    public static function default($source, $value)
    {
        return empty($source) ? $value : $source;
    }

    /**
     * Make string decimal
     *
     * @param string $string            
     * @param int $decimals            
     * @return float
     */
    public static function makeDecimal(string $string, int $decimals = 0): float
    {
        return number_format(self::makeFloat($string), $decimals, '.', '');
    }

    /**
     * Normalize string to English alphabet removing all special characters and whitespaces
     *
     * @param string $string            
     * @param string $whitespaceReplacement            
     * @return string
     */
    public static function textId(string $string, string $whitespaceReplacement = '-'): string
    {
        $text = trim($string);
        $text = preg_replace('/[\s_]+/', $whitespaceReplacement, mb_strtolower($text, self::STRING_ENCODING));
        // Replace all special chars with english chars equivalent
        $curLocaleCType = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, self::STRING_NORMALIZED_LANG . '.' . self::STRING_ENCODING);
        $text = iconv(self::STRING_ENCODING, 'ASCII//TRANSLIT', $text);
        setlocale(LC_CTYPE, $curLocaleCType);
        $text = self::removeQuotes($text);
        
        return $text;
    }
    
    /**
     * Format string to proper class name (UpperCamelCase)
     * @param string $string
     * @return string
     */
    public static function className(string $string): string
    {
        return ucfirst(self::functionName($string));
    }
    
    /**
     * Format string to proper function name (camelCase)
     * @param string $string
     * @return string
     */
    public static function functionName(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords(preg_replace('/[\s\-]+/', '_', mb_strtolower($string, self::STRING_ENCODING)), '_')));
    }
    
    /**
     * Create getter method name for specified var name 
     * @param string $name
     * @return string
     */
    public static function getter(string $name): string
    {
        return 'get' . ucfirst(self::functionName($name));
    }
    
    /**
     * Create setter method name for specified var name
     * @param string $name
     * @return string
     */
    public static function setter(string $name): string
    {
        return 'set' . ucfirst(self::functionName($name));
    }
    
    /**
     * Format string to underscored name (UpperCamelCase -> upper_camel_case)
     * @param string $string
     * @return string
     */
    public static function underscoreName(string $string): string
    {
        return preg_replace_callback('/[A-Z]/', function ($match) {
            return '_' . mb_strtolower($match[0]); 
        }, lcfirst($string));
    }

    public static function removeNamespace(string $classname): string
    {
        $pos = strrpos($classname, '\\');
        return $pos === false ? $classname : substr($classname, $pos + 1);
    }
    
    /**
     * Make string readable.<br>
     * Replaces '-' and '_' with space and make first letter uppercase
     *
     * @param string $text            
     * @return string
     */
    public static function readable(string $text): string
    {
        return ucfirst(str_replace([
            '-',
            '_'
        ], ' ', $text));
    }

    /**
     * Convert any variable to compact simple string
     *
     * @param mixed $var            
     * @param bool $quote
     *            If strings should be outputed as quoted like <i>'this'</i> to distinguish them from boolean and number variables
     * @return string
     */
    public static function toString($var, bool $quote = true): string
    {
        if (is_string($var)) {
            return $quote ? "'{$var}'" : $var;
        } elseif (is_null($var)) {
            return 'null';
        } elseif (is_int($var) || is_float($var)) {
            return strval($var);
        } elseif (is_bool($var)) {
            return $var ? 'true' : 'false';
        } elseif (is_object($var)) {
            if (method_exists($var, '__toString')) {
                return strval($var);
            } else {
                return 'Object(' . get_class($var) . ')';
            }
        } elseif (is_callable($var, false, $name)) {
            return $name;
        } elseif (is_array($var)) {
            $isAssoc = ArrayHelper::isAssoc($var, true);
            $parts = [];
            foreach ($var as $k => $v) {
                if ($isAssoc) {
                    $parts[] = $k . ' => ' . self::toString($v);
                } else {
                    $parts[] = self::toString($v);
                }
            }
            return '[' . implode(', ', $parts) . ']';
        }
        return '';
    }

    /**
     * Make given string a proper URL
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param string $url            
     * @return string
     */
    public static function makeUrl(string $url): string
    {
        if (! preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = 'http' . (Server::isSecure() ? 's' : '') . "://" . $url;
        }
        return $url;
    }

    /**
     * Encode html tags with entities
     *
     * @param array|string $data            
     * @return array|string
     */
    public static function encodeHTMLData($data)
    {
        $return = $data;
        if (is_array($return)) {
            foreach ($return as $i => $w)
                if (is_array($w)) {
                    self::encodeHTMLData($return[$i]);
                } else {
                    $return[$i] = htmlentities(strval($w), ENT_QUOTES, self::STRING_ENCODING);
                }
        } else {
            $return = htmlentities(strval($return), ENT_QUOTES, self::STRING_ENCODING);
        }
        return $return;
    }

    /**
     * Decode html entities encoded by encodeHTMLData
     * 
     * @see Formatter::encodeHTMLData()
     * @param array|string $data            
     * @param string $allowedTags    
     * @return array|string        
     */
    public static function decodeHTMLData($data, string $allowedTags = '')
    {
        $return = $data;
        if (is_array($return)) {
            foreach ($return as $i => $w)
                if (is_array($w)) {
                    $return[$i] = self::encodeHTMLData($return[$i]);
                } else {
                    $return[$i] = html_entity_decode($w, ENT_QUOTES, self::STRING_ENCODING);
                    if (! empty($allowedTags)) {
                        $return[$i] = strip_tags($return[$i], $allowedTags);
                    }
                }
        } else {
            $return = html_entity_decode($return, ENT_QUOTES, self::STRING_ENCODING);
            if (! empty($allowedTags)) {
                $return = strip_tags($return, $allowedTags);
            }
        }
        return $return;
    }

    /**
     * Check if string starts with given phrase
     *
     * @param string $source            
     * @param string $phrase            
     * @param bool $caseSensitive            
     * @return bool
     */
    public static function startsWith(string $source, string $phrase, bool $caseSensitive = false): bool
    {
        $length = strlen($phrase);
        return ($caseSensitive ? (substr($source, 0, $length) === $phrase) : (mb_strtolower(substr($source, 0, $length)) === mb_strtolower($phrase)));
    }

    /**
     * Check if string ends with a given pharase
     *
     * @param string $haystack            
     * @param string $needle            
     * @param bool $caseSensitive            
     * @return bool
     */
    public static function endsWith(string $source, string $phrase, bool $caseSensitive = false): bool
    {
        $length = strlen($phrase);
        if ($length == 0) {
            return true;
        }
        
        return ($caseSensitive ? (substr($source, - $length) === $phrase) : (mb_strtolower(substr($source, - $length)) === mb_strtolower($phrase)));
    }

    /**
     * Remove trailing slash or directory separator from given string
     *
     * @param string $string            
     * @return string
     */
    public static function removeTrailingSlash(string $string): string
    {
        $return = $string;
        if (self::startsWith($return, "/") || self::startsWith($return, DIRECTORY_SEPARATOR)) {
            $return = ($tmp = substr($return, 1)) ? $tmp : '';
        }
        return $return;
    }

    /**
     * Remove ending slash or directory separator from given string
     *
     * @param string $string            
     * @return string
     */
    public static function removeLeadingSlash(string $string): string
    {
        $return = $string;
        if (self::endsWith($return, "/") || self::endsWith($return, DIRECTORY_SEPARATOR)) {
            $return = ($tmp = substr($return, 0, - 1)) ? $tmp : '';
        }
        return $return;
    }

    /**
     * Removes all types of quotes ( <b>'</b> <b>`</b> <b>"</b> ) from string
     *
     * @param string $string            
     * @return string
     */
    public static function removeQuotes(string $string): string
    {
        return str_replace([
            '\'',
            '"',
            '`'
        ], '', $string);
    }

    /**
     * Clean string using trim and strip_tags
     *
     * @param string $string            
     * @return string
     */
    public static function cleanString(string $string): string
    {
        return trim(strip_tags($string));
    }

    /**
     * Clean form or request data using strip_tags and trim<br>
     *
     * @param array|string $data            
     * @param array $filter Skip keys present in this array
     * @return array|string
     */
    public static function cleanData($data, $filter = [])
    {
        $return = $data;
        if (is_array($return)) {
            foreach ($return as $i => &$w) {
                if (is_array($w)) {
                    $w = self::cleanData($return[$i], $filter);
                } elseif (! in_array($i, ArrayHelper::forceArray($filter))) {
                    $w = self::cleanString(strval($return[$i]));
                }
            }
        } elseif (is_string($return)) {
            $return = self::cleanString($return);
        }
        return $return;
    }

    /**
     * Make supplied text inline by removing any newline char
     *
     * @param string $content            
     * @return string
     */
    public static function inline(string $text): string
    {
        // Strip newline characters.
        $text = str_replace(chr(10), " ", $text);
        $text = str_replace(chr(13), " ", $text);
        // Replace single quotes.
        $text = str_replace(chr(145), chr(39), $text);
        $text = str_replace(chr(146), chr(39), $text);
        
        return $text;
    }
}