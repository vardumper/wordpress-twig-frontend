<?php
declare(strict_types = 1);

/**
 * The admin-specific XML functionality of the plugin.
 *
 * @link       https://teuton.mx
 * @since      1.0.0
 */
namespace Teuton\WordpressTwigFrontend\Backend;

class ArrayManager {
    /**
     * @const string PATH
     */
    public const PATH = '/storage/resources/data/';

    /**
     * @const array
     */
    private const PUBLIC_TYPES = [
        'product',
        'product_variation',
        'attachment',
    ];

    /* @todo put public types into settings page */
    private const PUBLIC_STATES = [
        'publish',
    ];

    /* @todo put public types into settings page, distinct over all that exist, then allow by checkbox
     * no need to handle revisions, drafts, auto-drafts, shop_orders, invoices, etc etc.
     */

    /**
     * @var array
     */
    private $data;

    /**
     * Why is the constructor running always? @todo eliminate unneccessary calls
     */
    public function __construct() {
        if (is_file(WPFRONTROOT . self::PATH . 'data.json')) {
            // catch possible decoding error
            try {
                // load existing data
                $this->data = json_decode(file_get_contents(WPFRONTROOT . self::PATH . 'data.json'), true);
            } catch(\Exception $e) {
                error_log('Error occured: ' . $e->getMessage());
                $this->data = [];
            }
        } else {
            //error_log('data.json wasnt found.');
            $this->data = [];
        }
    }

    /**
     * Either
     * @return bool
     */
    protected function is_locked() : bool
    {
        $lock = false;
        if (is_file(WPFRONTROOT . '/locked.tmp')) {
            $last_mod = filemtime(WPFRONTROOT . '/locked.tmp');
            $diff = time() - $last_mod;
            if ($diff < (10 * 60)) {
                $lock = true;
            }
        }
        return $lock;
    }

    /**
     *
     */
    protected function lock() : void
    {
        file_put_contents(WPFRONTROOT . '/locked.tmp', microtime());
    }

    /**
     *
     */
    protected function unlock() : void
    {
        @unlink(WPFRONTROOT . '/locked.tmp');
    }

    /**
     *
     * @param unknown $value
     * @param unknown $modulus
     * @return int
     */
    protected function modulo($value, $modulus) : int
    {
        return ( $value % $modulus + $modulus ) % $modulus;
    }

    public function add_post(array $post) : void
    {
         $this->data[$post['type']][$post['id']] = $post;
    }

    public function update_post_status(string $new_status, string $old_status = null, \WP_Post $post = null) : void
    {
        global $post;
        $shipping_prices = $this->getShippingPricesArray();
        // error_log( 'update_post_status ' . $post->ID );

        // when a post moves to publish and wasn't there yet
        if ($new_status === 'publish' && $old_status !== 'publish') {
            // no need to delete it, it shouldnt be there
            $this->persist_post($post, $shipping_prices);
        } else if ($old_status === 'publish' && $new_status !== 'publish') {
            $this->delete_post($post->ID);
        } else {
            // error_log("weird combination.");
        }
    }

    public function delete_post(\WP_Post $post) : bool
    {
        if (isset($this->data[$post->post_type][$post->ID])) {
            unset($this->data[$post->post_type][$post->ID]);
            return true;
        }
        return false;
    }

    protected function has_post(\WP_Post $post) : bool
    {
        return isset($this->data[$post->post_type][$post->ID]);
    }

    protected function is_public_post_type(\WP_Post $post, array $types = self::PUBLIC_TYPES) : bool
    {
        return in_array($post->post_type, $types);
    }

    protected function is_public_post_status(\WP_Post $post, array $states = self::PUBLIC_STATES) : bool
    {
        return in_array($post->post_status, $states);
    }
    
    /**
     * twice
     */
    public function save() : void
    {
        $this->addOptions();

        file_put_contents(WPFRONTROOT . self::PATH . 'data.php', '<?php return ' . var_export($this->data, true) . '; ?>'); // no need to decode
        file_put_contents(WPFRONTROOT . self::PATH . 'data.json', json_encode($this->data, JSON_PRETTY_PRINT)); // easier to read 
    }
    
    private function addOptions() {
        $relevant_options = [
            'admin_email',
            'avatar_default',
            'avatar_rating',
            'blog_charset',
            'blog_public',
            'blogdescription',
            'blogname',
            'category_base',
            'close_comments_days_old',
            'close_comments_for_old_posts',
            'comment_max_links',
            'comment_moderation',
            'comment_order',
            'comment_registration',
            'comment_whitelist',
            'comments_notify',
            'comments_per_page',
            'date_format',
            'db_version',
            'default_category',
            'default_comment_status',
            'default_comments_page',
            'default_email_category',
            'default_link_category',
            'default_ping_status',
            'default_pingback_flag',
            'default_post_format',
            'default_role',
            'finished_splitting_shared_terms',
            'gmt_offset',
            'hack_file',
            'home',
            'html_type',
            'image_default_align',
            'image_default_link_type',
            'image_default_size',
            'large_size_h',
            'large_size_w',
            'link_manager_enabled',
            'links_updated_date_format',
            'mailserver_login',
            'mailserver_pass',
            'mailserver_port',
            'mailserver_url',
            'medium_large_size_h',
            'medium_large_size_w',
            'medium_size_h',
            'medium_size_w',
            'moderation_notify',
            'page_comments',
            'page_for_posts',
            'page_on_front',
            'permalink_structure',
            'ping_sites',
            'posts_per_page',
            'posts_per_rss',
            'require_name_email',
            'rss_use_excerpt',
            'show_avatars',
            'show_comments_cookies_opt_in',
            'show_on_front',
            'site_icon',
            'siteurl',
            'start_of_week',
            'stylesheet',
            'tag_base',
            'template',
            'thread_comments',
            'thread_comments_depth',
            'thumbnail_crop',
            'thumbnail_size_h',
            'thumbnail_size_w',
            'time_format',
            'timezone_string',
            'upload_path',
            'upload_url_path',
            'uploads_use_yearmonth_folders',
            'use_balanceTags',
            'use_smilies',
            'use_trackback',
            'users_can_register',
            'wp_page_for_privacy_policy',
        ];
        
        foreach($relevant_options as $option_name) {
            if (null !== get_option($option_name, null)) {
                $value = get_option($option_name);

                if (false === boolval($value)) {
                    $value = false;
                }

                if (intval($value) === 1) {
                    $value = true;
                }

                if (is_numeric($value)) {
                    $value = $value + 0;
                }

                if ($value !== '') {
                    $this->data['option'][$option_name] = is_string($value) ? $this->decode($value) : $value;
                }
            }
        }
    }
    
    private function decode(string $string) : string
    {
        if (!is_string($string)) {
            return $string;
        }
        $string = html_entity_decode($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
        if (strpos($string, '&') && html_entity_decode($string) !== $string) {
            die($string);
        }
        return $string;
    }
    
    public function __destruct() {
        if ($this->is_locked()) {
            $this->unlock();
        }
    }
}
