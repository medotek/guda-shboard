<?php

namespace App\Builder;

use App\Helper\Stat\StatsPeriodHelper;
use DateTime;
use Exception;

class StatsBuilder
{
    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function prepareStats(DateTime $from, DateTime $to, string $period, array $data)
    {
        if (empty($data)) {
            return false;
        }

        $statsKeys = [];
        switch ($period) {
            case 'day':
                if ($statsKeys = StatsPeriodHelper::periodArray($from, $to, $period)) {
                    break;
                }
                return false;
            case 'week':
                break;
            case 'month':
                break;
            case 'year':
                break;
        }

        // For HoyoUserStats
        foreach ($data as $item) {
            /** @var DateTime $itemDate */
            $itemDate = $item['date'];
            if ($itemDate instanceof DateTime) {
                // Verify if there is other values
                $statsKeys[$itemDate->format('d-m-Y H') . 'H'][] = $item;
            }
        }

        $newStats = [];
        // Calcul avg
        foreach ($statsKeys as $key => $stats) {
            if (count($stats) == 1) {
                unset($stats[0]['date']);
                $newStats[$key] = $stats[0];
            } else if (count($stats) > 1){
                $avgStat = [];
                foreach ($stats as $stat) {
                    unset($stat['date']);
                    if (is_array($stat)) {
                        foreach ($stat as $kStat => $vStat) {
                            $avgStat[$key][$kStat][] = $vStat;
                        }
                    }
                }
                // avg value for a key
                $avgStatKeys = array_keys($avgStat[$key]);
                foreach ($avgStatKeys as $avgStatKey) {
                    $avgStat[$key][$avgStatKey] = array_sum($avgStat[$key][$avgStatKey]) / count($avgStat[$key][$avgStatKey]);
                }
                $newStats[$key] = $avgStat[$key];
            } else {
                $newStats[$key] = '';
            }
        }

        return $newStats;
    }
}