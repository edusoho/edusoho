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
            for($i=0;$i<$l;$i++){
                $array[]=mb_substr($name,$i,1,'UTF-8');
            }
            //反转字符串
            krsort($array);
            //拼接字符串
            $string=implode($array);
            $afterCutName .= mb_substr($string, 0, $endNum, 'utf-8');
        }
      	return $afterCutName;
    }


}