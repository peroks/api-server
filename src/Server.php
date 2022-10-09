<?php namespace Peroks\ApiServer;

use Peroks\ApiServer\Exceptions\ApiServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The api server main class.
 *
 * It is an ultra light api server based on PSR-4, PSR-7, PSR-11 and PSR-15
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
 * @property-read Settings $settings The server settings.
 * @property-read Dependencies $dependencies The PSR-11 container for dependency injection.
 * @property-read Dispatcher $dispatcher The PSR-15 request dispatcher.
 * @property-read Registry $registry A container for registered request handlers and middleware.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Server implements RequestHandlerInterface {

	/**
	 * @var object[] The server properties.
	 */
	protected array $properties = [];

	/**
	 * Constructor.
	 *
	 * @param Settings|null $settings The server settings.
	 */
	public function __construct( Settings $settings = null ) {
		if ( $settings ) {
			$this->properties['settings'] = $settings->validate( true );
		}

		$this->init();
		$this->plugins();
	}

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

				// Create the server default settings.
				case 'settings':
					$settings                  = Settings::read( 'api-server.json' );
					$this->properties[ $name ] = $settings->validate( true );
					break;

				// Create the PSR-11 container for dependency injection.
				case 'dependencies':
					$this->properties[ $name ] = new Dependencies( $this->settings );
					break;

				// Create the PSR-15 request dispatcher.
				case 'dispatcher':
					$this->properties[ $name ] = new Dispatcher( $this );
					break;

				// Create the container for registered request handlers and middleware.
				case 'registry':
					$this->properties[ $name ] = new Registry( $this );
					break;

				// Throws an exception if the property name doesn't exist.
				default:
					$error = sprintf( 'The property %s does not exist in %s', $name, static::class );
					throw new ApiServerException( $error, 500 );
			}
		}

		return $this->properties[ $name ];
	}

	/**
	 * Initialises the api server.
	 */
	protected function init() {}

	/**
	 * Loads server plugins.
	 */
	protected function plugins() {
		$apiServer = $this;

		foreach ( $this->dependencies->get( 'plugins' ) as $path ) {
			is_readable( $path ) && require $path;
		}
	}

	/**
	 * Forwards a request to the server request dispatcher.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {
		return $this->dispatcher->handle( $request );
	}
}
