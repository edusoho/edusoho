<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class ExerciseConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $exercise = ServiceKernel::instance()->createService('Homework:Homework.ExerciseService')->getExercise($id);
        if (empty($exercise)) {
            throw new \Exception('exercise not found');
        }
        return $exercise;
    }

}

