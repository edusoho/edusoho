<?php

namespace Bazinga\Bundle\JsTranslationBundle;

use Bazinga\Bundle\JsTranslationBundle\DependencyInjection\Compiler\TranslationResourceFilesPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bazinga\Bundle\JsTranslationBundle\DependencyInjection\Compiler\AddLoadersPass;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class BazingaJsTranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddLoadersPass());
        $container->addCompilerPass(new TranslationResourceFilesPass());
    }
}
