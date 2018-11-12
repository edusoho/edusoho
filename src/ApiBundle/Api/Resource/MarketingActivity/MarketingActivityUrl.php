<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;
use AppBundle\Common\ArrayToolkit;

class MarketingActivityUrl extends AbstractResource
{
    public function add(ApiRequest $request, $activityId)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, array('domainUri', 'itemUri', 'source', 'grouponId'))) {
            throw new BadRequestHttpException('params missed', null, ErrorCode::INVALID_ARGUMENT);
        }
        $user = $this->getCurrentUser();
        $user['verifiedMobile'] = '15068832319';
        if (empty($user['verifiedMobile'])) {
            throw $this->createNotFoundException('mobile not found');
        }
        $client = MarketingAPIFactory::create('/h5');
        try {
            $activityUrl = $client->post(
                '/activity_by_mobile',
                array(
                    'activityId' => $activityId,
                    'mobile' => $user['verifiedMobile'],
                    'domainUri' => $params['domainUri'],
                    'itemUri' => $params['itemUri'],
                    'source' => $params['source'],
                    'grouponId' => $params['grouponId'],
                )
            );
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $activityUrl;
    }
}
