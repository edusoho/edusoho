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
        $userId = $this->getCurrentUser();
        $client = new EdusohoLiveClient();
        $replays = $client->downloadReplayForSelfLive($liveId, $userId);
        if (!empty($replays['error'])) {
            throw LiveReplayException::NOTFOUND_LIVE_REPLAY();
        }

        return [
            'replays' => $replays,
        ];
    }
}
