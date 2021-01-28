<?php

namespace CustomBundle\Biz;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CustomServiceProvider implements ServiceProviderInterface
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
        foreach ($serviceAliases as $serviceAlias){
            $biz["@{$serviceAlias}"] = $biz->service("CustomBundle:{$serviceAlias}");
        }
    }

    private function rewriteDao($biz)
    {
        $daoAliases = $this->getRewriteDaoAlias();
        //rewrite service
        foreach ($daoAliases as $daoAlias){
            $biz["@{$daoAlias}"] = $biz->dao("CustomBundle:{$daoAlias}");
        }
    }

    private function getRewriteServiceAlias()
    {
        return array(
            'Course:CourseService'
        );
    }

    private function getRewriteDaoAlias()
    {
        return array(
            'Course:CourseDao'
        );
    }
}
