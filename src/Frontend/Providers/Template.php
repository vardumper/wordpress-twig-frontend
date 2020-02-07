<?php 
/**
 * Frontend Template Provider
 *
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

namespace Teuton\WordpressTwigFrontend\Frontend\Providers;

use Twig\Environment;

class Template {
    
    public const DATA_PATH = '/storage/resources/data/data.php';
    
    private $environment;

    public function __construct(\Twig\Environment $environment) {
        /** adding template functions here... */
        $this->environment = $environment;
    }

    public function render(string $template, array $data = []) : string 
    {
        return $this->environment->render($template, $data);
    }
    
    public function getDataPath() : string
    {
        return self::DATA_PATH;
    }
}