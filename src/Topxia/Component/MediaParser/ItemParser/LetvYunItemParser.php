<?php
namespace Topxia\Component\MediaParser\ItemParser;

class LetvYunItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http:\/\/yuntv\.letv\.com/s'
    );

    public function parse($url)
    {
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
