<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Command\AutoComplete;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\GeneratorBundle\Command\AutoComplete\EntitiesAutoCompleter;

class EntitiesAutoCompleterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getNamespaces
     */
    public function testSuggestions($expected, $alias, $classes)
    {
        $autoCompleter = new EntitiesAutoCompleter($this->getEntityManagerMock($alias, $classes));

        $this->assertSame($expected, $autoCompleter->getSuggestions());
    }

    public function getNamespaces()
    {
        return array(
            array(
                array('AcmeBlogBundle:Post'),
                array('AcmeBlogBundle' => 'Acme\Bundle\BlogBundle\Entity'),
                array('Acme\Bundle\BlogBundle\Entity\Post'),
            ),
            array(
                array('AcmeBlogBundle:Blog\Post'),
                array('AcmeBlogBundle' => 'Acme\Bundle\BlogBundle\Entity'),
                array('Acme\Bundle\BlogBundle\Entity\Blog\Post'),
            ),
            array(
                array(
                    'AcmeBlogBundle:Post',
                    'AcmeCommentBundle:Comment',
                    'AcmeBlogBundle:Blog\Post',
                ),
                array(
                    'AcmeBlogBundle' => 'Acme\Bundle\BlogBundle\Entity',
                    'AcmeCommentBundle' => 'Acme\Bundle\CommentBundle\Entity',
                ),
                array(
                    'Acme\Bundle\BlogBundle\Entity\Post',
                    'Acme\Bundle\CommentBundle\Entity\Comment',
                    'Acme\Bundle\BlogBundle\Entity\Blog\Post',
                ),
            ),
        );
    }

    /**
     * @param $aliases
     * @param $classes
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManagerMock($aliases, $classes)
    {
        $cache = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver')->getMock();
        $cache
            ->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue($classes))
        ;

        $configuration = $this->getMockBuilder('Doctrine\ORM\Configuration')->getMock();
        $configuration
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($cache))
        ;

        $configuration
            ->expects($this->any())
            ->method('getEntityNamespaces')
            ->will($this->returnValue($aliases))
        ;

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
        $manager
            ->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration))
        ;

        return $manager;
    }
}
