<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'courseSet.maxRate.update' => 'onCourseSetMaxRateUpdate',
            'courseSet.recommend' => 'onCourseSetRecommend',
            'courseSet.recommend.cancel' => 'onCourseSetCancelRecommend',
            'course-set.update' => 'onCourseSetUpdate',
            'course-set.unlock' => 'onCourseSetUnlock',
            'courseSet.courses.sort' => 'onCourseSetCoursesSort',
            'course.publish' => 'onCourseStatusChange',
            'course.close' => 'onCourseStatusChange',
        );
    }

    public function onCourseStatusChange(Event $event)
    {
        $course = $event->getSubject();
        $this->getCourseSetService()->updateCourseSetDefaultCourseId($course['courseSetId']);
    }

    public function onCourseSetCoursesSort(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->getCourseSetService()->updateCourseSetDefaultCourseId($courseSet['id']);
    }

    public function onCourseSetMaxRateUpdate(Event $event)
    {
        $subject = $event->getSubject();
        $courseSet = $subject['courseSet'];
        $maxRate = $subject['maxRate'];

        $this->getCourseService()->updateMaxRateByCourseSetId($courseSet['id'], $maxRate);
    }

    public function onCourseSetRecommend(Event $event)
    {
        $courseSet = $event->getSubject();
        $fields = $event->getArguments();
        $this->getCourseService()->recommendCourseByCourseSetId($courseSet['id'], $fields);
    }

    public function onCourseSetCancelRecommend(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->getCourseService()->cancelRecommendCourseByCourseSetId($courseSet['id']);
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();

        if (!isset($courseSet['categoryId'])) {
            return;
        }

        $this->getCourseService()->updateCategoryByCourseSetId($courseSet['id'], $courseSet['categoryId']);
    }

    public function onCourseSetUnlock(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->getChapterDao()->update(array('courseId' => $courseSet['defaultCourseId']), array('copyId' => 0));
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->getBiz()->dao('Course:CourseChapterDao');
    }
}
