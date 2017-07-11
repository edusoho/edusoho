<?php

namespace AppBundle\Twig\Asset\VersionStrategy;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

/**
 * rewrite file of \Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy
 * Returns the same version for all assets.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class StaticVersionStrategy implements VersionStrategyInterface
{
    private $version;
    private $format;
    private $plugins;

    /**
     * @param string $version Version number
     * @param string $format  Url format
     * @param object $biz
     */
    public function __construct($version, $format = null, $biz = null)
    {
        $this->version = $version;
        $this->format = $format ?: '%s?%s';

        $rootDir = $biz['root_directory'];
        $pluginFilePath = $rootDir.'app/config/plugin.php';
        if (file_exists($pluginFilePath)) {
            $this->plugins = require $pluginFilePath;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($path)
    {
        if (!$this->isEsCompileAssets($path)) {
            return $this->version;
        }
        $version = $this->parseVersionFromPlugin($path);
        if (empty($version)) {
            return $this->version;
        }

        return $version;
    }

    private function isEsCompileAssets($path)
    {
        return strpos($path, 'static-dist') !== false;
    }

    private function parseVersionFromPlugin($path)
    {
        $version = '';

        try {
            if (empty($this->plugins['installed_plugins'])) {
                return $version;
            }
            $plugins = array_change_key_case($this->plugins['installed_plugins'], CASE_LOWER);

            $paths = explode('/', $path);
            $path = $paths[1];

            if (array_key_exists($path, $plugins)) {
                return $plugins[$path]['version'];
            }
        } catch (\Exception $e) {
            return $version;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyVersion($path)
    {
        $versionized = sprintf($this->format, ltrim($path, '/'), $this->getVersion($path));
        if ($path && '/' == $path[0]) {
            return '/'.$versionized;
        }

        return $versionized;
    }
}
