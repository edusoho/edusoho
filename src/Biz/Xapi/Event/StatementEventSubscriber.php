<?php

namespace Biz\Xapi\Event;

use AppBundle\Common\MathToolkit;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\User\CurrentUser;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use QiQiuYun\SDK\Constants\XAPIVerbs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatementEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'exam.finish' => 'onExamFinish',
            'question_marker.finish' => 'onQuestionMarkerFinish',
            'order.paid' => 'onOrderPaid',
            'classReview.add' => 'onClassroomReviewAdd',

            'user.search' => 'onUserSearch',
            'user.daily.active' => 'onUserDailyActive',
            'user.registered' => 'onUserRegistered',

            'course.task.finish' => 'onCourseTaskFinish',
            'course.note.create' => 'onCourseNoteCreate',
            'course.thread.create' => 'onCourseThreadCreate',
            'courseSet.favorite' => 'onCourseSetFavorite',
            'course.review.add' => 'onCourseReviewAdd',
        );
    }

    public function onCourseTaskFinish(Event $event)
    {
        $user = $event->getArgument('user');
        if (empty($user)) {
            return;
        }

        if ($user instanceof CurrentUser && !$user->isLogin()) {
            return;
        }

        $taskResult = $event->getSubject();

        $this->createStatement($user['id'], 'finish', $taskResult['id'], 'activity');
    }

    public function onQuestionMarkerFinish(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }
        $questionMarkerResult = $event->getSubject();

        $this->createStatement($user['id'], 'answered', $questionMarkerResult['id'], 'question');
    }

    public function onExamFinish(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }
        // testpaper, exercise, homework
        $examResult = $event->getSubject();

        switch ($examResult['type']) {
            case 'testpaper':
                $this->testpaperFinish($examResult);
                break;
            case 'homework':
                $this->homeworkFinish($examResult);
                break;
            case 'exercise':
                $this->exerciseFinish($examResult);
                break;
            default:
                break;
        }
    }

    public function onUserSearch(Event $event)
    {
        $subject = $event->getSubject();
        $this->createStatement($subject['userId'], XAPIVerbs::SEARCHED, 0, 'keyword', $subject);
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $orderItem = empty($order['items']) ? array() : $order['items'][0];
        // TODO 如果改成一个订单多个商品的话，每一个 item 需要保存真实支付的现金
        $isSuiteOrder = 'outside' != $order['source'] && $order['pay_amount'] > 0 && $orderItem && in_array($orderItem['target_type'], array(CourseProduct::TYPE, ClassroomProduct::TYPE));
        if ($isSuiteOrder) {
            $this->createStatement($order['user_id'], XAPIVerbs::PURCHASED, $orderItem['target_id'], $orderItem['target_type'], array(
                'pay_amount' => round(MathToolkit::simple($order['pay_amount'], 0.01), 2),
                'title' => $orderItem['title'],
            ));
        }
    }

    public function onUserDailyActive(Event $event)
    {
        $subject = $event->getSubject();
        $this->createStatement($subject['userId'], XAPIVerbs::LOGGED_IN, $subject['userId'], 'user');
    }

    protected function testpaperFinish($testpaperResult)
    {
        $this->createStatement($testpaperResult['userId'], 'completed', $testpaperResult['id'], 'testpaper');
    }

    protected function homeworkFinish($homeworkResult)
    {
        $this->createStatement($homeworkResult['userId'], 'completed', $homeworkResult['id'], 'homework');
    }

    protected function exerciseFinish($exerciseFinish)
    {
        $this->createStatement($exerciseFinish['userId'], 'completed', $exerciseFinish['id'], 'exercise');
    }

    public function onCourseNoteCreate(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }
        $note = $event->getSubject();

        $this->createStatement($note['userId'], 'noted', $note['id'], 'note');
    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        if ('question' != $thread['type']) {
            return;
        }

        $this->createStatement($thread['userId'], 'asked', $thread['id'], 'question');
    }

    public function onCourseSetFavorite(Event $event)
    {
        $favorite = $event->getSubject();
        $course = $event->getArgument('course');

        $this->createStatement($favorite['userId'], XAPIVerbs::BOOKMARKED, $course['id'], 'course', array(
        ));
    }

    public function onCourseReviewAdd(Event $event)
    {
        $review = $event->getSubject();

        $this->createStatement($review['userId'], XAPIVerbs::RATED, $review['courseId'], 'course', array(
            'score' => array(
                'raw' => $review['rating'],
                'max' => 5,
                'min' => 1,
            ),
            'response' => $review['content'],
        ));
    }

    public function onClassroomReviewAdd(Event $event)
    {
        $review = $event->getSubject();
        $classroom = $event->getArgument('classroom');

        $this->createStatement($review['userId'], XAPIVerbs::RATED, $review['classroomId'], 'classroom', array(
            'score' => array('raw' => $review['rating'], 'max' => 5, 'min' => 1),
            'response' => $review['content'],
            'name' => $classroom['title'],
        ));
    }

    public function onUserRegistered(Event $event)
    {
        $user = $event->getSubject();

        $this->createStatement($user['id'], XAPIVerbs::REGISTERED, $user['id'], 'user', array());
    }

    private function createStatement($userId, $verb, $targetId, $targetType, $context = array())
    {
        if (empty($userId)) {
            return;
        }
        try {
            $statement = array(
                'user_id' => $userId,
                'verb' => $verb,
                'target_id' => $targetId,
                'target_type' => $targetType,
                'context' => $context,
                'occur_time' => time(),
            );

            $this->getXapiService()->createStatement($statement);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }
}
