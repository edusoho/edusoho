<?php

namespace AppBundle\Controller;

class HLSVideoController extends HLSBaseController
{
    /**
     * 取被播放m3u8的属性
     */
    protected function getMediaAttr()
    {
        return 'metas2';
    }

    /**
     * 用于生成路由,
     *   hls_{$this->getRoutingPrefix()}clef
     *   hls_{$this->getRoutingPrefix()}stream
     */
    protected function getRoutingPrefix()
    {
        return '';
    }
}
