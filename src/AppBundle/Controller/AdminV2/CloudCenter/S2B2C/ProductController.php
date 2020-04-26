<?php

namespace AppBundle\Controller\AdminV2\CloudCenter\S2B2C;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends BaseController
{
    public function pullSupplierProductAction(Request $request, $productType, $productId)
    {
        $product = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierProductDetail($productId);

        return $this->forward($this->getForwardTarget($productType).':deal', ['product' => $product, 'request' => $request]);
    }

    private function getForwardTarget($productType)
    {
        $controllers = ['course_set' => 'AppBundle:AdminV2/CloudCenter/S2B2C/CourseSetProduct'];

        return $controllers[$productType];
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
