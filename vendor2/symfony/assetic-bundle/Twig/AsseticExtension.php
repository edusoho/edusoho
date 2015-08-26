<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Twig;

use Assetic\Extension\Twig\AsseticExtension as BaseAsseticExtension;
use Assetic\Factory\AssetFactory;
use Assetic\ValueSupplierInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Assetic extension.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticExtension extends BaseAsseticExtension
{
    private $useController;
    private $templateNameParser;
    private $enabledBundles;

    public function __construct(AssetFactory $factory, TemplateNameParserInterface $templateNameParser, $useController = false, $functions = array(), $enabledBundles = array(), ValueSupplierInterface $valueSupplier = null)
    {
        parent::__construct($factory, $functions, $valueSupplier);

        $this->useController = $useController;
        $this->templateNameParser = $templateNameParser;
        $this->enabledBundles = $enabledBundles;
    }

    public function getTokenParsers()
    {
        return array(
            $this->createTokenParser('javascripts', 'js/*.js'),
            $this->createTokenParser('stylesheets', 'css/*.css'),
            $this->createTokenParser('image', 'images/*', true),
        );
    }

    public function getNodeVisitors()
    {
        return array(
            new AsseticNodeVisitor($this->templateNameParser, $this->enabledBundles),
        );
    }

    public function getGlobals()
    {
        $globals = parent::getGlobals();
        $globals['assetic']['use_controller'] = $this->useController;

        return $globals;
    }

    private function createTokenParser($tag, $output, $single = false)
    {
        $tokenParser = new AsseticTokenParser($this->factory, $tag, $output, $single, array('package'));
        $tokenParser->setTemplateNameParser($this->templateNameParser);
        $tokenParser->setEnabledBundles($this->enabledBundles);

        return $tokenParser;
    }
}
