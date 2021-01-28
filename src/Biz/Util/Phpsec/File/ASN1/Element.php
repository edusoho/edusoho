<?php
/**
 * Pure-PHP ASN.1 Parser.
 *
 * PHP version 5
 *
 * @category  File
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2012 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @see      http://phpseclib.sourceforge.net
 */

namespace Biz\Util\Phpsec\File\ASN1;

/**
 * ASN.1 Element.
 *
 * Bypass normal encoding rules in Biz\Util\Phpsec\File\ASN1::encodeDER()
 *
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class Element
{
    /**
     * Raw element value.
     *
     * @var string
     */
    public $element;

    /**
     * Constructor.
     *
     * @param string $encoded
     *
     * @return \Biz\Util\Phpsec\File\ASN1\Element
     */
    public function __construct($encoded)
    {
        $this->element = $encoded;
    }
}
