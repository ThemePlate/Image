<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use Brain\Monkey;
use ThemePlate\Image\MetaHelper;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\expect;

class MetaHelperTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_get_meta(): void {
		$expected = array( 'from_db' => true );

		// First from function then the rest from cache
		expect( 'get_metadata' )->once()->andReturn( $expected );

		$this->assertEquals( $expected, MetaHelper::get_meta( 0 ) );
		$this->assertEquals( $expected, MetaHelper::get_meta( 0 ) );
		$this->assertEquals( $expected, MetaHelper::get_meta( 0 ) );
	}

	public function test_update_meta(): void {
		$expected = array( 'manual_set' => true );

		expect( 'update_metadata' )->andReturn( true );

		$this->assertTrue( MetaHelper::update_meta( 1, $expected ) );
		$this->assertEquals( $expected, MetaHelper::get_meta( 1 ) );
	}

	public function test_lock_attachment(): void {
		$expected = array( 'tpi_lock' => array( 'size_name' => true ) );

		expect( 'get_metadata' )->andReturn( $expected );
		expect( 'update_metadata' )->andReturn( true );

		MetaHelper::lock_attachment( 2, 'size_name' );
		$this->assertEquals( $expected, MetaHelper::get_meta( 2 ) );
	}

	public function test_is_processed(): void {
		expect( 'get_metadata' )->once()->andReturn( array() );
		expect( 'update_metadata' )->times( 3 )->andReturn( true );

		// Image is unknown
		$this->assertTrue( MetaHelper::is_processed( 3, 'size_name' ) );

		// Image is locked
		MetaHelper::update_meta( 3, array( 'tpi_lock' => array( 'size_name' => true ) ) );
		$this->assertTrue( MetaHelper::is_processed( 3, 'size_name' ) );

		// Image is processed
		MetaHelper::update_meta( 3, array( 'sizes' => array( 'size_name' => true ) ) );
		$this->assertTrue( MetaHelper::is_processed( 3, 'size_name' ) );

		// Image is fresh
		MetaHelper::update_meta( 3, array( 'data' ) );
		$this->assertFalse( MetaHelper::is_processed( 3, 'size_name' ) );
	}
}
