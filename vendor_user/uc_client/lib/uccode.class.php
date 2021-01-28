<?php

/*
    [UCenter] (C)2001-2099 Comsenz Inc.
    This is NOT a freeware, use is subject to license terms

    $Id: uccode.class.php 1059 2011-03-01 07:25:09Z monkey $
*/

class uccode
{
    public $uccodes;

    public function uccode()
    {
        $this->uccode = array(
            'pcodecount' => -1,
            'codecount' => 0,
            'codehtml' => '',
        );
    }

    public function codedisp($code)
    {
        ++$this->uccode['pcodecount'];
        $code = str_replace('\\"', '"', preg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", '\\1', $code));
        $this->uccode['codehtml'][$this->uccode['pcodecount']] = $this->tpl_codedisp($code);
        ++$this->uccode['codecount'];

        return "[\tUCENTER_CODE_".$this->uccode[pcodecount]."\t]";
    }

    public function complie($message)
    {
        $message = htmlspecialchars($message);
        if (false !== strpos($message, '[/code]')) {
            $message = preg_replace_callback("/\s*\[code\](.+?)\[\/code\]\s*/is", array($this, 'complie_callback_codedisp_1'), $message);
        }
        if (false !== strpos($message, '[/url]')) {
            $message = preg_replace_callback("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)([^\[\"']+?))?\](.+?)\[\/url\]/is", array($this, 'complie_callback_parseurl_15'), $message);
        }
        if (false !== strpos($message, '[/email]')) {
            $message = preg_replace_callback("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/is", array($this, 'complie_callback_parseemail_14'), $message);
        }
        $message = str_replace(array(
            '[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]',
            '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
            '[list=A]', '[*]', '[/list]', '[indent]', '[/indent]', '[/float]',
        ), array(
            '</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>', '<i>',
            '</i>', '<u>', '</u>', '<ul>', '<ul type="1">', '<ul type="a">',
            '<ul type="A">', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>',
        ), preg_replace(array(
            "/\[color=([#\w]+?)\]/i",
            "/\[size=(\d+?)\]/i",
            "/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
            "/\[font=([^\[\<]+?)\]/i",
            "/\[align=(left|center|right)\]/i",
            "/\[float=(left|right)\]/i",
        ), array(
            '<font color="\\1">',
            '<font size="\\1">',
            '<font style="font-size: \\1">',
            '<font face="\\1 ">',
            '<p align="\\1">',
            '<span style="float: \\1;">',
        ), $message));
        if (false !== strpos($message, '[/quote]')) {
            $message = preg_replace("/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is", $this->tpl_quote(), $message);
        }
        if (false !== strpos($message, '[/img]')) {
            $message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", array($this, 'complie_callback_bbcodeurl_1'), $message);
            $message = preg_replace_callback("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", array($this, 'complie_callback_bbcodeurl_312'), $message);
        }
        for ($i = 0; $i <= $this->uccode['pcodecount']; ++$i) {
            $message = str_replace("[\tUCENTER_CODE_$i\t]", $this->uccode['codehtml'][$i], $message);
        }

        return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
    }

    public function complie_callback_codedisp_1($matches)
    {
        return $this->codedisp($matches[1]);
    }

    public function complie_callback_parseurl_15($matches)
    {
        return $this->parseurl($matches[1], $matches[5]);
    }

    public function complie_callback_parseemail_14($matches)
    {
        return $this->parseemail($matches[1], $matches[4]);
    }

    public function complie_callback_bbcodeurl_1($matches)
    {
        return $this->bbcodeurl($matches[1], '<img src="%s" border="0" alt="" />');
    }

    public function complie_callback_bbcodeurl_312($matches)
    {
        return $this->bbcodeurl($matches[3], '<img width="'.$matches[1].'" height="'.$matches[2].'" src="%s" border="0" alt="" />');
    }

    public function parseurl($url, $text)
    {
        if (!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
            $url = $matches[0];
            $length = 65;
            if (strlen($url) > $length) {
                $text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, -intval($length * 0.3));
            }

            return '<a href="'.('www.' == substr(strtolower($url), 0, 4) ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
        } else {
            $url = substr($url, 1);
            if ('www.' == substr(strtolower($url), 0, 4)) {
                $url = 'http://'.$url;
            }

            return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
        }
    }

    public function parseemail($email, $text)
    {
        if (!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
            $email = trim($matches[0]);

            return '<a href="mailto:'.$email.'">'.$email.'</a>';
        } else {
            return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
        }
    }

    public function bbcodeurl($url, $tags)
    {
        if (!preg_match('/<.+?>/s', $url)) {
            if (!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'))) {
                $url = 'http://'.$url;
            }

            return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
        } else {
            return '&nbsp;'.$url;
        }
    }

    public function tpl_codedisp($code)
    {
        return '<div class="blockcode"><code id="code'.$this->uccodes['codecount'].'">'.$code.'</code></div>';
    }

    public function tpl_quote()
    {
        return '<div class="quote"><blockquote>\\1</blockquote></div>';
    }
}

/*

Usage:
$str = <<<EOF
1
2
3
EOF;
require_once 'lib/uccode.class.php';
$this->uccode = new uccode();
echo $this->uccode->complie($str);

*/
