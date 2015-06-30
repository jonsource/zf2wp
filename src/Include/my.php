<?php

function wp_parse_args( $args, $defaults = '' ) {
    if ( is_object( $args ) )
        $r = get_object_vars( $args );
    elseif ( is_array( $args ) )
        $r =& $args;
    else
        wp_parse_str( $args, $r );

    if ( is_array( $defaults ) )
        return array_merge( $defaults, $r );
    return $r;
}

function wp_parse_str( $string, &$array ) {
    parse_str( $string, $array );
    if ( get_magic_quotes_gpc() )
        $array = stripslashes_deep( $array );
    /**
     * Filter the array of variables derived from a parsed string.
     *
     * @since 2.3.0
     *
     * @param array $array The array populated with variables.
     */
    //$array = apply_filters( 'wp_parse_str', $array );
}

function absint( $maybeint ) {
    return abs( intval( $maybeint ) );
}

function wp_filter_object_list( $list, $args = array(), $operator = 'and', $field = false ) {
    if ( ! is_array( $list ) )
        return array();

    $list = wp_list_filter( $list, $args, $operator );

    if ( $field )
        $list = wp_list_pluck( $list, $field );

    return $list;
}

function do_action($tag, $arg = '') {
    global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;

    if ( ! isset($wp_actions[$tag]) )
        $wp_actions[$tag] = 1;
    else
        ++$wp_actions[$tag];

    // Do 'all' actions first
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return;
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    $args = array();
    if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
        $args[] =& $arg[0];
    else
        $args[] = $arg;
    for ( $a = 2, $num = func_num_args(); $a < $num; $a++ )
        $args[] = func_get_arg($a);

    // Sort
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    do {
        foreach ( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) )
                call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($wp_filter[$tag]) !== false );

    array_pop($wp_current_filter);
}

function do_action_ref_array($tag, $args) {
    global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;

    if ( ! isset($wp_actions[$tag]) )
        $wp_actions[$tag] = 1;
    else
        ++$wp_actions[$tag];

    // Do 'all' actions first
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return;
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    // Sort
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    do {
        foreach( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) )
                call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($wp_filter[$tag]) !== false );

    array_pop($wp_current_filter);
}

function is_admin() {
    if ( isset( $GLOBALS['current_screen'] ) )
        return $GLOBALS['current_screen']->in_admin();
    elseif ( defined( 'WP_ADMIN' ) )
        return WP_ADMIN;

    return false;
}

function get_option( $option, $default = false ) {
    global $wpdb;

    $option = trim( $option );
    if ( empty( $option ) )
        return false;

    /**
     * Filter the value of an existing option before it is retrieved.
     *
     * The dynamic portion of the hook name, `$option`, refers to the option name.
     *
     * Passing a truthy value to the filter will short-circuit retrieving
     * the option value, returning the passed value instead.
     *
     * @since 1.5.0
     *
     * @param bool|mixed $pre_option Value to return instead of the option value.
     *                               Default false to skip it.
     */
    $pre = apply_filters( 'pre_option_' . $option, false );
    if ( false !== $pre )
        return $pre;

    if ( defined( 'WP_SETUP_CONFIG' ) )
        return false;

    if ( ! defined( 'WP_INSTALLING' ) ) {
        // prevent non-existent options from triggering multiple queries
        //TODO: check what this does!!!
        /*$notoptions = wp_cache_get( 'notoptions', 'options' );
        if ( isset( $notoptions[ $option ] ) ) {
            return apply_filters( 'default_option_' . $option, $default );
        }*/
        $notoptions = false;

        $alloptions = wp_load_alloptions();

        if ( isset( $alloptions[$option] ) ) {
            $value = $alloptions[$option];
        } else {
            //TODO: removed cache
            //$value = wp_cache_get( $option, 'options' );
            $value = false;

            if ( false === $value ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );

                // Has to be get_row instead of get_var because of funkiness with 0, false, null values
                if ( is_object( $row ) ) {
                    $value = $row->option_value;
                    wp_cache_add( $option, $value, 'options' );
                } else { // option does not exist, so we must cache its non-existence
                    if ( ! is_array( $notoptions ) ) {
                        $notoptions = array();
                    }
                    $notoptions[$option] = true;
                    //wp_cache_set( 'notoptions', $notoptions, 'options' );

                    /** This filter is documented in wp-includes/option.php */
                    return apply_filters( 'default_option_' . $option, $default );
                }
            }
        }
    } else {
        $suppress = $wpdb->suppress_errors();
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );
        $wpdb->suppress_errors( $suppress );
        if ( is_object( $row ) ) {
            $value = $row->option_value;
        } else {
            /** This filter is documented in wp-includes/option.php */
            return apply_filters( 'default_option_' . $option, $default );
        }
    }

    // If home is not set use siteurl.
    if ( 'home' == $option && '' == $value )
        return get_option( 'siteurl' );

    if ( in_array( $option, array('siteurl', 'home', 'category_base', 'tag_base') ) )
        $value = untrailingslashit( $value );

    /**
     * Filter the value of an existing option.
     *
     * The dynamic portion of the hook name, `$option`, refers to the option name.
     *
     * @since 1.5.0 As 'option_' . $setting
     * @since 3.0.0
     *
     * @param mixed $value Value of the option. If stored serialized, it will be
     *                     unserialized prior to being returned.
     */
    return apply_filters( 'option_' . $option, maybe_unserialize( $value ) );
}

function apply_filters( $tag, $value ) {
    global $wp_filter, $merged_filters, $wp_current_filter;

    $args = array();

    // Do 'all' actions first.
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $args = func_get_args();
        _wp_call_all_hook($args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return $value;
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    // Sort.
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    if ( empty($args) )
        $args = func_get_args();

    do {
        foreach( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) ){
                $args[1] = $value;
                $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
            }

    } while ( next($wp_filter[$tag]) !== false );

    array_pop( $wp_current_filter );

    return $value;
}

function wp_load_alloptions() {
    global $wpdb;

    /*if ( !defined( 'WP_INSTALLING' ) || !is_multisite() )
        $alloptions = wp_cache_get( 'alloptions', 'options' );
    else*/
        $alloptions = false;

    if ( !$alloptions ) {
        //$suppress = $wpdb->suppress_errors();
        if ( !$alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE autoload = 'yes'" ) )
            $alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options" );
        //$wpdb->suppress_errors($suppress);
        $alloptions = array();
        foreach ( (array) $alloptions_db as $o ) {
            $alloptions[$o->option_name] = $o->option_value;
        }
        //TODO:removed cache
        /*if ( !defined( 'WP_INSTALLING' ) || !is_multisite() )
            wp_cache_add( 'alloptions', $alloptions, 'options' );*/
    }

    return $alloptions;
}

function wp_load_translations_early() {

}

function wp_debug_backtrace_summary( $ignore_class = null, $skip_frames = 0, $pretty = true ) {
    if ( version_compare( PHP_VERSION, '5.2.5', '>=' ) )
        $trace = debug_backtrace( false );
    else
        $trace = debug_backtrace();

    $caller = array();
    $check_class = ! is_null( $ignore_class );
    $skip_frames++; // skip this function

    foreach ( $trace as $call ) {
        if ( $skip_frames > 0 ) {
            $skip_frames--;
        } elseif ( isset( $call['class'] ) ) {
            if ( $check_class && $ignore_class == $call['class'] )
                continue; // Filter out calls

            $caller[] = "{$call['class']}{$call['type']}{$call['function']}";
        } else {
            if ( in_array( $call['function'], array( 'do_action', 'apply_filters' ) ) ) {
                $caller[] = "{$call['function']}('{$call['args'][0]}')";
            } elseif ( in_array( $call['function'], array( 'include', 'include_once', 'require', 'require_once' ) ) ) {
                $caller[] = $call['function'] . "('" . str_replace( array( WP_CONTENT_DIR, ABSPATH ) , '', $call['args'][0] ) . "')";
            } else {
                $caller[] = $call['function'];
            }
        }
    }
    if ( $pretty )
        return join( ', ', array_reverse( $caller ) );
    else
        return $caller;
}

function is_multisite() {
    return false;
}

function wp_using_ext_object_cache() {
    return false;
}

function apply_filters_ref_array($tag, $args) {
    global $wp_filter, $merged_filters, $wp_current_filter;

    // Do 'all' actions first
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return $args[0];
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    // Sort
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    do {
        foreach( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) )
                $args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($wp_filter[$tag]) !== false );

    array_pop( $wp_current_filter );

    return $args[0];
}

function get_post_type_object( $post_type ) {
    return new stdClass();
}

function get_current_user_id() {
    return null;
}

function is_user_logged_in() {
    return null;
}

function get_post_stati( $args = array(), $output = 'names', $operator = 'and' ) {
    return array('draft','publish','trash');
}

function maybe_unserialize( $original ) {
    if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize( $original );
    return $original;
}

function is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace )
            return false;
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 )
            return false;
        if ( false !== $brace && $brace < 4 )
            return false;
    }
    $token = $data[0];
    switch ( $token ) {
        case 's' :
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
        // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
    }
    return false;
}

function sanitize_key( $key ) {
    $raw_key = $key;
    $key = strtolower( $key );
    $key = preg_replace( '/[^a-z0-9_\-]/', '', $key );

    /**
     * Filter a sanitized key string.
     *
     * @since 3.0.0
     *
     * @param string $key     Sanitized key.
     * @param string $raw_key The key prior to sanitization.
     */
    return apply_filters( 'sanitize_key', $key, $raw_key );
}

function get_post( $post = null, $output = OBJECT, $filter = 'raw' ) {
    if ( empty( $post ) && isset( $GLOBALS['post'] ) )
        $post = $GLOBALS['post'];

    if ( $post instanceof WP_Post ) {
        $_post = $post;
    } elseif ( is_object( $post ) ) {
        if ( empty( $post->filter ) ) {
            $_post = sanitize_post( $post, 'raw' );
            $_post = new WP_Post( $_post );
        } elseif ( 'raw' == $post->filter ) {
            $_post = new WP_Post( $post );
        } else {
            $_post = WP_Post::get_instance( $post->ID );
        }
    } else {
        $_post = WP_Post::get_instance( $post );
    }

    if ( ! $_post )
        return null;

    $_post = $_post->filter( $filter );

    if ( $output == ARRAY_A )
        return $_post->to_array();
    elseif ( $output == ARRAY_N )
        return array_values( $_post->to_array() );

    return $_post;
}

function sanitize_post( $post, $context = 'display' ) {
    if ( is_object($post) ) {
        // Check if post already filtered for this context.
        if ( isset($post->filter) && $context == $post->filter )
            return $post;
        if ( !isset($post->ID) )
            $post->ID = 0;
        foreach ( array_keys(get_object_vars($post)) as $field )
            $post->$field = sanitize_post_field($field, $post->$field, $post->ID, $context);
        $post->filter = $context;
    } else {
        // Check if post already filtered for this context.
        if ( isset($post['filter']) && $context == $post['filter'] )
            return $post;
        if ( !isset($post['ID']) )
            $post['ID'] = 0;
        foreach ( array_keys($post) as $field )
            $post[$field] = sanitize_post_field($field, $post[$field], $post['ID'], $context);
        $post['filter'] = $context;
    }
    return $post;
}

function sanitize_post_field($field, $value, $post_id, $context) {
    $int_fields = array('ID', 'post_parent', 'menu_order');
    if ( in_array($field, $int_fields) )
        $value = (int) $value;

    // Fields which contain arrays of integers.
    $array_int_fields = array( 'ancestors' );
    if ( in_array($field, $array_int_fields) ) {
        $value = array_map( 'absint', $value);
        return $value;
    }

    if ( 'raw' == $context )
        return $value;

    $prefixed = false;
    if ( false !== strpos($field, 'post_') ) {
        $prefixed = true;
        $field_no_prefix = str_replace('post_', '', $field);
    }

    if ( 'edit' == $context ) {
        $format_to_edit = array('post_content', 'post_excerpt', 'post_title', 'post_password');

        if ( $prefixed ) {

            /**
             * Filter the value of a specific post field to edit.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value   Value of the post field.
             * @param int   $post_id Post ID.
             */
            $value = apply_filters( "edit_{$field}", $value, $post_id );

            /**
             * Filter the value of a specific post field to edit.
             *
             * The dynamic portion of the hook name, `$field_no_prefix`, refers to
             * the post field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value   Value of the post field.
             * @param int   $post_id Post ID.
             */
            $value = apply_filters( "{$field_no_prefix}_edit_pre", $value, $post_id );
        } else {
            $value = apply_filters( "edit_post_{$field}", $value, $post_id );
        }

        if ( in_array($field, $format_to_edit) ) {
            if ( 'post_content' == $field )
                $value = format_to_edit($value, user_can_richedit());
            else
                $value = format_to_edit($value);
        } else {
            $value = esc_attr($value);
        }
    } elseif ( 'db' == $context ) {
        if ( $prefixed ) {

            /**
             * Filter the value of a specific post field before saving.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value Value of the post field.
             */
            $value = apply_filters( "pre_{$field}", $value );

            /**
             * Filter the value of a specific field before saving.
             *
             * The dynamic portion of the hook name, `$field_no_prefix`, refers
             * to the post field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value Value of the post field.
             */
            $value = apply_filters( "{$field_no_prefix}_save_pre", $value );
        } else {
            $value = apply_filters( "pre_post_{$field}", $value );

            /**
             * Filter the value of a specific post field before saving.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value Value of the post field.
             */
            $value = apply_filters( "{$field}_pre", $value );
        }
    } else {

        // Use display filters by default.
        if ( $prefixed ) {

            /**
             * Filter the value of a specific post field for display.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed  $value   Value of the prefixed post field.
             * @param int    $post_id Post ID.
             * @param string $context Context for how to sanitize the field. Possible
             *                        values include 'raw', 'edit', 'db', 'display',
             *                        'attribute' and 'js'.
             */
            $value = apply_filters( $field, $value, $post_id, $context );
        } else {
            $value = apply_filters( "post_{$field}", $value, $post_id, $context );
        }
    }

    if ( 'attribute' == $context )
        $value = esc_attr($value);
    elseif ( 'js' == $context )
        $value = esc_js($value);

    return $value;
}

final class WP_Post {

    /**
     * Post ID.
     *
     * @var int
     */
    public $ID;

    /**
     * ID of post author.
     *
     * A numeric string, for compatibility reasons.
     *
     * @var string
     */
    public $post_author = 0;

    /**
     * The post's local publication time.
     *
     * @var string
     */
    public $post_date = '0000-00-00 00:00:00';

    /**
     * The post's GMT publication time.
     *
     * @var string
     */
    public $post_date_gmt = '0000-00-00 00:00:00';

    /**
     * The post's content.
     *
     * @var string
     */
    public $post_content = '';

    /**
     * The post's title.
     *
     * @var string
     */
    public $post_title = '';

    /**
     * The post's excerpt.
     *
     * @var string
     */
    public $post_excerpt = '';

    /**
     * The post's status.
     *
     * @var string
     */
    public $post_status = 'publish';

    /**
     * Whether comments are allowed.
     *
     * @var string
     */
    public $comment_status = 'open';

    /**
     * Whether pings are allowed.
     *
     * @var string
     */
    public $ping_status = 'open';

    /**
     * The post's password in plain text.
     *
     * @var string
     */
    public $post_password = '';

    /**
     * The post's slug.
     *
     * @var string
     */
    public $post_name = '';

    /**
     * URLs queued to be pinged.
     *
     * @var string
     */
    public $to_ping = '';

    /**
     * URLs that have been pinged.
     *
     * @var string
     */
    public $pinged = '';

    /**
     * The post's local modified time.
     *
     * @var string
     */
    public $post_modified = '0000-00-00 00:00:00';

    /**
     * The post's GMT modified time.
     *
     * @var string
     */
    public $post_modified_gmt = '0000-00-00 00:00:00';

    /**
     * A utility DB field for post content.
     *
     *
     * @var string
     */
    public $post_content_filtered = '';

    /**
     * ID of a post's parent post.
     *
     * @var int
     */
    public $post_parent = 0;

    /**
     * The unique identifier for a post, not necessarily a URL, used as the feed GUID.
     *
     * @var string
     */
    public $guid = '';

    /**
     * A field used for ordering posts.
     *
     * @var int
     */
    public $menu_order = 0;

    /**
     * The post's type, like post or page.
     *
     * @var string
     */
    public $post_type = 'post';

    /**
     * An attachment's mime type.
     *
     * @var string
     */
    public $post_mime_type = '';

    /**
     * Cached comment count.
     *
     * A numeric string, for compatibility reasons.
     *
     * @var string
     */
    public $comment_count = 0;

    /**
     * Stores the post object's sanitization level.
     *
     * Does not correspond to a DB field.
     *
     * @var string
     */
    public $filter;

    /**
     * Retrieve WP_Post instance.
     *
     * @static
     * @access public
     *
     * @global wpdb $wpdb
     *
     * @param int $post_id Post ID.
     * @return WP_Post|false Post object, false otherwise.
     */
    public static function get_instance( $post_id ) {
        global $wpdb;

        $post_id = (int) $post_id;
        if ( ! $post_id )
            return false;

        $_post = wp_cache_get( $post_id, 'posts' );

        if ( ! $_post ) {
            $_post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $post_id ) );

            if ( ! $_post )
                return false;

            $_post = sanitize_post( $_post, 'raw' );
            wp_cache_add( $_post->ID, $_post, 'posts' );
        } elseif ( empty( $_post->filter ) ) {
            $_post = sanitize_post( $_post, 'raw' );
        }

        return new WP_Post( $_post );
    }

    /**
     * Constructor.
     *
     * @param WP_Post|object $post Post object.
     */
    public function __construct( $post ) {
        foreach ( get_object_vars( $post ) as $key => $value )
            $this->$key = $value;
    }

    /**
     * Isset-er.
     *
     * @param string $key Property to check if set.
     * @return bool
     */
    public function __isset( $key ) {
        if ( 'ancestors' == $key )
            return true;

        if ( 'page_template' == $key )
            return ( 'page' == $this->post_type );

        if ( 'post_category' == $key )
            return true;

        if ( 'tags_input' == $key )
            return true;

        return metadata_exists( 'post', $this->ID, $key );
    }

    /**
     * Getter.
     *
     * @param string $key Key to get.
     * @return mixed
     */
    public function __get( $key ) {
        if ( 'page_template' == $key && $this->__isset( $key ) ) {
            return get_post_meta( $this->ID, '_wp_page_template', true );
        }

        if ( 'post_category' == $key ) {
            if ( is_object_in_taxonomy( $this->post_type, 'category' ) )
                $terms = get_the_terms( $this, 'category' );

            if ( empty( $terms ) )
                return array();

            return wp_list_pluck( $terms, 'term_id' );
        }

        if ( 'tags_input' == $key ) {
            if ( is_object_in_taxonomy( $this->post_type, 'post_tag' ) )
                $terms = get_the_terms( $this, 'post_tag' );

            if ( empty( $terms ) )
                return array();

            return wp_list_pluck( $terms, 'name' );
        }

        // Rest of the values need filtering.
        if ( 'ancestors' == $key )
            $value = get_post_ancestors( $this );
        else
            $value = get_post_meta( $this->ID, $key, true );

        if ( $this->filter )
            $value = sanitize_post_field( $key, $value, $this->ID, $this->filter );

        return $value;
    }

    /**
     * {@Missing Summary}
     *
     * @param string $filter Filter.
     * @return self|array|bool|object|WP_Post
     */
    public function filter( $filter ) {
        if ( $this->filter == $filter )
            return $this;

        if ( $filter == 'raw' )
            return self::get_instance( $this->ID );

        return sanitize_post( $this, $filter );
    }

    /**
     * Convert object to array.
     *
     * @return array Object as array.
     */
    public function to_array() {
        $post = get_object_vars( $this );

        foreach ( array( 'ancestors', 'page_template', 'post_category', 'tags_input' ) as $key ) {
            if ( $this->__isset( $key ) )
                $post[ $key ] = $this->__get( $key );
        }

        return $post;
    }
}