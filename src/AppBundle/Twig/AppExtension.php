<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('currency', array($this, 'currency'))
        );
    }

    public function getFunctions()
    {
        return array(
        );
    }

    /*
     * 返回金额的货币表示
     * @param money 金额，单位：分
     *
     */
    public function currency($money)
    {
        //当前仅考虑中文的货币处理；
        if ($money == 0) {
            return '0';
        }
        return sprintf('%.2f', $money / 100.0);
    }

    public function getName()
    {
        return 'app_twig';
    }
}
