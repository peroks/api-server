<?php namespace Peroks\ApiServer\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use Peroks\ApiServer\Dispatcher;
use Peroks\ApiServer\Endpoint;
use Peroks\ApiServer\Event;
use Peroks\ApiServer\Handler;
use Peroks\ApiServer\Listener;
use Peroks\ApiServer\Middleware;
use Peroks\ApiServer\Registry;
use Peroks\ApiServer\Server;
use Peroks\ApiServer\ServerException;
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
	 * Check core server instances.
	 *
	 * @return void
	 */
	public function testCoreInstances(): void {
		$this->assertInstanceOf( Server::class, $this->server );
		$this->assertInstanceOf( Registry::class, $this->server->registry );
		$this->assertInstanceOf( Handler::class, $this->server->handler );
		$this->assertInstanceOf( Dispatcher::class, $this->server->dispatcher );
	}

	/**
	 * Registers server endpoints and checks their results.
	 */
	public function testEndpoints(): void {

		// Create a request handler.
		$handler = new TestHandler();

		// Create an endpoint for saying "Hallo World".
		$hello = new Endpoint( [
			'id'      => 'hello',
			'route'   => '/test',
			'method'  => Endpoint::GET,
			'handler' => $handler,
		] );

		// Create an endpoint for echoing a greeting.
		$echo = new Endpoint( [
			'id'      => 'echo',
			'route'   => '/test',
			'method'  => Endpoint::POST,
			'handler' => $handler,
		] );

		// Register a new endpoint and check the result.
		$result = $this->server->registry->addEndpoint( $hello );
		$this->assertTrue( $result );

		// Register a second endpoint and check the result.
		$result = $this->server->registry->addEndpoint( $echo );
		$this->assertTrue( $result );

		// The endpoint must be unique, so the same endpoint can't be registered twice.
		$result = $this->server->registry->addEndpoint( $echo );
		$this->assertFalse( $result );

		// Check that the echo endpoint is registered.
		$result = $this->server->registry->hasEndpoint( $echo->route, $echo->method );
		$this->assertTrue( $result );

		// Check that the correct endpoint is returned.
		$result = $this->server->registry->getEndpoint( $echo->route, $echo->method );
		$this->assertEquals( $echo, $result );

		// Check that both endpoints are registered.
		$result = current( $this->server->registry->getEndpoints() );
		$this->assertCount( 2, $result );
		$this->assertEquals( $hello, $result[ $hello->method ] );
		$this->assertEquals( $echo, $result[ $echo->method ] );

		// Send a GET request and check the result.
		$request  = new ServerRequest( 'GET', '/test' );
		$response = $this->server->handle( $request );
		$this->assertEquals( 'Hello World', $response->getBody() );

		// Send a POST request and check the result.
		$request  = new ServerRequest( 'POST', '/test', [], 'Greetings' );
		$response = $this->server->handle( $request );
		$this->assertEquals( 'Greetings', $response->getBody() );

		// Remove the hello endpoint and check the result.
		$result = $this->server->registry->removeEndpoint( $hello->route );
		$this->assertEquals( $hello, $result );

		// Check that the hello endpoint was removed.
		$result = $this->server->registry->hasEndpoint( $hello->route );
		$this->assertFalse( $result );

		// Check that getting an unregistered entry is throwing an exception.
		$this->expectException( ServerException::class );
		$this->server->registry->getEndpoint( $hello->route );
	}

	/**
	 * Registers server middleware and checks their results.
	 */
	public function testMiddleware(): void {

		// Register an endpoint.
		$this->server->registry->addEndpoint( new Endpoint( [
			'id'      => 'echo',
			'route'   => '/test',
			'method'  => Endpoint::POST,
			'handler' => new TestHandler(),
		] ) );

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

		// Check that one middleware entries are registered.
		$result = $this->server->registry->getMiddlewareEntries();
		$this->assertCount( 1, $result );
		$this->assertEquals( $entry, $result[0] );

		// The middleware returns 403 Forbidden for all unauthorized requests.
		$request  = new ServerRequest( 'POST', '/test', [], 'Hello World' );
		$response = $this->server->handle( $request );
		$this->assertEquals( 403, $response->getStatusCode() );

		// The middleware lets authorized requests pass through.
		$request  = new ServerRequest( 'POST', '/test', [ 'authorization' => 'yes' ], 'Hello World' );
		$response = $this->server->handle( $request );
		$this->assertEquals( 'Hello World', $response->getBody() );

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

	public function testDispatcher(): void {

		// Register an endpoint.
		$this->server->registry->addEndpoint( new Endpoint( [
			'id'      => 'echo',
			'route'   => '/test',
			'method'  => Endpoint::POST,
			'handler' => new TestHandler(),
		] ) );

		// Register a middleware.
		$this->server->registry->addMiddleware( new Middleware( [
			'id'       => TestMiddleware::class,
			'name'     => 'Middleware instance for testing',
			'priority' => 20,
			'instance' => new TestMiddleware(),
		] ) );

		// Create an event listener that adds authorization to all requests.
		$listener = new Listener( [
			'id'       => 'test',
			'type'     => 'handle',
			'callback' => function( Event $event ) {
				$event->data = $event->data->withHeader( 'authorization', 'yes' );
			},
		] );

		// Since the event listener is not yet registered, it should not be found.
		$result = $this->server->registry->hasListener( $listener->id, $listener->type );
		$this->assertFalse( $result );

		// The middleware returns 403 Forbidden for all unauthorized requests.
		$request  = new ServerRequest( 'POST', '/test', [], 'Hello World' );
		$response = $this->server->handle( $request );
		$this->assertEquals( 403, $response->getStatusCode() );

		// Register the event listener and check the result.
		$result = $this->server->registry->addListener( $listener );
		$this->assertTrue( $result );

		// The listener id and type must be unique, so the same entry can't be registered twice.
		$result = $this->server->registry->addListener( $listener );
		$this->assertFalse( $result );

		// Check that the event listener is registered.
		$result = $this->server->registry->hasListener( $listener->id, $listener->type );
		$this->assertTrue( $result );

		// Check that the correct event listener is returned.
		$result = $this->server->registry->getListener( $listener->id, $listener->type );
		$this->assertEquals( $listener, $result );

		// Check that one event listener is registered for the "handle" event.
		$result = $this->server->registry->getTypeListeners( $listener->type );
		$this->assertCount( 1, $result );
		$this->assertEquals( $listener, $result[0] );

		// Check that the event listener are registered.
		$result = current( $this->server->registry->getListeners() );
		$this->assertCount( 1, $result );
		$this->assertEquals( $listener, current( $result ) );

		// Now the request is authorized by the event listener.
		$request  = new ServerRequest( 'POST', '/test', [], 'Hello World' );
		$response = $this->server->handle( $request );
		$this->assertEquals( 'Hello World', $response->getBody() );

		// Remove the event listener and check the result.
		$result = $this->server->registry->removeListener( $listener->id, $listener->type );
		$this->assertEquals( $listener, $result );

		// Check that the event listener was removed.
		$result = $this->server->registry->hasListener( $listener->id, $listener->type );
		$this->assertFalse( $result );

		// Check that getting an unregistered event listener is throwing an exception.
		$this->expectException( ServerException::class );
		$this->server->registry->getEndpoint( $listener->id, $listener->type );
	}

	public function testPlaceholder(): void {
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
