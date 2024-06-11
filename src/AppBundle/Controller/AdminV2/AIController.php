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

    public function applyAction(Request $request)
    {
        $user = $this->getUserService()->getUser($this->getUser()->getId());
        $userprofile = $this->getUserService()->getUserProfile($user['id']);

        return $this->render('admin-v2/ai/apply-modal.html.twig', [
            'name' => $userprofile['truename'],
            'mobile' => $user['verifiedMobile'] ?: $userprofile['mobile'],
            'feature' => $request->query->get('feature'),
        ]);
    }

    public function likeFeatureAction(Request $request)
    {
        $user = $this->getUserService()->getUser($this->getUser()->getId());
        $userprofile = $this->getUserService()->getUserProfile($user['id']);
        $fields = $request->request->all();
        $body = [
            'feature' => $fields['feature'],
            'nickname' => $user['nickname'],
            'bindMobile' => $user['verifiedMobile'] ?: $userprofile['mobile'],
            'email' => $user['email'],
        ];
        if (isset($fields['name'])) {
            $body['name'] = $fields['name'];
        }
        if (isset($fields['mobile'])) {
            $body['mobile'] = $fields['mobile'];
        }
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
