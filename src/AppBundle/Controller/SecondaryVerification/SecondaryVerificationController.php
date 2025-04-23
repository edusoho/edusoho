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
        if (0 == $cloudSmsSetting['sms_enabled']) {
            return $this->render('secondary-verification/sms-open-redirect-modal.html.twig');
        }
        if (empty($cloudSmsSetting['sms_secondary_verification'])) {
            $cloudSmsSetting['sms_secondary_verification'] = 'on';
            $this->getSettingService()->set('cloud_sms', $cloudSmsSetting);
        }
        $params = $request->query->all();

        return $this->render(
            'secondary-verification/secondary-verification-modal.html.twig',
            [
                'exportFileName' => $params['exportFileName'],
                'targetFormId' => $params['targetFormId'],
                'mobile' => $this->getMobile($params['exportFileName']),
                'params' => $params,
            ]
        );
    }

    private function getMobile($exportFileName)
    {
        $useCurrentUser = in_array($exportFileName, ['classroomStudent', 'itemBankExercise', 'courseStudent', 'deleteUser', 'course-order'], true);

        if ($useCurrentUser) {
            return $this->getUser()['verifiedMobile'] ?? '';
        }

        $result = CloudAPIFactory::create('leaf')->get('/me');

        return $result['mobile'] ?? '';
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
