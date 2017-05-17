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

    const STRING_ENDING = "...";

    const STRING_ENCODING = "utf-8";

    const STRING_NORMALIZED_LANG = "en_GB";

    const HTML_TAG_START = "<";

    const HTML_TAG_END = ">";

    const HTML_ENDING = "/";

    const HTML_STYLE_ATTRIBUTE = "style";

    const DEFAULT_WHITESPACE_REPLACEMENT = "-";

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
     * Check if nested element of array exists - if it doesn't return default value<br>
     *
     * Example of usage:<br>
     * Get nested id param from $_GET with 0 as default value<br>
     * <pre>$_GET['search']['offer']['id']</pre>
     * <pre>Formatter::defaultArrayValue($_GET, ['search', 'offer', 'id'], 0)</pre>
     *
     * @see ArrayHelper::getKeysArray()
     * @param array $array
     *            Source array
     * @param mixed $keys
     *            Array keys in format compatible with ArrayHelper::getKeysArray() method
     * @param mixed $value            
     * @return mixed
     */
    public static function defaultArrayValue(array $array, $keys, $value)
    {
        $return = $array;
        foreach (ArrayHelper::getKeysArray($keys) as $key) {
            if (! is_array($return) || ! array_key_exists($key, $return)) {
                return $value;
            }
            $return = $return[$key];
        }
        return $return;
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
     * Cut string to given length
     *
     * @param string $string            
     * @param int $length            
     * @return string
     */
    public static function cutString(string $string, int $length): string
    {
        return substr($string, 0, $length) . (strlen($string) > $length ? self::STRING_ENDING : "");
    }

    /**
     * Cut string to specified length considering whole words.<br>
     * Afterwards append '...' which can link to address specified by $linkAddress
     *
     * @param string $string            
     * @param int $length            
     * @param string $linkAddress            
     */
    public static function smartCutString(string $string, int $length, string $linkAddress = ''): string
    {
        $len = mb_strlen($string, self::STRING_ENCODING);
        if ($len <= $length) {
            return $string;
        }
        $pos = strpos($string, ' ', $length);
        $string = mb_substr($string, 0, $pos, self::STRING_ENCODING);
        
        for ($i = $pos - 1; $i > 0; $i --) {
            $chr = mb_substr($string, $i, 1, self::STRING_ENCODING);
            if ($chr == self::HTML_TAG_END) {
                break;
            } else {
                if ($chr == self::HTML_TAG_START) {
                    $string = mb_substr($string, 0, $i, self::STRING_ENCODING);
                }
            }
            if ($len > $pos) {
                $string .= empty($linkAddress) ? self::STRING_ENDING : self::makeHtmlLink($linkAddress, self::STRING_ENDING);
            }
            
            // array for missing HTML tags
            $tags = [];
            
            if (($i = mb_strpos($string, self::HTML_TAG_START)) === false) {
                return $string;
            }
            
            while ($i >= 0 && $i < mb_strlen($string) && $i !== false) {
                if (($j = mb_strpos($string, self::HTML_TAG_END, $i)) === false) {
                    break;
                }
                $k = mb_strpos($string, ' ', $i);
                if ($k > $i && $k < $j) {
                    $tag = mb_substr($string, $i + 1, $k - $i - 1);
                } else {
                    $tag = mb_substr($string, $i + 1, $j - $i - 1);
                }
                $tag = strtolower($tag);
                
                if ((mb_strpos($tag, self::HTML_ENDING)) === 0) {
                    
                    $tag = mb_substr($tag, 1);
                    
                    if ($tags[count($tags) - 1] == $tag) {
                        unset($tags[count($tags) - 1]);
                    }
                } else {
                    $tags[count($tags)] = $tag;
                }
                
                $i = mb_strpos($string, self::HTML_TAG_START, $j);
            }
            
            // Add closing tags
            if (count($tags) > 0) {
                for ($i = count($tags) - 1; $i >= 0; $i --) {
                    $string .= self::HTML_TAG_START . self::HTML_ENDING . $tags[$i] . self::HTML_TAG_END;
                }
            }
            return $string;
        }
        return $string;
    }

    /**
     * Create html <a> tag
     *
     * @param string $address
     *            Link address
     * @param string $text
     *            Link text
     * @param array $attributes
     *            <a> tag attributes
     * @return string
     */
    public static function makeHtmlLink(string $address, string $text, array $attributes = []): string
    {
        return "<a href=\"{$address}\" " . (! empty($attributes) ? self::htmlAttributes($attributes) : "") . ">{$text}</a>";
    }

    /**
     * Normalize string to English alphabet removing all special characters and whitespaces
     *
     * @param string $string            
     * @param string $whitespaceReplacement            
     * @return string
     */
    public static function textId(string $string, string $whitespaceReplacement = self::DEFAULT_WHITESPACE_REPLACEMENT): string
    {
        $text = trim($string);
        $text = preg_replace('/\s+/', $whitespaceReplacement, mb_strtolower($text, self::STRING_ENCODING));
        // Replace all special chars with english chars equivalent
        $curLocaleCType = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, self::STRING_NORMALIZED_LANG . '.' . self::STRING_ENCODING);
        $text = iconv(self::STRING_ENCODING, 'ASCII//TRANSLIT', $text);
        setlocale(LC_CTYPE, $curLocaleCType);
        $text = self::removeQuotes($text);
        
        return $text;
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
        } elseif (is_object($var)) {
            if (method_exists($var, '__toString')) {
                return strval($var);
            } else {
                return 'Object(' . get_class($var) . ')';
            }
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
     */
    public static function decodeHTMLData($data, string $allowedTags = ''): string
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
     * @param string $haystack            
     * @param string $needle            
     * @param bool $caseSensitive            
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle, bool $caseSensitive = false): bool
    {
        $length = strlen($needle);
        return ($caseSensitive ? (substr($haystack, 0, $length) === $needle) : (mb_strtolower(substr($haystack, 0, $length)) === mb_strtolower($needle)));
    }

    /**
     * Check if string ends with a given pharase
     *
     * @param string $haystack            
     * @param string $needle            
     * @param bool $caseSensitive            
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle, bool $caseSensitive = false): bool
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        
        return ($caseSensitive ? (substr($haystack, - $length) === $needle) : (mb_strtolower(substr($haystack, - $length)) === mb_strtolower($needle)));
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
     * @param array $filter            
     * @return array
     */
    public static function cleanData($data, array $filter = [])
    {
        $return = $data;
        if (is_array($return)) {
            foreach ($return as $i => &$w) {
                if (is_array($w)) {
                    $w = self::cleanData($return[$i]);
                } elseif (! array_key_exists($i, $filter)) {
                    $w = self::cleanString($return[$i]);
                }
            }
        } elseif (is_string($return)) {
            $return = self::cleanString($return);
        }
        return $return;
    }

    /**
     * Create HTML attributes from given array.<br>
     * This function doesn't check if given attribute names are correct.
     *
     * @param array $array            
     * @return string
     */
    public static function htmlAttributes(array $array): string
    {
        $return = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_bool($value)) {
                    if ($value === true) {
                        $return .= ' ' . $key;
                    }
                } elseif ($key == self::HTML_STYLE_ATTRIBUTE && is_array($value)) {
                    $return .= ' ' . $key . '="';
                    foreach ($value as $k => $v) {
                        $return .= $k . ':' . $v . '; ';
                    }
                    $return .= '"';
                } else {
                    $return .= ' ' . $key . '="' . $value . '"';
                }
            }
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