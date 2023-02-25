<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;
use ThemePlate\Image;
use ThemePlate\Image\Imager;
use function Brain\Monkey\Functions\expect;

class ImagerTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function for_register(): array {
		return array(
			array( 'size1', 80, 80, true ),
			array( 'size2', 150, 150, false ),
			array( 'size3', 1920, 1080, false ),
		);
	}

	/** @dataProvider for_register */
	public function test_register( string $name, int $width, int $height, bool $crop ): void {
		$imager = ( new Imager() )->register( $name, $width, $height, $crop );
		$actual = $imager->dump();

		$this->assertArrayHasKey( $name, $actual );
		$this->assertSame( compact( 'width', 'height', 'crop' ), $actual[ $name ]['size_args'] );
	}

	public function for_manipulate(): array {
		return array(
			array( 'size1', 'greyscale', array() ),
			array( 'size1', 'opacity', array( 50 ) ),
			array( 'size1', 'pixelate', array( 12 ) ),
		);
	}

	/** @dataProvider for_manipulate */
	public function test_manipulate( string $size, string $filter, array $args ): void {
		$imager = ( new Imager() )->manipulate( $size, $filter, $args );
		$actual = $imager->dump();

		$this->assertArrayHasKey( $size, $actual );
		$this->assertSame( compact( 'filter', 'args' ), $actual[ $size ]['manipulations'][0] );
	}

	public function for_action(): array {
		return array(
			'with_array_of_ids'  => array(
				array( 1, 2 ),
				'size',
				true,
			),
			'with_a_string_size' => array(
				array( 'image_data' ),
				'size1',
				true,
			),
			'with_size_as_array' => array(
				array( 'image_data' ),
				array( 80, 80 ),
				true,
			),
			'with_unknown_image' => array(
				false,
				'size',
				false,
			),
		);
	}

	/** @dataProvider for_action */
	public function test_action( $image, $size, bool $with_data ): void {
		expect( 'is_admin' )->andReturn( true );

		$expected = $with_data ? $image : array();
		$actual   = Image::action( $image, 0, $size );

		$this->assertSame( $expected, $actual );
	}
}
