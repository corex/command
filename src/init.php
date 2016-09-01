<?php

if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}

$paths = [
    dirname(dirname(dirname(__DIR__))),
    dirname(dirname(__DIR__)) . '/vendor',
    dirname(__DIR__ . '/vendor')
];

$loaded = false;
foreach ($paths as $path) {
    if (file_exists($path . '/autoload.php')) {
        require_once($path . '/autoload.php');
        $loaded = true;
        break;
    }
}

// Setup auto-loader.
$throughComposer = true;
if (!$loaded) {
    require_once(__DIR__ . '/Loader.php');
    \CoRex\Command\Loader::initialize();
    $loaded = true;
    $throughComposer = false;
}
