<?php namespace monotone; ?>

<article>

    <h1><?php the_title(); ?></h1>

    <div class="d-flex flex-column row-gap-4 flex-md-row column-gap-md-4 justify-content-md-start">

        <div class="order-md-1 col-md-5">

            <?php if ( has_post_thumbnail() ) { ?>
                <?= Articles::get_featured_image() ?>
            <?php } ?>

        </div>

        <div class="d-flex flex-column gap-3 col-md-6">

            <?php the_content(); ?>

            <footer>
                <?php if ( $custom_taxonomies = get_object_taxonomies( get_post_type() ) ) {
                    foreach ( $custom_taxonomies as $taxonomy ) {
                        if ( $terms = Articles::get_term_links( get_the_ID(), $taxonomy ) ) { ?>
                            <span><?= get_taxonomy( $taxonomy )->label ?>: </span>
                            <span><?= $terms ?></span>
                        <?php }
                    }
                } ?>
            </footer>

        </div>

    </div>

    <?= Articles::paginate_single_post() ?>

</article>
