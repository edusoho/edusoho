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

    public function productDetailAction(Request $request, $id, $tab = 'summary')
    {
        //当前静默是课程，不过真是情况是会有多种模式的课程
        $courseSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->getSupplierCourseSetProductDetail($id);

        if (empty($courseSet) || !empty($courseSet['error'])) {
            throw $this->createNotFoundException('原课程未找到或出错了');
        }

        $chosenCourseSet = $this->getCourseSetService()->searchCourseSets(['originProductId' => $courseSet['id']], [], 0, 1);
        $productDetail['hasChosen'] = !empty($chosenCourseSet);

        /**
         * mock
         */
        $merchant = $this->getS2B2CFacadeService()->getMe();

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/show.html.twig', [
            'tab' => $tab,
            'courseSet' => $courseSet,
            'courses' => $courseSet['courses'],
            'merchant' => $merchant,
            'hasChosen' => $chosenCourseSet,
            'marketingPage' => true,
        ]);
    }

    public function productsVersionAction(Request $request)
    {
        $necessaryConditions = [
            'platform' => 'supplier',
        ];
        $conditions = $request->query->all();
        $courseSets = $this->getCourseSetService()->searchCourseSets(array_merge($conditions, $necessaryConditions), [], 0, PHP_INT_MAX);

        $courses = [];
        foreach ($courseSets as $courseSet) {
            $coursesInCourseSet = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
            foreach ($coursesInCourseSet as &$course) {
                $course['courseSet'] = $courseSet;
            }
            $courses = array_merge($courses, $coursesInCourseSet);
        }

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $products = $this->getS2B2CProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($s2b2cConfig['supplierId'], 'course', ArrayToolkit::column($courses, 'id'));

        $courseSetProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($s2b2cConfig['supplierId'], 'course_set', ArrayToolkit::column($courseSets, 'id'));

        $productVersionList = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getProductVersionList(ArrayToolkit::column($products, 'remoteResourceId'));
        if (!empty($productVersionList['error'])) {
            throw $this->createNotFoundException();
        }

        $hasNewVersion = $this->getCacheService()->get('s2b2c.hasNewVersion') ?: [];
        if (!empty($hasNewVersion['courseSet'])) {
            $hasNewVersion['courseSet'] = 0;
            $this->getCacheService()->set('s2b2c.hasNewVersion', $hasNewVersion);
        }

        $merchant = $this->getS2B2CFacadeService()->getMe();

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/list.html.twig',
            [
                'request' => $request,
                'productVersionList' => $productVersionList,
                'courses' => ArrayToolkit::index($courses, 'id'),
                'courseSets' => $courseSets,
                'startDateTime' => empty($conditions['startDateTime']) ? 0 : strtotime($conditions['startDateTime']),
                'endDateTime' => empty($conditions['endDateTime']) ? 0 : strtotime($conditions['endDateTime']),
                'merchant' => $merchant,
                'supplier' => [],
                'products' => ArrayToolkit::index($products, 'remoteResourceId'), //remoteResourceId == remoteResourceId.productId
                'courseSetProducts' => ArrayToolkit::index($courseSetProducts, 'localResourceId'),
            ]
        );
    }

    public function productVersionDetailAction(Request $request, $productId)
    {
        $product = $this->getS2B2CProductService()->getProduct($productId);
        $course = $this->getCourseService()->getCourse($product['localResourceId']);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $productVersions = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getProductVersions($product['remoteResourceId']);
        if (!empty($productVersions['error']) || (!empty($productVersions) && $product['remoteResourceId'] != $productVersions[0]['productId'])) {
            throw $this->createNotFoundException();
        }
//        $versionChangeLogs = $this->getProductService()->generateVersionChangeLogs($course['sourceVersion'], $productVersions);
//        $this->getProductService()->setCourseNewVersionChangeLogs($course['id'], $versionChangeLogs);

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/detail.html.twig',
            [
                'productVersions' => $productVersions,
            ]
        );
    }

    public function updateProductToLatestVersionAction(Request $request, $productId)
    {
        $course = $this->getCourseByProductId($productId);

        if ($request->isMethod('POST')) {
            /**
             * mock
             */
            $merchant = $merchant = $this->getS2B2CFacadeService()->getMe();
            if (empty($merchant['status']) || 'active' != $merchant['status'] || 'cooperation' != $merchant['coop_status']) {
                return $this->createJsonResponse(['status' => false, 'error' => '更新失败']);
            }

            $result = $this->getS2B2CCourseProductService()->updateCourseVersionData($course['id']);

            return $this->createJsonResponse($result);
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/update-modal.html.twig',
            [
                'request' => $request,
                'productId' => $productId,
            ]
        );
    }

    protected function getCourseByProductId($productId)
    {
        $product = $this->getS2B2CProductService()->getProduct($productId);
        $course = $this->getCourseService()->getCourse($product['localResourceId']);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        return $course;
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
}
