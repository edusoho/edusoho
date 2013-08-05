<?php
namespace Topxia\Component\MediaParser\ItemParser;

class NeteaseOpenCourseItemParser extends AbstractItemParser
{

	public function parse($url)
	{
		preg_match('/^http\:\/\/v\.163\.com\/movie\/(.+?)\.html/s', $url, $matches);

		$videoId = $matches[1];

		$item['type'] = 'video';
		$item['source'] = 'neteaseopencourse';
		$item['uuid'] = 'NeteaseOpenCourse:' . $videoId;

        $response = $this->fetchUrl($url);
        if ($response['code'] != 200) {
            throw $this->createParseException('获取网易公开课视频信息失败！');
        }

        $matched = preg_match('/class=\'thdTit\'>(.*?)<\/span>/s', $response['content'], $matches);
		if (!$matched) {
			throw $this->createParseException('解析网易公开课视频标题失败！');
		}

		$item['name'] = iconv('gbk', 'utf-8', $matches[1]);
		$item['page'] = 'http://v.163.com/movie/' . $videoId . '.html';

		$matched = preg_match('/appsrc:\s*\'(.*?)\',\s*src:\s*\'(.*?)\'/s', $response['content'], $matches);
		if (!$matched) {
			throw $this->createParseException('解析网易公开课视频地址失败！');
		}

		$item['files'] = array(
			array('type' => 'swf', 'url' => $matches[2]),
			array('type' => 'm3u8', 'url' => $matches[1]),
		);

		return $item;
	}

    public function detect($url)
    {
        return !! preg_match('/^http\:\/\/v\.163\.com\/movie\/.+?\.html/s', $url);
    }
}