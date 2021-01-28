<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class QQVideoItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http\:\/\/v\.qq\.com\/cover\//s',
        'p2' => '/^http\:\/\/v\.qq\.com\/boke\/page\//s',
        'p3' => '/^http\:\/\/v\.qq\.com\/page\//s',
        'p4' => '/^http\:\/\/v\.qq\.com\/x\/page\//s',
        'p5' => '/^http\:\/\/v\.qq\.com\/x\/cover\//s',
        'p6' => '/^https\:\/\/v\.qq\.com\/cover\//s',
        'p7' => '/^https\:\/\/v\.qq\.com\/boke\/page\//s',
        'p8' => '/^https\:\/\/v\.qq\.com\/page\//s',
        'p9' => '/^https\:\/\/v\.qq\.com\/x\/page\//s',
        'p10' => '/^https\:\/\/v\.qq\.com\/x\/cover\//s',
    );

    protected function parseForWebUrl($item, $url)
    {
        $matched = preg_match('/vid=(\w+)/s', $url, $matches);
        $response = array();

        if (!empty($matched)) {
            $vid = $matches[1];
        } else {
            $response = $this->fetchUrl($url);
            if (200 != $response['code']) {
                throw ParserException::PARSED_FAILED_QQ();
            }
            $matched = preg_match('/VIDEO_INFO.*?[\"]?vid[\"]?\s*:\s*"(\w+?)"/s', $response['content'], $matches);

            if (empty($matched)) {
                throw ParserException::PARSED_FAILED_QQ();
            }

            $vid = $matches[1];
        }

        $matched = $this->getUrlMatched($url);

        if ($matched) {
            $responseInfo = $response ? $response : array();
            $videoUrl = 'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid='.$vid;

            $response = $this->fetchUrl($videoUrl);
            if (200 != $response['code']) {
                throw ParserException::PARSED_FAILED_QQ();
            }

            $matched = preg_match('/{.*}/s', $response['content'], $matches);

            if (empty($matched)) {
                throw ParserException::PARSED_FAILED_QQ();
            }

            $video = json_decode($matches[0], true) ?: array();

            if (!empty($video) && !empty($video['video'])) {
                $video = $video['video'];
                $title = $video['title'];
            } else {
                $video = array();
                $title = $url;

                if ($responseInfo) {
                    $title = $this->getVideoTitle($responseInfo);
                }
            }

            $summary = $video ? $video['desc'] : '';
            $duration = $video ? $video['duration'] : '';
            $pageUrl = $video ? 'http://v.qq.com/cover/'.substr($video['cover'], 0, 1)."/{$video['cover']}.html?vid={$vid}" : $url;

            $parsedInfo = $this->getItem($vid, $title, $summary, $duration, $pageUrl);
        } else {
            $title = $this->getVideoTitle($response);

            if (empty($title)) {
                throw ParserException::PARSED_FAILED_QQ();
            }
        }

        $parsedInfo = $this->getItem($vid, $title, '', '', $url);

        return array_merge($item, $parsedInfo);
    }

    protected function getUrlPrefixes()
    {
        return array(
            'v.qq.com',
        );
    }

    protected function convertMediaUri($video)
    {
        $video['mediaUri'] = str_replace('static.video.qq.com', 'imgcache.qq.com/tencentvideo_v1/playerv3', $video['mediaUri']);
        $matched = preg_match('/^(http|https):(\S*)/s', $video['mediaUri'], $matches);
        if ($matched) {
            $video['mediaUri'] = $matches[2];
        }

        return $video;
    }

    protected function getDefaultParsedInfo()
    {
        return array(
            'source' => 'qqvideo',
            'name' => 'QQ视频',
        );
    }

    private function getUrlMatched($url)
    {
        foreach ($this->patterns as $key => $pattern) {
            $matched = preg_match($pattern, $url);

            if ($matched) {
                return $matched;
            }
        }

        return false;
    }

    private function getVideoTitle($responseInfo)
    {
        $matched = preg_match('/VIDEO_INFO.*?[\"]?title[\"]?\s*:\s*"(.*?)"/s', $responseInfo['content'], $matches);

        if (empty($matched)) {
            return '';
        }

        return $matches[1];
    }

    private function getItem($vid, $title, $summary, $duration, $pageUrl)
    {
        $item = array(
            'uuid' => 'qqvideo:'.$vid,
            'name' => $title,
            'summary' => $summary,
            'duration' => $duration,
            'page' => $pageUrl,
            'pictures' => array(
                array('url' => "http://shp.qpic.cn/qqvideo/0/{$vid}/400"),
            ),
            'files' => array(
                array('type' => 'swf', 'url' => "//imgcache.qq.com/tencentvideo_v1/playerv3/TPout.swf?vid={$vid}&auto=1"),
                array('type' => 'mp4', 'url' => "//video.store.qq.com/{$vid}.mp4"),
            ),
        );

        return $item;
    }
}
