<?php

/**
 * Step 1: Require the Kaiser Framework using Composer's autoloader
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Step 2: Instantiate a Kaiser application
 */
$app = new Kaiser\App ();

/**
 * Step 3: Setting Kaiser application Controller
 */
$app->setAppDir([
    __DIR__ . '/app'
]);

/**
 * Step 4: Run the Kaiser application
 */
$app->run();
