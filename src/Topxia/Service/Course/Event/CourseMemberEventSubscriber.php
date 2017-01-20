<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseMemberEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.update'        => 'onCourseUpdate',
            'course.lesson.create' => 'onCourseLessonCreate',
            'course.lesson.delete' => 'onCourseLessonDelete',
            'course.lesson_finish' => 'onLessonFinish'
        );
    }

    public function onCourseUpdate(Event $event)
    {
        $context      = $event->getSubject();
        $sourceCourse = $context['sourceCourse'];
        $course       = $context['course'];

        if ($sourceCourse['serializeMode'] != $course['serializeMode']) {
            if ($course['serializeMode'] == 'serialize') {
                $conditions = array(
                    'courseId'  => $course['id'],
                    'isLearned' => 1
                );
                $this->getCourseMemberService()->updateMembers($conditions, array('isLearned' => 0));
            } elseif ($sourceCourse['serializeMode'] == 'serialize' && $course['serializeMode'] != 'serialize') {
                $conditions = array(
                    'courseId'              => $course['id'],
                    'learnedNumGreaterThan' => $course['lessonNum']
                );
                $this->getCourseMemberService()->updateMembers($conditions, array('isLearned' => 1));
            }
        }
    }

    public function onCourseLessonCreate(Event $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        $course = $this->getCourseService()->getCourse($lesson['courseId']);

        if ($course['serializeMode'] != 'serialize') {
            $conditions = array(
                'courseId'           => $course['id'],
                'isLearned'          => 1,
                'learnedNumLessThan' => $course['lessonNum']
            );
            $this->getCourseMemberService()->updateMembers($conditions, array('isLearned' => 0));
        }
    }

    public function onCourseLessonDelete(Event $event)
    {
        $context  = $event->getSubject();
        $lesson   = $context['lesson'];
        $courseId = $context['courseId'];

        $course = $this->getCourseService()->getCourse($lesson['courseId']);

        if ($course['serializeMode'] != 'serialize') {
            $conditions = array(
                'courseId'              => $course['id'],
                'learnedNumGreaterThan' => $course['lessonNum']
            );
            $updateFields = array(
                'isLearned'  => 1,
                'learnedNum' => $course['lessonNum']
            );

            $this->getCourseMemberService()->updateMembers($conditions, $updateFields);
        }
    }

    public function onLessonFinish(Event $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $learn  = $event->getArgument('learn');

        if ($course['status'] != 'published') {
            return false;
        }

        $conditions = array(
            'userId'   => $learn['userId'],
            'courseId' => $learn['courseId'],
            'status'   => 'finished'
        );
        $userLearnCount = $this->getCourseService()->searchLearnCount($conditions);
        $userLearns     = $this->getCourseService()->searchLearns(
            $conditions,
            array('finishedTime', 'DESC'),
            0, $userLearnCount
        );

        $totalCredits = $this->getCourseService()->sumLessonGiveCreditByLessonIds(ArrayToolkit::column($userLearns, 'lessonId'));

        $memberFields               = array();
        $memberFields['learnedNum'] = $userLearnCount;

        if ($course['serializeMode'] != 'serialize') {
            $memberFields['isLearned']    = $memberFields['learnedNum'] >= $course['lessonNum'] ? 1 : 0;
            $memberFields['finishedTime'] = $memberFields['isLearned'] ? time() : 0;
        }

        $memberFields['credit']        = $totalCredits;
        $memberFields['lastLearnTime'] = time();

        $courseMember = $this->getCourseMemberService()->getCourseMember($course['id'], $learn['userId']);
        $this->getCourseMemberService()->updateMember($courseMember['id'], $memberFields);

        $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);

        if (!empty($classroom)) {
            $this->getClassroomService()->updateLearndNumByClassroomIdAndUserId($classroom['classroomId'], $learn['userId']);
        }
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:ClassroomService');
    }

    protected function getCourseMemberService()
    {
        return ServiceKernel::instance()->createService('Course:MemberService');
    }
}
