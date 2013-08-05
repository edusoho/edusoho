<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\TrackService;

class TrackServiceImpl extends BaseService implements TrackService
{

    protected function prepareTrackConditions($conditions)
    {
        $prepared = array();
        if (!empty($conditions['timeRange'])) {
            $prepared['createdTime:between'] = $conditions['timeRange'];
        }
        if (!empty($conditions['action'])) {
            $prepared['action'] = $conditions['action'];
        }

        $fieldName = function ($name, $value) {
            if (substr($value, -1) == '%') {
                $name .= ':like';
            }
            return $name;
        };

        if (!empty($conditions['target'])) {
            $prepared[$fieldName('target', $conditions['target'])] = $conditions['target'];
        }
        if (!empty($conditions['holder'])) {
            $prepared[$fieldName('holder', $conditions['holder'])] = $conditions['holder'];
        }
        if (!empty($conditions['parent'])) {
            $prepared[$fieldName('parent', $conditions['parent'])] = $conditions['parent'];
        }

        return $prepared;
    }

    public function track($action, $target = null, $note = null)
    {
        $user = $this->getCurrentUser();
        $track = array();
        $track['userId'] = empty($user) ? 0 : $user['id'];
        $track['action'] = $action;
        if (is_string($target)) {
            $target = array('target' => $target);
        }
        if (!is_array($target) || empty($target['target'])) {
            throw $this->createServiceException('track target is error.');
        }
        $track['target'] = $target['target'];
        $track['parent'] = empty($target['parent']) ? null : $target['parent'];
        $track['holder'] = empty($target['holder']) ? null : $target['holder'];
        $track['ip'] =  $this->getRequest()->getClientIp();
        $track['note'] = empty($note) ? '' : $note;
        $track['createdTime'] = time();

        return $this->getTrackDao()->addTrack($track);
    }

    private function getTrackDao()
    {
        return $this->createDao('User.TrackDao');
    }

    private function geteUserService()
    {
        return $this->createService('User.UserService');
    }

}