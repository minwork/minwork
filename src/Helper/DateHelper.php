<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

use DateInterval;
use DateTime;

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
     * Reformat date string according to specified params
     * 
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function reformat(string $date, string $format = self::FORMAT_DATETIME)
    {
        return date($format, strtotime($date));
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
     * @throws \Exception
     * @throws \Exception
     */
    public static function addDays(int $days, ?string $date = null, string $format = self::FORMAT_DATETIME): string
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("P{$days}D"));
        return $curDate->format($format);
    }

    /**
     * Add specified amount of hours to date string
     *
     * @param int $hours
     * @param string|null $date Default - current date
     * @return string
     * @throws \Exception
     * @throws \Exception
     */
    public static function addHours(int $hours, ?string $date = null, string $format = self::FORMAT_DATETIME): string
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("PT{$hours}H"));
        return $curDate->format($format);
    }

    /**
     * Add specified amount of minutes to date string
     *
     * @param int $minutes
     * @param string|null $date Default - current date
     * @return string
     * @throws \Exception
     * @throws \Exception
     */
    public static function addMinutes(int $minutes, ?string $date = null, string $format = self::FORMAT_DATETIME): string
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("PT{$minutes}M"));
        return $curDate->format($format);
    }

    /**
     * Add specified amount of seconds to date string
     *
     * @param int $seconds
     * @param string|null $date Default - current date
     * @return string
     * @throws \Exception
     * @throws \Exception
     */
    public static function addSeconds(int $seconds, ?string $date = null, string $format = self::FORMAT_DATETIME): string
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("PT{$seconds}S"));
        return $curDate->format($format);
    }

    /**
     * Substract specified amount of seconds from date string
     *
     * @param int $seconds
     * @param string|null $date Default - current date
     * @return string
     * @throws \Exception
     * @throws \Exception
     */
    public static function subSeconds(int $seconds, ?string $date = null, string $format = self::FORMAT_DATETIME): string
    {
        $curDate = new DateTime($date);
        $curDate->sub(new DateInterval("PT{$seconds}S"));
        return $curDate->format($format);
    }

    /**
     * Get amount of seconds until specified date
     *
     * @param string $date
     * @return int
     * @throws \Exception
     */
    public static function secondsUntil(string $date): int
    {
        return (new DateTime($date))->getTimestamp() - (new DateTime())->getTimestamp();
    }

    /**
     * Get amount of seconds elapsed since specified date
     *
     * @param string $date
     * @return int
     * @throws \Exception
     */
    public static function secondsSince(string $date): int
    {
        return (new DateTime())->getTimestamp() - (new DateTime($date))->getTimestamp();
    }

    /**
     * Extract time part of datetime string and return it in specified format
     *
     * @param string $date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function extractTime(string $date, string $format = self::FORMAT_TIME): string
    {
        return (new DateTime($date))->format($format);
    }

    /**
     * Extract date part of datetime string and return it in specified format
     *
     * @param string $date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function extractDate(string $date, string $format = self::FORMAT_DATE): string
    {
        return (new DateTime($date))->format($format);
    }
}