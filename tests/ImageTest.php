<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use Brain\Monkey;
use Error;
use PHPUnit\Framework\TestCase;
use ThemePlate\Image;
use ThemePlate\Process\Tasks;

class ImageTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_undefined_method(): void {
		$this->expectException( Error::class );
		$this->expectExceptionMessage( 'Call to undefined method ' . Image::class . '::unknown()' );
		call_user_func( array( Image::class, 'unknown' ) );
	}

	public function for_processor(): array {
		return array(
			array( false ),
			array( true ),
		);
	}

	/** @dataProvider for_processor */
	public function test_processor( bool $with_tasks ): void {
		$actual = Image::processor( $with_tasks ? $this->getMockBuilder( 'ThemePlate\Process\Tasks' )->getMock() : null );

		$this->assertSame( 10, has_filter( 'wp_get_attachment_image_src', 'ThemePlate\Image\Imager->action()' ) );

		if ( $with_tasks ) {
			$this->assertInstanceOf( Tasks::class, $actual );
		} else {
			$this->assertNull( $actual );
		}
	}
}
