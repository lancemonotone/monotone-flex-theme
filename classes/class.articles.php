<?php

namespace monotone;

class Articles {
    public function __construct() {
    }

    /**
     * Get edit post link if user has permissions, with optional icon display and accessible label.
     *
     * @param int|null $post_id = null The ID of the post to edit.
     * @param string $icon_html Optional HTML content to display instead of text, e.g., an SVG icon.
     * @param string $alt_text Alternative text for the icon, for accessibility.
     *
     * @return string The HTML for the edit post link, with either text or an icon, and appropriate accessibility attributes.
     */
    public static function get_edit_post_link( int|null $post_id = null, string $icon = '', string $alt_text = 'Edit Post' ): string {
        if ( ! empty( $icon ) ) {
            $icon_html = SVG_Icons::get_svg( 'ui', $icon );
        }

        if ( current_user_can( 'edit_post', $post_id ) ) {
            $link = get_edit_post_link( $post_id );
            /* translators: Edit Post link text. */
            $aria_label = esc_attr( $alt_text );
            if ( empty( $icon_html ) ) {
                // Default text link
                return '<a href="' . esc_url( $link ) . '" class="edit-post-link btn btn-sm btn-outline-primary" aria-label="' . $aria_label . '">Edit Post</a>';
            } else {
                // Icon link with accessible label
                return '<a href="' . esc_url( $link ) . '" class="edit-post-link btn btn-sm btn-outline-primary" aria-label="' . $aria_label . '">' . $icon_html . '</a>';
            }
        }

        return '';
    }

    public static function get_featured_image( int|null $post_id = null ): string {
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( ! $thumbnail_id ) {
            return '';
        }

        // Get the full image URL as a fallback
        $src = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        // Get srcset for responsive images
        $srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'full' );
        // Get sizes for responsive behavior
        $sizes = wp_get_attachment_image_sizes( $thumbnail_id, 'full' );
        // Get alt text for accessibility
        $alt   = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
        $style = 'height:100px';

        // Construct and return the img tag
        return sprintf(
            '<img class="rounded w-100 wp-image-" src="%s" srcset="%s" sizes="%s" alt="%s">',
            esc_url( $src ),
            esc_attr( $srcset ),
            esc_attr( $sizes ),
            esc_attr( $alt ),
        );
    }

    /**
     * Fetch an array of post IDs based on given criteria.
     *
     * @param int $posts_per_page Number of posts to fetch.
     * @param int &$total_pages The total number of pages, passed by reference.
     *
     * @return array  An array of post IDs.
     */
    public static function get_post_ids( $term, int $posts_per_page = 10, int &$total_pages = 0 ): array {
        // Get the current page number from query vars
        $current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

        // Build WP_Query arguments
        $args = [
            'post_type'      => 'post',
            'posts_per_page' => $posts_per_page,
            'paged'          => $current_page,
        ];

        // If a term is specified, add it to the query
        if ( $term ) {
            $args[ 'tax_query' ] = [
                [
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $term,
                ],
            ];
        }

        // Execute the query
        $query = new \WP_Query( $args );

        // Calculate total pages
        $total_posts = $query->found_posts;
        $total_pages = ceil( $total_posts / $posts_per_page );

        // Fetch post IDs
        $post_ids = [];
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_ids[] = get_the_ID();
            }
        }

        // Reset postdata
        wp_reset_postdata();

        return $post_ids;
    }

    /**
     * Get term links as a comma-separated string for a given post and taxonomy.
     *
     * @param int|null $post_id = null The ID of the post.
     * @param string $taxonomy The taxonomy to retrieve terms from.
     *
     * @return string  A comma-separated string of term links.
     */
    public static function get_term_links( int|null $post_id = null, string $taxonomy = 'category' ): string {
        // Get the terms once and store them in a variable
        $terms = get_the_terms( $post_id, $taxonomy );

        // Initialize an array to hold the term links
        $term_links = [];

        // Loop through the stored terms
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                // $term_links[] = '<a class="btn btn-small btn-outline-secondary" href="' . get_term_link( $term ) . '">' . $term->name . '</a>';
                $term_links[] = '<a class="" href="' . get_term_link( $term ) . '">' . $term->name . '</a>';
            }
        }

        // Join term links into a comma-separated string
        return implode( ', ', $term_links );
    }

    /**
     * Get the linked title of a given post.
     *
     * @param int|null $post_id = null The ID of the post.
     *
     * @return string  The title of the post wrapped in an anchor tag.
     */
    public static function get_linked_title( int|null $post_id = null ): string {
        // Fetch the post title and permalink
        $title     = get_the_title( $post_id );
        $permalink = get_permalink( $post_id );

        // Return the title wrapped in an anchor tag
        return '<a href="' . esc_url( $permalink ) . '">' . esc_html( $title ) . '</a>';
    }

    /**
     * Get the content of a given post.
     *
     * @param int|null $post_id = null
     *
     * @return string
     */
    public static function get_post_content( int|null $post_id = null ): string {
        // Fetch the formatted post content
        // Return the content
        return apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
    }

    /**
     * Get the excerpt of a given post, falling back to the first n words of the post content.
     *
     * @param int|null $post_id = null The ID of the post.
     * @param int $word_count Number of words to use as a fallback.
     *
     * @return string  The excerpt or truncated post content.
     */
    public static function get_excerpt( int|null $post_id = null, int $word_count = 15 ): string {
        $excerpt_more_closure = function () {
            return '...';
        };

        $excerpt_length_closure = function () use ( $word_count ) {
            return $word_count;
        };

        // Register hook to change the readmore ellipsis
        add_filter( 'excerpt_more', $excerpt_more_closure );

        // Register hook to change the excerpt length
        add_filter( 'excerpt_length', $excerpt_length_closure );

        $excerpt = get_the_excerpt();

        // Remove the filters
        remove_filter( 'excerpt_more', $excerpt_more_closure );
        remove_filter( 'excerpt_length', $excerpt_length_closure );

        // Leave a space for grid layout
        return $excerpt;
    }

    public static function get_read_more( int|null $post_id = null ): string {
        $permalink = get_permalink( $post_id );

        return '<a href="' . esc_url( $permalink ) . '" class="btn btn-small btn-light" aria-label="Read more about ' . get_the_title( $post_id ) . '">Read More</a>';
    }

    /**
     * Get the date and author of a given post in a specific format.
     *
     * @param int|null $post_id = null The ID of the post.
     *
     * @return string  The formatted date and author string.
     */
    public static function get_post_date( int|null $post_id = null ): string {
        // Fetch the post date in the 'F j, Y' format (e.g., July 28, 2023)
        $date = get_the_date( 'F j, Y', $post_id );

        // Combine date and author into the desired format
        return $date;
    }

    /**
     * Get the date and author of a given post in a specific format.
     *
     * @param int|null $post_id = null The ID of the post.
     *
     * @return string  The formatted date and author string.
     */
    public static function get_post_author( int|null $post_id = null, bool $link_author_name = false ): string {
        // Override option to link author name
        if ( ! $link_author_name ) {
            $link_author_name = get_field( 'link_author_name', 'option' ) ?? false;
        }
        // Step 1: Check if hide_author_card is false
        $hide_author_card = get_field( 'hide_author_card', $post_id ) ?? false;
        if ( ! $hide_author_card ) {
            // Step 2: Check for override author in current post options,
            // then check for default author in Theme Options
            [ $author_id, $author ] = self::get_author( $post_id );

            // Include author link if required
            // Combine into the desired format
            return $link_author_name ? '<a href="' . get_author_posts_url( $author_id ) . '">' . $author . '</a>' : $author;
        }

        return '';  // Return an empty string if hide_author_card is true
    }

    /**
     * Checks if an author card should be displayed for a given post and, if not hidden,
     * it constructs and includes the author's contact card template with their details.
     *
     * @param $post_id
     */
    public static function maybe_get_author_card( $post_id ) {
        // Check if the author card should be hidden
        $hide_author_card = get_field( 'hide_author_card', $post_id ) ?? false;
        if ( $hide_author_card ) {
            return;
        }
        // Get the post or default author
        [ $author_id, $author, $author_email ] = self::get_author( $post_id );

        // Prepare the author card data
        $card = [
            'heading'       => __( 'For questions contact' ),
            'content'       => "<p><strong>{$author}</strong><br>{$author_email}</p>",
            'card_link'     => [
                'url'    => 'mailto:' . $author_email,
                'target' => 'target="_blank" rel="noopener noreferrer"',
            ],
            'heading_size'  => 'header-sm',
            'acf_fc_layout' => 'external_link_card',
        ];

        // Filter out empty class values
        $card_classes = [
            'has-background',
            'has-primary-light-background-color',
            str_replace( '_', '-', 'external_link_card' ) // Assuming acf_fc_layout is always 'external_link_card'
        ];

        // Link aria-label
        $link_aria = $card[ 'heading' ] . ' ' . $author;

        // Include the author card template
        include( locate_template( 'layouts/cards/' . $card[ 'acf_fc_layout' ] . '.php' ) );
    }

    /**
     * @param int|null $post_id = null
     *
     * @return array
     */
    public static function get_author( int|null $post_id = null ): array {
        $override_author = get_field( 'override_author', $post_id ) ?? false;
        $default_author  = get_field( 'default_author', 'option' );

        // Step 3: If override is true or the default author is not set, use post author
        if ( $override_author || empty( $default_author ) ) {
            $author_id    = get_post_field( 'post_author', $post_id );
            $author       = get_the_author_meta( 'display_name', $author_id );
            $author_email = get_the_author_meta( 'user_email', $author_id );
        } else {
            // Step 4: Use default author from Theme Options
            $author_id    = $default_author[ 'ID' ]; // Assuming 'ID' is a key in your default_author array
            $author       = $default_author[ 'display_name' ]; // Assuming 'display_name' is a key in your default_author array
            $author_email = $default_author[ 'user_email' ];
        }

        return [ $author_id, $author, $author_email ];
    }

    /**
     * Get single previous/next post navigation in same category.
     *
     * @return string
     */
    public static function paginate_single_post(): string {
        $prev_post = get_previous_post( true );
        $next_post = get_next_post( true );

        if ( ! $prev_post && ! $next_post ) {
            return '';
        }

        // Define the SVG markup
        $svg_left = SVG_Icons::get_svg( 'ui', 'arrow-left' );
        $svg_right = SVG_Icons::get_svg( 'ui', 'arrow-right' );

        $output = '<div class="d-flex justify-content-center mt-tall">';

        $links = [];

        if ( $next_post ) {
            $next_title = get_the_title( $next_post->ID );
            $next_link  = get_permalink( $next_post->ID );

            $links []= '<a class="nav-left" href="' . $next_link . '">' . $svg_left .' ' . $next_title . '</a>';
        }

        if ( $prev_post ) {
            $prev_title = get_the_title( $prev_post->ID );
            $prev_link  = get_permalink( $prev_post->ID );

            $links []= '<a class="nav-right" href="' . $prev_link . '">' . $prev_title . ' ' . $svg_right . '</a>';
        }

        $output .= implode( '<span class="px-2"> | </span>', $links );

        $output .= '</div>';

        return $output;
    }
}

new Articles();
