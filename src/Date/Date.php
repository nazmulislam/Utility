<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Date;

use Carbon\Carbon;
use Carbon\Exceptions\Exception;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Browser
 *
 * @author apple
 */
class Date {

    static public function weeksInMonth(int $numOfDaysInMonth) {
        $daysInWeek = 7;
        $result = $numOfDaysInMonth / $daysInWeek;
        $numberOfFullWeeks = floor($result);
        $numberOfRemaningDays = ($result - $numberOfFullWeeks) * 7;
        return 'Weeks: ' . $numberOfFullWeeks . ' -  Days: ' . $numberOfRemaningDays;
    }

    public static function isValidDate(string $date, string $format) : bool
    {
        try {
            $d = Carbon::createFromFormat(format: $format, time: $date);
            return true;
        
        } catch (Exception $e) {
            return false;

        }
        // return ($d && $d->format($format) === $date) ? true : false;
    }
    
    public static function parseDate(string $date, string $format)
    {
        return Carbon::createFromFormat(format: $format, time: $date);
    }

    public static function createDateWithSeparator(string $day, string $month, string $year, string $separator)
    {
        return "{$day}{$separator}{$month}{$separator}{$year}";
    }

}
