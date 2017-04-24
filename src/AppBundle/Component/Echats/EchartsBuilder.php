<?php
/**
 * User: Edusoho V8
 * Date: 26/10/2016
 * Time: 12:05.
 */

namespace AppBundle\Component\Echats;

use AppBundle\Common\ArrayToolkit;

class EchartsBuilder
{
    //创建默认的折线图数据
    public static function createLineDefaultData($days, $format, $series)
    {
        $lineChatsData = array();
        $lineChatsData['xAxis']['date'] = self::generateDateRange($days, $format);

        $zeroAnalysis = self::generateZeroData($lineChatsData['xAxis']['date']);
        array_walk($series, function (&$data, $key) use ($zeroAnalysis) {
            $data = ArrayToolkit::index($data, 'date');
            $data = array_merge($zeroAnalysis, $data);

            $data = EchartsBuilder::arrayValueRecursive($data, 'count');
        });
        $lineChatsData['series'] = $series;

        return $lineChatsData;
    }

    //饼状图
    public function createPieDefaultData()
    {
    }

    //柱状图
    public static function createBarDefaultData($days, $format, $series)
    {
        $lineChatsData = array();
        $lineChatsData['xAxis']['date'] = self::generateDateRange($days, $format);
        $zeroAnalysis = self::generateZeroData($lineChatsData['xAxis']['date']);

        array_walk($series, function (&$data, $key) use ($zeroAnalysis) {
            $data = ArrayToolkit::index($data, 'date');
            $data = array_merge($zeroAnalysis, $data);

            $data = EchartsBuilder::arrayValueRecursive($data, 'count');
        });
        $lineChatsData['series'] = $series;

        return $lineChatsData;
    }

    public static function generateDateRange($days, $format = 'Y/m/d')
    {
        $dates = array();
        for ($i = $days; $i >= 0; --$i) {
            $dates[] = date($format, time() - $i * 24 * 60 * 60);
        }

        return $dates;
    }

    public static function generateZeroData($xAxis)
    {
        $zeroAnalysis = array();
        //用于填充的空模板数据
        foreach ($xAxis as $date) {
            $date = date('Y-m-d', strtotime($date));
            $zeroAnalysis[$date] = array('count' => 0, 'date' => $date);
        }

        return $zeroAnalysis;
    }

    public static function arrayValueRecursive(array $array, $key)
    {
        $val = array();
        array_walk_recursive($array, function ($v, $k) use ($key, &$val) {
            if ($k == $key) {
                array_push($val, intval($v));
            }
        });

        return count($val) > 1 ? $val : array_pop($val);
    }
}
