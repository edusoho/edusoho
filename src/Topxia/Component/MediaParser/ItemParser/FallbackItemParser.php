<?php
namespace Topxia\Component\MediaParser\ItemParser;

class FallbackItemParser extends AbstractItemParser
{

    private $patterns = array(
        'p1' => '/^https?:\/\/.+?[(\.mp4)|(\.swf)]/s',
    );

	public function parse($url)
	{
        $item = array();

        $item['id'] = md5($url);
        $item['uuid'] = 'Fallback:' . $item['id'];
        $item['type'] = 'video';
        $item['title'] = '';

        $item['url'] = $url;

        if (stripos($url, '.mp4') > 0) {
            $item['mp4_url'] = $url;
        } else {
            $item['swf_url'] = $url;
        }

        return $item;
	}

    public function detect($url)
    {
        return !! preg_match($this->patterns['p1'], $url);
    }

}