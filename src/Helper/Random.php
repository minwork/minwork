<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

use Exception;
use InvalidArgumentException;

/**
 * Operations using randomness like generating random values or calculating percent chance
 *
 * @author Christopher Kalkhoff
 *        
 */
class Random
{
    /**
     * Sort chance from minimum to maximum and reverse array if no match found
     * 
     * @var string
     */
    const ALGORITHM_ASC_BOUNCE = 'ascending_bounce';

    /**
     * Sort chance from maximum to minimum and reverse array if no match found
     *
     * @var string
     */
    const ALGORITHM_DESC_BOUNCE = 'descending_bounce';
    
    /**
     * Sort chance from minimum to maximum and start from the beginning if no match found
     *
     * @var string
     */
    const ALGORITHM_ASC_REPEAT = 'ascending_repeat';
    
    /**
     * Sort chance from maximum to minimum and start from the beginning if no match found
     *
     * @var string
     */
    const ALGORITHM_DESC_REPEAT = 'descending_repeat';

    /**
     * Generate random sign (-1 or 1)
     *
     * @return int
     * @throws Exception
     */
    public static function sign(): int
    {
        do {
            $random = self::int();
        } while ($random === 0);
        
        return $random > 0 ? 1 : -1;
    }

    /**
     * Generate random string
     *
     * @param int $length
     * @return string
     * @throws Exception
     */
    public static function string(int $length): string
    {
        $string = '';
        for ($i = 1; $i <= $length; $i ++) {
            $string .= chr(self::int(32, 126));
        }
        return $string;
    }

    /**
     * Generate random text which consists of English alphabet letters and digits
     *
     * @param int $length
     * @return string
     * @throws Exception
     */
    public static function text(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxIndex = strlen($characters) - 1;
        $string = '';
        
        for ($i = 1; $i <= $length; $i++) {
            $string .= $characters[self::int(0, $maxIndex)];
        }
        
        return $string;
    }

    /**
     * Challenge given percentage chance of event against random number generator
     *
     * @param float $percent
     *            Success percent chance
     * @param int $precision
     *            Precision of comparing random value against $percent
     * @return bool If event specified by percent chance should happen or not
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function chance(float $percent, int $precision = 2): bool
    {
        if ($percent <= 0) {
            return false;
        }
        
        if ($percent >= 100) {
            return true;
        }
        
        if ($precision < 0) {
            $precision = 0;
        }
        
        return round(self::float(0, 100), $precision) <= round($percent, $precision);
    }

    /**
     * Random one of $options key (values are chances to pick corresponding key)<br>
     * Algorithm defines method of iterating $options array (see class constants for more info)
     *
     * @param array $options
     * @param string $algorithm
     * @param int $precision
     * @return NULL|mixed
     * @throws Exception
     */
    public static function option(array $options, string $algorithm = self::ALGORITHM_ASC_BOUNCE, int $precision = 2)
    {
        switch ($algorithm) {
            case self::ALGORITHM_ASC_BOUNCE:
            case self::ALGORITHM_ASC_REPEAT:
                asort($options);
                break;
                
            case self::ALGORITHM_DESC_BOUNCE:
            case self::ALGORITHM_DESC_REPEAT:
                arsort($options);
                break;
        }
        
        $return = null;
        
        while (is_null($return)) {
            foreach ($options as $option => $chance) {
                if (self::chance(floatval($chance), $precision)) {
                    $return = $option;
                    break 2;
                }
            }
            
            switch ($algorithm) {
                case self::ALGORITHM_ASC_BOUNCE:
                case self::ALGORITHM_DESC_BOUNCE:
                    $options = array_reverse($options, true);
                    break;
            }
        }
        
        return $return;
    }

    /**
     * Generate random integer number
     *
     * @param int $min
     * @param int $max
     * @return int
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function int(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        if ($min > $max) {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }
        return random_int($min, $max);
    }

    /**
     * Generate random float number
     *
     * @param float $min
     * @param float $max
     * @return float
     * @throws Exception
     * @throws Exception
     */
    public static function float(float $min = 0.0, float $max = 1.0): float
    {
        return $min + self::int($min) / PHP_INT_MAX * ($max - $min);
    }
}