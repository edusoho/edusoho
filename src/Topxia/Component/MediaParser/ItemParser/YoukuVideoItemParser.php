<?php
namespace Topxia\Component\MediaParser\ItemParser;

class YoukuVideoItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http\:\/\/v\.youku\.com\/v_show\/id_(.+?).html/s',
        'p2' => '/http:\/\/player\.youku\.com\/player\.php.*?\/sid\/(.+?)\/v.swf/s',
    );

	public function parse($url)
	{
        $matched = preg_match($this->patterns['p2'], $url, $matches);
        if ($matched) {
            $url = "http://v.youku.com/v_show/id_{$matches[1]}.html";
        }

        $matched = preg_match('/\/id_(.+?).html/s', $url, $matches);
        if (empty($matched)) {
            throw $this->createParseException("优酷视频地址不正确！");
        }

        $videoId = $matches[1];

        $response = $this->fetchUrl($url);
        if ($response['code'] != 200) {
            throw $this->createParseException('获取优酷视频页面信息失败！');
        }

        $item = array();
        $item['type'] = 'video';
        $item['source'] = 'youku';
        $item['uuid'] = 'youku:' . $videoId;

        $matched = preg_match('/id="s_baidu1"\s+href="(.*?)"/s', $response['content'], $matches);
        if (empty($matched)) {
            throw $this->createParseException('解析优酷视频页面信息失败!');
        }

        parse_str(parse_url($matches[1], PHP_URL_QUERY), $query);
        if (empty($query) or empty($query['title'])) {
            throw $this->createParseException('解析优酷视频页面信息失败!!');
        }

        $item['name'] = iconv('gbk', 'utf-8', $query['title']);
        $item['summary'] = empty($query['desc']) ? '' : iconv('gbk', 'utf-8', $query['desc']);
        $item['page'] = "http://v.youku.com/v_show/id_{$videoId}.html";
        $item['pictures'] = empty($query['pic']) ? array() : array('url' => $query['pic']);
        $item['files'] = array(
            array('url' => "http://player.youku.com/player.php/sid/{$videoId}/v.swf", 'type' => 'swf'),
        );

        return $item;
	}

    public function detect($url)
    {
        $matched = preg_match($this->patterns['p1'], $url);
        if ($matched) {
            return true;
        }
        $matched = preg_match($this->patterns['p2'], $url);
        if ($matched) {
            return true;
        }
    }


}