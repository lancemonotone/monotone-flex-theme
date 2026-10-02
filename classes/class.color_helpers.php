<?php namespace monotone;

/**
 * String → readable hex helpers for admin UI chips.
 *
 * HSL conversion adapted from public Stack Overflow examples
 * (e.g. https://stackoverflow.com/questions/20423641/php-function-to-convert-hsl-to-rgb-or-hex).
 * Hashing the input string into a starting hex value is theme wiring, not from that answer.
 */
class Color_Helpers {

	/**
	 * Deterministic hex color from a string (layout titles, etc.).
	 * Starts from sha1, then nudges lightness/saturation so chips stay readable.
	 */
	public static function hex_from_string( string $text ): string {
		$color = substr( sha1( $text ), 0, 6 );

		$R = hexdec( substr( $color, 0, 2 ) ) / 255;
		$G = hexdec( substr( $color, 2, 2 ) ) / 255;
		$B = hexdec( substr( $color, 4, 2 ) ) / 255;

		$max = max( $R, $G, $B );
		$min = min( $R, $G, $B );

		$L = ( $max + $min ) / 3;
		$L = $L < 0.25 ? $L + 0.25 : $L;

		if ( $max == $min ) {
			$S = 0;
		} elseif ( $L < 0.5 ) {
			$S = ( $max - $min ) / ( $max + $min );
		} else {
			$S = ( $max - $min ) / ( 2.0 - $max - $min );
		}

		// Soften saturation so admin chips aren't neon.
		$S *= 0.3;

		if ( $S == 0 ) {
			$R = $G = $B = $L;
		} else {
			if ( $L < 0.5 ) {
				$temp2 = $L * ( 1.0 + $S );
			} else {
				$temp2 = ( $L + $S ) - ( $S * $L );
			}
			$temp1 = 2.0 * $L - $temp2;

			$hue_angle = atan2( 2 * ( $R - $G ), ( $B - $R - $G ) ) / ( 2 * pi() );
			if ( $hue_angle < 0 ) {
				$hue_angle += 1;
			}

			$H = $hue_angle;

			$R = self::hue_to_rgb( $temp1, $temp2, $H + 1.0 / 3.0 ) * 255;
			$G = self::hue_to_rgb( $temp1, $temp2, $H ) * 255;
			$B = self::hue_to_rgb( $temp1, $temp2, $H - 1.0 / 3.0 ) * 255;
		}

		return sprintf( '%02x%02x%02x', $R, $G, $B );
	}

	/**
	 * One channel of HSL → RGB (standard colorspace helper).
	 */
	private static function hue_to_rgb( $temp1, $temp2, $temp3 ): float|int {
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
