<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetStatisticsSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.review.add' => 'onReviewNumberChange',
            'course.review.update' => 'onReviewNumberChange',
            'course.review.delete' => 'onReviewNumberChange',
        );
    }

    public function onReviewNumberChange(Event $event)
    {
        $review = $event->getSubject();

        $this->getCourseSetService()->updateCourseSetStatistics($review['courseSetId'], array(
            'ratingNum',
        ));
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
