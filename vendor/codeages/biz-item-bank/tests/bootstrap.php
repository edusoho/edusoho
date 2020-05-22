<?php
echo "[exec] bin/phpmig migrate\n";

chdir(dirname(__DIR__));

passthru('bin/phpmig migrate');
