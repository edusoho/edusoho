<?php

namespace Biz\Marker\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Marker\Service\MarkerService;

class MarkerServiceImpl extends BaseService implements MarkerService
{
    public function getMarker($id)
    {
        return $this->getMarkerDao()->get($id);
    }

    public function getMarkersByIds($ids)
    {
        $markers = $this->getMarkerDao()->findByIds($ids);

        return ArrayToolkit::index($markers, 'id');
    }

    public function findMarkersByMediaId($mediaId)
    {
        return $this->getMarkerDao()->findByMediaId($mediaId);
    }

    public function findMarkersMetaByMediaId($mediaId)
    {
        $markers = $this->findMarkersByMediaId($mediaId);

        if (empty($markers)) {
            return array();
        }

        $markerIds = ArrayToolkit::column($markers, 'id');

        $questionMarkers = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds($markerIds);
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
        return $this->getMarkerDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function updateMarker($id, $fields)
    {
        $marker = $this->getMarker($id);

        if (empty($marker)) {
            throw $this->createNotFoundException('Marker Not Found');
        }
        if (empty($fields['second'])) {
            throw $this->createInvalidArgumentException('Field Second Required');
        }

        return $this->getMarkerDao()->update($id, $fields);
    }

    public function addMarker($mediaId, $fields)
    {
        $media = $this->getUploadFileService()->getFile($mediaId);

        if (empty($media)) {
            $media['id'] = 0;
            $this->getLogService()->error('marker', 'mediaId_notExist', '视频文件不存在！');
        }

        if (!isset($fields['second']) || '' == $fields['second']) {
            throw $this->createInvalidArgumentException('Field Second Required');
        }

        $marker = array(
            'mediaId' => $media['id'],
            'second' => $fields['second'],
        );
        $marker = $this->getMarkerDao()->create($marker);
        $question = $this->getQuestionMarkerService()->addQuestionMarker($fields['questionId'], $marker['id'], 1);

        return $question;
    }

    public function deleteMarker($id)
    {
        $marker = $this->getMarker($id);

        if (empty($marker)) {
            throw $this->createNotFoundException('Marker Not Found');
        }

        $this->getMarkerDao()->delete($id);
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

    public function canManageMarker($lessonUserId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        if ($user['id'] != $lessonUserId) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $uploadMode = $this->getSettingService()->get('storage');

        if ('local' == $uploadMode['upload_mode']) {
            //TODO 翻译？！！
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
        return $this->createDao('Marker:MarkerDao');
    }

    protected function getQuestionMarkerService()
    {
        return $this->biz->service('Marker:QuestionMarkerService');
    }

    protected function getQuestionMarkerResultService()
    {
        return $this->biz->service('Marker:QuestionMarkerResultService');
    }

    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
