<?php
namespace Topxia\Service\Media;

interface MediaService
{
    /**
     * [getVideoPlayUrl 获取云视频的播放地址]
     */
    public function getVideoPlayUrl($globalId, $options);
}
