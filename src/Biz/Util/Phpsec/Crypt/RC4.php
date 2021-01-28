<?php

/**
 * Pure-PHP implementation of RC4.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * Useful resources are as follows:
 *
 *  - {@link http://www.mozilla.org/projects/security/pki/nss/draft-kaukonen-cipher-arcfour-03.txt ARCFOUR Algorithm}
 *  - {@link http://en.wikipedia.org/wiki/RC4 - Wikipedia: RC4}
 *
 * RC4 is also known as ARCFOUR or ARC4.  The reason is elaborated upon at Wikipedia.  This class is named RC4 and not
 * ARCFOUR or ARC4 because RC4 is how it is referred to in the SSH1 specification.
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rc4 = new \Biz\Util\Phpsec\Crypt\RC4();
 *
 *    $rc4->setKey('abcdefgh');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $rc4->decrypt($rc4->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @category  Crypt
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @see      http://phpseclib.sourceforge.net
 */

namespace Biz\Util\Phpsec\Crypt;

/**
 * Pure-PHP implementation of RC4.
 *
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class RC4 extends Base
{
    /**#@+
     * @access private
     * @see \Biz\Util\Phpsec\Crypt\RC4::_crypt()
     */
    const ENCRYPT = 0;
    const DECRYPT = 1;
    /**#@-*/

    /**
     * Block Length of the cipher.
     *
     * RC4 is a stream cipher
     * so we the block_size to 0
     *
     * @var int
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::block_size
     */
    public $block_size = 0;

    /**
     * The default password key_size used by setPassword().
     *
     * @var int
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::password_key_size
     * @see \Biz\Util\Phpsec\Crypt\Base::setPassword()
     */
    public $password_key_size = 128; // = 1024 bits

    /**
     * The mcrypt specific name of the cipher.
     *
     * @var string
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::cipher_name_mcrypt
     */
    public $cipher_name_mcrypt = 'arcfour';

    /**
     * Holds whether performance-optimized $inline_crypt() can/should be used.
     *
     * @var mixed
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::inline_crypt
     */
    public $use_inline_crypt = false; // currently not available

    /**
     * The Key.
     *
     * @var string
     *
     * @see \Biz\Util\Phpsec\Crypt\RC4::setKey()
     */
    public $key = "\0";

    /**
     * The Key Stream for decryption and encryption.
     *
     * @var array
     *
     * @see \Biz\Util\Phpsec\Crypt\RC4::setKey()
     */
    public $stream;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::__construct()
     *
     * @return \Biz\Util\Phpsec\Crypt\RC4
     */
    public function __construct()
    {
        parent::__construct(Base::MODE_STREAM);
    }

    /**
     * Test for engine validity.
     *
     * This is mainly just a wrapper to set things up for Crypt_Base::isValidEngine()
     *
     * @see Crypt_Base::Crypt_Base()
     *
     * @param int $engine
     *
     * @return bool
     */
    public function isValidEngine($engine)
    {
        switch ($engine) {
            case Base::ENGINE_OPENSSL:

                switch (strlen($this->key)) {
                case 5:
                        $this->cipher_name_openssl = 'rc4-40';
                        break;
                case 8:
                        $this->cipher_name_openssl = 'rc4-64';
                        break;
                case 16:
                        $this->cipher_name_openssl = 'rc4';
                        break;
                default:
                        return false;
                }
        }

        return parent::isValidEngine($engine);
    }

    /**
     * Dummy function.
     *
     * Some protocols, such as WEP, prepend an "initialization vector" to the key, effectively creating a new key [1].
     * If you need to use an initialization vector in this manner, feel free to prepend it to the key, yourself, before
     * calling setKey().
     *
     * [1] WEP's initialization vectors (IV's) are used in a somewhat insecure way.  Since, in that protocol,
     * the IV's are relatively easy to predict, an attack described by
     * {@link http://www.drizzle.com/~aboba/IEEE/rc4_ksaproc.pdf Scott Fluhrer, Itsik Mantin, and Adi Shamir}
     * can be used to quickly guess at the rest of the key.  The following links elaborate:
     *
     * {@link http://www.rsa.com/rsalabs/node.asp?id=2009 http://www.rsa.com/rsalabs/node.asp?id=2009}
     * {@link http://en.wikipedia.org/wiki/Related_key_attack http://en.wikipedia.org/wiki/Related_key_attack}
     *
     * @see \Biz\Util\Phpsec\Crypt\RC4::setKey()
     *
     * @param string $iv
     */
    public function setIV($iv)
    {
    }

    /**
     * Sets the key.
     *
     * Keys can be between 1 and 256 bytes long.  If they are longer then 256 bytes, the first 256 bytes will
     * be used.  If no key is explicitly set, it'll be assumed to be a single null byte.
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::setKey()
     *
     * @param string $key
     */
    public function setKey($key)
    {
        parent::setKey(substr($key, 0, 256));
    }

    /**
     * Encrypts a message.
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::decrypt()
     * @see \Biz\Util\Phpsec\Crypt\RC4::_crypt()
     *
     * @param string $plaintext
     *
     * @return string $ciphertext
     */
    public function encrypt($plaintext)
    {
        if ($this->engine != Base::ENGINE_INTERNAL) {
            return parent::encrypt($plaintext);
        }

        return $this->_crypt($plaintext, self::ENCRYPT);
    }

    /**
     * Decrypts a message.
     *
     * $this->decrypt($this->encrypt($plaintext)) == $this->encrypt($this->encrypt($plaintext)).
     * At least if the continuous buffer is disabled.
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::encrypt()
     * @see \Biz\Util\Phpsec\Crypt\RC4::_crypt()
     *
     * @param string $ciphertext
     *
     * @return string $plaintext
     */
    public function decrypt($ciphertext)
    {
        if ($this->engine != Base::ENGINE_INTERNAL) {
            return parent::decrypt($ciphertext);
        }

        return $this->_crypt($ciphertext, self::DECRYPT);
    }

    /**
     * Encrypts a block.
     *
     * @param string $in
     */
    public function _encryptBlock($in)
    {
        // RC4 does not utilize this method
    }

    /**
     * Decrypts a block.
     *
     * @param string $in
     */
    public function _decryptBlock($in)
    {
        // RC4 does not utilize this method
    }

    /**
     * Setup the key (expansion).
     *
     * @see \Biz\Util\Phpsec\Crypt\Base::_setupKey()
     */
    public function _setupKey()
    {
        $key = $this->key;
        $keyLength = strlen($key);
        $keyStream = range(0, 255);
        $j = 0;

        for ($i = 0; $i < 256; ++$i) {
            $j = ($j + $keyStream[$i] + ord($key[$i % $keyLength])) & 255;
            $temp = $keyStream[$i];
            $keyStream[$i] = $keyStream[$j];
            $keyStream[$j] = $temp;
        }

        $this->stream = array();
        $this->stream[self::DECRYPT] = $this->stream[self::ENCRYPT] = array(
            0, // index $i
            0, // index $j
            $keyStream,
        );
    }

    /**
     * Encrypts or decrypts a message.
     *
     * @see \Biz\Util\Phpsec\Crypt\RC4::encrypt()
     * @see \Biz\Util\Phpsec\Crypt\RC4::decrypt()
     *
     * @param string $text
     * @param int    $mode
     *
     * @return string $text
     */
    public function _crypt($text, $mode)
    {
        if ($this->changed) {
            $this->_setup();
            $this->changed = false;
        }

        $stream = &$this->stream[$mode];

        if ($this->continuousBuffer) {
            $i = &$stream[0];
            $j = &$stream[1];
            $keyStream = &$stream[2];
        } else {
            $i = $stream[0];
            $j = $stream[1];
            $keyStream = $stream[2];
        }

        $len = strlen($text);

        for ($k = 0; $k < $len; ++$k) {
            $i = ($i + 1) & 255;
            $ksi = $keyStream[$i];
            $j = ($j + $ksi) & 255;
            $ksj = $keyStream[$j];

            $keyStream[$i] = $ksj;
            $keyStream[$j] = $ksi;
            $text[$k] = $text[$k] ^ chr($keyStream[($ksj + $ksi) & 255]);
        }

        return $text;
    }
}
