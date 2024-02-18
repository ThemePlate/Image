<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Image\ProcessHelper;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\expect;

class ProcessHelperTest extends TestCase {
	public function test_get_driver(): void {
		$this->assertSame( 'gd', ProcessHelper::get_driver() );
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// $this->assertSame( 'imagick', ProcessHelper::get_driver() );
	}

	public function for_position(): array {
		// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		return array(
			'no actual cropping' => array(
				array(
					'width'  => 400,
					'height' => 300,
					'crop'   => array( null, null ),
				),
				array(
					'pos_x' => 0.0,
					'pos_y' => 0.0,
				),
			),
			'half at center:center' => array(
				array(
					'width'  => 200,
					'height' => 150,
					'crop'   => array( null, null ),
				),
				array(
					'pos_x' => 100.0,
					'pos_y' => 75.0,
				),
			),
			'half at left:center' => array(
				array(
					'width'  => 200,
					'height' => 150,
					'crop'   => array( 'left', null ),
				),
				array(
					'pos_x' => 0,
					'pos_y' => 75.0,
				),
			),
			'half at right:center' => array(
				array(
					'width'  => 200,
					'height' => 150,
					'crop'   => array( 'right', null ),
				),
				array(
					'pos_x' => 200,
					'pos_y' => 75.0,
				),
			),
			'half at center:top' => array(
				array(
					'width'  => 200,
					'height' => 150,
					'crop'   => array( null, 'top' ),
				),
				array(
					'pos_x' => 100.0,
					'pos_y' => 0,
				),
			),
			'half at center:bottom' => array(
				array(
					'width'  => 200,
					'height' => 150,
					'crop'   => array( null, 'bottom' ),
				),
				array(
					'pos_x' => 100.0,
					'pos_y' => 150,
				),
			),
		);
		// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	}

	/** @dataProvider for_position */
	public function test_position( array $size, $expected ): void {
		$this->assertSame(
			$expected,
			ProcessHelper::position(
				$size,
				array(
					'width'  => 400,
					'height' => 300,
				)
			)
		);
	}

	public function test_forced_refresh(): void {
		expect( 'get_metadata' )->once()->andReturn( array() );
		expect( 'update_metadata' )->once()->andReturn( true );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		global $_REQUEST;

		$_REQUEST['tpi_refresh'] = '123';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		ProcessHelper::maybe_force_refresh( $_REQUEST['tpi_refresh'], 'test' );
		$this->assertTrue( true );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_REQUEST['tpi_refresh'] );
	}
}
