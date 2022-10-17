<?php namespace Peroks\ApiServer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The api server main class.
 *
 * It is an ultra light api server based on PSR-4, PSR-7, PSR-14 and PSR-15
 * best-practice standards.
 *
 * The api server is not a stand-alone application, but a host for external
 * PSR-15 request handlers and middleware. You can use this class as a module
 * in your own application or extend it to create custom api servers.
 *
 * The api server does not handle any requests by itself, it just dispatches
 * them to the registered request handlers and middleware and returns their
 * responses.
 *
 * @property-read Handler $handler The internal PSR-15 request handler.
 * @property-read Registry $registry A container for registered request handlers, middleware and event listeners.
 * @property-read Dispatcher $dispatcher A PSR-14 listener provider and event dispatcher.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Server implements RequestHandlerInterface {

	/**
	 * The api server version.
	 */
	const VERSION = '0.2.0';

	/**
	 * @var object[] The server properties.
	 */
	protected array $properties = [];

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Provides protected access to server properties.
	 *
	 * @param string $name The name of the property to get.
	 *
	 * @return object A server property.
	 */
	public function __get( string $name ): object {
		if ( empty( $this->properties[ $name ] ) ) {
			switch ( $name ) {

				// Create the internal PSR-15 request handler.
				case 'handler':
					$this->properties[ $name ] = new Handler( $this );
					break;

				// Create the container for registered request handlers and middleware.
				case 'registry':
					$this->properties[ $name ] = new Registry( $this );
					break;

				// Create the internal PSR-14 listener provider and event dispatcher.
				case 'dispatcher':
					$this->properties[ $name ] = new Dispatcher( $this->registry );
					break;

				// Throws an exception if the property name doesn't exist.
				default:
					$error = sprintf( 'The property %s does not exist in %s', $name, static::class );
					throw new ServerException( $error, 500 );
			}
		}

		return $this->properties[ $name ];
	}

	/**
	 * Forwards a request to the server request dispatcher.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {
		return $this->handler->handle( $request );
	}
}
