<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Command;

use Sensio\Bundle\GeneratorBundle\Model\EntityGeneratorResult;
use Symfony\Component\Console\Tester\CommandTester;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand;

class GenerateDoctrineEntityCommandTest extends GenerateCommandTest
{
    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($entity, $format, $fields) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $format, $fields)
            ->willReturn(new EntityGeneratorResult('', '', ''))
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return array(
            array(array(), "Acme2BlogBundle:Blog/Post\n", array('Blog\\Post', 'annotation', array())),
            array(array('entity' => 'Acme2BlogBundle:Blog/Post'), '', array('Blog\\Post', 'annotation', array())),
            array(array(), "Acme2BlogBundle:Blog/Post\nyml\n\n", array('Blog\\Post', 'yml', array())),
            array(array(), "Acme2BlogBundle:Blog/Post\nyml\ncreated_by\n\n255\nfalse\nfalse\ndescription\ntext\nfalse\ntrue\nupdated_at\ndatetime\ntrue\nfalse\nrating\ndecimal\n5\n3\nfalse\nfalse\n\n", array('Blog\\Post', 'yml', array(
                array('fieldName' => 'createdBy', 'type' => 'string', 'length' => 255, 'columnName' => 'created_by'),
                array('fieldName' => 'description', 'type' => 'text', 'unique' => true, 'columnName' => 'description'),
                array('fieldName' => 'updatedAt', 'type' => 'datetimetz', 'nullable' => true, 'columnName' => 'updated_at'),
                array('fieldName' => 'rating', 'type' => 'decimal', 'precision' => 5, 'scale' => 3, 'columnName' => 'rating'),
            ))),
            // Deprecated, to be removed in 4.0
            array(array('--entity' => 'Acme2BlogBundle:Blog/Post'), '', array('Blog\\Post', 'annotation', array())),
        );
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($entity, $format, $fields) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $format, $fields)
            ->willReturn(new EntityGeneratorResult('', '', ''))
        ;
        $generator
            ->expects($this->any())
            ->method('isReservedKeyword')
            ->will($this->returnValue(false))
        ;

        $tester = new CommandTester($this->getCommand($generator));
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        return array(
            array(array('entity' => 'Acme2BlogBundle:Blog/Post'), array('Blog\\Post', 'annotation', array())),
            array(array('entity' => 'Acme2BlogBundle:Blog/Post', '--format' => 'yml', '--fields' => 'created_by:string(255) updated_by:string(length=128 nullable=true) description:text rating:decimal(precision=7 scale=2)'), array('Blog\\Post', 'yml', array(
                array('fieldName' => 'created_by', 'type' => 'string', 'length' => 255),
                array('fieldName' => 'updated_by', 'type' => 'string', 'length' => 128, 'nullable' => true),
                array('fieldName' => 'description', 'type' => 'text'),
                array('fieldName' => 'rating', 'type' => 'decimal', 'precision' => 7, 'scale' => 2),
            ))),
            // Deprecated, to be removed in 4.0
            array(array('--entity' => 'Acme2BlogBundle:Blog/Post'), array('Blog\\Post', 'annotation', array())),
            array(array('--entity' => 'Acme2BlogBundle:Blog/Post', '--format' => 'yml', '--fields' => 'created_by:string(255) updated_by:string(length=128 nullable=true) description:text rating:decimal(precision=7 scale=2)'), array('Blog\\Post', 'yml', array(
                array('fieldName' => 'created_by', 'type' => 'string', 'length' => 255),
                array('fieldName' => 'updated_by', 'type' => 'string', 'length' => 128, 'nullable' => true),
                array('fieldName' => 'description', 'type' => 'text'),
                array('fieldName' => 'rating', 'type' => 'decimal', 'precision' => 7, 'scale' => 2),
            ))),
        );
    }

    protected function getCommand($generator)
    {
        $command = new GenerateDoctrineEntityCommand();
        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet());
        $command->setGenerator($generator);

        return $command;
    }

    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate', 'isReservedKeyword'))
            ->getMock()
        ;
    }
}
