<?php
/**
 * Frontend Routes
 *
 * @desc     Main Plugin File
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

$dispatcher = FastRoute\cachedDispatcher(function(FastRoute\RouteCollector $router) {
    $router->addRoute('GET','/[page/{page}/]', [Teuton\WordpressTwigFrontend\Frontend\Controllers\HomeController::class, 'render']);
    $router->addRoute(['POST','GET'],'/search/[page/{page}/]', [Teuton\WordpressTwigFrontend\Frontend\Controllers\SearchController::class, 'render']);
    $router->addRoute('GET','/{post_name}/', [Teuton\WordpressTwigFrontend\Frontend\Controllers\PostController::class, 'render']);
}, [ 'cacheFile' => 'storage/cache/route.cache', 'cacheDisabled' => true ]);


$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

foreach ($response->headers->all() as $key => $header) {
    header("$key: $header[0]", false);
}

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $response->setContent(file_get_contents(dirname(__FILE__) .'/../../storage/resources/views/errors/404.html'));
        $response->setStatusCode(404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response->setContent(file_get_contents(dirname(__FILE__) . '/../../storage/resources/views/errors/405.html'));
        $response->setStatusCode(405);
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller = $dice->create($routeInfo[1][0]);
        $response = $controller->{$routeInfo[1][1]}($routeInfo[2]);
        break;
}
$response->prepare($request);
$response->send();