<?php

/**
 * This file is part of Twig.
 *
 * (c) 2014 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class Twig_Extensions_Extension_Date extends Twig_Extension
{
    static $units = array(
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator A TranslatorInterface instance.
     */
    public function __construct(TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('time_diff', array($this, 'diff'), array(
                'needs_environment' => true
            )),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'date';
    }

    /**
     * Filter for converting dates to a time ago string like Facebook and Twitter has.
     *
     * @param Twig_Environment $env  A Twig_Environment instance.
     * @param string|DateTime  $date A string or DateTime object to convert.
     * @param string|DateTime  $now  A string or DateTime object to compare with. If none given, the current time will be used.
     *
     * @return string The converted time.
     */
    public function diff(Twig_Environment $env, $date, $now = null)
    {
        // Convert both dates to DateTime instances.
        $date = twig_date_converter($env, $date);
        $now = twig_date_converter($env, $now);

        // Get the difference between the two DateTime objects.
        $diff = $date->diff($now);

        // Check for each interval if it appears in the $diff object.
        foreach (self::$units as $attribute => $unit) {
            $count = $diff->$attribute;

            if (0 !== $count) {
                return $this->getPluralizedInterval($count, $diff->invert, $unit);
            }
        }

        return '';
    }

    protected function getPluralizedInterval($count, $invert, $unit)
    {
        if ($this->translator) {
            $id = sprintf('diff.%s.%s', $invert ? 'in' : 'ago', $unit);

            return $this->translator->transChoice($id, $count, array('%count%' => $count), 'date');
        }

        if ($count > 1) {
            $unit .= 's';
        }

        return $invert ? "in $count $unit" : "$count $unit ago";
    }
}
