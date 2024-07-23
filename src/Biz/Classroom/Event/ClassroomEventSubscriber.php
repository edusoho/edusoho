<?php

namespace Biz\Classroom\Event;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\MemberService;
use Biz\Course\Service\CourseService;
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
            'course.statistics.update' => 'onCourseStatisticsUpdate',
            'classroom.delete' => 'onClassroomDelete',
            'classroom.course.create' => 'onClassroomCourseChange',
            'classroom.course.delete' => 'onClassroomCourseChange',
            'classroom.course.update' => 'onClassroomCourseChange',
            'classroom.courses.delete' => 'onClassroomCoursesDelete',
            'review.create' => 'onReviewChanged',
            'review.update' => 'onReviewChanged',
            'review.delete' => 'onReviewChanged',
        ];
    }

    public function onCourseStatisticsUpdate(Event $event)
    {
        $course = $event->getSubject();
        if ($course['parentId'] > 0) {
            $needFields = [
                'compulsoryTaskNum',
                'electiveTaskNum',
                'lessonNum',
            ];
            $updatedFields = $event->getArgument('updatedFields');
            $arr = array_intersect($needFields, array_keys($updatedFields));
            if (!empty($arr)) {
                $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
                if (empty($classroom)) {
                    return;
                }
                $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
                $this->getClassroomService()->updateClassroom($classroom['id'], [
                    'lessonNum' => array_sum(ArrayToolkit::column($courses, 'lessonNum')),
                    'compulsoryTaskNum' => array_sum(ArrayToolkit::column($courses, 'compulsoryTaskNum')),
                    'electiveTaskNum' => array_sum(ArrayToolkit::column($courses, 'electiveTaskNum')),
                ]);
                $this->getClassroomService()->updateClassroomMembersFinishedStatus($classroom['id']);
            }
        }
    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $course = $this->getCourseService()->getCourse($task['courseId']);
        if (empty($course)) {
            return;
        }
        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $this->getClassroomService()->updateClassroomMembersFinishedStatus($classroom['id']);
        }
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
        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
        $this->getClassroomService()->updateClassroom($classroom['id'], [
            'courseNum' => $courseNum,
            'lessonNum' => array_sum(ArrayToolkit::column($courses, 'lessonNum')),
            'compulsoryTaskNum' => array_sum(ArrayToolkit::column($courses, 'compulsoryTaskNum')),
            'electiveTaskNum' => array_sum(ArrayToolkit::column($courses, 'electiveTaskNum')),
        ]);
        $this->getClassroomService()->updateClassroomMembersFinishedStatus($classroom['id']);

        $this->getClassroomService()->updateClassroomTeachers($classroomId);
    }

    public function onClassroomCoursesDelete(Event $event)
    {
        $classroom = $event->getSubject();
        $classroomId = $classroom['id'];
        $courseNum = $this->getClassroomService()->countCoursesByClassroomId($classroomId);
        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $this->getClassroomService()->updateClassroom($classroomId, [
            'courseNum' => $courseNum,
            'lessonNum' => array_sum(ArrayToolkit::column($courses, 'lessonNum')),
            'compulsoryTaskNum' => array_sum(ArrayToolkit::column($courses, 'compulsoryTaskNum')),
            'electiveTaskNum' => array_sum(ArrayToolkit::column($courses, 'electiveTaskNum')),
        ]);
        $this->getClassroomService()->updateClassroomMembersFinishedStatus($classroomId);
        $this->getClassroomService()->updateClassroomMembersNoteAndThreadNums($classroomId);
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getClassroomMemberService()
    {
        return $this->getBiz()->service('Classroom:MemberService');
    }
}
