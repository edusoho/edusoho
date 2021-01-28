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

namespace PhpOffice\Common\Tests;

use PhpOffice\Common\Font;

/**
 * Test class for Font
 *
 * @coversDefaultClass PhpOffice\Common\Font
 */
class FontTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testMath()
    {
        $value = rand(1, 100);
        $this->assertEquals(16, Font::fontSizeToPixels());
        $this->assertEquals((16 / 12) * $value, Font::fontSizeToPixels($value));
        $this->assertEquals(96, Font::inchSizeToPixels());
        $this->assertEquals(96 * $value, Font::inchSizeToPixels($value));
        $this->assertEquals(37.795275591, Font::centimeterSizeToPixels());
        $this->assertEquals(37.795275591 * $value, Font::centimeterSizeToPixels($value));
        $this->assertEquals($value / 2.54 * 1440, Font::centimeterSizeToTwips($value));
        $this->assertEquals($value * 1440, Font::inchSizeToTwips($value));
        $this->assertEquals($value / 96 * 1440, Font::pixelSizeToTwips($value));
        $this->assertEquals($value / 72 * 1440, Font::pointSizeToTwips($value));
    }
}
