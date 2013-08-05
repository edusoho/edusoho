<?php
namespace Topxia\Service\Content\Type;

class ContentTypeFactory
{
	public static function create($alias)
	{
		$alias = ucfirst($alias);
        $class = __NAMESPACE__ . "\\{$alias}ContentType";
        return new $class();
	}
}