<?php

namespace MarketingMallBundle\Biz;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MarketingMallServiceProvider implements ServiceProviderInterface
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
            $biz["@{$serviceAlias}"] = $biz->service("MarketingMallBundle:{$serviceAlias}");
        }
    }

    private function rewriteDao($biz)
    {
        $daoAliases = $this->getRewriteDaoAlias();
        //rewrite service
        foreach ($daoAliases as $daoAlias) {
            $biz["@{$daoAlias}"] = $biz->dao("MarketingMallBundle:{$daoAlias}");
        }
    }

    private function getRewriteServiceAlias()
    {
        return [
            'ProductMallGoodsRelation:ProductMallGoodsRelationService',
            'Mall:MallService',
            'Role:RoleService',
            'MallAdminProfile:MallAdminProfileService',
        ];
    }

    private function getRewriteDaoAlias()
    {
        return [
            'ProductMallGoodsRelation:ProductMallGoodsRelationDao',
            'MallAdminProfile:MallAdminProfileDao',
        ];
    }
}
