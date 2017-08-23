<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\Service\PushService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Group\Service\GroupService;
use Biz\Group\Service\ThreadService;
use Biz\IM\Service\ConversationService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Topxia\Api\Util\MobileSchoolUtil;

class PushServiceImpl extends BaseService implements PushService
{
    public function push()
    {
        // TODO: Implement push() method.
    }

    public function pushArticleCreate($article)
    {
        $schoolUtil = new MobileSchoolUtil();
        $articleApp = $schoolUtil->getArticleApp();
        $articleApp['avatar'] = $this->getAssetUrl($articleApp['avatar']);
        $imSetting = $this->getSettingService()->get('app_im', array());
        $article['convNo'] = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';
        $article['app'] = $articleApp;
        $article = $this->convertArticle($article);

        $from = array(
            'id' => $article['app']['id'],
            'type' => $article['app']['code'],
        );

        $to = array(
            'type' => 'global',
            'convNo' => empty($article['convNo']) ? '' : $article['convNo'],
        );

        $body = array(
            'type' => 'news.create',
            'id' => $article['id'],
            'title' => $article['title'],
            'image' => $article['thumb'],
            'content' => $this->plainText($article['body'], 50),
        );

        $this->pushIM($from, $to, $body);
    }

    public function pushAnnouncementCreate($announcement)
    {
        $target = $this->getTarget($announcement['targetType'], $announcement['targetId']);
        $announcement['target'] = $target;

        $from = array(
            'type' => $target['type'],
            'id' => $target['id'],
        );

        $to = array(
            'type' => $target['type'],
            'id' => $target['id'],
            'convNo' => empty($target['convNo']) ? '' : $target['convNo'],
        );

        $body = array(
            'id' => $announcement['id'],
            'type' => 'announcement.create',
            'title' => $this->plainText($announcement['content'], 50),
        );

        $this->pushIM($from, $to, $body);
    }

    public function pushThreadCreate($thread)
    {
        $thread = $this->convertThread($thread, 'thread.create');

        $from = array(
            'type' => $thread['target']['type'],
            'id' => $thread['target']['id'],
        );

        $to = array(
            'type' => $thread['target']['type'],
            'id' => $thread['target']['id'],
            'convNo' => empty($target['convNo']) ? '' : $target['convNo'],
        );

        $body = array(
            'type' => 'question.created',
            'threadId' => $thread['id'],
            'courseId' => $thread['target']['id'],
            'lessonId' => $thread['relationId'],
            'questionCreatedTime' => $thread['createdTime'],
            'questionTitle' => $thread['title'],
        );

        foreach ($thread['target']['teacherIds'] as $teacherId) {
            $to['id'] = $teacherId;
            $this->pushIM($from, $to, $body);
        }
    }

    /**
     * @param $threadPost
     * 老师的回帖，要提醒提问的人
     */
    public function pushThreadPostCreate($threadPost)
    {
        $threadPost = $this->convertThreadPost($threadPost, 'thread.post.create');
        if ($threadPost['target']['type'] != 'course' || empty($threadPost['target']['teacherIds'])) {
            return ['ignore' => 1];
        }

        if ($threadPost['thread']['type'] != 'question') {
            return ['ignore' => 1];
        }

        foreach ($threadPost['target']['teacherIds'] as $teacherId) {
            if ($teacherId != $threadPost['userId']) {
                continue;
            }

            $from = array(
                'type' => $threadPost['target']['type'],
                'id' => $threadPost['target']['id'],
                'image' => $threadPost['target']['image'],
            );

            $to = array(
                'type' => 'user',
                'id' => $threadPost['thread']['userId'],
                'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
            );

            $body = array(
                'type' => 'question.answered',
                'threadId' => $threadPost['threadId'],
                'courseId' => $threadPost['target']['id'],
                'lessonId' => $threadPost['thread']['relationId'],
                'questionCreatedTime' => $threadPost['thread']['createdTime'],
                'questionTitle' => $threadPost['thread']['title'],
                'postContent' => $threadPost['content'],
            );

            $this->pushIM($from, $to, $body);
        }
    }

    public function pushCourseJoin($member)
    {
    }

    protected function pushIM($from, $to, $body)
    {
        $setting = $this->getSettingService()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return;
        }

        $params = array(
            'fromId' => 0,
            'fromName' => '系统消息',
            'toName' => '全部',
            'body' => array(
                'v' => 1,
                't' => 'push',
                'b' => $body,
                's' => $from,
                'd' => $to,
            ),
            'convNo' => empty($to['convNo']) ? '' : $to['convNo'],
        );

        if ($to['type'] == 'user') {
            $params['toId'] = $to['id'];
        }

        if (empty($params['convNo'])) {
            return;
        }

        try {
            $api = IMAPIFactory::create();
            $result = $api->post('/push', $params);

            $setting = $this->getSettingService()->get('developer', array());
            if (!empty($setting['debug'])) {
                IMAPIFactory::getLogger()->debug('API RESULT', !is_array($result) ? array() : $result);
            }
        } catch (\Exception $e) {
            IMAPIFactory::getLogger()->warning('API REQUEST ERROR:'.$e->getMessage());
        }
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

    protected function convertThreadPost($threadPost, $eventName)
    {
        if (strpos($eventName, 'course') === 0) {
            $threadPost['targetType'] = 'course';
            $threadPost['targetId'] = $threadPost['courseId'];
            $threadPost['thread'] = $this->convertThread(
                $this->getThreadService('course')->getThread($threadPost['courseId'], $threadPost['threadId']),
                $eventName
            );
        } elseif (strpos($eventName, 'group') === 0) {
            $thread = $this->getThreadService('group')->getThread($threadPost['threadId']);
            $threadPost['targetType'] = 'group';
            $threadPost['targetId'] = $thread['groupId'];
            $threadPost['thread'] = $this->convertThread($thread, $eventName);
        } else {
            $threadPost['thread'] = $this->convertThread(
                $this->getThreadService()->getThread($threadPost['threadId']),
                $eventName
            );
        }

        // id, threadId, content, userId, createdTime, target, thread
        $converted = array();

        $converted['id'] = $threadPost['id'];
        $converted['threadId'] = $threadPost['threadId'];
        $converted['content'] = $this->convertHtml($threadPost['content']);
        $converted['userId'] = $threadPost['userId'];
        $converted['target'] = $this->getTarget($threadPost['targetType'], $threadPost['targetId']);
        $converted['thread'] = $threadPost['thread'];
        $converted['createdTime'] = $threadPost['createdTime'];

        return $converted;
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

    protected function plainText($text, $count)
    {
        return mb_substr($text, 0, $count, 'utf-8');
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
