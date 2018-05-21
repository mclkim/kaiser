<?php
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__FILE__));
defined('BASE_PATH') or define('BASE_PATH', dirname(ROOT_PATH));

date_default_timezone_set('Asia/Seoul'); // 한국시간(timezone)설정

/**
 * Step 1: Require the Kaiser Framework using Composer's autoloader
 */
$autoload = BASE_PATH . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    exit ('You need to execute <strong>composer install</strong>');
}
$loader = require_once $autoload;
//$loader->addPsr4('App\\', BASE_PATH.'/app');
//$loader->addPsr4('Models\\', BASE_PATH.'/models');
//$loader->add('Mcl\\Kaiser\\', BASE_PATH.'/src/');
//var_dump($loader);
/**
 * Step 2: Setting Kaiser Container
 */
$container = new Mcl\Kaiser\Container ();

$container ['MYSQL'] = function ($c) {
    $dbname = 'mysql';
    $user = 'root';
    $pass = '';

    try {
        return new PDO('mysql:host=localhost;dbname=' . $dbname, $user, $pass);
    } catch (PDOException $e) {
        die('Connection failed.' . $e->getMessage());
    }
};

/**
 * Step 3: Instantiate a Kaiser application Controller
 */
$app = new Mcl\Kaiser\App($container);

/**
 * Step 4: Run the Kaiser application
 */
$app->run([BASE_PATH . '/app']);
