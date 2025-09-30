<?php

namespace AgentBundle;

use AppBundle\Common\ExtensionalBundle;
use AgentBundle\Biz\AgentServiceProvider;

class AgentBundle extends ExtensionalBundle
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $directory = $this->getPath().DIRECTORY_SEPARATOR.'Biz';
        if (is_dir($directory)) {
            $biz['autoload.aliases'][$this->getName()] = "{$this->getNamespace()}\\Biz";
        }

        $biz['autoload.agent.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";
                if ('Biz\\' === substr($namespace, 0, strlen('Biz\\'))) {
                    $agentNamespace = "{$this->getNamespace()}\\{$namespace}";
                    $agentClass = "{$agentNamespace}\\Service\\Impl\\{$name}Impl";
                    if (class_exists($agentClass)) {
                        $class = $agentClass;
                    }
                }

                return new $class($biz);
            };
        };

        $biz->register(new AgentServiceProvider());
        $this->container->get('api.resource.manager')->registerApi('AgentBundle\Api');
    }
}
