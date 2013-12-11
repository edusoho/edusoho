<?php
namespace Topxia\Component\MediaParser\ItemParser;

class NeteaseOpenCourseItemParser extends AbstractItemParser
{

	public function parse($url)
	{
		preg_match('/^http\:\/\/v\.163\.com\/movie\/(.+?)\.html/s', $url, $matches);

		$item['id'] = $matches[1];
		$item['uuid'] = 'NeteaseOpenCourse:' . $item['id'];
		$item['type'] = 'video';
		$item['source'] = 'NeteaseOpenCourse';
        $response = $this->fetchUrl($url);

        if ($response['code'] != 200) {
            throw $this->createParseException('获取网易公开课视频信息失败！');
        }

        $matchedResult = $this->getMatchedResultByFilterUrl($response['content'], $matches);

		$item['id'] = $matchedResult['id'];
		$item['title'] = $item['name'] = $matchedResult['title'];
		$item['url'] = $item['page'] = 'http://v.163.com/movie/' . $matches[1] . '.html';
		$item['pictures'] = array(
            array('url' => $matchedResult['image'])
        );

		$item['files'] = array(
            array('type' => 'swf', 'url' => $matchedResult['src']),
            array('type' => 'mp4', 'url' => str_replace("m3u8","mp4",$matchedResult['appsrc'])),
            array('type' => 'm3u8', 'url' => $matchedResult['appsrc'])
        );

		return $item;
	}

    public function detect($url)
    {
        return !! preg_match('/^http\:\/\/v\.163\.com\/movie\/.+?\.html/s', $url);
    }

    /**
	*  returned keys: id, number, image, title, appsrc, src, jsUrl
	**/
	private function  getMatchedResultByFilterUrl($content, $matches)
	{
		$pregMatchStringOnce = "#_oc.getCurrentMovie\s=\sfunction\(\)\s\{[\S\s]*?\};#";
		$matched = preg_match($pregMatchStringOnce, $content, $matches);

		$pregMatchStringTwice = "#return\s\{[\S\s]*?\};#";
		$matched = preg_match($pregMatchStringTwice, $matches[0], $matches);

		if (!$matched) {
			throw $this->createParseException('解析网易公开课视频地址失败！');
		}

		$arrayStringToReplace = array('return','{','}',' ','\t','\r','\n','http://', "'", '+');
		$str = str_replace($arrayStringToReplace, '', $matches[0]); 

		$matchedResult = array();
		$arrayStringToReplace = explode(',', $str);
		foreach ($arrayStringToReplace as $value) {
			$x = explode(':', $value);
			$x[0] = trim($x[0]);
			if ($x[0] == 'title') {
				$matchedResult[$x[0]] = iconv('gbk', 'utf-8', $x[1]);
				continue;
			}
			if (in_array($x[0], array('appsrc', 'src', 'jsUrl'))){
				$matchedResult[$x[0]] = "http://" . $x[1];
				continue;
			}
			if ($x[0] == 'image') {
				$matchedResult[$x[0]] = 'http://' . $x[1];
				continue;
			}
			$matchedResult[$x[0]] = $x[1];
		}

		return $matchedResult;
	}
}