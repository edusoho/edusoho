<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\DeviceToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\LiveReplayException;
use Biz\Course\Service\LiveReplayService;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\LiveCourseService;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourseReplay extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if ($lesson['replayStatus'] == 'videoGenerated') {
            throw OpenCourseException::LESSON_TYPE_INVALID();
        }

        $device = $request->query->get('device', DeviceToolkit::isMobileClient() ? 'mobile' : 'desktop');
        $replays = $this->getLiveReplayService()->findReplaysByCourseIdAndLessonId($courseId, $lessonId, 'liveOpen');

        if (!$replays) {
            throw LiveReplayException::NOTFOUND_LIVE_REPLAY();
        }

        $visibleReplays = array_filter($replays, function ($replay) {
            return empty($replay['hidden']);
        });

        $visibleReplays = array_values($visibleReplays);

        $user = $this->getCurrentUser();

        $user['userId'] = $user->isLogin() ? $user['id'] : (int) ($this->getMillisecond()) * 1000 + rand(0, 999);
        $user['nickname'] = $user->isLogin() ? $user['nickname'] : '游客'.$this->getRandomString(8);

        $protocol = $request->getHttpRequest()->getScheme();
        $replays = array();

        foreach ($visibleReplays as $index => $visibleReplay) {
            $replays[] = CloudAPIFactory::create('root')->get("/lives/{$lesson['mediaId']}/replay", array(
                'replayId' => $visibleReplays[$index]['replayId'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'device' => $device,
                'protocol' => $protocol, ));
            $replays[$index]['title'] = $visibleReplay['title'];
        }

        return array('replays' => $replays);
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }

    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());

        return (float) sprintf('%.0f', ((float) ($t1) + (float) ($t2)) * 1000);
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return LiveCourseService
     */
    protected function getLiveCourseService()
    {
        return $this->getBiz()->service('OpenCourse:LiveCourseService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->service('Course:LiveReplayService');
    }
}
