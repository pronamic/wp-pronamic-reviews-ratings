<?php
/**
 * Utilities.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Utilities.
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class Util {
	/**
	 * Array to HTML attributes.
	 *
	 * @param array $attributes Key and value pairs to convert to HTML attributes.
	 * @return string
	 */
	public static function array_to_html_attributes( array $attributes ) {
		$html = '';

		foreach ( $attributes as $key => $value ) {
			// Check boolean attribute.
			if ( \is_bool( $value ) ) {
				if ( $value ) {
					$html .= sprintf( '%s ', $key );
				}

				continue;
			}

			$html .= sprintf( '%s="%s" ', $key, esc_attr( $value ) );
		}

		$html = trim( $html );

		return $html;
	}
}
