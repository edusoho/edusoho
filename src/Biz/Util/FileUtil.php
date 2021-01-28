<?php

namespace Biz\Util;

use Symfony\Component\Filesystem\Filesystem;

class FileUtil
{
    public static function emptyDir($dirPath, $includeDir = false)
    {
        $fileSystem = new FileSystem();
        if (!$fileSystem->exists($dirPath)) {
            return;
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isFile() ? $fileSystem->remove($path->getPathname()) : rmdir($path->getPathname());
        }
        if ($includeDir) {
            rmdir($dirPath);
        }
    }

    public static function deepCopy($src, $dest, array $patternMatch = null)
    {
        $fileSystem = new FileSystem();
        if (!$fileSystem->exists($src)) {
            return;
        }
        if (!$fileSystem->exists($dest)) {
            $fileSystem->mkdir($dest, 0777);
        }
        $match = false;
        if (!empty($patternMatch) && 2 == count($patternMatch)) {
            $match = true;
        }
        $fileCount = 0;

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $path) {
            if ($match && $patternMatch[0]->$patternMatch[1]($path->getPathname())) {
                continue;
            }

            $relativeFile = str_replace($src, '', $path->getPathname());

            $destFile = $dest.$relativeFile;
            if ($path->isDir()) {
                if (!$fileSystem->exists($destFile)) {
                    $fileSystem->mkdir($destFile, 0777);
                }
            } else {
                if (0 === strpos($path->getFilename(), '.')) {
                    continue;
                }
                $fileSystem->copy($path->getPathname(), $destFile, true);
                ++$fileCount;
            }
        }

        return $fileCount;
    }
}
