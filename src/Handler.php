<?php
/**
 * This class dispatches requests to registered middleware and request handlers.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */

declare( strict_types = 1 );
namespace Peroks\ApiServer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This class dispatches requests to registered middleware and request handlers.
 */
class Handler implements RequestHandlerInterface {

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

	/**
	 * Dispatches requests to registered middleware and request handlers.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 * @throws ServerException
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {
		$endpoint = $this->getEndpoint( $request, $attributes );
		$request  = $this->addAttributes( $request, $attributes );
		$stack    = $this->getStack( $endpoint->handler );
		$data     = (object) [
			'handler'  => $this,
			'stack'    => $stack,
			'endpoint' => $endpoint,
			'request'  => $request,
		];

		// Dispatch handler request event.
		$event = new Event( 'server/request', $data );
		$data  = $this->server->dispatcher->dispatch( $event )->data;

		// Get the response for the possibly modified request.
		$data->response = $data->stack->handle( $data->request );

		// Dispatch handler response event.
		$event = new Event( 'server/response', $data );
		$data  = $this->server->dispatcher->dispatch( $event )->data;

		// Return the possibly modified server response.
		return $data->response;
	}

	/**
	 * Gets an endpoint matching the request.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 * @param mixed $attributes An assoc array of attributes extracted from the request uri.
	 *
	 * @return Endpoint The matching endpoint.
	 * @throws ServerException
	 */
	protected function getEndpoint( ServerRequestInterface $request, mixed &$attributes = null ): Endpoint {
		$endpoints  = $this->server->registry->getEndpoints();
		$attributes = is_array( $attributes ) ? $attributes : [];
		$path       = $request->getUri()->getPath();

		foreach ( $endpoints as $route => $methods ) {
			if ( preg_match( '|^' . $route . '$|', $path, $matches ) ) {
				$method   = $request->getMethod();
				$endpoint = $methods[ $method ] ?? null;

				if ( empty( $endpoint ) ) {
					$error = 'The requested method is not allowed';
					throw new ServerException( $error, 405 );
				}

				$attributes             = array_filter( $matches, 'is_string', ARRAY_FILTER_USE_KEY );
				$attributes['__route']  = $endpoint->route;
				$attributes['__action'] = $endpoint->action;

				return $endpoint;
			}
		}

		$error = 'The requested resource was not found';
		throw new ServerException( $error, 404 );
	}

	/**
	 * Adds attributes to a server request.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 * @param array $attributes The attributes to add to the request.
	 *
	 * @return ServerRequestInterface The cloned request with attributes.
	 */
	protected function addAttributes( ServerRequestInterface $request, array $attributes ): ServerRequestInterface {
		foreach ( $attributes as $attribute => $value ) {
			$request = $request->withAttribute( $attribute, $value );
		}
		return $request;
	}

	/**
	 * Gets the middleware processing stack.
	 *
	 * @param RequestHandlerInterface $handler The endpoint request handler.
	 *
	 * @return Stack The middleware stack.
	 */
	protected function getStack( RequestHandlerInterface $handler ): Stack {
		$entries    = $this->server->registry->getMiddlewareEntries();
		$middleware = array_column( $entries, 'instance' );

		return new Stack( $middleware, $handler );
	}
}
