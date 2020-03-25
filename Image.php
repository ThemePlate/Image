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
	private static $tasks;


	public static function register( $name, $width, $height ) {

		self::$sizes[ $name ] = compact( 'width', 'height' );

		return self::$sizes;

	}


	public static function manipulate( $size, $filter, $args = null ) {

		self::$manipulations[ $size ][] = compact( 'filter', 'args' );

		return self::$manipulations;

	}


	public static function get_html( $attachment_id, $size ) {

		self::maybe_process( $attachment_id, $size );

		return wp_get_attachment_image( $attachment_id, $size );

	}


	public static function get_url( $attachment_id, $size ) {

		self::maybe_process( $attachment_id, $size );

		return wp_get_attachment_image_url( $attachment_id, $size );

	}


	public static function processor() {

		self::$tasks = new Tasks( __CLASS__ );

		if ( ! defined( 'DOING_AJAX' ) ) {
			add_action( 'shutdown', array( self::$tasks, 'execute' ) );
		}

		return self::$tasks;

	}


	private static function maybe_process( $attachment_id, $size ) {

		if ( ! self::is_processed( $attachment_id, $size ) && ! empty( self::$sizes[ $size ] ) ) {
			self::$tasks->add( array( Image::class, 'process' ), array( $attachment_id, $size ) );
		}

	}


	public static function process( $attachment_id, $size ) {

		$file = get_attached_file( $attachment_id );

		if ( ! $file ) {
			return false;
		}

		self::lock_attachment( $attachment_id );

		$image = self::filter( $file, 'crop', self::$sizes[ $size ] );
		$image = self::do_manipulations( $image, $size );
		$info  = pathinfo( $file );
		$meta  = self::get_meta( $attachment_id );
		$name  = $info['filename'] . '-' . $size . '.' . $info['extension'];

		$meta['sizes'][ $size ] = self::$sizes[ $size ];
		$meta['sizes'][ $size ]['file'] = $name;
		$meta['sizes'][ $size ]['mime-type'] = $image->mime();

		$image->save( $info['dirname'] . '/' . $name );
		unset( $meta['tpi_lock'] );

		return self::update_meta( $attachment_id, $meta );

	}


	private static function is_processed( $attachment_id, $size ) {

		$meta = self::get_meta( $attachment_id );

		if ( isset( $meta['tpi_lock'] ) ) {
			return true;
		}

		return isset( $meta['sizes'][ $size ] );

	}


	private static function get_meta( $attachment_id ) {

		return get_metadata( 'post', $attachment_id, '_wp_attachment_metadata', true );

	}


	private static function update_meta( $attachment_id, $data ) {

		return update_metadata( 'post', $attachment_id, '_wp_attachment_metadata', $data );

	}


	private static function lock_attachment( $attachment_id ) {

		$meta = self::get_meta( $attachment_id );

		$meta['tpi_lock'] = true;

		self::update_meta( $attachment_id, $meta );

	}


	private static function do_manipulations( $image, $size ) {

		if ( ! empty( self::$manipulations[ $size ] ) ) {
			foreach ( self::$manipulations[ $size ] as $manipulation ) {
				$image = call_user_func_array( array( $image, $manipulation['filter'] ), (array) $manipulation['args'] );
			}
		}

		return $image;

	}


	private static function filter( $image, $name, $args ) {

		if ( ! self::$manager instanceof \Intervention\Image\ImageManager ) {
			self::$manager = new ImageManager( array( 'driver' => 'gd' ) );
		}

		if ( ! $image instanceof \Intervention\Image\Image ) {
			$image = self::$manager->make( $image );
		}

		return call_user_func_array( array( $image, $name ), (array) $args );

	}

}
