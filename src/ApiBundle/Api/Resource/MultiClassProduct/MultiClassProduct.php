<?php

namespace ApiBundle\Api\Resource\MultiClassProduct;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;

class MultiClassProduct extends AbstractResource
{
    /**
     * @param $id
     *
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function update(ApiRequest $request, $id)
    {
        $product = $this->getMultiClassProductService()->getProduct($id);

        if (empty($product)) {
            throw MultiClassException::PRODUCT_NOT_FOUND();
        }

        $fields = [
            'title' => $request->request->get('title'),
            'remark' => $request->request->get('remark'),
        ];

        if (empty($fields['title'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $existed = $this->getMultiClassProductService()->getProductByTitle($fields['title']);

        if (!empty($existed['id']) && $product['id'] != $existed['id']) {
            throw MultiClassException::MULTI_CLASS_PRODUCT_EXIST();
        }

        return $this->getMultiClassProductService()->updateProduct($product['id'], $fields);
    }

    /**
     * @param $id
     *
     * @return bool[]
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function remove(ApiRequest $request, $id)
    {
        $product = $this->getMultiClassProductService()->getProduct($id);

        if (empty($product)) {
            throw MultiClassException::PRODUCT_NOT_FOUND();
        }

        if ('default' === $product['type']) {
            throw MultiClassException::CANNOT_DELETE_DEFAULT_PRODUCT();
        }

        $multiClass = $this->getMultiClassService()->findByProductId($product['id']);
        if (!empty($multiClass)) {
            throw MultiClassException::CAN_NOT_DELETE_PRODUCT();
        }

        $this->getMultiClassProductService()->deleteProduct($product['id']);

        return ['success' => true];
    }

    /**
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $product = [
            'title' => $request->request->get('title'),
            'remark' => $request->request->get('remark', ''),
        ];

        if (empty($product['title'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $existed = $this->getMultiClassProductService()->getProductByTitle($product['title']);

        if (!empty($existed['id'])) {
            throw MultiClassException::MULTI_CLASS_PRODUCT_EXIST();
        }

        $product = $this->getMultiClassProductService()->createProduct($product);

        return $product;
    }

    /**
     * @return array
     * @Access(roles="ROLE_TEACHER_ASSISTANT,ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request)
    {
        $conditions = [
            'keywords' => $request->query->get('keywords', ''),
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $products = $this->getMultiClassProductService()->searchProducts($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $products = $this->appendBaseInfo($products);
        $total = $this->getMultiClassProductService()->countProducts($conditions);

        $products = $this->makePagingObject($products, $total, $offset, $limit);

        return $products;
    }

    protected function appendBaseInfo($products)
    {
        $multiClasses = $this->getMultiClassService()->findByProductIds(array_column($products, 'id'));
        $multiClasses = ArrayToolkit::group($multiClasses, 'productId');
        foreach ($products as &$product) {
            $classes = isset($multiClasses[$product['id']]) ? $multiClasses[$product['id']] : [];
            $product['multiClassNum'] = count($classes);
            $courseIds = ArrayToolkit::column($classes, 'courseId') ? ArrayToolkit::column($classes, 'courseId') : [-1];
            $income = $this->getCourseService()->sumTotalIncomeByIds($courseIds);
            $product['income'] = $income ? $income : '0.00';
            $product['studentNum'] = $this->getCourseMemberService()->countMembers(['courseIds' => $courseIds, 'role' => 'student']);
            $product['taskNum'] = $this->getTaskService()->countTasks(['courseIds' => $courseIds, 'isLesson' => 1]);
        }

        return $products;
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
