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

	protected int $attachment_id;
	protected ImageManager $manager;


	public function __construct( int $attachment_id, ImageManager $manager = null ) {

		$this->attachment_id = $attachment_id;

		$this->manager = $manager ?? new ImageManager( ProcessHelper::get_driver() );

	}


	public function process( string $size, array $args, array $manipulations ): bool {

		$file = get_attached_file( $this->attachment_id );

		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

		$type = $args['crop'] ? 'crop' : 'resize';
		$meta = MetaHelper::get_meta( $this->attachment_id );

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

		return MetaHelper::update_meta( $this->attachment_id, $meta );

	}


	protected function filter( string $image, array $manipulations ): Image {

		$image = $this->manager->make( $image );

		return $image->filter( new Filter( $manipulations ) );

	}

}
