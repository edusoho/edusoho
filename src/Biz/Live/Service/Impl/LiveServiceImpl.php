<?php

namespace Biz\Live\Service\Impl;

use Biz\Live\Service\LiveService;
use Biz\BaseService;
use Biz\Util\EdusohoLiveClient;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\AthenaLiveToolkit;

class LiveServiceImpl extends BaseService implements LiveService
{
    public function createLiveRoom($params)
    {
        $liveParams = $this->filterCreateParams($params);

        try {
            $live = $this->getLiveClient()->createLive($liveParams);
        } catch (\Exception $e) {
            throw $e;
        }

        if (!empty($live['error'])) {
            throw $this->createServiceException($live['error']);
        }

        return $live;
    }

    public function updateLiveRoom($liveId, $params)
    {
        $params['liveId'] = $liveId;
        $liveParams = $this->filterUpdateParams($params);

        try {
            $live = $this->getLiveClient()->updateLive($liveParams);

            return $live;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteLiveRoom($liveId)
    {
        if (empty($liveId)) {
            return;
        }

        try {
            return $this->getLiveClient()->deleteLive($liveId);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function filterCreateParams($params)
    {
        if (!ArrayToolkit::requireds($params, array('startTime', 'endTime', 'speakerId', 'type'))) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        $liveParams = array(
            'summary' => empty($params['summary']) ? '' : $params['summary'],
            'title' => empty($params['title']) ? '' : $params['title'],
            'type' => $params['type'],
            'speaker' => $this->getSpeakerName($params['speakerId']),
            'authUrl' => empty($params['authUrl']) ? '' : $params['authUrl'],
            'jumpUrl' => empty($params['jumpUrl']) ? '' : $params['jumpUrl'],
            'liveLogoUrl' => $this->getLiveLogo(),
            'startTime' => $params['startTime'],
            'endTime' => $params['endTime'],
        );

        if (!empty($params['isCallback'])) {
            $liveParams['callback'] = $this->buildCallbackUrl($params[
                'endTime'], $params['targetId'], $params['targetType']);
        }

        if (!empty($params['roomType']) && $this->isRoomType($params['roomType'])) {
            $liveParams['roomType'] = $params['roomType'];
        }

        return $liveParams;
    }

    protected function filterUpdateParams($params)
    {
        $liveParams = ArrayToolkit::parts($params, array(
            'liveId',
            'summary',
            'title',
        ));

        if (!empty($params['startTime']) && $params['startTime'] > time()) {
            $liveParams['startTime'] = $params['startTime'];
            $liveParams['endTime'] = $params['endTime'];
        }

        if (!empty($params['roomType']) && !empty($params['startTime']) && $this->canUpdateRoomType($params['startTime'])) {
            $liveParams['roomType'] = $params['roomType'];
        }

        return $params;
    }

    protected function getLiveLogo()
    {
        $liveLogo = $this->getSettingService()->get('course');
        $liveLogoUrl = '';

        $baseUrl = $this->getBaseUrl();
        if (!empty($liveLogo) && !empty($liveLogo['live_logo'])) {
            $liveLogoUrl = $baseUrl.'/'.$liveLogo['live_logo'];
        }

        return $liveLogoUrl;
    }

    protected function buildCallbackUrl($endTime, $targetId, $targetType)
    {
        $duration = $endTime + 86400 - time();
        $args = array(
            'duration' => $duration,
            'data' => array(
                'courseId' => $targetId,
                'type' => $targetType,
            ),
        );
        $token = $this->getTokenService()->makeToken('live.callback', $args);

        $baseUrl = $this->getBaseUrl();

        return AthenaLiveToolkit::generateCallback($baseUrl, $token['token'], $targetId);
    }

    public function canUpdateRoomType($liveStartTime)
    {
        $timeDiff = $liveStartTime - time();
        $disableSeconds = 3600 * 2;

        if ($timeDiff < 0 || ($timeDiff > 0 && $timeDiff <= $disableSeconds)) {
            return 0;
        }

        return 1;
    }

    protected function isRoomType($liveRoomType)
    {
        return in_array($liveRoomType, array(EdusohoLiveClient::LIVE_ROOM_LARGE, EdusohoLiveClient::LIVE_ROOM_SMALL));
    }

    protected function getSpeakerName($speakerId)
    {
        $user = $this->getUserService()->getUser($speakerId);

        if (empty($user)) {
            throw $this->createNotFoundException('Speaker not found');
        }

        return $user['nickname'];
    }

    protected function getBaseUrl()
    {
        return $this->biz['env']['base_url'];
    }

    protected function getLiveClient()
    {
        return $this->biz['educloud.live_client'];
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
