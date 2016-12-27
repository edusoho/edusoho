<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class TagUtil
{
    public static function buildTags($ownerType, $ownerId)
    {
        return self::getTagService()->findTagsByOwner(array(
            'ownerType' => $ownerType,
            'ownerId'   => $ownerId
        ));
    }

    protected static function getTagService()
    {
        return self::getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();   
    }
}