<?php

namespace AppBundle\Controller\AdminV2\CloudCenter\S2B2C;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseSetProductController extends ProductController
{
    public function dealAction(Request $request, $product)
    {
        $courseSetData = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierCourseSetProductDetail($product['id']);
        if (!ArrayToolkit::requireds($courseSetData, ['title', 'type', 'id', 'defaultCourseId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        if ('published' != $courseSetData['editStatus']) {
            $this->createNewException('源平台课程正在编辑中，无法选择');
        }

        $merchant = $this->getS2B2CFacadeService()->getMe();
        if (empty($merchant['coop_status']) || 'active' != $merchant['status'] || 'cooperation' != $merchant['coop_status']) {
            $this->createNewException('用户状态非法，无法选择');
        }

        $visibleCourseTypes = $this->getCourseTypes();
        $type = $courseSetData['type'];
        if (empty($visibleCourseTypes[$type])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        if (empty($s2b2cConfig['supplierId']) || empty($courseSetData['s2b2cDistributeId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        /**
         * 以前通过preparePurchaseData函数去实现，现在直接通过Product函数去实现
         */
        $localProduct = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteProductId($s2b2cConfig['supplierId'], $courseSetData['s2b2cDistributeId']);
        if ($localProduct) {
            return $this->createJsonResponse(['status' => 'repeat']);
        }

        $prepareCourseSet = $this->prepareCourseSetData($courseSetData);
        $purchaseProducts = $this->preparePurchaseData($courseSetData);
        $result = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->checkPurchaseProducts($purchaseProducts);
        if (!empty($result['success']) && true == $result['success']) {
            $result = $this->getS2B2CFacadeService()->getS2B2CService()->purchaseProducts($purchaseProducts);
            if (!empty($result['status']) && true === $result['status']) {
                $newCourseSet = $this->getCourseSetService()->addCourseSet($prepareCourseSet);
                $product = $this->getS2B2CProductService()->createProduct([
                    'supplierId' => $s2b2cConfig['supplierId'],
                    'productType' => 'course_set',
                    'remoteProductId' => $courseSetData['s2b2cDistributeId'],
                    'remoteResourceId' => $courseSetData['id'],
                    'localResourceId' => $newCourseSet['id'],
                ]);
                $this->getCourseProductService()->syncCourses($newCourseSet, $product);
            }

            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(array_merge($result, ['status' => false]));
    }

    /**
     * @return Response
     */
    public function marketAction(Request $request)
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

    /**
     * @return Response
     */
    public function categoriesAction(Request $request)
    {
        $selectedCategory = $request->query->get('selectedCategory', 0);
        $selectedSubCategory = $request->query->get('selectedSubCategory', 0);
        $selectedThirdLevelCategory = $request->query->get('selectedThirdLevelCategory', 0);

        $categoryList = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->searchProductCategories([
                'group' => 'course',
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

    public function productListAction(Request $request)
    {
        $pageSize = 16;
        $conditions = $request->query->all();
        $conditions['offset'] = ($request->query->get('page', 1) - 1) * $pageSize;
        $conditions['limit'] = $pageSize;
        $conditions['sort'] = '-created_time,-id';

        list($courseSets, $total) = $this->getS2B2CProductService()->searchRemoteProducts($conditions);

        $merchant = $this->getS2B2CFacadeService()->getMe();

        $paginator = new Paginator($request, $total, $pageSize);
        $paginator->setBaseUrl($this->generateUrl('admin_v2_purchase_market_products_list', ['type' => 'courseSet']));

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

    public function productDetailAction(Request $request, $remoteResourceId)
    {
        $courseSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->getSupplierCourseSetProductDetail($remoteResourceId);

        if (empty($courseSet) || !empty($courseSet['error'])) {
            throw $this->createNotFoundException('原课程未找到或出错了');
        }
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $chosenCourseSet = $this->getS2B2CProductService()->searchProducts(
            ['supplierId' => $s2b2cConfig['supplierId'], 'productType' => 'course_set', 'remoteResourceId' => $courseSet['id']],
            [],
            0,
            1
        );
        $productDetail['hasChosen'] = !empty($chosenCourseSet);

        $courses = $courseSet['courses'];
        $courses = ArrayToolkit::index($courses, 's2b2cDistributeId');
        $remoteCourseResourceId = $request->get('remoteCourseResourceId');
        $course = !empty($courses[$remoteCourseResourceId]) ? $courses[$remoteCourseResourceId] : $courses[$courseSet['s2b2cDistributeId']];
        unset($courseSet['courses']);

        $merchant = $this->getS2B2CFacadeService()->getMe();

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/show.html.twig', [
            'tab' => $request->get('tab'),
            'courseSet' => $courseSet,
            'courses' => $courses,
            'course' => $course,
            'merchant' => $merchant,
            'hasChosen' => $chosenCourseSet,
            'marketingPage' => true,
        ]);
    }

    protected function prepareCourseSetData($courseSetData)
    {
        return [
            'syncStatus' => 'waiting',
            'sourceCourseSetId' => $courseSetData['id'],
            'title' => $courseSetData['title'],
            'type' => $courseSetData['type'],
            'sourceCourseId' => $courseSetData['defaultCourseId'],
            'subtitle' => $courseSetData['subtitle'],
            'summary' => $courseSetData['summary'],
            'cover' => $courseSetData['cover'],
            'maxCoursePrice' => $courseSetData['maxCoursePrice'],
            'minCoursePrice' => $courseSetData['minCoursePrice'],
            'platform' => 'supplier',
        ];
    }

    protected function preparePurchaseData($courseSetData)
    {
        $settings = $this->getSettingService()->get('storage', []);
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $sourceCourseSetId = $courseSetData['id'];
        $sourceCourseSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->getSupplierCourseSetProductDetail($sourceCourseSetId);
        $sourcesCourses = $sourceCourseSet['courses'];

        $purchaseProducts = [];
        foreach ($sourcesCourses as $course) {
            if (empty($course['s2b2cDistributeId'])) {
                continue;
            }
            $purchaseProducts[] = [
                'product_id' => $course['id'],
                'product_type' => 'course',
                'access_key' => $settings['cloud_access_key'],
                'supplier_id' => $s2b2cConfig['supplierId'],
                'cooperation_price' => $course['cooperationPrice'],
                'suggestion_price' => $course['suggestionPrice'],
                's2b2cDistributeId' => $course['s2b2cDistributeId'],
            ];
        }

        return $purchaseProducts;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseTypes()
    {
        return $this->get('web.twig.course_extension')->getCourseTypes();
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }
}
