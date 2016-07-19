<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\JoinPointToolkit;

class OrderExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(

        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('check_order_type', array($this, 'checkOrderType'))
        );
    }

    public function checkOrderType($type)
    {
      $orderType = JoinPointToolkit::load('order');
      if(in_array($type, array_keys($orderType))){
        return $orderType[$type]['order_show_template'];
      }
      return false;
    }

    private function createService($name)
    {
        return ServiceKernel::instance()->createService($name);
    }

    public function getName()
    {
        return 'topxia_order_twig';
    }
}
