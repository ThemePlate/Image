<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\Image;

class ProcessHelper {

	public static function get_driver(): array {

		$config = array(
			'driver' => 'gd',
		);

		if ( class_exists( 'Imagick', false ) || extension_loaded( 'imagick' ) ) {
			$config['driver'] = 'imagick';
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

}
