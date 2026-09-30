<?php namespace monotone;

class Custom_Taxonomies {
    public array $taxonomies = [
        [
            'slug'       => 'species',
            'singular'   => 'Species',
            'plural'     => 'Species',
            'post_types' => [ 'applicant' ],
            'args'       => [
                'public'            => true,
                'show_ui'           => true,
                'meta_box_cb'       => false,
                'show_in_nav_menus' => true,
                'show_tagcloud'     => true,
                'show_admin_column' => true,
                'hierarchical'      => false,
            ]
        ],
        [
            'slug'       => 'experience',
            'singular'   => 'Experience',
            'plural'     => 'Experience',
            'post_types' => [ 'applicant' ],
            'args'       => [
                'public'            => true,
                'show_ui'           => true,
                'meta_box_cb'       => false,
                'show_in_nav_menus' => true,
                'show_tagcloud'     => true,
                'show_admin_column' => true,
                'hierarchical'      => false,
            ]
        ],
        [
            'slug'       => 'skill',
            'singular'   => 'Skill',
            'plural'     => 'Skills',
            'post_types' => [ 'applicant' ],
            'args'       => [
                'public'            => true,
                'show_ui'           => true,
                'meta_box_cb'       => false,
                'show_in_nav_menus' => true,
                'show_tagcloud'     => true,
                'show_admin_column' => true,
                'hierarchical'      => false,
            ]
        ],
    ];

    public function __construct() {
        add_action( 'init', [ $this, 'register_custom_taxonomies' ] );
    }

    public function register_custom_taxonomies(): void {
        foreach ( $this->taxonomies as $taxonomy ) {
            $this->register_taxonomy( $taxonomy );
        }
    }

    public function register_taxonomy( $taxonomy ): void {
        $labels = [
            'name'                       => __( $taxonomy[ 'plural' ], 'monotone' ),
            'singular_name'              => __( $taxonomy[ 'singular' ], 'monotone' ),
            'search_items'               => sprintf( __( 'Search %s', 'monotone' ), $taxonomy[ 'plural' ] ),
            'popular_items'              => sprintf( __( 'Popular %s', 'monotone' ), $taxonomy[ 'plural' ] ),
            'all_items'                  => sprintf( __( 'All %s', 'monotone' ), $taxonomy[ 'plural' ] ),
            'parent_item'                => sprintf( __( 'Parent %s', 'monotone' ), $taxonomy[ 'singular' ] ),
            'parent_item_colon'          => sprintf( __( 'Parent %s:', 'monotone' ), $taxonomy[ 'singular' ] ),
            'edit_item'                  => sprintf( __( 'Edit %s', 'monotone' ), $taxonomy[ 'singular' ] ),
            'update_item'                => sprintf( __( 'Update %s', 'monotone' ), $taxonomy[ 'singular' ] ),
            'add_new_item'               => sprintf( __( 'Add New %s', 'monotone' ), $taxonomy[ 'singular' ] ),
            'new_item_name'              => sprintf( __( 'New %s Name', 'monotone' ), $taxonomy[ 'singular' ] ),
            'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'monotone' ), $taxonomy[ 'plural' ] ),
            'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'monotone' ), $taxonomy[ 'plural' ] ),
            'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'monotone' ), $taxonomy[ 'plural' ] ),
            'not_found'                  => sprintf( __( 'No %s found', 'monotone' ), $taxonomy[ 'plural' ] ),
            'menu_name'                  => __( $taxonomy[ 'plural' ], 'monotone' ),
        ];

        $default_args = [
            'labels'            => $labels,
            'public'            => $taxonomy[ 'args' ][ 'public' ],
            'show_ui'           => $taxonomy[ 'args' ][ 'show_ui' ],
            'meta_box_cb'       => null,
            'show_in_nav_menus' => $taxonomy[ 'args' ][ 'show_in_nav_menus' ],
            'show_tagcloud'     => $taxonomy[ 'args' ][ 'show_tagcloud' ],
            'show_admin_column' => $taxonomy[ 'args' ][ 'show_admin_column' ],
            'hierarchical'      => $taxonomy[ 'args' ][ 'hierarchical' ],
        ];

        $args = wp_parse_args( $taxonomy[ 'args' ], $default_args );

        register_taxonomy( $taxonomy[ 'slug' ], $taxonomy[ 'post_types' ], $args );
    }
}

new Custom_Taxonomies();
