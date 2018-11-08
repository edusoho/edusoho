<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;

class MarketingActivity extends AbstractResource
{
    /**
     * @param ApiRequest $request
     *
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request)
    {
        $conditions = $this->fillParams($request);
        $user = $this->getCurrentUser();
        $client = MarketingAPIFactory::create();

        return $client->get(
            '/activities',
            $conditions,
            array('MERCHANT-USER-ID: '.$user['id'])
        );
    }

    public function fillParams($request)
    {
        $conditions = $request->query->all();
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions['page'] = ceil(($offset + 1) / $limit);
        $conditions['limit'] = $limit;

        return $conditions;
    }
}
