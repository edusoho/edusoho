<?php

namespace AppBundle\Common;

class StringToolkit
{
    public static function template($string, array $variables)
    {
        if (empty($variables)) {
            return $string;
        }

        $search = array_keys($variables);
        array_walk($search, function (&$item) {
            $item = '{{'.$item.'}}';
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

        return md5(json_encode($data).$key);
    }

    public static function secondsToText($value)
    {
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;

        return sprintf('%02d', $minutes).':'.sprintf('%02d', $seconds);
    }

    public static function textToSeconds($text)
    {
        if (false === strpos($text, ':')) {
            return 0;
        }
        list($minutes, $seconds) = explode(':', $text, 2);

        return intval($minutes) * 60 + intval($seconds);
    }

    public static function plain($text, $length = 0)
    {
        $text = strip_tags($text);

        $text = str_replace(array("\n", "\r", "\t"), '', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);

        $length = (int) $length;
        if (($length > 0) && (mb_strlen($text) > $length)) {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

    public static function specialCharsFilter($text)
    {
        return str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;', '&lsquo;', '&rsquo;'), array(' ', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…', '‘', '’'), $text);
    }

    public static function createRandomString($length)
    {
        $start = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = null;
        for ($i = 0; $i < $length; ++$i) {
            $rand = rand(0, 61);
            $code = $code.$start[$rand];
        }

        return $code;
    }

    public static function jsonPettry($json)
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = null;
        $json_length = strlen($json);

        for ($i = 0; $i < $json_length; ++$i) {
            $char = $json[$i];
            $new_line_level = null;
            $post = '';
            if (null !== $ends_line_level) {
                $new_line_level = $ends_line_level;
                $ends_line_level = null;
            }
            if ($in_escape) {
                $in_escape = false;
            } elseif ('"' === $char) {
                $in_quotes = !$in_quotes;
            } elseif (!$in_quotes) {
                switch ($char) {
                    case '}':
                    case ']':
                        $level--;
                        $ends_line_level = null;
                        $new_line_level = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;
                        // no break
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = ' ';
                        break;

                    case ' ':
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = '';
                        $ends_line_level = $new_line_level;
                        $new_line_level = null;
                        break;
                }
            } elseif ('\\' === $char) {
                $in_escape = true;
            }
            if (null !== $new_line_level) {
                $result .= "\n".str_repeat("\t", $new_line_level);
            }
            $result .= $char.$post;
        }

        return $result;
    }

    public static function cutter($name, $leastLength, $prefixLength, $suffixLength)
    {
        $afterCutName = $name;
        $length = mb_strlen($name, 'UTF-8');
        if ($length > $leastLength) {
            $afterCutName = mb_substr($name, 0, $prefixLength, 'utf-8').'…';
            $afterCutName .= mb_substr($name, $length - $suffixLength, $length, 'utf-8');
        }

        return $afterCutName;
    }

    public static function jsonEncode($data)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $data = urlencode(json_encode($data));

            return urldecode($data);
        }
    }

    public static function printMem($bytes)
    {
        $format = function ($number) {
            return number_format($number, 2);
        };
        if ($bytes < 1024 * 1024 * 1024) {
            return call_user_func($format, $bytes / 1024 / 1024).'M';
        } else {
            return call_user_func($format, $bytes / 1024 / 1024 / 1024).'G';
        }
    }

    /**
     * gzip 方式压缩字符串, 注意，压缩出来的是 byte 数据，不能直接转化为 string, 转化为字符串，需要转为base64
     * 注意， 只有 php5.5或以上版本才支持压缩，已做判断，如果是php5.3, 不会压缩
     *
     * @param $content 待压缩的内容
     * @param $level 压缩等级， 范围 0 ~ 9， 最低为0，不压缩，最高为9，最消耗cpu资源， 默认为5
     */
    public static function compress($content, $level = 5)
    {
        if (self::isCompressable()) {
            return gzencode($content, $level);
        }

        return $content;
    }

    /**
     * gzip 方式解压字符串, 注意，参数为 压缩后的内容，为byte数据
     * 注意， 只有 php5.5或以上版本才支持解压，已做判断，如果是php5.3, 不会解压
     *
     * @param $content 待解缩的内容
     */
    public static function uncompress($content)
    {
        if (self::isCompressable()) {
            return gzdecode($content);
        }

        return $content;
    }

    public static function appendGzipResponseHeader($header = array())
    {
        if (self::isCompressable()) {
            $header['Content-Encoding'] = 'gzip';
            $header['Vary'] = 'Accept-Encoding';
        }

        return $header;
    }

    public static function isCompressable()
    {
        $segs = explode('.', PHP_VERSION);
        if (count($segs) > 1) {  //版本号至少为 2位
            if ($segs[0] > 5 || $segs[1] > 4) {   //php5.5 或 以上版本
                return true;
            }
        }

        return false;
    }
}
