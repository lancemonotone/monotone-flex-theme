<?php namespace monotone;
/*
* Template Name: Flex Page Template
*
* Used to display a page with a flexible content layout.
* Layouts are defined in the ACF field group for the field.
*/
?>

<?php get_header(); ?>

<?php ACF_Flex_Page::get_layout('template_flex_page', 'page-' . get_the_ID()); ?>

<?php get_footer(); ?>
