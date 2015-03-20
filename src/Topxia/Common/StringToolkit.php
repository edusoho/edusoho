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

    public static function sign($data, $key)
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data);

        return md5(json_encode($data) . $key);
    }

    public static function secondsToText($value)
    {
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;
        return sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
    }

    public static function textToSeconds($text)
    {
        if (strpos($text, ':') === false) {
            return 0;
        }
        list($minutes, $seconds) = explode(':', $text, 2);
        return intval($minutes) * 60 + intval($seconds);
    }

    public static function plain($text, $length = 0)
    {
        $text = strip_tags($text);

        $text = str_replace(array("\n", "\r", "\t") , '', $text);
        $text = str_replace('&nbsp;' , ' ', $text);
        $text = trim($text);

        $length = (int) $length;
        if ( ($length > 0) && (mb_strlen($text) > $length) )  {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

}