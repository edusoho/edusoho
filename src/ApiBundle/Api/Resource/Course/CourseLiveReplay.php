<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\LiveReplayException;
use Biz\Util\EdusohoLiveClient;

class CourseLiveReplay extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $liveId)
    {
        $user = $this->getCurrentUser();
        $client = new EdusohoLiveClient();
        $replay = $client->downloadReplayForSelfLive($liveId, $user['id']);
        if (!empty($replay['error'])) {
            throw LiveReplayException::NOTFOUND_LIVE_REPLAY();
        }

        return [
            'url' => $replay['url'],
            'token' => $replay['token'],
            'roomId' => $replay['roomId'],
            'type' => 'selfLive', //目前只有自研直播，多供应商直播支持后，可以扩展类型
        ];
    }
}
