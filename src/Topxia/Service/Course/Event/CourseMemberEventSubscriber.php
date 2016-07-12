<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
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

    public function onCourseUpdate(ServiceEvent $event)
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
                $this->getCourseService()->updateMembers($conditions, array('isLearned' => 0));
            } elseif ($sourceCourse['serializeMode'] == 'serialize' && $course['serializeMode'] != 'serialize') {
                $conditions = array(
                    'courseId'              => $course['id'],
                    'learnedNumGreaterThan' => $course['lessonNum']
                );
                $this->getCourseService()->updateMembers($conditions, array('isLearned' => 1));
            }
        }
    }

    public function onCourseLessonCreate(ServiceEvent $event)
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
            $this->getCourseService()->updateMembers($conditions, array('isLearned' => 0));
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
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

            $this->getCourseService()->updateMembers($conditions, $updateFields);
        }
    }

    public function onLessonFinish(ServiceEvent $event)
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
            $memberFields['isLearned'] = $memberFields['learnedNum'] >= $course['lessonNum'] ? 1 : 0;
        }

        $memberFields['credit'] = $totalCredits;

        $courseMember = $this->getCourseService()->getCourseMember($course['id'], $learn['userId']);
        $this->getCourseService()->updateCourseMember($courseMember['id'], $memberFields);
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }
}
