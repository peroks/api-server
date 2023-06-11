<?php namespace Peroks\ApiServer\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Api Server test case.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT
 */
final class ApiServerTest extends TestCase {

	public static function setUpBeforeClass(): void {}

	public function setUp(): void {}

	/**
	 * Data provider for testSearchProductRequest().
	 *
	 * @return array[][]
	 */
	public static function getTestData(): array {
		return [
			'test-data-01' => [],
			'test-data-02' => [],
			'test-data-03' => [],
		];
	}

	#[DataProvider( 'getTestData' )]
	public function testValidate(): void {
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
