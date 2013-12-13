<?php
namespace Topxia\Component\MediaParser\ItemParser;

class NeteaseOpenCourseItemParser extends AbstractItemParser
{

	public function parse($url)
	{
        $response = $this->fetchUrl($url);

        if ($response['code'] != 200) {
            throw $this->createParseException('获取网易公开课视频信息失败！');
        }

        $matched = preg_match('/getCurrentMovie.*?id\s*:\s*\'(.*?)\'.*?image\s*:\s*\'(.*?)\'\s*\+\s*\'(.*?)\'\s*\+\s*\'(.*?)\'.*?title\s*:\s*\'(.*?)\'.*?appsrc\s*:\s*\'(.*?)\'.*?src\s*:\s*\'(.*?)\'/s', $response['content'], $matches);
        if (!$matched) {
            throw $this->createParseException("解析土豆视频信息失败");
        }

        $item['id'] = $matches[1];
        $item['uuid'] = 'NeteaseOpenCourse:' . $item['id'];
        $item['type'] = 'video';
        $item['source'] = 'NeteaseOpenCourse';
        $item['name'] = iconv('gbk', 'utf-8', $matches[5]);
		$item['page'] = $url;
		$item['pictures'] = array(
            array('url' => $matches[2] . $matches[3] . $matches[4]),
        );

		$item['files'] = array(
            array('type' => 'swf', 'url' => $matches[7]),
            array('type' => 'mp4', 'url' => str_replace('.m3u8', '.mp4', $matches[6])),
            array('type' => 'm3u8', 'url' => $matches[6])
        );

		return $item;
	}

    public function detect($url)
    {
        return !! preg_match('/^http\:\/\/v\.163\.com\/movie\/.+?\.html/s', $url);
    }

}