<?php namespace monotone;

/**
 * Template Name: Applicant
 */
// Get applicant post
$applicant_id = get_field( 'applicant' );

// Run a new WP_Query
$query = new \WP_Query( [
    'post_type' => 'applicant',
    'p'         => $applicant_id,
] );

// Get the post
$query->the_post();

get_header(); ?>

<?php get_template_part( 'parts/article/article-single' ); ?>

<?php wp_reset_postdata() ?>

<?php get_footer(); ?>
