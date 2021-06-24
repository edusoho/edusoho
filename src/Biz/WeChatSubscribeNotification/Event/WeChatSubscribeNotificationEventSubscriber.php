<?php

namespace Biz\WeChatSubscribeNotification\Event;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Notification\WeChatTemplateMessage\MessageSubscribeTemplateUtil;
use Biz\Activity\Service\ActivityService;
use Biz\AppLoggerConstant;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Sms\SmsType;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Util\TextHelper;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WeChatSubscribeNotificationEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    private $testpaperStatus = ['excellent' => '优秀', 'good' => '良好', 'passed' => '合格', 'unpassed' => '不合格'];

    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return [
            'course.task.unpublish' => 'onTaskUnpublish',
            'course.task.publish' => 'onTaskPublish',
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
            'answer.finished' => 'onAnswerFinished',
            'course.task.create.sync' => 'onTaskCreateSync',
            'course.task.update.sync' => 'onTaskUpdateSync',
            'course.task.publish.sync' => 'onTaskPublishSync',
            'course.thread.create' => 'onCourseQuestionCreate',
            'thread.create' => 'onClassroomQuestionCreate',
            'course.thread.post.create' => 'onCourseQuestionAnswerCreate',
            'thread.post.create' => 'onClassroomQuestionAnswerCreate',
        ];
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

        $this->sendTasksPublishNotification([$task]);
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

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();
        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerReport['answer_scene_id']);
        if (empty($activity) || !in_array($activity['mediaType'], ['testpaper', 'homework'])) {
            return;
        }

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
        if (empty($task)) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'wechat_subscribe_notification_error', '发送微信订阅通知失败:获取任务失败', $activity);

            return;
        }

        $user = $this->getUserService()->getUser($answerRecord['user_id']);
        if (empty($user) || $user['locked']) {
            return;
        }

        $weChatUser = $this->getWeChatService()->searchWeChatUsers(['userId' => $answerRecord['user_id']], ['lastRefreshTime' => 'ASC'], 0, 1, ['id', 'openId']);
        $weChatUser = empty($weChatUser) ? [] : $weChatUser[0];
        if (empty($weChatUser['openId'])) {
            return $this->sendHomeworkOrTestpaperResultSms($user['id'], $task, $activity);
        }

        list($templateCode, $logName) = $this->getTemplateCodeAndLogNameByActivity($activity);

        $templateId = $this->getWeChatService()->getSubscribeTemplateId($templateCode);
        if (empty($templateId)) {
            return $this->sendHomeworkOrTestpaperResultSms($user['id'], $task, $activity);
        }

        $subscribeRecordConditions = ['templateCode' => $templateId, 'templateType' => 'once', 'toId' => $weChatUser['openId'], 'isSend_LT' => 1];
        $subscribeRecordsCount = $this->getWeChatService()->searchSubscribeRecordCount($subscribeRecordConditions);
        if (empty($subscribeRecordsCount)) {
            return $this->sendHomeworkOrTestpaperResultSms($user['id'], $task, $activity);
        }

        $subscribeRecords = $this->getWeChatService()->searchSubscribeRecords($subscribeRecordConditions, ['id' => 'ASC'], 0, 1);
        $remainTime = $subscribeRecordsCount > 1 ? '剩'.($subscribeRecordsCount - 1).'次通知' : '无剩余通知';
        if ('testpaper' === $activity['mediaType']) {
            $remainTime = $remainTime.'，请进入课程学习页订阅';
        }

        if ('testpaper' === $activity['mediaType']) {
            $data = [
                'thing1' => ['value' => $this->plainTextByLength($task['title'], 20)],
                'number4' => ['value' => $answerReport['score']],
                'thing6' => ['value' => $remainTime],
            ];
        } elseif ('homework' === $activity['mediaType']) {
            $data = [
                'thing2' => ['value' => $this->plainTextByLength($task['title'], 18)],
                'thing3' => ['value' => $this->testpaperStatus[$answerReport['grade']].'（'.$remainTime.'）'],
                'thing8' => ['value' => empty($answerReport['comment']) ? '--' : $this->plainTextByLength($answerReport['comment'], 20)],
            ];
        }

        $list[] = [
            'template_id' => $templateId,
            'template_args' => $data,
            'channel' => $this->getWeChatService()->getWeChatSendChannel(),
            'to_id' => $weChatUser['openId'],
            'goto' => ['type' => 'url', 'url' => $this->generateUrl('course_task_show', ['courseId' => $task['courseId'], 'id' => $task['id']], UrlGeneratorInterface::ABSOLUTE_URL)],
        ];

        $result = $this->getWeChatService()->sendSubscribeWeChatNotification($templateCode, $logName, $list);

        if ($result) {
            $this->getWeChatService()->updateSubscribeRecordsByIds(array_column($subscribeRecords, 'id'), ['isSend' => 1]);
        }
    }

    protected function sendHomeworkOrTestpaperResultSms($userId, $task, $activity)
    {
        $params = [
            'course_title' => '课程：'.$this->getCourseNameByCourseId($task['courseId']),
            'lesson_title' => $task['title'].('testpaper' === $activity['mediaType'] ? '的试卷' : '的作业'),
        ];
        $smsType = 'testpaper' === $activity['mediaType'] ? 'sms_testpaper_check' : 'sms_homework_check';
        $subscribeSmsType = 'testpaper' === $activity['mediaType'] ? MessageSubscribeTemplateUtil::TEMPLATE_EXAM_RESULT : MessageSubscribeTemplateUtil::TEMPLATE_HOMEWORK_RESULT;

        if ($this->getWeChatService()->isSubscribeSmsEnabled($subscribeSmsType) && !$this->getWeChatService()->isSubscribeSmsEnabled($smsType)) {
            return $this->getWeChatService()->sendSubscribeSms($subscribeSmsType, [$userId], SmsType::EXAM_REVIEW, $params);
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

    public function onTaskUpdateSync(Event $event)
    {
        $task = $event->getSubject();
        if ('live' == $task['type']) {
            $copiedTasks = $this->getCopiedTasks($task);
            foreach ($copiedTasks as $copiedTask) {
                $this->deleteLiveNotificationJob($copiedTask);
                if ('published' == $copiedTask['status']) {
                    $this->registerLiveNotificationJob($copiedTask);
                }
            }
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

        $templateParams = [
            'thread' => $thread,
            'userIds' => $userIds,
            'title' => "{$this->getCourseNameByCourseId($thread['courseId'])}",
            'goto' => $this->generateUrl('course_thread_show', ['courseId' => $thread['courseId'], 'threadId' => $thread['id']], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
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

        $templateParams = [
            'thread' => $thread,
            'userIds' => $userIds,
            'title' => "（班级）{$classroom['title']}",
            'goto' => $this->generateUrl('classroom_thread_show', ['classroomId' => $classroom['id'], 'threadId' => $thread['id']], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $this->askQuestionSendNotification($templateParams);
    }

    public function onCourseQuestionAnswerCreate(Event $event)
    {
        $post = $event->getSubject();

        if (empty($post['courseId'])) {
            return;
        }

        $thread = $this->getCourseThreadService()->getThread($post['courseId'], $post['threadId']);

        if ($this->getCourseMemberService()->isCourseTeacher($post['courseId'], $post['userId'])) {
            $title = $this->getCourseNameByCourseId($post['courseId']);
            if (!$this->isUserLocked($thread['userId'])) {
                $templateParams = [
                    'userId' => $thread['userId'],
                    'content' => $post['content'],
                    'title' => $title,
                    'createdTime' => $thread['createdTime'],
                    'goto' => $this->generateUrl('course_thread_show', ['courseId' => $post['courseId'], 'threadId' => $thread['id']], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
                $this->answerQuestionSendSmsNotification($templateParams);
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
                $templateParams = [
                    'userId' => $thread['userId'],
                    'content' => $post['content'],
                    'title' => $classroom['title'],
                    'createdTime' => $thread['createdTime'],
                    'goto' => $this->generateUrl('classroom_thread_show', ['classroomId' => $classroom['id'], 'threadId' => $thread['id']], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
                $this->answerQuestionSendSmsNotification($templateParams);
            }
        }
    }

    protected function answerQuestionSendSmsNotification($templateParams)
    {
        $templateParams = ArrayToolkit::filter($templateParams, [
            'userId' => null,
            'content' => null,
            'title' => null,
            'createdTime' => null,
            'goto' => null,
        ]);

        $data = [
            'title' => $templateParams['title'],
            'day' => date('Y-m-d H:i:s', $templateParams['createdTime']),
            'content' => TextHelper::truncate($templateParams['content'], 30),
        ];

        return $this->getWeChatService()->sendSubscribeSms(MessageSubscribeTemplateUtil::TEMPLATE_ANSWER_QUESTION, [$templateParams['userId']], SmsType::QUESTION_ANSWER_NOTIFY, $data);
    }

    protected function askQuestionSendNotification($templateParams)
    {
        $templateCode = MessageSubscribeTemplateUtil::TEMPLATE_ASK_QUESTION;
        $templateParams = ArrayToolkit::filter($templateParams, [
            'thread' => [],
            'userIds' => [],
            'title' => null,
            'goto' => null,
        ]);

        $users = $this->getUserService()->searchUsers(
            ['userIds' => $templateParams['userIds'], 'locked' => 0],
            [],
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($users, 'id');
        if (empty($userIds)) {
            return;
        }
        $user = $this->getUserService()->getUser($templateParams['thread']['userId']);

        $templateId = $this->getWeChatService()->getSubscribeTemplateId($templateCode);
        if (empty($templateId)) {
            return $this->getWeChatService()->sendSubscribeSms(
                MessageSubscribeTemplateUtil::TEMPLATE_ASK_QUESTION,
                $userIds,
                SmsType::ANSWER_QUESTION_NOTIFY,
                [
                    'title' => $templateParams['title'],
                    'user' => $user['nickname'],
                    'question' => $templateParams['thread']['title'],
                    'time' => date('Y-m-d H:i', $templateParams['thread']['createdTime']),
                ]);
        }

        $subscribeRecords = $this->getWeChatService()->findOnceSubscribeRecordsByTemplateCodeUserIds($templateId, $userIds);
        $smsBatch = $this->getWeChatService()->sendSubscribeSms(
            MessageSubscribeTemplateUtil::TEMPLATE_ASK_QUESTION,
            array_diff($userIds, array_column($subscribeRecords, 'userId')),
            SmsType::ANSWER_QUESTION_NOTIFY,
            [
                'title' => $templateParams['title'],
                'user' => $user['nickname'],
                'question' => $templateParams['thread']['title'],
                'time' => date('Y-m-d H:i', $templateParams['thread']['createdTime']),
            ]);

        if (empty($subscribeRecords)) {
            return;
        }

        $data = [
            'thing4' => ['value' => $this->plainTextByLength($templateParams['title'], 20)],
            'thing2' => ['value' => $templateParams['thread']['title']],
            'date3' => ['value' => date('Y-m-d H:i', $templateParams['thread']['createdTime'])],
        ];

        $list = [];
        foreach ($subscribeRecords as $record) {
            $subscribeRecordConditions = [
                'templateCode' => $record['templateCode'],
                'templateType' => $record['templateType'],
                'toId' => $record['toId'],
                'isSend_LT' => 1,
            ];
            $subscribeRecordsCount = $this->getWeChatService()->searchSubscribeRecordCount($subscribeRecordConditions);
            $remainTime = $subscribeRecordsCount > 1 ? '（剩'.($subscribeRecordsCount - 1).'次通知）' : '（无剩余通知）';
            $data['thing2'] = ['value' => $this->plainTextByLength($templateParams['thread']['title'], 11).$remainTime];

            $list[] = array_merge([
                'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                'to_id' => $record['toId'],
                'goto' => ['type' => 'url', 'url' => $templateParams['goto']],
            ], [
                'template_id' => $templateId,
                'template_args' => $data,
            ]);
        }

        $result = $this->getWeChatService()->sendSubscribeWeChatNotification($templateCode, 'wechat_subscribe_notify_ask_question', $list, empty($smsBatch['id']) ? 0 : $smsBatch['id']);

        if ($result) {
            $this->getWeChatService()->updateSubscribeRecordsByIds(array_column($subscribeRecords, 'id'), ['isSend' => 1]);
        }
    }

    protected function sendTasksPublishNotification($tasks)
    {
        foreach ($tasks as $task) {
            if ('live' == $task['type']) {
                $this->deleteLiveNotificationJob($task);
                $this->registerLiveNotificationJob($task);
            }
            $this->deleteLessonPublishJob($task);
            $this->registerLessonNotificationJob(MessageSubscribeTemplateUtil::TEMPLATE_COURSE_UPDATE, $task);
        }
    }

    private function registerLessonNotificationJob($templateCode, $task)
    {
        $templateId = $this->getWeChatService()->getSubscribeTemplateId($templateCode);
        if (!empty($templateId)) {
            $job = [
                'name' => 'WeChatSubscribeNotificationJob_LessonPublish_'.$task['id'],
                'expression' => time(),
                'class' => 'Biz\WeChatSubscribeNotification\Job\LessonPublishNotificationJob',
                'misfire_threshold' => 60 * 60,
                'args' => [
                    'templateCode' => $templateCode,
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('my_course_show', ['id' => $task['courseId']], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
            ];
            $this->getSchedulerService()->register($job);
        }
    }

    private function registerLiveNotificationJob($task)
    {
        $hourTemplateId = $this->getWeChatService()->getSubscribeTemplateId(MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN, 'beforeOneHour');
        $dayTemplateId = $this->getWeChatService()->getSubscribeTemplateId(MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN, 'beforeOneDay');
        if (!empty($dayTemplateId) && $task['startTime'] >= (time() + 24 * 60 * 60)) {
            $job = [
                'name' => 'WeChatSubscribeNotification_LiveOneDay_'.$task['id'],
                'expression' => intval($task['startTime'] - 24 * 60 * 60),
                'class' => 'Biz\WeChatSubscribeNotification\Job\LiveNotificationJob',
                'misfire_threshold' => 60 * 60,
                'args' => [
                    'templateCode' => MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN,
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('my_course_show', ['id' => $task['courseId']], UrlGeneratorInterface::ABSOLUTE_URL),
                    'cloudSmsType' => 'sms_live_play_one_day',
                ],
            ];
            $this->getSchedulerService()->register($job);
        }

        if (!empty($hourTemplateId) && $task['startTime'] >= (time() + 60 * 60)) {
            $job = [
                'name' => 'WeChatSubscribeNotification_LiveOneHour_'.$task['id'],
                'expression' => intval($task['startTime'] - 60 * 60),
                'class' => 'Biz\WeChatSubscribeNotification\Job\LiveNotificationJob',
                'misfire_threshold' => 60 * 10,
                'args' => [
                    'templateCode' => MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN,
                    'taskId' => $task['id'],
                    'url' => $this->generateUrl('my_course_show', ['id' => $task['courseId']], UrlGeneratorInterface::ABSOLUTE_URL),
                    'cloudSmsType' => 'sms_live_play_one_hour',
                ],
            ];
            $this->getSchedulerService()->register($job);
        }

        $this->registerMultiClassNotificationJob($task);
    }

    private function registerMultiClassNotificationJob($task)
    {
        $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($task['courseId']);
        if (empty($multiClass) || empty($multiClass['liveRemindTime'])) {
            return;
        }

        $hourTemplateId = $this->getWeChatService()->getSubscribeTemplateId(MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN, 'beforeOneHour');
        $dayTemplateId = $this->getWeChatService()->getSubscribeTemplateId(MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN, 'beforeOneDay');
        if (empty($hourTemplateId) && empty($dayTemplateId)) {
            return;
        }

        if ($task['startTime'] < (time() + $multiClass['liveRemindTime'] * 60)) {
            return;
        }

        $smsType = '';
        if (!empty($hourTemplateId)) {
            $smsType = 'sms_live_play_one_hour';
        }

        if (!empty($dayTemplateId)) {
            $smsType = 'sms_live_play_one_day';
        }

        $job = [
            'name' => 'WeChatSubscribeNotification_LiveOpen_'.$task['id'],
            'expression' => intval($task['startTime'] - $multiClass['liveRemindTime'] * 60),
            'class' => 'Biz\WeChatSubscribeNotification\Job\LiveNotificationJob',
            'misfire_threshold' => 60 * 10,
            'args' => [
                'templateCode' => MessageSubscribeTemplateUtil::TEMPLATE_LIVE_OPEN,
                'taskId' => $task['id'],
                'url' => $this->generateUrl('my_course_show', ['id' => $task['courseId']], UrlGeneratorInterface::ABSOLUTE_URL),
                'cloudSmsType' => $smsType,
            ],
        ];
        $this->getSchedulerService()->register($job);
    }

    private function getCopiedTasks($task)
    {
        if (empty($task)) {
            return [];
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
        $this->deleteByJobName('WeChatSubscribeNotification_LessonPublish_'.$task['id']);
    }

    private function deleteLiveNotificationJob($task)
    {
        $this->deleteByJobName('WeChatSubscribeNotification_LiveOneHour_'.$task['id']);
        $this->deleteByJobName('WeChatSubscribeNotification_LiveOneDay_'.$task['id']);
        $this->deleteByJobName('WeChatSubscribeNotification_LiveOpen_'.$task['id']);
    }

    private function deleteByJobName($jobName)
    {
        $jobs = $this->getSchedulerService()->searchJobs(['name' => $jobName], [], 0, PHP_INT_MAX);

        foreach ($jobs as $job) {
            $this->getSchedulerService()->deleteJob($job['id']);
        }
    }

    /**
     * @param $route
     * @param $parameters
     * @param $referenceType
     *
     * @return mixed
     */
    private function generateUrl($route, $parameters, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        global $kernel;
        $router = $this->decorateRouter($kernel->getContainer()->get('router'));

        return $router->generate($route, $parameters, $referenceType);
    }

    private function decorateRouter($router)
    {
        $routerContext = $router->getContext();
        if ('localhost' == $routerContext->getHost()) {
            $url = $this->getSettingService()->node('site.url');
            if (!empty($url)) {
                $parsedUrl = parse_url($url);

                empty($parsedUrl['host']) ?: $routerContext->setHost($parsedUrl['host']);
                empty($parsedUrl['scheme']) ?: $routerContext->setScheme($parsedUrl['scheme']);
            }
        }

        return $router;
    }

    protected function getCourseNameByCourseId($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return empty($course['title']) ? $course['courseSetTitle'] : $course['title'];
    }

    protected function plainTextByLength($text, $length)
    {
        if (mb_strlen($text, 'utf-8') <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 1, 'utf-8').'…';
    }

    protected function getTemplateCodeAndLogNameByActivity($activity)
    {
        $templateCodes = [
            'testpaper' => MessageSubscribeTemplateUtil::TEMPLATE_EXAM_RESULT,
            'homework' => MessageSubscribeTemplateUtil::TEMPLATE_HOMEWORK_RESULT,
        ];

        return [
            empty($templateCodes[$activity['mediaType']]) ? '' : $templateCodes[$activity['mediaType']],
            "wechat_subscribe_notify_{$activity['mediaType']}",
        ];
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

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    public function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return MultiClassService
     */
    public function getMultiClassService()
    {
        return $this->getBiz()->service('MultiClass:MultiClassService');
    }
}
