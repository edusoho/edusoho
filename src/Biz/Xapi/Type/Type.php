<?php

namespace Biz\Xapi\Type;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\File\Service\UploadFileService;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Context\BizAware;

abstract class Type extends BizAware
{
    abstract public function package($statement);

    abstract public function packages($statements);

    protected function createService($alias)
    {
        return $this->biz->service($alias);
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
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
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

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getActor($userId)
    {
        $currentUser = $this->getUserService()->getUser($userId);
        $userProfile = $this->getUserService()->getUserProfile($userId);
        $siteSettings = $this->getSettingService()->get('site', array());

        $host = empty($siteSettings['url']) ? '' : $siteSettings['url'];

        return array(
            'account' => array(
                'id' => $currentUser['id'],
                'name' => $currentUser['nickname'],
                'email' => empty($currentUser['email']) ? '' : md5($currentUser['email']),
                'phone' => empty($userProfile['mobile']) ? '' : md5($userProfile['mobile']),
                'homePage' => $host,
            ),
        );
    }

    /**
     * @return \QiQiuYun\SDK\Service\XAPIService
     */
    public function createXAPIService()
    {
        return $this->getXapiService()->getXapiSdk();
    }

    protected function num_to_capital($num)
    {
        $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return $char[$num];
    }

    protected function convertMediaType($mediaType)
    {
        $list = array(
            'audio' => 'audio',
            'video' => 'video',
            'doc' => 'document',
            'ppt' => 'document',
            'discuss' => 'online-discussion',
            'testpaper' => 'testpaper',
            'homework' => 'homework',
            'exercise' => 'exercise',
            'download' => 'document',
            'live' => 'live',
            'text' => 'document',
            'flash' => 'document',
        );

        return empty($list[$mediaType]) ? $mediaType : $list[$mediaType];
    }
}
