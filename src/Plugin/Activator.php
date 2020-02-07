<?php
/**
 * Plugin Activator
 *
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
declare(strict_types = 1);

/**
 * Fired during plugin activation
 *
 * @link       https://teuton.mx
 * @since      1.0.0
 */

namespace Teuton\WordpressTwigFrontend\Plugin;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @author     David Aguilera <david.aguilera@neliosoftware.com>
 */
class Activator {

	/**
	 * Creates settings needed for backend page of plugin
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    /* general */
// 	    register_setting( $option_group, $option_name, $sanitize_callback );
	    register_setting('wordpress_nosql_frontend_settings',      'wordpress_nosql_frontend_settings');

	    /* posts */
	    register_setting('wordpress_nosql_frontend_posts',         'wordpress_nosql_frontend_posts');
	    register_setting('wordpress_nosql_frontend_posts',         'wordpress_nosql_frontend_post_types');
	    register_setting('wordpress_nosql_frontend_posts',         'wordpress_nosql_frontend_post_fields');
	    register_setting('wordpress_nosql_frontend_posts',         'wordpress_nosql_frontend_post_attachment_fields');
	    
	    /* users */
	    register_setting('wordpress_nosql_frontend_users',         'wordpress_nosql_frontend_users');
	    register_setting('wordpress_nosql_frontend_users',         'wordpress_nosql_frontend_users_fields');
	    register_setting('wordpress_nosql_frontend_users',         'wordpress_nosql_frontend_users_anonymize');
	    
	    /* categories */
	    register_setting('wordpress_nosql_frontend_categories',    'wordpress_nosql_frontend_categories');
	    register_setting('wordpress_nosql_frontend_categories',    'wordpress_nosql_frontend_categories_types');
	    
	    /* media & attachments */
	    register_setting('wordpress_nosql_frontend_media',         'wordpress_nosql_frontend_media_types');
	}

}
