<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class YoukuVideoItemParser extends AbstractItemParser
{
    private $patterns = [
        'p1' => '/^http[s]{0,1}\:\/\/v\.youku\.com\/v_show\/id_(.+?).html/s',
        'p2' => '/http[s]{0,1}:\/\/player\.youku\.com\/player\.php.*?\/sid\/(.+?)\/v.swf/s',
        'p3' => '/http[s]{0,1}:\/\/player\.youku\.com\/embed\/(.*)/s',
    ];

    protected function parseForWebUrl($item, $url)
    {
        $matched = preg_match($this->patterns['p1'], $url, $matches) || preg_match($this->patterns['p3'], $url, $matches);
        if ($matched) {
            $url = "http://v.youku.com/v_show/id_{$matches[1]}.html";
        }
        $matched = preg_match('/\/id_(.+?).html/s', $url, $matches);
        if (empty($matched)) {
            throw ParserException::PARSED_FAILED_YOUKU();
        }
        $videoId = $matches[1];
        $response = $this->fetchUrl($url);
        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_YOUKU();
        }
        $response['content'] = htmlspecialchars_decode(urldecode($response['content']));
        //匹配标题
        $titlePattern = '|<meta property="og:title" content="(.*?)"/>|s';
        preg_match($titlePattern, $response['content'], $title);
        //匹配描述
        $summaryPattern = '|"desc":"(.*?)"|s';
        preg_match($summaryPattern, $response['content'], $summary);
        //匹配封面
        $picturesPattern = '|<meta property="og:image" content="(.*?)"/>|s';
        preg_match($picturesPattern, $response['content'], $pictures);

        $item['name'] = $title[1] ?? 'YouKu'.$videoId;
        $item['summary'] = $summary[1] ?? '';
        $item['pictures'] = $pictures[1] ?? '';
        $item['uuid'] = 'youku:'.$videoId;
        $item['page'] = "http://v.youku.com/v_show/id_{$videoId}.html";
        $item['files'] = [
            ['url' => "https://player.youku.com/embed/{$videoId}", 'type' => 'mp4'],
        ];
        $item['content'] = $response['content'];

        return $item;
    }

    protected function getUrlPrefixes()
    {
        return ['v.youku.com', 'player.youku.com'];
    }

    protected function convertMediaUri($video)
    {
        $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $video['mediaUri'], $matches);
        if ($matched) {
            $video['mediaUri'] = "//player.youku.com/embed/{$matches[1]}";
        }

        return $video;
    }

    protected function getDefaultParsedInfo()
    {
        return [
            'source' => 'youku',
            'name' => '优酷视频',
        ];
    }
}
