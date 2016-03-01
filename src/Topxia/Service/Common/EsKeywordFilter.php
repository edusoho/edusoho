<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\BaseService;

class EsKeywordFilter extends BaseService
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
