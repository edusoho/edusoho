<?php
namespace Biz\Media\Service;

interface MediaService
{
    /**
     * [getVideoPlayUrl 获取云视频的播放地址]
     */
    public function getVideoPlayUrl($globalId, $options);
}
