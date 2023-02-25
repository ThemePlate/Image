<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

use Error;
use Intervention\Image\ImageManager;
use ThemePlate\Image\Imager;
use ThemePlate\Process\Tasks;

/**
 * @method static Imager register( string $name, int $width, int $height, bool $crop = false )
 * @method static Imager manipulate( string $size, string $filter, array $args = array() )
 * @method static Imager manager( ImageManager $manager )
 * @method static Imager tasks( Tasks $tasks )
 * @method static array action( array|false $image, int $attachment_id, string|int[] $size )
 * @method static array dump()
 */
class Image {

	private static ?Imager $imager = null;


	public static function __callStatic( string $name, array $arguments ) {

		if ( ! self::$imager instanceof Imager ) {
			self::$imager = new Imager();
		}

		if ( method_exists( self::$imager, $name ) ) {
			return call_user_func_array( array( self::$imager, $name ), $arguments );
		}

		throw new Error( 'Call to undefined method ' . __CLASS__ . '::' . $name . '()' );

	}


	public static function processor( Tasks $tasks = null ): ?Tasks {

		if ( ! self::$imager instanceof Imager ) {
			self::$imager = new Imager();
		}

		if ( null === $tasks && class_exists( Tasks::class ) ) {
			$tasks = new Tasks( __CLASS__ );
		}

		if ( $tasks instanceof Tasks ) {
			self::$imager->tasks( $tasks );
		}

		add_filter( 'wp_get_attachment_image_src', array( self::$imager, 'action' ), 10, 3 );

		return $tasks;

	}

}
