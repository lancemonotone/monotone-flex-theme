<?php

namespace monotone;

class Branding {
	public function __construct() {
		// Add a logo to the WordPress Customizer
		add_action('after_setup_theme', [$this, 'add_primary_custom_logo']);

		// Add a footer logo to the WordPress Customizer
		add_action('customize_register', [$this, 'add_footer_custom_logo']);

		// Add a second logo to the WordPress Customizer
		// add_action( 'customize_register', [ $this, 'add_secondary_custom_logo' ] );
	}

	function add_primary_custom_logo() {
		$defaults = array(
			'height'               => 110,
			'width'                => 278,
			'flex-height'          => TRUE,
			'flex-width'           => TRUE,
			'header-text'          => array('site-title', 'site-description'),
			'unlink-homepage-logo' => TRUE,
		);
		add_theme_support('custom-logo', $defaults);
	}

	/**
	 * Add a footer logo to the WordPress Customizer
	 *
	 * @param $wp_customize
	 */
    function add_footer_custom_logo($wp_customize) {
        $wp_customize->add_setting('footer_logo'); // Add setting for footer logo uploader

        // Updated to use WP_Customize_Media_Control
        $wp_customize->add_control(new \WP_Customize_Media_Control($wp_customize, 'footer_logo', array(
            'label'    => __('Footer Logo'),
            'section'  => 'title_tagline', // Add in default WordPress customizer section
            'settings' => 'footer_logo',
            'mime_type' => 'image', // Ensure only images can be uploaded
            'priority' => 8, // Set the priority so it's higher than the custom-logo
        )));
    }

	/**
	 * Add a second logo to the WordPress Customizer
	 *
	 * @param $wp_customize
	 */
	function add_secondary_custom_logo($wp_customize) {
		$wp_customize->add_setting('second_logo'); // Add setting for second logo uploader
		$wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'second_logo', array(
			'label'    => __('Second Logo'),
			'section'  => 'title_tagline', // Add in default WordPress customizer section
			'settings' => 'second_logo',
			'priority' => 8, // Set the priority so it's higher than the custom-logo
		)));
	}


	/**
	 * Get the site logo
	 *
	 * @param string $which
	 * @param bool $link
	 */
	static function get_site_logo($which = 'custom_logo', $link = true) {
		$logo = get_theme_mod($which);
		if (filter_var($logo, FILTER_VALIDATE_URL)) {
			$attachment_id = attachment_url_to_postid($logo);
		} else {
			$attachment_id = $logo;
		}
		$image = wp_get_attachment_image_src($attachment_id, 'full');
		$image_url = esc_url($image[0]);
		$image_width = $image[1];
		$image_height = $image[2];
		$alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
		if (empty($alt)) {
			// Use site title if no alt is provided.
			$alt = get_bloginfo('name', 'display');
		}
		if ($link) {
			echo <<<EOD
			<a href="/" aria-label="Click to navigate to the homepage">
			EOD;
		}

		echo <<< EOD
		 <img class="img-fluid" src="{$image_url}" width="{$image_width}" height="{$image_height}" alt="{$alt}" />
		EOD;

		if ($link) {
			echo <<<EOD
			</a>
			EOD;
		}
	}
}

new Branding();
