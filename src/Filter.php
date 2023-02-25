<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Image;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class Filter implements FilterInterface {

	private array $manipulations;


	public function __construct( array $manipulations ) {

		$this->manipulations = $manipulations;

	}


	public function applyFilter( Image $image ): Image {

		if ( ! empty( $this->manipulations ) ) {
			foreach ( $this->manipulations as $manipulation ) {
				if ( ! isset( $manipulation['filter'], $manipulation['args'] ) ) {
					continue;
				}

				$image = call_user_func_array( array( $image, $manipulation['filter'] ), $manipulation['args'] );
			}
		}

		return $image;

	}

}
