<?php namespace monotone;

class Assets {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 10 );

        // dequeue WP Block Library CSS
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
        remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
        add_action( 'wp_enqueue_scripts', [$this, 'remove_wp_block_library_css'], 100 );
    }

    public function remove_wp_block_library_css() {
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
    }

    /**
     * Enqueue scripts for pages using templates defined in
     * class ACF, which use new Vite build system.
     */
    public function enqueue_scripts(): void {
        $css = '/css/index.css';
        $js  = '/js/index.js';

        $css_version = filemtime( THEME_BUILD_PATH . $css );
        $js_version  = filemtime( THEME_BUILD_PATH . $js );

        wp_enqueue_style( 'monotone', THEME_BUILD_URI . $css, [], $css_version );
        wp_enqueue_script( 'monotone', THEME_BUILD_URI . $js, [ 'jquery' ], $js_version, true );
    }

    /**
     * Enqueue admin styles for pages using templates defined in
     * class ACF, which use new Vite build system.
     */
    public function enqueue_admin_scripts(): void {
        $admin_css = '/css/admin.css'; // Admin-specific CSS file
        $admin_js  = '/js/admin.js';   // Admin-specific JS file

        // Use THEME_ADMIN_BUILD_PATH and THEME_ADMIN_BUILD_URI for admin assets
        $admin_css_version = filemtime( THEME_ADMIN_BUILD_PATH . $admin_css );
        $admin_js_version  = filemtime( THEME_ADMIN_BUILD_PATH . $admin_js );

        wp_enqueue_style( 'monotone-admin', THEME_ADMIN_BUILD_URI . $admin_css, [], $admin_css_version );
        wp_enqueue_script( 'monotone-admin', THEME_ADMIN_BUILD_URI . $admin_js, [ 'jquery' ], $admin_js_version, true );
    }

}

new Assets();
