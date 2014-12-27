<?php
namespace Topxia\Common;

class NumberToolkit {
	public static function roundUp($value, $precision=2) 
    {
        $amt = explode(".", $value);
        if(count($amt)==0){
            return 0;
        } 

        if(count($amt) == 1){
            return $amt[0];
        }

        if(strlen($amt[1]) > $precision) {
            $next = (int)substr($amt[1],$precision);
            $amt[1] = (float)(".".substr($amt[1],0,$precision));
            if($next != 0) {
                $rUp = "";
                for($x=1;$x<$precision;$x++) $rUp .= "0";
                $amt[1] = $amt[1] + (float)(".".$rUp."1");
            }
        }
        else {
            $amt[1] = (float)(".".$amt[1]);
        }
        return $amt[0]+$amt[1];
    }
}