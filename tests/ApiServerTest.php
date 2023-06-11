<?php namespace Peroks\ApiServer\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Api Server test case.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT
 */
final class ApiServerTest extends TestCase {

	protected function setUp(): void {}

	/**
	 * Data provider for testSearchProductRequest().
	 *
	 * @return array[][]
	 */
	public static function getTestData(): array {
		return [[]];
	}

	/**
	 * @dataProvider getTestData
	 */
	public function testValidate(): void {
	}
}
