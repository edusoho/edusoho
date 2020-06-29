<?php

namespace AppBundle\Controller\AdminV2\CloudCenter\S2B2C;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use QiQiuYun\SDK\Service\S2B2CService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseSetProductController extends ProductController
{
    public function dealAction(Request $request, $product)
    {
        $result = $this->getS2B2CProductService()->adoptProduct($product['id']);

        return $this->createJsonResponse($result);
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
        $offset = ($request->query->get('page', 1) - 1) * $pageSize;

        $distributes = $this->getS2B2CFacadeService()->getS2B2CService()->searchDistribute($conditions, ['created_time' => 'desc'], $offset, $pageSize);
        $products = ArrayToolkit::column($distributes['items'], 'product');
        $merchant = $this->getS2B2CFacadeService()->getMe();

        $paginator = new Paginator($request, $distributes['count'], $pageSize);
        $paginator->setBaseUrl($this->generateUrl('admin_v2_purchase_market_products_list', ['type' => 'courseSet']));

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $s2b2cProductIds = ArrayToolkit::column($products, 'id');
        //$remoteResourceIds = ArrayToolkit::column($courseSets, 'id');

        if (!empty($s2b2cConfig['supplierId'])) {
            $chosenProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndIds($s2b2cConfig['supplierId'], 'course_set', $s2b2cProductIds);
            //$chosenProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndIds($s2b2cConfig['supplierId'], 'course_set', $remoteResourceIds);
            $chosenProducts = ArrayToolkit::index($chosenProducts, 'remoteProductId');
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/course-list.html.twig', [
            'products' => $products,
            //'courseSets' => $courseSets,
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
                'selling_price' => $course['cooperationPrice'],
                's2b2cDistributeId' => $course['s2b2cDistributeId'],
            ];
        }

        $purchaseRecord = [
            'parent_id' => $sourceCourseSet['id'],
            'parent_title' => $sourceCourseSet['title'],
            'type' => 'course',
            'product_ids' => ArrayToolkit::column($sourcesCourses, 'id'),
        ];

        return [$purchaseProducts, $purchaseRecord];
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
