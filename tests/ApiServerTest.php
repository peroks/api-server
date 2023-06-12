<?php namespace Peroks\ApiServer\Tests;

use Peroks\ApiServer\Dispatcher;
use Peroks\ApiServer\Handler;
use Peroks\ApiServer\Middleware;
use Peroks\ApiServer\Registry;
use Peroks\ApiServer\Server;
use Peroks\ApiServer\ServerException;
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

	private Server $server;

	public function setUp(): void {
		$this->server = new Server();
	}

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

	/**
	 * Check server instances.
	 *
	 * @return void
	 */
	public function testServerInstances(): void {
		$this->assertInstanceOf( Server::class, $this->server );
		$this->assertInstanceOf( Registry::class, $this->server->registry );
		$this->assertInstanceOf( Handler::class, $this->server->handler );
		$this->assertInstanceOf( Dispatcher::class, $this->server->dispatcher );
	}

	public function testRegisterMiddleware(): void {

		// Create a middleware entry for testing.
		$entry = Middleware::create( [
			'id'       => TestMiddleware::class,
			'name'     => 'Middleware instance for testing',
			'priority' => 20,
			'instance' => new TestMiddleware(),
		] );

		// Since the middleware entry is not yet registered, it should not be found.
		$result = $this->server->registry->hasMiddleware( $entry->id );
		$this->assertFalse( $result );

		// Register a new middleware entry and check the result.
		$result = $this->server->registry->addMiddleware( $entry );
		$this->assertTrue( $result );

		// The entry id must be unique, so the same entry can't be registered twice.
		$result = $this->server->registry->addMiddleware( $entry );
		$this->assertFalse( $result );

		// Check that the middleware entry is registered.
		$result = $this->server->registry->hasMiddleware( $entry->id );
		$this->assertTrue( $result );

		// Check that the correct middleware entry is returned.
		$result = $this->server->registry->getMiddleware( $entry->id );
		$this->assertEquals( $entry, $result );

		// Check that one middleware entries are registered..
		$result = $this->server->registry->getMiddlewareEntries();
		$this->assertCount( 1, $result );
		$this->assertEquals( $entry, $result[0] );

		// Remove the middleware entry and check the result.
		$result = $this->server->registry->removeMiddleware( $entry->id );
		$this->assertEquals( $entry, $result );

		// Check that the middleware entry was removed.
		$result = $this->server->registry->hasMiddleware( $entry->id );
		$this->assertFalse( $result );

		// Check that getting an unregistered entry is throwing an exception.
		$this->expectException( ServerException::class );
		$this->server->registry->getMiddleware( $entry->id );
	}

	#[DataProvider( 'getTestData' )]
	public function testEndpoint(): void {
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	#[DataProvider( 'getTestData' )]
	public function testPlaceholder(): void {
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
