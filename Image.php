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
	private static $storage = array();
	private static $manager;
	private static $tasks;


	public static function register( $name, $width, $height, $crop = false ) {

		self::$sizes[ $name ] = compact( 'width', 'height', 'crop' );

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

		if ( self::is_image( $attachment_id ) && ! self::is_processed( $attachment_id, $size ) && ! empty( self::$sizes[ $size ] ) ) {
			self::lock_attachment( $attachment_id, $size );

			if ( self::$tasks instanceof Tasks ) {
				self::$tasks->add( array( Image::class, 'process' ), array( $attachment_id, $size ) );
			} else {
				self::process( $attachment_id, $size );
			}
		}

	}


	public static function process( $attachment_id, $size ) {

		$file = get_attached_file( $attachment_id );

		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

		$args = self::$sizes[ $size ];
		$type = $args['crop'] ? 'crop' : 'resize';
		$meta = self::get_meta( $attachment_id );

		if ( is_array( $args['crop'] ) ) {
			$args += self::position( $args, $meta );
		}

		if ( 'resize' === $type ) {
			$args[] = function( $constraint ) {
				$constraint->aspectRatio();
				$constraint->upsize();
			};
		}

		unset( $args['crop'] );

		$image = self::filter( $file, $type, $args );
		$image = self::do_manipulations( $image, $size );
		$info  = pathinfo( $file );
		$name  = $info['filename'] . '-' . $size . '.' . $info['extension'];

		$meta['sizes'][ $size ] = self::$sizes[ $size ];
		$meta['sizes'][ $size ]['file'] = $name;
		$meta['sizes'][ $size ]['mime-type'] = $image->mime();

		$image->save( $info['dirname'] . '/' . $name, 100 );
		unset( $meta['tpi_lock'][ $size ] );

		return self::update_meta( $attachment_id, $meta );

	}


	private static function is_image( $attachment_id ) {

		$file = get_attached_file( $attachment_id );

		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

		$meta = self::get_meta( $attachment_id );

		return ! empty( $meta );

	}


	private static function is_processed( $attachment_id, $size ) {

		$meta = self::get_meta( $attachment_id );

		if ( isset( $meta['tpi_lock'][ $size ] ) ) {
			return true;
		}

		return isset( $meta['sizes'][ $size ] );

	}


	private static function get_meta( $attachment_id ) {

		if ( empty( self::$storage[ $attachment_id ] ) ) {
			self::$storage[ $attachment_id ] = get_metadata( 'post', $attachment_id, '_wp_attachment_metadata', true );
		}

		return self::$storage[ $attachment_id ];

	}


	private static function update_meta( $attachment_id, $data ) {

		self::$storage[ $attachment_id ] = $data;

		return update_metadata( 'post', $attachment_id, '_wp_attachment_metadata', $data );

	}


	private static function lock_attachment( $attachment_id, $size ) {

		$meta = self::get_meta( $attachment_id );

		$meta['tpi_lock'][ $size ] = true;

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


	private static function position( $size, $meta ) {

		$size['crop'] = array_values( $size['crop'] );

		$crop_x = $size['crop'][0];
		$crop_y = $size['crop'][1];
		$crop_w = $size['width'];
		$crop_h = $size['height'];
		$orig_w = $meta['width'];
		$orig_h = $meta['height'];

		if ( 'left' === $crop_x ) {
			$pos_x = 0;
		} elseif ( 'right' === $crop_x ) {
			$pos_x = $orig_w - $crop_w;
		} else {
			$pos_x = floor( ( $orig_w - $crop_w ) / 2 );
		}

		if ( 'top' === $crop_y ) {
			$pos_y = 0;
		} elseif ( 'bottom' === $crop_y ) {
			$pos_y = $orig_h - $crop_h;
		} else {
			$pos_y = floor( ( $orig_h - $crop_h ) / 2 );
		}

		return compact( 'pos_x', 'pos_y' );

	}

}
