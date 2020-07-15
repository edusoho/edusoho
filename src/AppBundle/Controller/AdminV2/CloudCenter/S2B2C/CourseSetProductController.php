<?php

namespace AppBundle\Controller\AdminV2\CloudCenter\S2B2C;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseSetProductController extends ProductController
{
    public function dealAction(Request $request, $product)
    {
        $result = $this->getS2B2CProductService()->adoptProduct($product['id']);

        return $this->createJsonResponse(['status' => $result]);
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

        $result = $this->getS2B2CFacadeService()->getS2B2CService()->searchDistribute($conditions, ['createdTime' => 'desc'], $offset, $pageSize);

        $products = $result['items'];
        $merchant = $this->getS2B2CFacadeService()->getMe();

        $paginator = new Paginator($request, $result['count'], $pageSize);
        $paginator->setBaseUrl($this->generateUrl('admin_v2_purchase_market_products_list', ['type' => 'courseSet']));

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $s2b2cProductIds = ArrayToolkit::column($products, 'id');

        if (!empty($s2b2cConfig['supplierId'])) {
            $chosenProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndProductIds($s2b2cConfig['supplierId'], 'course_set', $s2b2cProductIds);
            $chosenProducts = ArrayToolkit::index($chosenProducts, 'remoteProductId');
        }

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/course-list.html.twig', [
            'products' => $products,
            'paginator' => $paginator,
            'merchant' => $merchant,
            'chosenCourses' => empty($chosenProducts) ? [] : $chosenProducts,
        ]);
    }

    public function productDetailAction(Request $request, $s2b2cProductId, $courseId)
    {
        $result = $this->getS2B2CFacadeService()->getS2B2CService()->getDistributeProduct($s2b2cProductId);
        if (!empty($result['status']) && 'success' == $result['status']) {
            $product = $result['data'];
            $courseSet = $product['content'];
        } else {
            throw $this->createNotFoundException('商品未找到或出错了');
        }

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $chosenCourseSet = $this->getS2B2CProductService()->searchProducts(
            ['supplierId' => $s2b2cConfig['supplierId'], 'productType' => 'course_set', 'remoteProductId' => $s2b2cProductId],
            [],
            0,
            1
        );
        $productDetail['hasChosen'] = !empty($chosenCourseSet);
        $courses = $courseSet['courses'];
        $course = array_filter($courses, function ($course) use ($courseId) {
            return $course['id'] == $courseId;
        });
        $course = empty($course) ? $courses[0] : current($course);
        unset($courseSet['courses']);

        $merchant = $this->getS2B2CFacadeService()->getMe();

        return $this->render(
            'admin-v2/cloud-center/content-resource/market/course-set/show.html.twig', [
            'tab' => $request->get('tab'),
            'product' => $product,
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
