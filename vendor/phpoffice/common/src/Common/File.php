<?php
/**
 * This file is part of PHPOffice Common
 *
 * PHPOffice Common is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/Common
 * @copyright   2009-2016 PHPOffice Common contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\Common;

/**
 * Drawing
 */
class File
{
    /**
     * Verify if a file exists
     *
     * @param  string $pFilename Filename
     * @return bool
     */
    public static function fileExists($pFilename)
    {
        // Sick construction, but it seems that
        // file_exists returns strange values when
        // doing the original file_exists on ZIP archives...
        if (strtolower(substr($pFilename, 0, 3)) == 'zip') {
            // Open ZIP file and verify if the file exists
            $zipFile     = substr($pFilename, 6, strpos($pFilename, '#') - 6);
            $archiveFile = substr($pFilename, strpos($pFilename, '#') + 1);

            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === true) {
                $returnValue = ($zip->getFromName($archiveFile) !== false);
                $zip->close();

                return $returnValue;
            }

            return false;
        }

        // Regular file_exists
        return file_exists($pFilename);
    }
    /**
     * Returns the content of a file
     *
     * @param  string $pFilename Filename
     * @return string
     */
    public static function fileGetContents($pFilename)
    {
        if (!self::fileExists($pFilename)) {
            return false;
        }
        if (strtolower(substr($pFilename, 0, 3)) == 'zip') {
            // Open ZIP file and verify if the file exists
            $zipFile     = substr($pFilename, 6, strpos($pFilename, '#') - 6);
            $archiveFile = substr($pFilename, strpos($pFilename, '#') + 1);

            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === true) {
                $returnValue = $zip->getFromName($archiveFile);
                $zip->close();
                return $returnValue;
            }
            return false;
        }
        // Regular file contents
        return file_get_contents($pFilename);
    }

    /**
     * Returns canonicalized absolute pathname, also for ZIP archives
     *
     * @param  string $pFilename
     * @return string
     */
    public static function realpath($pFilename)
    {
        // Try using realpath()
        $returnValue = realpath($pFilename);

        // Found something?
        if ($returnValue == '' || is_null($returnValue)) {
            $pathArray = explode('/', $pFilename);
            while (in_array('..', $pathArray) && $pathArray[0] != '..') {
                $numPathArray = count($pathArray);
                for ($i = 0; $i < $numPathArray; ++$i) {
                    if ($pathArray[$i] == '..' && $i > 0) {
                        unset($pathArray[$i]);
                        unset($pathArray[$i - 1]);
                        break;
                    }
                }
            }
            $returnValue = implode('/', $pathArray);
        }

        // Return
        return $returnValue;
    }
}
