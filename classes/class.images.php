<?php namespace monotone;

/**
 * This class provides functionality to handle WEBP images in WordPress. It includes methods to automatically resize images and
 * generate HTML <picture> tags with responsive image support.
 */
class Images {
    private static int $default_width = 1500;

    public function __construct() {
        add_filter('jpeg_quality', function($arg){return 100;});
    }

    /**
     * Creates an HTML string that represents a responsive background
     * image using the <picture> and <source> elements. Based on the
     * provided max_width, the function selects the first source larger
     * than max_width as the default image and includes only the sources
     * smaller than this default in the <picture> element.
     *
     * It requires the following CSS, which positions the <picture> element
     * absolutely within its container and sets the image to cover the container.
     * The output of this function should come before any other elements in the container.
     *
        picture.background-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            }
        }
     *
     *
     * @param $id
     * @param null $max_width
     *
     * @return string
     */
    public static function get_background_image( $id, $max_width = null ): string {
        // Get the alt text
        $alt_text = get_post_meta( $id, '_wp_attachment_image_alt', true );
        if ( ! $alt_text ) {
            $alt_text = ''; // Default alt text
        }

        // Get the srcset array
        $srcset = wp_get_attachment_image_srcset( $id );

        // If the srcset is not available, return an empty string
        if ( ! $srcset ) {
            return '';
        }

        // Split the srcset into individual sources
        $sources = explode( ', ', $srcset );
        $sorted_sources = [];
        foreach ( $sources as $source ) {
            [ $url, $size ] = explode( ' ', $source );
            $sorted_sources[ (int) rtrim($size, 'w') ] = $url;
        }

        // Sort by size
        ksort($sorted_sources);

        // Find the default source based on max_width or use the first one
        $default_src = '';
        $default_size = 0;
        if ( $max_width ) {
            foreach ( $sorted_sources as $size => $url ) {
                if ( $size >= $max_width ) {
                    $default_src = $url;
                    $default_size = $size;
                    break;
                }
            }
        }
        if ( ! $default_src ) {
            $default_src = end( $sorted_sources ); // Get the URL from the first source
            $default_size = key( $sorted_sources ); // Get the corresponding size
        }

        // Create <picture>/<source> elements
        $html = "<picture class=\"background-container\">";
        foreach ( $sorted_sources as $size => $url ) {
            if ( $size < $default_size ) {
                $html .= "<source srcset=\"{$url}\" media=\"(min-width: {$size}w)\">";
            }
        }
        $html    .= "<img src=\"{$default_src}\" alt=\"$alt_text\">";
        $html    .= "</picture>";

        return $html;
    }

    /**
     * This static function returns the URL of the resized image. It resizes the image if it doesn't exist.
     *
     * @param array $args
     *
     * @return string The URL of the resized image.
     */
    public static function get_image_url( array $args ): string {
        if ( ! $image = self::get_image_attributes( $args ) ) {
            return '';
        }

        return $image[ 'resized_webp_url' ] ?? $image[ 'resized_url' ] ?? $image[ 'original' ][ 0 ];
    }

    /**
     * This static function generates an HTML <picture> tag with responsive image support. It resizes the image
     * and creates the <source> elements for the different sizes of the image.
     *
     * @param array $args
     *
     * @return string The HTML <picture> tag with the responsive image support.
     */
    public static function get_image( array $args ): string {
        if ( ! $image = self::get_image_attributes( $args ) ) {
            return '';
        }

        [ $id, $have_resized, $size, $class, $style, $original, $resized, $resized_webp, $resized_url, $resized_mime, $resized_webp_url, $resized_webp_mime, $srcset ] = array_values( $image );

        $width  = $have_resized && ! empty( $resized ) ? $resized[ 1 ] : $original[ 1 ];
        $height = $have_resized && ! empty( $resized ) ? $resized[ 2 ] : $original[ 2 ];

        // Get the alt text for the image.
        $alt = get_post_meta( $id, '_wp_attachment_image_alt', true );

        // Get the srcset attribute for the image.
        $srcset = $srcset ? wp_get_attachment_image_srcset( $id, $size ) : false;

        // Create the picture tag.
        $args = [
            'url'               => $original[ 0 ],
            'width'             => $width,
            'class'             => $class,
            'style'             => $style,
            'alt'               => $alt,
            'height'            => $height,
            'resized_url'       => $resized_url,
            'resized_mime'      => $resized_mime,
            'resized_webp_url'  => $resized_webp_url,
            'resized_webp_mime' => $resized_webp_mime,
            'srcset'            => $srcset,
        ];

        return self::get_image_tag( $args );
    }

    /**
     * Returns the HTML <picture> tag with  responsive image support.
     *
     * @param array $args
     *
     * @return string
     */
    public static function get_image_tag( array $args ): string {
        if ( empty( $args[ 'url' ] ) ) {
            return '';
        }

        $parsed = wp_parse_args( $args, [
            'url'               => '',
            'width'             => '',
            'class'             => '',
            'style'             => '',
            'alt'               => '',
            'height'            => 'auto',
            'resized_url'       => null,
            'resized_mime'      => null,
            'resized_webp_url'  => null,
            'resized_webp_mime' => null,
            'srcset'            => false,
        ] );

        [ $url, $width, $class, $style, $alt, $height, $resized_url, $resized_mime, $resized_webp_url, $resized_webp_mime, $srcset ] = array_values( $parsed );

        $srcset = $resized_url && $srcset ? $srcset : false;

        $picture_tag = '<picture>';
        if ( $resized_webp_url ) {
            $picture_tag .= '<source srcset="' . $resized_webp_url . '" type="' . $resized_webp_mime . '">';
        }
        if ( $resized_url ) {
            $picture_tag .= '<source srcset="' . $resized_url . '" type="' . $resized_mime . '">';
        }
        $picture_tag .= '<img loading="lazy" style="'  . $style . '" class="' . $class . '" src="' . $url . '" alt="' . $alt . '" width="' . $width . '" height="' . $height . '" ' . ( $srcset ? 'srcset="' . $srcset . '"' : '' ) . '>';
        $picture_tag .= '</picture>';

        return $picture_tag;
    }

    /**
     * This static function returns the attributes of the resized image. It resizes the image if it doesn't exist.
     *
     * @param array $args - The arguments to pass to the function.
     *
     * @return array|bool - Returns an array of attributes if the image exists, otherwise returns FALSE.
     */
    private static function get_image_attributes( array $args ): bool|array {
        $parsed = wp_parse_args( $args, [
            'id'     => 0,
            'size'   => 'full',
            'width'  => self::get_default_width(),
            'class'  => '',
            'style'  => '',
            'force'  => false,
            'srcset' => true,
        ] );

        [ $id, $size, $width, $class, $style, $force, $srcset ] = array_values( $parsed );

        if ( ! $id || ! is_int( $id ) ) {
            return false;
        }

        $have_resized = self::resize_image_by_id( $id, $size, $width, $force );

        /**
         * wp_get_attachment_image_src() returns an array with the following structure:
         * [0] => URL of the image
         * [1] => Width of the image
         * [2] => Height of the image
         * [3] => Boolean indicating whether the image is an intermediate size
         */
        $original          = wp_get_attachment_image_src( $id, 'full' );
        $resized           = $have_resized ? wp_get_attachment_image_src( $id, $size ) : null;
        $resized_mime      = $have_resized ? wp_get_attachment_metadata( $id )[ 'sizes' ][ $size ][ 'mime-type' ] : null;
        $resized_webp      = $have_resized ? wp_get_attachment_image_src( $id, $size . '-webp' ) : null;
        $resized_webp_mime = $have_resized ? wp_get_attachment_metadata( $id )[ 'sizes' ][ $size . '-webp' ][ 'mime-type' ] : null;

        $resized_url      = $have_resized && ! empty( $resized ) ? $resized[ 0 ] : null;
        $resized_webp_url = $have_resized && ! empty( $resized_webp ) ? $resized_webp[ 0 ] : null;

        return [
            'id'                => $id,
            'have_resized'      => $have_resized,
            'size'              => $size,
            'class'             => $class,
            'style'             => $style,
            'original'          => $original,
            'resized'           => $resized,
            'resized_webp'      => $resized_webp,
            'resized_url'       => $resized_url,
            'resized_mime'      => $resized_mime,
            'resized_webp_url'  => $resized_webp_url,
            'resized_webp_mime' => $resized_webp_mime,
            'srcset'            => $srcset,
        ];
    }

    /**
     * This static function resizes the image with the specified size and saves the resized image metadata in
     * WordPress.
     *
     * @param int $attachment_id The ID of the attachment post that represents the image.
     * @param string $size The image size to use for the resized image.
     * @param int $new_width The maximum width of the image. The image will be resized proportionally to fit this width.
     * @param bool $force Whether to force the image to be resized even if it has already been resized. Useful for tweaking during development.
     *
     * @return bool Whether the resized image was successfully created or already existed.
     */
    private static function resize_image_by_id( int $attachment_id, string $size, int $new_width = 600, bool $force = false ): bool {
        // Fudge to increase the image quality without sacrificing filesize too much.
        $new_width *= 3;

        // Get the image path
        if ( ! $original_image_path = self::get_image_path( $attachment_id ) ) {
            return false;
        }

        // Check if the image has already been resized
        $metadata = wp_get_attachment_metadata( $attachment_id );
        if ( ! $force && ! empty( $metadata[ 'sizes' ][ $size . '-webp' ] ) ) {
            return true;
        }

        if ( ! $force && ! empty( $metadata[ 'sizes' ][ $size ] ) ) {
            return true;
        }

        $original_image = wp_get_attachment_image_src( $attachment_id, 'full' );
        $original       = [
            'url'    => $original_image[ 0 ],
            'width'  => $original_image[ 1 ],
            'height' => $original_image[ 2 ],
        ];

        // Resize the image
        $aspect_ratio = $original[ 'height' ] / $original[ 'width' ];

        // if the original width is smaller than the new width, convert to webp but don't resize
        if ( $original[ 'width' ] < $new_width ) {
            $new_width = $original[ 'width' ];
        }

        $new_height = intval( round( $new_width * $aspect_ratio ) );

        $image_sizes = $metadata[ 'sizes' ];
        $did_resize  = false;
        if ( $resized_webp = self::image_make_intermediate_size( $original_image_path, $new_width, $new_height, 'webp' ) ) {
            $image_sizes[ $size . '-webp' ] = $resized_webp;
            $did_resize                     = true;
        }

        if ( $resized_original = self::image_make_intermediate_size( $original_image_path, $new_width, $new_height ) ) {
            $image_sizes[ $size ] = $resized_original;
            $did_resize           = true;
        }

        if ( ! $did_resize ) {
            return false;
        }

        // Update the image metadata
        $updated_metadata = [
            'sizes' => $image_sizes
        ];

        $metadata = array_merge( $metadata, $updated_metadata );
        wp_update_attachment_metadata( $attachment_id, $metadata );

        return true;
    }

    /**
     * This private static function resizes the image and saves the resized image in WordPress.
     *
     * @param string $file The path to the original image file.
     * @param int $width The maximum width of the image. The image will be resized proportionally to fit this width.
     * @param int $height The maximum height of the image. The image will be resized proportionally to fit this height.
     * @param string|null $mime The MIME type of the image.
     * @param int $quality The quality of the resized image.
     * @param bool $crop Whether to crop the image to fit the specified dimensions.
     *
     * @return array|false The metadata of the resized image if successful, false otherwise.
     */
    private static function image_make_intermediate_size( string $file, int $width, int $height, string $mime = null, int $quality = 80, bool $crop = false ) {
        if ( $width || $height ) {
            $editor = wp_get_image_editor( $file );

            if ( is_wp_error( $editor ) || is_wp_error( $editor->resize( $width, $height, $crop ) ) ) {
                return false;
            }

            $editor->set_quality( $quality );
            $mime_type    = $mime ? 'image/' . $mime : null;
            $resized_file = $editor->save( null, $mime_type );

            if ( ! is_wp_error( $resized_file ) && $resized_file ) {
                unset( $resized_file[ 'path' ] );

                return $resized_file;
            }
        }

        return false;
    }

    /**
     * Get the path of the image file for a given attachment ID.
     *
     * @param int $attachment_id The ID of the attachment.
     *
     * @return string|false The path of the image file, or false if it could not be retrieved.
     */
    private static function get_image_path( int $attachment_id ) {
        $image_path = get_attached_file( $attachment_id );

        return $image_path ?: false;
    }

    /**
     * @return int
     */
    public static function get_default_width(): int {
        return self::$default_width;
    }

}

new Images();
