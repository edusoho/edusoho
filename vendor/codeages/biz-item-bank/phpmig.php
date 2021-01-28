<?php

$biz = require __DIR__.'/biz.php';

$biz['migration.directories'][] = __DIR__ . '/migrations';

$migration = new \Codeages\Biz\Framework\Dao\MigrationBootstrap($biz['db'], $biz['migration.directories']);

return $migration->boot();
