<?php

namespace AppBundle\Controller;

class HLSVideoController extends HLSBaseController
{
    /**
     * return audioMetas2 或 metas2
     */
    protected function getMediaAttr()
    {
        return 'metas2';
    }

    /**
     * video使用老路由
     */
    protected function getRoutingPrefix()
    {
        return '';
    }
}
