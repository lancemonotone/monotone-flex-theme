<?php namespace monotone;

/**
 * Disable Editor
 *
 * @package      EAStarter
 * @author       Bill Erickson
 * @since        1.0.0
 * @license      GPL-2.0+
 **/
class DisableGutenberg {
    /**
     * Disable Gutenberg for ids, post types and templates.
     * We're hijacking the use_block_editor_for_post_type
     * filter to disable Gutenberg for the page template.
     *
     * @param bool $is_enabled
     * @param string $post_type
     * @param array $excluded_templates
     * @param array $excluded_post_types
     * @param array $excluded_ids
     *
     * @return bool
     */
    public static function disable_gutenberg( bool $is_enabled, string $post_type, array $excluded_templates = [], array $excluded_post_types = [], array $excluded_ids = [] ): bool {
        global $post;

        if ( empty( $post ) ) {
            return $is_enabled;
        }

        // Exclude templates
        if ( in_array( get_page_template_slug( $post->ID ), $excluded_templates, true ) ) {
            return false;
        }

        // Exclude post types
        if ( in_array( $post_type, $excluded_post_types ) ) {
            return false;
        }

        // Exclude post IDs
        if ( in_array( $post->ID, $excluded_ids ) ) {
            return false;
        }

        return $is_enabled;
    }
}
