<?php namespace Peroks\ApiServer;

use Peroks\ApiServer\Exceptions\ApiServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Processes a middleware stack.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Stack implements RequestHandlerInterface {

	/**
	 * @var MiddlewareInterface[]|RequestHandlerInterface[] $middleware The middleware stack.
	 */
	protected array $middleware;

	/**
	 * @var MiddlewareInterface|RequestHandlerInterface $next The next middleware in the stack.
	 */
	protected $next;

	/**
	 * Constructor.
	 *
	 * @param MiddlewareInterface[] $middleware An array of PSR-15 middleware sorted by priority.
	 * @param RequestHandlerInterface $handler A PSR-15 request handler.
	 */
	public function __construct( array $middleware, RequestHandlerInterface $handler ) {
		$this->middleware   = $middleware;
		$this->middleware[] = $handler;
		$this->next         = reset( $this->middleware );
	}

	/**
	 * Creates a middleware stack instance.
	 *
	 * @param MiddlewareInterface[] $middleware An array of PSR-15 middleware sorted by priority.
	 * @param RequestHandlerInterface $handler A PSR-15 request handler.
	 *
	 * @return Stack The middleware processing stack.
	 */
	public static function create( array $middleware, RequestHandlerInterface $handler ): self {
		return new static( $middleware, $handler );
	}

	/**
	 * Recursively forwards requests to the next middleware or handler in the stack.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {

		// Forward the request to the next middleware in the stack for processing.
		if ( $this->next instanceof MiddlewareInterface ) {
			$middleware = $this->next;
			$this->next = next( $this->middleware );
			return $middleware->process( $request, $this );
		}

		// Forward the request to the request handler after completing middleware processing.
		if ( $this->next instanceof RequestHandlerInterface ) {
			return $this->next->handle( $request );
		}

		$error = 'Middleware must implement the PSR-15 MiddlewareInterface';
		throw new ApiServerException( $error, 500 );
	}
}
