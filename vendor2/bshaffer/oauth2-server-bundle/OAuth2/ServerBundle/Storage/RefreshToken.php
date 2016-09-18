<?php

namespace OAuth2\ServerBundle\Storage;

use OAuth2\Storage\RefreshTokenInterface;
use Doctrine\ORM\EntityManager;

class RefreshToken implements RefreshTokenInterface
{
    private $em;

    public function __construct(EntityManager $EntityManager)
    {
        $this->em = $EntityManager;
    }

    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Stored refresh token identifier.
     * - client_id: Stored client identifier.
     * - user_id: Stored user identifier.
     * - expires: Stored expiration unix timestamp.
     * - scope: (optional) Stored scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    public function getRefreshToken($refresh_token)
    {
        $refreshToken = $this->em->getRepository('OAuth2ServerBundle:RefreshToken')->find($refresh_token);

        if (!$refreshToken) {
            return null;
        }

        // Get Client
        $client = $refreshToken->getClient();

        return array(
            'refresh_token' => $refreshToken->getToken(),
            'client_id' => $client->getClientId(),
            'user_id' => $refreshToken->getUserId(),
            'expires' => $refreshToken->getExpires()->getTimestamp(),
            'scope' => $refreshToken->getScope()
        );
    }

    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * expires to be stored.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_6
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        // Get Client Entity
        $client = $this->em->getRepository('OAuth2ServerBundle:Client')->find($client_id);
        if (!$client) {
            return null;
        }

        // Create Refresh Token
        $refreshToken = new \OAuth2\ServerBundle\Entity\RefreshToken();
        $refreshToken->setToken($refresh_token);
        $refreshToken->setClient($client);
        $refreshToken->setUserId($user_id);
        $refreshToken->setExpires($expires);
        $refreshToken->setScope($scope);

        // Store Refresh Token
        $this->em->persist($refreshToken);
        $this->em->flush();
    }

    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expirse.
     *
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken($refresh_token)
    {
        $refreshToken = $this->em->getRepository('OAuth2ServerBundle:RefreshToken')->find($refresh_token);
        $this->em->remove($refreshToken);
        $this->em->flush();
    }
}
