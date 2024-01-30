<?php

namespace ApiBundle\Api\Resource\LearnStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;

class LearnStatistic extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $defaultCondition = [
            'startDate' => '',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
        ];
        $conditions = $request->query->all();
        $conditions = array_merge($defaultCondition, $conditions);

        $userConditions = ['destroyed' => 0, 'isStudent' => true];
        $keyword = trim($conditions['keyword'] ?? '');
        if ($keyword) {
            $userConditions['nickname'] = $keyword;
        }

        if (isset($conditions['keywordType']) && 'mobile' == $conditions['keywordType'] && $keyword) {
            unset($userConditions['nickname']);
            $userConditions['verifiedMobile'] = $keyword;
        }
        $total = $this->getUserService()->countUsers($userConditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        file_put_contents('/tmp/jc123', json_encode($userConditions), 8);
        $users = $this->getUserService()->searchUsers(
            $userConditions,
            ['id' => 'DESC'],
            $offset,
            $limit
        );
        $conditions = array_merge($conditions, ['userIds' => ArrayToolkit::column($users, 'id')]);

        return $this->makePagingObject($this->getLearnStatisticsService()->statisticsDataSearch($conditions), $total, $offset, $limit);
    }

    /**
     * @return LearnStatisticsService
     */
    protected function getLearnStatisticsService()
    {
        return $this->service('UserLearnStatistics:LearnStatisticsService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
