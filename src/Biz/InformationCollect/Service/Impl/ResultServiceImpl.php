<?php

namespace Biz\InformationCollect\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\InformationCollect\Dao\ItemDao;
use Biz\InformationCollect\Dao\ResultDao;
use Biz\InformationCollect\Dao\ResultItemDao;
use Biz\InformationCollect\InformationCollectException;
use Biz\InformationCollect\Service\EventService;
use Biz\InformationCollect\Service\ResultService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class ResultServiceImpl extends BaseService implements ResultService
{
    public function countGroupByEventId($eventIds)
    {
        $counts = $this->getResultDao()->countGroupByEventId($eventIds);

        return ArrayToolkit::index($counts, 'eventId');
    }

    public function isSubmited($userId, $eventId)
    {
        return !empty($this->getResultDao()->getByUserIdAndEventId($userId, $eventId));
    }

    public function getResultByUserIdAndEventId($userId, $eventId)
    {
        $result = $this->getResultDao()->getByUserIdAndEventId($userId, $eventId);

        if ($result) {
            $result['items'] = $this->findResultItemsByResultId($result['id']);
        }

        return $result;
    }

    public function findResultsByUserIdsAndEventId($userIds, $eventId)
    {
        $results = $this->getResultDao()->findByUserIdsAndEventId($userIds, $eventId);

        return $results;
    }

    public function submitForm($userId, $eventId, $form)
    {
        $this->validateSubmitForm($userId, $eventId, $form);

        $this->beginTransaction();
        try {
            $event = $this->getInformationCollectEventService()->get($eventId);
            $items = ArrayToolkit::index($this->getInformationCollectEventService()->findItemsByEventId($eventId), 'code');
            $form = ArrayToolkit::parts($form, ArrayToolkit::column($items, 'code'));

            $result = $this->getResultByUserIdAndEventId($userId, $eventId);
            if (empty($result)) {
                $result = $this->getResultDao()->create([
                    'formTitle' => $event['formTitle'],
                    'userId' => $userId,
                    'eventId' => $eventId,
                ]);
            }

            $resultItems = empty($result['items']) ? [] : ArrayToolkit::index($result['items'], 'code');
            $updateResultItems = $insertResultItems = [];
            foreach ($form as $code => $value) {
                $value = is_array($value) ? json_encode($value) : $value;
                if (isset($resultItems[$code])) {
                    $updateResultItems[] = [
                        'id' => $resultItems[$code]['id'],
                        'value' => is_array($value) ? json_encode($value) : $value,
                    ];
                } else {
                    $insertResultItems[] = [
                        'eventId' => $eventId,
                        'resultId' => $result['id'],
                        'code' => $code,
                        'labelName' => $items[$code]['labelName'],
                        'value' => $value,
                    ];
                }
            }

            if (!empty($insertResultItems)) {
                $this->getResultItemDao()->batchCreate($insertResultItems);
            }

            if (!empty($updateResultItems)) {
                $this->getResultItemDao()->batchUpdate(ArrayToolkit::column($updateResultItems, 'id'), $updateResultItems);
            }

            $result['items'] = $this->findResultItemsByResultId($result['id']);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    protected function validateSubmitForm($userId, $eventId, $form)
    {
        $event = $this->getInformationCollectEventService()->get($eventId);
        if (empty($event)) {
            $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        if ('close' == $event['status']) {
            $this->createNewException(InformationCollectException::COLLECTION_IS_CLOSE());
        }

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $itemsGroup = ArrayToolkit::group($this->getInformationCollectEventService()->findItemsByEventId($eventId), 'required');
        if (!empty($itemsGroup['1'])) {
            $requiredItems = ArrayToolkit::column($itemsGroup['1'], 'code');
            if (!ArrayToolkit::requireds($form, $requiredItems, true)) {
                $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
            }
        }
    }

    public function findResultItemsByResultId($resultId)
    {
        $resultItems = $this->getResultItemDao()->findByResultId($resultId);

        return $this->filterResultItems($resultItems);
    }

    protected function filterResultItems($resultItems)
    {
        foreach ($resultItems as &$resultItem) {
            'province_city_area' == $resultItem['code'] && $resultItem['value'] = json_decode($resultItem['value'], true);
        }

        return $resultItems;
    }

    /**
     * @return EventService
     */
    protected function getInformationCollectEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createDao('User:UserService');
    }

    public function searchCollectedData($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getResultDao()->search($conditions, $orderBy, $start, $limit);
    }

    private function _prepareConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        }
        );

        $keywordType = '';
        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            $keywordType = $conditions['keywordType'];
        }

        if (empty($conditions['eventId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!empty($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        if (!empty($conditions['nickname'])) {
            $users = $this->getUserService()->searchUsers(
                ['nickname' => $conditions['nickname']],
                [],
                0,
                PHP_INT_MAX
            );

            $conditions['userIds'] = empty($users) ? [-1] : ArrayToolkit::column($users, 'id');
        }

        // 查询 mobile 来自个人资料 (个人指信息填写者)
        if (!empty($conditions['mobile'])) {
            $userProfiles = $this->getUserService()->searchUserProfiles(
                ['tel' => $conditions['mobile']],
                [],
                0,
                PHP_INT_MAX
            );

            $userIds = ArrayToolkit::column($userProfiles, 'id');
            // userIds 转换为采集事件的 resultIds
            $results = $this->findResultsByUserIdsAndEventId($userIds, $conditions['eventId']);
            $conditions['ids'] = empty($results) ? [-1] : ArrayToolkit::column($results, 'id');
        }

        if (in_array($keywordType, ['mobile', 'name', 'idcard']) && !empty($conditions['keyword'])) {
            $itemsConditions = $this->_prepareItemsConditions($conditions);
            $collectedDataItems = $this->getResultItemDao()->search($itemsConditions, [], 0, PHP_INT_MAX);

            $eventItemResultIds = empty($collectedDataItems) ? [-1] : ArrayToolkit::column($collectedDataItems, 'resultId');
            // 合并 mobile 查出下来的 resultIds
            $conditions['ids'] = empty($conditions['ids']) ? $eventItemResultIds : array_merge($conditions['ids'], $eventItemResultIds);
        }

        return $conditions;
    }

    // todo
    private function _prepareItemsConditions($conditions)
    {
        $itemsConditions = [];
        $itemsConditions['eventId'] = $conditions['eventId'];
        $itemsConditions['code'] = $conditions['keywordType'];
        $itemsConditions['value'] = $conditions['keyword'];

        // 手机号码编码转换 (信息采集可选字段)
        if ('mobile' == $itemsConditions['code']) {
            $itemsConditions['code'] = 'phone';
        }

        // 姓名模糊搜索,手机号码和身份证号精确查找 (信息采集可选字段)
        if ('name' == $itemsConditions['code']) {
            $itemsConditions['likeValue'] = $itemsConditions['value'];
            unset($itemsConditions['value']);
        }

        return $itemsConditions;
    }

    public function findResultDataByResultIds($resultIds)
    {
        $resultData = ArrayToolkit::group(
            $this->filterResultItems($this->getResultItemDao()->findResultDataByResultIds($resultIds)),
            'resultId'
        );

        foreach ($resultData as $resultId => &$datum) {
            $datum = ArrayToolkit::index($datum, 'code');
        }

        return $resultData;
    }

    public function count($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getResultDao()->count($conditions);
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->createDao('InformationCollect:ItemDao');
    }

    /**
     * @return ResultDao
     */
    protected function getResultDao()
    {
        return $this->createDao('InformationCollect:ResultDao');
    }

    /**
     * @return ResultItemDao
     */
    protected function getResultItemDao()
    {
        return $this->createDao('InformationCollect:ResultItemDao');
    }
}
