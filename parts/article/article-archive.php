<?php namespace monotone;

/**
 * This template must be used within the loop because it relies on the global $post variable.
 *
 * The template has access to the following variables:
 * $args - array - The arguments passed to the template.
 *
 * You can hide the following fields by passing them in the $args['hide_fields'] array:
 * - date
 * - title
 * - author
 * - img
 * - excerpt
 * - readmore
 * - [taxonomy-slugs (category, post_tag, etc.)]
 *
 * The extra <span> tags are used to keep the grid playing nicely if a field is hidden.
 *
 * @param array $args
 *
 * @return void
 * @since 1.0.0
 * @package monotone
 * @subpackage Blog
 */

$default_hide = [
    // 'date',
    // 'title',
    // 'author',
    // 'img',
    // 'excerpt',
    // 'readmore',
    //  [taxonomy-slugs (category, post_tag, etc.)],
];

$hide = wp_parse_args( $args[ 'hide_fields' ] ?? [], $default_hide );
?>

<div class="articles d-grid column-gap-5">

    <?php while ( have_posts() ) {
        the_post();
        ?>

        <article <?php post_class( 'position-relative mt-0 mb-standard d-grid gap-2' ) ?>>

            <span class="edit-post-link position-absolute top-0 end-0 small d-none bg-white">
                <?= Articles::get_edit_post_link( get_the_ID(), 'pencil' ) ?>
            </span>

            <span class="post-date">
            <?php if ( ! in_array( 'date', $hide ) ) { ?>
                <span class="text-muted small"><?= Articles::get_post_date() ?></span>
            <?php } ?>
            </span>

            <span class="post-title">
                <?php if ( ! in_array( 'title', $hide ) ) { ?>
                    <h2 class="fs-3"><?= Articles::get_linked_title() ?></h2>
                <?php } ?>
            </span>

            <span class="featured-image">
            <?php if ( ! in_array( 'img', $hide ) ) { ?>
                <span><?= Articles::get_featured_image() ?></span>
            <?php } ?>
            </span>

            <span class="post-author">
            <?php if ( ! in_array( 'author', $hide ) ) { ?>
                <span class="text-muted small"><?= Articles::get_post_author( null, true ) ?></span>
            <?php } ?>
            </span>

            <span class="post-excerpt">
            <?php if ( ! in_array( 'excerpt', $hide ) ) { ?>
                <p><?= Articles::get_excerpt( get_the_ID(), 30 ) ?></p>
            <?php } ?>
            </span>

            <footer>
                <?php // List taxonomies attached to the post
                if ( $custom_taxonomies = get_object_taxonomies( get_post_type() ) ) {
                    foreach ( $custom_taxonomies as $taxonomy ) {
                        // don't include hidden taxonomies
                        if ( in_array( $taxonomy, $hide ) ) {
                            continue;
                        }

                        if ( $terms = Articles::get_term_links( get_the_ID(), $taxonomy ) ) { ?>
                            <span><?= get_taxonomy( $taxonomy )->label ?>:</span>
                            <span><?= $terms ?></span>
                        <?php }
                    }
                } ?>
            </footer>

            <span class="read-more">
            <?php if ( ! in_array( 'readmore', $hide ) ) { ?>
                <span class="my-1 d-grid"><?= Articles::get_read_more() ?></span>
            <?php } ?>
            </span>

        </article>

    <?php } ?>

</div>
<?php Pagination::get_pagination(); ?>
