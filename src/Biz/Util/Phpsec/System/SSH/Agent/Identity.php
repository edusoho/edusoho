<?php
/**
 * Pure-PHP ssh-agent client.
 *
 * PHP version 5
 *
 * @category  System
 *
 * @internal  See http://api.libssh.org/rfc/PROTOCOL.agent
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @see      http://phpseclib.sourceforge.net
 */

namespace Biz\Util\Phpsec\System\SSH\Agent;

use Biz\Util\Phpsec\System\SSH\Agent;

/**
 * Pure-PHP ssh-agent client identity object.
 *
 * Instantiation should only be performed by \Biz\Util\Phpsec\System\SSH\Agent class.
 * This could be thought of as implementing an interface that Biz\Util\Phpsec\Crypt\RSA
 * implements. ie. maybe a Net_SSH_Auth_PublicKey interface or something.
 * The methods in this interface would be getPublicKey, setSignatureMode
 * and sign since those are the methods phpseclib looks for to perform
 * public key authentication.
 *
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class Identity
{
    /**
     * Key Object.
     *
     * @var \Biz\Util\Phpsec\Crypt\RSA
     *
     * @see \Biz\Util\Phpsec\System\SSH\Agent\Identity::getPublicKey()
     */
    public $key;

    /**
     * Key Blob.
     *
     * @var string
     *
     * @see \Biz\Util\Phpsec\System\SSH\Agent\Identity::sign()
     */
    public $key_blob;

    /**
     * Socket Resource.
     *
     * @var resource
     *
     * @see \Biz\Util\Phpsec\System\SSH\Agent\Identity::sign()
     */
    public $fsock;

    /**
     * Default Constructor.
     *
     * @param resource $fsock
     *
     * @return \Biz\Util\Phpsec\System\SSH\Agent\Identity
     */
    public function __construct($fsock)
    {
        $this->fsock = $fsock;
    }

    /**
     * Set Public Key.
     *
     * Called by \Biz\Util\Phpsec\System\SSH\Agent::requestIdentities()
     *
     * @param \Biz\Util\Phpsec\Crypt\RSA $key
     */
    public function setPublicKey($key)
    {
        $this->key = $key;
        $this->key->setPublicKey();
    }

    /**
     * Set Public Key.
     *
     * Called by \Biz\Util\Phpsec\System\SSH\Agent::requestIdentities(). The key blob could be extracted from $this->key
     * but this saves a small amount of computation.
     *
     * @param string $key_blob
     */
    public function setPublicKeyBlob($key_blob)
    {
        $this->key_blob = $key_blob;
    }

    /**
     * Get Public Key.
     *
     * Wrapper for $this->key->getPublicKey()
     *
     * @param int $format optional
     *
     * @return mixed
     */
    public function getPublicKey($format = null)
    {
        return !isset($format) ? $this->key->getPublicKey() : $this->key->getPublicKey($format);
    }

    /**
     * Set Signature Mode.
     *
     * Doesn't do anything as ssh-agent doesn't let you pick and choose the signature mode. ie.
     * ssh-agent's only supported mode is \Biz\Util\Phpsec\Crypt\RSA::SIGNATURE_PKCS1
     *
     * @param int $mode
     */
    public function setSignatureMode($mode)
    {
    }

    /**
     * Create a signature.
     *
     * See "2.6.2 Protocol 2 private key signature request"
     *
     * @param string $message
     *
     * @return string
     */
    public function sign($message)
    {
        // the last parameter (currently 0) is for flags and ssh-agent only defines one flag (for ssh-dss): SSH_AGENT_OLD_SIGNATURE
        $packet = pack('CNa*Na*N', Agent::SSH_AGENTC_SIGN_REQUEST, strlen($this->key_blob), $this->key_blob, strlen($message), $message, 0);
        $packet = pack('Na*', strlen($packet), $packet);

        if (strlen($packet) != fputs($this->fsock, $packet)) {
            user_error('Connection closed during signing');
        }

        $length = current(unpack('N', fread($this->fsock, 4)));
        $type = ord(fread($this->fsock, 1));

        if ($type != Agent::SSH_AGENT_SIGN_RESPONSE) {
            user_error('Unable to retreive signature');
        }

        $signature_blob = fread($this->fsock, $length - 1);
        // the only other signature format defined - ssh-dss - is the same length as ssh-rsa
        // the + 12 is for the other various SSH added length fields
        return substr($signature_blob, strlen('ssh-rsa') + 12);
    }
}
