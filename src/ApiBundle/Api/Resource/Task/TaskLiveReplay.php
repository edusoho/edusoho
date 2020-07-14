<?php

namespace ApiBundle\Api\Resource\Task;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\MemberException;
use Biz\Task\TaskException;
use Biz\CloudPlatform\CloudAPIFactory;
use AppBundle\Common\SettingToolkit;

class TaskLiveReplay extends AbstractResource
{
    public function add(ApiRequest $request, $taskId)
    {
        $canLearn = $this->getCourseService()->canLearnTask($taskId);
        if ('success' != $canLearn['code']) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if ('live' != $task['type']) {
            throw TaskException::TYPE_INVALID();
        }

        if ('videoGenerated' == $activity['ext']['replayStatus']) {
            throw TaskException::LIVE_REPLAY_INVALID();
        }

        $device = $request->request->get('device');
        $copyId = empty($activity['copyId']) ? $activity['id'] : $activity['copyId'];
        $replays = $this->getLiveReplayService()->findReplayByLessonId($copyId);
        if (!$replays) {
            throw TaskException::LIVE_REPLAY_NOT_FOUND();
        }

        $visibleReplays = array_filter($replays, function ($replay) {
            return empty($replay['hidden']);
        });

        $visibleReplays = array_values($visibleReplays);

        $user = $this->getCurrentUser();
        $response = array(
            'url' => '',
            'extra' => array(
                'provider' => '',
                'lessonId' => $activity['id'],
            ),
            'device' => $device,
        );

        $protocol = $this->container->get('request')->getScheme();
        $replays = array();
        $sendParams = array(
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
            'device' => $device,
            'protocol' => $protocol
        );

        foreach ($visibleReplays as $index => $visibleReplay) {
            $sendParams['replayId'] = $visibleReplays[$index]['replayId'];
            if (!empty($activity['syncId'])) {
                $replays[] = $this->getS2B2CFacadeService()->getS2B2CService()->createAppLiveReplayList($activity['ext']['liveId'], $sendParams);
            } else {
                $replays[] = CloudAPIFactory::create('root')->get("/lives/{$activity['ext']['liveId']}/replay", $sendParams);
            }

            $replays[$index]['title'] = $visibleReplay['title'];
        }

        $response = $replays[0];
        $response['replays'] = $replays;

        return $response;
    }

    protected function isSetEncryption()
    {
        $enable_hls_encryption_plus = SettingToolkit::getSetting('storage.enable_hls_encryption_plus');

        if ($enable_hls_encryption_plus) {
            return true;
        }

        return false;
    }

    protected function getEsLiveReplayUrl($globalId, $options)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);
        if (empty($file)) {
            throw TaskException::LIVE_REPLAY_INVALID();
        }

        if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
            if (isset($file['convertParams']['convertor']) && ('HLSEncryptedVideo' == $file['convertParams']['convertor'])) {
                $tokenFields = array(
                    'data' => array(
                        'id' => $file['id'],
                        'fromApi' => $options['fromApi'],
                        'type' => $options['type'],
                        'replayId' => $options['replayId'],
                    ),
                    'times' => $options['times'],
                    'duration' => $options['duration'],
                );

                $token = $this->getTokenService()->makeToken('hls.playlist', $tokenFields);

                return $this->container->get('request')->getSchemeAndHttpHost()."/hls/0/playlist/{$token['token']}.m3u8?hideBeginning=1&format={$options['format']}&line=".$options['line'];
            } else {
                throw new \RuntimeException('当前视频格式不能被播放！');
            }
        } else {
            if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                $key = $file['metas']['hd']['key'];
            } else {
                $key = $file['hashId'];
            }

            if ($key) {
                $result = $this->getCloudFileService()->player($file['globalId']);
            }
        }

        return isset($result['url']) ? $result['url'] : '';
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getMediaService()
    {
        return $this->service('Media:MediaService');
    }

    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    protected function getLiveReplayService()
    {
        return $this->service('Course:LiveReplayService');
    }

    protected function getCloudFileService()
    {
        return $this->service('CloudFile:CloudFileService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
