<?php

namespace Biz\Common;

class EsKeywordFilter
{
    public function add($keyword)
    {
        return true;
    }

    public function remove($keyword)
    {
        return true;
    }

    public function update($keyword)
    {
    }

    public function scan($str)
    {
        return $str;
    }
}
