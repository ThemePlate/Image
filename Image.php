<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

use Intervention\Image\ImageManager;

class Image {

	private static $sizes = array();
	private static $manipulations = array();
	private static $manager;


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


	private static function filter( $file, $name, $args ) {

		if ( ! self::$manager ) {
			self::$manager = new ImageManager( array( 'driver' => 'gd' ) );
		}

		$image = self::$manager->make( $file );

		return call_user_func_array( array( $image, $name ), (array) $args );

	}


	public static function test( $attachment_id ) {

		$file  = get_attached_file( $attachment_id );
		$image = self::filter( $file, 'pixelate', 10 );

		$image->save( $file );

	}

}
