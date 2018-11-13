<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeMarketingActivity extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\MarketingActivity\MarketingActivityFilter", mode="public"))
     */
    public function search(ApiRequest $request)
    {
        $conditions = $this->fillParams($request);
        $systemUser = $this->getUserService()->getUserByType('system');
        $this->getMarketingPlatformService()->simpleLogin($systemUser['id']);
        $client = MarketingAPIFactory::create();

        return $client->get(
            '/user_activities',
            $conditions,
            array('MERCHANT-USER-ID: '.$systemUser['id'])
        );
    }

    public function fillParams($request)
    {
        $user = $this->getCurrentUser();
        $conditions = $request->query->all();
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions['page'] = ceil(($offset + 1) / $limit);
        $conditions['limit'] = $limit;
        $conditions['mobile'] = 12312345678;//$user['verifiedMobile'];

        return $conditions;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    protected function getMarketingPlatformService()
    {
        return $this->service('Marketing:MarketingPlatformService');
    }
}
