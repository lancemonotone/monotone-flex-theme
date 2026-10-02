<?php

namespace monotone;

/**
 * ACF Flex Page builder lets editors build a page from stacked modules. Each
 * module is an ACF Flexible Content 'layout' (hero, divider, etc.) with its
 * own fields and styles.
 *
 * Why it exists (and why you shouldn't use it):
 *     Although Gutenberg blocks were a great idea in concept, early block
 *     editing was brutal. Gutenberg expected us to speak React, which had
 *     not yet gone viral, and developer documentation was confusing and
 *     inconsistent. This Flex Page builder attempted to provide editors a 
 *     modular 'block' solution authored in the familiar WordPress way, with
 *     its own fields, template, assets, and a thumbnail UI preview. However,
 *     an improved Block API and the advent of ACF Blocks (which I now prefer)
 *     have improved the block experience tremendously, making this approach
 *     obsolete.
 *
 *     I have since developed a standalone plugin for defining and managing
 *     multiple ACF Block modules within a shared architecture, featuring
 *     automatic block and field registration, asset handling, and editor
 *     templates per block. It's the spiritual successor to this Flex Page
 *     builder, but instead of embedding Flex Content field definitions
 *     directly into the theme, defined ACF Block modules can be reused
 *     across any site with a block-enabled theme. ACF Blocks are authored in
 *     a more familiar way than native WP Blocks, are more performant than
 *     Flexible Content fields, and don't require anything special from themes.
 *     That said, take a trip back in time with me...
 *
 * Layouts:
 *     Each Flex Page layout module is a self-contained directory in
 *     `{theme}/layouts/{slug}/{slug}.php`. This directory contains the
 *     layout PHP template, a thumbnail image of the rendered UI for a
 *     popover preview, and optional SCSS and JS files. Each layout's JS
 *     (and any SCSS it imports) is wired through `layouts/layouts.js`
 *     into the Vite frontend build. When building a flex page, editors
 *     choose layouts from predefined Flex Content modules, each with its
 *     own fields and styles. Building a flex page is fast, flexible, and
 *     difficult to wreck, making it a great solution for non-technical
 *     site owners to add variety while maintaining consistency across a
 *     site.
 *
 * ACF setup:
 *     Screenshot: `layouts/acf_flexible_content_field_example.png`
 *
 *     The field group 'Flex Layouts' has one Flexible Content field
 *     labeled 'Flex Modules'. Its field name must be
 *     'template_flex_page' (`THEME_LAYOUT_SLUG`). Layouts inside that
 *     field are the modules (e.g. full_width_section, divider) available
 *     in the editor, each with its own sub-fields and supported by layout
 *     templates (containing php, css, js, and a thumbnail image) defined
 *     in `layouts/{slug}/{slug}.php`.
 *
 * Usage:
 *     This class renders ACF Flexible Content layouts on page templates
 *     which call:
 *
 *     `<?php ACF_Flex_Page::get_layout( get_the_ID() ); ?>`
 *
 *     `get_layout()` loops through the flexible content field (`THEME_LAYOUT_SLUG`)
 *     and loads each layout module from `layouts/{slug}/{slug}.php`.
 *
 * Admin assets:
 *     Admin editor UX lives in:
 *
 *     `assets/src/components/admin/acf-flex-layouts.js`
 *     `assets/src/components/admin/_acf-flex-layouts.scss`
 *
 * Collapse all:
 *     Adds a 'Collapse All' control on flexible content labels/actions
 *     so editors can collapse every layout row at once. ACF now provides
 *     this functionality natively. I like to think I was the inspiration :P
 * 
 * Layout Title Colors:
 *     Adds a colored background to the layout title in the admin editor, based 
 *     on the layout type name. Colors are the same for each layout type, so 
 *     editors can easily identify different layouts at a glance.
 * 
 * Layout Thumbnails:
 *     Enhances the layout selection tooltips in the WordPress admin by adding 
 *     thumbnail previews. Thumbnail images are appended to each layout choice 
 *     in the ACF Flexible Content 'Add' popup. Thumbnails appear on hover, 
 *     providing a visual preview of the rendered layout.
 */
class ACF_Flex_Page {
    // ACF flexible content field name (from `THEME_LAYOUT_SLUG`).
    public static string $layout_slug = '';

    // Relative layouts dir for get_template_part (from `THEME_LAYOUT_DIR`).
    public static string $layout_dir = '';

    // Preferred admin handle label field (from `THEME_LAYOUT_TITLE_FIELD`).
    public static string $layout_title_field = '';

    // Alternate title field slugs for the admin handle (from `THEME_LAYOUT_TITLE_FIELDS`).
    public static array $layout_title_fields = [];

    public function __construct() {
        add_action('after_setup_theme', [$this, 'init'], 1);

        add_filter('acf/fields/flexible_content/layout_title', [$this, 'add_layout_title'], 10, 4);

        // Add AJAX actions for getting layout thumbnails
        add_action('wp_ajax_get_layout_thumbnail', [$this, 'ajax_get_layout_thumbnail']);
    }

    /**
     * Copy layout config from theme constants onto this class.
     */
    public function init(): void {
        self::$layout_slug = THEME_LAYOUT_SLUG;
        self::$layout_dir = THEME_LAYOUT_DIR;
        self::$layout_title_field = THEME_LAYOUT_TITLE_FIELD;
        self::$layout_title_fields = THEME_LAYOUT_TITLE_FIELDS;
    }

    /**
     * Walk the page's flex field and print (or return) each layout module.
     *
     * Field name comes from self::$layout_slug. Layout IDs get a page-{id} prefix
     * so two pages don't collide if the same layout shows up twice.
     *
     * @param int  $page_id Page that holds the flexible content rows.
     * @param bool $return  True to return markup; false to echo it.
     *
     * @return string|void
     */
    public static function get_layout(int $page_id, bool $return = false) {
        $page_id_prefix = 'page-' . $page_id;

        // Which row we're on. Used for default layout ids when 'layout_id' isn't set
        // so two of the same layout don't get the same id.
        $count  = 1;
        // Populated when $return is true; ignored when echoing.
        $output = '';

        // Loop through every layout in this flexible content field.
        while (have_rows(self::$layout_slug, $page_id)) {
            the_row();

            // Get row layout, build its settings, load its template.
            $layout = get_row_layout();
            $layout_settings = self::get_layout_settings($layout, $page_id_prefix, $count++);
            $layout_content = self::get_layout_content($layout, $layout_settings);

            // No template (or empty) for this layout name? Skip to the next.
            if ($layout_content === null) {
                continue;
            }

            // Either return the markup or echo it now.
            if ($return) {
                $output .= $layout_content;
            } else {
                echo $layout_content;
            }
        }

        // Return the markup if $return is true, otherwise echo it.
        if ($return) {
            return $output;
        }
    }

    /**
     * Load a layout's PHP template and return its markup.
     *
     * @param string $slug Layout name/folder.
     * @param array  $args Passed through to `get_template_part()`.
     *
     * @return string|null Markup, or null when the template is missing/empty.
     */
    public static function get_layout_content(string $slug, array $args = []): ?string {
        $template_path = self::$layout_dir . '/' . $slug . '/' . $slug;
        ob_start();
        get_template_part($template_path, null, $args);
        $content = ob_get_clean();

        return ($content === '') ? null : $content;
    }

    /**
     * Return layout settings (ID, classes, and inline styles).
     *
     * @param string $row_layout      ACF layout name.
     * @param string $page_id_prefix  Prefix for default layout IDs (e.g. page-123).
     * @param int    $count           Row index among siblings.
     *
     * @return array
     */
    public static function get_layout_settings(string $row_layout, string $page_id_prefix, int $count): array {
        // convert _ to -
        $layout    = str_replace('_', '-', $row_layout);

        // If a layout_id field exists and is set, use it, otherwise use 'page-123-layout-name-1'.
        $layout_id = get_sub_field('layout_id') ?: $page_id_prefix . '-' . $layout . '-' . $count;

        $classes = ['layout', $layout];
        $styles  = [];

        // NO BOTTOM PADDING
        if (get_sub_field('no_bottom_padding')) {
            $classes[] = 'no-padding-bottom';
        }

        // NO TOP PADDING
        if (get_sub_field('no_top_padding')) {
            $classes[] = 'no-padding-top';
        }

        // ADD TOP PADDING
        if (get_sub_field('add_top_padding')) {
            $classes[] = 'add-padding-top';
        }

        // BACKGROUND COLOR
        if (get_sub_field('background_color')) {
            $classes[] = get_sub_field('background_color');
        }

        // HIDE PAGE TITLE
        if (get_sub_field('hide_page_title')) {
            $classes[] = 'hide-page-title';
        }

        // SPLIT IMAGE
        if ($split_type = get_sub_field('split_type')) {
            $classes[] = $split_type;
        }

        // BACKGROUND IMAGE
        if (get_sub_field('background_image')) {
            $classes[] = 'has-background-img lazy-bg';
            $styles[]  = self::get_background_image_style(get_sub_field('background_image'));
        }

        $classes = implode(' ', $classes);
        $styles  = implode(' ', $styles);

        return [
            'id'      => $layout_id,
            'classes' => $classes,
            'styles'  => $styles
        ];
    }

    /**
     * Build an inline background-image style string for a layout module.
     *
     * Uses the attachment's full-size URL via `Images::get_image_url()`.
     * When `$bg_image_only` is true, returns only the background-image property;
     * otherwise also adds cover/center/no-repeat properties.
     *
     * @param mixed $image         Image attachment ID.
     * @param bool  $bg_image_only Skip the cover/center/no-repeat properties.
     *
     * @return string
     */
    public static function get_background_image_style(int $image, bool $bg_image_only = false): string {
        $url = Images::get_image_url([
            'id'   => $image,
            'size' => 'full'
        ]);

        $style = 'background-image: url(' . $url . ');';

        if ($bg_image_only) {
            return $style;
        }

        $style .= 'background-repeat: no-repeat; background-position: center center; background-size: cover;';

        return $style;
    }

    /**
     * Customize layout titles in the editor.
     *
     * By default, collapsed layout labels show the layout type, which is not helpful for
     * distinguishing between layouts (e.g. five 'Full Width Section' layouts). This method
     * builds the title HTML so editors can tell layouts apart by their text label, a layout
     * thumbnail, and a colored background. ACF injects the returned HTML label into the
     * row header. Title styles live in `_acf-flex-layouts.scss`.
     * 
     * @callback acf/fields/flexible_content/layout_title
     *
     * @param string $title  Layout type label from ACF (e.g. 'Full Width Section').
     * @param array  $field  The ACF field definition (unused)
     * @param array  $layout Layout definition, including sub_fields.
     * @param int    $i      The index of the layout in the flexible content field (unused).
     *
     * @return string HTML for the flex row title in wp-admin.
     */
    public function add_layout_title(string $title, array $field, array $layout, int $i): string {
        // Apply any shortcodes to the $title string.
        $title = do_shortcode($title);

        // Only use fields that actually exist on this layout.
        $found = [];
        $sub_fields = $layout['sub_fields'] ?? [];
        foreach ($sub_fields as $sub) {
            if (! empty($sub['name'])) {
                $found[$sub['name']] = true;
            }
        }

        // Title field names to check for a value (preferred title field first).
        $title_fields = [];

        // Prefer $layout_title_field when this layout has that field.
        if (isset($found[self::$layout_title_field])) {
            $title_fields[] = self::$layout_title_field;
        }

        // Then any other title fields that exist on this layout.
        foreach (self::$layout_title_fields as $field_name) {
            if (isset($found[$field_name])) {
                $title_fields[] = $field_name;
            }
        }

        // Colors are the same for each layout type, so cache calculation across calls.
        static $color_cache = [];
        if (! isset($color_cache[$title])) {
            // Generate a color from the title.
            $color_cache[$title] = Color_Helpers::hex_from_string($title);
        }
        $color = $color_cache[$title];

        // Add the layout thumbnail.
        $title_html  = $this->add_layout_thumbnail($layout);

        // Add the layout type color background.
        $type_label = esc_html($title);
        $title_html .= <<<HTML
<span class="acf-layout-type" style="background: #{$color}">{$type_label}</span>
HTML;

        // Find first field with a value. Append its text as the label, then return the full title HTML.
        foreach ($title_fields as $field_name) {
            if ($value = get_sub_field($field_name)) {
                $value = strip_tags($value);
                $value = do_shortcode($value);
                $value = ucwords($value);
                $value = esc_html($value);
                $title_html .= <<<HTML
<span class="acf-layout-title">{$value}</span>
HTML;
                return $title_html;
            }
        }

        // No label found; return thumb and name only.
        return $title_html;
    }

    /**
     * Returns layout thumbnail HTML.
     *
     * If no thumbnail exists, returns an empty .thumbnail.no-thumbnail span so
     * spacing stays consistent. Styles in `_acf-flex-layouts.scss` handle both cases.
     *
     * @param array $layout Layout definition from ACF (needs name / slug).
     *
     * @return string Thumbnail markup for the row title.
     */
    public function add_layout_thumbnail(array $layout): string {
        $slug = $layout['name'];

        if ($thumbnail_url = $this->get_layout_thumbnail($slug)) {
            $src = esc_url($thumbnail_url);

            return <<<HTML
<span class="thumbnail"><img src="{$src}" height="36px" alt="" /></span>
HTML;
        }

        return <<<HTML
<span class="thumbnail no-thumbnail"></span>
HTML;
    }

    /**
     * AJAX request handler for layout thumbnails in the ACF 'Add layout' popup.
     *
     * When the editor opens the layout picker, `acf-flex-layouts.js` requests 
     * layout thumbnails. Request should contain a nonce named 'layout_thumbnail'
     * and the layout slug. Echoes the thumbnail URL if found.
     *
     * @return void
     */
    public function ajax_get_layout_thumbnail(): void {
        check_ajax_referer('layout_thumbnail', 'nonce');

        $slug = sanitize_text_field(wp_unslash($_POST['layout'] ?? ''));
        if ($slug === '') {
            echo '';
            wp_die();
        }

        echo $this->get_layout_thumbnail($slug);
        wp_die();
    }

    /**
     * Find the preview image for a layout and return its URL.
     *
     * Checks `THEME_LAYOUT_PATH/{slug}/` for the layout preview image and returns 
     * its URL. Returns an empty string if not found. Caches each slug request
     * because layouts are reused.
     *
     * @param string $slug Layout folder name (e.g. full_width_section).
     *
     * @return string Thumb URL, or empty string if missing.
     */
    public function get_layout_thumbnail(string $slug): string {
        // Keep path bits out of the slug; AJAX can send anything.
        $slug = basename($slug);
        if ($slug === '' || $slug === '.' || $slug === '..') {
            return '';
        }

        // Same layout name? Cache the results.
        static $thumb_cache = [];

        // Return cached result if available.
        if (array_key_exists($slug, $thumb_cache)) {
            return $thumb_cache[$slug];
        }

        $file_types = ['jpg', 'png', 'webp'];
        foreach ($file_types as $type) {
            $file_path = THEME_LAYOUT_PATH . "/{$slug}/thumb.{$type}";
            if (file_exists($file_path)) {
                // Cache and return the result.
                return $thumb_cache[$slug] = THEME_LAYOUT_URI . "/{$slug}/thumb.{$type}";
            }
        }

        return $thumb_cache[$slug] = '';
    }
}

if (class_exists('ACF')) {
    new ACF_Flex_Page();
}
