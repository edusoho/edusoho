<?php

namespace Biz\RewardPoint\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RewardPointSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.create' => 'onCourseThreadCreate',
            'thread.create' => 'onThreadCreate',
            'course.thread.post.create' => 'onCourseThreadPostCreate',
            'thread.post.create' => 'onThreadPostCreate',
            'course.thread.elite' => 'onCourseThreadElite',
            'thread.nice' => 'onThreadNice',
            'course.review.add' => 'onCourseReviewAdd',
            'classReview.add' => 'onClassReviewAdd',
            'course.task.finish' => 'onCourseTaskFinish',
        );
    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $params = array(
            'way' => ($thread['type'] == 'question') ? 'create_question' : 'create_discussion',
            'targetId' => $thread['id'],
            'targetType' => 'course_thread',
            'userId' => $thread['userId'],
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();

        if ($thread['targetType'] != 'classroom') {
            return;
        }

        $result = $this->getClassroomService()->isClassroomAuditor($thread['targetId'], $thread['userId']);

        if (!$result) {
            $params = array(
                'way' => ($thread['type'] == 'question') ? 'create_question' : 'create_discussion',
                'targetId' => $thread['id'],
                'targetType' => 'thread',
                'userId' => $thread['userId'],
            );

            $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
            $commonAcquireRewardPoint->reward($params);
        }
    }

    public function onCourseThreadPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $thread = $this->getCourseThreadService()->getThread($post['courseId'], $post['threadId']);
        $params = array(
            'way' => ($thread['type'] == 'question') ? 'reply_question' : 'reply_discussion',
            'targetId' => $post['id'],
            'targetType' => 'course_thread_post',
            'userId' => $thread['userId'],
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    public function onThreadPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $thread = $this->getThreadService()->getThread($post['threadId']);

        if ($thread['targetType'] != 'classroom') {
            return;
        }

        $result = $this->getClassroomService()->isClassroomAuditor($thread['targetId'], $thread['userId']);

        if (!$result) {
            $params = array(
                'way' => ($thread['type'] == 'question') ? 'reply_question' : 'reply_discussion',
                'targetId' => $post['id'],
                'targetType' => 'thread_post',
                'userId' => $thread['userId'],
            );

            $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
            $commonAcquireRewardPoint->reward($params);
        }
    }

    public function onCourseThreadElite(Event $event)
    {
        $thread = $event->getSubject();
        $params = array(
            'way' => 'elite_thread',
            'targetId' => $thread['id'],
            'targetType' => 'course_thread_elite',
            'userId' => $thread['userId'],
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    public function onThreadNice(Event $event)
    {
        $thread = $event->getSubject();
        $params = array(
            'way' => 'elite_thread',
            'targetId' => $thread['id'],
            'targetType' => 'thread_nice',
            'userId' => $thread['userId'],
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    public function onCourseReviewAdd(Event $event)
    {
        $review = $event->getSubject();
        $params = array(
            'way' => 'appraise_course_classroom',
            'targetId' => $review['id'],
            'targetType' => 'course_review_add',
            'userId' => $review['userId'],
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    public function onClassReviewAdd(Event $event)
    {
        $review = $event->getSubject();
        $params = array(
            'way' => 'appraise_course_classroom',
            'targetId' => $review['id'],
            'targetType' => 'classroom_review_add',
            'userId' => $review['userId'],
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();

        $params = array(
            'way' => 'task_reward_point',
            'targetId' => $taskResult['courseTaskId'],
            'targetType' => 'task',
            'userId' => $taskResult['userId'],
        );

        $courseAcquireRewardPoint = $this->getRewardPointFactory('course-acquire');
        $courseAcquireRewardPoint->reward($params);
    }

    protected function getCourseThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function getRewardPointFactory($type)
    {
        $biz = $this->getBiz();
        if (!isset($biz["reward_point.{$type}"])) {
            return null;
        }

        return $biz["reward_point.{$type}"];
    }
}
