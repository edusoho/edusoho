<?php

namespace AppBundle\Controller\SecondaryVerification;

use AppBundle\Controller\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class SecondaryVerificationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');
        if (empty($cloudSmsSetting['sms_secondary_verification'])) {
            $cloudSmsSetting['sms_secondary_verification'] = 'on';
            $this->getSettingService()->set('cloud_sms', $cloudSmsSetting);
        }
        $params = $request->query->all();
        $operateUser = $this->getUser();
        $result = CloudAPIFactory::create('leaf')->get('/me');

        return $this->render(
            'secondary-verification/secondary-verification-modal.html.twig',
            [
                'exportFileName' => $params['exportFileName'],
                'targetFormId' => $params['targetFormId'],
                'mobile' => $result['mobile'],
            ]
        );
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
