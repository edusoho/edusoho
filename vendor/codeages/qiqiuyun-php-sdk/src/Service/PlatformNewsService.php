<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Constants\PlatformNewsBlockTypes;

/**
 * Open站
 */
class PlatformNewsService extends BaseService
{
    protected $host = 'platform-news-service.qiqiuyun.net';

    /**
     * 获取ES商学院课程推荐
     *
     * @param $limit
     *
     * @return array
     *               id   int  区块id
     *               name string  区块名
     *               returnUrl string  跳转es商学院url
     *               details  array    取回的信息
     *               *title    string 课程名
     *               *subtitle string 课程说明
     *               *image    string 课程封面
     *               *url      string 课程url
     *               *position int    课程推荐排序
     */
    public function getAdvice($limit = 4)
    {
        return $this->getFromPlatformNews(PlatformNewsBlockTypes::ADVICE_BLOCK, $limit);
    }

    /**
     * 获取推荐应用
     *
     * @param $limit
     *
     * @return array
     *               id   int  区块id
     *               name string  区块名
     *               returnUrl string  跳转营销云url
     *               details  array    取回的信息
     *               *title    string 应用名
     *               *subtitle string 应用说明
     *               *image    string 应用封面
     *               *url      string 应用url
     *               *position int    应用排序
     */
    public function getApplications($limit = 4)
    {
        return $this->getFromPlatformNews(PlatformNewsBlockTypes::PLUGIN_BLOCK, $limit);
    }

    /**
     * 获取公众号二维码
     *
     * @param $limit
     *
     * @return array
     *               id   int  区块id
     *               name string  区块名
     *               returnUrl string  跳转url
     *               details  array    取回的信息
     *               *title    string 公众号名
     *               *subtitle string 二维码描述
     *               *image    string 二维码图片
     *               *url      string 二维码图片url
     *               *position int
     */
    public function getQrCode($limit = 1)
    {
        return $this->getFromPlatformNews(PlatformNewsBlockTypes::QR_CODE_BLOCK, $limit);
    }

    /**
     * 获取站长公告
     *
     * @param $limit
     *
     * @return array
     *               id   int  区块id
     *               name string  区块名
     *               returnUrl string  跳转url
     *               details  array    取回的信息
     *               *title    string 公告title
     *               *subtitle string 公告内容
     *               *image    string 公告图片
     *               *url      string 公告url
     *               *position int
     */
    public function getAnnouncements($limit = 1)
    {
        return $this->getFromPlatformNews(PlatformNewsBlockTypes::ANNOUNCEMENT_BLOCK, $limit);
    }

    /**
     * 根据区块id获取区块信息
     *
     * @param $blockId
     * @param int $limit
     *
     * @return array
     *               id int  区块id
     *               name string  区块名
     *               returnUrl string  跳转url
     *               details array    取回的信息
     */
    protected function getFromPlatformNews($blockId, $limit = 4)
    {
        return $this->request('GET', "/api/news/block/{$blockId}", array('limit' => $limit));
    }
}
