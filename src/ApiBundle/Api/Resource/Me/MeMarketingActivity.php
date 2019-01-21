<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\Service\UserMarketingActivityService;

class MeMarketingActivity extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!empty($user['verifiedMobile'])) {
            try {
                $this->getUserMarketingActivityService()->syncByMobile($user['verifiedMobile']);
            } catch (\Exception $e) {
            }
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = array(
            'userId' => $user['id'],
        );
        $activities = $this->getUserMarketingActivityService()->searchActivities(
            $conditions,
            array('joinedTime' => 'DESC'),
            $offset,
            $limit
        );
        $total = $this->getUserMarketingActivityService()->searchActivityCount($conditions);

        return $this->makePagingObject($activities, $total, $offset, $limit);
    }

    /**
     * @return UserMarketingActivityService
     */
    protected function getUserMarketingActivityService()
    {
        return $this->service('Marketing:UserMarketingActivityService');
    }
}
