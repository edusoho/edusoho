<?php
namespace Topxia\Service\Article\Type;

class ArticleTypeFactory
{
	public static function create($alias)
	{
		$alias = ucfirst($alias);
        $class = __NAMESPACE__ . "\\{$alias}ArticleType";
        return new $class();
	}
}