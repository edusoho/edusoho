<?php

namespace AppBundle\Component\Export\Order;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Order\Service\OrderService;

class OrderExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getOrderService()->countOrders($this->conditions);
    }

    public function getTitles()
    {
        return array();
    }

    public function getContent($start, $limit)
    {
    }

    public function buildCondition($conditions)
    {
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return parent::getUserService();
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
