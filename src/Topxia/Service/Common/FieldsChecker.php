<?php
namespace Topxia\Service\Common;

class FieldsChecker
{
	public static function checkFieldName($names)
    {
    	foreach ($names as $name) {
	        if (!ctype_alnum(str_replace('_', '', $name))) {
	            throw new \InvalidArgumentException('Field name is invalid.');
	        }
	    }

        return true;
    }
}