<?php

namespace Topxia\Service\Marker\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Marker\MarkerService;

class MarkerServiceImpl extends BaseService implements MarkerService
{
    public function getMarker($id)
    {
        return $this->getMarkerDao()->getMarker($id);
    }

    public function getMarkersByIds($ids)
    {
        $markers = $this->getMarkerDao()->getMarkersByIds($ids);
        return ArrayToolkit::index($markers, 'id');
    }

    public function findMarkersByMediaId($mediaId)
    {
        return $this->getMarkerDao()->findMarkersByMediaId($mediaId);
    }

    public function findMarkersMetaByMediaId($mediaId)
    {
        $markers = $this->findMarkersByMediaId($mediaId);

        if (empty($markers)) {
            return array();
        }

        $markerIds = ArrayToolkit::column($markers, 'id');

        $questionMarkers      = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds($markerIds);
        $questionMarkerGroups = ArrayToolkit::group($questionMarkers, 'markerId');

        foreach ($markers as $index => $marker) {
            if (!empty($questionMarkerGroups[$marker['id']])) {
                $markers[$index]['questionMarkers'] = $questionMarkerGroups[$marker['id']];
            }
        }

        return $markers;
    }

    public function searchMarkers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareMarkerConditions($conditions);
        return $this->getMarkerDao()->searchMarkers($conditions, $orderBy, $start, $limit);
    }

    public function updateMarker($id, $fields)
    {
        $marker = $this->getMarker($id);

        if (empty($marker)) {
            throw $this->createServiceException("驻点不存在");
        }

        if (isset($fields['updatedTime']) || $fields['updatedTime'] == "") {
            $fields['updatedTime'] = time();
        }

        if (empty($fields['second'])) {
            throw $this->createServiceException("更新驻点时间不存在");
        }

        return $this->getMarkerDao()->updateMarker($id, $fields);
    }

    public function addMarker($mediaId, $fields)
    {
        $media = $this->getUploadFileService()->getFile($mediaId);

        if (empty($media)) {
            $media['id'] = 0;
            $this->getLogService()->error('marker', 'mediaId_notExist', "视频文件不存在！");
        }

        if (!isset($fields['second']) || $fields['second'] == "") {
            throw $this->createServiceException("请输入弹题时间！");
        }

        $marker = array(
            'mediaId'     => $media['id'],
            'createdTime' => time(),
            'updatedTime' => time(),
            'second'      => $fields['second']
        );
        $marker = $this->getMarkerDao()->addMarker($marker);
        $this->getLogService()->info('marker', 'create', "增加驻点#{$marker['id']}");
        $question = $this->getQuestionMarkerService()->addQuestionMarker($fields['questionId'], $marker['id'], 1);
        return $question;
    }

    public function deleteMarker($id)
    {
        $marker = $this->getMarker($id);

        if (empty($marker)) {
            throw $this->createServiceException("驻点不存在，操作失败。");
        }

        $this->getMarkerDao()->deleteMarker($id);
        $this->getLogService()->info('marker', 'delete', "驻点#{$id}永久删除");

        return true;
    }

    public function isFinishMarker($userId, $markerId)
    {
        $questionMarkers = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($markerId);

        if (empty($questionMarkers)) {
            return true;
        }

        $questionMarkerResults = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($userId, $markerId);

        if (!in_array('none', ArrayToolkit::column($questionMarkerResults, 'status')) && !array_diff(ArrayToolkit::column($questionMarkers, 'id'), ArrayToolkit::column($questionMarkerResults, 'questionMarkerId'))) {
            return true;
        }

        return false;
    }

    protected function prepareMarkerConditions($conditions)
    {
        if (isset($conditions['second']) && $conditions['second'] == "") {
            unset($conditions['second']);
        }

        return $conditions;
    }

    public function canManageMarker($lessonUserId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        if ($user['id'] != $lessonUserId) {
            throw $this->createAccessDeniedException('该视频不属于你，无权操作！');
        }

        $uploadMode = $this->getSettingService()->get('storage');

        if ($uploadMode['upload_mode'] == 'local') {
            throw $this->createAccessDeniedException('请到我的教育云开启云视频！');
        }

        return true;
    }

    public function merge($sourceMarkerId, $targetMarkerId)
    {
        $this->getQuestionMarkerService()->merge($sourceMarkerId, $targetMarkerId);
        $this->deleteMarker($sourceMarkerId);
        return true;
    }

    protected function getMarkerDao()
    {
        return $this->createDao('Marker.MarkerDao');
    }

    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker.QuestionMarkerService');
    }

    protected function getQuestionMarkerResultService()
    {
        return $this->createService('Marker.QuestionMarkerResultService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
