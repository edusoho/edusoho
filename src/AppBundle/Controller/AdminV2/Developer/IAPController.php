<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class IAPController extends BaseController
{
    public function mobileIapProductAction(Request $request)
    {
        $products = $this->getSettingService()->get('mobile_iap_product', array());
        if ('POST' === $request->getMethod()) {
            $fileds = $request->request->all();

            //新增校验
            if (empty($fileds['productId']) || empty($fileds['title']) || empty($fileds['price']) || !is_numeric($fileds['price'])) {
                $this->setFlashMessage('danger', 'admin.setting.mobile.lap.incorrect_input');

                return $this->redirect($this->generateUrl('admin_v2_setting_mobile_iap_product'));
            }

            //新增
            $products[$fileds['productId']] = array(
                'productId' => $fileds['productId'],
                'title' => $fileds['title'],
                'price' => $fileds['price'],
            );
            $this->getSettingService()->set('mobile_iap_product', $products);

            $this->getLogService()->info('system', 'update_settings', '更新IOS内购产品设置', $products);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('admin_v2_setting_mobile_iap_product'));
        }

        return $this->render('admin-v2/developer/iap/mobile-iap-product.html.twig', array(
            'products' => $products,
        ));
    }

    public function mobileIapProductDeleteAction(Request $request, $productId)
    {
        $products = $this->getSettingService()->get('mobile_iap_product', array());

        if (array_key_exists($productId, $products)) {
            unset($products[$productId]);
        }

        $this->getSettingService()->set('mobile_iap_product', $products);

        return $this->createJsonResponse(true);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
