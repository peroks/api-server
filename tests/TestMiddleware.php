<?php
/**
 * Middleware class for testing purposes.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */

declare( strict_types = 1 );
namespace Peroks\ApiServer\Tests;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware class for testing purposes.
 */
class TestMiddleware implements MiddlewareInterface {

	/**
	 * Process an incoming server request.
	 *
	 * @param ServerRequestInterface $request The server request.
	 * @param RequestHandlerInterface $handler The request handler.
	 *
	 * @return ResponseInterface
	 */
	public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
		if ( $request->getHeader( 'authorization' ) ) {
			return $handler->handle( $request );
		}
		return new Response( 403 );
	}
}
