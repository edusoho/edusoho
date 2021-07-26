<?php

namespace AppBundle\Controller;

use Biz\System\Service\SettingService;

class AgreementController extends BaseController
{
    public function coursePurchaseAgreementAction()
    {
        $purchaseAgreement = $this->getSettingService()->get('course_purchase_agreement');
        $purchaseAgreement['content'] = empty($purchaseAgreement['content']) ? '' : $purchaseAgreement['content'];

        return $this->render('agreement/course-purchase-agreement.html.twig', [
            'purchaseAgreement' => $purchaseAgreement,
        ]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
