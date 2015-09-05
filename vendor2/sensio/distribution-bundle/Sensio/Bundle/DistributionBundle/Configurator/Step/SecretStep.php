<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Configurator\Step;

use Sensio\Bundle\DistributionBundle\Configurator\Form\SecretStepType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Secret Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecretStep implements StepInterface
{
    /**
     * @Assert\NotBlank
     */
    public $secret;

    public function setParameters(array $parameters)
    {
        if (array_key_exists('secret', $parameters)) {
            $this->secret = $parameters['secret'];

            if ('ThisTokenIsNotSoSecretChangeIt' == $this->secret) {
                $this->secret = $this->generateRandomSecret();
            }
        } else {
            $this->secret = $this->generateRandomSecret();
        }
    }

    private function generateRandomSecret()
    {
        return hash('sha1', uniqid(mt_rand()));
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new SecretStepType();
    }

    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        return array();
    }

    /**
     * checkOptionalSettings.
     */
    public function checkOptionalSettings()
    {
        return array();
    }

    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        return array('secret' => $data->secret);
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'SensioDistributionBundle:Configurator/Step:secret.html.twig';
    }
}
