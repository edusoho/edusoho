<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 11/12/2016
 * Time: 12:15
 */


if (!is_callable('random_bytes')) {

    function random_bytes($nbBytes)
    {
        $useOpenSsl = false;
        // determine whether to use OpenSSL
        if ('\\' === DIRECTORY_SEPARATOR && PHP_VERSION_ID < 50304) {
            $useOpenSsl = false;
        } elseif (!function_exists('openssl_random_pseudo_bytes')) {
            $useOpenSsl = false;
        } else {
            $useOpenSsl = true;
        }

        $seedFile = __DIR__.DIRECTORY_SEPARATOR.'secure_random.seed';

        // try OpenSSL
        if ($useOpenSsl) {
            $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
            if (false !== $bytes && true === $strong) {
                return $bytes;
            }
        }

        $seed              = null;
        $seedUpdated       = false;
        $seedLastUpdatedAt = false;
        // initialize seed
        if (null === $seed) {
            if (null === $seedFile) {
                throw new \RuntimeException('You need to specify a file path to store the seed.');
            }

            if (is_file($seedFile)) {
                list($seed, $seedLastUpdatedAt) = json_decode(file_get_contents($seedFile));
            } else {
                $seed = uniqid(mt_rand(), true);
                if (!$seedUpdated && $seedLastUpdatedAt < time() - mt_rand(1, 10)) {
                    file_put_contents($seedFile, json_encode(array($seed, microtime(true))));
                }
            }
        }

        $bytes = '';
        while (strlen($bytes) < $nbBytes) {
            static $incr = 1;
            $bytes .= hash('sha512', $incr++.$seed.uniqid(mt_rand(), true).$nbBytes, true);
            $seed = base64_encode(hash('sha512', $seed.$bytes.$nbBytes, true));
            if (!$seedUpdated && $seedLastUpdatedAt < time() - mt_rand(1, 10)) {
                file_put_contents($seedFile, json_encode(array($seed, microtime(true))));
            }
        }

        return substr($bytes, 0, $nbBytes);
    }

}