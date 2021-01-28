<?php

namespace Biz\Classroom\Event;

use AppBundle\Common\StringToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Review\Service\ReviewService;
use Biz\Taxonomy\TagOwnerManager;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'classroom.delete' => 'onClassroomDelete',
            'classroom.course.create' => 'onClassroomCourseChange',
            'classroom.course.delete' => 'onClassroomCourseChange',
            'classroom.course.update' => 'onClassroomCourseChange',

            'review.create' => 'onReviewChanged',
            'review.update' => 'onReviewChanged',
            'review.delete' => 'onReviewChanged',
        ];
    }

    public function onClassroomDelete(Event $event)
    {
        $classroom = $event->getSubject();
        $tagOwnerManager = new TagOwnerManager('classroom', $classroom['id']);
        $tagOwnerManager->delete();
    }

    public function onClassroomCourseChange(Event $event)
    {
        $classroom = $event->getSubject();
        $classroomId = $classroom['id'];
        $courseNum = $this->getClassroomService()->countCoursesByClassroomId($classroomId);
        $taskNum = $this->getClassroomService()->countCourseTasksByClassroomId($classroomId);

        $fields = ['courseNum' => $courseNum, 'lessonNum' => $taskNum];
        $this->getClassroomService()->updateClassroom($classroomId, $fields);
        $this->getClassroomService()->updateClassroomTeachers($classroomId);
    }

    public function onReviewChanged(Event $event)
    {
        $review = $event->getSubject();

        if ('classroom' != $review['targetType']) {
            return true;
        }

        $ratingFields = $this->getReviewService()->countRatingByTargetTypeAndTargetId($review['targetType'], $review['targetId']);
        $this->getClassroomService()->updateClassroom($review['targetId'], $ratingFields);

        if (0 == $review['parentId']) {
            return;
        }

        $classroom = $this->getClassroomService()->getClassroom($review['targetId']);

        $review = $this->getReviewService()->getReview($review['id']);

        if (empty($review['id']) || $review['createdTime'] != $review['updatedTime']) {
            return;
        }

        $parentReview = $this->getReviewService()->getReview($review['parentId']);
        if (!$parentReview) {
            return;
        }

        $message = [
            'title' => $classroom['title'],
            'targetId' => $review['targetId'],
            'targetType' => 'classroom',
            'userId' => $review['userId'],
        ];

        $this->getNotifiactionService()->notify($parentReview['userId'], 'comment-post',
            $message);
    }

    private function simplifyClassroom($classroom)
    {
        return [
            'id' => $classroom['id'],
            'title' => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about' => StringToolkit::plain($classroom['about'], 100),
            'price' => $classroom['price'],
        ];
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->getBiz()->service('User:NotificationService');
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
}
