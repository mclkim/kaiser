<?php

/**
 * Step 1: Require the Kaiser Framework using Composer's autoloader
 *
 * If you are not using Composer, you need to load Kaiser Framework with your own
 * PSR-4 autoloader.
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Step 2: Instantiate a Kaiser application
 *
 * This example instantiates a Kaiser application using
 * its default settings. However, you will usually configure
 * your Kaiser application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new Kaiser\App ();

/**
 * Step 3:
 */
$app->setAppDir ( [ 
		__DIR__ . '/app' 
] );

/**
 * Step 4: Run the Kaiser application
 *
 * This method should be called last. This executes the Kaiser application
 * and returns the HTTP response to the HTTP client.
 */
$app->run ();
