<?php
namespace Topxia\WebBundle\DataDict;

class ActTypeDict implements DataDictInterface
{
    public function getDict() {
        return array(
            '公开课' => '公开课',
            '培训' => '培训',
        );
    }

    public function getRenderedDict() {
        return $this->getDict();
    }

    public function getGroupedDict() {
        return $this->getDict();
    }
}