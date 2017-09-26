<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\JoinPointToolkit;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Biz
     */
    protected $biz;

    protected $webStatusMap = array(
        'created' => 'notPaid',
        'paying' => 'notPaid',
        'closed' => 'closed',
        'refunded' => 'closed',
        'fail' => 'paid',
        'paid' => 'paid',
        'refunding' => 'paid',
        'success' => 'paid',
    );

    protected $adminStatusMap = array(
        'created' => 'notPaid',
        'paying' => 'notPaid',
        'closed' => 'closed',
        'refunded' => 'refunded',
        'fail' => 'paid',
        'paid' => 'paid',
        'refunding' => 'paid',
        'success' => 'paid',
    );

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('check_order_type', array($this, 'checkOrderType')),
            new \Twig_SimpleFunction('display_order_status', array($this, 'displayOrderStatus'), array('is_safe' => array('html'))),
        );
    }

    public function checkOrderType($type)
    {
        $orderType = JoinPointToolkit::load('order');
        if (in_array($type, array_keys($orderType))) {
            return $orderType[$type]['order_show_template'];
        }

        return false;
    }

    public function displayOrderStatus($orderStatus, $isAdmin = 1)
    {
        $displayStatus = $this->getDisplayStatus($orderStatus, $isAdmin);

        return $isAdmin ? $this->displayAdminStatus($displayStatus) : $this->displayWebStatus($displayStatus);
    }

    public function getOrderStatusFromDisplayStatus($displayStatus, $isAdmin = 1)
    {
        $map = $isAdmin ? $this->adminStatusMap : $this->webStatusMap;

        $result = array();
        foreach ($map as $orderStatus => $disStatus) {
            if ($displayStatus == $disStatus) {
                $result[] = $orderStatus;
            }
        }

        return $result;
    }

    public function getAllDisplayStatus($isAdmin = 1)
    {
        $map = $isAdmin ? $this->adminStatusMap : $this->webStatusMap;

        return array_unique($map);
    }

    public function getStatusMap($isAdmin = 1)
    {
        return $isAdmin ? $this->adminStatusMap : $this->webStatusMap;
    }

    private function getDisplayStatus($orderStatus, $isAdmin)
    {
        $map = $isAdmin ? $this->adminStatusMap : $this->webStatusMap;

        return isset($map[$orderStatus]) ? $map[$orderStatus] : $orderStatus;
    }

    private function displayAdminStatus($displayStatus)
    {
        $text = $this->container->get('codeages_plugin.dict_twig_extension')->getDictText('orderDisplayStatus', $displayStatus);
        switch ($displayStatus) {
            case 'notPaid':
                $majorClass = 'label-warning';
                break;
            case 'paid':
                $majorClass = 'label-success';
                break;
            case 'refunded':
                $majorClass = 'label-danger';
                break;
            case 'closed':
                $majorClass = 'label-default';
                break;
            default:
                $majorClass = 'label-default';
        }

        return sprintf('<span class="label %s">%s</span>', $majorClass, $text);
    }

    private function displayWebStatus($displayStatus)
    {
        $text = $this->container->get('codeages_plugin.dict_twig_extension')->getDictText('orderDisplayStatus', $displayStatus);
        switch ($displayStatus) {
            case 'notPaid':
                $majorClass = 'cd-status-warning';
                break;
            case 'paid':
                $majorClass = 'cd-status-success';
                break;
            case 'refunded':
                $majorClass = 'cd-status-danger';
                break;
            case 'closed':
                $majorClass = 'cd-status-disabled';
                break;
            default:
                $majorClass = 'cd-status-disabled';
        }

        return sprintf('<span class="cd-status %s">%s</span>', $majorClass, $text);
    }

    public function getName()
    {
        return 'topxia_order_twig';
    }
}
