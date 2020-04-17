<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Codeages\Biz\Order\Service\Impl\OrderServiceImpl;
use Symfony\Component\HttpFoundation\Request;

class ResourceSettlementController extends BaseController
{
    private $pageSize = 20;

    const TYPE_BALANCE = 'balance';

    const TYPE_ORDER = 'order';

    const TYPE_PRODUCT = 'product';

    public function balanceAction(Request $request)
    {
        $conditions = $this->prepareConditionsByType($request->query->all(), self::TYPE_BALANCE);

        $resultSet = $this->getS2B2CFacadeService()->getS2B2CService()->searchMerchantFlow(
            $conditions,
            array('created_time' => 'DESC'),
            $request->query->get('page', 0),
            $this->pageSize
        );

        $paginator = new Paginator($request, $resultSet['count'], $this->pageSize);

        return $this->render('admin-v2/resource-settlement/balance/index.html.twig', array(
            'paginator' => $paginator,
            'items' => $resultSet['items'],
            'merchant' => $this->getS2B2CFacadeService()->getMe(),
            'total' => $resultSet['count'],
        ));
    }

    public function balanceModalAction(Request $request, $id)
    {
        $detail = $this->getS2B2CFacadeService()->getS2B2CService()->getFlowDetail($id);
        if (empty($detail)) {
            throw $this->createNotFoundException("Flow#{$id} not found");
        }

        return $this->render('admin-v2/resource-settlement/balance/modal.html.twig', array('detail' => $detail));
    }

    public function orderAction(Request $request)
    {
        $conditions = $this->prepareConditionsByType($request->query->all(), self::TYPE_ORDER);

        $resultSet = $this->getS2B2CFacadeService()->getS2B2CService()->searchMerchantOrder(
            $conditions,
            array('created_time' => 'DESC'),
            $request->query->get('page', 0),
            $this->pageSize
        );

        $paginator = new Paginator($request, $resultSet['count'], $this->pageSize);

        return $this->render('admin-v2/resource-settlement/order/index.html.twig', array(
            'paginator' => $paginator,
            'items' => $resultSet['items'],
            'merchant' => $this->getS2B2CFacadeService()->getMe(),
            'total' => $resultSet['count'],
            'pageSize' => $this->pageSize,
        ));
    }

    public function orderModalAction(Request $request, $sn)
    {
        $detail = $this->getS2B2CFacadeService()->getS2B2CService()->getOrderDetail($sn);
        if (empty($detail)) {
            throw $this->createNotFoundException("Flow#{$sn} not found");
        }

        return $this->render('admin-v2/resource-settlement/order/modal.html.twig', array(
            'detail' => $detail,
            'merchant' => $this->getS2B2CFacadeService()->getMe(),
        ));
    }

    public function productAction(Request $request)
    {
        $conditions = $this->prepareConditionsByType($request->query->all(), self::TYPE_PRODUCT);

        $conditions['offset'] = $request->query->get('page', 0);

        $resultSet = $this->getProductService()->searchSelectedItemProduct($conditions);

        $paginator = new Paginator($request, $resultSet['count'], $this->pageSize);

        return $this->render(
            'admin-v2/resource-settlement/product.html.twig',
            array(
                'paginator' => $paginator,
                'items' => $resultSet['items'],
                'merchant' => $this->getS2B2CFacadeService()->getMe(),
                'total' => $resultSet['count'],
                'pageSize' => $this->pageSize,
            ));
    }

    protected function prepareConditionsByType($conditions, $type)
    {
        $conditionTypes = array(
            self::TYPE_BALANCE => array(
                'created_time_GTE' => empty($conditions['startTime']) ? null : strtotime($conditions['startTime']),
                'created_time_LTE' => empty($conditions['endTime']) ? null : strtotime($conditions['endTime']),
                'title_like' => empty($conditions['title']) ? null : $conditions['title'],
            ),
            self::TYPE_ORDER => array(
                'only_show_debt' => empty($conditions['showDebt']) ? 0 : 1,
                'start_time' => empty($conditions['startTime']) ? null : strtotime($conditions['startTime']),
                'end_time' => empty($conditions['endTime']) ? null : strtotime($conditions['endTime']),
                'status' => empty($conditions['status']) ? null : $conditions['status'],
                'title_like' => empty($conditions['title']) ? null : $conditions['title'],
            ),
            self::TYPE_PRODUCT => array(
                'productName' => empty($conditions['title']) ? null : $conditions['title'],
                'limit' => $this->pageSize,
                'sorts' => array('created_time' => 'DESC'),
            ),
        );

        return empty($conditionTypes[$type]) ? array() : $conditionTypes[$type];
    }

    /**
     * @return OrderServiceImpl
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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
    protected function getProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
