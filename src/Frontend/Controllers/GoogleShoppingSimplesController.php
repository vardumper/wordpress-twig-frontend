<?php 
/**
 * Google Shopping Simples Controller - prepares data and passes it to specific template
 *
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

class GoogleShoppingSimplesController {
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
     * 
     * @var FormInterface
     */
    protected $loginForm;

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
        $data = json_decode(file_get_contents(WPFRONTROOT . $this->template->getDataPath()), true);
        
        // no data no feed... :(
        if (empty($data)) {
            $this->response->setContent('404 Not Found');
            $this->response->setStatusCode(404);
            return $this->response;
        }
        
        /** data pre filtering: remove parents of variations **/
        if (isset($data['product'])) {
            foreach($data['product'] as $id => $p) {
                /** regular price **/
                if (isset($p['meta_regular_price_wmcp'])) {
                    $data['product'][$id]['meta_regular_price_wmcp'] = json_decode($p['meta_regular_price_wmcp'], true);
                }
                /** sale price **/
                if (isset($p['meta_sale_price_wmcp']) && strlen($p['meta_sale_price_wmcp']) > 2 ) {
                    $data['product'][$id]['meta_sale_price_wmcp'] = json_decode($p['meta_sale_price_wmcp'], true);
                }
            }
        }
        
        if (isset($data['product_variation'])) {
            foreach($data['product_variation'] as $id => $p) {
                /** regular price **/
                if (isset($p['meta_regular_price_wmcp'])) {
                    $data['product_variation'][$id]['meta_regular_price_wmcp'] = json_decode($p['meta_regular_price_wmcp'], true);
                }
                /** sale price **/
                if (isset($p['meta_sale_price_wmcp']) && strlen($p['meta_sale_price_wmcp']) > 2) {
                    $data['product_variation'][$id]['meta_sale_price_wmcp'] = json_decode($p['meta_sale_price_wmcp'], true);
                }
                /** get attributes of parent and append variation attributes with value to variation **/
                $str = '';
                if (isset($data['product'][$p['post_parent']])) {
                    foreach($data['product'][$p['post_parent']]['product_attributes'] as $key => $attribute) {
                        if ($attribute['is_variation'] == 1 && $attribute['is_visible']) {
                            $str .= '&attribute_'.$key.'='.$p['meta_attribute_'.$key];
                        }
                    }
                    $data['product_variation'][$id]['variation_attributes'] = '?' . ltrim($str, '&');
                }
            }
        }
        
        /**
         * @desc render
         */
        $data['domain'] = $this->request->server->get('HTTP_HOST');
        $this->response->headers->set('Content-Type', 'text/xml;charset=utf8');
        $this->response->setContent($this->template->render('google-shopping-simples.xml', $data));
        return $this->response;
    }
}