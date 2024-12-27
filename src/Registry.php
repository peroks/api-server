<?php
/**
 * A container for registered request handlers and middleware.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */

declare( strict_types = 1 );
namespace Peroks\ApiServer;

use Peroks\Model\ModelException;

/**
 * A container for registered request handlers and middleware.
 */
class Registry {

	/**
	 * @var Endpoint[] An array of registered endpoints for PSR-15 handlers.
	 */
	protected array $endpoints = [];

	/**
	 * @var Middleware[] An array of registered entries for PSR-15 middleware.
	 */
	protected array $middleware = [];

	/**
	 * @var Listener[] An array of registered PSR-14 event listeners.
	 */
	protected array $listeners = [];

	/**
	 * @var array Cached registry objects.
	 */
	protected array $cache = [];

	/**
	 * @var Server The Api Server.
	 */
	protected Server $server;

	/**
	 * Constructor.
	 *
	 * @param Server $server The Api Server.
	 */
	public function __construct( Server $server ) {
		$this->server = $server;
	}

	/* -------------------------------------------------------------------------
	 * Endpoints
	 * -----------------------------------------------------------------------*/

	/**
	 * Adds an endpoint to the registry.
	 *
	 * This method returns false if an endpoint with the same route and method
	 * is already registered.
	 *
	 * @param Endpoint $endpoint The endpoint to add to the registry.
	 *
	 * @return bool True if the endpoint was added, false otherwise.
	 * @throws ModelException
	 */
	public function addEndpoint( Endpoint $endpoint ): bool {
		$endpoint->validate( true );

		if ( $this->hasEndpoint( $endpoint->route, $endpoint->method ) ) {
			return false;
		}

		$data     = (object) [
			'registry' => $this,
			'endpoint' => $endpoint,
		];
		$event    = new Event( 'registry/add-endpoint', $data );
		$endpoint = $this->server->dispatcher->dispatch( $event )->data->endpoint;

		$this->endpoints[ $endpoint->route ][ $endpoint->method ] = $endpoint;
		return true;
	}

	/**
	 * Removes an endpoint from the registry.
	 *
	 * @param string $route The endpoint route.
	 * @param string $method The endpoint method.
	 *
	 * @return Endpoint|null The endpoint removed from the registry or null.
	 * @throws ServerException
	 */
	public function removeEndpoint( string $route, string $method = Endpoint::GET ): Endpoint|null {
		if ( $this->hasEndpoint( $route, $method ) ) {
			$endpoint = $this->getEndpoint( $route, $method );
			unset( $this->endpoints[ $route ][ $method ] );

			$event = new Event( 'registry/remove-endpoint', (object) [
				'registry' => $this,
				'route'    => $route,
				'method'   => $method,
				'endpoint' => $endpoint,
			] );

			$data = $this->server->dispatcher->dispatch( $event )->data;
			return $data->endpoint;
		}
		return null;
	}

	/**
	 * Checks if an endpoint is registered.
	 *
	 * @param string $route The endpoint route.
	 * @param string $method The endpoint method.
	 *
	 * @return bool True if a matching endpoint is registered, null otherwise.
	 */
	public function hasEndpoint( string $route, string $method = Endpoint::GET ): bool {
		$value = isset( $this->endpoints[ $route ][ $method ] );
		$event = new Event( 'registry/has-endpoint', (object) [
			'registry' => $this,
			'route'    => $route,
			'method'   => $method,
			'value'    => $value,
		] );

		$data = $this->server->dispatcher->dispatch( $event )->data;
		return $data->value;
	}

	/**
	 * Gets a registered endpoint.
	 *
	 * @param string $route The endpoint route.
	 * @param string $method The endpoint method.
	 *
	 * @return Endpoint The matching endpoint in the registry.
	 * @throws ServerException
	 */
	public function getEndpoint( string $route, string $method = Endpoint::GET ): Endpoint {
		if ( $this->hasEndpoint( $route, $method ) ) {
			$endpoint = $this->endpoints[ $route ][ $method ];
			$event    = new Event( 'registry/get-endpoint', (object) [
				'registry' => $this,
				'route'    => $route,
				'method'   => $method,
				'endpoint' => $endpoint,
			] );

			$data = $this->server->dispatcher->dispatch( $event )->data;
			return $data->endpoint;
		}

		$error = sprintf( 'No endpoint found for %s %s in %s', $method, $route, static::class );
		throw new ServerException( $error, 500 );
	}

	/**
	 * Gets all registered endpoints.
	 *
	 * @return Endpoint[] An array of registered endpoints.
	 */
	public function getEndpoints(): array {
		$event = new Event( 'registry/get-endpoints', (object) [
			'registry'  => $this,
			'endpoints' => $this->endpoints,
		] );

		$data = $this->server->dispatcher->dispatch( $event )->data;
		return $data->endpoints;
	}

	/* -------------------------------------------------------------------------
	 * Middleware
	 * -----------------------------------------------------------------------*/

	/**
	 * Adds a middleware entry to the registry.
	 *
	 * This method returns false if a middleware entry with the same id is
	 * already registered.
	 *
	 * @param Middleware $middleware The middleware entry to add to the registry.
	 *
	 * @return bool True if the middleware was added, false otherwise.
	 * @throws ModelException
	 */
	public function addMiddleware( Middleware $middleware ): bool {
		$middleware->validate( true );

		if ( $this->hasMiddleware( $middleware->id ) ) {
			return false;
		}

		$data       = (object) [
			'registry'   => $this,
			'middleware' => $middleware,
		];
		$event      = new Event( 'registry/add-middleware', $data );
		$middleware = $this->server->dispatcher->dispatch( $event )->data->middleware;

		$this->middleware[ $middleware->id ] = $middleware;
		return uasort( $this->middleware, [ $this, 'sortMiddleware' ] );
	}

	/**
	 * Removes a middleware entry from the registry.
	 *
	 * @param string $id The middleware id.
	 *
	 * @return Middleware|null The middleware removed from the registry or null.
	 * @throws ServerException
	 */
	public function removeMiddleware( string $id ): Middleware|null {
		if ( $this->hasMiddleware( $id ) ) {
			$middleware = $this->getMiddleware( $id );
			unset( $this->middleware[ $id ] );

			$event = new Event( 'registry/remove-middleware', (object) [
				'registry'   => $this,
				'id'         => $id,
				'middleware' => $middleware,
			] );

			$data = $this->server->dispatcher->dispatch( $event )->data;
			return $data->middleware;
		}
		return null;
	}

	/**
	 * Checks if a middleware is registered.
	 *
	 * @param string $id The middleware id.
	 *
	 * @return bool True if a matching middleware is registered, null otherwise.
	 */
	public function hasMiddleware( string $id ): bool {
		$value = isset( $this->middleware[ $id ] );
		$event = new Event( 'registry/has-middleware', (object) [
			'registry' => $this,
			'id'       => $id,
			'value'    => $value,
		] );

		$data = $this->server->dispatcher->dispatch( $event )->data;
		return $data->value;
	}

	/**
	 * Gets a registered middleware entry.
	 *
	 * @param string $id The middleware id.
	 *
	 * @return Middleware The matching middleware in the registry.
	 * @throws ServerException
	 */
	public function getMiddleware( string $id ): Middleware {
		if ( $this->hasMiddleware( $id ) ) {
			$middleware = $this->middleware[ $id ];
			$event      = new Event( 'registry/get-middleware', (object) [
				'registry'   => $this,
				'id'         => $id,
				'middleware' => $middleware,
			] );

			$data = $this->server->dispatcher->dispatch( $event )->data;
			return $data->middleware;
		}

		$error = sprintf( 'Middleware %s not found in %s', $id, static::class );
		throw new ServerException( $error, 500 );
	}

	/**
	 * Gets all registered middleware instances sorted by priority.
	 *
	 * @return Middleware[] An array of registered middleware entries.
	 */
	public function getMiddlewareEntries(): array {
		$event = new Event( 'registry/get-middleware-entries', (object) [
			'registry' => $this,
			'entries'  => array_values( $this->middleware ),
		] );

		$data = $this->server->dispatcher->dispatch( $event )->data;
		return $data->entries;
	}

	/**
	 * Sorts middleware by priority.
	 *
	 * @param Middleware $a A middleware entry to sort.
	 * @param Middleware $b Another middleware entry to sort.
	 */
	protected static function sortMiddleware( Middleware $a, Middleware $b ): int {
		return $a->priority <=> $b->priority;
	}

	/* -------------------------------------------------------------------------
	 * Event listeners
	 * -----------------------------------------------------------------------*/

	/**
	 * Adds an event listener to the registry.
	 *
	 * This method returns false if an event listener with the same id and type
	 * is already registered.
	 *
	 * The listener id must be a string which is unique for the event type. It
	 * is used to get or remove a listener, potentially from another plugin.
	 * In order to prevent naming conflicts, it is recommended to use an id with
	 * a plugin prefix, like <plugin-prefix>/<listener-id>.
	 *
	 * The need for an id might seem a bit superfluous, but it allows you to
	 * remove listeners without knowing the exact callback, which is very
	 * helpful when the callback is an instance method or a closure.
	 *
	 * @param Listener $listener The event listener to add to the registry.
	 *
	 * @return bool True if the event listener was added, false otherwise.
	 * @throws ModelException
	 */
	public function addListener( Listener $listener ): bool {
		$listener->validate( true );

		if ( $this->hasListener( $listener->id ) ) {
			return false;
		}

		$data     = (object) [
			'registry' => $this,
			'listener' => $listener,
		];
		$event    = new Event( 'registry/add-listener', $data );
		$listener = $this->server->dispatcher->dispatch( $event )->data->listener;

		$this->listeners[ $listener->id ] = $listener;
		unset( $this->cache['listeners'] );
		return true;
	}

	/**
	 * Removes an event listener from the registry.
	 *
	 * @param string $id The event listener id.
	 *
	 * @return Listener|null The event listener removed from the registry or null.
	 * @throws ServerException
	 */
	public function removeListener( string $id ): Listener|null {
		if ( $this->hasListener( $id ) ) {
			$listener = $this->getListener( $id );
			unset( $this->listeners[ $id ] );
			unset( $this->cache['listeners'] );

			$event = new Event( 'registry/remove-listener', (object) [
				'registry' => $this,
				'id'       => $id,
				'listener' => $listener,
			] );

			$data = $this->server->dispatcher->dispatch( $event )->data;
			return $data->listener;
		}
		return null;
	}

	/**
	 * Checks if an event listener is registered.
	 *
	 * @param string $id The event listener id.
	 *
	 * @return bool True if a matching event listener is registered, null otherwise.
	 */
	public function hasListener( string $id ): bool {
		$value = isset( $this->listeners[ $id ] );
		$event = new Event( 'registry/has-listener', (object) [
			'registry' => $this,
			'id'       => $id,
			'value'    => $value,
		] );

		$data = $this->server->dispatcher->dispatch( $event )->data;
		return $data->value;
	}

	/**
	 * Gets a registered event listener.
	 *
	 * @param string $id The event listener id.
	 *
	 * @return Listener The matching event listener in the registry.
	 * @throws ServerException
	 */
	public function getListener( string $id ): Listener {
		if ( $this->hasListener( $id ) ) {
			$listener = $this->listeners[ $id ];
			$event    = new Event( 'registry/get-listener', (object) [
				'registry' => $this,
				'id'       => $id,
				'listener' => $listener,
			] );

			$data = $this->server->dispatcher->dispatch( $event )->data;
			return $data->listener;
		}

		$error = sprintf( 'Listener %s not found in %s', $id, static::class );
		throw new ServerException( $error, 500 );
	}

	/**
	 * Gets all registered event listeners of the given type.
	 *
	 * @param string $type The event type.
	 *
	 * @return Listener[] An array of registered listeners.
	 */
	public function getTypeListeners( string $type ): array {
		if ( $this->listeners && empty( $this->cache['listeners'] ) ) {
			$this->cache['listeners'] = $this->buildListeners();
		}
		return $this->cache['listeners'][ $type ] ?? [];
	}

	/**
	 * Gets all registered event listeners.
	 *
	 * @return Listener[][] An array containing registered listener.
	 */
	public function getListeners(): array {
		return $this->listeners;
	}

	/**
	 * Builds an array of listeners indexed by type and sorted by priority.
	 *
	 * @return Listener[][]
	 */
	protected function buildListeners(): array {
		$result = [];

		foreach ( $this->listeners as $listener ) {
			$result[ $listener->type ][] = $listener;
		}

		foreach ( $result as &$entries ) {
			usort( $entries, function ( Listener $a, Listener $b ): int {
				return $a->priority <=> $b->priority;
			} );
		}

		return $result;
	}
}
