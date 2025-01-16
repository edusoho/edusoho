<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class BiLiBiLiVideoItemParser extends AbstractItemParser
{
    protected $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36';

    protected function parseForWebUrl($item, $url)
    {
        $parseUrl = parse_url($url);

        // 获取视频分P参数
        $page = 1; // 默认是第1页
        if (isset($parseUrl['query'])) {
            parse_str($parseUrl['query'], $queryParams);
            $page = $queryParams['p'] ?? 1;
        }

        $response = $this->fetchUrl($url);

        if ($response['code'] !== 200) {
            throw ParserException::PARSED_FAILED_BILIBILI();
        }

        $content = $response['content'];

        // 提取 bvid, aid, cid 等信息
        preg_match('/"bvid":"(.*?)"/', $content, $bvidMatch);
        preg_match('/"aid":(\d+)/', $content, $aidMatch);
        preg_match('/"cid":(\d+)/', $content, $cidMatch);

        if (empty($bvidMatch) || empty($aidMatch) || empty($cidMatch)) {
            throw ParserException::PARSED_FAILED_BILIBILI_VIDEO_ID();
        }

        $bvid = $bvidMatch[1];
        $aid = $aidMatch[1];
        $cid = $cidMatch[1];

        // 提取标题
        preg_match('/<title>(.*?)_哔哩哔哩_bilibili<\/title>/', $content, $titleMatch);
        $title = $titleMatch[1] ?? 'b站视频';

        // 提取视频时长
        preg_match('/"duration":(\d+)/', $content, $durationMatch);
        $duration = $durationMatch[1] ?? 0;

        // 构造播放地址
        $playUrl = $this->getPlayUrl($aid, $bvid, $cid, $page);

        // 构造返回数据
        $parsedInfo = $this->getItem($bvid, $title, $duration, $url, $playUrl);

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
