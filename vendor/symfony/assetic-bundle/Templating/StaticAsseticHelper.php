<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Templating;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;

/**
 * The static "assetic" templating helper.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class StaticAsseticHelper extends AsseticHelper
{
    private $packages;
    private $assetsHelper;

    /**
     * Constructor.
     *
     * @param Packages|CoreAssetsHelper $packages The assets packages
     * @param AssetFactory              $factory  The asset factory
     */
    public function __construct($packages, AssetFactory $factory)
    {
        // Symfony <2.7 BC
        if (!$packages instanceof Packages && !$packages instanceof CoreAssetsHelper) {
        }

        if ($packages instanceof Packages) {
            $this->packages = $packages;
        } elseif ($packages instanceof CoreAssetsHelper) {
            $this->assetsHelper = $packages;
        } else {
            throw new \InvalidArgumentException('Argument 1 passed to '.__METHOD__.' must be an instance of Symfony\Component\Asset\Packages or Symfony\Component\Templating\Helper\CoreAssetsHelper, instance of '.get_class($packages).' given');
        }

        parent::__construct($factory);
    }

    protected function getAssetUrl(AssetInterface $asset, $options = array())
    {
        $package = isset($options['package']) ? $options['package'] : null;

        if (null === $this->packages) {
            return $this->assetsHelper->getUrl($asset->getTargetPath(), $package);
        }

        return $this->packages->getPackage($package)->getUrl($asset->getTargetPath());
    }
}
