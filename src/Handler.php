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

		$driver = ProcessHelper::get_driver();

		if ( PHP_VERSION_ID >= 80000 ) {
			$driver = sprintf( 'Intervention\Image\Drivers\%s\Driver', ucfirst( $driver ) );
		} else {
			$driver = compact( 'driver' );
		}

		$this->manager = $manager ?? new ImageManager( $driver );

	}


	public function process( string $size, array $data ): bool {

		$file = get_attached_file( $this->attachment_id );

		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

		ProcessHelper::prepare( $data );

		$args = ProcessHelper::parse_args( $data['size_arguments'] );
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

		if ( 'resize' === $type && PHP_VERSION_ID >= 80000 ) {
			$type = 'scale';
		}

		array_unshift(
			$data['manipulations'],
			array(
				'filter' => $type,
				'args'   => $args,
			)
		);

		$image = $this->filter( $file, $data['manipulations'] );
		$info  = pathinfo( $file );
		$name  = $info['filename'] . '-' . $size . '.' . $info['extension'];

		$meta['sizes'][ $size ]['file']      = $name;
		$meta['sizes'][ $size ]['width']     = $image->width();
		$meta['sizes'][ $size ]['height']    = $image->height();
		$meta['sizes'][ $size ]['mime-type'] = PHP_VERSION_ID >= 80000 ? $image->origin()->mediaType() : $image->mime();

		$image->save( $info['dirname'] . '/' . $name, 100 );
		unset( $meta['tpi_lock'][ $size ] );

		return MetaHelper::update_meta( $this->attachment_id, $meta );

	}


	protected function filter( string $image, array $manipulations ): Image {

		if ( PHP_VERSION_ID >= 80000 ) {
			$image = $this->manager->read( $image );

			return $image->modify( new Modify( $manipulations ) );
		}

		$image = $this->manager->make( $image );

		return $image->filter( new Filter( $manipulations ) );

	}

}
