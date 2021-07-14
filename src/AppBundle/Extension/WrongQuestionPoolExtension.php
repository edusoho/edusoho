<?php

namespace AppBundle\Extension;

use Biz\WrongBook\Pool\ClassroomPool;
use Biz\WrongBook\Pool\CoursePool;
use Biz\WrongBook\Pool\ItemBankExercisePool;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class WrongQuestionPoolExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pools = $this->getWrongQuestionPools();
        /*
         * pool ServiceName: wrong_question.course_pool、 wrong_question.classroom_pool、 wrong_question.exercise_pool
         */
        foreach ($pools as $poolName => $pool) {
            $pimple['wrong_question.'.$poolName.'_pool'] = static function ($biz) use ($pool) {
                return new $pool['class']($biz);
            };
        }
    }

    public function getWrongQuestionPools()
    {
        return [
            'course' => [
                'class' => CoursePool::class,
            ],
            'classroom' => [
                'class' => ClassroomPool::class,
            ],
            'exercise' => [
                'class' => ItemBankExercisePool::class,
            ],
        ];
    }
}
