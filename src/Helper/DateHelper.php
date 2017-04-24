<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Usefull date related functions
 * 
 * @author Christopher Kalkhoff
 *        
 */
class DateHelper
{

    const FORMAT_DATETIME = "Y-m-d H:i:s";

    const FORMAT_DATE = "Y-m-d";

    const FORMAT_TIME = "H:i:s";

    const EMPTY_DATE = "0000-00-00";

    /**
     * Returns current date
     *
     * @param bool $withTime            
     * @return string
     */
    public static function now($withTime = true)
    {
        return $withTime ? date(self::FORMAT_DATETIME) : date(self::FORMAT_DATE);
    }

    /**
     * Check if date is empty (0000-00-00 [00:00:00])
     * 
     * @param string $date            
     */
    public static function isEmpty(string $date)
    {
        return $date == self::EMPTY_DATE || strtotime($date) <= 0;
    }

    /**
     * Add specified amount of days to date string
     * 
     * @param string $date            
     * @param int $days            
     * @return \DateTime
     */
    public static function addDays(string $date, int $days)
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("P{$days}D"));
        return $curDate;
    }

    /**
     * Add specified amount of hours to date string
     * 
     * @param string $date            
     * @param int $hours            
     * @return \DateTime
     */
    public static function addHours(string $date, int $hours)
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("PT{$hours}H"));
        return $curDate;
    }

    /**
     * Add specified amount of minutes to date string
     * 
     * @param string $date            
     * @param int $minutes            
     * @return \DateTime
     */
    public static function addMinutes(string $date, int $minutes)
    {
        $curDate = new \DateTime($date);
        $curDate->add(new \DateInterval("PT{$minutes}M"));
        return $curDate;
    }
}