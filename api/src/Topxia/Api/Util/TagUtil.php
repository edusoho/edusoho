<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class TagUtil
{
    public static function buildTags($ownerType, $ownerId)
    {
        $tags = self::getTagService()->findTagsByOwner(array(
            'ownerType' => $ownerType,
            'ownerId'   => $ownerId
        ));

        $newTags = array();
        foreach ($tags as $tag) {
            $newTags[] = array(
                'id'   => $tag['id'],
                'name' => $tag['name'],
            );
        }

        return $newTags;
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