<?php
/**
 * Pure-PHP ASN.1 Parser
 *
 * PHP version 5
 *
 * @category  File
 * @package   ASN1
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2012 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @link      http://phpseclib.sourceforge.net
 */

namespace Topxia\Service\Util\Phpsec\File\ASN1;

/**
 * ASN.1 Element
 *
 * Bypass normal encoding rules in Topxia\Service\Util\Phpsec\File\ASN1::encodeDER()
 *
 * @access  public
 * @package ASN1
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class Element
{
    /**
     * Raw element value
     *
     * @var String
     * @access private
     */
    public $element;

    /**
     * Constructor
     *
     * @access public
     * @param  String                                          $encoded
     * @return \Topxia\Service\Util\Phpsec\File\ASN1\Element
     */
    public function __construct($encoded)
    {
        $this->element = $encoded;
    }
}
