<?php
namespace Topxia\Service\File\FireWall;

class FireWallFactory
{
    public static function create($targetType)
    {
        if (empty($targetType)) {
            throw new \InvalidArgumentException("Resource  targetType  argument missing.");
        }
        $targetTypes = explode('.', $targetType);
        $class       = __NAMESPACE__."\\".ucfirst($targetTypes[0]).'FileFireWall';
        return new $class();
    }
}
