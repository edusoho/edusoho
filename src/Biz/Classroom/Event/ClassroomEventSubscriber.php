<?php
namespace Biz\Classroom\Event;

use Topxia\Common\StringToolkit;
use Biz\Taxonomy\TagOwnerManager;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.delete' => 'onClassroomDelete',
            'classroom.update' => 'onClassroomUpdate',
            'classReview.add'  => 'onReviewCreate'
        );
    }

    public function onClassroomDelete(Event $event)
    {
        $classroom = $event->getSubject();

        $tagOwnerManager = new TagOwnerManager('classroom', $classroom['id']);
        $tagOwnerManager->delete();
    }

    public function onClassroomUpdate(Event $event)
    {
        $fields = $event->getSubject();

        $tagIds      = $fields['tagIds'];
        $userId      = $fields['userId'];
        $classroomId = $fields['classroomId'];

        $tagOwnerManager = new TagOwnerManager('classroom', $classroomId, $tagIds, $userId);
        $tagOwnerManager->update();
    }

    public function onReviewCreate(Event $event)
    {
        $review = $event->getSubject();

        if ($review['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroom($review['classroomId']);

            $parentReview = $this->getClassroomReviewService()->getReview($review['parentId']);
            if (!$parentReview) {
                return false;
            }

            $message = array(
                'title'      => $classroom['title'],
                'targetId'   => $review['classroomId'],
                'targetType' => 'classroom',
                'userId'     => $review['userId']
            );
            $this->getNotifiactionService()->notify($parentReview['userId'], 'comment-post',
                $message);
        }
    }

    private function simplifyClassroom($classroom)
    {
        return array(
            'id'      => $classroom['id'],
            'title'   => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about'   => StringToolkit::plain($classroom['about'], 100),
            'price'   => $classroom['price']
        );
    }

    private function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    protected function getNotifiactionService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    private function getClassroomReviewService()
    {
        return $this->getBiz()->service('Classroom:ClassroomReviewService');
    }
}
