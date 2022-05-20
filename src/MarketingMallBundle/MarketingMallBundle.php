<?php

namespace MarketingMallBundle;

use AppBundle\Common\ExtensionalBundle;
use MarketingMallBundle\Biz\MarketingMallServiceProvider;

class MarketingMallBundle extends ExtensionalBundle
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $directory = $this->getPath().DIRECTORY_SEPARATOR.'Biz';
        if (is_dir($directory)) {
            $biz['autoload.aliases'][$this->getName()] = "{$this->getNamespace()}\\Biz";
        }

        $biz['autoload.marketing_mall.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";
                if ('Biz\\' === substr($namespace, 0, strlen('Biz\\'))) {
                    $marketNamespace = "{$this->getNamespace()}\\{$namespace}";
                    $marketClass = "{$marketNamespace}\\Service\\Impl\\{$name}Impl";
                    if (class_exists($marketClass)) {
                        $class = $marketClass;
                    }
                }

                return new $class($biz);
            };
        };

        $biz->register(new MarketingMallServiceProvider());
        $this->container->get('api.resource.manager')->registerApi('MarketingMallBundle\Api');
    }
}
