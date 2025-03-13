<?php

namespace AgentBundle\Biz;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AgentServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->rewriteService($biz);
        $this->rewriteDao($biz);
    }

    private function rewriteService($biz)
    {
        $serviceAliases = $this->getRewriteServiceAlias();
        //rewrite service
        foreach ($serviceAliases as $serviceAlias) {
            $biz["@{$serviceAlias}"] = $biz->service("AgentBundle:{$serviceAlias}");
        }
    }

    private function rewriteDao($biz)
    {
        $daoAliases = $this->getRewriteDaoAlias();
        //rewrite service
        foreach ($daoAliases as $daoAlias) {
            $biz["@{$daoAlias}"] = $biz->dao("AgentBundle:{$daoAlias}");
        }
    }

    private function getRewriteServiceAlias()
    {
        return [];
    }

    private function getRewriteDaoAlias()
    {
        return [];
    }
}
