<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ArrayToolkit;

class TagUtil
{
    public static function buildTags($ownerType, $ownerId)
    {
        $originalTags = self::getTagService()->findTagsByOwner(array(
            'ownerType' => $ownerType,
            'ownerId'   => $ownerId
        ));

        $formalTags = array();
        foreach ($originalTags as $tag) {
            $formalTags[] = array(
                'id'   => $tag['id'],
                'name' => $tag['name'],
            );
        }

        return $formalTags;
    }

    protected static function getTagService()
    {
        return self::getServiceKernel()->createService('Taxonomy:TagService');
    }

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();   
    }
}