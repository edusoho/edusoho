<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Biz\Marketing\MarketingAPIFactory;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Exception\ErrorCode;

class MarketingController extends BaseController
{
    public function activityAction(Request $request, $activityId)
    {
        $params = $request->query->all();
        if (!ArrayToolkit::requireds($params, array('userId', 'domainUri', 'source', 'grouponId'))) {
            throw new BadRequestHttpException('params missed', null, ErrorCode::INVALID_ARGUMENT);
        }
        $user = $this->getUserService()->getUser($params['userId']);
        if (empty($user) || empty($user['verifiedMobile'])) {
            throw $this->createNotFoundException('user or mobile not found');
        }
        $client = MarketingAPIFactory::create('/h5');
        $activityUri = $client->post(
            '/activity_by_mobile',
            array(
                'activityId' => $activityId,
                'mobile' => $user['verifiedMobile'],
                'domainUri' => $params['domainUri'],
                'source' => $params['source'],
                'grouponId' => $params['grouponId'],
            )
        );

        return $this->redirect($activityUri['url']);
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
