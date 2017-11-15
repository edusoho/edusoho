<?php

namespace Biz\Xapi\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\File\Service\UploadFileService;
use Biz\Marker\Service\MarkerService;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatementEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.finish' => 'onCourseTaskFinish',
            'exam.finish' => 'onExamFinish',
            'course.note.create' => 'onCourseNoteCreate',
            'course.thread.create' => 'onCourseThreadCreate',
            'question_marker.finish' => 'onQuestionMarkerFinish',
        );
    }

    public function onCourseTaskFinish(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
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
        if ($thread['type'] != 'question') {
            return;
        }

        $this->createStatement($thread['userId'], 'asked', $thread['id'], 'question');
    }

    private function createStatement($userId, $verb, $targetId, $targetType)
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
                'occur_time' => time(),
            );

            $this->getXapiService()->createStatement($statement);
        } catch (\Exception $e) {
            file_put_contents('1.txt', $e->getMessage().PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return QuestionMarkerResultService
     */
    protected function getQuestionMarkerResultService()
    {
        return $this->createService('Marker:QuestionMarkerResultService');
    }

    /**
     * @return MarkerService
     */
    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }

    /**
     * @return QuestionMarkerService
     */
    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker:QuestionMarkerService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }
}
