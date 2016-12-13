<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class TagUtil
{
    public static function buildTags($ownerType, $ownerId)
    {
        $res['tags'] = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => $ownerType,
            'ownerId'   => $ownerId
        ));

        return implode(',', ArrayToolkit::column($res['tags'], 'name'));
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}