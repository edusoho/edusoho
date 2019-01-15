<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Exception\ErrorCode;

class MarketingActivityUrl extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request, $activityId)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, array('domainUri', 'itemUri', 'source'))) {
            throw new BadRequestHttpException('params missed', null, ErrorCode::INVALID_ARGUMENT);
        }
        $user = $this->getCurrentUser();
        $client = MarketingAPIFactory::create('/h5');
        try {
            $activityUrl = $client->post(
                '/activity_by_mobile',
                array(
                    'activityId' => $activityId,
                    'mobile' => empty($user['verifiedMobile']) ? '' : $user['verifiedMobile'],
                    'isLogin' => 0 == $user['id'] ? 0 : 1,
                    'domainUri' => $params['domainUri'],
                    'itemUri' => $params['itemUri'],
                    'source' => $params['source'],
                )
            );
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), null, ErrorCode::BAD_REQUEST);
        }

        return $activityUrl;
    }
}
