<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Image;

use Intervention\Image\ImageManager;
use ThemePlate\Process\Tasks;

class Imager {

	protected array $storage = array();

	protected ?ImageManager $manager = null;

	protected ?Tasks $tasks = null;


	public function register( string $name, int $width, int $height, bool $crop = false ): Imager {

		$this->storage[ $name ]['size_arguments'] = compact( 'width', 'height', 'crop' );

		return $this;

	}


	public function manipulate( string $size, string $filter, array $args = array() ): Imager {

		$this->storage[ $size ]['manipulations'][] = compact( 'filter', 'args' );

		return $this;

	}


	public function manager( ImageManager $manager ): Imager {

		$this->manager = $manager;

		return $this;

	}


	public function tasks( Tasks $tasks ): Imager {

		$this->tasks = $tasks;

		return $this;

	}


	/**
	 * @param array|false  $image
	 * @param string|int[] $size
	 *
	 * @return array|false
	 */
	public function action( $image, int $attachment_id, $size ) {

		if ( is_array( $size ) ) {
			return $image;
		}

		ProcessHelper::maybe_force_refresh( $attachment_id, $size );

		if ( ! is_admin() && ! empty( $this->storage[ $size ] ) && ! MetaHelper::is_processed( $attachment_id, $size ) ) {
			MetaHelper::lock_attachment( $attachment_id, $size );

			$callback_func = array( new Handler( $attachment_id, $this->manager ), 'process' );
			$callback_args = array( $size, $this->storage[ $size ] );

			if ( $this->tasks instanceof Tasks ) {
				$this->tasks->add( $callback_func, $callback_args );
			} else {
				call_user_func_array( $callback_func, $callback_args );
			}
		}

		return $image;

	}


	public function dump(): array {

		return $this->storage;

	}

}
