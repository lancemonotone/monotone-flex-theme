<?php namespace monotone;

/**
 * Generate a unique ID for each form and a string containing an aria-label
 * if one was passed to get_search_form() in the args array.
 */
$_unique_id  = wp_unique_id( 'search-form-' );
$_aria_label = ! empty( $args[ 'aria_label' ] ) ? 'aria-label="' . esc_attr( $args[ 'aria_label' ] ) . '"' : '';
?>

<form class="d-flex col-12 py-3" role="search" action="<?= esc_url( home_url( '/' ) ); ?>" method="get" <?= $_aria_label; ?>>
    <label class="visually-hidden" for="<?= esc_attr( $_unique_id ); ?>">
        <?= esc_html__( 'Search', 'monotone' ) ?>
    </label>

    <input type="text" class="form-control me-2"
           placeholder="<?=
           /* translators: Search button text. */
           esc_attr__( 'Search', 'monotone' ); ?>"
           name="s"
           id="<?= esc_attr( $_unique_id ); ?>"
           value="<?= get_search_query(); ?>">

    <button class="btn btn-primary text-nowrap" type="submit">
        <?=
        /* translators: Search button text. */
        esc_html__( 'Search', 'monotone' ); ?>
    </button>
</form>


