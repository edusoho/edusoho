<?php
namespace Topxia\WebBundle\DataDict;

class GenderDict implements DataDictInterface
{
    public function getDict() {
        return array(
            'male' => '男',
            'female' => '女',
        );
    }

    public function getRenderedDict() {
        return $this->getDict();
    }

    public function getGroupedDict() {
        return $this->getDict();
    }
}