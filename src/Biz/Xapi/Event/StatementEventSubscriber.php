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
use QiQiuYun\SDK\Auth;
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

        $statement = array(
            'user_id' => $user['id'],
            'verb' => 'finish',
            'target_id' => $taskResult['id'],
            'target_type' => 'activity',
        );

        $this->getXapiService()->createStatement($statement);
    }

    public function onQuestionMarkerFinish(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }
        $questionMarkerResult = $event->getSubject();

        $statement = array(
            'user_id' => $user['id'],
            'verb' => 'answered',
            'target_id' => $questionMarkerResult['id'],
            'target_type' => 'question',
        );

        $this->getXapiService()->createStatement($statement);
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
        $statement = array(
            'user_id' => $testpaperResult['userId'],
            'verb' => 'completed',
            'target_id' => $testpaperResult['id'],
            'target_type' => 'testpaper',
        );

        $this->getXapiService()->createStatement($statement);
    }

    protected function homeworkFinish($homeworkResult)
    {
        $statement = array(
            'user_id' => $homeworkResult['userId'],
            'verb' => 'completed',
            'target_id' => $homeworkResult['id'],
            'target_type' => 'homework',
        );

        $this->getXapiService()->createStatement($statement);
    }

    protected function exerciseFinish($exerciseFinish)
    {
        $statement = array(
            'user_id' => $exerciseFinish['userId'],
            'verb' => 'completed',
            'target_id' => $exerciseFinish['id'],
            'target_type' => 'exercise',
        );

        $this->getXapiService()->createStatement($statement);
    }

    public function onCourseNoteCreate(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }

        $note = $event->getSubject();
        $statement = array(
            'user_id' => $note['userId'],
            'verb' => 'noted',
            'target_id' => $note['id'],
            'target_type' => 'note',
        );

        $this->getXapiService()->createStatement($statement);
    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        if ($thread['type'] != 'question') {
            return;
        }
        $statement = array(
            'user_id' => $thread['userId'],
            'verb' => 'asked',
            'target_id' => $thread['id'],
            'target_type' => 'question',
        );

        $this->getXapiService()->createStatement($statement);
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
                'mobile' => empty($currentUser['mobile']) ? '' : $currentUser['mobile'],
                'homePage' => $host,
            ),
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

    protected function num_to_capital($num)
    {
        $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return $char[$num];
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

    public function createXAPIService()
    {
        $settings = $this->getSettingService()->get('storage', array());
        $siteSettings = $this->getSettingService()->get('site', array());

        $siteName = empty($siteSettings['name']) ? '' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];
        $accessKey = empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'];
        $secretKey = empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'];
        $auth = new Auth('9DdikSDLhmObBhE0t3mhN9UUl8FW2Zdh', 'jNqSV44Fx5kxBFc4VI840pLk8D6QeO86');

        return new \QiQiuYun\SDK\Service\XAPIService($auth, array(
            'base_uri' => 'http://192.168.4.214:8769/v1/xapi/', //推送的URL需要配置
            'school' => array(
                'accessKey' => $accessKey,
                'url' => $siteUrl,
                'name' => $siteName,
            ),
        ));
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }

    protected function convertMediaType($mediaType)
    {
        $list = array(
            'audio' => 'audio',
            'video' => 'video',
            'doc' => 'document',
            'ppt' => 'document',
            'testpaper' => 'testpaper',
            'homework' => 'homework',
            'exercise' => 'exercise',
            'download' => 'download',
            'live' => 'live',
            'text' => 'text',
            'flash' => 'flash',
        );

        return empty($list[$mediaType]) ? $mediaType : $list[$mediaType];
    }
}
