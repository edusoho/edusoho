<?php

namespace AppBundle\Common;

class DynUrlToolkit
{
    /**
     * @param $baseUrl controller调用的render方法中的url, 如 course-manage/create-modal.html.twig
     * @param $params array('type' => 'one-to-one')
     *
     * @return
     *  返回动态页面地址
     *  动态页面地址必须提前在 biz内定义好， 如
     *  $biz['course-manage/create-modal']['one-to-one'] = 'plugins/course-manage/create-modal/one-to-one.html.twig'，
     *  如果此方法的参数
     *  $baseUrl = 'course-manage/create-modal.html.twig'
     *  $params = array('type' => 'one-to-one')
     *  会返回 'plugins/course-manage/create-modal/one-to-one.html.twig'
     */
    public static function getUrl($biz, $baseUrl, $params)
    {
        $twigSegs = explode('.html.twig', $baseUrl);
        $bizPrefix = $twigSegs[0];
        if (!empty($biz[$bizPrefix]) && !empty($biz[$bizPrefix][$params['type']])) {
            return $biz[$bizPrefix][$params['type']];
        }

        return $baseUrl;
    }
}
