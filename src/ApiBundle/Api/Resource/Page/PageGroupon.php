<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;

class PageGroupon extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal,$activityId)
    {
        if (!in_array($portal, ['h5', 'miniprogram', 'apps'])) {
            throw PageException::ERROR_PORTAL();
        }

        $client = MarketingAPIFactory::create('/h5');
        $num = $client->get("/activities/${activityId}/groupons");
        $time = $client->get("/activities/${activityId}/grouponNum");

        return ['grouponNum' => $num['grouponNum']??0, 'groupTime' => $time['grouponNum']??0];

    }
}
