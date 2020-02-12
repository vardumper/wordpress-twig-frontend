<?php
/**
 * Home Page Controller
 *
 * @desc
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

namespace Teuton\WordpressTwigFrontend\Frontend\Controllers;

use Teuton\WordpressTwigFrontend\Frontend\Providers\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController {
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Template
     */
    protected $template;
    
    /** @var string */
    protected $post_name;
    /**
     *
     * @var FormInterface
     */
    protected $loginForm;
    
    public function __construct(
        Request $request,
        Response $response,
        Template $template
    ) {
        $this->request = $request;
        $this->response= $response;
        $this->template = $template;
    }
    
    public function render(array $params) : Response
    {
        /**
         * getting data
         */
        $data = include WPFRONTROOT . $this->template->getDataPath();
        if (empty($data)) {
            $this->response->setContent('404 Not Found');
            $this->response->setStatusCode(404);
            return $this->response;
        }

        /**
         * @desc render
         */
        $this->response->setContent($this->template->render('post.twig', ['data' => $data, 'params' => $params]));
        return $this->response;
    }
}