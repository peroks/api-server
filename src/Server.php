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
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Server implements RequestHandlerInterface {

	/**
	 * The api server version.
	 */
	const VERSION = '0.5.0';

	/**
	 * @var Registry A container for registered request handlers, middleware and event listeners.
	 */
	public readonly Registry $registry;

	/**
	 * @var RequestHandlerInterface The internal PSR-15 request handler and distributor.
	 */
	public readonly RequestHandlerInterface $handler;

	/**
	 * @var DispatcherInterface A PSR-14 listener provider and event dispatcher.
	 */
	public readonly DispatcherInterface $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param Registry|null $registry A container for registered request handlers, middleware and event listeners.
	 * @param RequestHandlerInterface|null $handler The internal PSR-15 request handler and distributor.
	 * @param DispatcherInterface|null $dispatcher A PSR-14 listener provider and event dispatcher.
	 */
	public function __construct( Registry $registry = null, RequestHandlerInterface $handler = null, DispatcherInterface $dispatcher = null ) {
		$this->registry   = $registry ?? new Registry( $this );
		$this->handler    = $handler ?? new Handler( $this );
		$this->dispatcher = $dispatcher ?? new Dispatcher( $this );
		$this->dispatcher->dispatch( new Event( 'server/init', $this ) );
	}

	/**
	 * Forwards a request to the internal request handler.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {
		$data = (object) [
			'server'  => $this,
			'request' => $request,
		];

		// Dispatch server request event.
		$event = new Event( 'server/request', $data );
		$data  = $this->dispatcher->dispatch( $event )->data;

		// Get the response for the possibly modified request.
		$data->response = $this->handler->handle( $data->request );

		// Dispatch server response event.
		$event = new Event( 'server/response', $data );
		$data  = $this->dispatcher->dispatch( $event )->data;

		// Return the possibly modified server response.
		return $data->response;
	}
}
