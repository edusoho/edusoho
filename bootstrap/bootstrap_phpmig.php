<?php

use Codeages\Biz\Framework\Dao\MigrationBootstrap;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;

$input = new ArgvInput();
$env = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev');

$request = Request::createFromGlobals();

$kernel = new AppKernel($env, true);
$kernel->setRequest($request);
$kernel->boot();

$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request, 'request');

$biz = $container->get('biz');

$migration = new MigrationBootstrap($biz['db'], $biz['migration.directories']);

return $migration->boot();
