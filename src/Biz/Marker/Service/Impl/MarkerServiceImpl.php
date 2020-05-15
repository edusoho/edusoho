<?php

namespace Biz\Marker\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Marker\MarkerException;
use Biz\Marker\Service\MarkerService;
use Biz\System\SettingException;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

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
            return [];
        }

        $markerIds = ArrayToolkit::column($markers, 'id');

        $questionMarkers = $this->findQuestionMarkersWithItem($markerIds);

        $questionMarkerGroups = ArrayToolkit::group($questionMarkers, 'markerId');
        foreach ($markers as $index => $marker) {
            if (!empty($questionMarkerGroups[$marker['id']])) {
                $markers[$index]['questionMarkers'] = $questionMarkerGroups[$marker['id']];
            } else {
                unset($markers[$index]);
            }
        }

        return array_values($markers);
    }

    protected function findQuestionMarkersWithItem($markerIds)
    {
        $questionMarkers = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds($markerIds);
        $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($questionMarkers, 'questionId'), true);
        foreach ($questionMarkers as $key => &$questionMarker) {
            if (!empty($items[$questionMarker['questionId']]['questions'])) {
                $questionMarker['item'] = $items[$questionMarker['questionId']];
                $questionMarker['question'] = current($items[$questionMarker['questionId']]['questions']);
            } else {
                unset($questionMarkers[$key]);
            }
        }

        return $questionMarkers;
    }

    public function searchMarkers($conditions, $orderBy, $start, $limit)
    {
        return $this->getMarkerDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function updateMarker($id, $fields)
    {
        $marker = $this->getMarker($id);

        if (empty($marker)) {
            $this->createNewException(MarkerException::NOTFOUND_MARKER());
        }
        if (empty($fields['second'])) {
            $this->createNewException(MarkerException::FIELD_SECOND_REQUIRED());
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
            $this->createNewException(MarkerException::FIELD_SECOND_REQUIRED());
        }

        $marker = [
            'mediaId' => $media['id'],
            'second' => $fields['second'],
        ];
        $marker = $this->getMarkerDao()->create($marker);
        $question = $this->getQuestionMarkerService()->addQuestionMarker($fields['questionId'], $marker['id'], 1);

        return $question;
    }

    public function deleteMarker($id)
    {
        $marker = $this->getMarker($id);

        if (empty($marker)) {
            $this->createNewException(MarkerException::NOTFOUND_MARKER());
        }

        $this->getMarkerDao()->delete($id);

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
            $this->createNewException(UserException::UN_LOGIN());
        }

        if ($user['id'] != $lessonUserId) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $uploadMode = $this->getSettingService()->get('storage');

        if ('local' == $uploadMode['upload_mode']) {
            //TODO 翻译？！！
            $this->createNewException(SettingException::CLOUD_VIDEO_DISABLE());
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

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }
}
