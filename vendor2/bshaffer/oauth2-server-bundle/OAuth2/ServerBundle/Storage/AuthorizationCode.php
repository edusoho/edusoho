<?php

namespace OAuth2\ServerBundle\Storage;

use OAuth2\Storage\AuthorizationCodeInterface;
use Doctrine\ORM\EntityManager;
use OAuth2\ServerBundle\Entity\Client;

class AuthorizationCode implements AuthorizationCodeInterface
{
    private $em;

    public function __construct(EntityManager $EntityManager)
    {
        $this->em = $EntityManager;
    }

    /**
     * Fetch authorization code data (probably the most common grant type).
     *
     * Retrieve the stored data for the given authorization code.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be check with.
     *
     * @return
     * An associative array as below, and NULL if the code is invalid
     * @code
     * return array(
     *     "client_id"    => CLIENT_ID,      // REQUIRED Stored client identifier
     *     "user_id"      => USER_ID,        // REQUIRED Stored user identifier
     *     "expires"      => EXPIRES,        // REQUIRED Stored expiration in unix timestamp
     *     "redirect_uri" => REDIRECT_URI,   // REQUIRED Stored redirect URI
     *     "scope"        => SCOPE,          // OPTIONAL Stored scope values in space-separated string
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1
     *
     * @ingroup oauth2_section_4
     */
    public function getAuthorizationCode($code)
    {
        // Get Code
        $code = $this->em->getRepository('OAuth2ServerBundle:AuthorizationCode')->find($code);

        if (!$code) {
            return null;
        }

        return array(
            'client_id' => $code->getClient()->getClientId(),
            'user_id' => $code->getUserId(),
            'expires' => $code->getExpires()->getTimestamp(),
            'redirect_uri' => implode(' ', $code->getRedirectUri()),
            'scope' => $code->getScope()
        );
    }

    /**
     * Take the provided authorization code values and store them somewhere.
     *
     * This function should be the storage counterpart to getAuthCode().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param string $redirect_uri
     *                             Redirect URI(s) to be stored in a space-separated string.
     * @param int    $expires
     *                             Expiration to be stored as a Unix timestamp.
     * @param string $scope
     *                             (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        $client = $this->em->getRepository('OAuth2ServerBundle:Client')->find($client_id);

        if (!$client) throw new \Exception('Unknown client identifier');

        $authorizationCode = new \OAuth2\ServerBundle\Entity\AuthorizationCode();
        $authorizationCode->setCode($code);
        $authorizationCode->setClient($client);
        $authorizationCode->setUserId($user_id);
        $authorizationCode->setRedirectUri($redirect_uri);
        $authorizationCode->setExpires($expires);
        $authorizationCode->setScope($scope);

        $this->em->persist($authorizationCode);
        $this->em->flush();
    }

    /**
     * once an Authorization Code is used, it must be exipired
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
     *
     *    The client MUST NOT use the authorization code
     *    more than once.  If an authorization code is used more than
     *    once, the authorization server MUST deny the request and SHOULD
     *    revoke (when possible) all tokens previously issued based on
     *    that authorization code
     *
     */
    public function expireAuthorizationCode($code)
    {
        $code = $this->em->getRepository('OAuth2ServerBundle:AuthorizationCode')->find($code);
        $this->em->remove($code);
        $this->em->flush();
    }
}
