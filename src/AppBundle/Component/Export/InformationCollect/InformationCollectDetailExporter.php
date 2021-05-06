<?php

namespace AppBundle\Component\Export\InformationCollect;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\InformationCollect\Service\EventService;
use Biz\InformationCollect\Service\ResultService;
use Biz\User\Service\UserService;

class InformationCollectDetailExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $this->getResultService()->count($this->conditions);
    }

    public function getTitles()
    {
        $fields = [
            'user.fields.username_label',
            'user.fields.profile_mobile_label',
            'admin.information_collect.collected_time',
        ];

        $collectItems = $this->getEventService()->findItemsByEventId($this->parameter['eventId']);
        $itemLabelNames = ArrayToolkit::column($collectItems, 'labelName');

        return array_merge($fields, $itemLabelNames);
    }

    public function buildCondition($conditions)
    {
        return $conditions;
    }

    public function getContent($start, $limit)
    {
        $results = $this->getResultService()->searchCollectedData(
            $this->conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($results, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $resultItems = $this->getResultService()->findResultDataByResultIds(ArrayToolkit::column($results, 'id'));

        $collectItems = $this->getEventService()->findItemsByEventId($this->parameter['eventId']);
        $itemLabelCodes = ArrayToolkit::column($collectItems, 'code');

        $exportData = [];
        foreach ($resultItems as $key => $resultItem) {
            foreach ($itemLabelCodes as $itemLabelCode) {
                if (!empty($resultItem[$itemLabelCode])) {
                    $exportData[$key][$itemLabelCode] = $resultItem[$itemLabelCode];
                } else {
                    $exportData[$key][$itemLabelCode] = ['value' => ''];
                }
            }
            if (isset($exportData[$key]['province_city_area']) && !empty($exportData[$key]['province_city_area']['value'])) {
                $exportData[$key]['province_city_area']['value'] = implode('', $exportData[$key]['province_city_area']['value']);
            }
        }

        $contents = [];
        foreach ($results as $result) {
            $data = [];
            $data[] = $users[$result['userId']]['nickname'];
            $data[] = $userProfiles[$result['userId']]['mobile'];
            $data[] = date('Y-n-d H:i:s', $result['createdTime']);

            if (empty($exportData[$result['id']])) {
                $contents[] = $data;
                continue;
            }

            $resultItemValues = ArrayToolkit::column($exportData[$result['id']], 'value');
            foreach ($resultItemValues as $value) {
                $data[] = $value;
            }
            $contents[] = $data;
        }

        return $contents;
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['eventId'] = $conditions['eventId'];

        return $parameter;
    }

    /**
     * @return EventService
     */
    protected function getEventService()
    {
        return $this->getBiz()->service('InformationCollect:EventService');
    }

    /**
     * @return ResultService
     */
    public function getResultService()
    {
        return $this->getBiz()->service('InformationCollect:ResultService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return parent::getUserService();
    }
}
