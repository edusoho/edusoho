<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates a Controller inside a bundle.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ControllerGenerator extends Generator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $controller, $routeFormat, $templateFormat, array $actions = array())
    {
        $dir = $bundle->getPath();
        $controllerFile = $dir.'/Controller/'.$controller.'Controller.php';
        if (file_exists($controllerFile)) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $controller));
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle->getName(),
            'format' => array(
                'routing' => $routeFormat,
                'templating' => $templateFormat,
            ),
            'controller' => $controller,
        );

        foreach ($actions as $i => $action) {
            // get the action name without the suffix Action (for the template logical name)
            $actions[$i]['basename'] = substr($action['name'], 0, -6);
            $params = $parameters;
            $params['action'] = $actions[$i];

            // create a template
            $template = $actions[$i]['template'];
            if ('default' == $template) {
                @trigger_error('The use of the "default" keyword is deprecated. Use the real template name instead.', E_USER_DEPRECATED);
                $template = $bundle->getName().':'.$controller.':'.
                    strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr(substr($action['name'], 0, -6), '_', '.')))
                    .'.html.'.$templateFormat;
            }

            if ('twig' == $templateFormat) {
                $this->renderFile('controller/Template.html.twig.twig', $dir.'/Resources/views/'.$this->parseTemplatePath($template), $params);
            } else {
                $this->renderFile('controller/Template.html.php.twig', $dir.'/Resources/views/'.$this->parseTemplatePath($template), $params);
            }

            $this->generateRouting($bundle, $controller, $actions[$i], $routeFormat);
        }

        $parameters['actions'] = $actions;

        $this->renderFile('controller/Controller.php.twig', $controllerFile, $parameters);
        $this->renderFile('controller/ControllerTest.php.twig', $dir.'/Tests/Controller/'.$controller.'ControllerTest.php', $parameters);
    }

    public function generateRouting(BundleInterface $bundle, $controller, array $action, $format)
    {
        // annotation is generated in the templates
        if ('annotation' == $format) {
            return true;
        }

        $file = $bundle->getPath().'/Resources/config/routing.'.$format;
        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $bundle->getPath().'/Resources/config')) {
            self::mkdir($dir);
        }

        $controller = $bundle->getName().':'.$controller.':'.$action['basename'];
        $name = strtolower(preg_replace('/([A-Z])/', '_\\1', $action['basename']));

        if ('yml' == $format) {
            // yaml
            if (!isset($content)) {
                $content = '';
            }

            $content .= sprintf(
                "\n%s:\n    path:     %s\n    defaults: { _controller: %s }\n",
                $name,
                $action['route'],
                $controller
            );
        } elseif ('xml' == $format) {
            // xml
            if (!isset($content)) {
                // new file
                $content = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://symfony.com/schema/routing"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">
</routes>
EOT;
            }

            $sxe = simplexml_load_string($content);

            $route = $sxe->addChild('route');
            $route->addAttribute('id', $name);
            $route->addAttribute('path', $action['route']);

            $default = $route->addChild('default', $controller);
            $default->addAttribute('key', '_controller');

            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($sxe->asXML());
            $content = $dom->saveXML();
        } elseif ('php' == $format) {
            // php
            if (isset($content)) {
                // edit current file
                $pointer = strpos($content, 'return');
                if (!preg_match('/(\$[^ ]*).*?new RouteCollection\(\)/', $content, $collection) || false === $pointer) {
                    throw new \RuntimeException('Routing.php file is not correct, please initialize RouteCollection.');
                }

                $content = substr($content, 0, $pointer);
                $content .= sprintf("%s->add('%s', new Route('%s', array(", $collection[1], $name, $action['route']);
                $content .= sprintf("\n    '_controller' => '%s',", $controller);
                $content .= "\n)));\n\nreturn ".$collection[1].';';
            } else {
                // new file
                $content = <<<EOT
<?php
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

\$collection = new RouteCollection();
EOT;
                $content .= sprintf("\n\$collection->add('%s', new Route('%s', array(", $name, $action['route']);
                $content .= sprintf("\n    '_controller' => '%s',", $controller);
                $content .= "\n)));\n\nreturn \$collection;";
            }
        }

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RuntimeException(sprintf('We cannot write into file "%s", has that file the correct access level?', $file));
            }
        } else {
            throw new \RuntimeException(sprintf('Problems with generating file "%s", did you gave write access to that directory?', $file));
        }
    }

    protected function parseTemplatePath($template)
    {
        $data = $this->parseLogicalTemplateName($template);

        return $data['controller'].'/'.$data['template'];
    }

    protected function parseLogicalTemplateName($logicalName, $part = '')
    {
        if (2 !== substr_count($logicalName, ':')) {
            throw new \RuntimeException(sprintf('The given template name ("%s") is not correct (it must contain two colons).', $logicalName));
        }

        $data = array();

        list($data['bundle'], $data['controller'], $data['template']) = explode(':', $logicalName);

        return $part ? $data[$part] : $data;
    }
}
