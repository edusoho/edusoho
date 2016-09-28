<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LessonReplay extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $lesson = $this->getCourseService()->getLesson($id);

        if (!$lesson) {
            return $this->error('500', '课时不存在！');
        }

        if ($lesson['replayStatus'] == 'videoGenerated') {
            return json_decode($this->sendRequest('GET', $this->getHttpHost().$app['url_generator']->generate('get_lesson', array('id' => $lesson['id'])), array(sprintf('X-Auth-Token: %s', $request->headers->get('X-Auth-Token')))), true);
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
