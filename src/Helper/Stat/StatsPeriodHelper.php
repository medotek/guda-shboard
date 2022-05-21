<?php

namespace App\Helper\Stat;

use Exception;

class StatsPeriodHelper
{
    /**
     * @throws Exception
     */
    public static function periodArray(\DateTime $from, \DateTime $to, $period) {
        $array = [];
        switch ($period) {
            case 'day':
                $array = self::getHoursForAPeriod($from, $to);
                break;
            case 'week':
                break;
            case 'month':
                break;
            case 'year':
                break;
        }

        if (!empty($array)) {
            return $array;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public static function getHoursForAPeriod(\DateTime $from, \DateTime $to) {
        // for loop by incrementing datetime by hour
        // end when date from and to are equals
        $from = new \DateTime($from->format('Y-m-d H') . ':00:00');
        $to = new \DateTime($to->format('Y-m-d H') . ':00:00');

        $array = [];
        if ($from < $to) {
            while ($from <= $to) {
                // Append the hour in array
                $array[$from->format('d-m-Y H') . 'H'] = [];
                $from->modify('+1 hour');
            }
            return $array;
        } else {
            return false;
        }
    }
}