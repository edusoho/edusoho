<?php

namespace Biz\Player\Service;

interface PlayerService
{
    public function getAudioAndVideoPlayerType($file);

    public function agentInWhiteList($userAgent);

    public function getVideoPlayer($file, $agentInWhiteList, $context, $ssl);
}
