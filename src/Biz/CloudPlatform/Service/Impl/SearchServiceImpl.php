<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\SearchService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Group\Service\GroupService;
use Biz\IM\Service\ConversationService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Topxia\Api\Util\MobileSchoolUtil;

class SearchServiceImpl extends BaseService implements SearchService
{
    public function notifyDelete($params)
    {
        // TODO: Implement notifyDelete() method.
    }

    public function notifyUpdate($params)
    {
        // TODO: Implement notifyUpdate() method.
    }

    public function notifyUserCreate($user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = $this->convertUser($user, $profile);

        $this->notifyUpdate($user);
    }

    public function notifyUserUpdate($user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user =  $this->convertUser($user, $profile);
        $this->notifyUpdate($user);
    }

    public function notifyUserDelete($user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = $this->convertUser($user, $profile);

        $this->notifyDelete($user);
    }

    public function notifyCourseCreate($course)
    {
        $course = $this->convertCourse($course);
        $this->notifyUpdate($course);
    }

    public function notifyCourseUpdate($course)
    {
        $course = $this->convertCourse($course);
        $this->notifyUpdate($course);
    }

    public function notifyCourseDelete($course)
    {
        $course = $this->convertCourse($course);
        $this->notifyDelete($course);
    }

    /**
     * @param create = publish
     */
    public function notifyTaskCreate($task)
    {
        $this->notifyUpdate($task);
    }

    public function notifyTaskUpdate($task)
    {
        $this->notifyUpdate($task);
    }

    public function notifyTaskDelete($task)
    {
        $this->notifyDelete($task);
    }

    public function notifyArticleCreate($article)
    {
        $schoolUtil = new MobileSchoolUtil();
        $articleApp = $schoolUtil->getArticleApp();
        $articleApp['avatar'] = $this->getAssetUrl($articleApp['avatar']);
        $imSetting = $this->getSettingService()->get('app_im', array());
        $article['convNo'] = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';
        $article['app'] = $articleApp;
        $article = $this->convertArticle($article);

        $this->notifyUpdate($article);

    }

    public function notifyArticleUpdate($article)
    {
        $article = $this->convertArticle($article);

        $this->notifyUpdate($article);
    }

    public function notifyArticleDelete($article)
    {
        $article = $this->convertArticle($article);

        $this->notifyDelete($article);
    }

    public function notifyThreadCreate($thread)
    {
        $thread = $this->convertThread($thread, 'thread.create');

        $this->notifyUpdate($thread);
    }

    public function notifyThreadUpdate($thread)
    {
        $thread = $this->convertThread($thread, 'thread.create');

        $this->notifyUpdate($thread);
    }

    public function notifyThreadDelete($thread)
    {
        $thread = $this->convertThread($thread, 'thread.create');

        $this->notifyDelete($thread);
    }

    public function notifyOpenCourseCreate($openCourse)
    {
        $openCourse = $this->convertOpenCourse($openCourse);

        $this->notifyUpdate($openCourse);
    }

    public function notifyOpenCourseUpdate($openCourse)
    {
        $openCourse = $this->convertOpenCourse($openCourse);

        $this->notifyUpdate($openCourse);
    }

    public function notifyOpenCourseDelete($openCourse)
    {
        $openCourse = $this->convertOpenCourse($openCourse);

        $this->notifyDelete($openCourse);
    }

    public function notifyOpenCourseLessonCreate()
    {
        // TODO:暂无
    }

    public function notifyOpenCourseLessonUpdate()
    {
        // TODO:暂无
    }

    protected function convertHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);

        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, $this->getFileUrl($url), $text);
        }

        return $text;
    }

    protected function convertOpenCourse($openCourse)
    {
        $openCourse['smallPicture'] = $this->getFileUrl($openCourse['smallPicture']);
        $openCourse['middlePicture'] = $this->getFileUrl($openCourse['middlePicture']);
        $openCourse['largePicture'] = $this->getFileUrl($openCourse['largePicture']);
        $openCourse['about'] = $this->convertHtml($openCourse['about']);

        return $openCourse;
    }

    protected function convertThread($thread, $eventName)
    {
        if (strpos($eventName, 'course') === 0) {
            $thread['targetType'] = 'course';
            $thread['targetId'] = $thread['courseId'];
            $thread['relationId'] = $thread['taskId'];
        } elseif (strpos($eventName, 'group') === 0) {
            $thread['targetType'] = 'group';
            $thread['targetId'] = $thread['groupId'];
            $thread['relationId'] = 0;
        }

        // id, target, relationId, title, content, postNum, hitNum, updateTime, createdTime
        $converted = array();

        $converted['id'] = $thread['id'];
        $converted['target'] = $this->getTarget($thread['targetType'], $thread['targetId']);
        $converted['relationId'] = $thread['relationId'];
        $converted['type'] = empty($thread['type']) ? 'none' : $thread['type'];
        $converted['userId'] = empty($thread['userId']) ? 0 : $thread['userId'];
        $converted['title'] = $thread['title'];
        $converted['content'] = $this->convertHtml($thread['content']);
        $converted['postNum'] = $thread['postNum'];
        $converted['hitNum'] = $thread['hitNum'];
        $converted['updateTime'] = isset($thread['updateTime']) ? $thread['updateTime'] : $thread['updatedTime'];
        $converted['createdTime'] = $thread['createdTime'];

        return $converted;
    }

    protected function convertArticle($article)
    {
        $article['thumb'] = $this->getFileUrl($article['thumb']);
        $article['originalThumb'] = $this->getFileUrl($article['originalThumb']);
        $article['picture'] = $this->getFileUrl($article['picture']);
        $article['body'] = $article['title'];

        return $article;
    }

    protected function convertCourse($course)
    {
        $course['smallPicture'] = isset($course['cover']['small']) ? $this->getFileUrl($course['cover']['small']) : '';
        $course['middlePicture'] = isset($course['cover']['middle']) ? $this->getFileUrl($course['cover']['middle']) : '';
        $course['largePicture'] = isset($course['cover']['large']) ? $this->getFileUrl($course['cover']['large']) : '';
        $course['about'] = isset($course['summary']) ? $this->convertHtml($course['summary']) : '';

        return $course;
    }

    protected function convertUser($user, $profile = array())
    {
        // id, nickname, title, roles, point, avatar(最大那个), about, updatedTime, createdTime
        $converted = array();
        $converted['id'] = $user['id'];
        $converted['nickname'] = $user['nickname'];
        $converted['title'] = $user['title'];

        if (!is_array($user['roles'])) {
            $user['roles'] = explode('|', $user['roles']);
        }

        $converted['roles'] = in_array('ROLE_TEACHER', $user['roles']) ? 'teacher' : 'student';
        $converted['point'] = $user['point'];
        $converted['avatar'] = $this->getFileUrl($user['largeAvatar']);
        $converted['about'] = empty($profile['about']) ? '' : $profile['about'];
        $converted['updatedTime'] = $user['updatedTime'];
        $converted['createdTime'] = $user['createdTime'];

        return $converted;
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return $path;
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";

        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }

        $path = "http://{$_SERVER['HTTP_HOST']}/assets/{$path}";

        return $path;
    }

    protected function getTarget($type, $id)
    {
        $target = array('type' => $type, 'id' => $id);

        switch ($type) {
            case 'course':
                $course = $this->getCourseService()->getCourse($id);
                $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
                $target['title'] = $course['title'];
                $target['image'] = empty($courseSet['cover']['small']) ? '' : $this->getFileUrl(
                    $courseSet['cover']['small']
                );
                $target['teacherIds'] = empty($course['teacherIds']) ? array() : $course['teacherIds'];
                $conv = $this->getConversationService()->getConversationByTarget($id, 'course-push');
                $target['convNo'] = empty($conv) ? '' : $conv['no'];
                break;
            case 'lesson':
                $task = $this->getTaskService()->getTask($id);
                $target['title'] = $task['title'];
                break;
            case 'classroom':
                $classroom = $this->getClassroomService()->getClassroom($id);
                $target['title'] = $classroom['title'];
                $target['image'] = $this->getFileUrl($classroom['smallPicture']);
                break;
            case 'group':
                $group = $this->getGroupService()->getGroup($id);
                $target['title'] = $group['title'];
                $target['image'] = $this->getFileUrl($group['logo']);
                break;
            case 'global':
                $schoolUtil = new MobileSchoolUtil();
                $schoolApp = $schoolUtil->getAnnouncementApp();
                $target['title'] = '网校公告';
                $target['id'] = $schoolApp['id'];
                $target['image'] = $this->getFileUrl($schoolApp['avatar']);
                $setting = $this->getSettingService()->get('app_im', array());
                $target['convNo'] = empty($setting['convNo']) ? '' : $setting['convNo'];
                break;
            default:
                // code...
                break;
        }

        return $target;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * 根据thread区域不同返回不同的Service
     */
    protected function getThreadService($type = '')
    {
        if ($type == 'course') {
            return $this->createService('Course:ThreadService');
        }

        if ($type == 'group') {
            return $this->createService('Group:ThreadService');
        }

        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ConversationService
     */
    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}