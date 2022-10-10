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
	 * @var Server The api server.
	 */
	protected Server $server;

	/**
	 * @var Endpoint[] An array of registered endpoints for PSR-15 handlers.
	 */
	protected array $endpoints = [];

	/**
	 * @var Middleware[] An array of registered entries for PSR-15 middleware.
	 */
	protected array $middleware = [];

	/**
	 * Constructor.
	 *
	 * @param Server $server The api server.
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
		$endpoint = $this->getEndpoint( $route, $method );
		unset( $this->endpoints[ $route ][ $method ] );
		return $endpoint;
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
	 * @return Endpoint|null The matching endpoint in the registry or null.
	 */
	public function getEndpoint( string $route, string $method = Endpoint::GET ): ?Endpoint {
		return $this->endpoints[ $route ][ $method ] ?? null;
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
	 * This method returns false if a middleware entry with the same name is
	 * already registered.
	 *
	 * @param Middleware $middleware The middleware entry to add to the registry.
	 *
	 * @return bool True if the middleware was added, false otherwise.
	 */
	public function addMiddleware( Middleware $middleware ): bool {
		$middleware->validate( true );

		if ( $this->hasMiddleware( $middleware->name ) ) {
			return false;
		}

		// Add middleware entry and sort by priority.
		$this->middleware[ $middleware->name ] = $middleware;
		usort( $this->middleware, [ static::class, 'sortMiddleware' ] );

		return true;
	}

	/**
	 * Removes a middleware entry from the registry.
	 *
	 * @param string $name The middleware name.
	 *
	 * @return Middleware|null The middleware removed from the registry or null.
	 */
	public function removeMiddleware( string $name ): ?Middleware {
		$middleware = $this->getMiddleware( $name );
		unset( $this->middleware[ $name ] );
		return $middleware;
	}

	/**
	 * Checks if a middleware is registered.
	 *
	 * @param string $name The middleware name.
	 *
	 * @return bool True if a matching middleware is registered, null otherwise.
	 */
	public function hasMiddleware( string $name ): bool {
		return isset( $this->middleware[ $name ] );
	}

	/**
	 * Gets a registered middleware entry.
	 *
	 * @param string $name The middleware name.
	 *
	 * @return Middleware|null The matching middleware in the registry or null.
	 */
	public function getMiddleware( string $name ): ?Middleware {
		return $this->middleware[ $name ] ?? null;
	}

	/**
	 * Gets all registered middleware instances.
	 *
	 * @return Middleware[] An array of registered middleware entries.
	 */
	public function getMiddlewareEntries(): array {
		return $this->middleware;
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
}
