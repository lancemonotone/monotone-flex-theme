<?php namespace monotone;

// Get the post type object
$post_type_obj = get_post_type_object( get_post_type() );

// Check if the post type object exists and get the plural label
$plural = $post_type_obj ? $post_type_obj->labels->name : '';

// Translate the plural label
$translated_plural = __( $plural, 'monotone' );

$args = [
    'hide_fields' => [
        'date',
        'title',
        'author',
        // 'img',
        // 'excerpt',
        'readmore',
        //  [taxonomy-slugs (category, post_tag, etc.)],
    ],
];

get_header();
?>
    <?php if ( have_posts() ) { ?>

    <h1><?= esc_html( $translated_plural ) ?></h1>

    <?php if ( $description = get_the_archive_description() ) { ?>
        <div><?= wp_kses_post( wpautop( $description ) ) ?></div>
    <?php } ?>

    <?php get_template_part( 'parts/article/article-archive', null, $args ); ?>

<?php } else { ?>

    <p><?=
        /* translators: Nothing found text. */
        esc_html__( sprintf( 'No %s found.', $translated_plural ), 'monotone' ) ?>
    </p>

<?php } ?>

    <?php get_footer();
