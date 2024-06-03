<?php

namespace AppBundle\Controller\AdminV2;

use Biz\CloudData\Service\CloudDataService;
use Symfony\Component\HttpFoundation\Request;

class AIController extends BaseController
{
    public function surveyAction()
    {
        return $this->render('admin-v2/ai/survey-modal.html.twig');
    }

    public function likeAction(Request $request)
    {
        $user = $this->getUserService()->getUser($this->getUser()->getId());
        $userprofile = $this->getUserService()->getUserProfile($user['id']);
        $body = [
            'feature' => $request->request->get('feature'),
            'nickname' => $user['nickname'],
            'mobile' => $user['verifiedMobile'] ?: $userprofile['mobile'],
            'email' => $user['email'],
        ];
        $this->getCloudDataService()->push('ai.like_feature', $body, time(), 'important');

        return $this->createJsonResponse(['ok' => true]);
    }

    /**
     * @return CloudDataService
     */
    private function getCloudDataService()
    {
        return $this->createService('CloudData:CloudDataService');
    }
}
