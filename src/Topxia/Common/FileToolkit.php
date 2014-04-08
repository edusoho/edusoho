<?php
namespace Topxia\Common;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileToolkit
{

    public static function mungeFilename($filename, $extensions)
    {
        $original = $filename;

        // Remove any null bytes. See http://php.net/manual/en/security.filesystem.nullbytes.php
        $filename = str_replace(chr(0), '', $filename);

        $whitelist = array_unique(explode(' ', trim($extensions)));

        // Split the filename up by periods. The first part becomes the basename
        // the last part the final extension.
        $filename_parts = explode('.', $filename);
        $new_filename = array_shift($filename_parts); // Remove file basename.
        $final_extension = array_pop($filename_parts); // Remove final extension.

        // Loop through the middle parts of the name and add an underscore to the
        // end of each section that could be a file extension but isn't in the list
        // of allowed extensions.
        foreach ($filename_parts as $filename_part) {
            $new_filename .= '.' . $filename_part;
            if (!in_array($filename_part, $whitelist) && preg_match("/^[a-zA-Z]{2,5}\d?$/", $filename_part)) {
                $new_filename .= '_';
            }
        }

        $filename = $new_filename . '.' . $final_extension;

        return $filename;
    }

    public static function validateFileExtension(File $file, $extensions = array())
    {
        if (empty($extensions)) {
            $extensions = self::getSecureFileExtensions();
        }

        if ($file instanceof UploadedFile) {
            $filename = $file->getClientOriginalName();
        } else {
            $filename = $file->getFilename();
        }

        $errors = array();
        $regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';
        if (!preg_match($regex, $filename)) {
            $errors[] = "只允许上传以下扩展名的文件：" . $extensions;
        }
        return $errors;
    }

    public static function isImageFile(File $file) 
    {
        $ext = self::getFileExtension($file);
        return in_array(strtolower($ext), explode(' ', self::getImageExtensions()));
    }

    public static function isIcoFile(File $file)
    {
        $ext = strtolower(self::getFileExtension($file));
        return $ext == 'ico' ? true : false;
    }

    public static function generateFilename($ext = '')
    {
        $filename = date('Yndhis') . '-' . substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 6);
        return $filename . '.' . $ext;
    }

    public static function getFileExtension(File $file)
    {
        return $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->getExtension();
    }

    public static function getSecureFileExtensions()
    {
        return 'jpg jpeg gif png txt doc docx xls xlsx pdf ppt pptx pps ods odp mp4 mp3 avi flv wmv wma mov zip rar gz tar 7z swf ico';
    }

    public static function getImageExtensions()
    {
        return 'bmp jpg jpeg gif png ico';
    }

    public static function getFileTypeByMimeType($mimeType)
    {
        if (strpos($mimeType, 'video') === 0) {
            return 'video';
        } elseif (strpos($mimeType, 'audio') === 0) {
            return 'audio';
        } elseif (strpos($mimeType, 'image') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'application/vnd.ms-') === 0 
            or strpos($mimeType, 'application/vnd.openxmlformats-officedocument') === 0
            or strpos($mimeType, 'application/pdf') === 0) {
            return 'document';
        }

        return 'other';
    }

    public static function getFileTypeByExtension($extension)
    {
        if (in_array($extension, array('mp4', 'avi', 'wmv', 'flv', 'mov'))) {
            return 'video';
        } elseif (in_array($extension, array('mp3', 'wma'))) {
            return 'audio';
        } elseif (in_array($extension, array('jpg', 'jpeg', 'gif', 'png'))) {
            return 'image';
        } elseif (in_array($extension, array('txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx'))) {
            return 'document';
        } else {
            return 'other';
        }
    }

    public static function formatFileSize($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);
        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(1000, $exp);
            $currentUnit = $unit;
            $currentValue = $size / $divisor;
            if ($currentValue < 1000) {
                break;
            }
        }

        return sprintf('%.1f', $currentValue) . $currentUnit;
    }

    public static function getMaxFilesize()
    {
        $max = strtolower(ini_get('upload_max_filesize'));

        if ('' === $max) {
            return PHP_INT_MAX;
        }

        if (preg_match('#^\+?(0x?)?(.*?)([kmg]?)$#', $max, $match)) {
            $shifts = array('' => 0, 'k' => 10, 'm' => 20, 'g' => 30);
            $bases = array('' => 10, '0' => 8, '0x' => 16);

            return intval($match[2], $bases[$match[1]]) << $shifts[$match[3]];
        }

        return 0;
    }

}