<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Image;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class Handler {

	protected ImageManager $manager;


	public function __construct( ImageManager $manager = null ) {

		$this->manager = $manager ?? new ImageManager( ProcessHelper::get_driver() );

	}


	public function process( int $attachment_id, string $size, array $args, array $manipulations ): bool {

		$file = get_attached_file( $attachment_id );

		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

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

		array_unshift(
			$manipulations,
			array(
				'filter' => $type,
				'args'   => $args,
			)
		);

		$image = $this->filter( $file, $manipulations );
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


	protected function filter( string $image, array $manipulations ): Image {

		$image = $this->manager->make( $image );

		return $image->filter( new Filter( $manipulations ) );

	}

}
