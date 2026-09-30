<?php namespace monotone;

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

get_header(); ?>

    <h1>
        <?php
        printf(
        /* translators: %s: Search term. */
            esc_html__( 'Search Results for "%s"', 'monotone' ),
            '<span class="page-description search-term">' . esc_html( get_search_query() ) . '</span>'
        );
        ?>
    </h1>

    <p><?php
        printf(
            esc_html(
            /* translators: %d: The number of search results. */
                _n(
                    'We found %d result for your search.',
                    'We found %d results for your search.',
                    (int)$wp_query->found_posts,
                    'monotone'
                )
            ),
            (int)$wp_query->found_posts
        );
        ?></p>

    <div class="bg-body-tertiary">
        <div class="container d-flex justify-content-end mb-5">
            <?php get_template_part( 'parts/searchform' ); ?>
        </div>
    </div>

    <?php get_template_part( 'parts/article/article-archive', null, $args ); ?>

<?php get_footer();
