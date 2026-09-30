<?php namespace monotone;

// Instead of using the post type, we'll use the archive title
$title = get_the_archive_title();

$args = [
    'hide_fields' => [
        // 'date',
        // 'title',
        // 'author',
        // 'img',
        // 'excerpt',
        // 'readmore',
        //  [taxonomy-slugs (category, post_tag, etc.)],
    ],
];

get_header();
?>
    <?php if ( have_posts() ) { ?>

    <h1 class="archive-title"><?= $title ?></h1>

    <?php if ( $description = get_the_archive_description() ) { ?>
        <div><?= wp_kses_post( wpautop( $description ) ) ?></div>
    <?php } ?>

    <?php get_template_part( 'parts/article/article-archive', null, $args ); ?>

<?php } else { ?>

    <p><?=
        /* translators: Nothing found text. */
        esc_html__( sprintf( 'No %s found.', $title ), 'monotone' ) ?>
    </p>

<?php } ?>

    <?php get_footer();
