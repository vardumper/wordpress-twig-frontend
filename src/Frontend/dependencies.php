<?php 
/**
 * Frontend Dependency Injection
 *
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

use Symfony\Component\HttpFoundation\Response;

$dice->addRule(\Twig\Loader\FilesystemLoader::class, [
    'constructParams' => [ WPFRONTROOT . '/storage/resources/views'],
    'shared' => true,
]);


$dice->addRule(\Twig\Environment::class, [
    'constructParams' => [$dice->create(Twig\Loader\FilesystemLoader::class), [
        'cache' => WPFRONTROOT . '/storage/cache/views/',
        'debug' => true,
    ]],
    'shared' => true,
]);

// $dice->addRule(Template::class, [
//     'constructParams' => [ $dice->create(Twig_Environment::class) ],
//     'shared' => true,
// ]);

$dice->addRule(Symfony\Component\HttpFoundation\Request::class, [
    'constructParams' => [
        $_GET,
        $_POST,
        [],
        $_COOKIE,
        $_FILES,
        $_SERVER,
    ],
    'shared' => false,
]);
// $template = $dice->create(Template::class);
$request = $dice->create(Symfony\Component\HttpFoundation\Request::class);
$response = $dice->create(Symfony\Component\HttpFoundation\Response::class);