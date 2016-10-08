<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Topxia\WebBundle\Twig\Asset;

use Symfony\Component\Templating\Asset\Package;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Util\CdnUrl;

/**
 * The path packages adds a version and a base path to asset URLs.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class PathPackage extends Package
{
    private $basePath;

    /**
     * Constructor.
     *
     * @param string $basePath The base path to be prepended to relative paths
     * @param string $version  The package version
     * @param string $format   The format used to apply the version
     */
    public function __construct(Request $request, $version = null, $format = null)
    {
    	$basePath = $request->getBasePath();
    	$cdn = new CdnUrl();
        $cdnUrl = $cdn->get();

        parent::__construct($version, $format);

        if (!$basePath) {
            $this->basePath = '/';
        } else {
            if ('/' != $basePath[0]) {
                $basePath = '/'.$basePath;
            }

            $this->basePath = rtrim($basePath, '/').'/';
        }

        $this->basePath = $cdnUrl . $this->basePath;
    }

    public function getUrl($path)
    {
        if (false !== strpos($path, '://') || 0 === strpos($path, '//')) {
            return $path;
        }

        $url = $this->applyVersion($path);

        // apply the base path
        if ('/' !== substr($url, 0, 1)) {
            $url = $this->basePath.$url;
        }
        return $url;
    }

    /**
     * Returns the base path.
     *
     * @return string The base path
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
}
