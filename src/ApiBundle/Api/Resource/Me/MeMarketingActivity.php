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
        $client = MarketingAPIFactory::create();

        return $client->get(
            '/user_activities',
            $conditions,
            array('MERCHANT-USER-ID: 2')
        );
    }

    public function fillParams($request)
    {
        $user = $this->getCurrentUser();
        $conditions = $request->query->all();
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions['page'] = ceil(($offset + 1) / $limit);
        $conditions['limit'] = $limit;
        $conditions['mobile'] = $user['verifiedMobile'];

        return $conditions;
    }
}
