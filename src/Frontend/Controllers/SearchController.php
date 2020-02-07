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

class SearchController {
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
    
    /**
     * @var FormInterface
     */
    protected $searchForm;
    
    public function __construct(
        Request $request,
        Response $response,
        Template $template
        )
    {
        $this->request = $request;
        $this->response= $response;
        $this->template = $template;
    }
    
    public function render() : Response
    {
        /**
         * getting data
         */
        $data = include WPFRONTROOT . $this->template->getDataPath();
        
        // no data no feed... :(
        if (empty($data)) {
            $this->response->setContent('404 Not Found');
            $this->response->setStatusCode(404);
            return $this->response;
        }

        /**
         * @desc render
         */
        $this->response->setContent($this->template->render('search.twig', $data));
        return $this->response;
    }
}