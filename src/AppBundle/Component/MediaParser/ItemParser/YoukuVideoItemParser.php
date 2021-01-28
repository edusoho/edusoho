<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class YoukuVideoItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http[s]{0,1}\:\/\/v\.youku\.com\/v_show\/id_(.+?).html/s',
        'p2' => '/http[s]{0,1}:\/\/player\.youku\.com\/player\.php.*?\/sid\/(.+?)\/v.swf/s',
    );

    protected function parseForWebUrl($item, $url)
    {
        $matched = preg_match($this->patterns['p2'], $url, $matches);
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
        $matched = preg_match('/id=[\\\\]{0,1}"s_baidu1[\\\\]{0,1}"\s+href=[\\\\]{0,1}"(.*?)[\\\\]{0,1}"/s', $response['content'], $matches);
        if (empty($matched)) {
            throw ParserException::PARSED_FAILED_YOUKU();
        }
        $queryString = substr($matches[1], strpos($matches[1], '?') + 1);
        $queryString = substr($queryString, 0, strpos($queryString, '#') ?: strlen($queryString));
        parse_str($queryString, $query);

        if (empty($query) || empty($query['title'])) {
            throw ParserException::PARSED_FAILED_YOUKU();
        }

        $item['uuid'] = 'youku:'.$videoId;
        $item['name'] = $query['title'];
        $item['summary'] = empty($query['desc']) ? '' : $query['desc'];
        $item['page'] = "http://v.youku.com/v_show/id_{$videoId}.html";
        $item['pictures'] = empty($query['pic']) ? array() : array('url' => $query['pic']);
        $item['files'] = array(
            array('url' => "//player.youku.com/player.php/sid/{$videoId}/v.swf", 'type' => 'swf'),
        );

        return $item;
    }

    protected function getUrlPrefixes()
    {
        return array('v.youku.com', 'player.youku.com');
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
        return array(
            'source' => 'youku',
            'name' => '优酷视频',
        );
    }
}
