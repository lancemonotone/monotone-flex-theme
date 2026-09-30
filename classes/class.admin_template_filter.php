<?php namespace monotone;

/**
 * Class Admin_Template_Filter
 * @description Filter pages by template in admin panel
 * @author Rus Miller
 */
class Admin_Template_Filter {

	public function __construct() {

		if ( ! empty( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] == 'edit.php' ) {
			$post_type = $this->current_post_type();
			add_action( 'restrict_manage_posts', array( $this, 'filter_dropdown' ) );
			add_filter( 'request', array( $this, 'filter_post_list' ) );
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'post_list_columns_head' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'post_list_columns_content' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'post_list_columns_head' ) );
		}

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	public function current_post_type() {
		return isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';
	}

	public function filter_dropdown() {
		if ( empty( $GLOBALS['pagenow'] ) || $GLOBALS['pagenow'] === 'upload.php' ) {
			return;
		}

		$post_type     = $this->current_post_type();
		$template      = isset( $_GET['page_template_filter'] ) ? $_GET['page_template_filter'] : "all";
		$default_title = apply_filters( 'default_page_template_title', __( 'Default Template' ), 'meta-box' );
		?>
        <select name="page_template_filter"
                id="page_template_filter">
            <option value="all"><?php
                /* translators: All page templates text. */
                esc_html_e( 'All Page Templates', 'filter-page-by-template' ) ?>
            </option>
            <option value="all_missing"
                    style="color:red" <?= ( $template == 'all_missing' ) ? ' selected="selected" ' : ""; ?>>
                <?php
                /* translators: All Missing Page Templates text. */
                esc_html_e( 'All Missing Page Templates', 'filter-page-by-template' ) ?>
            </option>
            <option value="default" <?= ( $template == 'default' ) ? ' selected="selected" ' : ""; ?>>
                <?= esc_html( $default_title ); ?>
            </option>
			<?php page_template_dropdown( $template, $post_type ); ?>
        </select>
		<?php
	}

	public function filter_post_list( $vars ) {
		if ( ! isset( $_GET['page_template_filter'] ) ) {
			return $vars;
		}
		$template = trim( $_GET['page_template_filter'] );

		$data        = get_option( "filter_page_by_template_data", array() );
		$filter_used = isset( $data['filter_used'] ) ? intval( $data['filter_used'] ) : 0;
		$filter_used ++;
		$data['filter_used'] = $filter_used;
		update_option( "filter_page_by_template_data", $data );

		if ( $template == "" || $template == 'all' ) {
			return $vars;
		}

		if ( $template == 'all_missing' ) {
			$templates        = wp_get_theme()->get_page_templates( NULL, 'page' );
			$template_files   = array_keys( $templates );
			$template_files[] = 'default';
			$vars             = array_merge(
				$vars,
				array(
					'meta_query' => array(
						array(
							'key'     => '_wp_page_template',
							'value'   => $template_files,
							'compare' => 'NOT IN',
						)
					),
				)
			);
		} else {
			$vars = array_merge(
				$vars,
				array(
					'meta_query' => array(
						array(
							'key'     => '_wp_page_template',
							'value'   => $template,
							'compare' => '=',
						),
					),
				)
			);
		}

		return $vars;

	}

	# Add new column to post list
	public function post_list_columns_head( $columns ) {
		$columns['template'] = 'Template';

		return $columns;
	}

	#post list column content
	public function post_list_columns_content( $column_name, $post_id ) {
		$post_type = $this->current_post_type();

		if ( $column_name == 'template' ) {
			$template = get_post_meta( $post_id, "_wp_page_template", TRUE );
			if ( $template ) {
				if ( $template == 'default' ) {
					$template_name = apply_filters( 'default_page_template_title', __( 'Default Template' ), 'meta-box' );
					echo '<span title="' .
                         /* translators: Template file text. */
                         esc_attr__( 'Template file', 'filter-page-by-template' ) . ': page.php">' . $template_name . '</span>';
				} else {
					$templates = wp_get_theme()->get_page_templates( NULL, $post_type );

					if ( isset( $templates[ $template ] ) ) {
						echo '<span title="Template file: ' . $template . '">' . $templates[ $template ] . '</span>';
					} else {

						echo '<span style="color:red" title="' .
                             /* translators: This template file does not exist text. */
                             esc_attr__( 'This template file does not exist', 'filter-page-by-template' ) . '">' . $template . '</span>';
					}
				}

			}
		}
	}

	/**
	 * @return void
	 * @since 2.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'filter-page-by-template', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

new Admin_Template_Filter();
