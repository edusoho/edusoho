<?php

namespace Biz\FaceInspection\Common;

class FileToolkit
{
    public static function saveBase64Image($base64, $type = 'face_capture')
    {
        $path = getcwd().'/files/facein';
        if (!is_dir($path)) {
            @mkdir($path);
        }
        $path = $path.'/'.$type;
        if (!is_dir($path)) {
            @mkdir($path);
        }

        $bases = preg_split('/(,|;)/', $base64); //分隔三部分，data:image/png  base64  后面一堆
        $base64Data = $bases[2];
        $bases2 = explode('/', $bases[0]); //分割出图片格式
        $suffix = '.'.$bases2[1];

        $fileName = self::getImgName($type, $suffix);
        $uploadPath = $path.'/'.$fileName;
        file_put_contents($uploadPath, base64_decode($base64Data));

        return 'public://facein/'.$type.'/'.$fileName;
    }

    public static function saveBlobImage($blob, $type = 'face_capture')
    {
        $path = getcwd().'/files/facein';
        if (!is_dir($path)) {
            @mkdir($path);
        }
        $path = $path.'/'.$type;
        if (!is_dir($path)) {
            @mkdir($path);
        }
        $image = $blob['tmp_name'];
        $tpe = $blob['type'];
        $bases = explode('/', $tpe); //分割出图片格式image/png
        $suffix = '.'.$bases[1];
        $fileName = self::getImgName($type, $suffix);
        $fp = fopen($image, 'r');
        $file = fread($fp, $blob['size']); //二进制数据流
        $uploadPath = $path.'/'.$fileName;
        $newFile = fopen($uploadPath, 'w');
        fwrite($newFile, $file);

        fclose($newFile); //关闭文件
        return 'public://facein/'.$type.'/'.$fileName;
    }

    protected static function getImgName($type = 'face_capture', $suffix = '.jpg')
    {
        $mic = str_replace('.', '_', microtime(true));

        return $type.'_'.$mic.$suffix;
    }
}
