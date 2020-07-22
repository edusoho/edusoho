<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\S2B2CProductException;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\CacheService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourcePurchaseController extends BaseController
{
    public $productController = [
        'courseSet' => 'AppBundle:AdminV2/CloudCenter/S2B2C/CourseSetProduct',
    ];

    /**
     * @param $tab
     *
     * @return Response
     *
     * @throws \Exception
     *                    这里是分课营销的入口，具体产品的渲染放到$productController中
     */
    public function marketAction(Request $request, $tab)
    {
        $controller = $this->getProductController($tab);
        if (empty($controller)) {
            $this->createNewException(S2B2CProductException::INVALID_S2B2C_PRODUCT_TYPE());
        }

        return $this->forward("{$controller}:market", [
            'request' => $request,
        ]);
    }

    /**
     * @param $type
     *
     * @return Response
     *
     * @throws \Exception
     *                    这里是分课分类模块的入口，具体的分类模块实现放到了$productController中
     */
    public function categoriesAction(Request $request, $type)
    {
        $controller = $this->getProductController($type);
        if (empty($controller)) {
            $this->createNewException(S2B2CProductException::INVALID_S2B2C_PRODUCT_TYPE());
        }

        return $this->forward("{$controller}:categories", [
            'request' => $request,
        ]);
    }

    public function productsAction(Request $request, $type)
    {
        $controller = $this->getProductController($type);
        if (empty($controller)) {
            $this->createNewException(S2B2CProductException::INVALID_S2B2C_PRODUCT_TYPE());
        }

        return $this->forward("{$controller}:productList", [
            'request' => $request,
        ]);
    }

    public function productDetailAction(Request $request, $productType, $s2b2cProductId, $courseId)
    {
        $controller = $this->getProductController($productType);
        if (empty($controller)) {
            $this->createNewException(S2B2CProductException::INVALID_S2B2C_PRODUCT_TYPE());
        }

        return $this->forward("{$controller}:productDetail", [
            'request' => $request,
            's2b2cProductId' => $s2b2cProductId,
            'courseId' => $courseId,
        ]);
    }

    public function productsVersionAction(Request $request)
    {
        $products = $this->getS2B2CProductService()->findUpdatedVersionProductList();

        if (!empty($products)) {
            $courseSetIds = array_column($products, 'localResourceId');

            $courseSets = ArrayToolkit::index($this->getCourseSetService()->findCourseSetsByIds($courseSetIds), 'id');

            foreach ($products as &$product) {
                $product['courseSet'] = empty($courseSets[$product['localResourceId']]) ? null : $courseSets[$product['localResourceId']];
            }
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/list.html.twig',
            [
                'request' => $request,
                'productVersionList' => $products,
            ]
        );
    }

    public function productVersionDetailAction(Request $request, $remoteSourceId)
    {
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $product = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteResourceIdAndType($s2b2cConfig['supplierId'], $remoteSourceId, 'course');

        if (empty($product)) {
            throw $this->createNotFoundException('需先更新至本地');
        }

        $productVersions = $this->getS2B2CFacadeService()->getS2B2CService()->getDistributeProductVersions($product['s2b2cProductDetailId']);

        if (empty($productVersions['status'])) {
            throw $this->createNotFoundException('未找到版本信息');
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/detail.html.twig',
            [
                'productVersions' => $productVersions['data'],
            ]
        );
    }

    public function updateProductToLatestVersionAction(Request $request, $productId)
    {
        if ($request->isMethod('POST')) {
            /**
             * mock
             */
            $merchant = $merchant = $this->getS2B2CFacadeService()->getMe();
            if (empty($merchant['status']) || 'active' != $merchant['status'] || 'cooperation' != $merchant['coop_status']) {
                return $this->createJsonResponse(['status' => false, 'error' => '更新失败']);
            }
            try {
                $this->getS2B2CProductService()->updateProductVersion($productId);
            } catch (\Exception $exception) {
                $errorMessage = '';
                if (S2B2CProductException::ADOPT_PRODUCT_FAILED == $exception->getCode()) {
                    $errorMessage = $this->get('translator')->trans($exception->getMessage());
                }

                return $this->createJsonResponse(['status' => false, 'error' => $errorMessage]);
            }

            return $this->createJsonResponse(['status' => true]);
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/update-modal.html.twig',
            [
                'path' => 'admin_v2_content_resource_update_product_version',
                'request' => $request,
                'productId' => $productId,
            ]
        );
    }

    public function productUpdateSettingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $type = $this->getS2B2CProductService()->setProductUpdateType($request->request->get('type'));

            $this->setFlashMessage('success', $this->trans('site.modify.success'));
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/update-product-setting.html.twig',
            [
                'type' => !empty($type) ? $type : $this->getSettingService()->get('productUpdateType', ProductService::UPDATE_TYPE_MANUAL),
            ]
        );
    }

    protected function getProductController($type)
    {
        return $this->productController[$type];
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return CourseProductService
     */
    protected function getS2B2CCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }
}
