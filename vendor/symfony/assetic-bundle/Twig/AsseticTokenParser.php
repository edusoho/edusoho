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

use Assetic\Asset\AssetInterface;
use Assetic\Extension\Twig\AsseticTokenParser as BaseAsseticTokenParser;
use Symfony\Bundle\AsseticBundle\Exception\InvalidBundleException;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Assetic token parser.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticTokenParser extends BaseAsseticTokenParser
{
    /**
     * @var TemplateNameParserInterface|null
     */
    private $templateNameParser;
    private $enabledBundles;

    public function setTemplateNameParser(TemplateNameParserInterface $templateNameParser)
    {
        $this->templateNameParser = $templateNameParser;
    }

    public function setEnabledBundles(array $enabledBundles = null)
    {
        $this->enabledBundles = $enabledBundles;
    }

    public function parse(\Twig_Token $token)
    {
        if ($this->templateNameParser && is_array($this->enabledBundles)) {
            // check the bundle
            $templateRef = null;
            try {
                $templateRef = $this->templateNameParser->parse($this->parser->getStream()->getSourceContext()->getName());
            } catch (\RuntimeException $e) {
                // this happens when the filename isn't a Bundle:* url
                // and it contains ".."
            } catch (\InvalidArgumentException $e) {
                // this happens when the filename isn't a Bundle:* url
                // but an absolute path instead
            }
            $bundle = $templateRef instanceof TemplateReference ? $templateRef->get('bundle') : null;
            if ($bundle && !in_array($bundle, $this->enabledBundles)) {
                throw new InvalidBundleException($bundle, "the {% {$this->getTag()} %} tag", $templateRef->getLogicalName(), $this->enabledBundles);
            }
        }

        return parent::parse($token);
    }

    protected function createBodyNode(AssetInterface $asset, \Twig_Node $body, array $inputs, array $filters, $name, array $attributes = array(), $lineno = 0, $tag = null)
    {
        return new AsseticNode($asset, $body, $inputs, $filters, $name, $attributes, $lineno, $tag);
    }

    protected function createNode(AssetInterface $asset, \Twig_NodeInterface $body, array $inputs, array $filters, $name, array $attributes = array(), $lineno = 0, $tag = null)
    {
        return new AsseticNode($asset, $body, $inputs, $filters, $name, $attributes, $lineno, $tag);
    }
}
