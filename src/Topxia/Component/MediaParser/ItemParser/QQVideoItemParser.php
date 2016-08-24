<?php

namespace Topxia\Component\MediaParser\ItemParser;

class QQVideoItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http\:\/\/v\.qq\.com\/cover\//s',
        'p2' => '/^http\:\/\/v\.qq\.com\/boke\/page\//s',
        'p3' => '/^http\:\/\/v\.qq\.com\/page\//s',
        'p4' => '/^http\:\/\/v\.qq\.com\/x\/page\//s',
        'p5' => '/^http\:\/\/v\.qq\.com\/x\/cover\//s'
    );

    public function parse($url)
    {
        $matched  = preg_match('/vid=(\w+)/s', $url, $matches);
        $response = array();

        if (!empty($matched)) {
            $vid = $matches[1];
        } else {
            $response = $this->fetchUrl($url);
            if ($response['code'] != 200) {
                throw $this->createParseException('获取QQ视频页面信息失败！');
            }

            $matched = preg_match('/VIDEO_INFO.*?[\"]?vid[\"]?\s*:\s*"(\w+?)"/s', $response['content'], $matches);
            if (empty($matched)) {
                throw $this->createParseException("解析QQ视频ID失败！");
            }

            $vid = $matches[1];
        }

        $matched = $this->getUrlMatched($url);

        if ($matched) {
            $responseInfo = $response ? $response : array();
            $videoUrl     = 'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid='.$vid;

            $response = $this->fetchUrl($videoUrl);
            if ($response['code'] != 200) {
                throw $this->createParseException('获取QQ视频信息失败！.');
            }

            $matched = preg_match('/{.*}/s', $response['content'], $matches);
            if (empty($matched)) {
                throw $this->createParseException('解析QQ视频信息失败！..');
            }

            $video = json_decode($matches[0], true) ?: array();
            if (!empty($video) && !empty($video['video'])) {
                $video = $video['video'];
                $title = $video['title'];
                //throw $this->createParseException('解析QQ视频信息失败！...');
            } else {
                $video = array();
                $title = $url;
                if ($responseInfo) {
                    $title = $this->getVideoTitle($responseInfo);
                }
            }

            $summary  = $video ? $video['desc'] : '';
            $duration = $video ? $video['duration'] : '';
            $pageUrl  = $video ? 'http://v.qq.com/cover/'.substr($video['cover'], 0, 1)."/{$video['cover']}.html?vid={$vid}" : $url;

            $item = $this->getItem($vid, $title, $summary, $duration, $pageUrl);
        } else {
            $title = $this->getVideoTitle($response);
            if (empty($title)) {
                throw $this->createParseException("解析QQ视频ID失败！....");
            }
        }

        return $this->getItem($vid, $title, '', '', $url);
    }

    protected function getItem($vid, $title, $summary, $duration, $pageUrl)
    {
        $item = array(
            'type'     => 'video',
            'source'   => 'qqvideo',
            'uuid'     => 'qqvideo:'.$vid,
            'name'     => $title,
            'summary'  => $summary,
            'duration' => $duration,
            'page'     => $pageUrl,
            'pictures' => array(
                array('url' => "http://shp.qpic.cn/qqvideo/0/{$vid}/400")
            ),
            'files'    => array(
                array('type' => 'swf', 'url' => "http://static.video.qq.com/TPout.swf?vid={$vid}&auto=1"),
                array('type' => 'mp4', 'url' => "http://video.store.qq.com/{$vid}.mp4")
            )
        );

        return $item;
    }

    public function detect($url)
    {
        $matched = $this->getUrlMatched($url);

        if ($matched) {
            return true;
        }

        return false;
    }

    protected function getUrlMatched($url)
    {
        foreach ($this->patterns as $key => $pattern) {
            $matched = preg_match($pattern, $url);
            if ($matched) {
                return $matched;
            }
        }

        return false;
    }

    protected function getVideoTitle($responseInfo)
    {
        $matched = preg_match('/VIDEO_INFO.*?[\"]?title[\"]?\s*:\s*"(.*?)"/s', $responseInfo['content'], $matches);

        if (empty($matched)) {
            return '';
        }

        return $matches[1];
    }
}
