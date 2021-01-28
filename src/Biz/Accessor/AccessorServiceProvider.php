<?php

namespace Biz\Accessor;

use Biz\Classroom\Accessor\JoinClassroomAccessor;
use Biz\Classroom\Accessor\JoinClassroomMemberAccessor;
use Biz\Classroom\Accessor\LearnClassroomAccessor;
use Biz\Classroom\Accessor\LearnClassroomMemberAccessor;
use Biz\Course\Accessor\JoinCourseAccessor;
use Biz\Course\Accessor\JoinCourseMemberAccessor;
use Biz\Course\Accessor\LearnCourseAccessor;
use Biz\Course\Accessor\LearnCourseMemberAccessor;
use Biz\Course\Accessor\LearnCourseTaskAccessor;
use Biz\ItemBankExercise\Accessor\JoinExerciseAccessor;
use Biz\ItemBankExercise\Accessor\JoinExerciseMemberAccessor;
use Biz\ItemBankExercise\Accessor\LearnExerciseAccessor;
use Biz\ItemBankExercise\Accessor\LearnExerciseMemberAccessor;
use Pimple\Container;
use  Pimple\ServiceProviderInterface;

class AccessorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['course.join_chain'] = function () use ($biz) {
            $joinCourseChain = new AccessorChain();
            $joinCourseChain->add(new JoinCourseAccessor($biz), 100);
            $joinCourseChain->add(new JoinCourseMemberAccessor($biz), 20);

            return $joinCourseChain;
        };

        $biz['course.learn_chain'] = function () use ($biz) {
            $learnCourseChain = new AccessorChain();
            $learnCourseChain->add(new LearnCourseAccessor($biz), 100);
            $learnCourseChain->add(new LearnCourseMemberAccessor($biz), 20);

            return $learnCourseChain;
        };

        $biz['classroom.join_chain'] = function () use ($biz) {
            $joinClassroomChain = new AccessorChain();
            $joinClassroomChain->add(new JoinClassroomAccessor($biz), 100);
            $joinClassroomChain->add(new JoinClassroomMemberAccessor($biz), 20);

            return $joinClassroomChain;
        };

        $biz['classroom.learn_chain'] = function () use ($biz) {
            $learnClassroomChain = new AccessorChain();
            $learnClassroomChain->add(new LearnClassroomAccessor($biz), 100);
            $learnClassroomChain->add(new LearnClassroomMemberAccessor($biz), 20);

            return $learnClassroomChain;
        };

        $biz['course.task.learn_chain'] = function () use ($biz) {
            $courseTaskLearnChain = new AccessorChain();
            $courseTaskLearnChain->add(new LearnCourseTaskAccessor($biz), 100);

            return $courseTaskLearnChain;
        };

        $biz['item_bank_exercise.join_chain'] = function () use ($biz) {
            $joinItemBankExerciseChain = new AccessorChain();
            $joinItemBankExerciseChain->add(new JoinExerciseAccessor($biz), 100);
            $joinItemBankExerciseChain->add(new JoinExerciseMemberAccessor($biz), 20);

            return $joinItemBankExerciseChain;
        };

        $biz['item_bank_exercise.learn_chain'] = function () use ($biz) {
            $learnItemBankExerciseChain = new AccessorChain();
            $learnItemBankExerciseChain->add(new LearnExerciseAccessor($biz), 100);
            $learnItemBankExerciseChain->add(new LearnExerciseMemberAccessor($biz), 20);

            return $learnItemBankExerciseChain;
        };
    }
}
