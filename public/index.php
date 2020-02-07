<?php
/**
 * Short and simple.
 * @tutorial Remember adding .htaccess rule, revoking .htaccess access so WordPress does not overwrite the file
 * @tutorial On Apache, just add a new line RewriteCond %{REQUEST_URI}  !(wordpress-xml-feeds/public) [NC] to WP Rewrite Block, this way the plugin directory is not handled by WP 
 * @tutorial On Nginx, just add a new location /wp-content/plugins/wordpress-xml-feeds/public { try_files $uri $uri/ /wp-content/plugins/wordpress-xml-feeds/public/index.php?$query_string; } inside your server block
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

ini_set('display_startup_errors', '1');
ini_set('display_errors', '1');
error_reporting(-1);
if ((php_sapi_name() === 'cli-server' || php_sapi_name() === 'cli') && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));
define('WPFRONTROOT', dirname(__DIR__));

require_once 'src/Frontend/bootstrap.php';