<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 28/11/2016
 * Time: 11:21
 */

namespace Biz\Task\Strategy;


interface LearningStrategy
{
    public function canLearnTask($task);

    public function getCourseItems($courseId);
}