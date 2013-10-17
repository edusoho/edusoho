<?php
namespace Topxia\Common;

class StringToolkit
{
    public static function template($string, array $variables)
    {
        if (empty($variables)) {
            return $string;
        }

        $search = array_keys($variables);
        array_walk($search, function(&$item){
        	$item = '{{' . $item . '}}';
        });

        $replace = array_values($variables);

        return str_replace($search, $replace, $string);
    }
}