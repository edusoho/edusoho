<?php

namespace Biz\Accessor;

use Pimple\ServiceProviderInterface;

class AccessorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['course.join_chain'] = function () use ($biz) {
            $joinCourseChain = new AccessorChain();
            $joinCourseChain->add(new JoinCourseAccessor($biz), 1);
            $joinCourseChain->add(new JoinCourseMemberAccessor($biz), 2);

            return $joinCourseChain;
        };

        $biz['course.learn_chain'] = function () use ($biz) {
            $learnCourseChain = new AccessorChain();
            $learnCourseChain->add(new LearnCourseAccessor($biz), 1);
            $learnCourseChain->add(new LearnCourseMemberAccessor($biz), 2);

            return $learnCourseChain;
        };

        $biz['classroom.join_chain'] = function () use ($biz) {
            $joinClassroomChain = new AccessorChain();
            $joinClassroomChain->add(new JoinClassroomAccessor($biz), 1);
            $joinClassroomChain->add(new JoinClassroomMemberAccessor($biz), 2);

            return $joinClassroomChain;
        };

        $biz['classroom.learn_chain'] = function () use ($biz) {
            $learnClassroomChain = new AccessorChain();
            $learnClassroomChain->add(new LearnClassroomAccessor($biz), 1);
            $learnClassroomChain->add(new LearnClassroomMemberAccessor($biz), 2);

            return $learnClassroomChain;
        };
    }
}
