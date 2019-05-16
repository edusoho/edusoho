<?php
require __DIR__ . '/../../bootstrap/bootstrap_install.php';
$loader = new Twig_Loader_Filesystem(__DIR__ . '/help');
$twig   = new Twig_Environment($loader, array(
    'cache' => false
));
help();

function help()
{
    global $twig;
    echo $twig->render('help.html.twig');
}