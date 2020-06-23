<?php

namespace Biz\Player\Service;

interface PlayerService
{
    public function getAudioAndVideoPlayerType($file);

    public function getVideoFilePlayer($file, $agentInWhiteList, $context, $ssl);

    public function isHiddenVideoHeader($isHidden = false);

    public function getDocFilePlayer($doc, $ssl);

    public function getPptFilePlayer($ppt, $ssl);

    public function getVideoPlayUrl($file, $context, $ssl);

    public function getFlashFilePlayer($flash, $ssl);
}
