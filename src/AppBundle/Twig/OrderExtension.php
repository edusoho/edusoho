<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\JoinPointToolkit;

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

    public function getName()
    {
        return 'topxia_order_twig';
    }
}
