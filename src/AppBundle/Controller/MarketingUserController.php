<?php

namespace AppBundle\Controller;

use AppBundle\Common\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class MarketingUserController extends BaseController
{
    public function loginAction(Request $request)
    {
        $ticket = $request->query->get('ticket');
        $token = $this->getTokenService()->verifyToken('marketing_login', $ticket);
        if (false !== $token) {
            $user = $this->getUserService()->getUser($token['userId']);
            if (empty($user)) {
                return $this->createResourceNotFoundException('user', $token['userId']);
            }
            $this->authenticateUser($user);
            if (!in_array($token['data']['targetType'], array('classroom', 'course', 'coupon'))) {
                throw new InvalidArgumentException('targetType is invalid');
            }
            if (in_array($token['data']['targetType'], array('classroom', 'course'))) {
                $url = $this->generateTargetUrl($token['data']['targetType'], $token['data']['targetId']);
            } else {
                $url = $this->generateTargetUrl($token['data']['targetType']);
            }

            $request->getSession()->set('_target_path', $url);

            return $this->redirect($url);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    private function generateTargetUrl($targetType, $targetId = null)
    {
        switch ($targetType) {
            case 'classroom':
            case 'course':
                $url = $this->generateUrl($targetType.'_show', array('id' => $targetId));
                break;
            case 'coupon':
                $url = $this->generateUrl('my_cards');
                break;
            default:
                throw new InvalidArgumentException('targetType is invalid');
                break;
        }

        return $url;
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
