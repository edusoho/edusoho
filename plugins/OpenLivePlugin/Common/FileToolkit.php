<?php

namespace OpenLivePlugin\Common;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileToolkit
{
    public static function isImageFile(File $file)
    {
        $ext = static::getFileExtension($file);

        return in_array(strtolower($ext), explode(' ', static::getImageExtensions()));
    }

    public static function getFileExtension(File $file)
    {
        return $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->getExtension();
    }

    public static function getImageExtensions()
    {
        return 'bmp jpg jpeg gif png ico';
    }
}
