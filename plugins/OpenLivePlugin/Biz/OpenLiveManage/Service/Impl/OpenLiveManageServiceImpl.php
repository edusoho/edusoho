<?php

namespace OpenLivePlugin\Biz\OpenLiveManage\Service\Impl;

use AppBundle\Common\StringToolkit;
use Biz\BaseService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use OpenLivePlugin\Biz\OpenLiveManage\Service\OpenLiveManageService;
use OpenLivePlugin\Biz\OpenLivePlatform\PlatformSdk;
use OpenLivePlugin\Common\ArrayToolkit;

class OpenLiveManageServiceImpl extends BaseService implements OpenLiveManageService
{
    public function getLive($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getLive($id));
    }

    public function createLive($liveData)
    {
        $liveFields = $this->generateCreateFields($liveData);

        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->createLive($liveFields), ['success' => false], true);
    }

    public function editLive($liveData)
    {
        $liveFields = $this->generateEditFields($liveData);

        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->editLive($liveFields), ['success' => false], true);
    }

    public function searchLives($conditions)
    {
        $liveConditions = $this->generateConditions($conditions);
        $searchResult = $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->searchLives($liveConditions), [], true);
        if (!$searchResult['success']) {
            $this->setFlashMessage('danger', $searchResult['errorMsg']);
        }

        return $searchResult;
    }

    public function publishLive($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->publishLive($id), ['success' => false], true);
    }

    public function unpublishLive($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->unpublishLive($id), ['success' => false], true);
    }

    public function closeLive($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->closeLive($id), ['success' => false], true);
    }

    public function deleteLive($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->deleteLive($id), ['success' => false], true);
    }

    public function getLiveTeacherEntryUrl($id, $userInfo)
    {
       return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getTeacherEntryUrl($id, $userInfo), ['clientUrl' => '', 'webUrl' => '']);
    }

    public function getLiveShareUrl($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getLiveShareUrl($id), ['share_url' => '']);
    }

    public function searchLiveOnlineNumRecords($id, $conditions = [])
    {
        $searchConditions = [];
        if (!empty($conditions['startDate'])) {
            $searchConditions['save_time_GT'] = strtotime($conditions['startDate']);
        }
        if (!empty($conditions['endDate'])) {
            $searchConditions['save_time_LT'] = strtotime($conditions['endDate']);
        }

        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->searchLiveOnlineNumRecords($id, $searchConditions));
    }

    public function getLiveVisitorReport($id, $conditions = [])
    {
        $searchConditions = [];
        if (!empty($conditions['visitorNickname'])) {
            if (preg_match('/^1\d{10}$/', $conditions['visitorNickname'])) {
                $searchConditions['mobile'] = $conditions['visitorNickname'];
            } else {
                $searchConditions['student_nickname_like'] = $conditions['visitorNickname'];
            }
        }
        if (isset($conditions['offset']) && isset($conditions['limit'])) {
            $searchConditions['offset'] = (int) $conditions['offset'];
            $searchConditions['limit'] = (int) $conditions['limit'];
        }
        if (!empty($conditions['sorts']) && is_array($conditions['sorts'])) {
            $searchConditions['sorts'] = $conditions['sorts'];
        }

        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getLiveVisitorReport($id, $searchConditions), []);
    }

    public function getMemberAnalysisData($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getMemberAnalysisData($id), []);
    }

    public function initUploadFile($fileInfo)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->initUploadFile($fileInfo));
    }

    public function uploadFile(array $initFile, $fileInfo)
    {
        if (!ArrayToolkit::requireds($initFile, ['uploadUrl', 'uploadToken', 'reskey', 'no'])) {
            throw new ServiceException('init upload file error', 1);
        }
        $fileData = $this->tryGetFileData($fileInfo);
        if (empty($fileData) || $fileData === false) {
            throw new ServiceException('file can not read', 1);
        }
        $params = [
            'uploadUrl' => 'https:'.$initFile['uploadUrl'],
            'no' => $initFile['no'],
            'token' => $initFile['uploadToken'],
            'key' => $initFile['reskey'],
            'file' => $fileData,
            'fileName' => substr(md5($initFile['reskey']), 8, 16)
        ];

        return $this->getOpenLivePlatformSkd()->uploadFile($params);
    }

    public function generateOssFile($esFilePath, $keyPrefix, $extno, $defaultFileKey = 'avatar.png')
    {
        if (empty($esFilePath)) {
            $filePath = $this->getWebExtension()->getFpath($esFilePath, $defaultFileKey);
        } else {
            $filePath = $this->getWebExtension()->getFilePath($esFilePath);
        }
        $params = [
            'name' => md5($esFilePath),
            'reskey' => $keyPrefix.md5($filePath).$this->getEsFileExt($filePath),
            'extno' => $extno
        ];
        $initFileInfo = $this->initUploadFile($params);
        $upload = $this->uploadFile($initFileInfo, $this->generateLocalPath($filePath));

        return $initFileInfo['domain'].'/'.$upload['key'];
    }

    public function checkLiveRoomDetailAccess($id)
    {
        $liveRoom = $this->getLive($id);
        if ('paid' !== $liveRoom['charge_status'] || 'finished' !== $liveRoom['status']) {
            throw $this->createAccessDeniedException('直播状态异常，无法查看');
        }

        return $liveRoom;
    }

    public function searchEnrolledRoomStudent($id, array $conditions)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->searchEnrolledRoomStudent($id, $conditions), [], true);
    }

    public function searchSmsReachedRoomStudent($id, array $conditions)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->searchSmsReachedRoomStudent($id, $conditions), [], true);
    }

    public function saveLiveWeChatShareSetting($id, array $shareData)
    {
        if (!ArrayToolkit::requireds($shareData, ['shareTitle', 'shareContent', 'wechatShareImage'], true)) {
            throw new \InvalidArgumentException('argument missing');
        }
        $shareFields = [
            'share_title' => $shareData['shareTitle'],
            'share_content' => $shareData['shareContent'],
            'share_image' => $shareData['wechatShareImage']
        ];

        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->saveLiveWeChatShare($id, $shareFields), ['success' => false], true);
    }

    public function getWeChatShareSettingByLiveId($id)
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getWeChatShareByLiveId($id));
    }

    private function tryGetFileData($filePath)
    {
        if (strpos($filePath, 'http') === 0) {
            $fileData = file_get_contents($filePath);
        } else {
            $file = fopen($filePath, 'rb');
            if ($file === false) {
                throw new ServiceException('file can not open', 1);
            }
            $stat = fstat($file);
            $size = $stat['size'];
            $fileData = fread($file, $size);
            fclose($file);
        }

        return $fileData;
    }

    private function generateCreateFields($liveData)
    {
        if (!ArrayToolkit::requireds($liveData, ['title', 'startDate', 'endDate', 'enrollSms', 'enrollWechat', 'enrollNoticeTime', 'realSpeaker'], true)) {
            throw new \InvalidArgumentException('argument missing');
        }
        $user = $this->getUserService()->getUser($liveData['realSpeaker']);
        $fileOssUrl = $this->generateOssFile($user['largeAvatar'], 'open-live/speaker-avatar/', $user['id']);
        $liveData['liveAbout'] = $this->summaryImgReplace($liveData['liveAbout']);

        return [
            'title' => $liveData['title'],
            'room_cover' => $liveData['openLiveCover'],
            'start_time' => strtotime($liveData['startDate']),
            'end_time' => strtotime($liveData['endDate']),
            'speaker' => $liveData['realSpeaker'],
            'speaker_name' => $user['nickname'],
            'speaker_mobile' => $user['verifiedMobile'],
            'speaker_avatar' => $fileOssUrl,
            'enroll_sms' => $liveData['enrollSms'],
            'enroll_wechat' => $liveData['enrollWechat'],
            'enroll_notice_time' => strtotime($liveData['startDate']) - $liveData['enrollNoticeTime'] * 60,
            'summary' => empty($liveData['liveAbout']) ? '' : $liveData['liveAbout']
        ];
    }

    private function generateEditFields($liveData)
    {
        if (!isset($liveData['startDate'])) {
            if (!ArrayToolkit::requireds($liveData, ['liveRoomId', 'endDate'], true)) {
                throw new \InvalidArgumentException('argument missing');
            }

            return [
                'room_id' => $liveData['liveRoomId'],
                'end_time' => strtotime($liveData['endDate']),
            ];
        }

        if (!ArrayToolkit::requireds($liveData, ['liveRoomId', 'startDate', 'title', 'endDate', 'enrollSms', 'enrollWechat', 'enrollNoticeTime', 'realSpeaker'], true)) {
            throw new \InvalidArgumentException('argument missing');
        }
        $user = $this->getUserService()->getUser($liveData['realSpeaker']);
        $fileOssUrl = $this->generateOssFile($user['largeAvatar'], 'open-live/speaker-avatar/', $user['id']);
        $liveData['liveAbout'] = $this->summaryImgReplace($liveData['liveAbout']);

        return [
            'room_id' => $liveData['liveRoomId'],
            'title' => $liveData['title'],
            'room_cover' => $liveData['openLiveCover'],
            'start_time' => empty($liveData['startDate']) ? '' : strtotime($liveData['startDate']),
            'end_time' => strtotime($liveData['endDate']),
            'speaker' => $liveData['realSpeaker'],
            'speaker_name' => $user['nickname'],
            'speaker_mobile' => $user['verifiedMobile'],
            'speaker_avatar' => $fileOssUrl,
            'enroll_sms' => $liveData['enrollSms'],
            'enroll_wechat' => $liveData['enrollWechat'],
            'enroll_notice_time' => strtotime($liveData['startDate']) - $liveData['enrollNoticeTime'] * 60,
            'summary' => empty($liveData['liveAbout']) ? '' : $liveData['liveAbout']
        ];
    }

    private function summaryImgReplace($summary)
    {
        if (empty($summary)) {
            return '';
        }
        preg_match_all("/<img.*?src=\"(\/files\/.*?)\"/i", $summary, $arr);
        foreach ($arr[1] as $links) {
            $fileUri = str_replace('/files/', 'public://', $links);
            $fileOssUrl = $this->generateOssFile($fileUri, 'open-live/room-summary/', time().StringToolkit::createRandomString(6));
            $summary = str_replace($links, $fileOssUrl, $summary);
        }

        return $summary;
    }

    private function generateConditions($conditions)
    {
        $searchConditions = [];
        if (!empty($conditions['keyword']) && 'title' === $conditions['keywordType']) {
            $searchConditions['title'] = $conditions['keyword'];
        }
        if (!empty($conditions['keyword']) && 'speaker' === $conditions['keywordType']) {
            $speakers = $this->getUserService()->searchUsers(['nickname' => $conditions['keyword'], 'roles' => 'ROLE_TEACHER'], [], 0, PHP_INT_MAX);
            if (empty($speakers)) {
                return [];
            }
            $searchConditions['speakers'] = ArrayToolkit::column($speakers, 'id');
        }
        if (!empty($conditions['speaker'])) {
            $searchConditions['speaker'] = (int) $conditions['speaker'];
        }

        if (!empty($conditions['planStartDateST'])) {
            $searchConditions['start_time_GT'] = strtotime($conditions['planStartDateST']);
        }
        if (!empty($conditions['planStartDateED'])) {
            $searchConditions['start_time_LT'] = strtotime($conditions['planStartDateED']);
        }
        if (!empty($conditions['planEndDateST'])) {
            $searchConditions['end_time_GT'] = strtotime($conditions['planEndDateST']);
        }
        if (!empty($conditions['planEndDateED'])) {
            $searchConditions['end_time_LT'] = strtotime($conditions['planEndDateED']);
        }
        if (!empty($conditions['realStartDateST'])) {
            $searchConditions['actual_start_time_GT'] = strtotime($conditions['realStartDateST']);
        }
        if (!empty($conditions['realStartDateED'])) {
            $searchConditions['actual_start_time_LT'] = strtotime($conditions['realStartDateED']);
        }
        if (!empty($conditions['realEndDateST'])) {
            $searchConditions['actual_end_time_GT'] = strtotime($conditions['realEndDateST']);
        }
        if (!empty($conditions['realEndDateED'])) {
            $searchConditions['actual_end_time_LT'] = strtotime($conditions['realEndDateED']);
        }

        if (!empty($conditions['liveStatus'])) {
            $searchConditions['status'] = $conditions['liveStatus'];
        }
        if (!empty($conditions['settlementStatus'])) {
            $searchConditions['charge_status'] = $conditions['settlementStatus'];
        }
        if (!empty($conditions['publishStatus'])) {
            $searchConditions['is_published'] = ('published' === $conditions['publishStatus'] ? 1 : 0);
        }
        if (isset($conditions['offset']) && isset($conditions['limit'])) {
            $searchConditions['offset'] = (int) $conditions['offset'];
            $searchConditions['limit'] = (int) $conditions['limit'];
        }
        if (!empty($conditions['sorts']) && is_array($conditions['sorts'])) {
            $searchConditions['sorts'] = $conditions['sorts'];
        }

        return $searchConditions;
    }

    protected function setFlashMessage($level, $message)
    {
        global $kernel;
        $flashBag = $kernel->getContainer()->get('session')->getFlashBag();
        if (!empty($flashBag->keys())) {
            return [];
        }

        $kernel->getContainer()->get('session')->getFlashBag()->add($level, $message);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return PlatformSdk
     */
    protected function getOpenLivePlatformSkd()
    {
        return $this->biz->offsetGet('open_live.plugin.open_live_platform');
    }

    private function getWebExtension()
    {
        global $kernel;

        return $kernel->getContainer()->get('web.twig.extension');
    }

    private function getEsFileExt($filePath)
    {
        $filePathArr = explode('?', $filePath);
        $fileArr = explode('.', $filePathArr[0]);
        if (!empty(end($fileArr))) {
            return '.'.end($fileArr);
        }

        return '';
    }

    private function generateLocalPath($filePath)
    {
        if (strpos($filePath, 'http') === 0) {
            return $filePath;
        } elseif (strpos($filePath, '//') === 0) {
            return 'https:'.$filePath;
        }
        $filePathArr = explode('?', $filePath);
        global $kernel;

        return $kernel->getContainer()->getParameter('topxia.upload.public_directory').'/..'.$filePathArr[0];
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('open_live.plugin.logger');
    }
}
