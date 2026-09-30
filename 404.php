<?php namespace monotone;

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header(); ?>

    <h1 class=""><?php
        /* translators: Nothing found text. */
        esc_html_e( 'Nothing here', 'monotone' ); ?></h1>

    <p><?php
        /* translators: Nothing found text. */
        esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'monotone' ); ?></p>

    <div class="bg-body-tertiary">
        <div class="container d-flex justify-content-end">
            <?php get_template_part( 'parts/searchform' ); ?>
        </div>
    </div>

    <?php get_footer();
