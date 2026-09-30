<?php

$section_heading = get_sub_field( 'section_heading' ) ?? '';
$heading_size    = get_sub_field( 'heading_size' ) ?? 'fs-2';
$content         = get_sub_field( 'content' ) ?? '';

$id      = $args[ 'id' ] ?? '';
$classes = $args[ 'classes' ] ?? '';
$styles  = $args[ 'styles' ] ?? '';
?>

<div id="<?= $id ?>" class="<?= $classes ?>" style="<?= $styles ?>">

    <div class="container">

        <?php if ( $section_heading ) { ?>
            <h3 class="<?= $heading_size ?>"><?= $section_heading; ?></h3>
        <?php } ?>

        <?php if ( $content ) { ?>
            <?= $content ?>
        <?php } ?>

    </div>
</div>
