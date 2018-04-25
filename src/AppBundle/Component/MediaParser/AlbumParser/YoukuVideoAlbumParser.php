<?php

namespace AppBundle\Component\MediaParser\AlbumParser;

use AppBundle\Component\MediaParser\ParseException;

class YoukuVideoAlbumParser extends AbstractAlbumParser
{
    private $patterns = array(
        'p1' => '/^http:\/\/www\.youku\.com\/playlist_show\/id_(\d+)/s',
    );

    public function parse($url)
    {
        $response = $this->fetchUrl($url);
        if (200 != $response['code']) {
            throw new ParseException(array('获取优酷视频专辑(%url%)页面内容失败！', array('%url%' => $url)));
        }

        $album = array();
        $album['id'] = $this->parseId($url);
        $album['uuid'] = 'YoukuVideoAlbum:'.$album['id'];
        $album['title'] = $this->parseTitle($response['content']);
        $album['number'] = $this->parseNumber($response['content']);
        $album['items'] = $this->parseItems($album);

        return $album;
    }

    private function parseId($url)
    {
        $matched = preg_match($this->patterns['p1'], $url, $matches);
        if (empty($matched)) {
            throw new ParseException('获取优酷视频专辑ID失败');
        }

        return $matches[1];
    }

    private function parseTitle($content)
    {
        $matched = preg_match('/\<h1\sclass="title">.*?class="name">(.*?)<\/span>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取优酷视频专辑标题失败');
        }

        return $matches[1];
    }

    private function parseNumber($content)
    {
        $matched = preg_match('/视频:\s<span\sclass="num">(\d+)<\/span>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取优酷视频专辑视频数量失败');
        }

        return $matches[1];
    }

    private function parseItems($album)
    {
        $items = array();
        foreach (range(1, ceil($album['number'] / 50)) as $page) {
            $url = "http://v.youku.com/v_vpvideoplaylistv5?pl=50&f={$album['id']}&pn={$page}";
            $response = $this->fetchUrl($url);
            if (200 != $response['code']) {
                throw new ParseException('获取优酷视频专辑视频条目失败');
            }

            $matched = preg_match_all('/id="item_(.*?)".*?_src="(.*?)".*?class="l_title">(.*?)<\/span>.*?class="l_time">.*?class="num">(.*?)<\/em>/s', $response['content'], $matches, PREG_SET_ORDER);
            if (empty($matched)) {
                throw new ParseException('解析优酷视频专辑视频条目失败');
            }

            foreach ($matches as $match) {
                $items[] = array(
                    'id' => $match[1],
                    'uuid' => 'YoukuVideo:'.$match[1],
                    'url' => "http://v.youku.com/v_show/id_{$match[1]}.html",
                    'picture' => $match[2],
                    'title' => $match[3],
                    'length' => $match[4],
                );
            }
        }

        return $items;
    }

    public function detect($url)
    {
        return (bool) preg_match('/^http:\/\/www\.youku\.com\/playlist_show\/id_(\d+)/s', $url);
    }
}
