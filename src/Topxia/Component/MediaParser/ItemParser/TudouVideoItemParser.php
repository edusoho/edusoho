<?php
namespace Topxia\Component\MediaParser\ItemParser;

class TudouVideoItemParser extends AbstractItemParser
{
	private $patterns = array(
		'p1' => '/^http:\/\/www\.tudou\.com\/programs\/view\/.*/s',
	);

	public function parse($url)
	{
		$item = array();

		$response = $this->fetchUrl($url);
		if ($response['code'] != 200) {
            throw $this->createParseException("获取土豆视频({$url})页面内容失败！");
        }

        $matched = preg_match('/itemData\s*=\s*\{\s*iid:\s*(\d+).*?icode:\s*\'(.*?)\'.*?pic:\s*\'(.*?)\'.*?kw:\s*\'(.*?)\'/s', $response['content'], $matches);
        if (!$matched) {
            throw $this->createParseException("解析土豆视频信息失败");
        }

        $videoId =  $matches[2];

        $item['type'] = 'video';
        $item['source'] = 'tudou';
        $item['uuid'] = 'tudou:' . $videoId;

        $item['name'] = iconv('gbk', 'utf-8', $matches[4]);
        $item['page'] = "http://www.tudou.com/programs/view/{$videoId}/";
        $item['pictures'] = array(
            array('url' => $matches[3])
        );

        $item['files'] = array(
            array('type' => 'swf', 'url' => "http://www.tudou.com/v/{$videoId}/v.swf"),
            array('type' => 'mp4', 'url' => "http://vr.tudou.com/v3proxy/v2?it={$matches[1]}&st=52&pw="),
            array('type' => 'm3u8', 'url' => "http://vr.tudou.com/v2proxy/v2.m3u8?it={$matches[1]}&st=2&pw=")

        );

        return $item;
	}

    public function detect($url)
    {
        return !! preg_match($this->patterns['p1'], $url);
    }
}