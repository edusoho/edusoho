<?php

namespace Biz\Xapi\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Xapi\Service\XapiService;
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
            'course.thread.create' => 'onCourseThreadCreate'
        );
    }

    public function onCourseTaskFinish(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }

        $taskResult = $event->getSubject();
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);

        if (empty($course) || !$this->getMemberService()->isCourseStudent($course['id'], $user['id'])) {
            return;
        }

        $task = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        if (empty($task)) {
            return;
        }

    }

    public function onExamFinish(Event $event)
    {
        // testpaper, exercise, homework
        $testpaperResult = $event->getSubject();

        $testpaperResult = $event->getArgument('useename');
    }

    public function onCourseNoteCreate(Event $event)
    {
        $note = $event->getSubject();


    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();

    }

    private function getActor()
    {
        $currentUser = $this->getCurrentUser();
        global $kernel;

        if (empty($kernel)) {
            return false;
        }

        $host = $kernel->getContainer()->get('request')->getHttpHost();

        return array(
            'account' => array(
                'id' => $currentUser['id'],
                'name' => $currentUser['nickname'],
                'email' => $currentUser['email'],
                'mobile' => $currentUser['mobile'],
                'homePage' => $host,
            )
        );
    }

    private function getSchoolInfo()
    {
        $storage = $this->getSettingService()->get('storage', array());
        $accessKey = $storage['cloud_access_key'];
        $site = $this->getSettingService()->get('site', array());
        $name = empty($site['name']) ? '' : $site['name'];
        $url = empty($site['url']) ? '' : $site['url'];

        return array(
            'id' => $accessKey,
            'name' => $name,
            'url' => $url,
        );
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
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:Setting');
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }


}