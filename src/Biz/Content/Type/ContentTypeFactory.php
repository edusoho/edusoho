<?php

namespace Biz\Content\Type;

class ContentTypeFactory
{
    /**
     * @param $alias
     *
     * @return ContentType
     */
    public static function create($alias)
    {
        $alias = ucfirst($alias);
        $class = __NAMESPACE__."\\{$alias}ContentType";

        return new $class();
    }
}
