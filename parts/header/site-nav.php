<?php namespace monotone; ?>

<nav class="navbar navbar-expand-lg pt-lg-4" role="navigation" aria-label="<?php
/* translators: Primary Menu text. */
esc_attr_e( 'Primary menu', 'monotone' );
?>">
    <div class="d-flex justify-content-between align-items-center w-100 w-lg-25 w-xl-auto">
        <div class="navbar-brand">
            <?php Branding::get_site_logo(); ?>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-navigation" aria-controls="primary-navigation" aria-expanded="false" aria-label="<?php
        /* translators: Toggle navigation text. */
        esc_attr_e( 'Toggle navigation', 'monotone' ); ?>">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div> <!-- End of flex container -->

    <div class="collapse navbar-collapse" id="primary-navigation"> <!-- Navigation menu -->
        <div class="bg-body-tertiary d-lg-none">
            <div class="container d-flex justify-content-end">
                <?php get_template_part( 'parts/searchform' ); ?>
            </div>
        </div>
        <?php
        wp_nav_menu( [
            'theme_location' => 'primary',
            'depth'          => 2,
            'container'      => false, // Remove the container
            'menu_class'     => 'navbar-nav ms-auto mb-2 mb-lg-0',
            'fallback_cb'    => '\WP_Bootstrap_Navwalker::fallback',
            'walker'         => new \WP_Bootstrap_Navwalker(),
        ] );
        ?>
    </div>
</nav>
