<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\Service\PushService;
use Biz\System\Service\SettingService;

class PushServiceImpl extends BaseService implements PushService
{
    public function push($from, $to, $body)
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

    public function pushUserFollow($user, $friend)
    {
        $from = array(
            'id' => $friend['id'],
            'type' => 'user.follow',
        );

        $to = array(
            'id' => $friend['id'],
            'type' => 'user',
        );

        $body = array(
        );
    }

    public function pushUserUnFollow($user, $friend)
    {
    }

    public function pushArticleCreate($article)
    {
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

        $this->push($from, $to, $body);
    }

    public function pushAnnouncementCreate($announcement)
    {
        $target = $announcement['target'];

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

        $this->push($from, $to, $body);
    }

    public function pushThreadCreate($thread)
    {
        if ($thread['target']['type'] != 'course' || $thread['type'] != 'question') {
            return ['ignore' => 1];
        }

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
            $this->push($from, $to, $body);
        }
    }

    /**
     * @param $threadPost
     * 老师的回帖，要提醒提问的人
     */
    public function pushThreadPostCreate($threadPost)
    {
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

            $this->push($from, $to, $body);
        }
    }

    public function pushCourseJoin($member)
    {
    }

    public function pushCourseQuit($member)
    {
    }

    public function pushClassroomJoin($member)
    {
    }

    public function pushClassroomQuit($member)
    {
    }

    public function pushCouponReceived($coupon)
    {
    }

    protected function plainText($text, $count)
    {
        return mb_substr($text, 0, $count, 'utf-8');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
