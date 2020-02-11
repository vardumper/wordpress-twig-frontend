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

$routes = [
    '/[page/{page}/]' => Teuton\WordpressTwigFrontend\Frontend\Controllers\HomeController::class,
    '/search/[page/{page}/]' => Teuton\WordpressTwigFrontend\Frontend\Controllers\SearchController::class,
    '/{post_name}/' => Teuton\WordpressTwigFrontend\Frontend\Controllers\PostController::class,
];

$dispatcher = FastRoute\cachedDispatcher(function(FastRoute\RouteCollector $router) use ($routes) {
    foreach($routes as $match => $route_controller) {
        /**
         * We'll create two routes per route – this allows us to work on this in local environments as well as in production
         * without modifying the routes
         * - One to access the site from dev/admin cp server
         * - Another one for production
         */
        $router->addRoute('GET', $match, [$route_controller, 'render']);
        $router->addRoute('GET', "/wp-content/plugins/wordpress-twig-frontend/public$match", [$route_controller, 'render']);
    }
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
// print_r($routeInfo);
// exit;
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
$response->prepare($dice->create(Symfony\Component\HttpFoundation\Request::class));
$response->send();