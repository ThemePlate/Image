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
use ThemePlate\Image\Filter;
use ThemePlate\Image\ProcessHelper;
use ThemePlate\Image\MetaHelper;
use ThemePlate\Process\Tasks;

class Image {

	private static array $sizes         = array();
	private static array $manipulations = array();

	private static ?ImageManager $manager = null;
	private static ?Tasks $tasks          = null;


	public static function register( string $name, int $width, int $height, bool $crop = false ): array {

		self::$sizes[ $name ] = compact( 'width', 'height', 'crop' );

		self::$manipulations[ $name ] = array();

		return self::$sizes;

	}


	public static function manipulate( string $size, string $filter, array $args = array() ): array {

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


	public static function manager( ImageManager $manager ): void {

		self::$manager = $manager;

	}


	/**
	 * @param array|false  $image
	 * @param string|int[] $size
	 */
	public static function hooker( $image, int $attachment_id, $size ): array {

		if ( is_array( $size ) ) {
			return $image;
		}

		if ( ! empty( self::$sizes[ $size ] ) && ! is_admin() && ! MetaHelper::is_processed( $attachment_id, $size ) ) {
			MetaHelper::lock_attachment( $attachment_id, $size );

			if ( self::$tasks instanceof Tasks ) {
				self::$tasks->add( array( __CLASS__, 'process' ), array( $attachment_id, $size ) );
			} else {
				self::process( $attachment_id, $size );
			}
		}

		return false === $image ? array() : $image;

	}


	public static function process( int $attachment_id, string $size ): bool {

		$file = get_attached_file( $attachment_id );

		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

		$args = self::$sizes[ $size ];
		$type = $args['crop'] ? 'crop' : 'resize';
		$meta = MetaHelper::get_meta( $attachment_id );

		if ( is_array( $args['crop'] ) ) {
			$args += ProcessHelper::position( $args, $meta );
		}

		if ( 'resize' === $type ) {
			$args[] = static function( $constraint ) {
				$constraint->aspectRatio();
				$constraint->upsize();
			};
		}

		unset( $args['crop'] );

		$manipulations = array_merge(
			array(
				array(
					'filter' => $type,
					'args'   => $args,
				),
			),
			self::$manipulations[ $size ]
		);

		$image = self::filter( $file, $manipulations );
		$info  = pathinfo( $file );
		$name  = $info['filename'] . '-' . $size . '.' . $info['extension'];

		$meta['sizes'][ $size ]['file']      = $name;
		$meta['sizes'][ $size ]['width']     = $image->width();
		$meta['sizes'][ $size ]['height']    = $image->height();
		$meta['sizes'][ $size ]['mime-type'] = $image->mime();

		$image->save( $info['dirname'] . '/' . $name, 100 );
		unset( $meta['tpi_lock'][ $size ] );

		return MetaHelper::update_meta( $attachment_id, $meta );

	}


	private static function filter( string $image, array $manipulations ): ImageImage {

		if ( ! self::$manager instanceof ImageManager ) {
			self::$manager = new ImageManager( ProcessHelper::get_driver() );
		}

		$image = self::$manager->make( $image );

		return $image->filter( new Filter( $manipulations ) );

	}

}
