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

use Assetic\Extension\Twig\AsseticFilterFunction;
use Symfony\Bundle\AsseticBundle\Exception\InvalidBundleException;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Assetic node visitor.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticNodeVisitor implements \Twig_NodeVisitorInterface
{
    private $templateNameParser;
    private $enabledBundles;
    private $useNodeName;

    public function __construct(TemplateNameParserInterface $templateNameParser, array $enabledBundles)
    {
        $this->templateNameParser = $templateNameParser;
        $this->enabledBundles = $enabledBundles;

        $this->useNodeName = version_compare(\Twig_Environment::VERSION, '1.2.0-DEV', '<');
    }

    public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        return $node;
    }

    public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if (!$formula = $this->checkNode($node, $env, $name)) {
            return $node;
        }

        // check the bundle
        $templateRef = $this->templateNameParser->parse($env->getParser()->getStream()->getFilename());
        $bundle = $templateRef instanceof TemplateReference ? $templateRef->get('bundle') : null;
        if ($bundle && !in_array($bundle, $this->enabledBundles)) {
            throw new InvalidBundleException($bundle, "the $name() function", $templateRef->getLogicalName(), $this->enabledBundles);
        }

        list($input, $filters, $options) = $formula;
        $line = $node->getLine();

        // check context and call either asset() or path()
        return new \Twig_Node_Expression_Conditional(
            new \Twig_Node_Expression_GetAttr(
                new \Twig_Node_Expression_Name('assetic', $line),
                new \Twig_Node_Expression_Constant('use_controller', $line),
                new \Twig_Node_Expression_Array(array(), 0),
                \Twig_TemplateInterface::ARRAY_CALL,
                $line
            ),
            new \Twig_Node_Expression_Function(
                $this->useNodeName ? new \Twig_Node_Expression_Name('path', $line) : 'path',
                new \Twig_Node(array(
                    new \Twig_Node_Expression_Constant('_assetic_'.$options['name'], $line),
                )),
                $line
            ),
            new \Twig_Node_Expression_Function(
                $this->useNodeName ? new \Twig_Node_Expression_Name('asset', $line) : 'asset',
                new \Twig_Node(array($node, new \Twig_Node_Expression_Constant(isset($options['package']) ? $options['package'] : null, $line))),
                $line
            ),
            $line
        );
    }

    /**
     * Extracts formulae from filter function nodes.
     *
     * @return array|null The formula
     */
    private function checkNode(\Twig_NodeInterface $node, \Twig_Environment $env, & $name = null)
    {
        if ($node instanceof \Twig_Node_Expression_Function) {
            $name = $this->useNodeName
                ? $node->getNode('name')->getAttribute('name')
                : $node->getAttribute('name');
            if ($env->getFunction($name) instanceof AsseticFilterFunction) {
                $arguments = array();
                foreach ($node->getNode('arguments') as $argument) {
                    $arguments[] = eval('return '.$env->compile($argument).';');
                }

                $invoker = $env->getExtension('assetic')->getFilterInvoker($name);
                $factory = $invoker->getFactory();

                $inputs = isset($arguments[0]) ? (array) $arguments[0] : array();
                $filters = $invoker->getFilters();
                $options = array_replace($invoker->getOptions(), isset($arguments[1]) ? $arguments[1] : array());

                if (!isset($options['name'])) {
                    $options['name'] = $factory->generateAssetName($inputs, $filters);
                }

                return array($inputs, $filters, $options);
            }
        }
    }

    public function getPriority()
    {
        return 0;
    }
}
