<?php namespace monotone; ?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link visually-hidden" href="#content">
    <?php
    /* translators: Hidden accessibility text. */
    esc_html_e( 'Skip to content', 'monotone' );
    ?>
</a>

<div class="site">

    <?php get_template_part( 'parts/header/site-header' ) ?>

    <main id="content" class="mb-tall">
        <div class="container">
