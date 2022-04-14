<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class BiLiBiLiVideoItemParser extends AbstractItemParser
{
    protected $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36';

    protected function parseForWebUrl($item, $url)
    {
        $response = $this->fetchUrl($url);

        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_BILIBILI();
        }
        preg_match('/cid=(.*?)&aid=(.*?)&attribute=(.*?)&bvid=(.*?)&show_bv/', $response['content'], $matches);
        if (empty($matches)) {
            throw ParserException::PARSED_FAILED_BILIBILI_VIDEO_ID();
        }
        $bvid = $matches[4];
        preg_match('/<h1 title="(.*?)" class="video-title">/', $response['content'], $titleMatches);
        if (empty($titleMatches)) {
            $title = '';
        } else {
            $title = $titleMatches[1];
        }

        $parsedUrl = parse_url($url);
        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $parsedQuery);
        }
        $page = $parsedQuery['p'] ?? 1;
        $parsedInfo = $this->getItem($bvid, $title, $url, $this->getPlayUrl($matches, $page));

        return array_merge($item, $parsedInfo);
    }

    protected function getPlayUrl($matches, $page)
    {
        return "https://player.bilibili.com/player.html?aid={$matches[2]}&bvid={$matches[4]}&cid={$matches[1]}&page={$page}";
    }

    protected function getUrlPrefixes()
    {
        return [
            'www.bilibili.com',
        ];
    }

    protected function getDefaultParsedInfo()
    {
        return [
            'source' => 'bilibili',
            'name' => 'BiLiBiLi视频',
        ];
    }

    private function getItem($vid, $title, $pageUrl, $playUrl)
    {
        return [
            'uuid' => 'bilibili:'.$vid,
            'name' => $title,
            'summary' => '',
            'duration' => '',
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
