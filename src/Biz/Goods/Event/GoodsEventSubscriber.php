<?php

namespace Biz\Goods\Event;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Mediator\CourseSetGoodsMediator;
use Biz\Goods\Mediator\CourseSpecsMediator;
use Biz\Goods\Service\GoodsService;
use Biz\Review\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GoodsEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'classroom.course.delete' => 'onClassroomCourseDelete',
            'classroom.courses.delete' => 'onClassroomCoursesDelete',
            'review.create' => 'onReviewChanged',
            'review.delete' => 'onReviewChanged',
            'goods.delete' => 'onGoodsDelete',
        ];
    }

    public function onReviewChanged(Event $event)
    {
        $review = $event->getSubject();

        if (!isset($review['targetId'])) {
            return true;
        }

        $goods = $this->getGoodsService()->getGoods($review['targetId']);

        if (empty($goods)) {
            return true;
        }

        $reviewCount = $this->getReviewService()->countReviews([
            'targetId' => $goods['id'],
            'targetType' => 'goods',
        ]);

        $this->getGoodsService()->updateGoods($goods['id'], ['ratingNum' => $reviewCount]);
    }

    public function onClassroomCourseDelete(Event $event)
    {
        $classroom = $event->getSubject();
        $classroomId = $classroom['id'];
        $defaultCourseId = $event->getArgument('deleteCourseId');
        $course = $this->getCourseService()->getCourse($defaultCourseId);
        if (empty($course)) {
            return;
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $this->getCourseSetGoodsMediator()->onCreate($courseSet);
        $this->getCourseSetGoodsMediator()->onUpdateNormalData($courseSet);
        $this->getCourseSpecsMediator()->onCreate($course);
        $this->getCourseSpecsMediator()->onUpdateNormalData($course);
    }

    public function onClassroomCoursesDelete(Event $event)
    {
        $defaultCourseIds = $event->getArgument('deleteCourseIds');
        $courses = $this->getCourseService()->findCoursesByIds($defaultCourseIds);
        if (empty($courses)) {
            return;
        }
        $courseSetIds = array_column($courses, 'courseSetId');
        $courseSetArray = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        foreach ($courses as $course) {
            if (isset($courseSetArray[$course['courseSetId']])) {
                $this->getCourseSetGoodsMediator()->onCreate($courseSetArray[$course['courseSetId']]);
                $this->getCourseSetGoodsMediator()->onUpdateNormalData($courseSetArray[$course['courseSetId']]);
            }
            $this->getCourseSpecsMediator()->onCreate($course);
            $this->getCourseSpecsMediator()->onUpdateNormalData($course);
        }
    }

    public function onGoodsDelete(Event $event)
    {
        $goodsId = $event->getSubject();
        $this->getReviewService()->deleteReviewsByTargetTypeAndTargetId('goods', $goodsId);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Review:ReviewService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->getBiz()->service('Goods:GoodsService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseSpecsMediator
     */
    protected function getCourseSpecsMediator()
    {
        $biz = $this->getBiz();

        return $biz['specs.mediator.course'];
    }

    /**
     * @return CourseSetGoodsMediator
     */
    protected function getCourseSetGoodsMediator()
    {
        $biz = $this->getBiz();

        return $biz['goods.mediator.course_set'];
    }
}
