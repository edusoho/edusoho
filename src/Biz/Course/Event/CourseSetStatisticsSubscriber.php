<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetStatisticsSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.review.add' => 'onReviewNumberChange',
            'course.review.update' => 'onReviewNumberChange',
            'course.review.delete' => 'onReviewNumberChange',

            'review.create' => 'onReviewChange',
            'review.update' => 'onReviewChange',
            'review.delete' => 'onReviewChange',
        ];
    }

    public function onReviewNumberChange(Event $event)
    {
        $review = $event->getSubject();

        $this->getCourseSetService()->updateCourseSetStatistics($review['courseSetId'], [
            'ratingNum',
        ]);
    }

    public function onReviewChange(Event $event)
    {
        $review = $event->getSubject();

        if ('course' == $review['targetType']) {
            $course = $this->getCourseService()->getCourse($review['targetId']);
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], [
                'courseRatingNum',
            ]);
        }
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Course:ReviewService');
    }
}
