<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\SettingToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class LessonReplay extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $task = $this->getTaskService()->getTask($id);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (!$task) {
            return $this->error('500', '课时不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($task['courseId'])) {
            return array('message' => 'Access Denied');
        }

        if ($activity['ext']['replayStatus'] == 'videoGenerated') {
            return json_decode($this->sendRequest('GET', $this->getHttpHost().$app['url_generator']->generate('get_lesson', array('id' => $task['id'])), array(sprintf('X-Auth-Token: %s', $request->headers->get('X-Auth-Token')))), true);
        }

        $device = $request->query->get('device');
        $replays = $this->getLiveReplayService()->findReplayByLessonId($task['activityId']);

        if (!$replays) {
            return $this->error('500', '课时回放不存在！');
        }

        $visableReplays = array();
        foreach ($replays as $replay) {
            if ($replay['hidden'] == 0) {
                $visableReplays[] = $replay;
            }
        }

        $user = $this->getCurrentUser();
        $response = array(
            'url' => '',
            'extra' => array(
                'provider' => '',
                'lessonId' => $activity['id'],

            ),
            'device' => $device,
        );
        try {
            // play es replay
            if ($activity['ext']['liveProvider'] == 5) {
                //获取globalid
                $globalId = $visableReplays[0]['globalId'];
                $options = array(
                    'fromApi' => !$this->isSetEncryption(),
                    'times' => 2,
                    'line' => $request->query->get('line', ''),
                    'format' => $request->query->get('format', ''),
                    'type' => 'apiLessonReplay',
                    'replayId' => $visableReplays[0]['id'],
                );
                $response['url'] = $this->getMediaService()->getVideoPlayUrl($globalId, $options);
                $response['extra']['provider'] = 'longinus';
            } else {
                $response = CloudAPIFactory::create('root')->get("/lives/{$activity['ext']['liveId']}/replay", array('replayId' => $visableReplays[0]['replayId'], 'userId' => $user['id'], 'nickname' => $user['nickname'], 'device' => $device));
            }
        } catch (Exception $e) {
            return $this->error('503', '获取回放失败！');
        }

        return $response;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function isSetEncryption()
    {
        $enable_hls_encryption_plus = SettingToolkit::getSetting('storage.enable_hls_encryption_plus');

        if ($enable_hls_encryption_plus) {
            return true;
        }

        return false;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getMediaService()
    {
        return $this->getServiceKernel()->createService('Media:MediaService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }

    protected function getLiveReplayService()
    {
        return $this->getServiceKernel()->createService('Course:LiveReplayService');
    }

    protected function sendRequest($method, $url, $headers = array(), $params = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, 'Open EduSoho App Client 1.0');

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if (strtoupper($method) == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
