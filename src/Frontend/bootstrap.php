<?php 
/**
 * Frontend Bootstrap File 
 *
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

require 'vendor/autoload.php';

$environment = 'development';
$whoops = new \Whoops\Run;

if ($environment !== 'production') {
    $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
}
else {
    $whoops->prependHandler(function ($e) {
        echo 'TODO: Friendly error page and send an email?';
    });
}

$whoops->register();

$dice = new \Dice\Dice;

include 'dependencies.php';
include 'routes.php';
