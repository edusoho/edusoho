<?php

namespace Biz\Live\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\LiveWatermarkToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Live\Dao\LiveProviderTeacherDao;
use Biz\Live\Service\LiveService;
use Biz\System\Service\SettingService;
use Biz\User\UserException;
use Biz\Util\EdusohoLiveClient;

class LiveServiceImpl extends BaseService implements LiveService
{
    public function confirmLiveStatus($liveId)
    {
        try {
            $liveStatus = $this->getLiveClient()->getLiveRoomsInfo($liveId);
        } catch (\Exception $e) {
            throw $e;
        }

        return $liveStatus;
    }

    public function canExecuteLiveStatusJob($liveStatus, $jobType)
    {
        return 'startJob' === $jobType ? 'created' === $liveStatus : 'closed' !== $liveStatus;
    }

    protected function handleLiveStatus($liveStatus, $confirmStatus)
    {
        switch ($liveStatus) {
            case 'created':
                $status = 'start' === $confirmStatus ? 'start' : 'created';
                break;
            case 'start':
                $status = 'close' === $confirmStatus ? 'close' : 'start';
                break;
            default:
                $status = $liveStatus;
                break;
        }

        return $status;
    }

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
        if (!ArrayToolkit::requireds($params, ['startTime', 'endTime', 'speakerId', 'type'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $liveParams = [
            'summary' => empty($params['summary']) ? '' : $params['summary'],
            'title' => empty($params['title']) ? '' : $params['title'],
            'type' => $params['type'],
            'speaker' => $this->getSpeakerName($params['speakerId']),
            'authUrl' => empty($params['authUrl']) ? '' : $params['authUrl'],
            'jumpUrl' => empty($params['jumpUrl']) ? '' : $params['jumpUrl'],
            'liveLogoUrl' => $this->getLiveLogo(),
            'startTime' => $params['startTime'],
            'endTime' => $params['endTime'],
        ];

        if (!empty($params['roomType']) && $this->isRoomType($params['roomType'])) {
            $liveParams['roomType'] = $params['roomType'];
        }

        return $liveParams;
    }

    protected function filterUpdateParams($params)
    {
        $liveParams = ArrayToolkit::parts($params, [
            'liveId',
            'summary',
            'title',
        ]);

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
            $liveLogoUrl = $baseUrl . '/' . $liveLogo['live_logo'];
        }

        return $liveLogoUrl;
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

    public function isLiveProviderTeacherRequired($provider)
    {
        return EdusohoLiveClient::LIVE_PROVIDER_QUANSHI == $provider;
    }

    public function getLiveProviderTeacherId($userId, $provider)
    {
        if (!$this->isLiveProviderTeacherRequired($provider)) {
            return 0;
        }
        $liveProviderTeacher = $this->getLiveProviderTeacherDao()->getByUserIdAndProvider($userId, $provider);
        if ($liveProviderTeacher) {
            return $liveProviderTeacher['providerTeacherId'];
        }
        $user = $this->getUserService()->getUser($userId);
        $liveProviderTeacher = [
            'nickname' => $user['nickname'],
            'type' => 'email',
            'email' => $user['email'],
        ];
        if ($user['verifiedMobile']) {
            $liveProviderTeacher['type'] = 'mobile';
            $liveProviderTeacher['mobile'] = $user['verifiedMobile'];
        }
        $providerTeacher = $this->getLiveClient()->createLiveTeacher($liveProviderTeacher);
        if (!empty($providerTeacher['memberUserId'])) {
            $this->createLiveProviderTeacher([
                'userId' => $userId,
                'provider' => $provider,
                'providerTeacherId' => $providerTeacher['memberUserId'],
            ]);
        }

        return $providerTeacher['memberUserId'] ?? 0;
    }

    public function createLiveTicket($roomId, $user)
    {
        $liveTicket = $this->getLiveClient()->createLiveTicket($roomId, $user);

        return $this->addLiveCloudParams($liveTicket);
    }

    public function getLiveTicket($roomId, $ticketNo)
    {
        $liveTicket = $this->getLiveClient()->getLiveTicket($roomId, $ticketNo);

        return $this->addLiveCloudParams($liveTicket);
    }

    public function isESLive($provider)
    {
        if ($provider) {
            return EdusohoLiveClient::SELF_ES_LIVE_PROVIDER == $provider;
        }
        $liveAccount = $this->getLiveClient()->getLiveAccount();

        return 'liveCloud' == $liveAccount['provider'];
    }

    protected function createLiveProviderTeacher($liveProviderTeacher)
    {
        if (!ArrayToolkit::requireds($liveProviderTeacher, ['userId', 'provider', 'providerTeacherId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $liveProviderTeacher = ArrayToolkit::parts($liveProviderTeacher, ['userId', 'provider', 'providerTeacherId']);

        return $this->getLiveProviderTeacherDao()->create($liveProviderTeacher);
    }

    protected function isRoomType($liveRoomType)
    {
        return in_array($liveRoomType, [EdusohoLiveClient::LIVE_ROOM_LARGE, EdusohoLiveClient::LIVE_ROOM_SMALL]);
    }

    protected function getSpeakerName($speakerId)
    {
        $user = $this->getUserService()->getUser($speakerId);

        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($speakerId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        return $user['nickname'];
    }

    protected function addLiveCloudParams($liveTicket)
    {
        if (!empty($liveTicket['liveCloudSdk']['enable'])) {
            $liveTicket['liveCloudSdk'] = array_merge($liveTicket['liveCloudSdk'], [
                'watermark' => LiveWatermarkToolkit::build(),
            ]);
        }

        return $liveTicket;
    }

    protected function getBaseUrl()
    {
        return $this->biz['env']['base_url'];
    }

    /**
     * @return EdusohoLiveClient
     */
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

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return LiveProviderTeacherDao
     */
    protected function getLiveProviderTeacherDao()
    {
        return $this->createDao('Live:LiveProviderTeacherDao');
    }
}
