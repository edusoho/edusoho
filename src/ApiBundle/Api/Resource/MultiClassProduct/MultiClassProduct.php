<?php


namespace ApiBundle\Api\Resource\MultiClassProduct;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Order\Dao\OrderItemDao;

class MultiClassProduct extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $product = [
            'title' => $request->request->get('title'),
            'remark' => $request->request->get('remark'),
        ];

        if (empty($product['title'])){
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $existed = $this->getMultiClassProductService()->getProductByTitle($product['title']);

        if (!empty($existed['id'])) {
            throw MultiClassException::MULTI_CLASS_PRODUCT_EXIST();
        }

        $product = $this->getMultiClassProductService()->createProduct($product);

        return $product;
    }

    public function search(ApiRequest $request)
    {
        $conditions = [
            'keywords' => $request->query->get('keywords', '')
        ];
        $isPage = $request->query->get('isPage', 0);

        if ($isPage){
            list($offset, $limit) = $this->getOffsetAndLimit($request);

            $products = $this->getMultiClassProductService()->searchProducts($conditions, [], $offset, $limit);
            $products = $this->appendBaseInfo($products);
            $total = $this->getMultiClassProductService()->countProducts($conditions);

            $products = $this->makePagingObject($products, $total, $offset, $limit);

        }else{
            $products = $this->getMultiClassProductService()->searchProducts($conditions, [], 0, PHP_INT_MAX);;
        }

        return $products;
    }

    protected function appendBaseInfo($products)
    {
        foreach ($products as &$product){
            $multiClasses = $this->getMultiClassService()->findByProductId($product['id']);
            $product['multiClassNum'] = count($multiClasses);
            $courseSetIds = array_column($multiClasses, 'courseId') ? array_column($multiClasses, 'courseId') : [-1];
            $product['estimatedIncome'] = $this->getOrderItemDao()->sumPayAmount(['target_ids' => $courseSetIds, 'target_type' => 'course', 'statuses' => ['success', 'paid', 'finished']]);
            $product['studentNum'] = $this->getCourseMemberService()->countMembers(['multiClassId' => $product['id'], 'joinedType' => 'course']);
            $product['taskNum'] = $this->getTaskService()->countTasks(['multiClassId' => $product['id'], 'status' => 'publish']);
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
     * @return OrderItemDao
     */
    protected function getOrderItemDao()
    {
        return $this->getBiz()->dao('Order:OrderItemDao');
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