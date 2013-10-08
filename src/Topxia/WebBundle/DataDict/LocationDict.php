<?php
namespace Topxia\WebBundle\DataDict;

class LocationDict implements DataDictInterface
{
    public function getDict() {
        return array(
            '0' => '北京',
            '1' => '上海',
        );
    }

    public function getRenderedDict() {
        return $this->getDict();
    }

    public function getGroupedDict() {
        return $this->getDict();
    }
}