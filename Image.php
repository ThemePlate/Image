<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

use Intervention\Image\ImageManager;
use ThemePlate\Image\MetaHelper;
use ThemePlate\Image\Handler;
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

			$callback_func = array( new Handler( $attachment_id, self::$manager ), 'process' );
			$callback_args = array( $size, self::$sizes[ $size ], self::$manipulations[ $size ] );

			if ( self::$tasks instanceof Tasks ) {
				self::$tasks->add( $callback_func, $callback_args );
			} else {
				call_user_func_array( $callback_func, $callback_args );
			}
		}

		return false === $image ? array() : $image;

	}

}
