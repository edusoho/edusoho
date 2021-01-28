<?php

namespace Biz\Subtitle\Service;

interface SubtitleService
{
    public function findSubtitlesByMediaId($mediaId, $ssl = false);

    public function findSubtitlesByMediaIds($mediaIds);

    public function getSubtitle($id);

    public function addSubtitle($subtitle);

    public function deleteSubtitle($id);

    public function searchSubtitles($conditions, $orderBy, $start, $limit);

    /**
     * 如果是视频，且有字幕，则设置 转码成功的 字幕url数组
     *
     * @param $lesson
     *
     * @return 返回 $lesson, $lesson 中额外有 subtitlesUrls 属性
     *                如  array(
     *                ...
     *                'audioUri': '....',
     *                'subtitlesUrls': ['url1', 'url2']
     *                )
     */
    public function setSubtitlesUrls($lesson, $ssl = false);
}
