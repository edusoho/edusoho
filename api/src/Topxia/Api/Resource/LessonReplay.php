<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class LessonReplay extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $lesson = $this->getCourseService()->getLesson($id);

        if (!$lesson) {
            return $this->error('500', '课时不存在！');
        }

        $device = $request->query->get('device');
        $replay = $this->getCourseService()->getCourseLessonReplayByLessonId($id);

        if (!$replay) {
            return $this->error('500', '课时回放不存在！');
        }

        $user = $this->getCurrentUser();
        try {
            $res = CloudAPIFactory::create('root')->get("/lives/{$lesson['mediaId']}/replay", array('replayId' => $replay[0]['replayId'], 'userId' => $user['id'], 'nickname' => $user['nickname'], 'device' => $device));
        } catch (Exception $e) {
            return $this->error('503', '获取回放失败！');
        }

        return $res;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
