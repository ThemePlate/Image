<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Image;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image as ImageImage;

class Filter implements FilterInterface {

	private array $manipulations;


	public function __construct( array $manipulations ) {

		$this->manipulations = $manipulations;

	}


	public function applyFilter( ImageImage $image ): ImageImage {

		if ( ! empty( $this->manipulations ) ) {
			foreach ( $this->manipulations as $manipulation ) {
				$image = call_user_func_array( array( $image, $manipulation['filter'] ), $manipulation['args'] );
			}
		}

		return $image;

	}

}
