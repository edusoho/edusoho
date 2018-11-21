<?php

namespace Biz\Live\Service\Impl;

use AppBundle\Common\ESLiveToolkit;
use AppBundle\Common\JWTAuth;
use Biz\Live\Service\LiveService;
use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\Util\EdusohoLiveClient;
use AppBundle\Common\ArrayToolkit;

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
                'startTime'], $params['targetId'], $params['targetType'], $params['speakerId']);
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

    protected function buildCallbackUrl($startTime, $targetId, $targetType, $speakerId)
    {
        $baseUrl = $this->getBaseUrl();
        $args = array(
            'sources' => array('my', 'public', $targetType), //支持课程资料读取，还有我的资料库读取
            'courseId' => $targetId,
            'userId' => $speakerId,
        );

        $jwtToken = $this->getJWTAuth()->auth($args, array(
            'lifetime' => 60 * 60 * 4,
            'effect_time' => $startTime,
        ));

        $callbackUrl = ESLiveToolkit::generateCallback($baseUrl, $jwtToken);

        return $callbackUrl;
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

    /**
     * @return SettingService
     */
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

    /**
     * @return JWTAuth
     *
     * @throws \Exception
     */
    protected function getJWTAuth()
    {
        $setting = $this->getSettingService()->get('storage', array());
        $accessKey = $setting['cloud_access_key'] ?: '';
        $secretKey = $setting['cloud_secret_key'] ?: '';

        return new JWTAuth($accessKey, $secretKey);
    }
}
