<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\Image;

class MetaHelper {

	private static array $storage = array();


	public static function is_processed( int $attachment_id, string $size ): bool {

		$meta = self::get_meta( $attachment_id );

		if ( empty( $meta ) ) {
			return true;
		}

		if ( isset( $meta['tpi_lock'][ $size ] ) ) {
			return true;
		}

		return isset( $meta['sizes'][ $size ] );

	}


	public static function get_meta( int $attachment_id ): array {

		if ( empty( self::$storage[ $attachment_id ] ) ) {
			$meta = get_metadata( 'post', $attachment_id, '_wp_attachment_metadata', true );

			if ( $meta ) {
				self::$storage[ $attachment_id ] = $meta;
			} else {
				self::$storage[ $attachment_id ] = array();
			}
		}

		return self::$storage[ $attachment_id ];

	}


	public static function update_meta( int $attachment_id, array $data ): bool {

		self::$storage[ $attachment_id ] = $data;

		return update_metadata( 'post', $attachment_id, '_wp_attachment_metadata', $data );

	}


	public static function lock_attachment( int $attachment_id, string $size ): void {

		$meta = self::get_meta( $attachment_id );

		$meta['tpi_lock'][ $size ] = true;

		self::update_meta( $attachment_id, $meta );

	}

}
