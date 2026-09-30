<?php namespace monotone;

/**
 * Class Pagination
 */
class Pagination {
    public static function get_pagination(): void {
        global $wp_query;

        $big = 999999999; // need an unlikely integer

        $pagination = paginate_links( [
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '?paged=%#%',
            'current'   => max( 1, get_query_var( 'paged' ) ),
            'total'     => $wp_query->max_num_pages,
            'prev_next' => true,
            'next_text' => SVG_Icons::get_svg('ui', 'arrow-right' ),
            'prev_text' => SVG_Icons::get_svg('ui', 'arrow-left' ),
            'type'      => 'array',
            'mid_size'  => 1,
        ] );

        if ( is_array( $pagination ) ) {
            $html = <<<HTML
            <nav class="pagination-archive" aria-label="Pagination" role="navigation">
            <ul class="list-unstyled d-flex justify-content-center align-items-center gap-3">
            HTML;

            foreach ( $pagination as $page_link ) {
                $html .= <<<HTML
                <li class="d-inline-block"><span class="visually-hidden">Page </span>{$page_link}</li>
                HTML;
            }

            $html .= <<<HTML
            </ul>
            </nav>
            HTML;

            echo $html;
        }
    }


}
