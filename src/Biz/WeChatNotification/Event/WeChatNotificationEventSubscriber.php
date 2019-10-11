<?php

namespace Biz\WeChatNotification\Event;

use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Util\TextHelper;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Biz\AppLoggerConstant;
use AppBundle\Common\ArrayToolkit;

class WeChatNotificationEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
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
            'course.task.create.sync' => 'onTaskCreateSync',
            'course.task.publish.sync' => 'onTaskPublishSync',
            'course.thread.create' => 'onCourseQuestionCreate',
            'thread.create' => 'onClassroomQuestionCreate',
            'course.thread.post.create' => 'onCourseQuestionAnswerCreate',
            'thread.post.create' => 'onClassroomQuestionAnswerCreate',
            'wechat.template_setting.save' => 'onWeChatTemplateSettingSave',
        );
    }

    public function onTaskUnpublish(Event $event)
    {
        $task = $event->getSubject();
        $this->deleteLiveNotificationJob($task);
    }

    public function onTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ('published' != $courseSet['status'] || 'published' != $course['status']) {
            return;
        }

        $this->sendTasksPublishNotification(array($task));
    }

    public function onTaskUpdate(Event $event)
    {
        $task = $event->getSubject();
        if ('live' == $task['type']) {
            $this->deleteLiveNotificationJob($task);

            if ('published' == $task['status']) {
                $this->registerLiveNotificationJob($task);
            }
        }
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $this->deleteLiveNotificationJob($task);
    }

    public function onTestpaperReviewd(Event $event)
    {
        $paperResult = $event->getSubject();
        $activity = $this->getActivityService()->getActivity($paperResult['lessonId']);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
        if (empty($task)) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'wechat_notification_error', '发送微信通知失败:获取任务失败', $paperResult);

            return;
        }

        if (!in_array($paperResult['type'], array('testpaper', 'homework'))) {
            return;
        }

        if ('testpaper' == $paperResult['type']) {
            $key = 'examResult';
            $logName = 'wechat_notify_exam_result';
            $data = array(
                'first' => array('value' => '同学，您好，你的试卷已批阅完成'),
                'keyword1' => array('value' => $task['title']),
                'keyword2' => array('value' => $paperResult['score']),
                'remark' => array('value' => '再接再厉哦'),
            );
        } elseif ('homework' == $paperResult['type']) {
            $key = 'homeworkResult';
            $logName = 'wechat_notify_homework_result';
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
                'first' => array('value' => '同学，您好，你的作业已批阅完成'),
                'keyword1' => array('value' => $task['title']),
                'keyword2' => array('value' => $course['courseSetTitle']),
                'keyword3' => array('value' => $nickname),
                'remark' => array('value' => '作业结果：'.$this->testpaperStatus[$paperResult['passedStatus']]),
            );
        }

        $templateId = $this->getWeChatService()->getTemplateId($key);
        if (empty($templateId)) {
            return;
        }

        $options = array('type' => 'url', 'url' => $this->generateUrl('course_task_show', array('courseId' => $task['courseId'], 'id' => $task['id']), true));
        $weChatUser = $this->getWeChatService()->getOfficialWeChatUserByUserId($paperResult['userId']);
        if (empty($weChatUser['isSubscribe']) || $this->isUserLocked($paperResult['userId'])) {
            return;
        }

        $templates = TemplateUtil::templates();
        $templateCode = isset($templates[$key]['id']) ? $templates[$key]['id'] : '';
        $list = array(array(
            'channel' => $this->getWeChatService()->getWeChatSendChannel(),
            'to_id' => $weChatUser['openId'],
            'template_id' => $templateId,
            'template_code' => $templateCode,
            'template_args' => $data,
            'goto' => $options,
        ));
        $this->sendCloudWeChatNotification($key, $logName, $list);
    }

    public function onPaid(Event $event)
    {
        $trade = $event->getSubject();
        $chargeTemplateId = $this->getWeChatService()->getTemplateId('coinRecharge');
        $payTemplateId = $this->getWeChatService()->getTemplateId('paySuccess');
        if (!empty($chargeTemplateId) && 'recharge' == $trade['type']) {
            $data = array(
                'first' => array('value' => '尊敬的客户，您已充值成功'),
                'keyword1' => array('value' => '现金充值'),
                'keyword2' => array('value' => $trade['trade_sn']),
                'keyword3' => array('value' => ($trade['amount'] / 100).'元'),
                'keyword4' => array('value' => date('Y-m-d H:i', $trade['pay_time'])),
                'remark' => array('value' => '快去看看课程吧~'),
            );
            $options = array('type' => 'url', 'url' => $this->generateUrl('course_set_explore', array(), true));
            $weChatUser = $this->getWeChatService()->getOfficialWeChatUserByUserId($trade['user_id']);
            $templates = TemplateUtil::templates();
            $templateCode = isset($templates['coinRecharge']['id']) ? $templates['coinRecharge']['id'] : '';
            if (!empty($weChatUser['isSubscribe']) && !$this->isUserLocked($trade['user_id'])) {
                $list = array(array(
                    'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                    'to_id' => $weChatUser['openId'],
                    'template_id' => $chargeTemplateId,
                    'template_code' => $templateCode,
                    'template_args' => $data,
                    'goto' => $options,
                ));
                $this->sendCloudWeChatNotification('coinRecharge', 'wechat_notify_coin_recharge', $list);
            }
        }

        if (!empty($payTemplateId)) {
            $data = array(
                'first' => array('value' => '尊敬的客户，您已支付成功'),
                'keyword1' => array('value' => $trade['title']),
                'keyword2' => array('value' => ($trade['amount'] / 100).'元'),
                'keyword3' => array('value' => date('Y-m-d H:i', $trade['pay_time'])),
                'keyword4' => array('value' => '无'),
                'remark' => array('value' => '请前往查看'),
            );
            $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);
            if (empty($order)) {
                return;
            }
            $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
            $options = array('type' => 'url', 'url' => $this->getOrderTargetDetailUrl($orderItems[0]['target_type'], $orderItems[0]['target_id']));
            $weChatUser = empty($weChatUser) ? $this->getWeChatService()->getOfficialWeChatUserByUserId($trade['user_id']) : $weChatUser;
            $templates = TemplateUtil::templates();
            $templateCode = isset($templates['paySuccess']['id']) ? $templates['paySuccess']['id'] : '';
            if (!empty($weChatUser['isSubscribe']) && !$this->isUserLocked($trade['user_id'])) {
                $list = array(array(
                    'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                    'to_id' => $weChatUser['openId'],
                    'template_id' => $payTemplateId,
                    'template_code' => $templateCode,
                    'template_args' => $data,
                    'goto' => $options,
                ));
                $this->sendCloudWeChatNotification('paySuccess', 'wechat_notify_pay_success', $list);
            }
        }
    }

    public function onTaskCreateSync(Event $event)
    {
        $task = $event->getSubject();

        if ('published' == $task['status']) {
            $tasks = $this->getCopiedTasks($task);

            $this->sendTasksPublishNotification($tasks);
        }
    }

    public function onTaskPublishSync(Event $event)
    {
        $task = $event->getSubject();

        if ('published' == $task['status'] && $this->isTaskCreateSyncFinished($task)) {
            $tasks = $this->getCopiedTasks($task);

            $this->sendTasksPublishNotification($tasks);
        }
    }

    public function onCourseQuestionCreate(Event $event)
    {
        $thread = $event->getSubject();
        if ('question' != $thread['type']) {
            return;
        }
        $course = $this->getCourseService()->getCourse($thread['courseId']);
        $teachers = $this->getCourseMemberService()->findCourseTeachers($course['id']);
        $userIds = ArrayToolkit::column($teachers, 'userId');

        if (empty($userIds)) {
            return;
        }

        $courseName = empty($course['title']) ? $course['courseSetTitle'] : $course['title'];

        $templateParams = array(
            'thread' => $thread,
            'userIds' => $userIds,
            'title' => "在教课程《{$courseName}》",
            'goto' => $this->generateUrl('course_thread_show', array('courseId' => $course['id'], 'threadId' => $thread['id']), true),
        );
        $this->askQuestionSendNotification($templateParams);
    }

    public function onClassroomQuestionCreate(Event $event)
    {
        $thread = $event->getSubject();

        if ('classroom' != $thread['targetType'] || 'question' != $thread['type']) {
            return;
        }
        $classroom = $this->getClassroomService()->getClassroom($thread['targetId']);
        $userIds = $this->getClassroomService()->findTeachers($classroom['id']);
        if (empty($userIds)) {
            return;
        }

        $templateParams = array(
            'thread' => $thread,
            'userIds' => $userIds,
            'title' => "在教班级《{$classroom['title']}》",
            'goto' => $this->generateUrl('classroom_thread_show', array('classroomId' => $classroom['id'], 'threadId' => $thread['id']), true),
        );

        $this->askQuestionSendNotification($templateParams);
    }

    public function onCourseQuestionAnswerCreate(Event $event)
    {
        $post = $event->getSubject();

        if (empty($post['courseId'])) {
            return;
        }

        $course = $this->getCourseService()->getCourse($post['courseId']);
        $thread = $this->getCourseThreadService()->getThread($course['id'], $post['threadId']);
        if ($this->getCourseMemberService()->isCourseTeacher($post['courseId'], $post['userId'])) {
            $title = empty($course['title']) ? $course['courseSetTitle'] : $course['title'];
            if (!$this->isUserLocked($thread['userId'])) {
                $templateParams = array(
                    'userId' => $thread['userId'],
                    'content' => $post['content'],
                    'title' => $title,
                    'createdTime' => $thread['createdTime'],
                    'goto' => $this->generateUrl('course_thread_show', array('courseId' => $course['id'], 'threadId' => $thread['id']), true),
                );
                $this->answerQuestionNotification($templateParams);
            }
        }
    }

    public function onClassroomQuestionAnswerCreate(Event $event)
    {
        $post = $event->getSubject();

        if (empty($post['targetId']) && 'classroom' != $post['targetType']) {
            return;
        }

        $classroom = $this->getClassroomService()->getClassroom($post['targetId']);
        $thread = $this->getThreadService()->getThread($post['threadId']);

        if ($this->getClassroomService()->isClassroomTeacher($post['targetId'], $post['userId'])) {
            if (!$this->isUserLocked($thread['userId'])) {
                $templateParams = array(
                    'userId' => $thread['userId'],
                    'content' => $post['content'],
                    'title' => $classroom['title'],
                    'createdTime' => $thread['createdTime'],
                    'goto' => $this->generateUrl('classroom_thread_show', array('classroomId' => $classroom['id'], 'threadId' => $thread['id']), true),
                );
                $this->answerQuestionNotification($templateParams);
            }
        }
    }

    public function onWeChatTemplateSettingSave(Event $event)
    {
        $fields = $event->getSubject();
        $key = $event->getArgument('key');
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        $templates = empty($wechatSetting['templates']) ? array() : $wechatSetting['templates'];
        if ('homeworkOrTestPaperReview' == $key) {
            $templates['homeworkOrTestPaperReview']['sendTime'] = $fields['sendTime'];
            $notificationJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_HomeWorkOrTestPaperReview');
            if ($notificationJob) {
                $this->getSchedulerService()->deleteJob($notificationJob['id']);
            }
            if (!empty($templates['homeworkOrTestPaperReview']['templateId']) && !empty($templates['homeworkOrTestPaperReview']['sendTime'])) {
                $expression = $this->getSendTimeExpression($fields['sendTime']);

                if (1 == $templates['homeworkOrTestPaperReview']['status']) {
                    $job = array(
                        'name' => 'WeChatNotificationJob_HomeWorkOrTestPaperReview',
                        'expression' => $expression,
                        'class' => 'Biz\WeChatNotification\Job\HomeWorkOrTestPaperReviewNotificationJob',
                        'misfire_policy' => 'executing',
                        'args' => array(
                            'key' => $key,
                            'sendTime' => $templates['homeworkOrTestPaperReview']['sendTime'],
                        ),
                    );
                    $this->getSchedulerService()->register($job);
                }
            }
        }

        if ('courseRemind' == $key) {
            $templates['courseRemind']['sendTime'] = $fields['sendTime'];
            $templates['courseRemind']['sendDays'] = $fields['sendDays'];
            $notificationJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_CourseRemind');
            if ($notificationJob) {
                $this->getSchedulerService()->deleteJob($notificationJob['id']);
            }
            if (!empty($templates['courseRemind']['templateId']) && !empty($templates['courseRemind']['sendTime']) && !empty($templates['courseRemind']['sendDays'])) {
                $expression = $this->getSendDayAndTimeExpression($templates['courseRemind']['sendDays'], $templates['courseRemind']['sendTime']);

                if (1 == $templates['courseRemind']['status']) {
                    $job = array(
                        'name' => 'WeChatNotificationJob_CourseRemind',
                        'expression' => $expression,
                        'class' => 'Biz\WeChatNotification\Job\CourseRemindNotificationJob',
                        'misfire_policy' => 'executing',
                        'args' => array(
                            'key' => $key,
                            'url' => $this->generateUrl('my_courses_learning', array(), true),
                            'sendTime' => $templates['courseRemind']['sendTime'],
                            'sendDays' => $templates['courseRemind']['sendDays'],
                        ),
                    );
                    $this->getSchedulerService()->register($job);
                }
            }
        }
    }

    protected function answerQuestionNotification($templateParams)
    {
        $templateParams = ArrayToolkit::filter($templateParams, array(
            'userId' => null,
            'content' => null,
            'title' => null,
            'createdTime' => null,
            'goto' => null,
        ));

        $templateId = $this->getWeChatService()->getTemplateId('answerQuestion');
        if (!empty($templateId)) {
            $weChatUser = $this->getWeChatService()->getOfficialWeChatUserByUserId($templateParams['userId']);

            if (empty($weChatUser)) {
                return;
            }
            $content = TextHelper::truncate($templateParams['content'], 30);
            $data = array(
                'first' => array('value' => '亲爱的学员，您在《'.$templateParams['title'].'》中的发表的问题有了新的回答'),
                'keyword1' => array('value' => date('Y-m-d H:i:s', $templateParams['createdTime'])),
                'keyword2' => array('value' => $content),
                'remark' => array('value' => ''),
            );

            $templates = TemplateUtil::templates();
            $templateCode = isset($templates['answerQuestion']['id']) ? $templates['answerQuestion']['id'] : '';
            $list = array(array(
                'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                'to_id' => $weChatUser['openId'],
                'template_id' => $templateId,
                'template_code' => $templateCode,
                'template_args' => $data,
                'goto' => array(
                    'type' => 'url',
                    'url' => $templateParams['goto'],
                ),
            ));

            $this->sendCloudWeChatNotification('answerQuestion', 'wechat_notify_answer_question', $list);
        }
    }

    protected function askQuestionSendNotification($templateParams)
    {
        $templateParams = ArrayToolkit::filter($templateParams, array(
            'thread' => array(),
            'userIds' => array(),
            'title' => null,
            'goto' => null,
        ));

        $users = $this->getUserService()->searchUsers(
            array('userIds' => $templateParams['userIds'], 'locked' => 0),
            array(),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($users, 'id');

        if (empty($userIds)) {
            return;
        }
        $templateId = $this->getWeChatService()->getTemplateId('askQuestion');
        if (!empty($templateId)) {
            $user = $this->getUserService()->getUser($templateParams['thread']['userId']);
            $weChatUsers = $this->getWeChatService()->searchWeChatUsers(
                array('userIds' => $userIds),
                array('lastRefreshTime' => 'ASC'),
                0,
                PHP_INT_MAX,
                array('id', 'openId', 'unionId', 'userId')
            );

            if (empty($weChatUsers)) {
                return;
            }

            $data = array(
                'first' => array('value' => '尊敬的老师，您的'.$templateParams['title'].'中有学员发布了提问'),
                'keyword1' => array('value' => $user['nickname']),
                'keyword2' => array('value' => mb_substr($templateParams['thread']['title'], 0, 30, 'utf-8')),
                'keyword3' => array('value' => date('Y-m-d H:i:s', $templateParams['thread']['createdTime'])),
                'remark' => array('value' => ''),
            );
            $templates = TemplateUtil::templates();
            $templateCode = isset($templates['askQuestion']['id']) ? $templates['askQuestion']['id'] : '';
            $templateData = array(
                'template_id' => $templateId,
                'template_code' => $templateCode,
                'template_args' => $data,
            );

            $list = array();
            foreach ($weChatUsers as $weChatUser) {
                $list[] = array_merge(array(
                    'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                    'to_id' => $weChatUser['openId'],
                    'goto' => array(
                        'type' => 'url',
                        'url' => $templateParams['goto'],
                    ),
                ), $templateData);
            }

            $this->sendCloudWeChatNotification('askQuestion', 'wechat_notify_ask_question', $list);
        }
    }

    protected function sendCloudWeChatNotification($key, $logName, $list)
    {
        try {
            $result = $this->getCloudNotificationClient()->sendNotifications($list);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, $logName, "发送微信通知失败:template:{$key}", array('error' => $e->getMessage()));

            return;
        }

        if (empty($result['sn'])) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, $logName, "发送微信通知失败:template:{$key}", $result);

            return;
        }

        $this->getNotificationService()->createWeChatNotificationRecord($result['sn'], $key, $list[0]['template_args']);
    }

    protected function sendTasksPublishNotification($tasks)
    {
        foreach ($tasks as $task) {
            if ('live' == $task['type']) {
                $this->deleteLiveNotificationJob($task);
                $this->registerLiveNotificationJob($task);
            }
            $key = TemplateUtil::TEMPLATE_COURSE_UPDATE;
            $this->deleteLessonPublishJob($task);
            $this->registerLessonNotificationJob($key, $task);
        }
    }

    private function getOrderTargetDetailUrl($targetType, $targetId)
    {
        switch ($targetType) {
            case 'course':
                return $this->generateUrl('my_course_show', array('id' => $targetId), true);

            case 'classroom':
                return $this->generateUrl('classroom_show', array('id' => $targetId), true);

            case 'vip':
                return $this->generateUrl('vip', array(), true);

            default:
                return '';
        }
    }

    private function registerLessonNotificationJob($key, $task)
    {
        $templateId = $this->getWeChatService()->getTemplateId($key);
        if (!empty($templateId)) {
            $job = array(
                'name' => 'WeChatNotificationJob_LessonPublish_'.$task['id'],
                'expression' => time(),
                'class' => 'Biz\WeChatNotification\Job\LessonPublishNotificationJob',
                'misfire_threshold' => 60 * 60,
                'args' => array(
                    'key' => $key,
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('my_course_show', array('id' => $task['courseId']), true),
                ),
            );
            $this->getSchedulerService()->register($job);
        }
    }

    private function registerLiveNotificationJob($task)
    {
        $hourTemplateId = $this->getWeChatService()->getTemplateId(TemplateUtil::TEMPLATE_LIVE_OPEN, 'beforeOneHour');
        $dayTemplateId = $this->getWeChatService()->getTemplateId(TemplateUtil::TEMPLATE_LIVE_OPEN, 'beforeOneDay');
        if (!empty($dayTemplateId) && $task['startTime'] >= (time() + 24 * 60 * 60)) {
            $job = array(
                'name' => 'WeChatNotificationJob_LiveOneDay_'.$task['id'],
                'expression' => intval($task['startTime'] - 24 * 60 * 60),
                'class' => 'Biz\WeChatNotification\Job\LiveNotificationJob',
                'misfire_threshold' => 60 * 60,
                'args' => array(
                    'key' => 'liveOpen',
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('my_course_show', array('id' => $task['courseId']), true),
                ),
            );
            $this->getSchedulerService()->register($job);
        }

        if (!empty($hourTemplateId) && $task['startTime'] >= (time() + 60 * 60)) {
            $job = array(
                'name' => 'WeChatNotificationJob_LiveOneHour_'.$task['id'],
                'expression' => intval($task['startTime'] - 60 * 60),
                'class' => 'Biz\WeChatNotification\Job\LiveNotificationJob',
                'misfire_threshold' => 60 * 10,
                'args' => array(
                    'key' => 'liveOpen',
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('my_course_show', array('id' => $task['courseId']), true),
                ),
            );
            $this->getSchedulerService()->register($job);
        }
    }

    private function getCopiedTasks($task)
    {
        if (empty($task)) {
            return array();
        }

        $courses = $this->getCourseService()->findCoursesByParentIdAndLocked($task['courseId'], 1);

        return $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], ArrayToolkit::column($courses, 'id'));
    }

    private function isTaskCreateSyncFinished($task)
    {
        $courses = $this->getCourseService()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        $tasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], ArrayToolkit::column($courses, 'id'));

        if (count($tasks) == count($courses)) {
            return true;
        }

        return false;
    }

    protected function getSendDayAndTimeExpression($days, $time)
    {
        $filterDays = array();

        $allDays = array(
            'Sun' => 0,
            'Mon' => 1,
            'Tue' => 2,
            'Wed' => 3,
            'Thu' => 4,
            'Fri' => 5,
            'Sat' => 6,
        );

        foreach ($allDays as $key => $day) {
            if (in_array($key, $days)) {
                $filterDays[] = $day;
            }
        }

        $runDays = implode(',', $filterDays);
        $runDays = empty($runDays) ? '*' : $runDays;
        $time = explode(':', $time);
        $hour = 2 === count($time) ? $time[0] : 0;
        $minute = 2 === count($time) ? $time[1] : 0;

        return $minute.' '.$hour.' * * '.$runDays;
    }

    protected function getSendTimeExpression($sendTime)
    {
        $expression = '';
        if (!is_array($sendTime)) {
            $hourAndMinute = explode(':', $sendTime);
            $minute = ($hourAndMinute[1] < 10) ? $hourAndMinute[1] % 10 : $hourAndMinute[1];
            $hour = ($hourAndMinute[0] < 10) ? $hourAndMinute[0] % 10 : $hourAndMinute[0];

            $expression = $minute.' '.$hour.' * * *';
        }

        return $expression;
    }

    protected function isUserLocked($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if ($user['locked']) {
            return true;
        }

        return false;
    }

    private function deleteLessonPublishJob($task)
    {
        $this->deleteByJobName('WeChatNotificationJob_LessonPublish_'.$task['id']);
    }

    private function deleteLiveNotificationJob($task)
    {
        $this->deleteByJobName('WeChatNotificationJob_LiveOneHour_'.$task['id']);
        $this->deleteByJobName('WeChatNotificationJob_LiveOneDay_'.$task['id']);
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

    private function getCloudNotificationClient()
    {
        $biz = $this->getBiz();

        return $biz['qiQiuYunSdk.notification'];
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

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->getBiz()->service('WeChat:WeChatService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    protected function getNotificationService()
    {
        return $this->getBiz()->service('Notification:NotificationService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return \Biz\Thread\Service\ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
