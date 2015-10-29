<?php
namespace Topxia\Common;

class NameCutterTookit
{
  	public static function cutter($name, $length, $startNum, $endNum)
  	{
        $afterCutName = $name;
        $l=mb_strlen($name,'UTF-8');
        if ($l > $length) {
            $afterCutName = mb_substr($name, 0, $startNum, 'utf-8').'…';
            $afterCutName .= mb_substr($name, $l-$endNum, $l, 'utf-8');
        }
      	return $afterCutName;
    }


}