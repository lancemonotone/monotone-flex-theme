<?php

namespace monotone;

class Widgets {

	public function __construct() {
		add_action( 'widgets_init', [ $this, 'init' ] );
	}

	public function init(): void {
		register_sidebar( [
            /* translators: Footer Sidebar text. */
			'name' => __( 'Footer Sidebar', 'monotone'),
			'id' => 'sidebar-footer-1',
            /* translators: Sidebar that appears in the footer text. */
			'description' => __( 'Sidebar that appears in the footer.', 'monotone' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
        ] );
	}
}

new Widgets();
