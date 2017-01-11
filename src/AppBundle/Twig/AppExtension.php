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
            new \Twig_SimpleFunction('services', array($this, 'buildServiceTags'))
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

    public function buildServiceTags($selectedTags)
    {
        $tags = array(
            array(
                'short_name' => '练',
                'full_name'  => '24小时作业批改',
                'summary'    => '24小时内完成作业批改，即时反馈并巩固您的学习效果',
                'active'     => 0
            ),
            array(
                'short_name' => '试',
                'full_name'  => '24小时阅卷点评',
                'summary'    => '24小时内批阅您提交的试卷，给予有针对性的点评',
                'active'     => 0
            ),
            array(
                'short_name' => '问',
                'full_name'  => '提问必答',
                'summary'    => '对于提问做到有问必答，帮您扫清学习过程中的种种障碍',
                'active'     => 0
            ),
            array(
                'short_name' => '疑',
                'full_name'  => '一对一在线答疑',
                'summary'    => '提供专属的一对一在线答疑，快速答疑解惑。',
                'active'     => 0
            )
        );

        if (empty($selectedTags)) {
            return $tags;
        }
        foreach ($tags as &$tag) {
            if (in_array($tag['full_name'], $selectedTags)) {
                $tag['active'] = 1;
            }
        }
        return $tags;
    }

    public function getName()
    {
        return 'app_twig';
    }
}
