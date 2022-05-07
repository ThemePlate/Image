<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

use Intervention\Image\Image as ImageImage;
use Intervention\Image\ImageManager;
use ThemePlate\Process\Tasks;

class Image {

	private static array $sizes         = array();
	private static array $manipulations = array();
	private static array $storage       = array();

	private static ?ImageManager $manager = null;
	private static ?Tasks $tasks          = null;


	public static function register( string $name, int $width, int $height, bool $crop = false ): array {

		self::$sizes[ $name ] = compact( 'width', 'height', 'crop' );

		return self::$sizes;

	}


	public static function manipulate( string $size, string $filter, $args = array() ): array {

		self::$manipulations[ $size ][] = compact( 'filter', 'args' );

		return self::$manipulations;

	}


	public static function processor( Tasks $tasks = null ): ?Tasks {

		if ( ! self::$tasks instanceof Tasks && class_exists( Tasks::class ) ) {
			self::$tasks = $tasks ?? new Tasks( __CLASS__ );
		}

		add_filter( 'wp_get_attachment_image_src', array( __CLASS__, 'hooker' ), 10, 3 );

		return self::$tasks;

	}


	public static function hooker( array $image, int $attachment_id, string $size ): array {

		if ( ! empty( self::$sizes[ $size ] ) && ! is_admin() && ! self::is_processed( $attachment_id, $size ) ) {
			self::lock_attachment( $attachment_id, $size );

			if ( self::$tasks instanceof Tasks ) {
				self::$tasks->add( array( __CLASS__, 'process' ), array( $attachment_id, $size ) );
			} else {
				self::process( $attachment_id, $size );
			}
		}

		return $image;

	}


	public static function process( int $attachment_id, string $size ): bool {

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
			$args[] = static function( $constraint ) {
				$constraint->aspectRatio();
				$constraint->upsize();
			};
		}

		unset( $args['crop'] );

		$image = self::filter( $file, $type, $args );
		$image = self::do_manipulations( $image, $size );
		$info  = pathinfo( $file );
		$name  = $info['filename'] . '-' . $size . '.' . $info['extension'];

		$meta['sizes'][ $size ]['file']      = $name;
		$meta['sizes'][ $size ]['width']     = $image->width();
		$meta['sizes'][ $size ]['height']    = $image->height();
		$meta['sizes'][ $size ]['mime-type'] = $image->mime();

		$image->save( $info['dirname'] . '/' . $name, 100 );
		unset( $meta['tpi_lock'][ $size ] );

		return self::update_meta( $attachment_id, $meta );

	}


	private static function is_processed( int $attachment_id, string $size ): bool {

		$meta = self::get_meta( $attachment_id );

		if ( empty( $meta ) ) {
			return true;
		}

		if ( isset( $meta['tpi_lock'][ $size ] ) ) {
			return true;
		}

		return isset( $meta['sizes'][ $size ] );

	}


	private static function get_meta( int $attachment_id ): array {

		if ( empty( self::$storage[ $attachment_id ] ) ) {
			self::$storage[ $attachment_id ] = get_metadata( 'post', $attachment_id, '_wp_attachment_metadata', true );
		}

		return self::$storage[ $attachment_id ];

	}


	private static function update_meta( int $attachment_id, array $data ): bool {

		self::$storage[ $attachment_id ] = $data;

		return update_metadata( 'post', $attachment_id, '_wp_attachment_metadata', $data );

	}


	private static function lock_attachment( int $attachment_id, string $size ): void {

		$meta = self::get_meta( $attachment_id );

		$meta['tpi_lock'][ $size ] = true;

		self::update_meta( $attachment_id, $meta );

	}


	private static function do_manipulations( ImageImage $image, string $size ): ImageImage {

		if ( ! empty( self::$manipulations[ $size ] ) ) {
			foreach ( self::$manipulations[ $size ] as $manipulation ) {
				$image = call_user_func_array( array( $image, $manipulation['filter'] ), $manipulation['args'] );
			}
		}

		return $image;

	}


	private static function filter( string $image, string $name, array $args ): ImageImage {

		if ( ! self::$manager instanceof ImageManager ) {
			self::$manager = new ImageManager( self::get_driver() );
		}

		$image = self::$manager->make( $image );

		return call_user_func_array( array( $image, $name ), $args );

	}


	private static function position( array $size, array $meta ): array {

		$size['crop'] = array_values( $size['crop'] );

		[ $crop_x, $crop_y ] = $size['crop']; // phpcs:ignore Generic.Arrays.DisallowShortArraySyntax

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


	private static function get_driver(): array {

		$config = array(
			'driver' => 'gd',
		);

		if ( class_exists( 'Imagick', false ) || extension_loaded( 'imagick' ) ) {
			$config['driver'] = 'imagick';
		}

		return $config;

	}

}
