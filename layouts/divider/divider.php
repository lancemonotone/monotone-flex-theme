<?php

$divider_height = get_sub_field('divider_height');
$divider_label = get_sub_field('divider_label');

$id      = $args['id'] ?? '';
$classes = $args['classes'] ?? '';
$styles  = $args['styles'] ?? '';
?>

<div id="<?= $id ?>" class="<?= $classes ?>" style="<?= $styles ?>">

    <div class="container">

        <div class="<?=$divider_height?> user-select-none position-relative d-flex justify-content-end mt-4 pt-2 border-top border-1 border-primary-light">
            <?php if ($divider_label) { ?>
                <span class="text-uppercase text-primary-light w-90 text-end small"><?= $divider_label ?></span>
            <?php } ?>
        </div>

    </div>

</div>
