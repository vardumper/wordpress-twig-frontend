<?php
declare(strict_types = 1);
/**
 * Actually a Product Manager
 *
 * @desc     builds initial json file
 * @todo     consider realtime json updates when products are modified (seems is too much action goign on, because of total sales count etc)
 * @category Class
 * @package  Teuton\WordpressTwigFrontend
 * @license  CC-BY-NC-ND-4.0
 * @author   Erik Pöhler <info@teuton.mx>
 * @link     https://www.teuton.mx/
 */
namespace Teuton\WordpressTwigFrontend\Backend;

class PostManager extends ArrayManager {
    /**
     * @const array
     */
    private const EXCLUDE_META_FIELDS = [
        '_edit_lock',
        '_edit_last',
        'encloseme',
        '_wc_cog_cost',
        '_total_sales',
    ];
    
    /**
     * @const array
     */
    private const PUBLIC_POST_FIELDS = [
        'ancestors',
        'comment_status',
        'guid',
        'filter',
        'page_template',
        'post_author',
        'post_category',
        'post_content',
        'post_date',
        'post_excerpt',
        'post_title',
        'post_name',
        'post_modified',
        'post_mime_type',
        'post_parent',
    ];
    
    /**
     * @const array
     */
    private const PUBLIC_TYPES = [
        'post',
        'page',
    ];
    /* @todo put public types into settings page */
    private const PUBLIC_STATES = [
        'publish',
    ];
    
    /**
     * Adds all posts with all public post types to the XML
     */
    public function build() {
        global $wpdb;
        
        if ($this->is_locked()) {
            echo "is locked. exiting.\n";
            $this->unlock();
            return false;
        }
        echo "Starting Data Export.\n";
        
        foreach(self::PUBLIC_TYPES as $type) {
            $this->lock();
            // first get and create classic posts (parents)
            $sql = sprintf("SELECT ID FROM wp_posts WHERE post_status IN (%s) AND post_type = '$type';", "'".implode("','", self::PUBLIC_STATES)."'");
            $results = $wpdb->get_results( $sql, ARRAY_A);
            
            foreach($results as $post) {
                $wp_post = \WP_Post::get_instance((int) $post['ID']);
                $this->update_post((int) $post['ID'], $wp_post);
            }
            $this->save();
            $this->unlock();
            echo "Finished $type.\n";
        }
    }
    
    public function update_post_status(string $new_status, string $old_status = null, \WP_Post $post = null) : void
    {
        global $post;
        
        // when a post moves to publish and wasn't there yet
        if ($new_status === 'publish' && $old_status !== 'publish') {
            $this->persist_post($post);
        } else if ($old_status === 'publish' && $new_status !== 'publish') {
            $this->delete_post($post->ID);
        }
    }
    
    private function persist_post(\WP_Post $post, array $shipping_prices = []) : bool
    {
        //         global $wpdb, $_wp_additional_image_sizes;
        
        // if this post is a child, and the parent does not exists
        $parent = get_post($post->post_parent);
        if ($parent instanceof \WP_Post) {
            if ($post->post_parent !== 0 && !$this->has_post($parent)) {
                //                 error_log('We need to create the parent post first, it\'s still missing in DOM');
            }
        }
        
        /** @todo take care of frontend plugins modifying the posts content, shortcodes, etc **/
        $data = [];
        $ed = date('Y-m-d H:i:s');
        $data['id'] = $post->ID;
        $data['type'] = $post->post_type;
        if (empty($post->post_excerpt)) {
            $data['post_excerpt'] = $this->create_excerpt($post->post_content);
        }
        $data['post_content'] = $this->preparePostContent($post->post_content);
        $data['post_permalink'] = get_permalink($post);
        $data['post_exported_date'] = $ed;
        $data['post_exported_time'] = strtotime($ed);
        $data['search_content'] = $this->filter_text_for_search($post->post_content);
        $data['search_title'] = $this->filter_text_for_search($post->post_title);
        $data['search_full'] = $this->filter_text_for_search($post->post_title . ' ' . $post->post_content);
        
        // set data
        $post_array = $post->to_array();
        foreach($post_array as $key => $value) {
            if (in_array($key, self::PUBLIC_POST_FIELDS)) {
                switch (gettype($value)) {
                    case 'float':
                    case 'double':
                    case 'string':
                    case 'integer':
                    case 'bool':
                    case 'array':
                        if (!isset($data[$key])) {
                            $data[$key] = $value;
                        }
                        break;
                    default:
                        error_log(sprintf('key %s type %s is not supported. (%s)', $key, gettype($value), print_r($value, true)));
                        break;
                }
            }
        }
        
        $meta_array = get_post_meta($post->ID,'',true);
        foreach($meta_array as $key => $value) {
            $value = $value[0];
            if (!in_array($key, self::EXCLUDE_META_FIELDS)) {
                $skip = false;
                switch(gettype($value)) {
                    case 'string':
                        /** validate string and handle accordingly *
                         * @todo handle arrays as key value pairs and add more nodes
                         */
                        if ($value === 'yes') {
                            $value = '1';
                        } else if ($value === 'no') {
                            $value = '0';
                        } else {
                            /*
                             * check if is json_encoded shit...
                             */
                            try {
                                $decoded = @json_decode($value, true);
                                if ($decoded) {
                                    switch(gettype($decoded)) {
                                        case 'string':
                                            $value = $decoded;
                                            break;
                                        case 'array':
                                            $decoded = array_filter($decoded);
                                            if (empty($decoded)) {
                                                $skip = true;
                                            }
                                            $value = $decoded; // might be empty array tho
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            } catch (Exception $e) {
                                // could not unserilize, so... all good
                            }
                            
                            
                            /*
                             * check if is serialized shit...
                             */
                            try {
                                $unserialized = @unserialize($value);
                                switch(gettype($unserialized)) {
                                    case 'string':
                                        $value = $unserialized;
                                        break;
                                    case 'array':
                                        $unserialized = array_filter($unserialized);
                                        if (empty($unserialized)) {
                                            $skip = true;
                                        }
                                        $value = $unserialized; // might be empty array tho
                                        break;
                                    case 'object':
                                    default:
                                        break;
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                        break;
                    case 'array':
                        $value = array_filter($value); // filter empty stuff
                        if (empty($value)) {
                            $value = null; // skip empty arrays
                        }
                        break;
                    default:
                        break;
                }
                
                if (!is_null($value) && !empty($value) && $skip === false ) {
                    $data[str_replace('__', '_', strtolower('meta_' . $key))] = $value;
                }
            }
        }
        
        ksort($data);
        $this->add_post($data);
        return true;
    }
    
    private function preparePostContent(string $content) : string
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->strictErrorChecking = false;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->omitXmlDeclaration = true;
        $dom->normalizeDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>' . $content . '</body>');
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        
        $clean = new \DOMDocument();
        $clean->strictErrorChecking = false;
        $clean->preserveWhiteSpace = false;
        $clean->formatOutput = false;
        $clean->omitXmlDeclaration = true;
        $clean->loadXML('<div></div>');
        if ($dom->documentElement->childNodes->length) {
            foreach ($dom->documentElement->firstChild->childNodes as $node) {
                switch ($node->nodeName) {
                    case 'pre':
                        // handle code blocks differently
                        $pre = $node;
                        $code = html_entity_decode($pre->firstChild->nodeValue);
                        $hl = new \Highlight\Highlighter();
                        $hl->setAutodetectLanguages(['php', 'html', 'twig', 'sql', 'css', 'scss', 'bash', 'nginx']);
                        $highlighted = $hl->highlightAuto($code);
                        $fragment = $clean->createDocumentFragment();
                        $fragment->appendXML($highlighted->value);
                        $codenode = $clean->createElement('code');
                        $codenode->appendChild($fragment);
                        $codenode->setAttribute('class', "hljs {$highlighted->language}");
                        $prenode = $clean->createElement('pre');
                        $prenode->appendChild($codenode);
                        $clean->documentElement->appendChild($prenode);
                        break;
                    case '#comment':
                    case '#text':
                        // do nothing. get rid of anything thats not html
                        break;
                    default:
                        $newnode = $clean->importNode($node, true);
                        $clean->documentElement->appendChild($newnode);
                        break;
                }
            }
        }
        return $clean->saveXML($clean->documentElement, LIBXML_NOEMPTYTAG|LIBXML_NOXMLDECL );
    }
    
    private function sanitizeHtml(string $string) : string
    {
        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );
        
        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );
        return preg_replace($search, $replace, $string);
    }
    
    private function create_excerpt(string $text) {
        $text = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', '', $text));
        $text = strip_tags($text);
        if (preg_match('/^.{1,260}\b/s', $text, $match))
        {
            return $match[0];
        }
        return substr($text,0,260);
    }
    
    private function filter_text_for_search(string $value) : string
    {
        /** @todo figure out language, guess, analyze, etc. (edge cases:
         * content is not in blog locale
         * file isnt there */
        
        $stop_words = [];
        if (is_file(WPFRONTROOT . '/src/Backend/includes/stop-words.' . substr(get_locale(),0,5) . '.php')) {
            $stop_words = include WPFRONTROOT . '/src/Backend/includes/stop-words.' . substr(get_locale(),0,5) . '.php';
        }
        $search_in = strip_tags($value);
        $search_str = preg_replace('/\b(' . implode('|', $stop_words) . ')\b/','', $search_in);
        $search_str = preg_replace('!\s+!', ' ', $search_str);
        $search_str = str_replace([',','.','-'], ' ', $search_str);
        $search_str = preg_replace('!\s+!', ' ', $search_str);
        return $this->sanitizeHtml($search_str);
    }
    
    public function update_post(int $id, \WP_Post $post ) : bool
    {
        $this->lock();
        try {
            // first remove all instances
            if ($this->has_post($post)) {
                $this->delete_post($post);
            }
            // then add where it belongs
            $this->persist_post($post);
            return true;
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            $this->unlock();
            return false;
        }
    }
    
    public function __destruct()
    {
        $this->unlock();
    }
}