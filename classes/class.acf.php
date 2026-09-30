<?php namespace monotone;

class ACF {
    public static array $templates = [
        'template-flex-page.php',
        'page-applicant.php'
    ];

    public function __construct() {
        add_action( 'init', [ $this, 'add_options_page' ] );

        // Disable Gutenberg for Flex Page templates (see $templates array above)
        add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
    }


    /**
     * Disable Gutenberg for Flex Page templates.
     *
     * @param $is_enabled
     * @param $post_type
     *
     * @return bool|mixed
     */
    public function disable_gutenberg( $is_enabled, $post_type ) {
        if ( class_exists( 'monotone\DisableGutenberg' ) ) {
            return DisableGutenberg::disable_gutenberg( $is_enabled, $post_type, self::$templates );
        }

        return $is_enabled;
    }

    public function add_options_page() {
        if ( function_exists( 'acf_add_options_page' ) ) {
            acf_add_options_page( [
                'page_title' => 'Theme Options',
                'menu_title' => 'Theme Options',
                'menu_slug'  => 'theme-options',
                'capability' => 'edit_posts',
                'redirect'   => false,
                'position'   => 2,
            ] );
        }
    }


}

new ACF();
