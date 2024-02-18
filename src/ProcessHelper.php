<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\Image;

class ProcessHelper {

	public static function parse_args( array $args ): array {

		return array_merge(
			array(
				'width'  => 0,
				'height' => 0,
				'crop'   => false,
			),
			$args
		);

	}


	public static function prepare( array &$data ) {

		$data = array_merge(
			array(
				'size_arguments' => array(),
				'manipulations'  => array(),
			),
			$data
		);

	}


	public static function get_driver(): string {

		$config = 'gd';

		if ( class_exists( 'Imagick', false ) || extension_loaded( 'imagick' ) ) {
			$config = 'imagick';
		}

		return $config;

	}


	public static function position( array $size, array $meta ): array {

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


	public static function maybe_force_refresh( int $attachment_id, string $size ): void {

		if ( self::forced_refresh( $attachment_id ) ) {
			MetaHelper::unlock_attachment( $attachment_id, $size );
		}

	}


	protected static function forced_refresh( int $attachment_id ): bool {

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $_REQUEST['tpi_refresh'] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return in_array( (string) $attachment_id, (array) $_REQUEST['tpi_refresh'], true );

	}


	public static function using_v8(): bool {

		return PHP_VERSION_ID >= 80000;

	}

}
