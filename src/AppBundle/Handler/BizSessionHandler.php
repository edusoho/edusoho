<?php

namespace AppBundle\Handler;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BizSessionHandler implements \SessionHandlerInterface
{
    protected $biz;
    protected $storage;

    const MAX_LIFE_TIME = 86400;

    function __construct($container, TokenStorage $storage)
    {
        $this->biz = $container->get('biz');
        $this->storage = $storage;
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        // TODO: Implement close() method.
        return true;
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($session_id)
    {
        $this->getSessionService()->deleteSessionBySessId($session_id);
        return true;
    }

    /**
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxlifetime)
    {
        $this->getSessionService()->gc();
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $name)
    {
        $maxlifetime = self::MAX_LIFE_TIME;
        $this->gc($maxlifetime);
        return true;
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($session_id)
    {
        $session = $this->getSessionService()->getSessionBySessId($session_id);
        return $session['sess_data'];
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($session_id, $session_data)
    {
        $token = $this->storage->getToken();

        if (empty($token) || ($token instanceof AnonymousToken) || !$token->getUser()) {
            $userId = 0;
        } else {
            $userId = $token->getUser()->getId();
        }

        $session = $this->getSessionService()->getSessionBySessId($session_id);
        $unsavedSession = array(
            'sess_id' => $session_id,
            'sess_data' => $session_data,
            'sess_time' => time(),
            'sess_lifetime' => self::MAX_LIFE_TIME,
            'sess_user_id' => $userId,
            'source' => 'web'
        );
        if (empty($session)) {
            $this->getSessionService()->createSession($unsavedSession);
        } else {
            $this->getSessionService()->updateSessionBySessId($session_id, $unsavedSession);
        }
        return true;
    }

    private function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }
}