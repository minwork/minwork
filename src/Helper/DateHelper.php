<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Pack of useful date related functions
 *
 * @author Christopher Kalkhoff
 *        
 */
class DateHelper
{

    const FORMAT_DATETIME = 'Y-m-d H:i:s';

    const FORMAT_DATE = 'Y-m-d';

    const FORMAT_TIME = 'H:i:s';

    const EMPTY_DATE = '0000-00-00';
    
    const EMPTY_TIME = '00:00:00';
    
    const EMPTY_DATETIME = self::EMPTY_DATE . ' ' . self::EMPTY_TIME;

    /**
     * Returns current date
     *
     * @param bool $withTime            
     * @return string
     */
    public static function now($withTime = true): string
    {
        return $withTime ? date(self::FORMAT_DATETIME) : date(self::FORMAT_DATE);
    }

    /**
     * Check if date is empty (0000-00-00 [00:00:00])
     *
     * @param string $date            
     */
    public static function isEmpty(string $date): bool
    {
        return $date === self::EMPTY_DATE || strtotime($date) <= 0;
    }

    /**
     * Add specified amount of days to date string
     *
     * @param int $days            
     * @param string|null $date Default - current date
     * @return string
     */
    public static function addDays(int $days, ?string $date = null): string
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("P{$days}D"));
        return $curDate->format(self::FORMAT_DATETIME);
    }

    /**
     * Add specified amount of hours to date string
     *
     * @param int $hours            
     * @param string|null $date Default - current date
     * @return string
     */
    public static function addHours(int $hours, ?string $date = null): string
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("PT{$hours}H"));
        return $curDate->format(self::FORMAT_DATETIME);
    }

    /**
     * Add specified amount of minutes to date string
     *
     * @param int $minutes            
     * @param string|null $date Default - current date
     * @return string
     */
    public static function addMinutes(int $minutes, ?string $date = null): string
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("PT{$minutes}M"));
        return $curDate->format(self::FORMAT_DATETIME);
    }
    
    /**
     * Add specified amount of seconds to date string
     *
     * @param int $seconds
     * @param string|null $date Default - current date
     * @return string
     */
    public static function addSeconds(int $seconds, ?string $date = null): string
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("PT{$seconds}S"));
        return $curDate->format(self::FORMAT_DATETIME);
    }
    
    /**
     * Get amount of seconds until specified date
     * @param string|null $date Default - current date
     * @return int
     */
    public static function secondsUntil(?string $date = null): int
    {
        return (new \DateTime($date))->getTimestamp() - (new \DateTime())->getTimestamp();
    }
}