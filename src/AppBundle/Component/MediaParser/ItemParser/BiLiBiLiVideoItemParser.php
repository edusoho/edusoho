<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class BiLiBiLiVideoItemParser extends AbstractItemParser
{
    protected $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36';

    protected function parseForWebUrl($item, $url)
    {
        $parseUrl = parse_url($url);
        if (isset($parseUrl['query']) && $params = explode('&', $parseUrl['query'])) {
            foreach ($params as $param) {
                $param = explode('=', $param);
                if ('p' == $param[0]) {
                    $page = $param[1];
                }
            }
        }
        $response = $this->fetchUrl($url);

        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_BILIBILI();
        }
        preg_match('/cid=(.*?)&aid=(.*?)&attribute=(.*?)&bvid=(.*?)&show_bv/', $response['content'], $matches);
        if (empty($matches)) {
            preg_match('/"embedPlayer":(.*?)},"upData"/', $response['content'], $matches);
            if (empty($matches)) {
                throw ParserException::PARSED_FAILED_BILIBILI_VIDEO_ID();
            }
            $playerInfo = json_decode($matches[1], true);
        }
        $aid = $playerInfo['aid'] ?? $matches[2];
        $bvid = $playerInfo['bvid'] ?? $matches[4];
        $cid = $playerInfo['cid'] ?? $matches[1];
        $page = $page ?? ($playerInfo['p'] ?? 1);

        preg_match('/<h1 title="(.*?)" class="video-title(.*?)">/', $response['content'], $titleMatches);
        $title = empty($titleMatches) ? '' : $titleMatches[1];

        preg_match('/"state":(.*?),"duration":(.*?),"mission_id"/', $response['content'], $durationMatches);
        $duration = empty($durationMatches) ? '' : $durationMatches[2];

        $parsedInfo = $this->getItem($bvid, $title, $duration, $url, $this->getPlayUrl($aid, $bvid, $cid, $page));

        return array_merge($item, $parsedInfo);
    }

    protected function getPlayUrl($aid, $bvid, $cid, $page)
    {
        return "https://player.bilibili.com/player.html?aid={$aid}&bvid={$bvid}&cid={$cid}&page={$page}";
    }

    protected function getUrlPrefixes()
    {
        return [
            'https://www.bilibili.com'
        ];
    }

    protected function getDefaultParsedInfo()
    {
        return [
            'source' => 'bilibili',
            'name' => 'BiLiBiLi视频',
        ];
    }

    private function getItem($vid, $title, $duration, $pageUrl, $playUrl)
    {
        return [
            'uuid' => 'bilibili:'.$vid,
            'name' => $title,
            'summary' => '',
            'duration' => $duration,
            'page' => $pageUrl,
            'pictures' => [
                ['url' => ''],
            ],
            'files' => [
                ['type' => 'mp4', 'url' => $playUrl],
            ],
        ];
    }

    protected function convertMediaUri($video)
    {
    }
}
