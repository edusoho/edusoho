<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class ResourcePurchaseController extends BaseController
{
    public function marketAction(Request $request, $tab)
    {
        $methodName = "{$tab}Market";

        return $this->$methodName($request);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *                                                    called from marketAction
     */
    protected function courseSetMarket(Request $request)
    {
        $supplierSiteSetting = $this->getS2B2CFacadeService()->getSupplier();
        $merchant = $this->getS2B2CFacadeService()->getMe();

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/explore.html.twig',
            [
                'tags' => [],
                'supplierSiteSetting' => $supplierSiteSetting,
                'merchant' => $merchant,
                'supplier' => [],
            ]
        );
    }

    public function categoriesAction(Request $request, $group = 'course')
    {
        $selectedCategory = $request->query->get('selectedCategory', 0);
        $selectedSubCategory = $request->query->get('selectedSubCategory', 0);
        $selectedThirdLevelCategory = $request->query->get('selectedThirdLevelCategory', 0);

        /**
         * mock
         */
        $categoryList = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->searchProductCategories([
                'group' => $group,
            ]);

        if (!empty($categoryList['error'])) {
            $categories = $subCategories = $thirdLevelCategories = [];
        } else {
            list($categories, $subCategories, $thirdLevelCategories) = $categoryList;
        }

        return $this->render('admin-v2/cloud-center/content-resource/market/course-set/category.html.twig', [
            'selectedCategory' => $selectedCategory,
            'selectedSubCategory' => $selectedSubCategory,
            'selectedThirdLevelCategory' => $selectedThirdLevelCategory,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'thirdLevelCategories' => $thirdLevelCategories,
            'subCategoriesData' => empty($subCategories) ? [] : ArrayToolkit::group($subCategories, 'parentId'),
            'thirdLevelCategoriesData' => empty($thirdLevelCategories) ? [] : ArrayToolkit::group($thirdLevelCategories, 'parentId'),
            'request' => $request,
        ]);
    }

    public function productsAction(Request $request)
    {
        $pageSize = 16;

        $conditions = $request->query->all();
        $conditions['offset'] = ($request->query->get('page', 1) - 1) * $pageSize;
        $conditions['limit'] = $pageSize;
        $conditions['sort'] = '-created_time,-id';
        /*
         * mock
         */
        list($courseSets, $total) = $this->getS2B2CProductService()->searchRemoteProducts($conditions);

        $merchant = $this->getS2B2CFacadeService()->getMe();

        $paginator = new Paginator($request, $total, $pageSize);
        $paginator->setBaseUrl($this->generateUrl('admin_v2_purchase_market_products_list'));

        $supplierSettings = $this->getSettingService()->get('supplierSettings', []);

        $remoteResourceIds = ArrayToolkit::column($courseSets, 'id');

        if (!empty($supplierSettings['supplierId'])) {
            $chosenProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndIds($supplierSettings['supplierId'], 'course_set', $remoteResourceIds);
            $chosenProducts = ArrayToolkit::index($chosenProducts, 'remoteResourceId');
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/course-list.html.twig', [
            'courseSets' => $courseSets,
            'paginator' => $paginator,
            'merchant' => $merchant,
            'chosenCourses' => empty($chosenProducts) ? [] : $chosenProducts,
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
            'originProductId_GT' => 0,
            'syncStatus' => 'finished',
        ];
        $conditions = $request->query->all();
        $courseSets = $this->getCourseSetService()->searchCourseSets(array_merge($conditions, $necessaryConditions), [], 0, PHP_INT_MAX);
        $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseSets = ArrayToolkit::index($courseSets, 'defaultCourseId');
        $courses = ArrayToolkit::index($courses, 'sourceCourseId');

        /**
         * mock
         * 暂时无法获取完整数据结构
         */
        $productVersionList = [];
        if (!empty($productVersionList['error'])) {
            throw $this->createNotFoundException();
        }
        foreach ($courseSets as $courseSet) {
            /*
             * 暂时容错处理
             */
            if (!empty($courseSet['hasNewVersion'])) {
                $this->getProductService()->changeCourseProductHasNewVersionStatus($courseSet['id'], 0);
            }
        }

        /**
         * mock
         */
        $merchant = $this->getS2B2CFacadeService()->getMe();

        return $this->render(
            'admin-v2/cloud-center/content-resource/product-version/list.html.twig',
            [
                'request' => $request,
                'productVersionList' => $productVersionList,
                'courses' => $courses,
                'courseSets' => $courseSets,
                'startDateTime' => empty($conditions['startDateTime']) ? 0 : strtotime($conditions['startDateTime']),
                'endDateTime' => empty($conditions['endDateTime']) ? 0 : strtotime($conditions['endDateTime']),
                'merchant' => $merchant,
                'supplier' => [],
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
            /**
             * mock
             */
            $result = [];
//            $result = $this->getProductService()->updateCourseVersionData($course['id']);

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
        $supplierSettings = $this->getSettingService()->get('supplierSettings', []);
        if (empty($supplierSettings['supplierId'])) {
            throw $this->createNotFoundException();
        }
        $course = $this->getS2B2CProductService()->getOriginPlatformCourse('supplier', $supplierSettings['supplierId'], $productId);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        return $course;
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
}
