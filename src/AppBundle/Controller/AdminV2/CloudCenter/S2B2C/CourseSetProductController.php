<?php

namespace AppBundle\Controller\AdminV2\CloudCenter\S2B2C;

use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetProductController extends ProductController
{
    public function dealAction(Request $request, $product)
    {
        $courseSetData = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierCourseSetProductDetail($product['id']);
        if (!ArrayToolkit::requireds($courseSetData, array('title', 'type', 'id', 'defaultCourseId'))) {
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

        $request = $this->setRequestDefaultParams($request, $courseSetData);
        if ($this->hasChosenCourseProduct($request)) {
            return $this->createJsonResponse(array('status' => 'repeat'));
        }

        $purchaseProducts = $this->preparePurchaseData($request, $courseSetData);
        $result = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->checkPurchaseProducts($purchaseProducts);

        if (!empty($result['success']) && true == $result['success']) {
            $result = $this->getS2B2CFacadeService()->getS2B2CService()->purchaseProducts($purchaseProducts);
            if (!empty($result['status']) && true === $result['status']) {
                $this->forward($visibleCourseTypes[$type]['saveAction'], array('request' => $request));
            }

            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(array_merge($result, array('status' => false)));
    }

    protected function setRequestDefaultParams(Request $request, $courseSetData)
    {
        $supplierSettings = $this->getSettingService()->get('supplierSettings', array());
        if (empty($supplierSettings['supplierId']) || empty($courseSetData['s2b2cDistributeId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $defaultParams = array(
            'originPlatform' => 'supplier',
            'originPlatformId' => $supplierSettings['supplierId'],
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
            'courseSetData' => '',
            's2b2cDistributeId' => $courseSetData['s2b2cDistributeId'],
        );
        foreach ($defaultParams as $paramsName => $value) {
            $request->request->set($paramsName, $value);
        }

        return $request;
    }

    protected function preparePurchaseData(Request $request, $courseSetData)
    {
        $settings = $this->getSettingService()->get('storage', array());
        $supplierSettings = $this->getSettingService()->get('supplierSettings', array());
        $sourceCourseSetId = $request->request->get('sourceCourseSetId');
        $sourceCourseSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->getSupplierCourseSetProductDetail($sourceCourseSetId);
        $sourcesCourses = $sourceCourseSet['courses'];

        $purchaseProducts = array();
        foreach ($sourcesCourses as $course) {
            if (empty($course['s2b2cDistributeId'])) {
                continue;
            }
            $purchaseProducts[] = array(
                'product_id' => $course['id'],
                'product_type' => 'course',
                'access_key' => $settings['cloud_access_key'],
                'supplier_id' => $supplierSettings['supplierId'],
                'cooperation_price' => $course['cooperationPrice'],
                'suggestion_price' => $course['suggestionPrice'],
                's2b2cDistributeId' => $course['s2b2cDistributeId'],
            );
        }

        return $purchaseProducts;
    }

    protected function hasChosenCourseProduct($request)
    {
        $chosenCourseSet = $this->getCourseSetService()->getCourseSetBySourceCourseSetId($request->request->get('sourceCourseSetId'));
        $chosenCourse = $this->getProductService()->getOriginPlatformCourse($request->request->get('originPlatform'), $request->request->get('originPlatformId'), $request->request->get('sourceCourseId'));

        return !empty($chosenCourseSet) || !empty($chosenCourse);
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
}
