<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ConfiguratorController.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConfiguratorController extends ContainerAware
{
    /**
     * @return Response A Response instance
     */
    public function stepAction($index = 0)
    {
        $configurator = $this->container->get('sensio_distribution.webconfigurator');

        $step = $configurator->getStep($index);

        if (!$step instanceof StepInterface) {
            throw new NotFoundHttpException(sprintf('The step "%s" does not exist.', $index));
        }

        $form = $this->container->get('form.factory')->create($step->getFormType(), $step);

        $request = $this->container->get('request');
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $configurator->mergeParameters($step->update($form->getData()));
                $configurator->write();

                ++$index;

                if ($index < $configurator->getStepCount()) {
                    return new RedirectResponse($this->container->get('router')->generate('_configurator_step', array('index' => $index)));
                }

                return new RedirectResponse($this->container->get('router')->generate('_configurator_final'));
            }
        }

        return $this->container->get('templating')->renderResponse($step->getTemplate(), array(
            'form' => $form->createView(),
            'index' => $index,
            'count' => $configurator->getStepCount(),
            'version' => $this->getVersion(),
        ));
    }

    public function checkAction()
    {
        $configurator = $this->container->get('sensio_distribution.webconfigurator');

        // Trying to get as much requirements as possible
        $majors = $configurator->getRequirements();
        $minors = $configurator->getOptionalSettings();

        $url = $this->container->get('router')->generate('_configurator_step', array('index' => 0));

        if (empty($majors) && empty($minors)) {
            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse('SensioDistributionBundle::Configurator/check.html.twig', array(
            'majors' => $majors,
            'minors' => $minors,
            'url' => $url,
            'version' => $this->getVersion(),
        ));
    }

    public function finalAction()
    {
        $configurator = $this->container->get('sensio_distribution.webconfigurator');
        $configurator->clean();

        try {
            $welcomeUrl = $this->container->get('router')->generate('_welcome');
        } catch (\Exception $e) {
            $welcomeUrl = null;
        }

        return $this->container->get('templating')->renderResponse('SensioDistributionBundle::Configurator/final.html.twig', array(
            'welcome_url' => $welcomeUrl,
            'parameters' => $configurator->render(),
            'yml_path' => $this->container->getParameter('kernel.root_dir').'/config/parameters.yml',
            'is_writable' => $configurator->isFileWritable(),
            'version' => $this->getVersion(),
        ));
    }

    protected function getVersion()
    {
        $kernel = $this->container->get('kernel');

        return $kernel::VERSION;
    }
}
