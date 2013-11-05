<?php
namespace Topxia\WebBundle\DataDict;

class CityDict implements DataDictInterface
{
    public function getDict() {
        return array(
            '北京' => '北京',
            '上海' => '上海',
        );
    }

    public function getRenderedDict() {
        return $this->getDict();
    }

    public function getGroupedDict() {
        return $this->getDict();
    }
}