<?php namespace monotone;

class Custom_Post_Types {
    public array $post_types = [
        [
            'slug'     => 'applicant',
            'singular' => 'Applicant',
            'plural'   => 'Applicants',
            'args'     => [
                'public'      => true,
                'menu_icon'   => 'dashicons-businessman',
                'supports'    => [ 'title', 'editor', 'thumbnail' ],
                'has_archive' => true,
                'rewrite'     => [ 'slug' => 'applicants' ],
                'menu_position' => 4,
            ]
        ],
        [
            'slug'     => 'core-value',
            'singular' => 'Core Value',
            'plural'   => 'Core Values',
            'args'     => [
                'public'      => true,
                'menu_icon'   => 'dashicons-awards',
                'supports'    => [ 'title', 'editor', 'thumbnail' ],
                'has_archive' => true,
                'rewrite'     => [ 'slug' => 'core-values' ],
                'menu_position' => 4,
            ]
        ]
    ];

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_types' ] );
    }

    public function register_post_types(): void {
        foreach ( $this->post_types as $post_type ) {
            $this->register_post_type( $post_type );
        }
    }

    public function register_post_type( $post_type ): void {
        $labels = [
            'name'               => __( $post_type[ 'plural' ], 'monotone' ),
            'singular_name'      => __( $post_type[ 'singular' ], 'monotone' ),
            'menu_name'          => __( $post_type[ 'plural' ], 'monotone' ),
            'name_admin_bar'     => __( $post_type[ 'singular' ], 'monotone' ),
            'add_new'            => __( 'Add New', 'monotone' ),
            'add_new_item'       => sprintf( __( 'Add New %s', 'monotone' ), $post_type[ 'singular' ] ),
            'new_item'           => sprintf( __( 'New %s', 'monotone' ), $post_type[ 'singular' ] ),
            'edit_item'          => sprintf( __( 'Edit %s', 'monotone' ), $post_type[ 'singular' ] ),
            'view_item'          => sprintf( __( 'View %s', 'monotone' ), $post_type[ 'singular' ] ),
            'all_items'          => sprintf( __( 'All %s', 'monotone' ), $post_type[ 'plural' ] ),
            'search_items'       => sprintf( __( 'Search %s', 'monotone' ), $post_type[ 'plural' ] ),
            'parent_item_colon'  => sprintf( __( 'Parent %s:', 'monotone' ), $post_type[ 'singular' ] ),
            'not_found'          => sprintf( __( 'No %s found.', 'monotone' ), $post_type[ 'plural' ] ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'monotone' ), $post_type[ 'plural' ] ),
        ];

        $default_args = [
            'labels'             => $labels,
            'description'        => '',
            'public'             => $post_type[ 'args' ][ 'public' ],
            'publicly_queryable' => $post_type[ 'args' ][ 'public' ],
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => $post_type[ 'args' ][ 'rewrite' ][ 'slug' ] ],
            'capability_type'   => 'post',
            'has_archive'       => $post_type[ 'args' ][ 'has_archive' ],
            'hierarchical'      => false,
            'menu_position'     => $post_type[ 'args' ][ 'menu_position' ],
            'supports'          => $post_type[ 'args' ][ 'supports' ],
            'menu_icon'         => $post_type[ 'args' ][ 'menu_icon' ],
        ];

        $args = wp_parse_args( $post_type[ 'args' ], $default_args );

        register_post_type( $post_type[ 'slug' ], $args );
    }
}

new Custom_Post_Types();
