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

$dice->addRule(\Twig\Loader\FilesystemLoader::class, [
    'constructParams' => [ WPFRONTROOT . '/storage/resources/views'],
    'shared' => false,
]);

$dice->addRule(\Twig\Environment::class, [
    'constructParams' => [$dice->create(Twig\Loader\FilesystemLoader::class), [
//         'cache' => WPFRONTROOT . '/storage/cache/views/',
        'debug' => true,
    ]],
    'shared' => false,
]);

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

$request = $dice->create(Symfony\Component\HttpFoundation\Request::class);
$response = $dice->create(Symfony\Component\HttpFoundation\Response::class);