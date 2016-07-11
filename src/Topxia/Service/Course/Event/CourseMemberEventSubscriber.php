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

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        $course = $this->getCourseService()->getCourse($lesson['courseId']);

        $membersLearned = $this->getCourseService()->searchMembers(
            array(
                'courseId'  => $course['id'],
                'isLearned' => 1
            ),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if ($membersLearned && $course['serializeMode'] != 'serialize') {
            foreach ($membersLearned as $key => $member) {
                if ($member['learnedNum'] < $course['lessonNum']) {
                    $memberFields = array(
                        'isLearned' => 0
                    );

                    $this->getCourseService()->updateCourseMember($member['id'], $memberFields);
                }
            }
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $lesson   = $context['lesson'];
        $courseId = $context['courseId'];

        $course = $this->getCourseService()->getCourse($lesson['courseId']);

        $membersLearned = $this->getCourseService()->searchMembers(
            array(
                'courseId'              => $course['id'],
                'learnedNumGreaterThan' => $course['lessonNum']
            ),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if ($membersLearned && $course['serializeMode'] != 'serialize') {
            foreach ($membersLearned as $key => $member) {
                $memberFields = array(
                    'isLearned'  => 1,
                    'learnedNum' => $course['lessonNum']
                );

                $this->getCourseService()->updateCourseMember($member['id'], $memberFields);
            }
        }
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];
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
