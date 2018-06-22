<?php

namespace AppBundle\Component\MediaParser\AlbumParser;

use AppBundle\Component\MediaParser\ParseException;

class NeteaseOpenCourseAlbumParser extends AbstractAlbumParser
{
    private $patterns = array(
        'p1' => '/^(http|https):\/\/v\.163\.com\/special\/(.*?)[\/|(\.html)]$/s',
    );

    public function parse($url)
    {
        $response = $this->fetchUrl($url);
        if (200 != $response['code']) {
            throw new ParseException(array('获取网易公开课专辑(%url%)页面内容失败', array('%url%' => $url)));
        }

        $album = array();
        $album['id'] = $this->parseId($url);
        $album['uuid'] = 'NeteaseOpenCourseAlbum:'.$album['id'];

        $internationalAlbum = $this->parseInternationalAlbum($response['content']);
        if ($internationalAlbum) {
            return array_merge($album, $internationalAlbum);
        }

        $chineseAlbum = $this->parseChineseAlbum($response['content']);
        if ($chineseAlbum) {
            return array_merge($album, $chineseAlbum);
        }

        throw new ParseException('解析网易公开课专辑信息失败');
    }

    private function parseInternationalAlbum($content)
    {
        $matched = preg_match(iconv('utf-8', 'gb2312', '/class="m-cintro.*?<img\ssrc="(.*?)".*?\<h2\>(.*?)<\/h2>.*?本课程共(\d+).*?<p>课程介绍<\/p>.*?<p>(.*?)<\/p>/s'), $content, $matches);
        if (empty($matched)) {
            return null;
        }

        $album = array();

        $album['title'] = iconv('gb2312', 'utf-8', $matches[2]);
        $album['picture'] = $matches[1];
        $album['number'] = $matches[3];
        $album['summary'] = iconv('gb2312', 'utf-8', $matches[4]);
        $album['items'] = $this->parseInternationalItems($content);

        return $album;
    }

    private function parseChineseAlbum($content)
    {
        $album = array();

        $matched = preg_match('/<title>(.*?)<\/title>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('解析网易公开课专辑标题失败');
        }
        $album['title'] = iconv('gb2312', 'utf-8', trim(substr($matches[1], 0, strpos($matches[1], '_'))));

        $matched = preg_match(iconv('utf-8', 'gb2312', '/<h2>课程介绍<\/h2>.*?<span\sclass\=\"cdblan\">(.*?)<\/span>/s'), $content, $matches);
        if (empty($matched)) {
            throw new ParseException('解析网易公开课专辑摘要失败！');
        }
        $album['summary'] = iconv('gb2312', 'utf-8', trim($matches[1]));
        $album['picture'] = '';
        $album['items'] = $this->parseChineseItems($content);
        $album['number'] = count($album['items']);

        return $album;
    }

    private function parseId($url)
    {
        $matched = preg_match($this->patterns['p1'], $url, $matches);
        if (empty($matched)) {
            throw new ParseException('获取网易公开课专辑ID失败');
        }

        return $matches[1];
    }

    private function parseInternationalItems($content)
    {
        $items = array();

        $matched = preg_match('/<table.*?id="list2"(.*?)<\/table>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取网易公开课视频条目信息失败');
        }

        $matched = preg_match_all('/class="u-ctitle">.*?<a\shref="(.*?)">(.*?)<\/a>/s', $matches[1], $matches, PREG_SET_ORDER);
        if (empty($matched)) {
            throw new ParseException('获取网易公开课视频条目信息失败');
        }

        foreach ($matches as $match) {
            $items[] = array(
                'id' => substr($match[1], strlen('http://v.163.com/movie/'), -5),
                'title' => iconv('gb2312', 'utf-8', $match[2]),
                'url' => $match[1],
            );
        }

        return $items;
    }

    private function parseChineseItems($content)
    {
        $items = array();

        $matched = preg_match('/id="lession-list">(.*?)<\/ul>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取网易公开课视频(中国)条目信息失败');
        }

        $matched = preg_match_all('/<h3>\s*<a\s+href="(.*?)".*?>(.*?)<\/a>/s', $matches[1], $matches, PREG_SET_ORDER);
        if (empty($matched)) {
            throw new ParseException('获取网易公开课视频(中国)条目信息失败!');
        }

        foreach ($matches as $match) {
            $items[] = array(
                'id' => substr($match[1], strlen('http://v.163.com/movie/'), -5),
                'title' => iconv('gb2312', 'utf-8', $match[2]),
                'url' => $match[1],
            );
        }

        return $items;
    }

    public function detect($url)
    {
        return (bool) preg_match($this->patterns['p1'], $url);
    }
}
