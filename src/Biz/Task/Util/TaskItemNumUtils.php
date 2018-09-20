<?php

namespace Biz\Task\Util;

class TaskItemNumUtils
{
    /**
     * 同一课时下，任务序号都是从1开始
     */
    public static function resetNum($items)
    {
        $lessonTaskNums = array(); //key 为 lessonId, value为 taskNum

        foreach ($items as &$item) {
            if ('task' == $item['itemType'] && empty($item['isSingleTaskLesson'])) {
                if (isset($lessonTaskNums[$item['categoryId']])) {
                    ++$lessonTaskNums[$item['categoryId']];
                } else {
                    $lessonTaskNums[$item['categoryId']] = 1;
                }

                $item['number'] = $lessonTaskNums[$item['categoryId']];
            }
        }

        return $items;
    }
}
