<?php
namespace Topxia\Common;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileToolkit
{

    /**
     * this code from drupal 8
     */
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

    public static function getFileExtension(File $file)
    {
        return $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->getExtension();
    }

    public static function getSecureFileExtensions()
    {
        return 'jpg jpeg gif png txt doc docx xls xlsx pdf ppt pptx pps ods odp mp4 mp3 avi flv wmv wma zip rar gz tar 7z';
    }

    public static function getImageExtensions()
    {
        return 'jpg jpeg gif png';
    }

}