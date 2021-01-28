<?php
/**
 * This file is part of PHPOffice Common
 *
 * PHPOffice Common is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/Common/contributors.
 *
 * @link        https://github.com/PHPOffice/Common
 * @copyright   2009-2016 PHPOffice Common contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\Common;

/**
 * Font
 */
class Font
{
    /**
     * Calculate an (approximate) pixel size, based on a font points size
     *
     * @param  int $fontSizeInPoints Font size (in points)
     * @return int Font size (in pixels)
     */
    public static function fontSizeToPixels($fontSizeInPoints = 12)
    {
        return ((16 / 12) * $fontSizeInPoints);
    }

    /**
     * Calculate an (approximate) pixel size, based on inch size
     *
     * @param  int $sizeInInch Font size (in inch)
     * @return int Size (in pixels)
     */
    public static function inchSizeToPixels($sizeInInch = 1)
    {
        return ($sizeInInch * 96);
    }

    /**
     * Calculate an (approximate) pixel size, based on centimeter size
     *
     * @param  int $sizeInCm Font size (in centimeters)
     * @return int Size (in pixels)
     */
    public static function centimeterSizeToPixels($sizeInCm = 1)
    {
        return ($sizeInCm * 37.795275591);
    }

    /**
     * Convert centimeter to twip
     *
     * @param int $sizeInCm
     * @return double
     */
    public static function centimeterSizeToTwips($sizeInCm = 1)
    {
        return $sizeInCm / 2.54 * 1440;
    }

    /**
     * Convert inch to twip
     *
     * @param int $sizeInInch
     * @return double
     */
    public static function inchSizeToTwips($sizeInInch = 1)
    {
        return $sizeInInch * 1440;
    }

    /**
     * Convert pixel to twip
     *
     * @param int $sizeInPixel
     * @return double
     */
    public static function pixelSizeToTwips($sizeInPixel = 1)
    {
        return $sizeInPixel / 96 * 1440;
    }

    /**
     * Calculate twip based on point size, used mainly for paragraph spacing
     *
     * @param integer $sizeInPoint Size in point
     * @return integer Size (in twips)
     */
    public static function pointSizeToTwips($sizeInPoint = 1)
    {
        return $sizeInPoint / 72 * 1440;
    }
}
