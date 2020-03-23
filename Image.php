<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

class Image {

	private static $sizes = array();
	private static $manipulations = array();


	public static function register( $name, $width, $height ) {

		self::$sizes[ $name ] = compact( 'width', 'height' );

		return self::$sizes;

	}


	public static function manipulate( $size, $filter, $args ) {

		self::$manipulations[ $size ][] = compact( 'filter', 'args' );

		return self::$manipulations;

	}


	public static function get_html( $attachment_id, $size ) {

		return wp_get_attachment_image( $attachment_id, $size );

	}


	public static function get_url( $attachment_id, $size ) {

		return wp_get_attachment_image_url( $attachment_id, $size );

	}

}
