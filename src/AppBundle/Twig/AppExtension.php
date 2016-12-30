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
            new \Twig_SimpleFilter('currency', array($this, 'currency')),
            new \Twig_SimpleFilter('json_encode_utf8', array($this, 'json_encode_utf8'))
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

    /**
     * json_encode($arr, JSON_UNESCAPED_UNICODE) 需要PHP5.4以上版本，所以自己写一个以便支持PHP5.3
     * @param  $arr
     * @return string
     */
    public function json_encode_utf8($arr)
    {
        if (empty($arr)) {
            return '[]';
        }

        $encoded = json_encode($arr);

        return preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
            return html_entity_decode('&#x'.$matches[1].';', ENT_COMPAT, 'UTF-8');
        }, $encoded);
    }

    public function getName()
    {
        return 'app_twig';
    }
}
