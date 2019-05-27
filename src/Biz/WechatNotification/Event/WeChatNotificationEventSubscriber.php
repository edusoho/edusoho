<?php

namespace Biz\WechatNotification\Event;

use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Common\ArrayToolkit;

class WeChatNotificationEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    const OFFICIAL_TYPE = 'official';

    private $testpaperStatus = array('excellent' => '优秀', 'good' => '良好', 'passed' => '合格', 'unpassed' => '不合格');

    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.unpublish' => 'onTaskUnpublish',
            'course.task.publish' => 'onTaskPublish',
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
            'exam.reviewed' => 'onTestpaperReviewd',
            'payment_trade.paid' => 'onPaid',
        );
    }

    public function onTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ('published' != $courseSet['status'] || 'published' != $course['status']) {
            return;
        }

        if ('live' == $task['type']) {
            $key = 'liveTaskUpdate';
            $this->deleteJob($task);
            $this->registerLiveNotificationJob($task);
        } else {
            $key = 'normalTaskUpdate';
        }

        $templateId = $this->getWeChatService()->getTemplateId($key);
        if (empty($templateId)) {
            return;
        }

        $teachers = $this->getCourseMemberService()->searchMembers(
            array('courseId' => $course['id'], 'role' => 'teacher', 'isVisible' => 1),
            array('id' => 'asc'),
            0,
            1
        );
        $user = $this->getUserService()->getUser($teachers[0]['userId']);

        $batchs = $this->getSendBatchs($course['id']);
        if (empty($batchs)) {
            return;
        }

        $data = array(
            'first' => array('value' => '同学，你好，课程有新任务发布'),
            'keyword1' => array('value' => $courseSet['title']),
            'keyword2' => array('value' => ('live' == $task['type']) ? '直播课' : ''),
            'keyword3' => array('value' => $user['nickname']),
            'keyword4' => array('value' => ('live' == $task['type']) ? date('Y-m-d H:i', $task['startTime']) : date('Y-m-d H:i', $task['updatedTime'])),
            'remark' => array('value' => ('live' == $task['type']) ? '请准时参加' : '请及时前往学习'),
        );

        $options = array('url' => $this->generateUrl('course_task_show', array('courseId' => $course['id'], 'id' => $task['id']), true));
    }

    public function onTaskUnpublish(Event $event)
    {
        $task = $event->getSubject();
        $this->deleteJob($task);
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $this->deleteJob($task);
    }

    public function onTaskUpdate(Event $event)
    {
        $task = $event->getSubject();
        if ('live' == $task['type']) {
            $this->deleteJob($task);

            if ('published' == $task['status']) {
                $this->registerLiveNotificationJob($task);
            }
        }
    }

    public function onTestpaperReviewd(Event $event)
    {
        $paperResult = $event->getSubject();
        $task = $this->getTaskService()->getTask($paperResult['lessonId']);

        if ('testpaper' == $paperResult['type']) {
            $key = 'examResult';
            $data = array(
                'first' => array('value' => '同学，你好，你的试卷已批阅完成'),
                'keyword1' => array('value' => $task['title']),
                'keyword2' => array('value' => $paperResult['score']),
                'remark' => array('value' => '再接再厉哦'),
            );
        } elseif ('homework' == $paperResult['type']) {
            $key = 'homeworkResult';
            $course = $this->getCourseService()->getCourse($task['courseId']);
            $teachers = $this->getCourseMemberService()->searchMembers(
                array('courseId' => $course['id'], 'role' => 'teacher', 'isVisible' => 1),
                array('id' => 'asc'),
                0,
                1
            );
            if (empty($teachers)) {
                $nickname = '';
            } else {
                $user = $this->getUserService()->getUser($teachers[0]['userId']);
                $nickname = $user['nickname'];
            }

            $data = array(
                'first' => array('value' => '同学，你好，你的作业已批阅完成'),
                'keyword1' => array('value' => $task['title']),
                'keyword2' => array('value' => $course['courseSetTitle']),
                'keyword3' => array('value' => $nickname),
                'remark' => array('value' => '作业结果：'.$this->testpaperStatus[$paperResult['passedStatus']]),
            );
        } else {
            return;
        }

        $templateId = $this->getWeChatService()->getTemplateId($key);
        if (empty($templateId)) {
            return;
        }

        $options = array('url' => $this->generateUrl('course_task_show', array('courseId' => $task['courseId'], 'id' => $task['id']), true));
    }

    public function onPaid(Event $event)
    {
        $trade = $event->getSubject();
        $chargeTemplateId = $this->getWeChatService()->getTemplateId('coinRecharge');
        $payTemplateId = $this->getWeChatService()->getTemplateId('paySuccess');
        if (!empty($chargeTemplateId) && 'recharge' == $trade['type']) {
            $data = array(
                'first' => '尊敬的客户，您已充值成功',
                'keyword1' => '现金充值或学习卡',
                'keyword2' => $trade['trade_sn'],
                'keyword3' => $trade['cash_amount'] / 100,
                'keyword4' => date('Y-m-d H:i', $trade['pay_time']),
                'remark' => '快去看看课程吧~',
            );
            $options = array('url' => $this->generateUrl('course_set_explore', array(), true));
            $user = $this->getWeChatService()->getOfficialWeChatUserByUserId($trade['user_id']);
        }

        if (!empty($payTemplateId)) {
            $data = array(
                'first' => '尊敬的客户，您已支付成功',
                'keyword1' => $trade['title'],
                'keyword2' => $trade['cash_amount'] / 100,
                'keyword3' => date('Y-m-d H:i', $trade['pay_time']),
                'remark' => '请前往查看',
            );
            $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);
            if (empty($order)) {
                return;
            }
            $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
            $options = array('url' => $this->getOrderTargetDetailUrl($orderItems[0]['target_type'], $orderItems[0]['target_id']));
            $user = empty($user) ? $this->getWeChatService()->getOfficialWeChatUserByUserId($trade['user_id']) : $user;
        }
    }

    private function getSendBatchs($courseId)
    {
        $batchs = array();
        $conditions = array(
            'courseId' => $courseId,
            'role' => 'student',
        );
        $membersCount = $this->getCourseMemberService()->countMembers($conditions);
        if (empty($membersCount)) {
            return $batchs;
        }

        $batchNum = $membersCount / 100;
        for ($i = 0; $i < $batchNum; ++$i) {
            $batchs[] = ArrayToolkit::column($this->getCourseMemberService()->searchMembers($conditions, array(), $i * 100, 100, array('userId')), 'userId');
        }

        return $batchs;
    }

    private function getOrderTargetDetailUrl($targetType, $targetId)
    {
        switch ($targetType) {
            case 'course':
                return $this->generateUrl('course_show', array('id' => $targetId), true);

            case 'classroom':
                return $this->generateUrl('classroom_show', array('id' => $targetId), true);

            case 'vip':
                return $this->generateUrl('vip', array(), true);

            default:
                return '';
        }
    }

    private function registerLiveNotificationJob($task)
    {
        $hourTemplateId = $this->getWeChatService()->getTemplateId('oneHourBeforeLiveOpen');
        $dayTemplateId = $this->getWeChatService()->getTemplateId('oneDayBeforeLiveOpen');
        if (!empty($hourTemplateId) && $task['startTime'] >= (time() + 24 * 60 * 60)) {
            $job = array(
                'name' => 'WeChatNotificationSendOneHourJob_liveLesson_'.$task['id'],
                'expression' => intval($task['startTime'] - 24 * 60 * 60),
                'class' => 'Biz\WeChatNotification\Job\LiveNotificationJob',
                'misfire_threshold' => 60 * 60,
                'args' => array(
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('course_task_show', array('courseId' => $task['courseId'], 'id' => $task['id']), true),
                ),
            );
            $this->getSchedulerService()->register($job);
        }

        if (!empty($dayTemplateId) && $task['startTime'] >= (time() + 60 * 60)) {
            $job = array(
                'name' => 'WeChatNotificationSendOneDayJob_liveLesson_'.$task['id'],
                'expression' => intval($task['startTime'] - 60 * 60),
                'class' => 'Biz\WeChatNotification\Job\LiveNotificationJob',
                'misfire_threshold' => 60 * 10,
                'args' => array(
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('course_task_show', array('courseId' => $task['courseId'], 'id' => $task['id']), true),
                ),
            );
            $this->getSchedulerService()->register($job);
        }
    }

    private function deleteJob($task)
    {
        $this->deleteByJobName('WeChatNotificationSendOneHourJob_liveLesson_'.$task['id']);
        $this->deleteByJobName('WeChatNotificationSendOneDayJob_liveLesson_'.$task['id']);
    }

    private function deleteByJobName($jobName)
    {
        $jobs = $this->getSchedulerService()->searchJobs(array('name' => $jobName), array(), 0, PHP_INT_MAX);

        foreach ($jobs as $job) {
            $this->getSchedulerService()->deleteJob($job['id']);
        }
    }

    private function generateUrl($route, $parameters, $referenceType)
    {
        global $kernel;

        return $kernel->getContainer()->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->getBiz()->service('Queue:QueueService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getWeChatService()
    {
        return $this->getBiz()->service('WeChat:WeChatService');
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }
}
