<?php namespace Peroks\ApiServer;

/**
 * A container for registered request handlers and middleware.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
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
	 * @var Listener[][] An array of registered PSR-14 event listeners.
	 */
	protected array $listeners = [];

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
	 */
	public function addEndpoint( Endpoint $endpoint ): bool {
		$endpoint->validate( true );

		if ( $this->hasEndpoint( $endpoint->route, $endpoint->method ) ) {
			return false;
		}

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
	 */
	public function removeEndpoint( string $route, string $method = Endpoint::GET ): ?Endpoint {
		if ( $this->hasEndpoint( $route, $method ) ) {
			$endpoint = $this->getEndpoint( $route, $method );
			unset( $this->endpoints[ $route ][ $method ] );
			return $endpoint;
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
		return isset( $this->endpoints[ $route ][ $method ] );
	}

	/**
	 * Gets a registered endpoint.
	 *
	 * @param string $route The endpoint route.
	 * @param string $method The endpoint method.
	 *
	 * @return Endpoint The matching endpoint in the registry.
	 */
	public function getEndpoint( string $route, string $method = Endpoint::GET ): Endpoint {
		if ( $this->hasEndpoint( $route, $method ) ) {
			return $this->endpoints[ $route ][ $method ];
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
		return $this->endpoints;
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
	 */
	public function addMiddleware( Middleware $middleware ): bool {
		$middleware->validate( true );

		if ( $this->hasMiddleware( $middleware->id ) ) {
			return false;
		}

		// Add middleware entry and sort by priority.
		$this->middleware[ $middleware->id ] = $middleware;
		return true;
	}

	/**
	 * Removes a middleware entry from the registry.
	 *
	 * @param string $id The middleware id.
	 *
	 * @return Middleware|null The middleware removed from the registry or null.
	 */
	public function removeMiddleware( string $id ): ?Middleware {
		if ( $this->hasMiddleware( $id ) ) {
			$middleware = $this->getMiddleware( $id );
			unset( $this->middleware[ $id ] );
			return $middleware;
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
		return isset( $this->middleware[ $id ] );
	}

	/**
	 * Gets a registered middleware entry.
	 *
	 * @param string $id The middleware id.
	 *
	 * @return Middleware The matching middleware in the registry.
	 */
	public function getMiddleware( string $id ): Middleware {
		if ( $this->hasMiddleware( $id ) ) {
			return $this->middleware[ $id ];
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
		$entries = $this->middleware;
		usort( $entries, [ static::class, 'sortMiddleware' ] );
		return $entries;
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
	 */
	public function addListener( Listener $listener ): bool {
		$listener->validate( true );

		if ( $this->hasListener( $listener->id, $listener->type ) ) {
			return false;
		}

		// Add an event listener and sort by priority.
		$this->listeners[ $listener->type ][ $listener->id ] = $listener;
		return true;
	}

	/**
	 * Removes an event listener from the registry.
	 *
	 * @param string $id The event listener id.
	 * @param string $type The event type.
	 *
	 * @return Listener|null The event listener removed from the registry or null.
	 */
	public function removeListener( string $id, string $type ): ?Listener {
		if ( $this->hasListener( $id, $type ) ) {
			$listener = $this->getListener( $id, $type );
			unset( $this->listeners[ $type ][ $id ] );
			return $listener;
		}
		return null;
	}

	/**
	 * Checks if an event listener is registered.
	 *
	 * @param string $id The event listener id.
	 * @param string $type The event type.
	 *
	 * @return bool True if a matching event listener is registered, null otherwise.
	 */
	public function hasListener( string $id, string $type ): bool {
		return isset( $this->listeners[ $type ][ $id ] );
	}

	/**
	 * Gets a registered event listener.
	 *
	 * @param string $id The event listener id.
	 * @param string $type The event type.
	 *
	 * @return Listener The matching event listener in the registry.
	 */
	public function getListener( string $id, string $type ): Listener {
		if ( $this->hasListener( $id, $type ) ) {
			return $this->listeners[ $type ][ $id ];
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
		$listeners = $this->listeners[ $type ] ?? [];
		usort( $listeners, [ static::class, 'sortListener' ] );
		return $listeners;
	}

	/**
	 * Gets all registered event listeners.
	 *
	 * @return Listener[][] An array containing registered listener.
	 */
	public function getListeners(): array {
		$allListeners = $this->listeners;

		foreach ( $allListeners as &$listeners ) {
			usort( $listeners, [ static::class, 'sortListener' ] );
		}
		return $allListeners;
	}

	/**
	 * Sorts listener by priority.
	 *
	 * @param Listener $a An event listener to sort.
	 * @param Listener $b Another event listener to sort.
	 */
	protected static function sortListener( Listener $a, Listener $b ): int {
		return $a->priority <=> $b->priority;
	}
}
