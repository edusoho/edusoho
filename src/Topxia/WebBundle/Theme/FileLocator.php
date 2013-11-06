<?php
namespace Topxia\WebBundle\Theme;

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

use Topxia\Service\Common\ServiceKernel;

class FileLocator extends BaseFileLocator
{
    private $path;

    private $kernel;

    private $themePath;

    public function __construct(KernelInterface $kernel, $path = null, array $paths = array())
    {
        $this->kernel = $kernel;
        if (null !== $path) {
            $this->path = $path;
            $paths[] = $path;
        }

        try {
            $theme = $this->getSettingService()->get('theme', array());
        } catch (\Exception $e) {
            $theme = array();
        }

        if (!empty($theme['uri'])) {
            $themePath = $this->kernel->getRootDir() . '/../web/themes/' . $theme['uri'];
            if (is_dir($themePath)) {
                $this->themePath = $themePath;
            }
        }

        $paths[] = $this->themePath;

        parent::__construct($paths);
    }

    public function locate($file, $currentPath = null, $first = true)
    {
        if ('@' === $file[0]) {
            return $this->locateResource($file, $this->themePath, $this->path, $first);
        }

        return parent::locate($file, $currentPath, $first);
    }

    /**
     * 此方法修改自Symfony\Component\HttpKernel\Kernel::locateResource，加入了themeDir的目录的查找。
     */
    public function locateResource($name, $themeDir, $dir = null, $first = true)
    {
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

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

        foreach ($bundles as $bundle) {

            if ($themeDir) {
                if ($isResource && file_exists($file = $themeDir.'/'.$bundle->getName().$overridePath)) {
                    if (null !== $resourceBundle) {
                        throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                            $file,
                            $resourceBundle,
                            $themeDir.'/'.$bundles[0]->getName().$overridePath
                        ));
                    }

                    if ($first) {
                        return $file;
                    }
                    $files[] = $file;
                }
            }

            if ($isResource && file_exists($file = $dir.'/'.$bundle->getName().$overridePath)) {
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

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

}
