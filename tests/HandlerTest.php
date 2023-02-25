<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;
use ThemePlate\Image\Handler;
use function Brain\Monkey\Functions\expect;

class HandlerTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_action() {
		expect( 'get_attached_file' )->twice()->andReturn( false, __DIR__ . '/screenshot.png' );
		expect( 'get_metadata' )->once()->andReturn( array() );
		expect( 'update_metadata' )->once()->andReturn( true );

		$handler = new Handler();
		$args = array(
			9,
			'processed',
			array(
				'width'  => 160,
				'height' => 120,
				'crop'   => false,
			),
			array(
				array(
					'filter' => 'blur',
					'args'   => array(),
				),
			),
		);

		self::assertFalse( call_user_func_array( array( $handler, 'process' ), $args ) ); // Unknown file path
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@unlink( __DIR__ . '/screenshot-processed.png' ); // Remove processed image
		self::assertTrue( call_user_func_array( array( $handler, 'process' ), $args ) );
		self::assertTrue( file_exists( __DIR__ . '/screenshot-processed.png' ) );

		list( $width, $height ) = getimagesize( __DIR__ . '/screenshot-processed.png' );

		$this->assertSame( 160, $width );
		$this->assertSame( 120, $height );
	}
}
