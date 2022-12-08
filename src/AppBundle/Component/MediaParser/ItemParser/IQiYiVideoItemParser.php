<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class IQiYiVideoItemParser extends AbstractItemParser
{
    protected function parseForWebUrl($item, $url)
    {
        $response = $this->fetchUrl($url);
        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_IQIYI();
        }

        preg_match('/window\.Q\.PageInfo\.playPageInfo=(.*?);/', $response['content'], $matches);
        if (empty($matches)) {
            throw ParserException::PARSED_FAILED_IQIYI_VIDEO_ID();
        }
        $data = json_decode($matches[1], true);

        $parsedInfo = $this->getItem($data['vid'], $data['name'], $url, $this->getPlayUrl($data));

        return array_merge($item, $parsedInfo);
    }

    protected function getPlayUrl($matches)
    {
        return "https://open.iqiyi.com/developer/player_js/coopPlayerIndex.html?vid={$matches['vid']}&tvId={$matches['tvId']}&accessToken=2.ef9c39d6c7f1d5b44768e38e5243157d&appKey=8c634248790d4343bcae1f66129c1010&appId=1368&height=100%&width=100%";
    }

    protected function getUrlPrefixes()
    {
        return [
            'https://www.iqiyi.com',
        ];
    }

    protected function getDefaultParsedInfo()
    {
        return [
            'source' => 'iqiyi',
            'name' => '爱奇艺视频',
        ];
    }

    private function getItem($vid, $title, $pageUrl, $playUrl)
    {
        return [
            'uuid' => 'iqiyi:'.$vid,
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
