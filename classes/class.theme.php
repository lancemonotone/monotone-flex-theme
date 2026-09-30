<?php namespace monotone;

class Theme {
    public function __construct() {
        add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );
        add_filter( 'body_class', [ $this, 'add_body_classes' ] );
        add_filter( 'upload_mimes', [ $this, 'add_svg_upload_support' ] );
        add_action( 'admin_init', [ $this, 'disable_admin_comments' ] );
    }

    public function add_theme_support() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );

        // Disable comments
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );
        // Hide existing comments
        add_filter( 'comments_array', '__return_empty_array', 10, 2 );

        // Remove comments page in menu
        add_action( 'admin_menu', function () {
            remove_menu_page( 'edit-comments.php' );
        } );

        // Remove comments links from admin bar
        add_action( 'init', function () {
            if ( is_admin_bar_showing() ) {
                remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
            }
        } );
    }

    public function disable_admin_comments() {
        // Redirect any user trying to access comments page
        global $pagenow;

        if ( $pagenow === 'edit-comments.php' ) {
            wp_safe_redirect( admin_url() );
            exit;
        }

        // Remove comments metabox from dashboard
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

        // Disable support for comments and trackbacks in post types
        foreach ( get_post_types() as $post_type ) {
            if ( post_type_supports( $post_type, 'comments' ) ) {
                remove_post_type_support( $post_type, 'comments' );
                remove_post_type_support( $post_type, 'trackbacks' );
            }
        }
    }

    public function add_body_classes( $classes ) {
        // Add the page template name if it exists
        if ( is_page_template() ) {
            $classes[] = 'page-template-' . sanitize_html_class( str_replace( '.php', '', get_page_template_slug() ) );
        }

        // Add the host name as a class to target local vs staging vs production
        $classes[] = str_replace( '.', '-', $_SERVER[ 'HTTP_HOST' ] );
        // $classes[] = 'theme-light color-scheme-default';
        $classes[] = 'lang-' . CURRENT_LANG;

        return $classes;
    }

    // allow SVGs to be uploaded to media library
    public function add_svg_upload_support( $mimes ) {
        $mimes[ 'svg' ] = 'image/svg+xml';

        return $mimes;
    }
}

new Theme();
