<?php
/**
 * Cronjob logic to refresh product json data
 *
 * @category Class
 * @package  Teuton\WordpressXmlXsltFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);
header('Content-Type: text/plain');
echo php_sapi_name();
// Neither is CLI nor CLI-Server: Execution is forbidden (for HTTP). This is superuser stuff
if (php_sapi_name() !== "cli" && php_sapi_name() !== "cli-server") {
    http_response_code(403);
    echo 'Forbidden.';
    exit; // stop here
}
echo date('Y-m-d H:i:s') . " running ".__FILE__ ." via " . strtoupper(php_sapi_name()) . "\n";

// overwrite default wp error settings with cron specific ones:
ini_set('display_startup_errors', '1');
ini_set('display_errors', '1');
ini_set('error_reporting', 'E_ALL');
error_reporting(E_ALL);

try {
    $autoload = realpath(dirname( __FILE__ ) . '/../vendor/autoload.php');

    if (!file_exists($autoload)) {
        throw new Exception("the plugins' autoload.php file could not be found at $autoload");
    }
    if (!file_exists(realpath(dirname( __DIR__ )) . '/../../../wp-settings.php')) {
        throw new Exception("wp-settings.php not found at ". realpath(dirname( __DIR__ )) . '/web/wp-settings.php');
    }
    
    chdir(dirname( __DIR__ ));

    require_once $autoload;

    define('WP_USE_THEMES', false);
    defined('WPFRONTROOT') or define('WPFRONTROOT', dirname(__DIR__));

    /** Loads the WordPress Environment and Template */
    require (realpath(dirname( __DIR__ )) . '/../../../wp-load.php');

    // overwrite default wp error settings with cron specific ones:
    ini_set('display_startup_errors', '1');
    ini_set('display_errors', '1');
    ini_set('error_reporting', 'E_ALL');
    error_reporting(E_ALL);

    echo 'JSON Export started at ' . date('Y-m-d H:i:s') . "\n";
    $manager = new Teuton\WordpressTwigFrontend\Backend\PostManager();
    $manager->build();
    echo 'JSON Export finished at ' . date('Y-m-d H:i:s') . "\n";
} catch (\Exception $e) {
    echo $e->getMessage()."\n";
}