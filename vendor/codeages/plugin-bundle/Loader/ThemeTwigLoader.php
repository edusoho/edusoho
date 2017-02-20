<?php

namespace Codeages\PluginBundle\Loader;


use Codeages\PluginBundle\System\PluginableHttpKernelInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

class ThemeTwigLoader extends \Twig_Loader_Filesystem
{
    /**
     * @var PluginableHttpKernelInterface
     */
    private $kernel;

    public function __construct(PluginableHttpKernelInterface $kernel, FileLocatorInterface $locator, TemplateNameParserInterface $parser)
    {
        $this->kernel = $kernel;
        parent::__construct(array());
    }

    public function findTemplate($template, $throw = true)
    {
        $logicalName = (string) $template;

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $file = null;
        $previous = null;
        try {
            $file = $this->getThemeFile($logicalName);
        } catch (\Twig_Error_Loader $e) {
            $twigLoaderException = $e;

            try{
                $file = $this->getCustomFile($logicalName);
            }catch (\Twig_Error_Loader $exception){

            }

            // for BC
            try {
                $template = $this->parser->parse($template);
                $file = $this->locator->locate($template);
            } catch (\Exception $e) {
            }
        }

        if (false === $file || null === $file) {
            throw $twigLoaderException;
        }

        return $this->cache[$logicalName] = $file;
    }

    protected function getThemeFile($file){
        if($this->isAppResourceFile($file)){
            $themeDir = $this->kernel->getPluginConfigurationManager()->getActiveThemeDirectory();
            $file = $themeDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $file;
        }

        if(is_file($file)){
            return $file;
        }

        throw new \Twig_Error_Loader( sprintf('Unable to find template "%s".', $file));
    }

    protected function getCustomFile($template)
    {
        if($this->isAppResourceFile($template)){
            try{
                if(strpos($template, 'admin') === 0){
                    return $this->getCustomAdminFile($template);
                }else{
                    return $this->getCustomWebFile($template);
                }
            }catch (\InvalidArgumentException $exception){
            }catch (\Twig_Error_Loader $exception){
                throw $exception;
            }
        }

        throw new \Twig_Error_Loader( sprintf('Unable to find template "%s".', $template));
    }

    protected function isAppResourceFile($file)
    {
        return strpos((string) $file, 'Bundle') === false && strpos((string) $file, '@') !== 0;
    }

    private function getCustomAdminFile($template)
    {
        return $this->kernel->locateResource('@CustomAdminBundle/Resources/views/' . $template);
    }

    private function getCustomWebFile($template)
    {
        return $this->kernel->locateResource('@CustomWebBundle/Resources/views/' . $template);
    }
}