<?php
namespace Minwork\Helper;

class Random
{
    public static function string(int $length): string
    {
        $string = '';
        for ($i = 1; $i <= $length; $i++)
        {
            $string .= chr(self::int(32, 126));
        }
        return $string;
    }
    
    public static function chance(float $percent, int $precision = 2)
    {
        if ($percent < 0 || $percent > 100) {
            throw new \InvalidArgumentException('Percent chance must be within range <0, 100>');
        }
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be greater or equal to zero');
        }
        return round(self::float(0, 100), $precision) <= round($percent, $precision);
    }
    
    public static function int(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX)
    {
        if ($min > $max) {
            throw new \InvalidArgumentException('Minimum cannot be bigger than maximum');
        }
        return random_int($min, $max);
    }
    
    public static function float(float $min = 0.0, float $max = 1.0)
    {
        return $min + self::int() / PHP_INT_MAX * ($max - $min);
    }
}