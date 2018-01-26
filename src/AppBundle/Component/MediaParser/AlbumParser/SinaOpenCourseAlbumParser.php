<?php

namespace AppBundle\Component\MediaParser\AlbumParser;

use AppBundle\Component\MediaParser\ParseException;

class SinaOpenCourseAlbumParser extends AbstractAlbumParser
{
    private $patterns = array(
        'p1' => '/^http:\/\/open.sina.com.cn\/course\/id_(\d+)/s',
    );

    public function parse($url)
    {
        $response = $this->fetchUrl($url);
        if (200 != $response['code']) {
            throw new ParseException(array('获取新浪公开课专辑(%url%)页面内容失败！', array('%url%' => $url)));
        }

        $album = array();
        $album['id'] = $this->parseId($url);
        $album['uuid'] = 'SinaOpenCourseAlbum:'.$album['id'];
        $album['title'] = $this->parseTitle($response['content']);
        $album['summary'] = $this->parseSummary($response['content']);
        $album['items'] = $this->parseItems($url, $response['content']);

        return $album;
    }

    private function parseId($url)
    {
        $matched = preg_match($this->patterns['p1'], $url, $matches);
        if (empty($matched)) {
            throw new ParseException('获取新浪公开课专辑ID失败');
        }

        return 'course_'.$matches[1];
    }

    private function parseTitle($content)
    {
        $matched = preg_match('/<h2\sclass="fblue">\s*(.*?)\(\d+\)/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取新浪公开课专辑标题失败');
        }

        return $matches[1];
    }

    private function parseSummary($content)
    {
        $matched = preg_match('/<p\sclass="txt">(.*?)<\/p>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取新浪公开课专辑摘要失败');
        }

        return $matches[1];
    }

    private function parseItems($url, $content)
    {
        $items = array();

        $matched = preg_match('/<div\sclass="container2">\s*<ul>(.*?)<\/ul>/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('获取新浪公开课视频条目信息失败');
        }

        $matched = preg_match_all('/<li>.*?<img.*?alt="(.*?)".*?src="(.*?)"/s', $matches[1], $matches, PREG_SET_ORDER);
        if (empty($matched)) {
            throw new ParseException('获取新浪公开课视频条目信息失败');
        }

        foreach ($matches as $match) {
            $matched = preg_match('/<a\shref="(.*?)"/s', $match[0], $matchesInItem);
            $items[] = array(
                'url' => empty($matched) ? $url : $matchesInItem[1],
                'title' => $match[1],
                'picture' => $match[2],
            );
        }

        return $items;
    }

    public function detect($url)
    {
        return (bool) preg_match($this->patterns['p1'], $url);
    }
}
