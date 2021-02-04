<?php

namespace OpenLivePlugin\Biz\OpenLiveManage\Service;

interface OpenLiveManageService
{
    public function getLive($id);

    public function createLive($liveData);

    public function editLive($liveData);

    public function searchLives($conditions);

    public function publishLive($id);

    public function unpublishLive($id);

    public function closeLive($id);

    public function deleteLive($id);

    public function getLiveTeacherEntryUrl($id, $userInfo);

    public function getLiveShareUrl($id);

    public function searchLiveOnlineNumRecords($id, $conditions = []);

    public function getLiveVisitorReport($id, $conditions = []);

    public function getMemberAnalysisData($id);

    public function initUploadFile($fileInfo);

    /**
     * @param array $initFile
     * @param $fileInfo | 路径或文件内容
     * @return mixed
     */
    public function uploadFile(array $initFile, $fileInfo);

    public function generateOssFile($esFilePath, $keyPrefix, $extno, $defaultFileKey = 'avatar.png');

    public function checkLiveRoomDetailAccess($id);

    public function searchEnrolledRoomStudent($id, array $conditions);

    public function searchSmsReachedRoomStudent($id, array $conditions);

    public function saveLiveWeChatShareSetting($id, array $shareData);

    public function getWeChatShareSettingByLiveId($id);
}
