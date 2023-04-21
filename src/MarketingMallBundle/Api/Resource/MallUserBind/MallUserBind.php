<?php

namespace MarketingMallBundle\Api\Resource\MallUserBind;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class MallUserBind extends AbstractResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, ['type', 'fromId', 'toId', 'token'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $userBind = $this->getUserService()->getUserBindByTypeAndUserId($params['type'], $params['toId']);
        if ($userBind) {
            throw UserException::USER_ALREADY_BIND();
        }
        $this->getUserService()->bindUser($params['type'], $params['fromId'], $params['toId'], $params['token']);

        return ['success' => true];
    }

    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function get(ApiRequest $request, $type)
    {
        $fromId = $request->query->get('fromId');
        $toId = $request->query->get('toId');
        if (empty($fromId) && empty($toId)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if ($fromId) {
            return $this->getUserService()->getUserBindByTypeAndFromId($type, $fromId);
        }

        return $this->getUserService()->getUserBindByTypeAndUserId($type, $toId);
    }

    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function remove(ApiRequest $request, $type)
    {
        $fromId = $request->request->get('fromId');
        $userBind = $this->getUserService()->getUserBindByTypeAndFromId($type, $fromId);
        if ($userBind) {
            $this->getUserService()->unBindUserByTypeAndToId($type, $userBind['toId']);
        }

        return ['success' => true];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
