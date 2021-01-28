<?php

namespace Codeages\PluginBundle\Locator;

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Codeages\PluginBundle\System\PluginableHttpKernelInterface;

class ThemeFileLocator extends BaseFileLocator
{
    private $kernel;
    private $path;

    public function __construct(PluginableHttpKernelInterface $kernel, $path = null, array $paths = array())
    {
        $this->kernel = $kernel;
        if (null !== $path) {
            $this->path = $path;
            $paths[] = $path;
        }

        $themeDir = $this->kernel->getPluginConfigurationManager()->getActiveThemeDirectory();
        $paths = array_merge(array($themeDir), $paths);

        parent::__construct($paths);
    }

    public function locate($file, $currentPath = null, $first = true)
    {
        if (isset($file[0]) && '@' === $file[0]) {
            return $this->locateResource($file, $this->path, $first);
        }

        return parent::locate($file, $currentPath, $first);
    }

    protected function locateResource($name, $dir = null, $first = true)
    {
        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath = substr($path, 9);
        $resourceBundle = null;
        $bundles = $this->kernel->getBundle($bundleName, false);
        $files = array();

        $themeDir = $this->kernel->getPluginConfigurationManager()->getActiveThemeDirectory();

        foreach ($bundles as $bundle) {
            $lookupFiles = array();
            if ($themeDir) {
                $lookupFiles[] = sprintf('%s/views/%s/%s', $themeDir, $bundle->getName(), substr($overridePath, 7));
                // @todo this will remove in future.
                $lookupFiles[] = sprintf('%s/%s/%s', $themeDir, $bundle->getName(), substr($overridePath, 1));
            }
            $lookupFiles[] = $dir.'/'.$bundle->getName().$overridePath;

            foreach ($lookupFiles as $file) {
                if ($isResource && file_exists($file)) {
                    if (null !== $resourceBundle) {
                        throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                            $file,
                            $resourceBundle,
                            $dir.'/'.$bundles[0]->getName().$overridePath
                        ));
                    }

                    if ($first) {
                        return $file;
                    }
                    $files[] = $file;
                }
            }

            if (file_exists($file = $bundle->getPath().'/'.$path)) {
                if ($first && !$isResource) {
                    return $file;
                }
                $files[] = $file;
                $resourceBundle = $bundle->getName();
            }
        }

        if (count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }
}
