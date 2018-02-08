<?php

namespace AppBundle\Component\MediaParser\AlbumParser;

use AppBundle\Component\MediaParser\ParseException;

class QQVideoAlbumParser extends AbstractAlbumParser
{
    public function parse($url)
    {
        $response = $this->fetchUrl($url);
        if (200 != $response['code']) {
            throw new ParseException(array('获取QQ视频专辑(%url%)页面内容失败！', array('%url%' => $url)));
        }

        $list = array();
        $list = $this->parseInfos($response['content']);
        $list['items'] = $this->parseItems($response['content'], $list);

        return $list;
    }

    private function parseInfos($content)
    {
        $matched = preg_match('/COVER_INFO.*?title\s*:"(.*?)",\s*id\s*:"(.*?)"/s', $content, $matches);
        if (empty($matched)) {
            throw new ParseException('解析QQ视频专辑基础信息失败！');
        }

        return array(
            'id' => $matches[2],
            'uuid' => 'QQVideoAlbum:'.$matches[2],
            'title' => $matches[1],
            'summary' => '',
            'url' => 'http://v.qq.com/cover/'.substr($matches[2], 0, 1).'/'.$matches[2].'.html',
        );
    }

    private function parseItems($content, $list)
    {
        $matched = preg_match('/id="mod_videolist">.*?class="mod_cont">.*?\<ul\>(.*?)\<\/ul\>/s', $content, $matches);

        if (empty($matched)) {
            throw new ParseException('定位QQ视频专辑页面列表区域失败！');
        }

        $matched = preg_match_all('/<li\s*id="li_(.*?)".*?>/s', $matches[1], $matches);
        if (empty($matched)) {
            throw new ParseException('解析QQ视频专辑列表失败！');
        }

        $items = array();
        foreach ($matches[1] as $id) {
            $items[] = array(
                'id' => $id,
                'url' => $list['url'].'?vid='.$id,
            );
        }

        return $items;
    }

    public function detect($url)
    {
        return (bool) preg_match('/^http\:\/\/v\.qq\.com\/cover\//s', $url);
    }
}
