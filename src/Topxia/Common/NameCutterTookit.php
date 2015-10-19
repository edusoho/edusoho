<?php
namespace Topxia\Common;

class NameCutterTookit
{
  	public static function cutter($name, $length, $startNum, $endNum)
  	{
    	if (strlen($name) > $length) {
      		$afterCutName = substr($name, 0, $startNum).'…'.substr($name, $endNum);
    	} else {
    		$afterCutName = $name;
    	}
    	return $afterCutName;
  	}

}