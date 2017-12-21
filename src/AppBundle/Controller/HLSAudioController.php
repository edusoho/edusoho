<?php

namespace AppBundle\Controller;

class HLSVideoController extends HLSBaseController
{
    protected function getMediaAttr()
    {
        return 'audioMetas2';
    }

    protected function getRoutingPrefix()
    {
        return 'audio_';
    }
}
