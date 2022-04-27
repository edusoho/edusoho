<?php

namespace MarketingMallBundle;

use AppBundle\Common\ExtensionalBundle;
use CorporateTrainingBundle\DependencyInjection\Compiler\ExtensionPass;

class MarketingMallBundle extends ExtensionalBundle
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $directory = $this->getPath() . DIRECTORY_SEPARATOR . 'Biz';
        if (is_dir($directory)) {
            $biz['autoload.aliases'][$this->getName()] = "{$this->getNamespace()}\\Biz";
        }
        $biz['autoload.object_maker.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";
                if ('Biz\\' === substr($namespace, 0, strlen('Biz\\'))) {
                    $ctNamespace = "{$this->getNamespace()}\\{$namespace}";
                    $ctClass = "{$ctNamespace}\\Service\\Impl\\{$name}Impl";
                    if (class_exists($ctClass)) {
                        $class = $ctClass;
                    }
                }

                return new $class($biz);
            };
        };

        $this->container->get('api.resource.manager')->registerApi('MarketingMallBundle\Api');
    }

    public function getParent()
    {
        return 'AppBundle';
    }
}
