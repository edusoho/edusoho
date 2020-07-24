<?php

namespace AppBundle\Controller\Goods;

use AppBundle\Controller\BaseController;
use Biz\Common\CommonException;
use Biz\User\Service\UserFieldService;
use Symfony\Component\HttpFoundation\Request;

class GoodsController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        return $this->render(
            'goods/show.html.twig',
            []
        );
    }

    public function minScreenShowAction(Request $request, $id)
    {
        return $this->render('goods/min-screen-show.html.twig', []);
    }

    public function buyFLowModalAction(Request $request)
    {
        if (!in_array($request->query->get('template'), ['no-remain', 'payments-disabled', 'avatar-alert', 'fill-user-info'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $params = [];

        if ('fill-user-info' == $request->query->get('template')) {
            $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
            $user = $this->getCurrentUser();
            $userInfo = $this->getUserService()->getUserProfile($user['id']);
            $userInfo['approvalStatus'] = $user['approvalStatus'];

            $params['userFields'] = $userFields;
            $params['user'] = $userInfo;
        }

        return $this->render(
            'buy-flow/'.$request->query->get('template').'-modal.html.twig', $params);
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }
}
