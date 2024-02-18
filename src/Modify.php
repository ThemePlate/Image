<?php

/**
 * Handler for image
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Image;

use Intervention\Image\Interfaces\ModifierInterface;
use Intervention\Image\Interfaces\ImageInterface;

class Modify implements ModifierInterface {

	private array $manipulations;


	public function __construct( array $manipulations ) {

		$this->manipulations = $manipulations;

	}


	public function apply( ImageInterface $image ): ImageInterface {

		if ( ! empty( $this->manipulations ) ) {
			foreach ( $this->manipulations as $manipulation ) {
				if ( ! isset( $manipulation['filter'], $manipulation['args'] ) ) {
					continue;
				}

				$image = call_user_func_array( array( $image, $manipulation['filter'] ), array_values( $manipulation['args'] ) );
			}
		}

		return $image;

	}

}
