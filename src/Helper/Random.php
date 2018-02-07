<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Operations using randomness like generating random values or calculating percent chance
 *
 * @author Christopher Kalkhoff
 *        
 */
class Random
{

    /**
     * Generate random sign (-1 or 1)
     * 
     * @return int
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
     * @throws \InvalidArgumentException
     * @return bool If event specified by percent chance should happen or not
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
     * Generate random integer number
     *
     * @param int $min            
     * @param int $max            
     * @throws \InvalidArgumentException
     * @return int
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
     */
    public static function float(float $min = 0.0, float $max = 1.0): float
    {
        return $min + self::int($min) / PHP_INT_MAX * ($max - $min);
    }
}