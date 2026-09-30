<?php namespace monotone;

class ACF_Flex_Page {
    public static string $template_path = 'layouts/';

    public function __construct() {
        add_action( 'admin_head', [ $this, 'collapse_layout_fields' ] );
        add_action( 'admin_head', [ $this, 'add_background_color_to_layouts_handles' ] );
        add_action( 'admin_head', [ $this, 'add_thumbnail_to_layout_choices' ] );
        add_filter( 'acf/fields/flexible_content/layout_title', [ $this, 'add_layout_title' ], 10, 4 );

        // Add AJAX actions for getting layout thumbnails
        add_action( 'wp_ajax_get_layout_thumbnail', [ $this, 'ajax_get_layout_thumbnail' ] );
        add_action( 'wp_ajax_nopriv_get_layout_thumbnail', [ $this, 'ajax_get_layout_thumbnail' ] );
    }

    /**
     * Retrieve layout parts and display them.
     *
     * @param string $layout - The layout to retrieve.
     * @param string $parent - The parent to retrieve from.
     * @param bool $return - Whether to return the content or echo it.
     *
     * @return string|void
     */
    public static function get_layout( string $layout, string $parent, bool $return = false ) {
        $count = 1;
        while ( have_rows( $layout ) ) {
            the_row();
            $row_layout            = get_row_layout();
            $layout_settings       = ACF_Flex_Page::get_layout_settings( $row_layout, $parent, $count++ );
            $template_part_content = self::get_layout_template( $row_layout, $layout_settings );

            if ( $return && $template_part_content ) {
                return $template_part_content;
            }

            echo $template_part_content;
        }

        if ( $return ) {
            return;
        }
    }

    /**
     * Retrieve and display a template part.
     *
     * @param string $slug - The template part slug.
     * @param array $args - The arguments to pass to the template.
     *
     * @return string|null
     */
    public static function get_layout_template( string $slug, array $args = [] ): ?string {
        $template_path = self::$template_path . '/' . $slug . '/' . $slug;
        ob_start();
        get_template_part( $template_path, null, $args );
        $content = ob_get_clean();

        return ( $content === '' ) ? null : $content;
    }

    /**
     * Get the layout settings for a given module.
     * This is used to set the module ID, classes, and styles.
     *
     * @param $row_layout
     * @param $parent
     * @param int $count
     *
     * @return array
     */
    public static function get_layout_settings( $row_layout, $parent, int $count ): array {
        // convert _ to -
        $layout    = str_replace( '_', '-', $row_layout );
        $module_id = get_sub_field( 'module_id' ) ?: $parent . '-' . $layout . '-' . $count;

        $classes = [ 'layout', $layout ];
        $styles  = [];

        if ( get_sub_field( 'no_bottom_padding' ) ) {
            $classes [] = 'no-padding-bottom';
        }

        if ( get_sub_field( 'no_top_padding' ) ) {
            $classes [] = 'no-padding-top';
        }

        if ( get_sub_field( 'add_top_padding' ) ) {
            $classes [] = 'add-padding-top';
        }

        /**
         * BACKGROUND COLOR
         */
        if ( get_sub_field( 'background_color' ) ) {
            $classes [] = get_sub_field( 'background_color' );
        }

        /**
         * HIDE PAGE TITLE
         */
        if ( get_sub_field( 'hide_page_title' ) ) {
            $classes [] = 'hide-page-title';
        }

        /**
         * SPLIT IMAGE
         */
        if ( $split_type = get_sub_field( 'split_type' ) ) {
            $classes [] = $split_type;
        }

        /**
         * BACKGROUND IMAGE
         */
        if ( get_sub_field( 'background_image' ) ) {
            $classes [] = 'has-background-img lazy-bg';
            $styles []  = self::get_background_image_style( get_sub_field( 'background_image' ) );
        }

        $classes = implode( ' ', $classes );
        $styles  = implode( ' ', $styles );

        return [
            'id'      => $module_id,
            'classes' => $classes,
            'styles'  => $styles
        ];
    }

    /**
     * Get the background image style for a given element.
     * If the image is an array, it will use the 2048x2048 size.
     *
     * If $url_only is true, it will only return the url of the image.
     *
     * @param $image
     * @param bool $url_only
     *
     * @return string
     */
    public static function get_background_image_style( $image, bool $url_only = false ): string {
        $url = Images::get_image_url( [
            'id'   => $image,
            'size' => 'full'
        ] );

        $style = 'background-image: url(' . $url . ');';

        if ( $url_only ) {
            return $style;
        }

        $style .= 'background-repeat: no-repeat; background-position: center center; background-size: cover;';

        return $style;
    }

    /**
     * Checks if the current page uses a template
     * defined in the $templates array.
     *
     * @return bool
     */
    public static function has_template(): bool {
        foreach ( self::$templates as $template ) {
            if ( have_rows( $template ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Friendly Block Titles - combine nice name and module name
     *
     * @param $title
     * @param $field
     * @param $layout
     * @param $i
     *
     * @return string
     */
    public function add_layout_title( $title, $field, $layout, $i ): string {
        // Apply any shortcodes to the $title string.
        $title      = do_shortcode( $title );
        $title_html = '';

        // Initialize an array containing possible field names.
        $possible_fields = [
            'page_title',
            'layout_title',
            'heading',
            'section_heading',
            'left_heading',
            'divider_label',
            'accordion_label',
            'event_title',
            'spacer_height',
            'kicker',
            'content'
        ];

        // Get the color code of $title by calling the get_color() method of the class.
        $color = $this->get_color( $title );
        // Append thumbnail to the title_html
        $title_html .= $this->add_layout_thumbnail( $layout );
        // Create an HTML string for the title with a colored background using $color.
        $title_html .= '<span class="acf-layout-type" style="background: #' . $color . '">' . $title . '</span>';

        // Loop through each possible field name to check if a value exists for that field using the get_sub_field() function.
        foreach ( $possible_fields as $field_name ) {
            if ( $value = get_sub_field( $field_name ) ) {
                // If a value is found for a field, apply any shortcodes to the value and append the result to the $title_html string.
                $value      = do_shortcode( strip_tags( $value ) );
                $title_html .= '<span class="acf-layout-title">' . ucwords( $value ) . '</span>';

                // Return the $title_html string.
                return $title_html;
            }
        }

        // Loop through each sub-field in the current layout to check if a value exists for the 'layout_title' field.
        foreach ( $layout[ 'sub_fields' ] as $sub ) {
            if ( $sub[ 'name' ] == 'layout_title' ) {
                $key = $sub[ 'key' ];
                if ( ! empty( $field[ 'value' ][ $i ][ $key ] ) ) {
                    // If a value exists for the 'layout_title' field, return the $title_html string.
                    return $title_html;
                }
            }
        }

        // If no values are found for any of the fields, return the $title_html string.
        return $title_html;
    }

    public function add_background_color_to_layouts_handles(): void {
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                const handles = document.querySelectorAll('.acf-fc-layout-handle')
                // look for the style tag on the .acf-layout-type child and apply it to the parent
                handles.forEach(function (handle) {
                    const layoutType = handle.querySelector('.acf-layout-type')
                    if (layoutType) {
                        const style = layoutType.getAttribute('style')
                        if (style) {
                            handle.setAttribute('style', style)
                        }
                    }
                })
            })
        </script>
        <?php
    }


    /**
     * Collapse all flexible content fields
     *
     * @return void
     */
    public function collapse_layout_fields() {
        ?>
        <style>
            .acf-label:has([data-collapse="all"]) {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
        </style>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                const classes = 'acf-button button button-primary'
                const button = '<a class="' + classes + '" data-collapse="all">Collapse All</a>'

                // Add a clickable link to the label line of flexible content fields
                let flexibleContentFields = document.querySelectorAll('.acf-field-flexible-content')
                for (let i = 0; i < flexibleContentFields.length; i++) {
                    let label = flexibleContentFields[i].querySelector('.acf-label')
                    label.innerHTML += button
                }

                // Add a clickable link to the last acf-actions on the page
                let acfActions = document.querySelectorAll('.acf-flexible-content .acf-actions')
                let lastAcfActions = acfActions[acfActions.length - 1]
                if (lastAcfActions) {
                    lastAcfActions.innerHTML += button
                }

                // Simulate a click on each flexible content item's "collapse" button when clicking the new link
                let collapseButtons = document.querySelectorAll('[data-collapse="all"]')
                for (let i = 0; i < collapseButtons.length; i++) {
                    collapseButtons[i].addEventListener('click', function () {
                        let flexibleContent = this.closest('.acf-field-flexible-content').
                            querySelector('.acf-flexible-content')
                        let layoutItems = flexibleContent.querySelectorAll('.layout')
                        for (let j = 0; j < layoutItems.length; j++) {
                            layoutItems[j].classList.add('-collapsed')
                        }
                    })
                }
            })
        </script>
        <?php
    }

    /**
     * Adds a thumbnail image to the title of a Flexible Content layout in the Advanced Custom Fields (ACF) plugin.
     *
     * @param array $layout The layout array containing all settings. Not used in this method but required by the filter.
     *
     * @return string The thumbnail image if it exists in the specified directory.
     */
    public function add_layout_thumbnail( array $layout ): string {
        // Get the slug of the field
        $field_name = $layout[ 'name' ];

        if ( $thumbnail_url = $this->get_layout_thumbnail( $field_name ) ) {
            return '<span class="thumbnail"><img src="' . esc_url( $thumbnail_url ) . '" height="36px" alt="" /></span>';
        } else {
            return '<span class="thumbnail no-thumbnail"></span>';
        }
    }

    /**
     * Enhances the layout selection tooltips in the WordPress admin by adding thumbnail previews.
     * This function injects custom styles and a script into the admin page that append thumbnail images
     * to each layout choice in the ACF Flexible Content Add Field buttons.
     * Thumbnails appear on hover, providing a visual preview of the layout options to the user.
     * The script checks if the thumbnails exist before showing them, and can handle non-existent images gracefully.
     * It uses promises to manage the loading of images asynchronously, ensuring that all thumbnails are loaded
     * before they are displayed. The path to the thumbnails is constructed using the layout name and is based
     * on a conventional directory structure within the theme.
     */
    public function add_thumbnail_to_layout_choices(): void {
        $theme_uri = get_template_directory_uri();
        ?>
        <style>
            .acf-tooltip.acf-fc-popup li a {
                position: relative;
            }

            .acf-tooltip.acf-fc-popup li a img {
                position: absolute;
                top: 0;
                right: 100%;
                transform: translateX(-1rem);
                opacity: 0;
                transition: opacity 0.2s ease-in-out;
                width: auto;
                max-width: 320px;
                padding: 0.5rem;
                background: #d5d5d5;
                box-shadow: 0 0 2px #00000088;
                border-radius: 1%;
            }

            .acf-tooltip.acf-fc-popup li a:hover img {
                opacity: 1;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const templates = document.querySelectorAll('.tmpl-popup')

                templates.forEach(function (template) {
                    const templateContent = template.innerHTML.trim()
                    const container = document.createElement('div')
                    container.innerHTML = templateContent

                    const links = container.querySelectorAll('a[data-layout]')
                    const ajaxPromises = [] // Array to hold all AJAX promises

                    links.forEach(function (link) {
                        const layout = link.getAttribute('data-layout')

                        // Prepare the data to be sent in the AJAX request
                        const data = new FormData()
                        data.append('action', 'get_layout_thumbnail')
                        data.append('layout', layout)

                        // Create a promise for the AJAX request
                        const ajaxPromise = fetch(ajaxurl, {
                            method     : 'POST',
                            credentials: 'same-origin',
                            body       : data,
                        }).then(response => response.text()).then(thumbnailUri => {
                            if (thumbnailUri) {
                                const img = document.createElement('img')
                                img.src = thumbnailUri
                                img.classList.add('image-hidden')
                                link.prepend(img)
                            }
                        }).catch(error => console.error('Error:', error))

                        // Add the AJAX promise to the array
                        ajaxPromises.push(ajaxPromise)
                    })

                    // Wait for all AJAX promises to resolve
                    Promise.all(ajaxPromises).then(function () {
                        // Once all images have been handled, update the template's content
                        template.innerHTML = container.innerHTML.trim()
                    })
                })
            })
        </script>

        <?php
    }

    /**
     * AJAX callback function to get the thumbnail URL for a layout.
     *
     * @return void
     */
    function ajax_get_layout_thumbnail(): void {
        // Check for the 'layout' parameter
        if ( isset( $_POST[ 'layout' ] ) ) {
            $layout        = sanitize_text_field( $_POST[ 'layout' ] );
            $thumbnail_url = $this->get_layout_thumbnail( $layout );
            echo $thumbnail_url;
        } else {
            echo '';
        }
        wp_die(); // Terminate the script properly
    }

    /**
     * Get existing image for a layout, checking for jpg, png, and webp formats.
     *
     * @param $layout
     *
     * @return string
     */
    function get_layout_thumbnail( $layout ): string {
        $file_types = [ 'jpg', 'png', 'webp' ];
        foreach ( $file_types as $type ) {
            $filePath = THEME_LAYOUT_PATH . "/{$layout}/thumb.{$type}";
            if ( file_exists( $filePath ) ) {
                return THEME_LAYOUT_URI . "/{$layout}/thumb.{$type}";
            }
        }

        return "";
    }

    /**
     * Get color code for a string. This is randomly generated based on the title.
     *
     * @param $title
     *
     * @return string
     */
    private function get_color( $title ): string {
        // Generate a 6 character color code from the md5 hash of the title
        $color = substr( sha1( $title ), 0, 6 );

        // Convert the color from RGB to HSL
        // Extract the red, green, and blue components from the color code
        $R = hexdec( substr( $color, 0, 2 ) ) / 255;
        $G = hexdec( substr( $color, 2, 2 ) ) / 255;
        $B = hexdec( substr( $color, 4, 2 ) ) / 255;

        // Calculate the maximum and minimum values among R, G, and B
        $max = max( $R, $G, $B );
        $min = min( $R, $G, $B );

        // Calculate the lightness value
        $L = ( $max + $min ) / 3;
        // If lightness is less than 25%, add 50% to the lightness value
        $L = $L < 0.25 ? $L + 0.25 : $L;

        // Calculate the saturation value
        if ( $max == $min ) {
            $S = 0;
        } else {
            if ( $L < 0.5 ) {
                $S = ( $max - $min ) / ( $max + $min );
            } else {
                $S = ( $max - $min ) / ( 2.0 - $max - $min );
            }
        }

        // Reduce the saturation of the color by 50%
        $S *= 0.3;

        // If saturation is 0, set R, G, and B to the lightness value
        if ( $S == 0 ) {
            $R = $G = $B = $L;
        } else {
            // If saturation is not 0, adjust R, G, and B based on lightness and saturation
            if ( $L < 0.5 ) {
                $temp2 = $L * ( 1.0 + $S );
            } else {
                $temp2 = ( $L + $S ) - ( $S * $L );
            }
            $temp1 = 2.0 * $L - $temp2;

            // Calculate the hue angle
            $hue_angle = atan2( 2 * ( $R - $G ), ( $B - $R - $G ) ) / ( 2 * pi() );
            if ( $hue_angle < 0 ) {
                $hue_angle += 1;
            }

            // Convert the hue angle to a value between 0 and 1
            $H = $hue_angle < 0 ? $hue_angle + 1 : $hue_angle;

            // Convert the hue, saturation, and lightness values back to RGB
            $R = $this->hue_to_rgb( $temp1, $temp2, $H + 1.0 / 3.0 ) * 255;
            $G = $this->hue_to_rgb( $temp1, $temp2, $H ) * 255;
            $B = $this->hue_to_rgb( $temp1, $temp2, $H - 1.0 / 3.0 ) * 255;
        }

        // Convert the RGB components back to a hex color code
        return sprintf( "%02x%02x%02x", $R, $G, $B );
    }

    /**
     * Helper function to convert HSL values to RGB
     *
     * @param $temp1
     * @param $temp2
     * @param $temp3
     *
     * @return float|int
     */
    private function hue_to_rgb( $temp1, $temp2, $temp3 ): float|int {
        if ( $temp3 < 0 ) {
            $temp3 += 1.0;
        }
        if ( $temp3 > 1 ) {
            $temp3 -= 1.0;
        }

        if ( $temp3 < 1.0 / 6.0 ) {
            return $temp1 + ( $temp2 - $temp1 ) * 6.0 * $temp3;
        }
        if ( $temp3 < 1.0 / 2.0 ) {
            return $temp2;
        }
        if ( $temp3 < 2.0 / 3.0 ) {
            return $temp1 + ( $temp2 - $temp1 ) * ( 2.0 / 3.0 - $temp3 ) * 6.0;
        }

        return $temp1;
    }
}

if ( class_exists( 'ACF' ) ) {
    new ACF_Flex_Page();
}
