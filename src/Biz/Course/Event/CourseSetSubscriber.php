<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\Review\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'courseSet.maxRate.update' => 'onCourseSetMaxRateUpdate',
            'courseSet.recommend' => 'onCourseSetRecommend',
            'courseSet.recommend.cancel' => 'onCourseSetCancelRecommend',
            'course-set.update' => 'onCourseSetUpdate',
            'course-set.unlock' => 'onCourseSetUnlock',
            'courseSet.courses.sort' => 'onCourseSetCoursesSort',
            'course.publish' => 'onCourseStatusChange',
            'course.close' => 'onCourseStatusChange',
            'review.create' => 'onReviewChanged',
            'review.delete' => 'onReviewChanged',
        ];
    }

    public function onReviewChanged(Event $event)
    {
        $review = $event->getSubject();
        if (empty($review['targetId'])) {
            return true;
        }

        $goods = $this->getGoodsService()->getGoods($review['targetId']);
        if ('course' != $goods['type']) {
            return true;
        }

        $product = $this->getProductService()->getProduct($goods['productId']);
        if (!empty($product)) {
            $courseSet = $this->getCourseSetService()->getCourseSet($product['targetId']);
            if (!empty($courseSet)) {
                $reviewCount = $this->getReviewService()->countReviews([
                    'targetId' => $goods['id'],
                    'targetType' => 'goods',
                ]);
                $this->getCourseSetService()->updateCourseSetRatingNum($courseSet['id'], [
                    'ratingNum' => $reviewCount,
                ]);
            }
        }
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
        $this->getChapterDao()->update(['courseId' => $courseSet['defaultCourseId']], ['copyId' => 0]);
        $this->getTaskDao()->update(['courseId' => $courseSet['defaultCourseId']], ['copyId' => 0]);
        $this->getActivityDao()->update(['fromCourseId' => $courseSet['defaultCourseId'], 'excludeMediaType' => 'live'], ['copyId' => 0]);
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Review:ReviewService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->getBiz()->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->getBiz()->service('Product:ProductService');
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

    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    protected function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}
