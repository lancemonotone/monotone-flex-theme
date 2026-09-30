<?php

namespace monotone;

class Shortcodes {
    public static array $shortcodes = [
        // 'expando' => [
        //     'func'     => 'print_expando_shortcode',
        //     'template' => [ 'text' => 'Expando', 'value' => '[expando title="Read more"]Content[/expando]' ]
        // ],
        // 'shortcode_1' => [
        // 	'func'     => 'print_shortcode_1_shortcode',
        // 	'template' => [ 'text' => 'Shortcode 1', 'value' => '[shortcode_1]' ]
        // ],
        'menu'          => [
            'func'     => 'print_nav_menu_shortcode',
            'template' => [ 'text' => 'Navigation Menu', 'value' => '[menu name="" class=""]' ]
        ],
        'button'        => [
            'func'     => 'print_button_shortcode',
            'template' => [ 'text' => 'Button', 'value' => '[button url="#" class="" target=""]Button[/button]' ]
        ],
        'click_to_copy' => [ // This is your new shortcode entry
                             'func'     => 'print_click_to_copy_shortcode', // You will define this function to handle the shortcode
                             'template' => [ 'text' => 'Click to Copy', 'value' => '[click_to_copy]{{content}}[/click_to_copy]' ]
        ],
    ];

    public function __construct() {
        foreach ( self::$shortcodes as $shortcode => $data ) {
            add_shortcode( $shortcode, [ $this, $data[ 'func' ] ] );
        }
    }

    public static function get_shortcodes(): array {
        return self::$shortcodes;
    }

    /**
     * Shortcode that wraps content for click-to-copy functionality
     *
     * @param array $atts
     * @param null $content
     *
     * @return string
     */
    function print_click_to_copy_shortcode( $atts = [], $content = null ): string {
        ob_start();
        echo do_shortcode( $content );
        $shortcode_content = ob_get_clean();

        // Remove empty <p> tags from the content
        $shortcode_content = preg_replace('/<p[^>]*>(?:\s|&nbsp;)*<\/p>/', '', $shortcode_content);

        // Return only the content wrapped inside a div with a specific class
        // This wrapper will be the target for JavaScript to insert content
        return <<<EOD
<div class="click-to-copy-wrapper">
    {$shortcode_content}
</div>
EOD;
    }



    /**
     * Return shortcode with attributes
     * [menu name="" class=""]
     *
     * @param array $atts
     * @param null $content
     *
     * @return bool|string|null
     */
    function print_nav_menu_shortcode( $atts = [], $content = null ): bool|string|null {
        $shortcode_atts = shortcode_atts( [ 'name' => '', 'class' => '' ], $atts );
        $name           = $shortcode_atts[ 'name' ];
        $class          = $shortcode_atts[ 'class' ];

        return wp_nav_menu( [ 'menu' => $name, 'menu_class' => $class, 'echo' => false ] );
    }

    /**
     * Return shortcode with attributes
     * [button url="#" class="" target=""]Button[/button]
     *
     * @param array $atts
     * @param null $content
     *
     * @return string
     */
    function print_button_shortcode( $atts, $content = 'Button' ): string {
        $content = trim( str_replace( [ '<p>', '</p>' ], "", $content ) );

        $atts = shortcode_atts( [
            'url'    => '#',
            'target' => '',
            'class'  => ''
        ], $atts );

        //force content
        if ( ! $content || $content == '' ) {
            $content = 'Button';
        }

        //return
        return <<<EOD
		<a href="{$atts['url']}" class="button {$atts['class']}" target="{$atts['target']}">{$content}</a>
EOD;
    }

    /** NOT USED **/

    /**
     * Return shortcode value defined on Options page
     *
     * @param array $atts
     * @param null $content
     *
     * @return string
     */
    // function print_shortcode_1_shortcode( $atts = [], $content = NULL ): string {
    // 	$return = get_field( 'shortcode_1', 'option' );
    //
    // 	return $return ? $return : '';
    // }

    /**
     * Return shortcode with attributes
     * [expando title="Read more"]Content[/expando]
     *
     * @param array $atts
     * @param null $content
     *
     * @return string
     */
    // function print_expando_shortcode( $atts = [], $content = NULL ): string {
    //     $shortcode_atts = shortcode_atts( [ 'title' => '' ], $atts );
    //     $title          = $shortcode_atts['title'];
    //
    //     return '<div class="expando"><div class="expando-title">' . $title . '</div><p class="expando-content">' . $content . '</p></div>';
    // }
}

new Shortcodes();
