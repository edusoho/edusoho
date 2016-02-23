<?php
namespace Topxia\Component\MediaParser\ItemParser;

class LetvYunItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http:\/\/yuntv\.letv\.com\/bcloud\.html/s'
    );

    public function parse($url)
    {
        $response = $this->fetchUrl($url);

        if ($response['code'] != 200) {
            throw $this->createParseException('获取乐视视频信息失败！');
        }

        $url  = str_replace('width=640&height=360', 'width=100%&height=100%', $url);
        $item = array(
            "type"     => "video",
            "source"   => "letv",
            "uuid"     => "",
            "name"     => "letv".md5(time().mt_rand(0, 1000)),
            "summary"  => "",
            "page"     => "",
            "pictures" => array(
                "url" => ""),
            "files"    => array(
                array(
                    "url"  => $url,
                    "type" => "swf"))
        );

        return $item;
    }

    public function detect($url)
    {
        return !!preg_match($this->patterns['p1'], $url);
    }
}
